<?php

use App\Http\Controllers\Admin\CONTROLLERPATHNAMEController;
use Illuminate\Support\Facades\Route;



$controller = CONTROLLERNAMEController::class;

$master = admin_routes($controller, true, true, true, null, "MODULENAME", "MODULEDESCRIPTION");

Route::controller($controller)->name('.')->group(function()use ($master){
    

    /**
     * --------------------------------------------------------------------------------------------------------------------
     *                Method | URI                           |  Nethod                   | Route Name                
     * --------------------------------------------------------------------------------------------------------------------
     */

    // $checkSlug = Route::post('/check-slug',                    'checkSlug'                            )->name('check-slug');
    // $master->addActionByRouter($checkSlug, ['create', 'update']);
    
});