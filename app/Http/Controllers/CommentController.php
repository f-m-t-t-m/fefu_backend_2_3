<?php

namespace App\Http\Controllers;

use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($post_slug) : JsonResponse
    {
        $post = Post::query()->where('slug', $post_slug)->first();
        $comments = $post->comments()->orderByDesc('created_at')->paginate(2);
        return response()->json(CommentResource::collection($comments));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($post_slug, Request $request) : JsonResponse
    {
        $post = Post::query()->where('slug', $post_slug)->first();

        $validator = Validator::make($request->all(), [
            'text' => 'sometimes|required|max:150'
        ]);

        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['error' => $msg], 400);
        }

        $validated = $validator->validated();
        $comment = new Comment();
        $comment->text = $validated['text'];
        $comment->user_id = User::inRandomOrder()->first()->id;
        $comment->post_id = $post->id;
        $comment->save();

        return response()->json(new CommentResource($comment), 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function show($post_slug, Comment $comment) : JsonResponse
    {
        $post = Post::query()->where('slug', $post_slug)->first();
        if ($post->id !== $comment->post_id) {
            return response()->json(['message' => 'Comment not found'], 404);
        }
        return response()->json(new CommentResource($comment));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function update($post_slug, Request $request, Comment $comment) : JsonResponse
    {
        $post = Post::query()->where('slug', $post_slug)->first();
        if ($post->id !== $comment->post_id) {
            return response()->json(['message' => 'Comment not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'text' => 'sometimes|required|max:150'
        ]);

        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['error' => $msg], 400);
        }

        $validated = $validator->validated();
        $comment->text = $validated['title'];
        $comment->save();

        return response()->json(new CommentResource($comment));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function destroy($post_slug, Comment $comment) : JsonResponse
    {
        $post = Post::query()->where('slug', $post_slug)->first();
        if ($post->id !== $comment->post_id) {
            return response()->json(['message' => 'Comment not found'], 404);
        }
        $comment->delete();
        return response()->json(['message' => 'Comment removed successfully']);
    }
}
