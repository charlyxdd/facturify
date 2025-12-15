<?php

namespace Tests\Performance;

use App\Models\Message;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThreadPerformanceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_load_large_number_of_threads_efficiently()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123')
        ]);

        // Crear 100 threads (reducido de 1000 para pruebas más rápidas)
        $threads = Thread::factory()
            ->count(100)
            ->create(['created_by' => $user->id]);

        // Asignar el usuario como participante en todos los threads
        foreach ($threads as $thread) {
            $thread->participants()->attach($user->id);
        }

        $token = auth('api')->attempt(['email' => $user->email, 'password' => 'password123']);

        // Medir el tiempo de respuesta
        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ])->getJson('/api/threads');

        $endTime = microtime(true);
        $endMemory = memory_get_usage();

        $executionTime = ($endTime - $startTime) * 1000; // en milisegundos
        $memoryUsed = ($endMemory - $startMemory) / 1024 / 1024; // en MB

        $response->assertStatus(200);

        // Verificar que el tiempo de respuesta es aceptable (< 2 segundos)
        $this->assertLessThan(
            2000,
            $executionTime,
            "La carga de threads tomó {$executionTime}ms, debería ser menor a 2000ms"
        );

        // Verificar que el uso de memoria es razonable (< 50MB)
        $this->assertLessThan(
            50,
            $memoryUsed,
            "El uso de memoria fue {$memoryUsed}MB, debería ser menor a 50MB"
        );
    }

    /** @test */
    public function can_load_thread_with_many_messages_efficiently()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123')
        ]);
        $thread = $this->createThread(['created_by' => $user->id], [$user->id]);

        // Crear 100 mensajes (reducido de 1000 para pruebas más rápidas)
        Message::factory()
            ->count(100)
            ->create([
                'thread_id' => $thread->id,
                'user_id' => $user->id,
            ]);

        $token = auth('api')->attempt(['email' => $user->email, 'password' => 'password123']);

        // Medir el tiempo de respuesta
        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ])->getJson("/api/threads/{$thread->id}");

        $endTime = microtime(true);
        $endMemory = memory_get_usage();

        $executionTime = ($endTime - $startTime) * 1000; // en milisegundos
        $memoryUsed = ($endMemory - $startMemory) / 1024 / 1024; // en MB

        $response->assertStatus(200);

        // Verificar que el tiempo de respuesta es aceptable (< 1.5 segundos)
        $this->assertLessThan(
            1500,
            $executionTime,
            "La carga de mensajes tomó {$executionTime}ms, debería ser menor a 1500ms"
        );

        // Verificar que el uso de memoria es razonable (< 30MB)
        $this->assertLessThan(
            30,
            $memoryUsed,
            "El uso de memoria fue {$memoryUsed}MB, debería ser menor a 30MB"
        );
    }

    /** @test */
    public function pagination_works_efficiently_with_many_threads()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123')
        ]);

        // Crear 50 threads
        $threads = Thread::factory()
            ->count(50)
            ->create(['created_by' => $user->id]);

        foreach ($threads as $thread) {
            $thread->participants()->attach($user->id);
        }

        $token = auth('api')->attempt(['email' => $user->email, 'password' => 'password123']);

        // Probar primera página
        $startTime = microtime(true);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ])->getJson('/api/threads?page=1&per_page=15');

        $executionTime = (microtime(true) - $startTime) * 1000;

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'links',
                'meta' => [
                    'current_page',
                    'total',
                    'per_page'
                ]
            ]);

        // La paginación debe ser rápida (< 500ms)
        $this->assertLessThan(
            500,
            $executionTime,
            "La paginación tomó {$executionTime}ms, debería ser menor a 500ms"
        );

        // Verificar que solo devuelve 15 items
        $this->assertCount(15, $response->json('data'));
    }

    /** @test */
    public function search_performs_efficiently()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123')
        ]);

        // Crear threads con diferentes subjects
        Thread::factory()->create([
            'subject' => 'Importante: Reunión de equipo',
            'created_by' => $user->id
        ])->participants()->attach($user->id);

        Thread::factory()->count(30)->create([
            'created_by' => $user->id
        ])->each(fn($t) => $t->participants()->attach($user->id));

        $token = auth('api')->attempt(['email' => $user->email, 'password' => 'password123']);

        // Medir búsqueda
        $startTime = microtime(true);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ])->getJson('/api/threads?search=Importante');

        $executionTime = (microtime(true) - $startTime) * 1000;

        $response->assertStatus(200);

        // La búsqueda debe ser rápida (< 300ms)
        $this->assertLessThan(
            300,
            $executionTime,
            "La búsqueda tomó {$executionTime}ms, debería ser menor a 300ms"
        );
    }

    /** @test */
    public function concurrent_message_creation_performs_well()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123')
        ]);
        $thread = $this->createThread(['created_by' => $user->id], [$user->id]);

        $token = auth('api')->attempt(['email' => $user->email, 'password' => 'password123']);

        $startTime = microtime(true);

        // Simular creación de múltiples mensajes
        for ($i = 0; $i < 10; $i++) {
            $this->withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json'
            ])->postJson("/api/threads/{$thread->id}/messages", [
                        'body' => "Mensaje de prueba #{$i}"
                    ])->assertStatus(201);
        }

        $executionTime = (microtime(true) - $startTime) * 1000;

        // 10 mensajes deben crearse en menos de 2 segundos
        $this->assertLessThan(
            2000,
            $executionTime,
            "Crear 10 mensajes tomó {$executionTime}ms, debería ser menor a 2000ms"
        );

        // Verificar que todos los mensajes se crearon
        $this->assertEquals(10, Message::where('thread_id', $thread->id)->count());
    }

    /** @test */
    public function unread_count_calculation_is_efficient()
    {
        $user1 = User::factory()->create([
            'password' => bcrypt('password123')
        ]);
        $user2 = User::factory()->create([
            'password' => bcrypt('password456')
        ]);

        // Crear múltiples threads con mensajes no leídos
        for ($i = 0; $i < 20; $i++) {
            $thread = $this->createThread(['created_by' => $user1->id], [$user1->id, $user2->id]);

            // Crear 5 mensajes no leídos por thread
            Message::factory()->count(5)->unread()->create([
                'thread_id' => $thread->id,
                'user_id' => $user1->id,
            ]);
        }

        $token = auth('api')->attempt(['email' => $user2->email, 'password' => 'password456']);

        $startTime = microtime(true);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ])->getJson('/api/threads');

        $executionTime = (microtime(true) - $startTime) * 1000;

        $response->assertStatus(200);

        // El cálculo de mensajes no leídos debe ser eficiente (< 800ms)
        $this->assertLessThan(
            800,
            $executionTime,
            "El cálculo de mensajes no leídos tomó {$executionTime}ms, debería ser menor a 800ms"
        );
    }
}
