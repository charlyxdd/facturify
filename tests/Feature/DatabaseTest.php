<?php

namespace Tests\Feature;

use App\Models\Message;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function transaction_rollback_on_error()
    {
        $user = $this->createUser();

        try {
            DB::transaction(function () use ($user) {
                // Crear un thread válido
                $thread = Thread::create([
                    'subject' => 'Test Thread',
                    'created_by' => $user->id,
                ]);

                // Intentar crear un mensaje con datos inválidos (sin thread_id)
                // Esto debería lanzar una excepción
                Message::create([
                    'user_id' => $user->id,
                    'body' => 'Test message',
                    // thread_id faltante intencionalmente
                ]);
            });
        } catch (\Exception $e) {
            // Se espera una excepción
        }

        // Verificar que el thread no se creó debido al rollback
        $this->assertEquals(0, Thread::count());
    }

    /** @test */
    public function referential_integrity_prevents_orphan_messages()
    {
        $user = $this->createUser();
        $thread = $this->createThread(['created_by' => $user->id], [$user->id]);
        $message = $this->createMessage($thread, [], $user);

        // Verificar que el mensaje existe
        $this->assertDatabaseHas('messages', ['id' => $message->id]);

        // Intentar eliminar el thread (debería fallar o eliminar en cascada según la configuración)
        try {
            $thread->delete();

            // Si la eliminación fue exitosa, verificar que los mensajes también se eliminaron (cascada)
            $this->assertDatabaseMissing('messages', ['id' => $message->id]);
        } catch (\Exception $e) {
            // Si falla, es porque hay restricción de integridad referencial
            $this->assertDatabaseHas('messages', ['id' => $message->id]);
        }
    }

    /** @test */
    public function critical_indexes_exist()
    {
        // Verificar que existen índices críticos para rendimiento
        $threadIndexes = Schema::getIndexes('threads');
        $messageIndexes = Schema::getIndexes('messages');
        $participantIndexes = Schema::getIndexes('thread_participants');

        // Verificar que las tablas tienen índices
        $this->assertNotEmpty($threadIndexes, 'La tabla threads debe tener índices');
        $this->assertNotEmpty($messageIndexes, 'La tabla messages debe tener índices');
        $this->assertNotEmpty($participantIndexes, 'La tabla thread_participants debe tener índices');

        // Verificar índices específicos en messages
        $messageIndexNames = array_column($messageIndexes, 'name');

        // Debe haber un índice en thread_id para búsquedas eficientes
        $hasThreadIdIndex = collect($messageIndexes)->contains(function ($index) {
            return in_array('thread_id', $index['columns']);
        });

        $this->assertTrue($hasThreadIdIndex, 'Debe existir un índice en messages.thread_id');

        // Verificar índices en thread_participants
        $hasThreadParticipantIndex = collect($participantIndexes)->contains(function ($index) {
            return in_array('thread_id', $index['columns']) || in_array('user_id', $index['columns']);
        });

        $this->assertTrue($hasThreadParticipantIndex, 'Debe existir un índice en thread_participants');
    }

    /** @test */
    public function database_tables_exist()
    {
        $this->assertTrue(Schema::hasTable('users'));
        $this->assertTrue(Schema::hasTable('threads'));
        $this->assertTrue(Schema::hasTable('messages'));
        $this->assertTrue(Schema::hasTable('thread_participants'));
    }

    /** @test */
    public function threads_table_has_required_columns()
    {
        $this->assertTrue(Schema::hasColumn('threads', 'id'));
        $this->assertTrue(Schema::hasColumn('threads', 'subject'));
        $this->assertTrue(Schema::hasColumn('threads', 'created_by'));
        $this->assertTrue(Schema::hasColumn('threads', 'created_at'));
        $this->assertTrue(Schema::hasColumn('threads', 'updated_at'));
    }

    /** @test */
    public function messages_table_has_required_columns()
    {
        $this->assertTrue(Schema::hasColumn('messages', 'id'));
        $this->assertTrue(Schema::hasColumn('messages', 'thread_id'));
        $this->assertTrue(Schema::hasColumn('messages', 'user_id'));
        $this->assertTrue(Schema::hasColumn('messages', 'body'));
        $this->assertTrue(Schema::hasColumn('messages', 'is_read'));
        $this->assertTrue(Schema::hasColumn('messages', 'created_at'));
        $this->assertTrue(Schema::hasColumn('messages', 'updated_at'));
    }

    /** @test */
    public function thread_participants_table_has_required_columns()
    {
        $this->assertTrue(Schema::hasColumn('thread_participants', 'thread_id'));
        $this->assertTrue(Schema::hasColumn('thread_participants', 'user_id'));
        $this->assertTrue(Schema::hasColumn('thread_participants', 'last_read_at'));
    }

    /** @test */
    public function cascade_delete_removes_participant_relationships()
    {
        $user = $this->createUser();
        $thread = $this->createThread(['created_by' => $user->id], [$user->id]);

        // Verificar que la relación existe
        $this->assertDatabaseHas('thread_participants', [
            'thread_id' => $thread->id,
            'user_id' => $user->id,
        ]);

        // Eliminar el thread
        $thread->delete();

        // Verificar que la relación se eliminó
        $this->assertDatabaseMissing('thread_participants', [
            'thread_id' => $thread->id,
            'user_id' => $user->id,
        ]);
    }
}
