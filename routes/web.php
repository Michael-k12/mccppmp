<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProcurementItemController;
use App\Http\Controllers\ProcurementController;
use App\Http\Controllers\PpmpController;
use App\Http\Controllers\PdfExportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\SecurityMonitoringController;


/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', fn() => redirect()->route('login'))->name('home');

require __DIR__ . '/auth.php';


/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    /*
    | Dashboard
    */
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');


    /*
    | Settings (Volt)
    */
    Route::redirect('settings', 'settings/profile');
    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');


    /*
    |--------------------------------------------------------------------------
    | PPMP ROUTES
    |--------------------------------------------------------------------------
    */
    Route::get('/ppmp/create', [PpmpController::class, 'create'])->name('ppmp.create');
    Route::post('/ppmp', [PpmpController::class, 'store'])->name('ppmp.store');
    Route::get('/ppmp', [PpmpController::class, 'index'])->name('ppmp.index');

    Route::get('/ppmp/manage', [PpmpController::class, 'manage'])->name('ppmp.manage');

    Route::resource('ppmps', PpmpController::class);

    Route::get('/ppmp/view', [PpmpController::class, 'view'])->name('ppmp.view');
    Route::post('/ppmp/submit-to-principal', [PpmpController::class, 'submitToPrincipal'])->name('ppmp.submitToPrincipal');
    Route::get('/ppmp/principalview', [PpmpController::class, 'principalview'])->name('ppmp.principalview');
    Route::post('/ppmp/approve', [PpmpController::class, 'batchApprove'])->name('ppmp.batchApprove');
    Route::get('/ppmp/approved', [PpmpController::class, 'approved'])->name('ppmp.approved');

    Route::get('/ppmp/edit-quantities/{department}', [PpmpController::class, 'editDepartmentQuantities'])->name('ppmp.editDepartmentQuantities');
    Route::post('/ppmp/update-quantities/{department}', [PpmpController::class, 'updateDepartmentQuantities'])->name('ppmp.updateDepartmentQuantities');

    // Department views
    Route::get('/ppmp/bsit', [PpmpController::class, 'bsit'])->name('ppmp.bsit');
    Route::get('/ppmp/bsba', [PpmpController::class, 'bsba'])->name('ppmp.bsba');
    Route::get('/ppmp/bshm', [PpmpController::class, 'bshm'])->name('ppmp.bshm');
    Route::get('/ppmp/bsed', [PpmpController::class, 'bsed'])->name('ppmp.bsed');
    Route::get('/ppmp/library', [PpmpController::class, 'library'])->name('ppmp.library');
    Route::get('/ppmp/nurse', [PpmpController::class, 'nurse'])->name('ppmp.nurse');

    // PDF downloads per department
    Route::get('/ppmp/bsit/download', [PpmpController::class, 'downloadBsit'])->name('ppmp.bsit.download');
    Route::get('/ppmp/bsba/download', [PpmpController::class, 'downloadBsba'])->name('ppmp.bsba.download');
    Route::get('/ppmp/bsed/download', [PpmpController::class, 'downloadBsed'])->name('ppmp.bsed.download');
    Route::get('/ppmp/bshm/download', [PpmpController::class, 'downloadBshm'])->name('ppmp.bshm.download');
    Route::get('/ppmp/library/download', [PpmpController::class, 'downloadLibrary'])->name('ppmp.library.download');
    Route::get('/ppmp/nurse/download', [PpmpController::class, 'downloadNurse'])->name('ppmp.nurse.download');

    // Global PDF
    Route::get('/ppmp/export-pdf', [PdfExportController::class, 'export'])->name('ppmp.export.pdf');
    Route::get('/ppmp/download', [PdfExportController::class, 'download'])->name('ppmp.download.pdf');
    Route::get('ppmp/download-pdf/{year}', [PpmpController::class, 'downloadPdf'])->name('ppmp.download.pdf.year');

    Route::delete('/ppmp/delete-year', [PpmpController::class, 'deleteYear'])->name('ppmp.delete.year');
    Route::delete('/ppmp/delete-all', [PpmpController::class, 'deleteAll'])->name('ppmp.deleteAll');

    Route::get('/ppmp/remaining-budget', [PpmpController::class, 'getRemainingBudget'])->name('ppmp.remaining-budget');


    /*
    |--------------------------------------------------------------------------
    | BUDGET ROUTES (Secured)
    |--------------------------------------------------------------------------
    */
    Route::get('/budget', [BudgetController::class, 'index'])->name('budget.index');
    Route::post('/budget', [BudgetController::class, 'store'])->name('budget.store');
    Route::post('/budget/end/{id}', [BudgetController::class, 'end'])->name('budget.end');
    Route::delete('/budget/delete-selected', [BudgetController::class, 'deleteSelected'])->name('budget.deleteSelected');


    /*
    |--------------------------------------------------------------------------
    | MANAGE USERS (Admin Only Recommended)
    |--------------------------------------------------------------------------
    */
    Route::get('/manage-users', [UserController::class, 'index'])->name('users.index');
    Route::get('/manage-users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/manage-users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/manage-users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');


    /*
    |--------------------------------------------------------------------------
    | ITEMS (Protect these too)
    |--------------------------------------------------------------------------
    */
    Route::get('/add-items', [ItemController::class, 'index'])->name('items.index');
    Route::post('/add-items', [ItemController::class, 'store'])->name('items.store');
    Route::get('/items/{item}/edit', [ItemController::class, 'edit'])->name('items.edit');
    Route::put('/items/{item}', [ItemController::class, 'update'])->name('items.update');
    Route::delete('/items/{item}', [ItemController::class, 'destroy'])->name('items.destroy');


    /*
    |--------------------------------------------------------------------------
    | SECURITY MONITORING
    |--------------------------------------------------------------------------
    */
    Route::get('/security', [SecurityMonitoringController::class, 'index'])->name('security.index');
});
