<?php

namespace Database\Seeders;

use App\Models\Message;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = collect([
            User::factory()->create([
                'name' => 'User One',
                'email' => 'user1@test.com',
                'password' => bcrypt('password'),
            ]),
            User::factory()->create([
                'name' => 'User Two',
                'email' => 'user2@test.com',
                'password' => bcrypt('password'),
            ]),
            User::factory()->create([
                'name' => 'User Three',
                'email' => 'user3@test.com',
                'password' => bcrypt('password'),
            ]),
            User::factory()->create([
                'name' => 'User Four',
                'email' => 'user4@test.com',
                'password' => bcrypt('password'),
            ]),
            User::factory()->create([
                'name' => 'User Five',
                'email' => 'user5@test.com',
                'password' => bcrypt('password'),
            ]),
        ]);

        for ($i = 0; $i < 10; $i++) {
            $creator = $users->random();
            
            $thread = Thread::factory()->create([
                'created_by' => $creator->id,
            ]);

            $participantCount = rand(2, 4);
            $participants = $users->random($participantCount);
            
            if (!$participants->contains($creator)) {
                $participants->push($creator);
            }

            foreach ($participants as $participant) {
                $thread->participants()->attach($participant->id, [
                    'last_read_at' => now()->subHours(rand(1, 48)),
                ]);
            }

            $messageCount = rand(3, 8);
            $baseTime = now()->subDays(rand(1, 30));

            for ($j = 0; $j < $messageCount; $j++) {
                $messageUser = $j === 0 ? $creator : $participants->random();
                
                Message::factory()->create([
                    'thread_id' => $thread->id,
                    'user_id' => $messageUser->id,
                    'is_read' => $j < ($messageCount - 2) ? true : fake()->boolean(50),
                    'created_at' => $baseTime->copy()->addMinutes($j * rand(10, 120)),
                ]);
            }
        }
    }
}
