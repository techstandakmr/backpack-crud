<?php
namespace App\Http\Controllers\Admin\Operations;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Support\Facades\Route;

trait DuplicateOperation
{
    protected function setupDuplicateOperationRoutes($segment, $routeName, $controller)
    {
        Route::get($segment.'/{id}/duplicate', [
            'as' => $routeName.'.duplicate',
            'uses' => $controller.'@duplicate',
            'operation' => 'duplicate',
        ]);
    }

    protected function setupDuplicateDefaults()
    {
        CRUD::allowAccess('duplicate');
        CRUD::operation('duplicate', function () {
            CRUD::loadDefaultOperationSettingsFromConfig();
        });
    }

    public function duplicate($id)
    {
        $model = CRUD::getModel()->findOrFail($id);
        $newModel = $model->replicate();
        $newModel->save();

        \Alert::success('Item duplicated successfully!')->flash();
        return redirect()->back();
    }
}
