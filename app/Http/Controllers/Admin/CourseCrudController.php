<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\CourseRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Class CourseCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class CourseCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Course::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/course');
        CRUD::setEntityNameStrings('course', 'courses');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::setFromDb(); // set columns from db columns.
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(CourseRequest::class);

        // Title Field
        CRUD::field('title')
            ->type('text')
            ->label('Course Title')
            ->attributes(['placeholder' => 'Enter course title']);

        // Description Field
        CRUD::field('description')
            ->type('textarea')
            ->label('Description')
            ->attributes(['placeholder' => 'Enter course description', 'rows' => 4]);

        // Author (User) Selection with Create Button
        CRUD::addField([
            'name' => 'author_id',
            'label' => 'Author',
            'type' => 'select',
            'entity' => 'author',
            'attribute' => 'name',
            'model' => "App\Models\User"
        ]);
    }
    /**
     * Define what happens when the Update operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
    public function destroy($id)
    {
        $course = Course::findOrFail($id);
        $course->lessons()->delete();
        $course->enrollments()->delete();
        $course->delete();

        return redirect()->back()->with('success', 'Course deleted successfully.');
    }
    protected function setupShowOperation()
    {
        CRUD::column('title');
        CRUD::column('description');
        CRUD::column('created_at');

        CRUD::addColumn([
            'name' => 'author',
            'label' => 'Author',
            'type' => 'relationship',
            'attribute' => 'name',
            'wrapper' => [
                'href' => function ($crud, $column, $entry, $related_key) {
                    return backpack_url("user/{$related_key}/show");
                },
            ],
        ]);
        // CRUD::addColumn([
        //     'name' => 'lessons_table',
        //     'label' => 'Lessons',
        //     'type' => 'view',
        //     'view' => 'admin.courses.lessons_table',
        //     'escaped' => false,
        // ]);

        // CRUD::addColumn([
        //     'name' => 'enrollments_table',
        //     'label' => 'Enrollments',
        //     'type' => 'view',
        //     'view' => 'admin.courses.enrollments_table',
        //     'escaped' => false,
        // ]);


        // // Display Lessons Table with search
        // CRUD::addColumn([
        //     'name' => 'lessons_table',
        //     'label' => 'Lessons',
        //     'type' => 'closure',
        //     'function' => function ($entry) {
        //         $lessonSearch = request()->get('lesson_search');
        //         $enrollSearch = request()->get('enroll_search');
        //         $lessonPage = request()->get('lesson_page');
        //         $enrollPage = request()->get('enroll_page');

        //         $lessonsQuery = $entry->lessons();
        //         if ($lessonSearch) {
        //             $lessonsQuery->where(function ($query) use ($lessonSearch) {
        //                 $query->where('title', 'like', "%{$lessonSearch}%")
        //                     ->orWhere('content', 'like', "%{$lessonSearch}%")
        //                     ->orWhereHas('course', function ($q) use ($lessonSearch) {
        //                         $q->where('title', 'like', "%{$lessonSearch}%");
        //                     });
        //             });
        //         }

        //         $lessons = $lessonsQuery->paginate(10, ['*'], 'lesson_page')->appends(request()->query());


        //         // 🔍 Search Form (with persistent keys)
        //         $html = '<div class="mb-2">';
        //         $html .= '<form method="GET" class="form-inline">';

        //         // Input field
        //         $html .= '<input type="text" name="lesson_search" value="' . e($lessonSearch) . '" class="p-2 form-control form-control-sm mb-2" placeholder="Search Lessons">';

        //         // ✅ Preserve other search/page params
        //         if ($enrollSearch) $html .= '<input type="hidden" name="enroll_search" value="' . e($enrollSearch) . '">';
        //         if ($lessonPage) $html .= '<input type="hidden" name="lesson_page" value="' . e($lessonPage) . '">';
        //         if ($enrollPage) $html .= '<input type="hidden" name="enroll_page" value="' . e($enrollPage) . '">';

        //         $html .= '<button class="btn btn-sm btn-primary mr-2">Search</button>';

        //         // ✅ Reset only lesson search
        //         $keepForLessonReset = request()->except(['lesson_search', 'lesson_page']);
        //         $lessonResetQuery = count($keepForLessonReset) ? '?' . http_build_query($keepForLessonReset) : '';
        //         if ($lessonSearch || $lessonPage) {
        //             $html .= '<a href="' . url()->current() . $lessonResetQuery . '" class="btn btn-sm btn-secondary">Reset Lessons</a>';
        //         }

        //         $html .= '</form>';
        //         $html .= '</div>';

        //         if ($lessons->isEmpty()) {
        //             $html .= '<em class="text-muted">No lessons found for your search.</em>';
        //             return $html;
        //         }

        //         // ✅ Table HTML
        //         $html .= '<div class="table-responsive">';
        //         $html .= '<table class="table table-sm table-bordered table-striped mb-0">';
        //         $html .= '<thead><tr>
        //     <th>Sr.</th>
        //     <th>Title</th>
        //     <th>Content</th>
        //     <th>Course</th>
        //     <th>Created</th>
        // </tr></thead><tbody>';

        //         $serial = ($lessons->currentPage() - 1) * $lessons->perPage() + 1;
        //         foreach ($lessons as $lesson) {
        //             $lesson_url = backpack_url("lesson/{$lesson->id}/show");
        //             $course_url = backpack_url("course/{$lesson->course_id}/show");
        //             $content = $lessonSearch ? $lesson->content : Str::limit($lesson->content, 50);

        //             $html .= '<tr>';
        //             $html .= '<td><a href="' . $lesson_url . '">' . $serial++ . '</a></td>';
        //             $html .= '<td>' . e($lesson->title) . '</td>';
        //             $html .= '<td>' . e($content) . '</td>';
        //             $html .= '<td><a href="' . $course_url . '">' . e($lesson->course->title) . '</a></td>';
        //             $html .= '<td>' . $lesson->created_at->format('M d, Y') . '</td>';
        //             $html .= '</tr>';
        //         }
        //         $html .= '</tbody></table></div>';

        //         $html .= '<div class="mt-3 d-flex justify-content-center">';
        //         $html .= $lessons->links('pagination::bootstrap-4')->render();
        //         $html .= '</div>';

        //         return $html;
        //     },
        //     'escaped' => false,
        // ]);

        // // Display Enrollments Table with search
        // CRUD::addColumn([
        //     'name' => 'enrollments_table',
        //     'label' => 'Enrollments',
        //     'type' => 'closure',
        //     'function' => function ($entry) {
        //         $lessonSearch = request()->get('lesson_search');
        //         $enrollSearch = request()->get('enroll_search');
        //         $lessonPage = request()->get('lesson_page');
        //         $enrollPage = request()->get('enroll_page');

        //         $enrollmentsQuery = $entry->enrollments();
        //         if ($enrollSearch) {
        //             $enrollmentsQuery->where(function ($query) use ($enrollSearch) {
        //                 $query->where('student_name', 'like', "%{$enrollSearch}%")
        //                     ->orWhere('student_email', 'like', "%{$enrollSearch}%")
        //                     ->orWhere('phone', 'like', "%{$enrollSearch}%")
        //                     ->orWhereHas('course', function ($q) use ($enrollSearch) {
        //                         $q->where('title', 'like', "%{$enrollSearch}%");
        //                     });
        //             });
        //         }

        //         $enrollments = $enrollmentsQuery->paginate(10, ['*'], 'enroll_page')->appends(request()->query());


        //         // 🔍 Search Form (persistent keys)
        //         $html = '<div class="mb-2">';
        //         $html .= '<form method="GET" class="form-inline">';
        //         $html .= '<input type="text" name="enroll_search" value="' . e($enrollSearch) . '" class="form-control form-control-sm p-2 mb-2" placeholder="Search Enrollments">';

        //         if ($lessonSearch) $html .= '<input type="hidden" name="lesson_search" value="' . e($lessonSearch) . '">';
        //         if ($lessonPage) $html .= '<input type="hidden" name="lesson_page" value="' . e($lessonPage) . '">';
        //         if ($enrollPage) $html .= '<input type="hidden" name="enroll_page" value="' . e($enrollPage) . '">';

        //         $html .= '<button class="btn btn-sm btn-primary mr-2">Search</button>';

        //         $keepForEnrollReset = request()->except(['enroll_search', 'enroll_page']);
        //         $enrollResetQuery = count($keepForEnrollReset) ? '?' . http_build_query($keepForEnrollReset) : '';
        //         if ($enrollSearch || $enrollPage) {
        //             $html .= '<a href="' . url()->current() . $enrollResetQuery . '" class="btn btn-sm btn-secondary">Reset Enrollments</a>';
        //         }

        //         $html .= '</form>';
        //         $html .= '</div>';

        //         if ($enrollments->isEmpty()) {
        //             $html .= '<em class="text-muted">No enrollments found for your search.</em>';
        //             return $html;
        //         }

        //         // ✅ Table HTML
        //         $html .= '<div class="table-responsive">';
        //         $html .= '<table class="table table-sm table-bordered table-striped mb-0">';
        //         $html .= '<thead class="thead-light"><tr>';
        //         $html .= '<th>Sr.</th>';
        //         $html .= '<th>Course Title</th>';
        //         $html .= '<th>Student Name</th>';
        //         $html .= '<th>Student Email</th>';
        //         $html .= '<th>Phone</th>';
        //         $html .= '<th>Created At</th>';
        //         $html .= '<th>Updated At</th>';
        //         $html .= '</tr></thead><tbody>';

        //         $serial = ($enrollments->currentPage() - 1) * $enrollments->perPage() + 1;
        //         foreach ($enrollments as $enrollment) {
        //             $enrollmentUrl = backpack_url("enrollment/{$enrollment->id}/show");
        //             $courseUrl = backpack_url("course/{$enrollment->course_id}/show");

        //             $html .= '<tr>';
        //             $html .= '<td><a href="' . $enrollmentUrl . '">' . $serial++ . '</a></td>';
        //             $html .= '<td><a href="' . $courseUrl . '">' . e($enrollment->course->title) . '</a></td>';
        //             $html .= '<td>' . e($enrollment->student_name) . '</td>';
        //             $html .= '<td>' . e($enrollment->student_email) . '</td>';
        //             $html .= '<td>' . e($enrollment->phone ?? 'N/A') . '</td>';
        //             $html .= '<td>' . $enrollment->created_at->format('M d, Y H:i') . '</td>';
        //             $html .= '<td>' . $enrollment->updated_at->format('M d, Y H:i') . '</td>';
        //             $html .= '</tr>';
        //         }
        //         $html .= '</tbody></table></div>';

        //         $html .= '<div class="mt-3 d-flex justify-content-center">';
        //         $html .= $enrollments->links('pagination::bootstrap-4')->render();
        //         $html .= '</div>';

        //         return $html;
        //     },
        //     'escaped' => false,
        // ]);
    }



    public function customView(Request $request)
    {
        $query = Course::with(['lessons', 'enrollments', 'author']);

        // Filter by course title
        if ($request->filled('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }

        // Filter by description
        if ($request->filled('description')) {
            $query->where('description', 'like', '%' . $request->description . '%');
        }

        // Filter by author name (relationship)
        if ($request->filled('author_name')) {
            $query->whereHas('author', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->author_name . '%');
            });
        }

        // Filter by lesson title (relationship)
        if ($request->filled('lesson_title')) {
            $query->whereHas('lessons', function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->lesson_title . '%');
            });
        }

        $courses = $query->get();

        return view('admin.courses.custom_view', compact('courses'));
    }
}
