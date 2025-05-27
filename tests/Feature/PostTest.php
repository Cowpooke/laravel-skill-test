<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    protected User $user;

    protected Post $post;

    // Setup the data
    protected function setUp(): void
    {
        parent::setUp();

        // create user data with post
        $this->user = User::factory()->create();

        $this->post = Post::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Post Title',
            'content' => 'Content for the post',
            'is_draft' => false,
            'published_at' => now(),
        ]);
    }

    // 1. Test anyone can view all post
    public function test_index_returns_posts()
    {
        $response = $this->getJson('/posts');

        $response->assertStatus(200);
        $response->assertJsonFragment(['id' => $this->post->id]);
    }

    // 2. Test show a post
    public function test_show_returns_post()
    {
        $response = $this->getJson("/posts/{$this->post->id}");

        $response->assertStatus(200);
        $response->assertJsonFragment(['id' => $this->post->id]);
    }

    // 3. Test draft post
    public function test_show_returns_404_for_draft_or_unpublished_post()
    {
        $draftPost = Post::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Post Title',
            'content' => 'Content for the post',
            'is_draft' => true,
            'published_at' => null,
        ]);

        $response = $this->getJson("/posts/{$draftPost->id}");

        $response->assertStatus(404);
    }

    // 4. Test non-existent post that returns 404
    public function test_show_returns_404_for_non_existent_post()
    {
        $response = $this->getJson('/posts/999999'); // Non-existing ID

        $response->assertStatus(404);
    }

    // 5. Test authenticated user can create a post
    public function test_authenticated_user_can_create_post()
    {
        $this->actingAs($this->user);

        $postData = [
            'user_id' => $this->user->id,
            'title' => 'Post Title',
            'content' => 'Content for the post',
            'published_at' => now(),
        ];

        $response = $this->postJson('/posts', $postData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('posts', ['title' => 'Post Title']);
    }

    // 6. Test guest (unauthenticated user) cannot create post
    public function test_guest_cannot_create_post()
    {
        $postData = [
            'title' => 'New Post Title',
            'content' => 'Content for the new post',
        ];

        $response = $this->postJson('/posts', $postData);

        $response->assertStatus(401); // Unauthorized
    }

    // 7. Test validation errors when missing fields on create
    public function test_create_post_validation_errors()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/posts', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['title', 'content']);
    }

    // 8. Test owner can update their post
    public function test_owner_can_update_post()
    {
        $this->actingAs($this->user);

        $updateData = [
            'title' => 'Updated Title',
            'content' => 'Updated content',
        ];

        $response = $this->putJson("/posts/{$this->post->id}", $updateData);

        $response->assertStatus(200); // OK
        $this->assertDatabaseHas('posts', ['id' => $this->post->id, 'title' => 'Updated Title']);
    }

    // 9. Test non-owner cannot update post
    public function test_non_owner_cannot_update_post()
    {
        $otherUser = User::factory()->create();
        $this->actingAs($otherUser);

        $updateData = [
            'title' => 'New Title',
            'content' => 'New content',
        ];

        $response = $this->putJson("/posts/{$this->post->id}", $updateData);

        $response->assertStatus(403);
    }

    // 10. Test guest cannot update post
    public function test_guest_cannot_update_post()
    {
        $updateData = [
            'title' => 'New Title',
            'content' => 'New Title',
        ];

        $response = $this->putJson("/posts/{$this->post->id}", $updateData);

        $response->assertStatus(401);
    }

    // 11. Test validation errors on update missing fields
    public function test_update_post_validation_errors()
    {
        $this->actingAs($this->user);

        $response = $this->putJson("/posts/{$this->post->id}", []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['title', 'content']);
    }

    // 12. Test owner can delete post
    public function test_owner_can_delete_post()
    {
        $this->actingAs($this->user);

        $response = $this->deleteJson("/posts/{$this->post->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('posts', ['id' => $this->post->id]);
    }

    // 13. Test non-owner cannot delete post
    public function test_non_owner_cannot_delete_post()
    {
        $otherUser = User::factory()->create();
        $this->actingAs($otherUser);

        $response = $this->deleteJson("/posts/{$this->post->id}");

        $response->assertStatus(403);
    }

    // 14. Test guest cannot delete post
    public function test_guest_cannot_delete_post()
    {
        $response = $this->deleteJson("/posts/{$this->post->id}");

        $response->assertStatus(401);
    }
}
