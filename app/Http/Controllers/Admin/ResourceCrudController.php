<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ResourceRequest;
use App\Models\Resource;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Http\Request;

/**
 * Class ResourceCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class ResourceCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Resource::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/resource');
        CRUD::setEntityNameStrings('resource', 'resources');
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
            'name' => 'lesson_id',
            'type' => 'select',
            'entity' => 'lesson',
            'model' => "App\Models\Lesson",
            'attribute' => 'title',
            'label' => "Lesson"
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
    // good
    // protected function setupCreateOperation()
    // {
    //     CRUD::setValidation(ResourceRequest::class);
    //     CRUD::setFromDb(); // set fields from db columns.

    //     /**
    //      * Fields can be defined using the fluent syntax:
    //      * - CRUD::field('price')->type('number');
    //      */
    // }
protected function setupCreateOperation()
{
    CRUD::setValidation(ResourceRequest::class);

    //  Name field
    CRUD::field('name')
        ->label('Name')
        ->type('text');

    //  URL field
    CRUD::field('url')
        ->label('Course URL')
        ->type('url');

    //  Simple select for Courses
    CRUD::addField([
        'name'        => 'lesson_id',              // the column in your resources table
        'label'       => 'Lesson',
        'type'        => 'select',
        'entity'      => 'Lesson',                 // relationship method on Resource model
        'model'       => \App\Models\Lesson::class, // related model
        'attribute'   => 'title',                  // field shown in dropdown
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
        CRUD::modifyColumn('lesson_id', [
            'type'      => 'select',
            'entity'    => 'lesson',     // relationship method in Resource model
            'attribute' => 'title',      // field from related Lesson model
            'label'     => 'Lesson',     // optional: nicer label
        ]);
    }

    // public function customView(Request $request)
    // {
    //     $query = Resource::with('lesson');

    //     if ($request->filled('name')) {
    //         $query->where('name', 'like', '%' . $request->name . '%');
    //     }

    //     if ($request->filled('lesson_id')) {
    //         $query->where('lesson_id', $request->lesson_id);
    //     }

    //     if ($request->filled('resource_id')) {
    //         $query->where('id', $request->resource_id);
    //     }

    //     $resources = $query->get();

    //     return view('admin.resources.custom_view', compact('resources'));
    // }



    public function customView(Request $request)
    {
        $query = Resource::with('lesson');

        // Filter by resource name
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        // Filter by lesson title (via relation)
        if ($request->filled('lesson_title')) {
            $query->whereHas('lesson', function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->lesson_title . '%');
            });
        }

        // Filter by URL
        if ($request->filled('url')) {
            $query->where('url', 'like', '%' . $request->url . '%');
        }

        $resources = $query->get();

        return view('admin.resources.custom_view', compact('resources'));
    }

    public function destroy($id)
    {
        $resource = Resource::findOrFail($id);
        $resource->delete();

        return redirect()->back()->with('success', 'Resource deleted successfully.');
    }
}




