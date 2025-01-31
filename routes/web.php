<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\CustomerListController;
use App\Http\Controllers\ProductInventoryController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

//Admin Routes
Route::GET('/adminlogin', [AdminController::class, 'adminlogin'])->name('adminlogin');
Route::POST('/adminlogin', [AdminController::class, 'login_admin'])->name('login_admin');
Route::POST('/adminlogin', [AdminController::class, 'authenticate'])->name('admin.authenticate');

Route::prefix('admin')->group(function () {
     
    Route::middleware(['adminAuth'])->group(function () {
        
        Route::GET('/admindashboard', [AdminController::class, 'adminDashboard'])->name('admin.dashboard');
        Route::GET('/user_management', [AdminController::class, 'userManagement'])->name('admin.usermanagement');
        Route::POST('/adminlogout', [AdminController::class, 'adminlogout'])->name('admin.logout');
        Route::POST('/admin/add_user', [AdminController::class, 'add_user'])->name('admin.add_user');
        Route::PUT('/admin/user/{id}/edit', [AdminController::class, 'edit_user'])->name('admin.edit_user');
        Route::PUT('/admin/user/{id}/change_password', [AdminController::class, 'change_password'])->name('admin.change_password');
    });
});

//Sales Routes
Route::GET('/saleslogin', [SalesController::class, 'saleslogin'])->name('saleslogin');
Route::POST('/saleslogin', [SalesController::class, 'login_sales'])->name('login_sales');
Route::POST('/saleslogin', [SalesController::class, 'authenticate'])->name('sales.authenticate');

Route::prefix('sales')->group(function () {
     
    Route::middleware(['salesAuth'])->group(function () {
        
        Route::GET('/salestransaction', [SalesController::class, 'salesTransaction'])->name('sales.transaction');
        Route::POST('/saleslogout', [SalesController::class, 'saleslogout'])->name('sales.logout');
        Route::GET('/customerlist', [SalesController::class, 'customerList'])->name('sales.customerlist');
        Route::get('/sales/customers', [SalesController::class, 'getCustomers'])->name('sales.customers');

        Route::post('/customers', [CustomerListController::class, 'store'])->name('customers.store');
        Route::get('/get-departments', [CustomerListController::class, 'getDepartments']);//this is to show the departments in the dropdown

        Route::put('customers/{id}', [CustomerListController::class, 'update'])->name('customers.update');
        Route::delete('customers/{id}', [CustomerListController::class, 'destroy'])->name('customers.destroy');


        Route::GET('/productInventory', [ProductInventoryController::class, 'productInventory'])->name('sales.productInventory');
        Route::GET('/stockscount', [ProductInventoryController::class, 'countStocks'])->name('sales.stocksCount');
        Route::post('/stocks/store', [ProductInventoryController::class, 'storeStockCount'])->name('stocks.store');
        Route::patch('/stocks/update', [ProductInventoryController::class, 'updateStockCount'])->name('stocks.update');


        Route::post('/products/store', [ProductInventoryController::class, 'storeProduct'])->name('products.store');
        
    });
});

