<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $posts = Post::all();
        return response()->json($posts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'title' => 'required',
            'content' => 'required',
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validator->errors()
            ], 401);
        }

        $post = Post::create([
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'user_id' => auth()->user()->id,
        ]);

        return response()->json($post, 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $post = Post::findOrFail($id);
        if ($post->user_id !== auth()->user()->id) {
            return response()->json([
                'status' => false,
                'error' => 'Unauthorized',
                'message' => 'This post is not for you. Please',
            ], 403);
        }

        return response()->json($post);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(),[
            'title' => 'required',
            'content' => 'required',
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validator->errors()
            ], 401);
        }

        $post = Post::findOrFail($id);
        if ($post->user_id !== auth()->user()->id) {
            return response()->json([
                'status' => false,
                'error' => 'Unauthorized',
                'message' => 'This post is not for you. Please',
            ], 403);
        }
        $post->title = $request->input('title');
        $post->content = $request->input('content');
        $post->save();

        return response()->json($post);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $post = Post::findOrFail($id);
        // Check if the authenticated user is the owner of the post
        if ($post->user_id !== auth()->user()->id) {
            return response()->json([
                'status' => false,
                'error' => 'Unauthorized',
                'message' => 'This post is not for you. Please',
            ], 403);
        }

        $post->delete();

        return response()->json([
            'status' => true,
            'message' => 'Your post has been deleted',
        ], 200);
    }
}
