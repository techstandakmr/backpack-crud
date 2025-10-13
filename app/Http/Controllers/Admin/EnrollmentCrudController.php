<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\EnrollmentRequest;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Http\Request;

/**
 * Class EnrollmentCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class EnrollmentCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Enrollment::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/enrollment');
        CRUD::setEntityNameStrings('enrollment', 'enrollments');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        // Student Name
        CRUD::addColumn([
            'label'     => 'Student Name',
            'type' => 'text',
            'name' => 'user.name',
            'wrapper' => [
                'href' => function ($crud, $column, $entry, $related_key) {
                    return backpack_url('user/' . $related_key . '/show');
                },
            ],
        ]);

        // Student Email
        CRUD::addColumn([
            'label'     => 'Student Email',
            'type' => 'text',
            'name' => 'user.email',
        ]);

        // Student Phone
        CRUD::addColumn([
            'label'     => 'Student Phone',
            'type' => 'text',
            'name' => 'user.phone',
        ]);

        // Course Title
        CRUD::addColumn([
            'name'      => 'course',
            'label'     => 'Course',
            'type'      => 'relationship',
            'attribute' => 'title',
            'wrapper' => [
                'href' => function ($crud, $column, $entry, $related_key) {
                    return backpack_url('course/' . $related_key . '/show');
                },
            ],
        ]);
        CRUD::addFilter(
            [
                'name' => 'course_id',
                'type' => 'select2',
                'label' => 'Filter by Course',
            ],
            function () {
                return Course::all()->pluck('title', 'id')->toArray();
            },
            function ($value) {
                CRUD::addClause('where', 'course_id', $value);
            }
        );

        // Filter by student
        CRUD::addFilter(
            [
                'name' => 'user_id',
                'type' => 'select2',
                'label' => 'Filter by Student',
            ],
            function () {
                return User::all()->pluck('name', 'id')->toArray();
            },
            function ($value) {
                CRUD::addClause('where', 'user_id', $value);
            }
        );
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(EnrollmentRequest::class);
        CRUD::addField([
            'name' => 'user_id',
            'label' => 'Student',
            'type' => 'select',
            'entity' => 'user',
            'attribute' => 'name',
            'model' => "App\Models\User",
            'options' => (function ($query) {
                return $query->where('role', 'student')->get();
            }),
        ]);
        //  Course (dropdown select)
        CRUD::addField([
            'name'      => 'course_id',                // column in enrollments table
            'label'     => 'Course',
            'type'      => 'select',
            'entity'    => 'course',                   // relationship method in Enrollment model
            'model'     => \App\Models\Course::class,  // related model
            'attribute' => 'title',                    // field to show in dropdown
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
    protected function setupShowOperation()
    {
        CRUD::setFromDb(); // keep all default fields
        // display the timestamps
        CRUD::addColumn([
            'name'  => 'created_at',
            'label' => 'Created At',
            'type'  => 'datetime',
        ]);
        CRUD::addColumn([
            'name'  => 'updated_at',
            'label' => 'Updated At',
            'type'  => 'datetime',
        ]);
        // Override lesson_id to show related lesson title
        CRUD::modifyColumn('course_id', [
            'type'      => 'select',
            'entity'    => 'course',
            'attribute' => 'title',      // field from related Lesson model
            'label'     => 'Course',     // optional: nicer label
        ]);
    }
    public function destroy($id)
    {
        $this->crud->hasAccessOrFail('delete');

        $item = $this->crud->getEntry($id);
        $item->delete();

        return redirect()->back()->with('success', 'Enrollment deleted successfully.');
    }


    public function customView(Request $request)
    {
        $query = Enrollment::with(['course', 'user']); // include user relationship

        // Filter by student name
        if ($request->filled('student_name')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->student_name . '%');
            });
        }

        // Filter by student email
        if ($request->filled('student_email')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('email', 'like', '%' . $request->student_email . '%');
            });
        }

        // Filter by course title
        if ($request->filled('course_title')) {
            $query->whereHas('course', function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->course_title . '%');
            });
        }

        // Filter by phone (from user table)
        if ($request->filled('phone')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('phone', 'like', '%' . $request->phone . '%');
            });
        }

        $enrollments = $query->paginate(10)->appends($request->all());

        return view('admin.enrollments.custom_view', compact('enrollments'));
    }
}
