<?php

namespace Tests\Feature;

use App\Models\Thread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThreadTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_list_their_threads()
    {
        $user = $this->createUser(['password' => bcrypt('password123')]);
        $threads = Thread::factory()->count(3)->create();

        // Asignar al usuario como participante en los hilos
        foreach ($threads as $thread) {
            $thread->participants()->attach($user->id);
        }

        $token = $this->getAuthToken($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ])->getJson('/api/threads');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'subject',
                        'created_at',
                        'updated_at'
                    ]
                ],
                'links',
                'meta'
            ]);
    }

    /** @test */
    public function user_can_view_their_thread()
    {
        $user = $this->createUser(['password' => bcrypt('password123')]);
        $thread = $this->createThread([], [$user->id]);

        $token = $this->getAuthToken($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ])->getJson("/api/threads/{$thread->id}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $thread->id,
                'subject' => $thread->subject,
            ])
            ->assertJsonStructure([
                'id',
                'subject',
                'messages' => [
                    'data' => [
                        '*' => [
                            'id',
                            'body',
                            'user' => [
                                'id',
                                'name',
                                'email'
                            ],
                            'created_at',
                            'is_read'
                        ]
                    ],
                    'current_page',
                    'last_page',
                    'per_page',
                    'total'
                ],
                'participants' => [
                    '*' => [
                        'id',
                        'name'
                    ]
                ]
            ]);
    }

    /** @test */
    public function user_cannot_view_other_users_thread()
    {
        $user1 = $this->createUser(['password' => bcrypt('password123')]);
        $user2 = $this->createUser(['password' => bcrypt('password123')]);
        $thread = $this->createThread([], [$user1->id]);

        $token = $this->getAuthToken($user2);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ])->getJson("/api/threads/{$thread->id}");

        $response->assertStatus(403);
    }

    /** @test */
    public function user_can_create_thread()
    {
        $user = $this->createUser(['password' => bcrypt('password123')]);
        $participant = $this->createUser();

        $token = $this->getAuthToken($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ])->postJson('/api/threads', [
                    'subject' => 'Nuevo tema de conversación',
                    'body' => 'Este es el primer mensaje del hilo',
                    'participants' => [$participant->id]
                ]);

        $response->assertStatus(201)
            ->assertJson([
                'subject' => 'Nuevo tema de conversación',
            ])
            ->assertJsonStructure([
                'id',
                'subject',
                'latest_message' => [
                    'id',
                    'body',
                    'user'
                ],
                'participants'
            ]);

        $this->assertDatabaseHas('threads', [
            'subject' => 'Nuevo tema de conversación',
        ]);

        $this->assertDatabaseHas('messages', [
            'body' => 'Este es el primer mensaje del hilo',
            'user_id' => $user->id
        ]);
    }

    /** @test */
    public function thread_creation_requires_valid_data()
    {
        $user = $this->createUser(['password' => bcrypt('password123')]);

        $token = $this->getAuthToken($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ])->postJson('/api/threads', [
                    'subject' => '',
                    'body' => '',
                    'participants' => []
                ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['subject', 'body', 'participants']);
    }
}
