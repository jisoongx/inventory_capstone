<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MonthlyController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OwnerStaffController;
use App\Http\Controllers\TechnicalController;



Route::get('/', function () {
    return view('login');
});


Route::get('/monthly_profit', [MonthlyController::class, 'index'])->name('dashboards.owner.monthly_profit');
Route::post('/monthly_profit', [MonthlyController::class, 'add'])->name('dashboards.owner.monthly_profit_add');
Route::post('/monthly_profit/{$expense_id?}', [MonthlyController::class, 'edit'])->name('dashboards.owner.monthly_profit_edit');


Route::get('/owner/technical-request', [TechnicalController::class, 'index'])->name('dashboards.owner.technical_request');
Route::get('/dashboard/owner/technical_request/{req_id?}', [TechnicalController::class, 'show'])->name('dashboards.owner.technical_request');
Route::post('/dashboard/owner/technical_request/message/{req_id?}', [TechnicalController::class, 'add_message'])->name('dashboards.owner.technical_insert');
Route::post('/dashboard/owner/technical_request/request', [TechnicalController::class, 'add_request'])->name('dashboards.owner.technical_add');


Route::get('/staff/technical-request', [TechnicalController::class, 'index'])->name('dashboards.staff.technical_request');
Route::get('/dashboard/staff/technical_request/{req_id?}', [TechnicalController::class, 'show'])->name('dashboards.staff.technical_request');
Route::post('/dashboard/staff/technical_request/message/{req_id?}', [TechnicalController::class, 'add_message'])->name('dashboards.staff.technical_insert');
Route::post('/dashboard/staff/technical_request/request', [TechnicalController::class, 'add_request'])->name('dashboards.staff.technical_add');



//Login and logout
Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/', [LoginController::class, 'login'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

//Signup
Route::get('/signup', [RegisterController::class, 'showRegistrationForm'])->name('signup');
Route::post('/signup', [RegisterController::class, 'register'])->name('signup.submit');

//Subscription
Route::get('/subscription/select', [SubscriptionController::class, 'create'])->name('subscription.selection');
Route::post('/subscribe/{planId}', [SubscriptionController::class, 'store'])->name('subscription.store'); // This is the route for your form submission
Route::get('/subscribe/success', [SubscriptionController::class, 'showSubscriptionSuccess'])->name('subscription.success');

//Different user dashboards used as layout
Route::get('/super-admin/dashboard', fn() => view('dashboards.super_admin.super_admin'))->name('super_admin.dashboard');
Route::get('/owner/dashboard', [DashboardController::class, 'index'])->name('dashboards.owner.dashboard');
Route::get('/staff/dashboard', fn() => view('dashboards.staff.staff'))->name('staff.dashboard');

//Super Admin navbar functions****
//clients management
Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
Route::get('/clients/search', [ClientController::class, 'search'])->name('clients.search'); 
Route::put('/clients/{owner_id}/status', [ClientController::class, 'updateStatus'])->name('clients.updateStatus');
//Route::get('/clients/filter-by-status', [ClientController::class, 'filterByStatus'])->name('clients.filterByStatus');
//subscription management
Route::get('/subscription', [ClientController::class, 'subscribers'])->name('subscription');
Route::put('/subs/{owner_id}/status', [ClientController::class, 'updateSubStatus'])->name('subs.updateStatus');
Route::get('/clients/sub-search', [ClientController::class, 'sub_search'])->name('clients.sub_search');
// activity logs
Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('actLogs');


//Profile Management for Super admin, owner, and staff
Route::get('/super-admin/profile', [ProfileController::class, 'showSuperAdminProfile'])->name('super_admin.profile');
Route::put('/super-admin/profile', [ProfileController::class, 'updateSuperAdminProfile'])->name('super_admin.profile.update');
Route::get('/owner/profile', [ProfileController::class, 'showOwnerProfile'])->name('owner.profile');
Route::put('/owner/profile', [ProfileController::class, 'updateOwnerProfile'])->name('owner.profile.update');
Route::get('/staff/profile', [ProfileController::class, 'showStaffProfile'])->name('staff.profile');
Route::put('/staff/profile', [ProfileController::class, 'updateStaffProfile'])->name('staff.profile.update'); 

// Staff Management for Owners
Route::get('/owner/staff', [OwnerStaffController::class, 'index'])->name('owner.staff.index'); // <--- ADDED: Route to show staff creation form
Route::post('/owner/staff', [OwnerStaffController::class, 'store'])->name('owner.staff.store');
Route::put('/owner/staff/{staff}/status', [OwnerStaffController::class, 'updateStatus'])->name('owner.staff.updateStatus'); // Route to update status from dropdown
Route::put('/owner/staff/{staff}', [OwnerStaffController::class, 'update'])->name('owner.staff.update');
Route::delete('/owner/staff/{staff}', [OwnerStaffController::class, 'destroy'])->name('owner.staff.destroy'); // Route to delete staff