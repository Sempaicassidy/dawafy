<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Role;
use App\Models\AuditLog;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SaasDashboardController extends Controller
{
    public function index(Request $request)
    {
        // Handle legacy or direct query-based actions
        if ($request->filled('support_tenant_id')) {
            $tenant = Tenant::findOrFail($request->input('support_tenant_id'));
            return $this->supportLogin($tenant);
        }
        if ($request->filled('activate_branch')) {
            $tenant = Tenant::findOrFail($request->input('activate_branch'));
            return $this->activateBranch($tenant);
        }
        if ($request->filled('deactivate_branch')) {
            $tenant = Tenant::findOrFail($request->input('deactivate_branch'));
            return $this->deactivateBranch($tenant);
        }
        if ($request->filled('toggle_status')) {
            $tenant = Tenant::findOrFail($request->input('toggle_status'));
            return $this->toggleStatus($tenant);
        }
        if ($request->has('exit_support')) {
            return $this->exitSupport();
        }

        $hq_raw = Tenant::whereNull('parent_id')->get();
        $all_branches_raw = Tenant::whereNotNull('parent_id')->get();

        $pending_branches = Tenant::whereNotNull('parent_id')
            ->where('status', '!=', 'Active')
            ->get()
            ->map(function ($b) {
                $parent = Tenant::find($b->parent_id);
                return [
                    'id' => $b->id,
                    'name' => $b->name,
                    'parent_name' => $parent?->name ?? 'HQ',
                    'phone' => $b->phone,
                    'address' => $b->address,
                    'created_at' => $b->created_at,
                ];
            })->toArray();

        $branches_by_hq = [];
        $hq_tenants = $hq_raw->map(function ($h) use (&$branches_by_hq) {
            $branches = Tenant::where('parent_id', $h->id)->get()->toArray();
            $branches_by_hq[$h->id] = $branches;
            $data = $h->toArray();
            $data['branch_count'] = count($branches);
            $data['branches'] = $branches;
            return $data;
        })->toArray();

        $monthly_revenue = Tenant::where('payment_status', 'Paid')->sum('subscription_price') ?? 0;
        $total_revenue = Tenant::sum('subscription_price') ?? 0;

        return view('saas_dashboard', compact(
            'hq_tenants',
            'all_branches_raw',
            'monthly_revenue',
            'total_revenue',
            'pending_branches',
            'branches_by_hq'
        ));
    }

    public function supportLogin(Tenant $tenant)
    {
        session([
            'tenant_id' => $tenant->id,
            'masquerade_tenant_id' => $tenant->id,
            'masquerade_tenant_name' => $tenant->name
        ]);

        return redirect()->route('dashboard')->with('success', "Support Mode Active: You are now logged in as '{$tenant->name}'.");
    }

    public function exitSupport()
    {
        session()->forget(['masquerade_tenant_id', 'masquerade_tenant_name', 'tenant_id']);

        return redirect()->route('saas.dashboard')->with('info', "Support mode ended. Returned to Super Admin Portal.");
    }

    public function toggleStatus(Tenant $tenant)
    {
        $tenant->status = ($tenant->status === 'Active') ? 'Suspended' : 'Active';
        $tenant->save();

        return redirect()->route('saas.dashboard')->with('success', "Tenant '{$tenant->name}' status changed to {$tenant->status}.");
    }

    public function activateBranch(Tenant $tenant)
    {
        $tenant->status = 'Active';
        $tenant->save();

        return redirect()->route('saas.dashboard')->with('success', "Branch '{$tenant->name}' license activated successfully.");
    }

    public function deactivateBranch(Tenant $tenant)
    {
        $tenant->status = 'Pending';
        $tenant->save();

        return redirect()->route('saas.dashboard')->with('success', "Branch '{$tenant->name}' deactivated.");
    }

    public function updateContract(Request $request)
    {
        $tenant = Tenant::findOrFail($request->input('tenant_id'));

        $tenant->update([
            'payment_status' => $request->input('payment_status', 'Paid'),
            'subscription_price' => $request->input('subscription_price', 50000),
            'license_expiry' => $request->filled('license_expiry') ? $request->input('license_expiry') : null,
            'feature_ai_sales' => $request->has('f_ai'),
            'feature_whatsapp' => $request->has('f_wa'),
            'feature_wallet' => $request->has('f_wal'),
            'feature_data_transfer' => $request->has('f_dt'),
            'feature_erp' => $request->has('f_erp'),
            'feature_online_purchases' => $request->has('f_op'),
            'feature_vat' => $request->has('f_vat'),
            'feature_backup' => $request->has('f_bu'),
            'feature_advanced_pharmacy' => $request->has('f_adv'),
        ]);

        return redirect()->route('saas.dashboard')->with('success', "Contract & Privileges updated for '{$tenant->name}'.");
    }

    public function createTenant()
    {
        return view('saas_dashboard.create_tenant');
    }

    public function storeTenant(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'address' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'subscription_price' => 'nullable|numeric',
            'license_expiry' => 'nullable|date',
        ]);

        $validated['subscription_price'] = $validated['subscription_price'] ?? 50000;
        $validated['license_expiry'] = $request->filled('license_expiry') ? $request->input('license_expiry') : null;
        $validated['status'] = 'Active';
        $validated['payment_status'] = 'Paid';

        Tenant::create($validated);

        return redirect()->route('saas.dashboard')->with('success', 'Pharmacy Tenant registered successfully.');
    }

    public function editTenant(Tenant $tenant)
    {
        return view('saas_dashboard.edit_tenant', compact('tenant'));
    }

    public function updateTenant(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'address' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'payment_status' => 'required|in:Pending,Paid',
            'subscription_price' => 'required|numeric',
            'license_expiry' => 'nullable|date',
            'status' => 'required|in:Active,Inactive,Suspended',
        ]);

        $tenant->update($validated);

        return redirect()->route('saas.dashboard')->with('success', 'Tenant updated successfully.');
    }

    public function superAdmins()
    {
        $superAdminRole = Role::where('name', 'Super Admin')->first();
        $superAdmins = User::where('role_id', $superAdminRole?->id ?? 4)
            ->orderBy('id', 'asc')
            ->get();

        return view('saas_super_admins', compact('superAdmins'));
    }

    public function storeSuperAdmin(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|unique:users,username|max:100',
            'full_name' => 'nullable|string|max:255',
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'password' => 'required|string|min:4|confirmed',
        ]);

        $fullName = $request->input('full_name') ?: ($request->input('name') ?: $request->input('username'));

        $role = Role::where('name', 'Super Admin')->first();

        User::create([
            'username' => $validated['username'],
            'full_name' => $fullName,
            'email' => $request->input('email'),
            'password' => Hash::make($validated['password']),
            'role_id' => $role?->id ?? 4,
            'tenant_id' => null,
            'status' => 'Active',
        ]);

        return redirect()->route('saas.super_admins')->with('success', "Super Admin account '{$validated['username']}' registered successfully.");
    }

    public function deleteSuperAdmin(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('saas.super_admins')->with('error', 'Huwezi kufuta akaunti yako mwenyewe unayotumia sasa.');
        }

        $user->delete();

        return redirect()->route('saas.super_admins')->with('success', "Super Admin '{$user->username}' deleted successfully.");
    }

    public function resetSuperAdminPassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'password' => 'required|string|min:4|confirmed',
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
            'session_id' => null,
            'last_activity' => null,
        ]);

        return redirect()->route('saas.super_admins')->with('success', "Password for Super Admin '{$user->username}' updated successfully.");
    }

    public function auditLogs()
    {
        $logs = AuditLog::with(['user', 'tenant'])
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('saas_audit_logs', compact('logs'));
    }

    /**
     * Display Payment Methods Governance Portal for Super Admin.
     */
    public function paymentMethods()
    {
        $methods = PaymentMethod::orderBy('sort_order', 'asc')->get();
        $totalMethods = $methods->count();
        $activeCount = $methods->where('is_enabled', true)->count();
        $disabledCount = $totalMethods - $activeCount;

        return view('saas_payment_methods', compact('methods', 'totalMethods', 'activeCount', 'disabledCount'));
    }

    /**
     * Toggle enabled status of a payment method. Cash is strictly protected.
     */
    public function togglePaymentMethod(Request $request, PaymentMethod $paymentMethod)
    {
        // Safety lock: Cash can NEVER be disabled
        if ($paymentMethod->is_locked || strcasecmp($paymentMethod->name, 'Cash') === 0 || strcasecmp($paymentMethod->code, 'cash') === 0) {
            $msg = "Njia ya malipo ya 'Cash' (Pesa Taslimu) ni ya msingi ya mfumo na imefungwa kisheria, haiwezi kuzimwa!";
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 403);
            }
            return redirect()->back()->with('error', $msg);
        }

        $userId = auth()->id() ?? User::whereHas('role', fn($q) => $q->where('name', 'Super Admin'))->value('id') ?? 1;

        $paymentMethod->is_enabled = !$paymentMethod->is_enabled;
        $paymentMethod->updated_by = $userId;
        $paymentMethod->save();

        $actionStatus = $paymentMethod->is_enabled ? 'IMEWASHWA (ENABLED)' : 'IMEZIMWA (DISABLED)';
        $adminUsername = auth()->user()->username ?? 'Super Admin';

        AuditLog::create([
            'user_id' => $userId,
            'tenant_id' => null,
            'action' => 'UPDATE',
            'details' => "Super Admin {$adminUsername} changed payment method '{$paymentMethod->name}' to {$actionStatus}",
            'ip_address' => $request->ip(),
        ]);

        $msg = "Njia ya malipo ya '{$paymentMethod->display_name}' sasa imefanikiwa kuwa {$actionStatus}.";

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'is_enabled' => $paymentMethod->is_enabled,
                'message' => $msg,
            ]);
        }

        return redirect()->back()->with('success', $msg);
    }

    /**
     * Update payment method display name or description.
     */
    public function updatePaymentMethod(Request $request, PaymentMethod $paymentMethod)
    {
        $validated = $request->validate([
            'display_name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer',
        ]);

        $userId = auth()->id() ?? User::whereHas('role', fn($q) => $q->where('name', 'Super Admin'))->value('id') ?? 1;

        $paymentMethod->update([
            'display_name' => $validated['display_name'],
            'description' => $validated['description'] ?? $paymentMethod->description,
            'sort_order' => $validated['sort_order'] ?? $paymentMethod->sort_order,
            'updated_by' => $userId,
        ]);

        return redirect()->back()->with('success', "Taarifa za njia ya malipo '{$paymentMethod->name}' zimesasishwa kikamilifu.");
    }
}
