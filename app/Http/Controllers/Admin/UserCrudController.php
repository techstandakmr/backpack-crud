<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\UserRequest;
use App\Models\User;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Http\Request;
use Backpack\CRUD\app\Library\Widget;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Response;
use App\Exports\UserExport;

/**
 * Class UserCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class UserCrudController extends CrudController
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
        CRUD::setModel(\App\Models\User::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/user');
        CRUD::setEntityNameStrings('user', 'users');
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
        // add filters by role
        CRUD::addFilter(
            [
                'name' => 'role',
                'type' => 'select2',
                'label' => 'Role',
            ],
            function () {
                // Return the options for the dropdown
                return [
                    'student' => 'Student',
                    'teacher' => 'Teacher',
                    'admin' => 'Admin',
                ];
            },
            function ($value) {
                // Apply the filter
                CRUD::addClause('where', 'role', $value);
            }
        );
        /**
         * Columns can be defined using the fluent syntax:
         * - CRUD::column('price')->type('number');
         */
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(UserRequest::class);

        CRUD::setFromDb(); // keep the DB fields

        // Add Role field manually
        CRUD::addField([
            'name' => 'role',
            'label' => 'Role',
            'type' => 'select_from_array',
            'options' => [
                'student' => 'Student',
                'teacher' => 'Teacher',
                'admin' => 'Admin',
            ],
            'allows_null' => false,
            'default' => 'student',
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
        $this->setupListOperation();
        CRUD::addColumn('created_at');
        CRUD::addColumn('updated_at');

        // Add the custom table widgets at the end

        // Show courses only for student or teacher
        $entry = $this->crud->getCurrentEntry();

        if (in_array($entry->role, ['student', 'teacher'])) {
            $this->addCoursesWidgets();
        }
        // add button to export PDF
        if (
            $entry->role == 'student' &&
            ($entry->coursesEnrolled()->count() > 0 || $entry->enrollments()->count() > 0)
        ) {
            $this->crud->addButtonFromView('line', 'export_pdf', 'export_pdf', 'beginning');
        }
        if (
            $entry->role == 'teacher' &&
            ($entry->coursesAuthored()->count() > 0)
        ) {
            $this->crud->addButtonFromView('line', 'export_pdf', 'export_pdf', 'beginning');
        }
    }
    protected function addCoursesWidgets()
    {
        // Get the current entry
        $entry = $this->crud->getCurrentEntry();

        // Add the lessons widget
        Widget::add()->to('after_content')->type('view')->view('vendor.backpack.crud.widgets.courses_table')->content([
            'entry' => $entry
        ]);
    }
    public function exportPdf($id)
    {
        $user = User::with(['coursesEnrolled', 'enrollments.course', 'coursesAuthored'])->findOrFail($id);

        $data = [
            'user' => $user,
        ];

        $pdf = Pdf::loadView('admin.users.user_report', $data);

        return $pdf->download($user->name . '_report.pdf');
    }

    public function exportCsv($id)
    {
        $user = User::with(['coursesEnrolled', 'enrollments.course', 'coursesAuthored'])->findOrFail($id);

        $filename = $user->name . '_report.csv';
        $handle = fopen($filename, 'w+');

        if ($user->role === 'student') {
            fputcsv($handle, ['Enrollment ID', 'Course Title', 'Description', 'Enrolled At']);
            foreach ($user->enrollments as $enroll) {
                fputcsv($handle, [
                    $enroll->id,
                    $enroll->course->title ?? 'N/A',
                    $enroll->course->description ?? 'N/A',
                    $enroll->created_at->format('Y-m-d'),
                ]);
            }
        } elseif ($user->role === 'teacher') {
            fputcsv($handle, ['Course ID', 'Title', 'Description', 'Created At']);
            foreach ($user->coursesAuthored as $course) {
                fputcsv($handle, [
                    $course->id,
                    $course->title,
                    $course->description,
                    $course->created_at->format('Y-m-d'),
                ]);
            }
        }

        fclose($handle);
        return Response::download($filename)->deleteFileAfterSend(true);
    }

    public function exportExcel($id)
    {
        return Excel::download(new UserExport($id), 'user_report.xlsx');
    }
    public function customView(Request $request)
    {
        $query = User::query();

        // Filter by name
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        // Filter by email
        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }

        // Filter by phone
        if ($request->filled('phone')) {
            $query->where('phone', 'like', '%' . $request->phone . '%');
        }

        //  Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        //  pagination
        $users = $query->paginate(10)->appends($request->all());

        return view('admin.users.custom_view', compact('users'));
    }
}
