<?php

namespace App\Http\Controllers\Admin\Ajax;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Lesson;

class AjaxLessonCrudController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $search = $request->get('search');
            $course_id = $request->get('course');
            $perPage = 10;
            $page = $request->get('page', 1);
            $query  = Lesson::with('course')
                ->when($search, function ($query, $search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('title', 'like', "%{$search}%")
                            ->orWhere('content', 'like', "%{$search}%")
                            ->orWhereHas('course', fn($l) => $l->where('title', 'like', "%{$search}%"));
                    });
                })
                ->when($course_id, function ($query) use ($course_id) {
                    if ($course_id) {
                        $query->where('course_id', $course_id);
                    }
                })
                ->orderBy('id', 'desc');

            $lessons = $query->paginate($perPage, ['*'], 'page', $page);
            $courses = Course::select('id', 'title')->get();

            return response()->json([
                'courses' => $courses,
                'pagination' => [
                    'current_page' => $lessons->currentPage(),
                    'last_page' => $lessons->lastPage(),
                    'total' => $lessons->total(),
                ],
                'lessons' =>  $lessons->items(),
            ]);
        };
        return view('admin.ajax.lesson.index');
    }
    public function store(Request $request)
    {
        $request->validate(([
            'title' => 'required|string',
            'content' => 'required|string',
            'course_id' => 'required|exists:courses,id',
        ]));

        $lesson = Lesson::create($request->only('title', 'content', 'course_id'));
        return response()->json(['success' => true, 'lesson' => $lesson]);
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string',
            'content' => 'required|string',
            'course_id' => 'required|exists:courses,id',
        ]);
        $lesson = Lesson::findOrFail($id);
        $lesson->update($request->only('title', 'content', 'course_id'));
        return response()->json(['success' => true, 'lesson' => $lesson]);
    }
    public function destroy($id)
    {
        $lesson = Lesson::findOrFail($id);
        $lesson->delete();
        return response()->json(['success' => true]);
    }
}
