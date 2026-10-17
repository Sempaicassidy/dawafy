<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\SubCategoryController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProcurementController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\SaasDashboardController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\InsuranceController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\QuarantineController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CompoundingController;
use App\Http\Controllers\AddonController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Health Check / Test Route
Route::get('/test', function () {
    return 'Laravel is working! ' . date('Y-m-d H:i:s');
});

// Root Route: redirect to dashboard if authenticated, else to login
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Authentication Routes (Public)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::match(['GET', 'POST'], '/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Authenticated System Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    // Dashboard Routes
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/index', [DashboardController::class, 'index'])->name('dashboard.index');

    // POS Routes
    Route::get('/pos', [POSController::class, 'index'])->name('pos.index');
    Route::get('/pos/search', [POSController::class, 'searchDrugs'])->name('pos.search');
    Route::post('/pos/transaction', [POSController::class, 'createTransaction'])->name('pos.transaction');
    Route::get('/pos/customer-lookup', [POSController::class, 'findCustomer'])->name('pos.customer_lookup');
    Route::post('/pos/customer-register', [POSController::class, 'registerCustomer'])->name('pos.customer_register');
    Route::any('/pos_api.php', [POSController::class, 'legacyApi'])->name('pos.legacy_api');

    // Inventory Routes
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::post('/inventory/store', [InventoryController::class, 'store'])->name('inventory.store');
    Route::post('/inventory/update', [InventoryController::class, 'update'])->name('inventory.update');
    Route::post('/inventory/add-stock', [InventoryController::class, 'addStock'])->name('inventory.add_stock');

    // Sub-Categories Routes
    Route::get('/sub-categories', [SubCategoryController::class, 'index'])->name('sub_categories.index');
    Route::post('/sub-categories/store', [SubCategoryController::class, 'store'])->name('sub_categories.store');
    Route::post('/sub-categories/update', [SubCategoryController::class, 'update'])->name('sub_categories.update');
    Route::get('/sub-categories/destroy/{id}', [SubCategoryController::class, 'destroy'])->name('sub_categories.destroy');

    // Patient Routes
    Route::get('/patients', [PatientController::class, 'index'])->name('patients.index');
    Route::get('/patients/create', [PatientController::class, 'create'])->name('patients.create');
    Route::post('/patients', [PatientController::class, 'store'])->name('patients.store');
    Route::get('/patients/{patient}/edit', [PatientController::class, 'edit'])->name('patients.edit');
    Route::put('/patients/{patient}', [PatientController::class, 'update'])->name('patients.update');
    Route::delete('/patients/{patient}', [PatientController::class, 'destroy'])->name('patients.destroy');

    // Report Routes
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
    Route::get('/reports/inventory', [ReportController::class, 'inventory'])->name('reports.inventory');
    Route::get('/reports/audit-logs', [ReportController::class, 'auditLogs'])->name('reports.audit_logs');
    Route::get('/reports/daily', [ReportController::class, 'daily'])->name('reports.daily');

    // Procurement Routes
    Route::get('/procurement', [ProcurementController::class, 'index'])->name('procurement.index');
    Route::get('/procurement/create', [ProcurementController::class, 'create'])->name('procurement.create');
    Route::post('/procurement', [ProcurementController::class, 'store'])->name('procurement.store');
    Route::get('/procurement/{purchaseOrder}', [ProcurementController::class, 'show'])->name('procurement.show');
    Route::post('/procurement/{purchaseOrder}/approve', [ProcurementController::class, 'approve'])->name('procurement.approve');
    Route::post('/procurement/{purchaseOrder}/receive', [ProcurementController::class, 'receive'])->name('procurement.receive');

    // Prescription Routes
    Route::get('/prescriptions', [PrescriptionController::class, 'index'])->name('prescriptions.index');
    Route::get('/prescriptions/create', [PrescriptionController::class, 'create'])->name('prescriptions.create');
    Route::post('/prescriptions', [PrescriptionController::class, 'store'])->name('prescriptions.store');
    Route::get('/prescriptions/{prescription}', [PrescriptionController::class, 'show'])->name('prescriptions.show');

    // Shift Routes
    Route::get('/shifts', [ShiftController::class, 'index'])->name('shifts.index');
    Route::post('/shifts/start', [ShiftController::class, 'start'])->name('shifts.start');
    Route::post('/shifts/{shift}/end', [ShiftController::class, 'end'])->name('shifts.end');

    // Insurance Routes
    Route::get('/insurance', [InsuranceController::class, 'index'])->name('insurance.index');
    Route::get('/insurance/create-provider', [InsuranceController::class, 'createProvider'])->name('insurance.create_provider');
    Route::post('/insurance/provider', [InsuranceController::class, 'storeProvider'])->name('insurance.store_provider');
    Route::get('/insurance/create-claim', [InsuranceController::class, 'createClaim'])->name('insurance.create_claim');
    Route::post('/insurance/claim', [InsuranceController::class, 'storeClaim'])->name('insurance.store_claim');
    Route::post('/insurance/claim/{claim}/approve', [InsuranceController::class, 'approveClaim'])->name('insurance.approve_claim');
    Route::post('/insurance/claim/{claim}/reject', [InsuranceController::class, 'rejectClaim'])->name('insurance.reject_claim');

    // Wallet Routes
    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
    Route::post('/wallet/transaction', [WalletController::class, 'transaction'])->name('wallet.transaction');
    Route::get('/wallet/{patient}', [WalletController::class, 'show'])->name('wallet.show');
    Route::post('/wallet/{patient}/credit', [WalletController::class, 'credit'])->name('wallet.credit');
    Route::post('/wallet/{patient}/debit', [WalletController::class, 'debit'])->name('wallet.debit');

    // Quarantine Routes
    Route::get('/quarantine', [QuarantineController::class, 'index'])->name('quarantine.index');
    Route::get('/quarantine/create', [QuarantineController::class, 'create'])->name('quarantine.create');
    Route::post('/quarantine', [QuarantineController::class, 'store'])->name('quarantine.store');
    Route::post('/quarantine/{quarantine}/release', [QuarantineController::class, 'release'])->name('quarantine.release');

    // Expense Routes
    Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
    Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');

    // Compounding Routes
    Route::get('/compounding', [CompoundingController::class, 'index'])->name('compounding.index');

    // Addon Routes
    Route::get('/addons/ai-sales', [AddonController::class, 'aiSales'])->name('addons.ai_sales');
    Route::get('/addons/erp', [AddonController::class, 'erp'])->name('addons.erp');
    Route::get('/addons/online-purchases', [AddonController::class, 'onlinePurchases'])->name('addons.online_purchases');
    Route::get('/addons/whatsapp', [AddonController::class, 'whatsapp'])->name('addons.whatsapp');
    Route::get('/addons/data-transfer', [AddonController::class, 'dataTransfer'])->name('addons.data_transfer');
    Route::get('/addons/vat', [AddonController::class, 'vat'])->name('addons.vat');
    Route::get('/addons/backup', [AddonController::class, 'backup'])->name('addons.backup');

    // Role Switcher Route
    Route::post('/switch-role', function (\Illuminate\Http\Request $request) {
        $actualRole = session('actual_role') ?? (auth()->user()?->role?->name ?? 'Admin');
        $allowedRoles = ['Admin', 'Super Admin', 'Pharmacist'];
        if (in_array($actualRole, $allowedRoles) || !empty(session('masquerade_tenant_id'))) {
            if ($request->has('active_role')) {
                session(['role' => $request->input('active_role')]);
            }
        }
        return back();
    })->name('switch_role');

    /*
    |--------------------------------------------------------------------------
    | Admin-Only Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:Admin,Super Admin'])->group(function () {
        // Supplier Routes
        Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
        Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
        Route::post('/suppliers/update', [SupplierController::class, 'update'])->name('suppliers.update');
        Route::match(['get', 'delete'], '/suppliers/{id}/delete', [SupplierController::class, 'destroy'])->name('suppliers.destroy');

        // User Routes
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::post('/users/update', [UserController::class, 'update'])->name('users.update');
        Route::post('/users/{user}/reset-session', [UserController::class, 'resetSession'])->name('users.reset_session');

        // Settings Routes
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');

        // Branch Routes
        Route::get('/branches', [BranchController::class, 'index'])->name('branches.index');
        Route::get('/branches/create', [BranchController::class, 'create'])->name('branches.create');
        Route::post('/branches', [BranchController::class, 'store'])->name('branches.store');
        Route::post('/branches/update', [BranchController::class, 'update'])->name('branches.update_branch');
        Route::post('/branches/toggle', [BranchController::class, 'toggle'])->name('branches.toggle');
        Route::get('/branches/{branch}/edit', [BranchController::class, 'edit'])->name('branches.edit');
        Route::put('/branches/{branch}', [BranchController::class, 'update'])->name('branches.update');
        Route::post('/branches/{branch}/activate', [BranchController::class, 'activate'])->name('branches.activate');
    });

    /*
    |--------------------------------------------------------------------------
    | Super Admin-Only Routes (SaaS Management)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:Super Admin'])->group(function () {
        Route::get('/saas-dashboard', [SaasDashboardController::class, 'index'])->name('saas.dashboard');
        Route::get('/saas-dashboard/support/{tenant}', [SaasDashboardController::class, 'supportLogin'])->name('saas.support_login');
        Route::get('/saas-dashboard/exit-support', [SaasDashboardController::class, 'exitSupport'])->name('saas.exit_support');
        Route::get('/saas-dashboard/toggle/{tenant}', [SaasDashboardController::class, 'toggleStatus'])->name('saas.toggle_status');
        Route::get('/saas-dashboard/activate-branch/{tenant}', [SaasDashboardController::class, 'activateBranch'])->name('saas.activate_branch');
        Route::get('/saas-dashboard/deactivate-branch/{tenant}', [SaasDashboardController::class, 'deactivateBranch'])->name('saas.deactivate_branch');
        Route::post('/saas-dashboard/contract', [SaasDashboardController::class, 'updateContract'])->name('saas.update_contract');
        Route::get('/saas-dashboard/create-tenant', [SaasDashboardController::class, 'createTenant'])->name('saas.create_tenant');
        Route::post('/saas-dashboard/tenant', [SaasDashboardController::class, 'storeTenant'])->name('saas.store_tenant');
        Route::get('/saas-dashboard/{tenant}/edit', [SaasDashboardController::class, 'editTenant'])->name('saas.edit_tenant');
        Route::put('/saas-dashboard/{tenant}', [SaasDashboardController::class, 'updateTenant'])->name('saas.update_tenant');

        // Super Admin Management & Global Audit Logs
        Route::get('/saas-dashboard/super-admins', [SaasDashboardController::class, 'superAdmins'])->name('saas.super_admins');
        Route::post('/saas-dashboard/super-admins', [SaasDashboardController::class, 'storeSuperAdmin'])->name('saas.store_super_admin');
        Route::post('/saas-dashboard/super-admins/{user}/delete', [SaasDashboardController::class, 'deleteSuperAdmin'])->name('saas.delete_super_admin');
        Route::post('/saas-dashboard/super-admins/{user}/reset-password', [SaasDashboardController::class, 'resetSuperAdminPassword'])->name('saas.reset_super_admin_password');
        Route::get('/saas-dashboard/audit-logs', [SaasDashboardController::class, 'auditLogs'])->name('saas.audit_logs');
    });
});

/*
|--------------------------------------------------------------------------
| Legacy .php Compatibility Redirects
|--------------------------------------------------------------------------
*/
Route::match(['get', 'post'], '/inventory.php', fn() => redirect()->route('inventory.index'));
Route::match(['get', 'post'], '/pos.php', fn() => redirect()->route('pos.index'));
Route::match(['get', 'post'], '/dashboard.php', fn() => redirect()->route('dashboard'));
Route::match(['get', 'post'], '/patients.php', fn() => redirect()->route('patients.index'));
Route::match(['get', 'post'], '/reports.php', fn() => redirect()->route('reports.index'));
Route::match(['get', 'post'], '/prescriptions.php', fn() => redirect()->route('prescriptions.index'));
Route::match(['get', 'post'], '/procurement.php', fn() => redirect()->route('procurement.index'));
Route::match(['get', 'post'], '/saas_dashboard.php', function (\Illuminate\Http\Request $request) {
    return redirect()->route('saas.dashboard', $request->query());
});
Route::match(['get', 'post'], '/shifts.php', fn() => redirect()->route('shifts.index'));
Route::match(['get', 'post'], '/insurance.php', fn() => redirect()->route('insurance.index'));
Route::match(['get', 'post'], '/wallet.php', fn() => redirect()->route('wallet.index'));
Route::match(['get', 'post'], '/quarantine.php', fn() => redirect()->route('quarantine.index'));
Route::match(['get', 'post'], '/branches.php', fn() => redirect()->route('branches.index'));
Route::match(['get', 'post'], '/suppliers.php', fn() => redirect()->route('suppliers.index'));
Route::match(['get', 'post'], '/users.php', fn() => redirect()->route('users.index'));
Route::match(['get', 'post'], '/settings.php', fn() => redirect()->route('settings.index'));
Route::match(['get', 'post'], '/expenses.php', fn() => redirect()->route('expenses.index'));
Route::match(['get', 'post'], '/compounding.php', fn() => redirect()->route('compounding.index'));
Route::match(['get', 'post'], '/online_purchases.php', fn() => redirect()->route('addons.online_purchases'));
Route::match(['get', 'post'], '/ai_sales_report.php', fn() => redirect()->route('addons.ai_sales'));
Route::match(['get', 'post'], '/erp_integration.php', fn() => redirect()->route('addons.erp'));
Route::match(['get', 'post'], '/whatsapp_customers.php', fn() => redirect()->route('addons.whatsapp'));
Route::match(['get', 'post'], '/data_transfer.php', fn() => redirect()->route('addons.data_transfer'));
Route::match(['get', 'post'], '/vat_settings.php', fn() => redirect()->route('addons.vat'));
Route::match(['get', 'post'], '/backup_system.php', fn() => redirect()->route('addons.backup'));
Route::match(['get', 'post'], '/switch_role.php', fn(\Illuminate\Http\Request $r) => redirect()->route('dashboard'));
