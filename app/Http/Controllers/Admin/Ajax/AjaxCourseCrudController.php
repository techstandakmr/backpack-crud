<?php

namespace App\Http\Controllers\Admin\Ajax;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use App\Models\User;

class AjaxCourseCrudController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $search = $request->get('search');
            $authorId = $request->get('author');

            $courses = Course::with(['author', 'lessons'])
                ->when($search, function ($query, $search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('title', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%")
                            ->orWhereHas('author', fn($a) => $a->where('name', 'like', "%{$search}%"))
                            ->orWhereHas('lessons', fn($l) => $l->where('title', 'like', "%{$search}%"));
                    });
                })
                ->when($authorId, function ($query) use ($authorId) {
                    if ($authorId) {
                        $query->where('author_id', $authorId);
                    }
                })
                ->orderBy('id', 'desc')
                ->get();

            $authors = User::where('role', 'teacher')->get(['id', 'name']);

            return response()->json([
                'courses' => $courses,
                'authors' => $authors,
            ]);
        };
        return view('admin.ajax.courses.index');
    }
    public function store(Request $request)
    {
        $request->validate(([
            'title' => 'required|string',
            'description' => 'required|string',
            'author_id' => 'required|exists:users,id',
        ]));

        $course = Course::create($request->only('title', 'description', 'author_id'));
        return response()->json(['success' => true, 'course' => $course]);
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'author_id' => 'required|exists:users,id'
        ]);
        $course = Course::findOrFail($id);
        $course->update($request->only('title', 'description', 'author_id'));
        return response()->json(['success' => true, 'course' => $course]);
    }
    public function destroy($id)
    {
        $course = Course::findOrFail($id);
        // delete realted enrollments as well
        $course->enrollments()->delete();
        $course->delete();
        return response()->json(['success' => true]);
    }
}
