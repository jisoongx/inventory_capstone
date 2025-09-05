<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MonthlyController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OwnerStaffController;
use App\Http\Controllers\TechnicalController;
use App\Http\Controllers\InventoryOwnerController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\RestockController;



Route::get('/', function () {
    return view('login');
});


Route::get('/monthly_profit', [MonthlyController::class, 'index'])->name('dashboards.owner.monthly_profit');
Route::post('/monthly_profit/add', [MonthlyController::class, 'add'])->name('dashboards.owner.monthly_profit_add');
Route::post('/monthly_profit/edit/{expense_id?}', [MonthlyController::class, 'edit'])->name('dashboards.owner.monthly_profit_edit');
Route::get('/expenses/attachment/{expense_id?}', [MonthlyController::class, 'viewAttachment'])->name('expenses.attachment');


Route::get('/owner/technical-request', [TechnicalController::class, 'index'])->name('dashboards.owner.technical_request');
Route::get('/dashboard/owner/technical_request/{req_id?}', [TechnicalController::class, 'show'])->name('dashboards.owner.technical_request');
Route::post('/dashboard/owner/technical_request/message/{req_id?}', [TechnicalController::class, 'add_message'])->name('dashboards.owner.technical_insert');
Route::post('/dashboard/owner/technical_request/request', [TechnicalController::class, 'add_request'])->name('dashboards.owner.technical_add');


Route::get('/staff/technical-request', [TechnicalController::class, 'index'])->name('dashboards.staff.technical_request');
Route::get('/dashboard/staff/technical_request/{req_id?}', [TechnicalController::class, 'show'])->name('dashboards.staff.technical_request');
Route::post('/dashboard/staff/technical_request/message/{req_id?}', [TechnicalController::class, 'add_message'])->name('dashboards.staff.technical_insert');
Route::post('/dashboard/staff/technical_request/request', [TechnicalController::class, 'add_request'])->name('dashboards.staff.technical_add');



//Login and logout
Route::get('/', fn() => view('login'))->name('login');
Route::post('/', [LoginController::class, 'login'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

//Signup
Route::get('/signup',  fn() => view('signup'))->name('signup');
Route::post('/signup', [RegisterController::class, 'register'])->name('signup.submit');
Route::get('/term-of-service',  fn() => view('terms_of_service'))->name('terms.of.service');
Route::get('/privacy-policy',  fn() => view('privacy_policy'))->name('privacy.');
//Subscription
Route::get('/subscription/select', [SubscriptionController::class, 'create'])->name('subscription.selection');
Route::post('/subscribe/{planId}', [SubscriptionController::class, 'store'])->name('subscription.store');
Route::get('/subscription/progress', [SubscriptionController::class, 'progress'])->name('subscription.progress');
Route::get('/subscription/success', fn() => view('subscription_success'))->name('subscription.success');
Route::get('/subscription/expired', fn() => view('subscription_expired'))->name('subscription.expired');

//Different user dashboards used as layout
Route::get('/super-admin/dashboard', fn() => view('dashboards.super_admin.super_admin'))->name('super_admin.dashboard');
Route::get('/owner/dashboard', [DashboardController::class, 'index'])->name('dashboards.owner.dashboard');
Route::get('/staff/dashboard', fn() => view('dashboards.staff.staff'))->name('staff.dashboard');

//clients management

//subscription plan management
Route::get('/subscription/management', [SubscriptionController::class, 'subscribers'])->name('subscription');
Route::put('/subs/{owner_id}/status', [SubscriptionController::class, 'updateSubStatus'])->name('subs.updateStatus');
Route::get('/clients/sub-search', [SubscriptionController::class, 'sub_search'])->name('clients.sub_search');
Route::get('/billing-history', [SubscriptionController::class, 'showExpiredSubscribers'])->name('billing.history');
// activity logs
Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('actLogs');
Route::get('/staff-logs', [ActivityLogController::class, 'staffLogs'])->name('staffLogs');
Route::get('/activity-logs/search', [ActivityLogController::class, 'activity_search'])->name('actlogs.search');

//Profile Management for Super admin, owner, and staff
Route::get('/super-admin/profile', [ProfileController::class, 'showSuperAdminProfile'])->name('super_admin.profile');
Route::put('/super-admin/profile', [ProfileController::class, 'updateSuperAdminProfile'])->name('super_admin.profile.update');
Route::get('/owner/profile', [ProfileController::class, 'showOwnerProfile'])->name('owner.profile');
Route::put('/owner/profile', [ProfileController::class, 'updateOwnerProfile'])->name('owner.profile.update');
Route::get('/staff/profile', [ProfileController::class, 'showStaffProfile'])->name('staff.profile');
Route::put('/staff/profile', [ProfileController::class, 'updateStaffProfile'])->name('staff.profile.update');

// Staff Management 
Route::get('/owner/staff', [OwnerStaffController::class, 'showStaff'])->name('owner.show.staff');
Route::post('/owner/staff', [OwnerStaffController::class, 'addStaff'])->name('owner.add.staff');
Route::put('/owner/staff/{staff}/status', [OwnerStaffController::class, 'updateStatus'])->name('owner.staff.updateStatus');
Route::put('/owner/staff/{staff}', [OwnerStaffController::class, 'updateStaffInfo'])->name('owner.staff.update');

//Inventory for Owners
Route::get('/inventory-owner', [InventoryOwnerController::class, 'index'])->name('inventory-owner');   
Route::get('/inventory-owner/search', [InventoryOwnerController::class, 'index'])->name('inventory.search');
Route::get('/inventory-owner/suggest', [InventoryOwnerController::class, 'suggest']);
Route::post('/check-barcode', [InventoryOwnerController::class, 'checkBarcode']);
Route::post('/register-product', [InventoryOwnerController::class, 'registerProduct']);



Route::get('/billing/search', [BillingController::class, 'search'])->name('billing.search');
Route::get('/reports',  fn() => view('dashboards.owner.reports'))->name('reports');
Route::get('/restock-suggestions', [RestockController::class, 'lowStock'])->name('restock_suggestion');
Route::post('/restock/finalize', [RestockController::class, 'finalize'])->name('restock.finalize');
Route::get('/restock/list', [RestockController::class, 'list'])->name('restock.list');
