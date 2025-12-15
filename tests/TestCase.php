<?php

namespace Tests;

use App\Models\Message;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\CreatesApplication;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, RefreshDatabase;

    /**
     * Crea un usuario de prueba
     */
    protected function createUser(array $attributes = []): User
    {
        return User::factory()->create($attributes);
    }

    /**
     * Crea un thread de prueba
     */
    protected function createThread(array $attributes = [], ?array $participants = null): Thread
    {
        $thread = Thread::factory()->create($attributes);

        if ($participants) {
            $thread->participants()->sync($participants);
        }

        return $thread;
    }

    /**
     * Crea un mensaje de prueba
     */
    protected function createMessage(Thread $thread, array $attributes = [], ?User $user = null): Message
    {
        if (!$user) {
            $user = $this->createUser();
        }

        return Message::factory()->create(array_merge([
            'thread_id' => $thread->id,
            'user_id' => $user->id,
        ], $attributes));
    }

    /**
     * Autentica un usuario para la solicitud actual
     */
    protected function actingAsUser(?User $user = null): User
    {
        $user = $user ?: $this->createUser();
        $this->actingAs($user);

        return $user;
    }

    /**
     * Autentica un usuario con Sanctum para pruebas de API
     */
    protected function actingAsApiUser(?User $user = null, array $abilities = ['*']): User
    {
        $user = $user ?: $this->createUser();
        $this->actingAs($user, 'sanctum');

        return $user;
    }

    /**
     * Obtiene un token JWT para un usuario
     */
    protected function getAuthToken(?User $user = null): string
    {
        if (!$user) {
            $user = User::factory()->create([
                'password' => bcrypt('password123')
            ]);
        }

        $token = auth('api')->attempt([
            'email' => $user->email,
            'password' => 'password123'
        ]);

        if (!$token) {
            // Si el usuario ya existe con otra contraseña, intentar con la contraseña por defecto
            $user->update(['password' => bcrypt('password123')]);
            $token = auth('api')->attempt([
                'email' => $user->email,
                'password' => 'password123'
            ]);
        }

        return $token;
    }
}
