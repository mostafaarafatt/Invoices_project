<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ArchiveInvoicesController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\CustomersReports;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InvoiceReports;
use App\Http\Controllers\InvoicesAttachmentsController;
use App\Http\Controllers\InvoicesController;
use App\Http\Controllers\InvoicesDetailsController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SectionsController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

//Route::get('/{page}', [AdminController::class, 'index']);

Route::resource('books', BookController::class);
Route::resource('invoices', InvoicesController::class);
Route::resource('sections', SectionsController::class);
Route::resource('products',ProductsController::class);
Route::get('section/{id}',[InvoicesController::class,'getproducts']);
Route::get('InvoicesDeltails/{id}',[InvoicesDetailsController::class,'edit']);
Route::get('View_file/{invoice_number}/{file_name}',[InvoicesDetailsController::class,'viewFile']);
Route::get('download/{invoice_number}/{file_name}',[InvoicesDetailsController::class,'downloadFile']);
Route::post('delete_file',[InvoicesDetailsController::class,'destroy'])->name('delete_file');
Route::resource('addAttachment',InvoicesAttachmentsController::class);
Route::get('edit_invoice/{id}',[InvoicesController::class,'edit']);
Route::get('update_status/{id}',[InvoicesController::class,'show'])->name('update_status');
Route::post('status_update/{id}',[InvoicesController::class,'statusUpdate'])->name('status_update');
Route::get('paid_invoices',[InvoicesController::class,'paid_invoices']);
Route::get('unpaid_invoices',[InvoicesController::class,'unpaid_invoices']);
Route::get('partialPaid_invoices',[InvoicesController::class,'partialPaid_invoices']);
Route::resource('Archive', ArchiveInvoicesController::class);
Route::get('print_invoice/{id}',[InvoicesController::class,'print_invoice'])->name('print_invoice');
Route::get('invoice_export',[InvoicesController::class,'export'])->name('invoice_export');
Route::get('invoice_reports',[InvoiceReports::class,'index']);
Route::post('Search_invoices',[InvoiceReports::class,'search_invoices']);
Route::get('customer_reports',[CustomersReports::class,'index']);
Route::post('Search_customers',[CustomersReports::class,'search_customers']);
Route::get('get_invoice/{id}/{notifiId}',[InvoicesController::class,'get_invoice'])->name('get_invoice');
Route::get('read_all_invoices',[InvoicesController::class,'read_all_invoices'])->name('read_all_invoices');



Route::group(['middleware' => ['auth']], function() {
    Route::resource('roles', RoleController::class);
    Route::resource('users', UserController::class);
   // Route::resource('products', ProductController::class);
});


// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/dashboard',[DashboardController::class,'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


require __DIR__ . '/auth.php';
