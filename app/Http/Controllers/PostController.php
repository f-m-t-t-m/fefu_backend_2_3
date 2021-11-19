<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() : JsonResponse
    {
        $posts = Post::with('user')->ordered()->paginate(2);
        return response()->json(PostResource::collection($posts));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) : JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|max:100',
            'text' => 'required|max:2000'
        ]);

        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            return response()->json(['message' => $messages], 422);
        }

        $validated = $validator->validated();
        $post = new Post();
        $post->title = $validated['title'];
        $post->text = $validated['text'];
        $post->user_id = User::inRandomOrder()->first()->id;
        $post->save();

        return response()->json(new PostResource($post), 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function show(Post $post) : JsonResponse
    {
        return response()->json(new PostResource($post));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Post $post) : JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|max:100',
            'text' => 'sometimes|required|max:2000'
        ]);

        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            return response()->json(['error' => $messages], 422);
        }

        $validated = $validator->validated();
        if (isset($validated['title'])) {
            $post->title = $validated['title'];
        }
        if (isset($validated['text'])) {
            $post->text = $validated['text'];
        }
        $post->save();

        return response()->json(new PostResource($post));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function destroy(Post $post) : JsonResponse
    {
        $post->delete();
        return response()->json(['message' => 'Post removed successfully']);
    }
}
