<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title>Dawafy</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('assets/images/logo.png') }}">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Executive Laravel Design System & Theme -->
    <link href="{{ asset('css/daawafy-theme.css') }}?v={{ file_exists(public_path('css/daawafy-theme.css')) ? filemtime(public_path('css/daawafy-theme.css')) : '1.0' }}" rel="stylesheet">
    <link href="{{ asset('css/laravel-daawafy-ui.css') }}?v={{ file_exists(public_path('css/laravel-daawafy-ui.css')) ? filemtime(public_path('css/laravel-daawafy-ui.css')) : '1.0' }}" rel="stylesheet">

    <!-- Global Printable Layout & Thermal Media Rules -->
    <style>
        @media print {
            .navbar-laravel,
            .sidebar-laravel,
            #sidebar,
            .sidebar-backdrop,
            .bg-warning,
            .laravel-hero-banner,
            .btn,
            .btn-close,
            .card-header-reports,
            #reportTabs,
            .no-print,
            header,
            footer,
            nav {
                display: none !important;
            }

            body, html {
                background: #ffffff !important;
                color: #000000 !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
            }

            .col-md-10, .col-md-12, .flex-grow-1, .p-4 {
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                flex: 0 0 100% !important;
            }

            .card-laravel, .card {
                border: none !important;
                box-shadow: none !important;
                background: transparent !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .table {
                width: 100% !important;
                border-collapse: collapse !important;
            }

            .table th, .table td {
                color: #000000 !important;
                border: 1px solid #dee2e6 !important;
                padding: 8px !important;
            }

            .d-none:not(.report-panel.d-none) {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <?php 
    $role = session('role') ?? (Auth::check() ? (Auth::user()->role?->name ?? 'Manager') : 'Manager');
    $actual_role = session('actual_role') ?? $role;
    $is_masquerade = !empty(session('masquerade_tenant_id'));
    $isSuperAdmin = (($actual_role === 'Super Admin') || ($role === 'Super Admin'));
    $isManager = in_array($actual_role, ['Manager', 'Admin']) || $isSuperAdmin;
    ?>
    <div class="d-flex flex-column min-vh-100">
        <!-- Executive Top Navigation Bar (Laravel) -->
        <nav class="navbar-laravel px-2 px-md-4 py-2 py-md-3">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2 gap-md-3">
                    <button class="btn btn-sm btn-outline-secondary d-md-none p-1 px-2" onclick="toggleSidebar()" aria-label="Open Navigation Menu">
                        <i class="bi bi-list fs-5"></i>
                    </button>
                    <div class="d-flex align-items-center gap-2">
                        <?php if(!empty($SYS_SETTINGS['logo_path']) && !($isSuperAdmin && !$is_masquerade)): ?>
                            <img src="<?= htmlspecialchars(asset($SYS_SETTINGS['logo_path'])) ?>" alt="Logo" style="height: 32px; max-width: 100px; object-fit: contain; border-radius: 4px;">
                        <?php else: ?>
                            <div class="p-1 p-md-2 text-white d-flex align-items-center justify-content-center" style="background: #212529; width: 30px; height: 30px; border-radius: 3px;">
                                <i class="bi bi-capsule fs-5"></i>
                            </div>
                        <?php endif; ?>
                        <h5 class="mb-0 fw-bold text-dark text-truncate navbar-brand-title">
                            <?= ($isSuperAdmin && !$is_masquerade) ? 'Daawafy SaaS HQ' : (isset($SYS_SETTINGS['company_name']) ? htmlspecialchars($SYS_SETTINGS['company_name']) : 'Dawafy') ?>
                        </h5>
                        <small class="text-muted ms-1 d-none d-lg-inline" style="font-size: 0.75rem;">| <?= ($isSuperAdmin && !$is_masquerade) ? 'Platform Control' : 'Pharmacy Management' ?></small>
                    </div>
                </div>
                
                <div class="d-flex align-items-center gap-1 gap-md-2">
                    <?php 
                        $userRoles = Auth::check() ? (Auth::user()->all_roles ?? [$role]) : [$role];
                        $canSwitch = $isManager || count($userRoles) > 1;
                    ?>
                    <?php if($canSwitch): ?>
                    <form method="POST" action="{{ route('switch_role') }}" class="mb-0">
                        @csrf
                        <select name="active_role" class="form-select form-select-sm" style="border-radius: 3px; font-size: 0.8125rem;" onchange="this.form.submit()">
                            <?php if($isManager): ?>
                                <option value="Manager" <?= in_array($role, ['Manager', 'Admin']) ? 'selected' : '' ?>>Manager Mode</option>
                                <option value="Pharmacist" <?= $role === 'Pharmacist' ? 'selected' : '' ?>>Pharmacist Mode</option>
                                <option value="Cashier" <?= $role === 'Cashier' ? 'selected' : '' ?>>Cashier Mode</option>
                            <?php else: ?>
                                <?php foreach($userRoles as $uRole): ?>
                                    <option value="<?= htmlspecialchars($uRole) ?>" <?= $role === $uRole ? 'selected' : '' ?>><?= htmlspecialchars($uRole) ?> Mode</option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </form>
                    <?php endif; ?>

                    <div class="px-2 py-1 bg-light border text-secondary role-badge d-none d-sm-block" style="border-radius: 3px; font-size: 0.8125rem;">
                        <i class="bi bi-person me-1"></i> <strong><?= htmlspecialchars($role === 'Admin' ? 'Manager' : $role) ?></strong>
                    </div>

                    <form id="global-logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('global-logout-form').submit();" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1 p-1 px-2" title="Sign Out" style="border-radius: 3px; font-size: 0.8125rem;">
                        <i class="bi bi-box-arrow-right"></i> <span class="d-none d-md-inline">Sign Out</span>
                    </a>
                </div>
            </div>
        </nav>

        <?php if(!empty(session('masquerade_tenant_id'))): ?>
            <div class="bg-warning text-dark px-4 py-2 d-flex justify-content-between align-items-center border-bottom shadow-sm">
                <div class="small fw-bold">
                    <i class="bi bi-shield-exclamation me-1 fs-6"></i> SUPPORT MODE ACTIVE: You are currently masquerading as Manager for <strong><?= htmlspecialchars($SYS_SETTINGS['company_name'] ?? 'Pharmacy Tenant') ?></strong>.
                </div>
                <a href="{{ route('saas.exit_support') }}" class="btn btn-sm btn-dark px-3 py-1 fw-bold" style="border-radius: 4px;">
                    <i class="bi bi-x-circle me-1"></i> End Support Mode
                </a>
            </div>
        <?php endif; ?>
        
        <div class="app-container">
            <!-- Mobile Sidebar Backdrop -->
            <div class="sidebar-backdrop" id="sidebarBackdrop" onclick="toggleSidebar()"></div>

            <!-- Professional Human-Crafted Enterprise Sidebar -->
            <?php 
            global $SYS_SETTINGS, $pdo;
            if (empty($SYS_SETTINGS) && Auth::check() && Auth::user()?->tenant_id) {
                $SYS_SETTINGS = \App\Models\Tenant::find(Auth::user()->tenant_id)?->toArray() ?: [];
            }

            $hasFeature = function($key) use ($SYS_SETTINGS, $isSuperAdmin) {
                if ($isSuperAdmin) return true;
                return !empty($SYS_SETTINGS[$key]);
            };
            ?>
            <aside class="sidebar-traditional" id="sidebar">
                <div class="sidebar-nav-list">
                    <!-- Mobile Drawer Header -->
                    <div class="d-flex d-md-none align-items-center justify-content-between px-3 py-3 border-bottom mb-2 bg-light">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-capsule-pill text-primary fs-5"></i>
                            <span class="fw-bold text-dark fs-6">Navigation Menu</span>
                        </div>
                        <button type="button" class="btn btn-sm btn-light border p-1 px-2" onclick="toggleSidebar()" aria-label="Close menu">
                            <i class="bi bi-x-lg fs-6"></i>
                        </button>
                    </div>

                    @if($isSuperAdmin && !$is_masquerade)
                        {{-- SaaS Platform Super Admin Dedicated Navigation --}}
                        <div class="sidebar-heading">SaaS Multi-Tenant Hub</div>
                        <a href="{{ route('saas.dashboard') }}" class="sidebar-link {{ request()->routeIs('saas.dashboard') ? 'active' : '' }}">
                            <i class="bi bi-speedometer2"></i> <span>Platform Overview</span>
                        </a>
                        <a href="{{ route('saas.dashboard') }}#saasTable" class="sidebar-link">
                            <i class="bi bi-buildings"></i> <span>Pharmacies & Branches</span>
                        </a>
                        <a href="{{ route('saas.dashboard') }}#pendingBranches" class="sidebar-link">
                            <i class="bi bi-shield-check"></i> <span>Branch Approvals</span>
                        </a>

                        <div class="sidebar-heading">Platform Governance</div>
                        <a href="{{ route('saas.super_admins') }}" class="sidebar-link {{ request()->routeIs('saas.super_admins*') ? 'active' : '' }}">
                            <i class="bi bi-person-fill-gear"></i> <span>Super Admin Accounts</span>
                        </a>
                        <a href="{{ route('saas.payment_methods') }}" class="sidebar-link {{ request()->routeIs('saas.payment_methods*') ? 'active' : '' }}">
                            <i class="bi bi-credit-card-2-front"></i> <span>Payment Methods Settings</span>
                        </a>
                        <a href="{{ route('saas.audit_logs') }}" class="sidebar-link {{ request()->routeIs('saas.audit_logs*') ? 'active' : '' }}">
                            <i class="bi bi-clock-history"></i> <span>Global Audit Trail</span>
                        </a>
                        <a href="{{ route('addons.backup') }}" class="sidebar-link {{ request()->routeIs('addons.backup*') ? 'active' : '' }}">
                            <i class="bi bi-database-check"></i> <span>System & DB Backups</span>
                        </a>

                        <div class="sidebar-heading">Store Operations</div>
                        <div class="mx-3 my-2 p-2 rounded-3 bg-light border text-muted" style="font-size: 0.75rem; line-height: 1.35;">
                            <div class="fw-bold text-dark mb-1"><i class="bi bi-info-circle text-primary me-1"></i> Store Operations</div>
                            To manage a specific pharmacy branch (POS, Inventory, Staff), click <strong>"Support Login"</strong> in the pharmacies list.
                        </div>

                    @elseif($role === 'Pharmacist')
                        {{-- ========================================================================= --}}
                        {{-- 💊 PHARMACIST DEDICATED SIDEBAR (Clinical & Medicine Operations)          --}}
                        {{-- ========================================================================= --}}
                        
                        {{-- 🛒 1. Core Duties --}}
                        <div class="sidebar-heading">Core Duties</div>
                        <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard*') ? 'active' : '' }}">
                            <i class="bi bi-speedometer2"></i> <span>Dashboard</span>
                        </a>
                        <a href="{{ route('pos.index') }}" class="sidebar-link {{ request()->routeIs('pos*') ? 'active' : '' }}">
                            <i class="bi bi-capsule"></i> <span>Dispense Medicine (POS)</span>
                        </a>
                        <a href="{{ route('prescriptions.index') }}" class="sidebar-link {{ request()->routeIs('prescriptions*') ? 'active' : '' }}">
                            <i class="bi bi-file-earmark-medical"></i> <span>Prescriptions (POM)</span>
                        </a>

                        {{-- 📦 2. Dispensary Stock & Refills --}}
                        <div class="sidebar-heading">Dispensary Stock & Refills</div>
                        <a href="{{ route('dashboard') }}" class="sidebar-link">
                            <i class="bi bi-search"></i> <span>Global Drug Finder</span>
                        </a>
                        <a href="{{ route('pos.index') }}" class="sidebar-link">
                            <i class="bi bi-box-arrow-in-down"></i> <span>Request Stock Refill</span>
                        </a>

                        {{-- 🏥 3. Patients & Insurance --}}
                        <div class="sidebar-heading">Patients & Insurance</div>
                        <a href="{{ route('contacts.index') }}" class="sidebar-link {{ request()->routeIs('contacts*') || request()->routeIs('patients*') ? 'active' : '' }}">
                            <i class="bi bi-person-lines-fill"></i> <span>Patient Records</span>
                        </a>
                        <a href="{{ route('insurance.index') }}" class="sidebar-link {{ request()->routeIs('insurance*') ? 'active' : '' }}">
                            <i class="bi bi-shield-plus"></i> <span>Insurance Claims</span>
                        </a>

                        {{-- 📊 4. My Account --}}
                        <div class="sidebar-heading">My Account</div>
                        <a href="{{ route('reports.index', ['tab' => 'sales', 'my_sales' => '1']) }}" class="sidebar-link {{ request()->routeIs('reports*') && request('my_sales') == '1' ? 'active' : '' }}">
                            <i class="bi bi-receipt"></i> <span>My Shift Sales</span>
                        </a>

                    @elseif($role === 'Cashier')
                        {{-- ========================================================================= --}}
                        {{-- 💵 CASHIER DEDICATED SIDEBAR (Transactions & Shift Cash Control)          --}}
                        {{-- ========================================================================= --}}
                        
                        {{-- 💰 1. Transactions --}}
                        <div class="sidebar-heading">Transactions</div>
                        <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard*') ? 'active' : '' }}">
                            <i class="bi bi-speedometer2"></i> <span>Dashboard</span>
                        </a>
                        <a href="{{ route('pos.receive_payments') }}" class="sidebar-link {{ request()->routeIs('pos.receive_payments*') ? 'active' : '' }}">
                            <i class="bi bi-cash-coin"></i> <span>Receive Payments</span>
                        </a>
                        <a href="{{ route('reports.index', ['tab' => 'sales', 'my_sales' => '1']) }}" class="sidebar-link {{ request()->routeIs('reports*') && request('my_sales') == '1' ? 'active' : '' }}">
                            <i class="bi bi-receipt-cutoff"></i> <span>Receipt History</span>
                        </a>

                        {{-- 🏦 2. Shift & Cash Control --}}
                        <div class="sidebar-heading">Shift & Cash Control</div>
                        <a href="{{ route('expenses.index') }}" class="sidebar-link {{ request()->routeIs('expenses*') ? 'active' : '' }}">
                            <i class="bi bi-wallet2"></i> <span>Record Expense</span>
                        </a>
                        <a href="{{ route('shifts.index') }}" class="sidebar-link {{ request()->routeIs('shifts*') ? 'active' : '' }}">
                            <i class="bi bi-clock-history"></i> <span>Close Shift</span>
                        </a>

                    @else
                        {{-- ========================================================================= --}}
                        {{-- 👑 MANAGER / OWNER DEDICATED SIDEBAR (Executive Business Control)          --}}
                        {{-- ========================================================================= --}}
                        @if($is_masquerade)
                        <div class="sidebar-heading text-warning">Support Mode</div>
                        <a href="{{ route('saas.exit_support') }}" class="sidebar-link text-warning fw-bold mb-2">
                            <i class="bi bi-box-arrow-left"></i> <span>Exit to SaaS Portal</span>
                        </a>
                        @endif

                        {{-- 🎛️ 1. Core Actions & Overview --}}
                        <div class="sidebar-heading">Core Actions & Overview</div>
                        <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard*') ? 'active' : '' }}">
                            <i class="bi bi-speedometer2"></i> <span>Dashboard</span>
                        </a>
                        <a href="{{ route('pos.index') }}" class="sidebar-link {{ request()->routeIs('pos*') ? 'active' : '' }}">
                            <i class="bi bi-cart3"></i> <span>Point of Sale (POS)</span>
                        </a>

                        {{-- 📦 2. Inventory Management --}}
                        <div class="sidebar-heading">Inventory Management</div>
                        <a href="{{ route('inventory.index') }}" class="sidebar-link {{ request()->routeIs('inventory*') && !request()->has('tab') ? 'active' : '' }}">
                            <i class="bi bi-box-seam"></i> <span>Inventory & Stock</span>
                        </a>
                        <a href="{{ route('inventory.index', ['tab' => 'out_of_stock']) }}" class="sidebar-link {{ request()->routeIs('inventory*') && in_array(request('tab'), ['out_of_stock', 'low_stock', 'reorders']) ? 'active' : '' }}">
                            <i class="bi bi-exclamation-triangle"></i> <span>Low Stock & Reorders</span>
                        </a>
                        <a href="{{ route('inventory.index', ['tab' => 'expired']) }}" class="sidebar-link {{ request()->routeIs('inventory*') && in_array(request('tab'), ['expired', 'expiring']) ? 'active' : '' }}">
                            <i class="bi bi-calendar-x"></i> <span>Expiry Tracker</span>
                        </a>
                        <a href="{{ route('suppliers.index') }}" class="sidebar-link {{ request()->routeIs('suppliers*') || request()->routeIs('procurement*') ? 'active' : '' }}">
                            <i class="bi bi-truck"></i> <span>Suppliers & Orders</span>
                        </a>

                        {{-- 💰 3. Financials & Analytics --}}
                        <div class="sidebar-heading">Financials & Analytics</div>
                        <a href="{{ route('reports.index', ['tab' => 'sales']) }}" class="sidebar-link {{ request()->routeIs('reports*') && request('tab') !== 'financial' && !request()->routeIs('reports.audit_logs*') ? 'active' : '' }}">
                            <i class="bi bi-bar-chart-line"></i> <span>Sales Reports</span>
                        </a>
                        <a href="{{ route('reports.index', ['tab' => 'financial']) }}" class="sidebar-link {{ request()->routeIs('reports*') && in_array(request('tab'), ['financial', 'profit_loss', 'pnl']) ? 'active' : '' }}">
                            <i class="bi bi-pie-chart"></i> <span>Profit & Loss (P&L)</span>
                        </a>
                        <a href="{{ route('expenses.index') }}" class="sidebar-link {{ request()->routeIs('expenses*') ? 'active' : '' }}">
                            <i class="bi bi-cash-stack"></i> <span>Operating Expenses</span>
                        </a>

                        {{-- 📑 4. Clients, Insurance & Credit --}}
                        <div class="sidebar-heading">Clients, Insurance & Credit</div>
                        <a href="{{ route('contacts.index') }}" class="sidebar-link {{ request()->routeIs('contacts*') && request('filter') !== 'debt' && !request()->routeIs('patients*') ? 'active' : '' }}">
                            <i class="bi bi-person-lines-fill"></i> <span>Customers & Patients</span>
                        </a>
                        <a href="{{ route('insurance.index') }}" class="sidebar-link {{ request()->routeIs('insurance*') ? 'active' : '' }}">
                            <i class="bi bi-shield-plus"></i> <span>Insurance & Claims</span>
                        </a>
                        <a href="{{ route('contacts.index', ['filter' => 'debt']) }}" class="sidebar-link {{ request()->routeIs('contacts*') && request('filter') === 'debt' ? 'active' : '' }}">
                            <i class="bi bi-wallet2"></i> <span>Customer Credit & Debts</span>
                        </a>

                        {{-- 👥 5. Administration & Security --}}
                        <div class="sidebar-heading">Administration & Security</div>
                        <a href="{{ route('users.index') }}" class="sidebar-link {{ request()->routeIs('users*') ? 'active' : '' }}">
                            <i class="bi bi-people"></i> <span>Staff & Permissions</span>
                        </a>
                        <a href="{{ route('reports.audit_logs') }}" class="sidebar-link {{ request()->routeIs('reports.audit_logs*') ? 'active' : '' }}">
                            <i class="bi bi-clock-history"></i> <span>Audit Logs & History</span>
                        </a>
                        <a href="{{ route('settings.index') }}" class="sidebar-link {{ request()->routeIs('settings*') ? 'active' : '' }}">
                            <i class="bi bi-gear"></i> <span>System Settings</span>
                        </a>
                        <a href="{{ route('branches.index') }}" class="sidebar-link {{ request()->routeIs('branches*') ? 'active' : '' }}">
                            <i class="bi bi-diagram-3"></i> <span>Store Branches</span>
                        </a>
                        <?php if($isSuperAdmin): ?>
                        <a href="{{ route('saas.dashboard') }}" class="sidebar-link {{ request()->routeIs('saas*') ? 'active' : '' }}">
                            <i class="bi bi-shield-lock"></i> <span>SaaS Admin Portal</span>
                        </a>
                        <?php endif; ?>
                    @endif
                </div>

                <!-- Dignified User Profile Footer -->
                <div class="sidebar-user-footer">
                    <div class="sidebar-user-card mb-2">
                        <div class="d-flex align-items-center gap-2 overflow-hidden">
                            <div class="rounded-circle d-flex align-items-center justify-content-center bg-secondary text-white" style="width: 28px; height: 28px; font-size: 0.75rem; flex-shrink: 0;">
                                <i class="bi bi-person"></i>
                            </div>
                            <div class="overflow-hidden">
                                <div class="fw-semibold text-truncate text-dark" style="font-size: 0.8125rem;">
                                    <?= Auth::check() ? htmlspecialchars(Auth::user()->name ?? 'Staff') : 'Staff Member' ?>
                                </div>
                                <div class="text-muted text-truncate" style="font-size: 0.7rem;">
                                    <?= htmlspecialchars($role) ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('global-logout-form').submit();" class="sidebar-link text-secondary">
                        <i class="bi bi-box-arrow-right"></i> <span>Sign Out</span>
                    </a>
                </div>
            </aside>
            
            <!-- Main Content Canvas -->
            <main class="app-main-content">
                @yield('content')
                {!! $content ?? '' !!}
            </main>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- React 18 & ReactDOM -->
    <script src="https://unpkg.com/react@18/umd/react.production.min.js" crossorigin></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js" crossorigin></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
    <!-- Daawafy jQuery Pop-Up Notification Engine -->
    <script src="{{ asset('js/daawafy-notifications.js') }}"></script>
    <script type="text/babel" src="{{ asset('js/components/ReactPOSWidget.js') }}"></script>

    <!-- Mobile-First Interactive Sidebar Logic -->
    <script>
        window.toggleSidebar = function() {
            var sidebar = document.getElementById('sidebar');
            var backdrop = document.getElementById('sidebarBackdrop');
            if (sidebar) {
                var isOpen = sidebar.classList.toggle('show');
                if (backdrop) {
                    if (isOpen) {
                        backdrop.classList.add('show');
                        document.body.style.overflow = 'hidden';
                    } else {
                        backdrop.classList.remove('show');
                        document.body.style.overflow = '';
                    }
                }
            }
        };

        document.addEventListener('DOMContentLoaded', function() {
            var sidebar = document.getElementById('sidebar');
            var backdrop = document.getElementById('sidebarBackdrop');
            if (sidebar) {
                sidebar.querySelectorAll('.sidebar-link').forEach(function(link) {
                    link.addEventListener('click', function() {
                        if (window.innerWidth < 768) {
                            sidebar.classList.remove('show');
                            if (backdrop) backdrop.classList.remove('show');
                            document.body.style.overflow = '';
                        }
                    });
                });
            }
        });
    </script>
</body>
</html>
