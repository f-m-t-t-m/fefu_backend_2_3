<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class CommentTest extends TestCase
{
    public function setUp() : void
    {
        parent::setUp();

        $this->artisan('migrate');
        $this->artisan('db:seed');
    }

    public function tearDown() : void
    {
        $this->artisan('migrate:reset');
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_index() {
        $post = Post::factory(1)->has(Comment::factory(5))->create()->first();
        $response = $this->getJson("/api/posts/{$post->slug}/comments");
        $response->assertJsonStructure([
           '*' => [
               'author' => [
                   'login',
                   'name',
                   'email'
               ],
               'text',
               'created_at'
           ]
        ]);
    }

    public function test_index_wrong_slug() {
        $response = $this->getJson("/api/posts/wqewqe1233/comments");
        $response->assertStatus(404);
    }

    public function test_store_user() {
        $post = Post::factory(1)->create()->first();
        $user = User::factory()->make()->first();
        $comment = [
          'text' => 'new comment'
        ];

        $response = $this->actingAs($user)->postJson("/api/posts/{$post->slug}/comments", $comment);
        $response->assertStatus(201);
        $response->assertJson($comment);
    }

    public function test_store_no_user() {
        $post = Post::factory(1)->create()->first();
        $comment = [
          'text' => 'new comment'
        ];
        $response = $this->postJson("/api/posts/{$post->slug}/comments", $comment);
        $response->assertStatus(401);
        $response->assertExactJson([
           'message' => "Unauthenticated."
        ]);
    }

    public function test_store_validation() {
        $post = Post::factory(1)->create()->first();
        $user = User::factory()->make()->first();
        $comment = [];

        $response = $this->actingAs($user)->postJson("/api/posts/{$post->slug}/comments", $comment);
        $response->assertStatus(422);
        $response->assertExactJson([
            'message' => [
                'The text field is required.',
            ]
        ]);
    }
    public function test_update_moderator() {
        $post = Post::factory(1)->has(Comment::factory(1))->create()->first();
        $comment = $post->comments()->first();
        $updated_comment = [
            'text' => 'updated comment',
        ];
        $user = User::factory()->create()->first();
        $user->role = Role::MODERATOR;

        $response = $this->actingAs($user)->putJson("/api/posts/{$post->slug}/comments/{$comment->id}", $updated_comment);
        $response->assertStatus(200);
        $response->assertJson([
            'text' => 'updated comment',
        ]);
    }
    public function test_update_author() {
        $user = User::factory()->create()->first();
        $post = Post::factory()->count(1)->has(Comment::factory(1)->for($user))->create()->first();
        $comment = $post->comments()->first();
        $updated_comment = [
            'text' => 'updated comment',
        ];

        $response = $this->actingAs($user)->putJson("/api/posts/{$post->slug}/comments/{$comment->id}", $updated_comment);
        $response->assertStatus(200);
        $response->assertJson([
            'text' => 'updated comment',
        ]);
    }
    public function test_update_wrong_role() {
        $users = User::factory(2)->create();
        $wrong_user = $users[0];
        $right_user = $users[1];
        $post = Post::factory()->count(1)->has(Comment::factory(1)
            ->for($right_user))->create()->first();
        $comment = $post->comments()->first();
        $updated_comment = [
            'text' => 'updated comment',
        ];
        $response = $this->actingAs($wrong_user)
            ->putJson("/api/posts/{$post->slug}/comments/{$comment->id}", $updated_comment);
        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'This action is unauthorized.'
        ]);
    }
    public function test_show_right_id() {
        $post = Post::factory()->count(1)->has(Comment::factory(1))->create()->first();
        $comment = $post->comments()->first();
        $author = User::query()->where('id', $comment->user_id)->first();

        $response = $this->getJson("/api/posts/{$post->slug}/comments/{$comment->id}");
        $response->assertStatus(200);
        $response->assertJson([
            'author' => [
                'login' => $author->login,
                'name' => $author->name,
                'email' => $author->email,
            ],
            'text' => $comment->text,
        ]);
    }
    public function test_show_wrong_id() {
        $post = Post::factory(1)->create()->first();
        $response = $this->getJson("/api/posts/{$post->slug}/comments/100");
        $response->assertExactJson([
           'message' => 'Comment not found'
        ]);
    }
    public function test_delete_moderator() {
        $post = Post::factory(1)->has(Comment::factory(1))->create()->first();
        $user = User::factory(1)->create()->first();
        $user->role = Role::MODERATOR;
        $comment = $post->comments()->first();

        $response = $this->actingAs($user)->deleteJson("/api/posts/{$post->slug}/comments/{$comment->id}");
        $response->assertExactJson([
           'message' => 'Comment removed successfully',
        ]);
    }
    public function test_delete_wrong_role() {
        $post = Post::factory(1)->has(Comment::factory(1))->create()->first();
        $comment = $post->comments()->first();
        $wrong_user = User::factory(1)->create()->first();
        $response = $this->actingAs($wrong_user)->deleteJson("/api/posts/{$post->slug}/comments/{$comment->id}");
        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'This action is unauthorized.'
        ]);
    }
}
