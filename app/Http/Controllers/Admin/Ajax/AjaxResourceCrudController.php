<?php

namespace App\Http\Controllers\Admin\Ajax;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Lesson;
use App\Models\Resource;
class AjaxResourceCrudController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $search = $request->get('search');
            $lesson_id = $request->get('lesson');
            $perPage = 10;
            $page = $request->get('page', 1);
            $query  = Resource::with('lesson')
                ->when($search, function ($query, $search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('url', 'like', "%{$search}%")
                            ->orWhereHas('lesson', fn($l) => $l->where('title', 'like', "%{$search}%"));
                    });
                })
                ->when($lesson_id, function ($query) use ($lesson_id) {
                    if ($lesson_id) {
                        $query->where('lesson_id', $lesson_id);
                    }
                })
                ->orderBy('id', 'desc');

            $resources = $query->paginate($perPage, ['*'], 'page', $page);
            $lessons = Lesson::select('id', 'title')->get();

            return response()->json([
                'lessons' => $lessons,
                'pagination' => [
                    'current_page' => $resources->currentPage(),
                    'last_page' => $resources->lastPage(),
                    'total' => $resources->total(),
                ],
                'resources' =>  $resources->items(),
            ]);
        };
        return view('admin.ajax.resource.index');
    }
    public function store(Request $request)
    {
        $request->validate(([
            'name' => 'required|string',
            'url' => 'required|string',
            'lesson_id' => 'required|exists:lessons,id',
        ]));

        $resource = Resource::create($request->only('name', 'url', 'lesson_id'));
        return response()->json(['success' => true, 'resource' => $resource]);
    }
    public function update(Request $request, $id)
    {
        $request->validate(([
            'name' => 'required|string',
            'url' => 'required|string',
            'lesson_id' => 'required|exists:lessons,id',
        ]));
        $resource = Resource::findOrFail($id);
        $resource->update($request->only('name', 'url', 'lesson_id'));
        return response()->json(['success' => true, '$resource' => $resource]);
    }
    public function destroy($id)
    {
        $resource = Resource::findOrFail($id);
        $resource->delete();
        return response()->json(['success' => true]);
    }
}
