<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Post;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PostController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $posts = Post::published()->paginate(20);

        return response()->json($posts, 200);
    }

    public function store(StorePostRequest $request)
    {
        $data = $request->validated();
        $post = Post::create($data);

        return response()->json($post, 201);
    }

    public function show(Post $post)
    {
        if (! $post->isPublished()) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        return response()->json($post, 200);
    }

    public function update(UpdatePostRequest $request, Post $post)
    {
        $this->authorize('update', $post);
        $data = $request->validated();
        $post->update($data);

        return response()->json($post, 200);
    }

    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);
        $post->delete();

        return response()->json(['message' => 'Post deleted successfully'], 200);
    }
}
