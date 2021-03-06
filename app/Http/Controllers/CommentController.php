<?php

namespace App\Http\Controllers;

use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    public function __construct() {
        $this->middleware('auth:sanctum', ['only' => ['store', 'update', 'destroy']]);
        $this->authorizeResource(Comment::class, 'comment', [
            'except' => ['index', 'show']
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Post $post) : JsonResponse
    {
        $comments = $post->comments()->ordered()->paginate(2);
        return response()->json(CommentResource::collection($comments));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Post $post, Request $request) : JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'text' => 'required|max:150'
        ]);

        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            return response()->json(['error' => $messages], 422);
        }

        $validated = $validator->validated();
        $comment = new Comment();
        $comment->text = $validated['text'];
        $comment->user_id = Auth::user()->id;
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
    public function show(Post $post, Comment $comment) : JsonResponse
    {
        return response()->json(new CommentResource($comment));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function update(Post $post, Request $request, Comment $comment) : JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'text' => 'sometimes|required|max:150'
        ]);

        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            return response()->json(['error' => $messages], 422);
        }

        $validated = $validator->validated();
        $comment->text = $validated['text'];
        $comment->save();

        return response()->json(new CommentResource($comment));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Post $post, Comment $comment) : JsonResponse
    {
        $comment->delete();
        return response()->json(['message' => 'Comment removed successfully']);
    }
}
