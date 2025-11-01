<?php

namespace App\Http\Controllers\Admin\Ajax;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Lesson;
use App\Models\Resource;
use App\Models\Enrollment;

class AjaxEnrollmentCrudController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $search = $request->get('search');
            $course_id = $request->get('course');
            $user_id = $request->get('user');
            $perPage = 10;
            $page = $request->get('page', 1);
            $query  = Enrollment::with('course', 'user')
                ->when($search, function ($query, $search) {
                    $query->where(function ($q) use ($search) {
                        $q->whereHas('course', fn($l) => $l->where('title', 'like', "%{$search}%"))
                            ->orWhereHas('user', fn($l) => $l->where('name', 'like', "%{$search}%"));
                    });
                })->when($course_id, function ($query) use ($course_id) {
                    if ($course_id) {
                        $query->where('course_id', $course_id);
                    }
                })->when($user_id, function ($query) use ($user_id) {
                    if ($user_id) {
                        $query->where('user_id', $user_id);
                    }
                })
                ->orderBy('id', 'desc');

            $enrollments = $query->paginate($perPage, ['*'], 'page', $page);
            $users = User::select('id', 'name')->get();
            $courses = Course::select('id', 'title')->get();
            return response()->json([
                'users' => $users,
                'courses' => $courses,
                'pagination' => [
                    'current_page' => $enrollments->currentPage(),
                    'last_page' => $enrollments->lastPage(),
                    'total' => $enrollments->total(),
                ],
                'enrollments' =>  $enrollments->items(),
            ]);
        };
        return view('admin.ajax.enrollment.index');
    }
    public function store(Request $request)
    {
        $request->validate(([
            'course_id' => 'required|exists:courses,id',
            'user_id' => 'required|exists:users,id',
        ]));

        $enrollment = Enrollment::create($request->only('course_id', 'user_id'));
        return response()->json(['success' => true, 'enrollment' => $enrollment]);
    }
    public function update(Request $request, $id)
    {
        $request->validate(([
            'course_id' => 'required|exists:courses,id',
            'user_id' => 'required|exists:users,id',
        ]));
        $enrollment = Enrollment::findOrFail($id);
        $enrollment->update($request->only('course_id', 'user_id'));
        return response()->json(['success' => true, '$enrollment' => $enrollment]);
    }
    public function destroy($id)
    {
        $enrollment = Enrollment::findOrFail($id);
        $enrollment->delete();
        return response()->json(['success' => true]);
    }
}
