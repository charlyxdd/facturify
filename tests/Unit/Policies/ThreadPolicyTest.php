<?php

namespace Tests\Unit\Policies;

use App\Models\Thread;
use App\Models\User;
use App\Policies\ThreadPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThreadPolicyTest extends TestCase
{
    use RefreshDatabase;

    private ThreadPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new ThreadPolicy();
    }

    /** @test */
    public function participant_can_view_thread()
    {
        $user = $this->createUser();
        $thread = $this->createThread([], [$user->id]);

        $this->assertTrue($this->policy->view($user, $thread));
    }

    /** @test */
    public function non_participant_cannot_view_thread()
    {
        $user1 = $this->createUser();
        $user2 = $this->createUser();
        $thread = $this->createThread([], [$user1->id]);

        $this->assertFalse($this->policy->view($user2, $thread));
    }

    /** @test */
    public function creator_can_update_thread()
    {
        $creator = $this->createUser();
        $thread = $this->createThread(['created_by' => $creator->id], [$creator->id]);

        $this->assertTrue($this->policy->update($creator, $thread));
    }

    /** @test */
    public function non_creator_cannot_update_thread()
    {
        $creator = $this->createUser();
        $otherUser = $this->createUser();
        $thread = $this->createThread(['created_by' => $creator->id], [$creator->id, $otherUser->id]);

        $this->assertFalse($this->policy->update($otherUser, $thread));
    }

    /** @test */
    public function creator_can_delete_thread()
    {
        $creator = $this->createUser();
        $thread = $this->createThread(['created_by' => $creator->id], [$creator->id]);

        $this->assertTrue($this->policy->delete($creator, $thread));
    }

    /** @test */
    public function non_creator_cannot_delete_thread()
    {
        $creator = $this->createUser();
        $otherUser = $this->createUser();
        $thread = $this->createThread(['created_by' => $creator->id], [$creator->id, $otherUser->id]);

        $this->assertFalse($this->policy->delete($otherUser, $thread));
    }

    /** @test */
    public function multiple_participants_can_view_same_thread()
    {
        $user1 = $this->createUser();
        $user2 = $this->createUser();
        $user3 = $this->createUser();

        $thread = $this->createThread([], [$user1->id, $user2->id, $user3->id]);

        $this->assertTrue($this->policy->view($user1, $thread));
        $this->assertTrue($this->policy->view($user2, $thread));
        $this->assertTrue($this->policy->view($user3, $thread));
    }
}
