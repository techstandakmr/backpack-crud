<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\LessonRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Http\Request;
use App\Models\Lesson;

/**
 * Class LessonCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class LessonCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Lesson::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/lesson');
        CRUD::setEntityNameStrings('lesson', 'lessons');
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
        $this->crud->addField([
            'name' => 'course_id',
            'type' => 'select',
            'entity' => 'course',
            'model' => "App\Models\Course",
            'attribute' => 'title',
            'label' => "Course"
        ]);

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
    // good creating
    // protected function setupCreateOperation()
    // {
    //     CRUD::setValidation(LessonRequest::class);
    //     CRUD::setFromDb(); // set fields from db columns.

    //     /**
    //      * Fields can be defined using the fluent syntax:
    //      * - CRUD::field('price')->type('number');
    //      */
    // }

    protected function setupCreateOperation()
    {
        CRUD::setValidation(LessonRequest::class);

        //  Title Field
        CRUD::field('title')
            ->type('text')
            ->label('Lesson Title')
            ->attributes(['placeholder' => 'Enter lesson title']);

        //  Content Field
        CRUD::field('content')
            ->type('textarea')
            ->label('Content')
            ->attributes(['placeholder' => 'Enter lesson content', 'rows' => 6]);

        //  Course Selection (Simple Dropdown)
        CRUD::addField([
            'name' => 'course_id',
            'label' => 'Course',
            'type' => 'select',
            'entity' => 'course',
            'attribute' => 'title',
            'model' => "App\Models\Course",
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
        CRUD::modifyColumn('course_id', [
            'type'      => 'select',
            'entity'    => 'course',     // relationship method in Resource model
            'attribute' => 'title',      // field from related Lesson model
            'label'     => 'Course',     // optional: nicer label
        ]);
    }
    // public function customView(Request $request)
    // {
    //     $query = Lesson::with(['course']);

    //     if ($request->filled('title')) {
    //         $query->where('title', 'like', '%' . $request->title . '%');
    //     }

    //     if ($request->filled('course_id')) {
    //         $query->where('course_id', $request->course_id);
    //     }

    //     $lessons = $query->get();

    //     return view('admin.lessons.custom_view', compact('lessons'));
    // }


    public function customView(Request $request)
    {
        $query = Lesson::with(['course']);

        // Filter by lesson title
        if ($request->filled('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }

        // Filter by course title (not just ID)
        if ($request->filled('course')) {
            $query->whereHas('course', function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->course . '%');
            });
        }

        // Filter by content
        if ($request->filled('content')) {
            $query->where('content', 'like', '%' . $request->content . '%');
        }

        $lessons = $query->get();

        return view('admin.lessons.custom_view', compact('lessons'));
    }

    public function destroy($id)
    {
        $this->crud->hasAccessOrFail('delete');

        $item = $this->crud->getEntry($id);
        $item->delete();

        return redirect()->back()->with('success', 'Lesson deleted successfully.');
    }
}
