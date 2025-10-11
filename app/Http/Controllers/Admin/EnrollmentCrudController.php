<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\EnrollmentRequest;
use App\Models\Enrollment;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Http\Request;
use PDF; // DomPDF facade
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
        CRUD::setFromDb(); // set columns from db columns.
        // $this->crud->addField([
        //     'name' => 'course_id',
        //     'type' => 'select',
        //     'entity' => 'course',
        //     'model' => "App\Models\Course",
        //     'attribute' => 'title',
        //     'label' => "Course"
        // ]);

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
        CRUD::setValidation(EnrollmentRequest::class);

        //  Student Name
        CRUD::field('student_name')
            ->label('Student Name')
            ->type('text');

        //  Email
        CRUD::field('email')
            ->label('Email')
            ->type('email');

        //  Course (dropdown select)
        CRUD::addField([
            'name'      => 'course_id',                // column in enrollments table
            'label'     => 'Course',
            'type'      => 'select',
            'entity'    => 'course',                   // relationship method in Enrollment model
            'model'     => \App\Models\Course::class,  // related model
            'attribute' => 'title',                    // field to show in dropdown
        ]);

        //  Phone
        CRUD::field('phone')
            ->label('Phone')
            ->type('text');

        //  Enrolled At
        CRUD::field('enrolled_at')
            ->label('Enrolled At')
            ->type('datetime');
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
        // add button to export PDF
        $this->crud->addButtonFromView('line', 'export_pdf', 'export_pdf', 'beginning');

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
        $query = Enrollment::with(['course']);

        if ($request->filled('student_name')) {
            $query->where('student_name', 'like', '%' . $request->student_name . '%');
        }

        if ($request->filled('student_email')) {
            $query->where('student_email', 'like', '%' . $request->student_email . '%');
        }

        if ($request->filled('course_title')) {
            $query->whereHas('course', function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->course_title . '%');
            });
        }

        if ($request->filled('phone')) {
            $query->where('phone', 'like', '%' . $request->phone . '%');
        }
        // add pagination
        $enrollments = $query->paginate(10)->appends($request->all());

        return view('admin.enrollments.custom_view', compact('enrollments'));
    }
    public function exportPdf($email)
    {
        // Get the course's enrollments
        $enrollments = Enrollment::where('student_email', $email)->get();

        // Share data with view
        $data = [
            'enrollments' => $enrollments,
        ];

        // Load a Blade view for PDF
        $pdf = PDF::loadView('admin.enrollments.pdf', $data);

        // Download the PDF
        // random string
        $randomString = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'), 0, 5);
        return $pdf->download('enrollments_course_' .$randomString. '.pdf');
    }
}
