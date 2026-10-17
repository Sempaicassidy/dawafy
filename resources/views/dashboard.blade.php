@extends('layouts.app')

@section('content')
<!-- Executive Owner/Manager Command Center Header -->
<div class="laravel-hero-banner mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3 pb-2 border-bottom animate-fade-in">
    <div>
        <h2 class="fw-bold mb-1 text-dark">Manager Dashboard</h2>
        <p class="text-muted mb-0 small">Real-time oversight of store sales, gross & net margins (COGS), inventory health, staff performance, customer credit, and insurance claims.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('contacts.index') }}" class="btn btn-outline-secondary text-dark rounded-pill px-3 btn-sm fw-semibold">
            <i class="bi bi-journal-bookmark-fill me-1 text-primary"></i> Address Book (Directory)
        </a>
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary text-dark rounded-pill px-3 btn-sm fw-semibold">
            <i class="bi bi-bar-chart-line me-1 text-warning"></i> Financial Reports
        </a>
        <a href="{{ route('pos.index') }}" class="btn btn-primary rounded-pill px-4 btn-sm fw-bold shadow-sm">
            <i class="bi bi-cart3 me-1"></i> Open POS
        </a>
    </div>
</div>

<!-- Quick Accordion Controls & Helper Note -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="text-muted small">
        <i class="bi bi-info-circle me-1 text-primary"></i> Tap any section title to expand. The active section will open while the previous section collapses automatically.
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1" onclick="expandAllSections()">
            <i class="bi bi-arrows-expand me-1"></i> Expand All
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1" onclick="collapseAllSections()">
            <i class="bi bi-arrows-collapse me-1"></i> Collapse All
        </button>
    </div>
</div>

