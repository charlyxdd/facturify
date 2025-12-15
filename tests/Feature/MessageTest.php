<?php

namespace Tests\Feature;

use App\Models\Thread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_send_message_to_thread()
    {
        $user = $this->createUser(['password' => bcrypt('password123')]);
        $thread = $this->createThread([], [$user->id]);

        $token = $this->getAuthToken($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ])->postJson("/api/threads/{$thread->id}/messages", [
                    'body' => 'Este es un mensaje de prueba'
                ]);

        $response->assertStatus(201)
            ->assertJson([
                'body' => 'Este es un mensaje de prueba',
                'is_read' => false
            ])
            ->assertJsonPath('user.id', $user->id);

        $this->assertDatabaseHas('messages', [
            'thread_id' => $thread->id,
            'user_id' => $user->id,
            'body' => 'Este es un mensaje de prueba'
        ]);
    }

    /** @test */
    public function message_requires_body()
    {
        $user = $this->createUser(['password' => bcrypt('password123')]);
        $thread = $this->createThread([], [$user->id]);

        $token = $this->getAuthToken($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ])->postJson("/api/threads/{$thread->id}/messages", [
                    'body' => ''
                ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('body');
    }

    /** @test */
    public function user_cannot_send_message_to_unsubscribed_thread()
    {
        $user1 = $this->createUser(['password' => bcrypt('password123')]);
        $user2 = $this->createUser(['password' => bcrypt('password123')]);

        // user1 crea un hilo donde user2 no está suscrito
        $thread = $this->createThread(['created_by' => $user1->id], [$user1->id]);

        $token = $this->getAuthToken($user2);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ])->postJson("/api/threads/{$thread->id}/messages", [
                    'body' => 'Mensaje no autorizado'
                ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function messages_are_marked_as_read_when_viewing_thread()
    {
        $user1 = $this->createUser(['password' => bcrypt('password123')]);
        $user2 = $this->createUser(['password' => bcrypt('password123')]);
        $thread = $this->createThread(['created_by' => $user1->id], [$user1->id, $user2->id]);

        // user2 envía mensajes no leídos a user1
        $message1 = $this->createMessage($thread, ['is_read' => false], $user2);
        $message2 = $this->createMessage($thread, ['is_read' => false], $user2);

        // Verificar que los mensajes no están leídos
        $this->assertFalse($message1->fresh()->is_read);
        $this->assertFalse($message2->fresh()->is_read);

        // user1 ve el hilo, lo cual debería marcar los mensajes de user2 como leídos
        $token = $this->getAuthToken($user1);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ])->getJson("/api/threads/{$thread->id}");

        $response->assertStatus(200);

        // Verificar que los mensajes están marcados como leídos
        $this->assertTrue($message1->fresh()->is_read);
        $this->assertTrue($message2->fresh()->is_read);
    }

    /** @test */
    public function user_can_see_only_their_unread_messages_count()
    {
        $user1 = $this->createUser(['password' => bcrypt('password123')]);
        $user2 = $this->createUser(['password' => bcrypt('password123')]);

        // Crear un hilo con ambos usuarios
        $thread = $this->createThread(['created_by' => $user1->id], [$user1->id, $user2->id]);

        // user1 envía un mensaje a user2
        $token1 = $this->getAuthToken($user1);

        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token1,
            'Accept' => 'application/json'
        ])->postJson("/api/threads/{$thread->id}/messages", [
                    'body' => 'Hola, ¿cómo estás?'
                ]);

        // user2 debería ver 1 mensaje no leído
        $token2 = $this->getAuthToken($user2);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token2,
            'Accept' => 'application/json'
        ])->getJson('/api/threads');

        $response->assertStatus(200);

        // Verificar que user2 tiene 1 mensaje no leído
        $data = $response->json('data');
        $this->assertNotEmpty($data);
        $this->assertEquals(
            1,
            $data[0]['unread_count'],
            'User2 debería tener 1 mensaje no leído'
        );

        // user1 no debería ver mensajes no leídos (porque él los envió)
        // Refrescar el token de user1
        auth('api')->logout();
        $token1 = $this->getAuthToken($user1);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token1,
            'Accept' => 'application/json'
        ])->getJson('/api/threads');

        $response->assertStatus(200);

        // Verificar que user1 no tiene mensajes no leídos
        $data = $response->json('data');
        $this->assertNotEmpty($data);
        $this->assertEquals(
            0,
            $data[0]['unread_count'],
            'User1 no debería tener mensajes no leídos porque él envió el mensaje'
        );
    }
}
