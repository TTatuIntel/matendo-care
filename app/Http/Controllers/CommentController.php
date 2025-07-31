<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function create()
    {
        return view('comments.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'commentable_type' => 'required|string',
            'commentable_id' => 'required|integer',
            'comment' => 'required|string',
        ]);

        $validated['user_id'] = auth()->id();

        Comment::create($validated);

        return back()->with('success', 'Comment added.');
    }

    public function edit(Comment $comment)
    {
        return view('comments.edit', compact('comment'));
    }

    public function update(Request $request, Comment $comment)
    {
        $validated = $request->validate([
            'comment' => 'required|string',
        ]);

        $comment->update($validated);

        return back()->with('success', 'Comment updated.');
    }

    public function destroy(Comment $comment)
    {
        $comment->delete();
        return back()->with('success', 'Comment deleted.');
    }

    public function doctorMessages()
    {
        $doctor = auth()->user()->doctor;
        $comments = Comment::where('user_id', $doctor->user_id)->latest()->paginate(20);
        return view('doctor.messages.index', compact('comments'));
    }
}
