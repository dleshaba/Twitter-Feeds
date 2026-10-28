<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    // Retrieve all posts.
    public function index()
    {
        $posts = Post::latest()->get();

        return response()->json($posts);
    }

    // Save a new post.
    public function store(Request $request)
    {
        $validated = $request->validate([
            'author_name' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'content' => 'required|string|max:5000',
        ]);

        $post = Post::create([
            'author_name' => $validated['author_name'],
            'username' => $validated['username'],
            'content' => $validated['content'],
            'posted_at' => now(),
        ]);

        return response()->json($post, 201);
    }
}
