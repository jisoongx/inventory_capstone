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
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\Auth\PasswordResetController;




Route::get('/', function () {
    return view('login');
});


Route::get('/expense_record', [MonthlyController::class, 'index'])->name('dashboards.owner.expense_record');
Route::post('/expense_record/add', [MonthlyController::class, 'add'])->name('dashboards.owner.expense_record_add');
Route::post('/expense_record/edit/{expense_id?}', [MonthlyController::class, 'edit'])->name('dashboards.owner.expense_record_edit');
Route::get('/expenses/attachment/{expense_id?}', [MonthlyController::class, 'viewAttachment'])->name('expenses.attachment');


Route::get('/owner/technical-request', [TechnicalController::class, 'index'])->name('dashboards.owner.technical_request');
Route::get('/dashboard/owner/technical_request/{req_id?}', [TechnicalController::class, 'show'])->name('dashboards.owner.technical_request');
Route::post('/dashboard/owner/technical_request/message/{req_id?}', [TechnicalController::class, 'add_message'])->name('dashboards.owner.technical_insert');
Route::post('/dashboard/owner/technical_request/request', [TechnicalController::class, 'add_request'])->name('dashboards.owner.technical_add');


Route::get('/staff/technical-request', [TechnicalController::class, 'index'])->name('dashboards.staff.technical_request');
Route::get('/dashboard/staff/technical_request/{req_id?}', [TechnicalController::class, 'show'])->name('dashboards.staff.technical_request');
Route::post('/dashboard/staff/technical_request/message/{req_id?}', [TechnicalController::class, 'add_message'])->name('dashboards.staff.technical_insert');
Route::post('/dashboard/staff/technical_request/request', [TechnicalController::class, 'add_request'])->name('dashboards.staff.technical_add');


Route::get('/super/technical-support', [TechnicalController::class, 'index'])->name('dashboards.super_admin.technical');
Route::get('/super/technical-support/chat/{req_id?}', [TechnicalController::class, 'show'])->name('dashboards.super_admin.technical_show');
Route::post('super/technical-support/message/{req_id?}', [TechnicalController::class, 'add_message'])->name('dashboards.super_admin.technical_insert');


Route::get('/super/notification', [NotificationController::class, 'index'])->name('dashboards.super_admin.notification');
Route::post('/super/notification/create', [NotificationController::class, 'send_notification'])->name('dashboards.notification.send');

// Route::post('/notifications/mark-seen', [NotificationController::class, 'markSeen'])->name('notifications.markSeen');





//Login and logout
Route::get('/', fn() => view('login'))->name('login');
Route::post('/', [LoginController::class, 'login'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

//Signup
Route::get('/signup',  fn() => view('signup'))->name('signup');
Route::post('/signup', [RegisterController::class, 'register'])->name('signup.submit');
Route::get('/term-of-service',  fn() => view('terms_of_service'))->name('terms.of.service');
Route::get('/privacy-policy',  fn() => view('privacy_policy'))->name('privacy.');


//forgot password
Route::get('/forgot-password', fn() => view('forgot-password'))->name('password.request');
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password/{token}', function (string $token) {
    return view('reset-password', ['token' => $token]);
})->name('password.reset');
Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update');


//Subscription
Route::get('/subscription/select', [SubscriptionController::class, 'create'])->name('subscription.selection');
Route::post('/subscribe/{planId}', [SubscriptionController::class, 'store'])->name('subscription.store');
Route::get('/subscription/progress', [SubscriptionController::class, 'progress'])->name('subscription.progress');
Route::get('/subscription/success', fn() => view('subscription_success'))->name('subscription.success');
Route::get('/subscription/expired', fn() => view('subscription_expired'))->name('subscription.expired');

//Different user dashboards used as layout
Route::get('/super-admin/dashboard', fn() => view('dashboards.super_admin.super_admin'))->name('super_admin.dashboard');
Route::get('/owner/dashboard', [DashboardController::class, 'index'])->name('dashboards.owner.dashboard');
Route::get('/staff/dashboard', [DashboardController::class, 'index_staff'])->name('staff.dashboard');

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
Route::post('/inventory/restock', [InventoryOwnerController::class, 'restockProduct'])->name('inventory.restock');
Route::get('/inventory/latest-batch/{prodCode}', [InventoryOwnerController::class, 'getLatestBatch'])->name('inventory.latestBatch');
Route::get('/inventory/product/{prodCode}', [InventoryOwnerController::class, 'showProductDetails'])->name('inventory-product-info');





Route::get('/billing/search', [BillingController::class, 'search'])->name('billing.search');
Route::get('/reports',  fn() => view('dashboards.owner.reports'))->name('reports');
Route::get('/restock-suggestions', [RestockController::class, 'restockSuggestion'])->name('restock_suggestion');
Route::post('/restock/finalize', [RestockController::class, 'finalize'])->name('restock.finalize');
Route::get('/restock/list', [RestockController::class, 'list'])->name('restock.list');
Route::get('/seasonal-trends', [RestockController::class, 'topProducts']) ->name('seasonal_trends');

// Store: Transaction
// Main transactions routes
Route::get('/store_transactions', [StoreController::class, 'index'])->name('store_transactions');
// New routes for the enhanced functionality
Route::post('/search_product', [StoreController::class, 'searchProduct'])->name('search_product');
Route::post('/start_transaction', [StoreController::class, 'startTransaction'])->name('start_transaction');
Route::get('/store_start_transaction', [StoreController::class, 'showStartTransaction'])->name('store_start_transaction');
Route::post('/update_transaction_items', [StoreController::class, 'updateTransactionItems'])->name('update_transaction_items');
Route::post('/process_payment', [StoreController::class, 'processPayment'])->name('process_payment');