<div class="accordion dashboard-accordion" id="dashboardAccordion">
    <!-- ========================================================================= -->
    <!-- 1. FINANCIAL OVERVIEW & CASH FLOW                                         -->
    <!-- ========================================================================= -->
    <div class="dashboard-accordion-header d-flex justify-content-between align-items-center p-3 mb-2 bg-white rounded-4 shadow-sm"
         data-bs-toggle="collapse"
         data-bs-target="#collapseFinancial"
         aria-expanded="true"
         aria-controls="collapseFinancial">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-success-subtle text-success p-2 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                <i class="bi bi-cash-stack fs-5"></i>
            </div>
            <div>
                <h5 class="fw-bold text-dark mb-0">1. Financial Overview, Profitability & Cash Flow</h5>
                <small class="text-muted d-none d-sm-inline">Real-time revenue, gross margin after COGS, operating costs, and net liquidity</small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2 d-none d-md-inline-block">
                Today: TZS {{ number_format($metrics['today_sales']) }}
            </span>
            <div class="rounded-circle bg-light p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                <i class="bi bi-chevron-down text-secondary transition-chevron"></i>
            </div>
        </div>
    </div>

    <div id="collapseFinancial" class="collapse show" data-bs-parent="#dashboardAccordion">
        <div class="row g-3 mb-4 animate-fade-in pt-1">
    <!-- Today's Sales -->
    <div class="col-md-3">
        <div class="card shadow-sm border-0 rounded-4 p-3 h-100 bg-white">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-semibold">Today's Sales (Real-time)</span>
                <div class="rounded-circle bg-success-subtle text-success p-2" style="width: 36px; height: 36px; display: grid; place-items: center;">
                    <i class="bi bi-wallet2"></i>
                </div>
            </div>
            <h3 class="fw-bold text-dark mb-1">TZS {{ number_format($metrics['today_sales']) }}</h3>
            <div class="d-flex justify-content-between align-items-center small text-muted">
                <span>{{ $metrics['today_txns'] }} receipts issued</span>
                <span class="text-success fw-semibold"><i class="bi bi-graph-up-arrow"></i> Today</span>
            </div>
        </div>
    </div>

    <!-- Month & Week Sales -->
    <div class="col-md-3">
        <div class="card shadow-sm border-0 rounded-4 p-3 h-100 bg-white">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-semibold">This Month's Sales</span>
                <div class="rounded-circle bg-primary-subtle text-primary p-2" style="width: 36px; height: 36px; display: grid; place-items: center;">
                    <i class="bi bi-calendar-check"></i>
                </div>
            </div>
            <h3 class="fw-bold text-primary mb-1">TZS {{ number_format($metrics['month_sales']) }}</h3>
            <div class="d-flex justify-content-between align-items-center small text-muted">
                <span>This Week: TZS {{ number_format($metrics['week_sales']) }}</span>
                <span class="text-primary fw-semibold">This Month</span>
            </div>
        </div>
    </div>

    <!-- Gross Profit & Gross Margin -->
    <div class="col-md-3">
        <div class="card shadow-sm border-0 rounded-4 p-3 h-100 bg-white">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-semibold">Gross Profit</span>
                <div class="rounded-circle bg-info-subtle text-info p-2" style="width: 36px; height: 36px; display: grid; place-items: center;">
                    <i class="bi bi-pie-chart-fill"></i>
                </div>
            </div>
            <h3 class="fw-bold text-success mb-1">TZS {{ number_format($metrics['gross_profit']) }}</h3>
            <div class="d-flex justify-content-between align-items-center small text-muted">
                <span>Buying Cost (COGS): <strong class="text-dark">TZS {{ number_format($metrics['cogs']) }}</strong></span>
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">{{ $metrics['gross_margin_pct'] }}% Margin</span>
            </div>
        </div>
    </div>

    <!-- Cash Flow: Revenue vs Expenses -->
    <div class="col-md-3">
        <div class="card shadow-sm border-0 rounded-4 p-3 h-100 bg-white">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-semibold">Net Cash Flow</span>
                <div class="rounded-circle {{ $metrics['net_cash_flow'] >= 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} p-2" style="width: 36px; height: 36px; display: grid; place-items: center;">
                    <i class="bi bi-arrow-left-right"></i>
                </div>
            </div>
            <h3 class="fw-bold {{ $metrics['net_cash_flow'] >= 0 ? 'text-success' : 'text-danger' }} mb-1">
                TZS {{ number_format($metrics['net_cash_flow']) }}
            </h3>
            <div class="d-flex justify-content-between align-items-center small text-muted">
                <span>Expenses: TZS {{ number_format($metrics['total_expenses']) }}</span>
                <span class="{{ $metrics['net_profit'] >= 0 ? 'text-success' : 'text-danger' }} fw-semibold">
                    Net: TZS {{ number_format($metrics['net_profit']) }}
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Financial Detail Row: Payment Breakdown & Expenses -->
<div class="row g-3 mb-4">
    <!-- Payment Channels Breakdown -->
    <div class="col-md-6">
        <div class="card shadow-sm border-0 rounded-4 h-100">
            <div class="card-header bg-light py-2 px-3 border-bottom d-flex justify-content-between align-items-center rounded-top-4">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-credit-card me-1 text-primary"></i> Payment Channels Breakdown</h6>
                <span class="badge bg-secondary rounded-pill">{{ $metrics['payment_breakdown']->sum('count') }} Transactions</span>
            </div>
            <div class="card-body p-3">
                <div class="row g-2">
                    @forelse($metrics['payment_breakdown'] as $pm)
                    <div class="col-sm-6">
                        <div class="p-3 rounded-3 bg-light border">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold text-dark">
                                    @if($pm->payment_method === 'Cash')
                                        <i class="bi bi-cash me-1 text-success"></i> Cash
                                    @elseif($pm->payment_method === 'Mobile Money')
                                        <i class="bi bi-phone me-1 text-primary"></i> Mobile Money (M-Pesa/Tigo)
                                    @elseif($pm->payment_method === 'Card')
                                        <i class="bi bi-credit-card-2-front me-1 text-info"></i> Bank / Card
                                    @elseif($pm->payment_method === 'Insurance')
                                        <i class="bi bi-shield-check me-1 text-danger"></i> Insurance
                                    @else
                                        <i class="bi bi-wallet2 me-1 text-warning"></i> {{ $pm->payment_method }}
                                    @endif
                                </span>
                                <span class="badge bg-white text-muted border rounded-pill">{{ $pm->count }} txn</span>
                            </div>
                            <h5 class="fw-bold text-dark mb-0">TZS {{ number_format($pm->total) }}</h5>
                        </div>
                    </div>
                    @empty
                    <div class="col-12 text-center py-3 text-muted">No payment channel records available yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Operating Expenses -->
    <div class="col-md-6">
        <div class="card shadow-sm border-0 rounded-4 h-100">
            <div class="card-header bg-light py-2 px-3 border-bottom d-flex justify-content-between align-items-center rounded-top-4">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-receipt-cutoff me-1 text-danger"></i> Operating Expenses (Rent, Salaries, Licenses, etc.)</h6>
                <a href="{{ route('expenses.index') }}" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-0">Add Expense</a>
            </div>
            <div class="card-body p-3">
                @if($metrics['expenses_by_category']->isEmpty())
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-check2-circle text-success fs-3 d-block mb-1"></i>
                        No operating expenses recorded yet.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Expense Category</th>
                                    <th>Count</th>
                                    <th class="text-end">Amount (TZS)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($metrics['expenses_by_category'] as $exp)
                                <tr>
                                    <td class="fw-semibold text-dark"><i class="bi bi-dot text-danger fs-5"></i> {{ $exp->category }}</td>
                                    <td><span class="badge bg-light text-muted border rounded-pill">{{ $exp->count }}</span></td>
                                    <td class="text-end fw-bold text-danger">TZS {{ number_format($exp->total) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
    </div> <!-- End #collapseFinancial -->

    <!-- ========================================================================= -->
    <!-- 2. INVENTORY & STOCK CONTROL                                              -->
    <!-- ========================================================================= -->
    <div class="dashboard-accordion-header d-flex justify-content-between align-items-center p-3 mb-2 mt-3 bg-white rounded-4 shadow-sm"
         data-bs-toggle="collapse"
         data-bs-target="#collapseInventory"
         aria-expanded="false"
         aria-controls="collapseInventory">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-primary-subtle text-primary p-2 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                <i class="bi bi-box-seam fs-5"></i>
            </div>
            <div>
                <h5 class="fw-bold text-dark mb-0">2. Inventory & Stock Control</h5>
                <small class="text-muted d-none d-sm-inline">Mitigate expiration losses, fast-moving items, and automated reorder alerts</small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if(count($metrics['expiring_3_months']) > 0)
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-2 d-none d-md-inline-block">
                    {{ count($metrics['expiring_3_months']) }} Expiring Soon
                </span>
            @endif
            @if(count($metrics['low_stock_drugs']) > 0)
                <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-2 d-none d-md-inline-block">
                    {{ count($metrics['low_stock_drugs']) }} Low Stock
                </span>
            @endif
            <div class="rounded-circle bg-light p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                <i class="bi bi-chevron-down text-secondary transition-chevron"></i>
            </div>
        </div>
    </div>

    <div id="collapseInventory" class="collapse" data-bs-parent="#dashboardAccordion">
        <div class="row g-3 mb-4 pt-1">
    <!-- Expiry Alerts -->
    <div class="col-md-6">
        <div class="card shadow-sm border-0 rounded-4 h-100">
            <div class="card-header bg-danger-subtle text-danger py-2 px-3 border-bottom d-flex justify-content-between align-items-center rounded-top-4">
                <h6 class="mb-0 fw-bold"><i class="bi bi-exclamation-octagon-fill me-1"></i> Expiring Soon (Expiry Alerts)</h6>
                <div class="d-flex gap-1">
                    <span class="badge bg-danger rounded-pill">{{ count($metrics['expiring_3_months']) }} < 3 Months</span>
                    <span class="badge bg-warning text-dark rounded-pill">{{ $metrics['expiring_6_months_count'] }} < 6 Months</span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Medicine & Batch No.</th>
                                <th>In Stock</th>
                                <th>Expiry Date</th>
                                <th class="text-end">Required Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($metrics['expiring_3_months']->take(5) as $b)
                            <tr>
                                <td>
                                    <strong class="text-dark d-block">{{ $b->drug?->name ?? 'Medicine' }}</strong>
                                    <small class="text-muted font-monospace">Batch: {{ $b->batch_number }}</small>
                                </td>
                                <td><span class="badge bg-danger rounded-pill px-2 py-1 fw-bold">{{ $b->quantity }} Units</span></td>
                                <td>
                                    <span class="text-danger fw-bold"><i class="bi bi-calendar-x me-1"></i> {{ $b->expiry_date?->format('d M Y') }}</span>
                                    <small class="text-muted d-block">{{ $b->expiry_date ? now()->diffInDays($b->expiry_date) : 0 }} days left</small>
                                </td>
                                <td class="text-end">
                                    <span class="badge bg-warning text-dark rounded-pill px-2 py-1">Discount / Return</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">
                                    <i class="bi bi-shield-check text-success fs-4 d-block mb-1"></i>
                                    No medicines expiring within the next 3 months.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Fast-Moving Items & Low Stock -->
    <div class="col-md-6">
        <div class="card shadow-sm border-0 rounded-4 h-100">
            <div class="card-header bg-light py-2 px-3 border-bottom d-flex justify-content-between align-items-center rounded-top-4">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-fire text-danger me-1"></i> Fast-Moving Items (Top Sellers)</h6>
                <span class="badge bg-danger rounded-pill">{{ count($metrics['fast_moving_items']) }} Top Items</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Medicine Name</th>
                                <th>Units Sold</th>
                                <th class="text-end">Revenue Generated</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($metrics['fast_moving_items'] as $fast)
                            <tr>
                                <td class="fw-bold text-dark">
                                    <i class="bi bi-capsule text-primary me-1"></i> {{ $fast->drug?->name ?? 'Medicine' }}
                                    <small class="text-muted d-block">{{ $fast->drug?->brand_name ?? '' }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-primary rounded-pill px-3 py-1 fw-bold fs-7">
                                        {{ $fast->units_sold }} units
                                    </span>
                                </td>
                                <td class="text-end fw-bold text-success">
                                    TZS {{ number_format($fast->revenue_generated) }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center py-4 text-muted">Not enough sales records available yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Low stock alert ribbon at bottom -->
            @if(count($metrics['low_stock_drugs']) > 0)
            <div class="card-footer bg-warning-subtle text-dark p-2 px-3 border-top d-flex justify-content-between align-items-center rounded-bottom-4">
                <span class="small fw-bold">
                    <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i> 
                    {{ count($metrics['low_stock_drugs']) }} medicines are below the minimum reorder level.
                </span>
                <a href="{{ route('inventory.index') }}" class="btn btn-sm btn-dark rounded-pill px-3 py-0">Reorder Stock</a>
            </div>
            @endif
        </div>
    </div>
    </div> <!-- End #collapseInventory -->

    <!-- ========================================================================= -->
    <!-- 3. STAFF PERFORMANCE & AUDIT LOGS                                         -->
    <!-- ========================================================================= -->
    <div class="dashboard-accordion-header d-flex justify-content-between align-items-center p-3 mb-2 mt-3 bg-white rounded-4 shadow-sm"
         data-bs-toggle="collapse"
         data-bs-target="#collapseStaff"
         aria-expanded="false"
         aria-controls="collapseStaff">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-dark-subtle text-dark p-2 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                <i class="bi bi-shield-lock-fill fs-5"></i>
            </div>
            <div>
                <h5 class="fw-bold text-dark mb-0">3. Staff Performance & Security Audit Trail</h5>
                <small class="text-muted d-none d-sm-inline">Cashier and pharmacist sales productivity, session logs, and fraud prevention audit trail</small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2 d-none d-md-inline-block">
                {{ count($metrics['staff_sales']) }} Active Staff
            </span>
            <div class="rounded-circle bg-light p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                <i class="bi bi-chevron-down text-secondary transition-chevron"></i>
            </div>
        </div>
    </div>

    <div id="collapseStaff" class="collapse" data-bs-parent="#dashboardAccordion">
        <div class="row g-3 mb-4 pt-1">
    <!-- Sales per Staff Member -->
    <div class="col-md-6">
        <div class="card shadow-sm border-0 rounded-4 h-100">
            <div class="card-header bg-light py-2 px-3 border-bottom d-flex justify-content-between align-items-center rounded-top-4">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-person-check-fill me-1 text-primary"></i> Sales by Staff Member</h6>
                <span class="badge bg-primary rounded-pill">{{ count($metrics['staff_sales']) }} Active Staff</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Staff Member</th>
                                <th>Role</th>
                                <th>Receipts Issued</th>
                                <th class="text-end">Total Sales (TZS)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($metrics['staff_sales'] as $ss)
                            <tr>
                                <td class="fw-bold text-dark">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-secondary-subtle text-secondary p-1 text-center" style="width: 28px; height: 28px; display: grid; place-items: center;">
                                             <i class="bi bi-person"></i>
                                        </div>
                                        <span>{{ $ss->user?->full_name ?? ($ss->user?->username ?? 'Staff') }}</span>
                                    </div>
                                </td>
                                <td>
                                    @php $rName = $ss->user?->role?->name; @endphp
                                    @if($rName === 'Manager' || $rName === 'Admin')
                                        <span class="badge bg-primary px-2 py-1">Manager</span>
                                    @elseif($rName === 'Pharmacist')
                                        <span class="badge bg-success px-2 py-1">Pharmacist</span>
                                    @elseif($rName === 'Cashier')
                                        <span class="badge bg-info text-dark px-2 py-1">Cashier</span>
                                    @else
                                        <span class="badge bg-secondary px-2 py-1">{{ $rName ?? 'Staff' }}</span>
                                    @endif
                                </td>
                                <td><span class="badge bg-light text-dark border rounded-pill px-3 py-1">{{ $ss->total_txns }} tickets</span></td>
                                <td class="text-end fw-bold text-success fs-6">
                                    TZS {{ number_format($ss->total_volume) }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">No staff sales recorded yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Security Audit Trails -->
    <div class="col-md-6">
        <div class="card shadow-sm border-0 rounded-4 h-100">
            <div class="card-header bg-light py-2 px-3 border-bottom d-flex justify-content-between align-items-center rounded-top-4">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-clock-history me-1 text-danger"></i> Activity & Security Audit Trails</h6>
                <small class="text-muted">Fraud prevention & log analysis</small>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>User</th>
                                <th>Action</th>
                                <th>Details</th>
                                <th>Time & IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($metrics['audit_logs'] as $log)
                            <tr>
                                <td class="fw-semibold text-dark">{{ $log->user?->username ?? 'System' }}</td>
                                <td>
                                    <span class="badge {{ str_contains(strtolower($log->action), 'login') ? 'bg-primary' : (str_contains(strtolower($log->action), 'delete') ? 'bg-danger' : 'bg-secondary') }} rounded-pill px-2 py-1">
                                        {{ $log->action }}
                                    </span>
                                </td>
                                <td><small class="text-muted text-truncate d-inline-block" style="max-width: 180px;">{{ $log->details ?: 'Standard action' }}</small></td>
                                <td>
                                    <small class="text-muted d-block">{{ $log->created_at?->diffForHumans() }}</small>
                                    <small class="text-muted font-monospace" style="font-size: 0.65rem;">{{ $log->ip_address ?: '127.0.0.1' }}</small>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">No recent audit log entries available.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    </div> <!-- End #collapseStaff -->

    <!-- ========================================================================= -->
    <!-- 4. ACCOUNTS RECEIVABLE & INSURANCE CLAIMS                                  -->
    <!-- ========================================================================= -->
    <div class="dashboard-accordion-header d-flex justify-content-between align-items-center p-3 mb-2 mt-3 bg-white rounded-4 shadow-sm"
         data-bs-toggle="collapse"
         data-bs-target="#collapseReceivables"
         aria-expanded="false"
         aria-controls="collapseReceivables">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-warning-subtle text-warning p-2 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                <i class="bi bi-wallet-fill fs-5"></i>
            </div>
            <div>
                <h5 class="fw-bold text-dark mb-0">4. Customer Credit & Insurance Claims (Accounts Receivable)</h5>
                <small class="text-muted d-none d-sm-inline">NHIF & private fund claims reconciliation, credit balances, and debtors WhatsApp reminders</small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if($metrics['total_debt'] > 0)
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-2 d-none d-md-inline-block">
                    Owed: TZS {{ number_format($metrics['total_debt']) }}
                </span>
            @endif
            @if($metrics['insurance']['pending_count'] > 0)
                <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-2 d-none d-md-inline-block">
                    {{ $metrics['insurance']['pending_count'] }} Claims Pending
                </span>
            @endif
            <div class="rounded-circle bg-light p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                <i class="bi bi-chevron-down text-secondary transition-chevron"></i>
            </div>
        </div>
    </div>

    <div id="collapseReceivables" class="collapse" data-bs-parent="#dashboardAccordion">
        <div class="row g-3 mb-4 pt-1">
    <!-- Insurance Claims Status -->
    <div class="col-md-6">
        <div class="card shadow-sm border-0 rounded-4 h-100">
            <div class="card-header bg-light py-2 px-3 border-bottom d-flex justify-content-between align-items-center rounded-top-4">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-shield-plus me-1 text-danger"></i> Insurance Claims Status (NHIF & Private Funds)</h6>
                <a href="{{ route('insurance.index') }}" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-0">Insurance Portal</a>
            </div>
            <div class="card-body p-3">
                <div class="row g-2 mb-3">
                    <div class="col-4">
                        <div class="p-2 bg-warning-subtle text-warning border border-warning-subtle rounded-3 text-center">
                            <span class="small fw-bold d-block text-dark">Pending</span>
                            <h5 class="fw-bold text-dark mb-0">TZS {{ number_format($metrics['insurance']['pending_amount']) }}</h5>
                            <small class="text-muted">{{ $metrics['insurance']['pending_count'] }} claims</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 bg-success-subtle text-success border border-success-subtle rounded-3 text-center">
                            <span class="small fw-bold d-block text-dark">Approved</span>
                            <h5 class="fw-bold text-success mb-0">TZS {{ number_format($metrics['insurance']['approved_amount']) }}</h5>
                            <small class="text-muted">{{ $metrics['insurance']['approved_count'] }} claims</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 bg-danger-subtle text-danger border border-danger-subtle rounded-3 text-center">
                            <span class="small fw-bold d-block text-dark">Rejected</span>
                            <h5 class="fw-bold text-danger mb-0">TZS {{ number_format($metrics['insurance']['rejected_amount']) }}</h5>
                            <small class="text-muted">{{ $metrics['insurance']['rejected_count'] }} claims</small>
                        </div>
                    </div>
                </div>

                <h6 class="small fw-bold text-muted mb-2">Recent Claims:</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Claim No.</th>
                                <th>Insurance Provider</th>
                                <th>Amount (TZS)</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($metrics['insurance']['recent_claims'] as $cl)
                            <tr>
                                <td class="font-monospace small">{{ $cl->claim_number }}</td>
                                <td>{{ $cl->insuranceProvider?->name ?? 'NHIF' }}</td>
                                <td class="fw-bold">TZS {{ number_format((float)($cl->amount ?? 0)) }}</td>
                                <td>
                                    <span class="badge {{ $cl->status === 'Pending' ? 'bg-warning text-dark' : ($cl->status === 'Rejected' ? 'bg-danger' : 'bg-success') }} rounded-pill px-2 py-1">
                                        {{ $cl->status }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-3 text-muted">No insurance claims currently on record.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Debtors Ledger -->
    <div class="col-md-6">
        <div class="card shadow-sm border-0 rounded-4 h-100">
            <div class="card-header bg-light py-2 px-3 border-bottom d-flex justify-content-between align-items-center rounded-top-4">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-person-exclamation me-1 text-warning"></i> Customer Credit & Debtors Ledger</h6>
                <span class="badge bg-danger rounded-pill">Total Owed: TZS {{ number_format($metrics['total_debt']) }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Customer / Patient Name</th>
                                <th>Phone Number</th>
                                <th>Outstanding Debt</th>
                                <th class="text-end">Send Reminder</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($metrics['debtors'] as $deb)
                            <tr>
                                <td class="fw-bold text-dark">
                                    {{ $deb->name }}
                                    @if(!empty($deb->chronic_condition))
                                        <small class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill ms-1">{{ $deb->chronic_condition }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if(!empty($deb->phone))
                                        <a href="tel:{{ $deb->phone }}" class="text-decoration-none fw-semibold text-dark">
                                            <i class="bi bi-telephone-fill text-success me-1"></i> {{ $deb->phone }}
                                        </a>
                                    @else
                                        <span class="text-muted small">No Phone</span>
                                    @endif
                                </td>
                                <td>
                                    <strong class="text-danger fs-6">TZS {{ number_format((float)($deb->outstanding_debt ?? 0)) }}</strong>
                                </td>
                                <td class="text-end">
                                    @if(!empty($deb->phone))
                                        @php
                                            $cleanDebtPhone = preg_replace('/[^0-9]/', '', $deb->phone);
                                            if(str_starts_with($cleanDebtPhone, '0')) $cleanDebtPhone = '255' . substr($cleanDebtPhone, 1);
                                            $msgDebt = "Hello {$deb->name}, this is a gentle reminder regarding your outstanding medicine balance of TZS " . number_format((float)($deb->outstanding_debt ?? 0)) . " at Dawafy Pharmacy. Please settle via our store payment channels. Thank you!";
                                        @endphp
                                        <a href="https://wa.me/{{ $cleanDebtPhone }}?text={{ urlencode($msgDebt) }}" target="_blank" class="btn btn-sm btn-success rounded-pill px-3 py-1" title="Send WhatsApp Reminder">
                                            <i class="bi bi-whatsapp me-1"></i> Remind
                                        </a>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">
                                    <i class="bi bi-check-circle text-success fs-3 d-block mb-1"></i>
                                    No customers with outstanding credit balances!
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-light p-2 px-3 border-top d-flex justify-content-between align-items-center rounded-bottom-4">
                <small class="text-muted">Access complete customer directory or record new credit:</small>
                <a href="{{ route('contacts.index') }}?filter=debt" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-0">Full Directory</a>
            </div>
        </div>
    </div>
    </div> <!-- End #collapseReceivables -->
</div> <!-- End #dashboardAccordion -->

<style>
.dashboard-accordion-header {
    cursor: pointer;
    user-select: none;
    transition: all 0.25s ease-in-out;
    border: 1px solid #e2e8f0;
}
.dashboard-accordion-header:hover {
    background-color: #f8fafc !important;
    border-color: #cbd5e1 !important;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05) !important;
}
.dashboard-accordion-header[aria-expanded="true"] {
    background: #ffffff !important;
    border-color: #3b82f6 !important;
    box-shadow: 0 4px 14px rgba(59, 130, 246, 0.12) !important;
}
.dashboard-accordion-header .transition-chevron {
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.dashboard-accordion-header[aria-expanded="true"] .transition-chevron {
    transform: rotate(180deg);
    color: #2563eb !important;
}
.dashboard-accordion-header.collapsed .transition-chevron {
    transform: rotate(0deg);
    color: #64748b !important;
}
</style>

<script>
function expandAllSections() {
    const collapsibles = document.querySelectorAll('#dashboardAccordion .collapse');
    collapsibles.forEach(el => {
        el.removeAttribute('data-bs-parent');
        const bsCol = bootstrap.Collapse.getOrCreateInstance(el, { toggle: false });
        bsCol.show();
    });
    document.querySelectorAll('.dashboard-accordion-header').forEach(h => {
        h.setAttribute('aria-expanded', 'true');
        h.classList.remove('collapsed');
    });
}

function collapseAllSections() {
    const collapsibles = document.querySelectorAll('#dashboardAccordion .collapse');
    collapsibles.forEach(el => {
        el.setAttribute('data-bs-parent', '#dashboardAccordion');
        const bsCol = bootstrap.Collapse.getOrCreateInstance(el, { toggle: false });
        bsCol.hide();
    });
    document.querySelectorAll('.dashboard-accordion-header').forEach(h => {
        h.setAttribute('aria-expanded', 'false');
        h.classList.add('collapsed');
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const accordion = document.getElementById('dashboardAccordion');
    if (accordion) {
        accordion.addEventListener('show.bs.collapse', function (e) {
            // Restore data-bs-parent when interacting individually
            e.target.setAttribute('data-bs-parent', '#dashboardAccordion');
            const header = document.querySelector(`[data-bs-target="#${e.target.id}"]`);
            if (header) {
                header.setAttribute('aria-expanded', 'true');
                header.classList.remove('collapsed');
            }
        });
        accordion.addEventListener('hide.bs.collapse', function (e) {
            const header = document.querySelector(`[data-bs-target="#${e.target.id}"]`);
            if (header) {
                header.setAttribute('aria-expanded', 'false');
                header.classList.add('collapsed');
            }
        });
    }
});
</script>
@endsection
