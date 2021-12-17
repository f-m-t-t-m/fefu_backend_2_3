<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate');
        $this->artisan('db:seed');
    }

    public function tearDown(): void
    {
        $this->artisan('migrate:reset');
    }

    public function test_index()
    {
        $response = $this->getJson('/api/posts');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => [
                'title',
                'slug',
                'text',
                'comments' => [
                    '*' => [
                        'author' => [
                            'login',
                            'name',
                            'email'
                        ],
                        'text',
                        'created_at'
                    ]
                ],
                'created_at',
                'author' => [
                    'login',
                    'name',
                    'email'
                ]
            ]
        ]);
    }

    public function test_store_no_user() {
        $post = [
            'title' => 'new post',
            'text' => 'this is text of new post'
        ];
        $response = $this->postJson('/api/posts', $post);
        $response->assertStatus(401);
        $response->assertExactJson([
           'message' => "Unauthenticated."
        ]);
    }

    public function test_store_user() {
        $post = [
            'title' => 'new post',
            'text' => 'this is text of new post'
        ];
        $user = User::factory()->make()->first();

        $response = $this->actingAs($user)->postJson('/api/posts', $post);
        $response->assertStatus(201);
        $response->assertJson($post);
    }

    public function test_store_validation() {
        $post = [
            'title' => 'new post',
        ];
        $user = User::factory()->make()->first();
        $response = $this->actingAs($user)->postJson('/api/posts', $post);
        $response->assertStatus(422);
        $response->assertExactJson([
            'message' => [
                'The text field is required.',
            ]
        ]);
    }

    public function test_update_moderator() {
        $post = Post::factory()->create()->first();
        $updated_post = [
            'title' => 'new post title',
        ];
        $user = User::factory()->create()->first();
        $user->role = Role::MODERATOR;

        $response = $this->actingAs($user)->putJson('/api/posts/'.$post->slug, $updated_post);
        $response->assertStatus(200);
        $response->assertJson([
            'title' => 'new post title',
            'text' => $post->text,
        ]);
    }

    public function test_update_author() {
        $user = User::factory()->create()->first();
        $post = Post::factory()->count(1)->for($user)->create()->first();
        $updated_post = [
            'title' => 'new post title',
        ];

        $response = $this->actingAs($user)->putJson('/api/posts/'.$post->slug, $updated_post);
        $response->assertStatus(200);
        $response->assertJson([
            'title' => 'new post title',
            'text' => $post->text,
        ]);
    }

    public function test_update_wrong_role() {
        $users = User::factory(2)->create();
        $wrong_user = $users[0];
        $right_user = $users[1];
        $post = Post::factory()->count(1)->for($right_user)->create()->first();
        $updated_post = [
            'title' => 'new post title',
        ];
        $response = $this->actingAs($wrong_user)->putJson('/api/posts/'.$post->slug, $updated_post);
        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'This action is unauthorized.'
        ]);
    }

    public function test_show_right_slug() {
        $post = Post::all()->first();
        $author = User::query()->where('id', $post->user_id)->first();
        $response = $this->getJson('/api/posts/'.$post->slug);
        $response->assertStatus(200);
        $response->assertJson([
            'title' => $post->title,
            'slug' => $post->slug,
            'text' => $post->text,
            'author' => [
                'login' => $author->login,
                'name' => $author->name,
                'email' => $author->email,
            ]
        ]);
    }

    public function test_show_wrong_slug() {
        $response = $this->getJson('/api/posts/21312e12');
        $response->assertExactJson([
           'message' => 'Post not found'
        ]);
    }

    public function test_delete_moderator() {
        $post = Post::factory(1)->create()->first();
        $user = User::factory(1)->create()->first();
        $user->role = Role::MODERATOR;

        $response = $this->actingAs($user)->deleteJson('/api/posts/'.$post->slug);
        $response->assertExactJson([
           'message' => 'Post removed successfully',
        ]);
    }

    public function test_delete_author() {
        $user = User::factory(1)->create()->first();
        $post = Post::factory(1)->for($user)->create()->first();

        $response = $this->actingAs($user)->deleteJson('/api/posts/'.$post->slug);
        $response->assertExactJson([
            'message' => 'Post removed successfully',
        ]);
    }

    public function test_delete_wrong_role() {
        $post = Post::all()->first();
        $wrong_user = User::factory(1)->create()->first();
        $response = $this->actingAs($wrong_user)->deleteJson('/api/posts/'.$post->slug);
        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'This action is unauthorized.'
        ]);
    }
}
