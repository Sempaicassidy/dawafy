@php
    $pendingTxns = $metrics['pending_payments'] ?? collect();
@endphp
<!-- Cashier Shift & Cash Flow Command Center Header -->
<div class="laravel-hero-banner mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3 pb-2 border-bottom animate-fade-in">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill fw-bold" style="font-size: 0.75rem;">
                <i class="bi bi-wallet2 me-1"></i> Cashier Revenue Desk
            </span>
            <span class="text-muted small">| Shift & Drawer Control</span>
        </div>
        <h2 class="fw-bold mb-1 text-dark">Cashier Dashboard</h2>
        <p class="text-muted mb-0 small">Real-time cash drawer tracking, payment method breakdowns, petty expenses, and end-of-shift reconciliation.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary text-dark rounded-pill px-3 btn-sm fw-semibold">
            <i class="bi bi-dash-circle me-1 text-danger"></i> Record Petty Expense
        </a>
        <a href="{{ route('shifts.index') }}" class="btn btn-outline-secondary text-dark rounded-pill px-3 btn-sm fw-semibold">
            <i class="bi bi-clock-history me-1 text-info"></i> Shift Drawer Logs
        </a>
        <a href="{{ route('pos.receive_payments') }}" class="btn btn-success rounded-pill px-4 btn-sm fw-bold shadow-sm">
            <i class="bi bi-cash-coin me-1"></i> Receive Payments (<span class="cashierPendingCountBadge">{{ $pendingTxns->count() }}</span>)
        </a>
    </div>
</div>

<!-- ========================================================================= -->
<!-- 0. PENDING PAYMENTS QUEUE (From Pharmacists Dispense Workflow)            -->
<!-- ========================================================================= -->
<div id="pendingPaymentsSection" class="card-laravel mb-4 bg-white border border-start border-4 border-warning shadow-sm animate-fade-in">
    <div class="card-header bg-white border-bottom py-3 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-warning text-dark fw-bold px-2 py-1 rounded-pill" style="font-size: 0.72rem;">
                    <i class="bi bi-clock-history me-1"></i> Cashier Counter Queue
                </span>
                <span class="text-muted small">| Separated Workflow</span>
            </div>
            <h5 class="fw-bold text-dark mb-0">
                <i class="bi bi-cash-coin text-warning me-2"></i> Receive Payments / Pending Orders (<span class="cashierPendingCountBadge">{{ $pendingTxns->count() }}</span>)
            </h5>
            <small class="text-muted">Prescription orders sent by Pharmacists. Receive patient payments, approve below, and print receipt. Pharmacist will then dispense medicines.</small>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold" onclick="refreshCashierPendingQueue()">
                <i class="bi bi-arrow-clockwise me-1"></i> Refresh Queue
            </button>
        </div>
    </div>
    <div class="card-body p-3">
        <div id="cashierPendingTableContainer">
            @if($pendingTxns->isEmpty())
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-check2-circle fs-2 text-success d-block mb-1"></i>
                    <p class="mb-0 fw-semibold">No pending payments right now.</p>
                    <small class="text-muted">When a pharmacist clicks "Send to Cashier" in POS, orders will appear here instantly.</small>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-muted small">
                            <tr>
                                <th>Invoice / Order</th>
                                <th>Patient / Customer</th>
                                <th>Prescribed By</th>
                                <th>Total Amount</th>
                                <th>Time</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody id="cashierPendingTbody">
                            @foreach($pendingTxns as $pt)
                                <tr id="cpt-row-{{ $pt->id }}">
                                    <td>
                                        <strong class="text-dark">{{ $pt->transaction_id ?? ('#TXN-' . $pt->id) }}</strong>
                                        <span class="badge bg-warning text-dark ms-1" style="font-size: 0.68rem;">Pending Payment</span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $pt->customer?->name ?? 'Walk-in Patient' }}</div>
                                        <small class="text-muted">{{ $pt->customer?->phone ?? 'No phone' }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-secondary border">
                                            <i class="bi bi-person-badge me-1"></i> {{ $pt->pharmacist?->name ?? 'Pharmacist' }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong class="text-success fs-6 tabular-nums">TZS {{ number_format($pt->total_amount) }}</strong>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $pt->created_at->format('H:i') }} ({{ $pt->created_at->diffForHumans() }})</small>
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-success fw-bold rounded-pill px-3 shadow-sm" onclick="openCashierPaymentModal({{ $pt->id }})">
                                            <i class="bi bi-credit-card me-1"></i> Receive Payment
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- 1. SHIFT CASH DRAWER STATUS (Hali ya Droo ya Pesa)                        -->
<!-- ========================================================================= -->
<div class="row g-3 mb-4 animate-fade-in">
    <!-- Active Shift Status -->
    <div class="col-sm-6 col-lg-3">
        <div class="card-laravel p-3 h-100 bg-white border border-start border-4 border-primary shadow-sm">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold">Current Shift</span>
                @if($metrics['cashier']['active_shift'])
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1" style="font-size: 0.7rem;">
                        <i class="bi bi-record-circle me-1"></i> Active
                    </span>
                @else
                    <span class="badge bg-secondary-subtle text-muted border rounded-pill px-2 py-1" style="font-size: 0.7rem;">
                        Not Started
                    </span>
                @endif
            </div>
            @if($metrics['cashier']['active_shift'])
                <h4 class="fw-bold text-dark mb-1">Shift #{{ $metrics['cashier']['active_shift']->id }}</h4>
                <div class="text-muted small" style="font-size: 0.75rem;">
                    Started: {{ \Carbon\Carbon::parse($metrics['cashier']['active_shift']->start_time)->format('H:i, d M') }}
                </div>
            @else
                <h5 class="fw-bold text-muted mb-1">No Active Shift</h5>
                <a href="{{ route('shifts.index') }}" class="btn btn-sm btn-outline-primary rounded-pill mt-1 py-0 px-2" style="font-size: 0.75rem;">
                    <i class="bi bi-play-circle me-1"></i> Open Shift
                </a>
            @endif
        </div>
    </div>

    <!-- Opening Float (Pesa ya Kuanzia) -->
    <div class="col-sm-6 col-lg-3">
        <div class="card-laravel p-3 h-100 bg-white border border-start border-4 border-secondary shadow-sm">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold">Opening Float</span>
                <div class="rounded-circle bg-light text-secondary p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                    <i class="bi bi-safe fs-5"></i>
                </div>
            </div>
            <h3 class="fw-bold text-secondary mb-1">TZS {{ number_format($metrics['cashier']['opening_float']) }}</h3>
            <div class="text-muted" style="font-size: 0.75rem;">
                Morning start balance in cash drawer
            </div>
        </div>
    </div>

    <!-- Cash Sales Collected (Pesa ya Mauzo ya Taslimu) -->
    <div class="col-sm-6 col-lg-3">
        <div class="card-laravel p-3 h-100 bg-white border border-start border-4 border-success shadow-sm">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold">Shift Cash Collected</span>
                <div class="rounded-circle bg-success-subtle text-success p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                    <i class="bi bi-cash-stack fs-5"></i>
                </div>
            </div>
            <h3 class="fw-bold text-success mb-1">TZS {{ number_format($metrics['cashier']['shift_cash_sales']) }}</h3>
            <div class="text-muted" style="font-size: 0.75rem;">
                Direct cash received during active shift
            </div>
        </div>
    </div>

    <!-- Expected Drawer Cash (Jumla ya Pesa Inayotakiwa Drooni) -->
    <div class="col-sm-6 col-lg-3">
        <div class="card-laravel p-3 h-100 bg-white border border-start border-4 border-info shadow-sm">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold">Total Expected in Drawer</span>
                <div class="rounded-circle bg-primary-subtle text-primary p-2 d-flex align-items-center justify-content-center shadow-sm" style="width: 36px; height: 36px;">
                    <i class="bi bi-calculator fs-5"></i>
                </div>
            </div>
            <h3 class="fw-bold text-primary mb-1">TZS {{ number_format($metrics['cashier']['expected_drawer_cash']) }}</h3>
            <div class="text-muted" style="font-size: 0.75rem;">
                Opening Float + Shift Cash Sales
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- 2. PAYMENT METHODS BREAKDOWN TODAY (Njia za Malipo kwa Siku)             -->
<!-- ========================================================================= -->
<div class="card-laravel mb-4 bg-white border shadow-sm animate-fade-in">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="fw-bold text-dark mb-0">
                <i class="bi bi-pie-chart text-primary me-2"></i> Today's Payment Methods Breakdown
            </h5>
            <small class="text-muted">Multi-channel revenue split across Cash, Mobile Money, and Bank/Card</small>
        </div>
        <span class="badge bg-light text-dark border rounded-pill px-3 py-1">
            Today: {{ now()->format('d M Y') }}
        </span>
    </div>
    <div class="card-body p-4">
        <div class="row g-3">
            @php
                $methods = $metrics['cashier']['today_payment_breakdown'];
                $cashObj = $methods->firstWhere('payment_method', 'Cash');
                $momoObj = $methods->firstWhere('payment_method', 'Mobile Money');
                $cardObj = $methods->firstWhere('payment_method', 'Card');
                $insObj  = $methods->firstWhere('payment_method', 'Insurance');

                $cashTotal = (float)($cashObj->total ?? 0);
                $momoTotal = (float)($momoObj->total ?? 0);
                $cardTotal = (float)($cardObj->total ?? 0);
                $insTotal  = (float)($insObj->total ?? 0);
                $grandTotal = $cashTotal + $momoTotal + $cardTotal + $insTotal;
            @endphp

            <!-- Cash Card -->
            <div class="col-md-3">
                <div class="p-3 rounded-4 border bg-success-subtle border-success-subtle">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="fw-bold text-success"><i class="bi bi-cash me-1"></i> Cash (Taslimu)</span>
                        <span class="badge bg-success text-white rounded-pill px-2">{{ $cashObj->count ?? 0 }} txns</span>
                    </div>
                    <h4 class="fw-bold text-dark mb-1">TZS {{ number_format($cashTotal) }}</h4>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-success" style="width: {{ $grandTotal > 0 ? round(($cashTotal / $grandTotal) * 100) : 0 }}%;"></div>
                    </div>
                    <small class="text-muted d-block mt-1" style="font-size: 0.7rem;">
                        {{ $grandTotal > 0 ? round(($cashTotal / $grandTotal) * 100, 1) : 0 }}% of total volume
                    </small>
                </div>
            </div>

            <!-- Mobile Money Card -->
            <div class="col-md-3">
                <div class="p-3 rounded-4 border bg-info-subtle border-info-subtle">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="fw-bold text-info"><i class="bi bi-phone me-1"></i> Mobile Money</span>
                        <span class="badge bg-info text-white rounded-pill px-2">{{ $momoObj->count ?? 0 }} txns</span>
                    </div>
                    <h4 class="fw-bold text-dark mb-1">TZS {{ number_format($momoTotal) }}</h4>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-info" style="width: {{ $grandTotal > 0 ? round(($momoTotal / $grandTotal) * 100) : 0 }}%;"></div>
                    </div>
                    <small class="text-muted d-block mt-1" style="font-size: 0.7rem;">
                        M-Pesa, Tigo Pesa, Airtel Money
                    </small>
                </div>
            </div>

            <!-- Bank / Card -->
            <div class="col-md-3">
                <div class="p-3 rounded-4 border bg-primary-subtle border-primary-subtle">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="fw-bold text-primary"><i class="bi bi-credit-card me-1"></i> Card / Bank</span>
                        <span class="badge bg-primary text-white rounded-pill px-2">{{ $cardObj->count ?? 0 }} txns</span>
                    </div>
                    <h4 class="fw-bold text-dark mb-1">TZS {{ number_format($cardTotal) }}</h4>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-primary" style="width: {{ $grandTotal > 0 ? round(($cardTotal / $grandTotal) * 100) : 0 }}%;"></div>
                    </div>
                    <small class="text-muted d-block mt-1" style="font-size: 0.7rem;">
                        CRDB, NMB, Visa, Mastercard
                    </small>
                </div>
            </div>

            <!-- Insurance / Wallet -->
            <div class="col-md-3">
                <div class="p-3 rounded-4 border bg-warning-subtle border-warning-subtle">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="fw-bold text-warning-emphasis"><i class="bi bi-shield-plus me-1"></i> Insurance Claims</span>
                        <span class="badge bg-warning text-dark rounded-pill px-2">{{ $insObj->count ?? 0 }} txns</span>
                    </div>
                    <h4 class="fw-bold text-dark mb-1">TZS {{ number_format($insTotal) }}</h4>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-warning" style="width: {{ $grandTotal > 0 ? round(($insTotal / $grandTotal) * 100) : 0 }}%;"></div>
                    </div>
                    <small class="text-muted d-block mt-1" style="font-size: 0.7rem;">
                        NHIF & Private Insurance Claims
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- 3. TODAY'S SHIFT RECEIPTS & PETTY CASH EXPENSES                           -->
<!-- ========================================================================= -->
<div class="row g-4 mb-4">
    <!-- Left Column: Receipts Generated Today -->
    <div class="col-lg-8">
        <div class="card-laravel bg-white border shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold text-dark mb-0">
                        <i class="bi bi-receipt text-primary me-2"></i> Today's Shift Receipt History
                    </h5>
                    <small class="text-muted">Issued customer receipts with quick reprint and verification options</small>
                </div>
                <a href="{{ route('reports.index', ['tab' => 'sales', 'my_sales' => '1']) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    View All Sales
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-muted small">
                            <tr>
                                <th>Receipt #</th>
                                <th>Customer / Patient</th>
                                <th>Payment Method</th>
                                <th>Amount (TZS)</th>
                                <th>Time</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($metrics['cashier']['my_today_receipts'] as $rcpt)
                                <tr>
                                    <td class="fw-bold text-primary">#{{ str_pad($rcpt->id, 5, '0', STR_PAD_LEFT) }}</td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $rcpt->patient?->name ?? 'Walk-in Customer' }}</div>
                                        <small class="text-muted">{{ $rcpt->patient?->phone ?? '-' }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border px-2 py-1 rounded-pill">
                                            {{ $rcpt->payment_method ?? 'Cash' }}
                                        </span>
                                    </td>
                                    <td class="fw-bold text-dark">
                                        TZS {{ number_format($rcpt->total_amount) }}
                                    </td>
                                    <td class="text-muted small">
                                        {{ $rcpt->created_at ? $rcpt->created_at->format('H:i') : '-' }}
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-1">
                                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold" onclick="viewReceiptDetails({{ $rcpt->id }})" title="View and read receipt details">
                                                <i class="bi bi-eye me-1"></i> Read Receipt
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2" onclick="viewReceiptDetails({{ $rcpt->id }}, true)" title="Reprint receipt">
                                                <i class="bi bi-printer"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <i class="bi bi-receipt-cutoff text-muted fs-3 d-block mb-1"></i>
                                        No customer receipts issued yet during this shift.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Minor Petty Cash & Shift Close -->
    <div class="col-lg-4">
        <!-- Minor Expenses Card -->
        <div class="card-laravel mb-4 bg-white border shadow-sm">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold text-dark mb-0">
                    <i class="bi bi-cash text-danger me-2"></i> Petty Cash Deductions
                </h6>
                <a href="{{ route('expenses.index') }}" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-0" style="font-size: 0.75rem;">
                    + Record
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.8125rem;">
                        <thead class="table-light text-muted small sticky-top">
                            <tr>
                                <th>Item / Purpose</th>
                                <th class="text-end">Amount (TZS)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($metrics['cashier']['today_petty_expenses'] as $exp)
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $exp->category ?? 'Petty Cash' }}</div>
                                        <small class="text-muted">{{ $exp->description ?? 'Store petty expense' }}</small>
                                    </td>
                                    <td class="text-end text-danger fw-bold">
                                        -{{ number_format($exp->amount) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center py-3 text-muted small">
                                        No petty drawer expenses recorded today.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Shift Handover / Close Shift Action Card -->
        <div class="card-laravel bg-white border shadow-sm p-3">
            <div class="d-flex align-items-center gap-2 mb-2">
                <div class="rounded-circle bg-warning-subtle text-warning p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                    <i class="bi bi-clock-history fs-5"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-dark mb-0">End of Shift Reconciliation</h6>
                    <small class="text-muted" style="font-size: 0.75rem;">Physical cash count vs system cash</small>
                </div>
            </div>
            <p class="text-muted small mb-3">
                Before handing over your cash drawer to the next shift or store manager, enter your physical cash count to verify variance.
            </p>
            @if($metrics['cashier']['active_shift'])
                <form action="{{ route('shifts.end', $metrics['cashier']['active_shift']->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark">Physical Cash in Hand (TZS)</label>
                        <input type="number" 
                               name="closing_balance" 
                               class="form-control form-control-sm" 
                               placeholder="e.g. {{ $metrics['cashier']['expected_drawer_cash'] }}"
                               required
                               min="0"
                               step="any">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark">Shift Closing Notes (Optional)</label>
                        <textarea name="notes" rows="2" class="form-control form-control-sm" placeholder="Any float handovers or drawer notes..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-danger btn-sm w-100 rounded-pill fw-bold shadow-sm">
                        <i class="bi bi-door-closed me-1"></i> Close Shift & Reconcile Drawer
                    </button>
                </form>
            @else
                <div class="alert alert-light border rounded-3 text-center py-3 mb-0">
                    <span class="text-muted small d-block mb-2">You do not have an active shift running currently.</span>
                    <a href="{{ route('shifts.index') }}" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold">
                        <i class="bi bi-play-circle me-1"></i> Start New Shift
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- CASHIER PAYMENT COLLECTION MODAL (No Dispensing, Billing & Receipt Only)  -->
<!-- ========================================================================= -->
<div class="modal fade" id="cashierPaymentModal" tabindex="-1" aria-labelledby="cashierPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-success text-white py-3 px-4">
                <div>
                    <h5 class="modal-title fw-bold mb-0" id="cashierPaymentModalLabel">
                        <i class="bi bi-cash-coin me-2"></i> Receive Patient Payment
                    </h5>
                    <small class="text-white-50">Collect billing for prescription order sent by pharmacist</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="cpmTxnIdInput">

                <!-- Order Details Card -->
                <div class="bg-light p-3 rounded-3 border mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-primary text-white fw-bold px-2 py-1" id="cpmOrderNo">#TXN-0</span>
                        <span class="text-muted small" id="cpmOrderTime"></span>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <small class="text-muted d-block" style="font-size: 0.75rem;">Patient / Customer</small>
                            <strong class="text-dark d-block" id="cpmPatientName">Walk-in Patient</strong>
                            <small class="text-muted" id="cpmPatientPhone">-</small>
                        </div>
                        <div class="col-6 text-end">
                            <small class="text-muted d-block" style="font-size: 0.75rem;">Prescribed By</small>
                            <span class="badge bg-info-subtle text-info border border-info-subtle" id="cpmPharmacistName">Pharmacist</span>
                        </div>
                    </div>
                </div>

                <!-- Prescription Items Breakdown -->
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary text-uppercase mb-1" style="font-size: 0.72rem;">Prescription Items Breakdown</label>
                    <div class="border rounded-3 p-2 bg-white" style="max-height: 180px; overflow-y: auto;">
                        <table class="table table-sm table-borderless align-middle mb-0">
                            <thead class="small text-muted border-bottom" style="font-size: 0.75rem;">
                                <tr>
                                    <th>Item</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody id="cpmItemsBody">
                                <!-- Populated dynamically by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Total Amount Banner -->
                <div class="d-flex justify-content-between align-items-center p-3 rounded-3 bg-success-subtle border border-success-subtle mb-3">
                    <div>
                        <span class="text-muted small text-uppercase fw-bold d-block" style="font-size: 0.72rem;">Total Amount to Collect</span>
                        <small class="text-muted">Includes applicable taxes</small>
                    </div>
                    <div class="text-end">
                        <span class="fs-4 fw-bold text-success tabular-nums" id="cpmTotalAmount">TZS 0</span>
                    </div>
                </div>

                <!-- Payment Method Selection -->
                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark mb-1">
                        Select Payment Method <span class="text-danger">*</span>
                    </label>
                    @php
                        $cpmActiveMethods = \App\Models\PaymentMethod::getActiveMethods();
                    @endphp
                    <select id="cpmPaymentMethod" class="form-select form-select-lg fw-semibold shadow-sm">
                        @foreach($cpmActiveMethods as $pm)
                            <option value="{{ $pm->name }}" {{ $pm->name === 'Cash' ? 'selected' : '' }}>
                                @if($pm->name === 'Cash') 💵 Cash Payment
                                @elseif($pm->name === 'Mobile Money') 📱 Mobile Money (M-Pesa / Tigo Pesa / Airtel / HaloPesa)
                                @elseif($pm->name === 'Card') 💳 Credit / Debit Card (Visa / Mastercard)
                                @elseif($pm->name === 'Insurance') 🏥 Insurance (NHIF / Private)
                                @elseif($pm->name === 'Wallet') 👛 Customer Wallet
                                @else {{ $pm->display_name }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Action Button -->
                <button type="button" class="btn btn-success btn-lg w-100 rounded-pill fw-bold shadow-sm" id="cpmSubmitBtn" onclick="submitCashierPaymentApproval()">
                    <i class="bi bi-check2-circle me-1"></i> Approve Payment & Print Receipt
                </button>
                <div class="text-center mt-2">
                    <small class="text-muted" style="font-size: 0.75rem;">
                        <i class="bi bi-shield-lock me-1 text-primary"></i> Payment receipt will be issued instantly. Medicine will be released by the pharmacist.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- THERMAL RECEIPT PRINT MODAL (Universal Standard Layout)                   -->
<!-- ========================================================================= -->
<div class="modal fade" id="receiptModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-body text-center p-4">
        <div class="rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center mb-3" style="width: 52px; height: 52px;">
            <i class="bi bi-check-lg fs-2"></i>
        </div>
        <h5 class="fw-bold text-success mb-3">Payment Received Successfully!</h5>
        
        <div class="p-3 rounded mb-3 text-start font-monospace small border bg-white" id="printableReceiptArea" style="max-width: 290px; margin: 0 auto;">
            <!-- Header -->
            <div class="text-center fw-bold mb-2">
                @if(!empty($SYS_SETTINGS['logo_path']))
                    <div class="mb-1 text-center">
                        <img src="{{ asset($SYS_SETTINGS['logo_path']) }}" alt="Logo" style="max-height: 44px; max-width: 140px; object-fit: contain;">
                    </div>
                @endif
                <div class="fs-6"><?= htmlspecialchars($SYS_SETTINGS['company_name'] ?? 'DAWAFY PHARMACY') ?></div>
                <small class="text-muted d-block"><?= htmlspecialchars($SYS_SETTINGS['address'] ?? 'Tanzania') ?></small>
                <small class="text-muted d-block">Phone: <?= htmlspecialchars($SYS_SETTINGS['phone'] ?? '+255 700 000 000') ?></small>
            </div>
            <div class="text-center border-top border-bottom py-1 mb-2">
                <strong>TAX INVOICE / RECEIPT</strong>
            </div>

            <!-- Tax Info -->
            <div class="mb-2">
                <small>TIN: <?= htmlspecialchars($SYS_SETTINGS['tin'] ?? '101-001-001') ?></small><br>
                <small>VRN: <?= htmlspecialchars($SYS_SETTINGS['vat_reg'] ?? 'VAT-001') ?></small>
            </div>

            <!-- Transaction Details -->
            <div class="mb-2">
                <small>Invoice No:</small> <strong id="recTxnId"></strong><br>
                <small>Date:</small> <strong id="recDate"></strong><br>
                <small>Time:</small> <strong id="recTime"></strong>
            </div>

            <!-- Customer Info -->
            <div class="mb-2" id="recCustomerSection" style="display: none;">
                <small>Customer:</small> <strong id="recCustomer"></strong>
            </div>

            <!-- Items Header -->
            <div class="border-top border-bottom py-1 mb-1 fw-bold">
                <div class="d-flex justify-content-between">
                    <small>ITEM</small>
                    <small>QTY</small>
                    <small>PRICE</small>
                    <small>TOTAL</small>
                </div>
            </div>

            <!-- Items List -->
            <div id="recItems" class="mb-2"></div>

            <!-- Totals -->
            <div class="border-top pt-1">
                <div class="d-flex justify-content-between mb-1">
                    <small>Subtotal:</small>
                    <small id="recSubtotal">TZS 0</small>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <small>VAT (18%):</small>
                    <small id="recVat">TZS 0</small>
                </div>
                <div class="d-flex justify-content-between fw-bold border-top pt-1">
                    <small>TOTAL:</small>
                    <small id="recTotal" class="text-dark">TZS 0</small>
                </div>
            </div>

            <!-- Payment Info -->
            <div class="border-top pt-1 mt-2">
                <div class="d-flex justify-content-between">
                    <small>Payment Method:</small>
                    <small id="recMethod"></small>
                </div>
                <div class="d-flex justify-content-between">
                    <small>Cashier:</small>
                    <small>{{ auth()->user()?->name ?? 'Cashier' }}</small>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center mt-3 border-top pt-2">
                <small class="text-muted">Thank you for being our customer!</small><br>
                <small class="text-muted">Powered by Dawafy PMS</small>
            </div>
        </div>

        <button class="btn btn-primary w-100 mb-2 rounded-pill fw-bold shadow-sm" onclick="printCashierReceipt()">
            <i class="bi bi-printer me-1"></i> Print Thermal Receipt
        </button>
        <button class="btn btn-outline-secondary w-100 rounded-pill" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- ========================================================================= -->
<!-- PRINT STYLES & INTERACTIVE CASHIER LOGIC                                  -->
<!-- ========================================================================= -->
<style>
@media print {
    body * {
        visibility: hidden !important;
    }
    #receiptModal, #receiptModal * {
        visibility: visible !important;
    }
    #receiptModal {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        margin: 0;
        padding: 0;
    }
    .modal-backdrop, .btn, .btn-close, h5, .rounded-circle {
        display: none !important;
    }
    #printableReceiptArea {
        border: none !important;
        padding: 10px !important;
        background: white !important;
        max-width: 80mm !important;
        margin: 0 auto !important;
        box-shadow: none !important;
    }
}
</style>

<script>
window.cashierPendingMap = @json($pendingTxns->keyBy('id'));

function openCashierPaymentModal(txnId) {
    let t = window.cashierPendingMap[txnId];
    if(!t) {
        alert('Could not find order details. Please refresh the queue.');
        return;
    }

    document.getElementById('cpmTxnIdInput').value = t.id;
    document.getElementById('cpmOrderNo').innerText = t.transaction_id || ('#TXN-' + t.id);
    
    let custName = (t.customer && t.customer.name) ? t.customer.name : 'Walk-in Patient';
    let custPhone = (t.customer && t.customer.phone) ? t.customer.phone : 'No phone';
    document.getElementById('cpmPatientName').innerText = custName;
    document.getElementById('cpmPatientPhone').innerText = custPhone;

    let pharmName = (t.pharmacist && t.pharmacist.name) ? t.pharmacist.name : 'Pharmacist';
    document.getElementById('cpmPharmacistName').innerText = pharmName;

    let createdAt = t.created_at ? new Date(t.created_at).toLocaleTimeString() : '';
    document.getElementById('cpmOrderTime').innerText = createdAt;

    let total = parseFloat(t.total_amount) || 0;
    document.getElementById('cpmTotalAmount').innerText = 'TZS ' + total.toLocaleString();

    // Render items list
    let items = Array.isArray(t.items) ? t.items : (typeof t.items === 'string' ? JSON.parse(t.items || '[]') : []);
    let itemsHtml = '';
    if(items.length === 0) {
        itemsHtml = `<tr><td colspan="4" class="text-center text-muted py-2">Prescription items (1 package)</td></tr>`;
    } else {
        items.forEach(item => {
            let itemName = item.name || (item.drug ? item.drug.name : 'Medication');
            let qty = item.quantity || item.qty || 1;
            let price = parseFloat(item.price || item.unit_price || 0);
            let lineTotal = qty * price;
            itemsHtml += `
                <tr>
                    <td><strong class="text-dark small">${itemName}</strong></td>
                    <td class="text-center small">${qty}</td>
                    <td class="text-end small">${price.toLocaleString()}</td>
                    <td class="text-end small fw-bold">${lineTotal.toLocaleString()}</td>
                </tr>
            `;
        });
    }
    document.getElementById('cpmItemsBody').innerHTML = itemsHtml;

    // Reset payment method to Cash
    document.getElementById('cpmPaymentMethod').value = 'Cash';

    // Show modal
    new bootstrap.Modal(document.getElementById('cashierPaymentModal')).show();
}

function submitCashierPaymentApproval() {
    let txnId = document.getElementById('cpmTxnIdInput').value;
    let paymentMethod = document.getElementById('cpmPaymentMethod').value;
    let submitBtn = document.getElementById('cpmSubmitBtn');

    if(!txnId) return;

    submitBtn.disabled = true;
    submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> Processing Payment...`;

    let fd = new FormData();
    fd.append('payment_method', paymentMethod);

    fetch(`{{ url('/pos/transactions') }}/${txnId}/approve-payment`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: fd
    })
    .then(res => res.json())
    .then(data => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = `<i class="bi bi-check2-circle me-1"></i> Approve Payment & Print Receipt`;

        if(data.success) {
            // Hide payment modal
            let payModalEl = document.getElementById('cashierPaymentModal');
            let payModal = bootstrap.Modal.getInstance(payModalEl);
            if(payModal) payModal.hide();

            // Populate receipt modal
            let txn = data.transaction || window.cashierPendingMap[txnId] || {};
            let totalAmount = parseFloat(txn.total_amount || data.total_amount || 0);
            let subtotal = totalAmount / 1.18;
            let vat = totalAmount - subtotal;

            document.getElementById('recTxnId').innerText = txn.transaction_id || ('#TXN-' + txnId);
            document.getElementById('recDate').innerText = new Date().toLocaleDateString();
            document.getElementById('recTime').innerText = new Date().toLocaleTimeString();
            document.getElementById('recSubtotal').innerText = 'TZS ' + Math.round(subtotal).toLocaleString();
            document.getElementById('recVat').innerText = 'TZS ' + Math.round(vat).toLocaleString();
            document.getElementById('recTotal').innerText = 'TZS ' + totalAmount.toLocaleString();
            document.getElementById('recMethod').innerText = paymentMethod;

            if(txn.customer && txn.customer.name) {
                document.getElementById('recCustomer').innerText = txn.customer.name;
                document.getElementById('recCustomerSection').style.display = 'block';
            } else {
                document.getElementById('recCustomerSection').style.display = 'none';
            }

            let items = Array.isArray(txn.items) ? txn.items : (typeof txn.items === 'string' ? JSON.parse(txn.items || '[]') : []);
            let itemsHtml = '';
            items.forEach(item => {
                let itemName = item.name || (item.drug ? item.drug.name : 'Item');
                let qty = item.quantity || item.qty || 1;
                let price = item.price || item.unit_price || 0;
                let lineTotal = qty * price;
                itemsHtml += `
                    <div class="d-flex justify-content-between mb-1">
                        <small style="width: 40%; text-align: left;">${itemName.substring(0, 16)}</small>
                        <small style="width: 15%; text-align: center;">${qty}</small>
                        <small style="width: 20%; text-align: right;">${parseFloat(price).toLocaleString()}</small>
                        <small style="width: 25%; text-align: right;">${parseFloat(lineTotal).toLocaleString()}</small>
                    </div>
                `;
            });
            document.getElementById('recItems').innerHTML = itemsHtml;

            // Remove from queue and map
            delete window.cashierPendingMap[txnId];
            let rowEl = document.getElementById('cpt-row-' + txnId);
            if(rowEl) rowEl.remove();

            // Refresh queue counters
            refreshCashierPendingQueue();

            // Open receipt modal
            new bootstrap.Modal(document.getElementById('receiptModal')).show();
        } else {
            alert('Approval failed: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(err => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = `<i class="bi bi-check2-circle me-1"></i> Approve Payment & Print Receipt`;
        alert('Network error: ' + err.message);
    });
}

function printCashierReceipt() {
    window.print();
}

function refreshCashierPendingQueue() {
    fetch(`{{ route('pos.pending_payments') }}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.json())
    .then(data => {
        if(!Array.isArray(data)) return;

        // Update count badges
        document.querySelectorAll('.cashierPendingCountBadge').forEach(el => {
            el.innerText = data.length;
        });

        // Update map
        window.cashierPendingMap = {};
        data.forEach(t => { window.cashierPendingMap[t.id] = t; });

        let container = document.getElementById('cashierPendingTableContainer');
        if(!container) return;

        if(data.length === 0) {
            container.innerHTML = `
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-check2-circle fs-2 text-success d-block mb-1"></i>
                    <p class="mb-0 fw-semibold">No pending payments right now.</p>
                    <small class="text-muted">When a pharmacist clicks "Send to Cashier" in POS, orders will appear here instantly.</small>
                </div>
            `;
            return;
        }

        let html = `
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted small">
                        <tr>
                            <th>Invoice / Order</th>
                            <th>Patient / Customer</th>
                            <th>Prescribed By</th>
                            <th>Total Amount</th>
                            <th>Time</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody id="cashierPendingTbody">
        `;

        data.forEach(pt => {
            let custName = pt.customer ? pt.customer.name : 'Walk-in Patient';
            let custPhone = pt.customer ? (pt.customer.phone || 'No phone') : 'No phone';
            let pharmName = pt.pharmacist ? pt.pharmacist.name : 'Pharmacist';
            let timeStr = pt.created_at ? new Date(pt.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) : '';
            let totalFormatted = parseFloat(pt.total_amount || 0).toLocaleString();

            html += `
                <tr id="cpt-row-${pt.id}">
                    <td>
                        <strong class="text-dark">${pt.transaction_id || ('#TXN-' + pt.id)}</strong>
                        <span class="badge bg-warning text-dark ms-1" style="font-size: 0.68rem;">Pending Payment</span>
                    </td>
                    <td>
                        <div class="fw-semibold text-dark">${custName}</div>
                        <small class="text-muted">${custPhone}</small>
                    </td>
                    <td>
                        <span class="badge bg-light text-secondary border">
                            <i class="bi bi-person-badge me-1"></i> ${pharmName}
                        </span>
                    </td>
                    <td>
                        <strong class="text-success fs-6 tabular-nums">TZS ${totalFormatted}</strong>
                    </td>
                    <td>
                        <small class="text-muted">${timeStr}</small>
                    </td>
                    <td class="text-end">
                        <button type="button" class="btn btn-sm btn-success fw-bold rounded-pill px-3 shadow-sm" onclick="openCashierPaymentModal(${pt.id})">
                            <i class="bi bi-credit-card me-1"></i> Receive Payment
                        </button>
                    </td>
                </tr>
            `;
        });

        html += `
                    </tbody>
                </table>
            </div>
        `;
        container.innerHTML = html;
    })
    .catch(() => {});
}

// Auto-refresh queue every 25 seconds
setInterval(refreshCashierPendingQueue, 25000);

function viewReceiptDetails(txnId, andPrint = false) {
    fetch("{{ url('/pos/receipt') }}/" + txnId, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success || !data.receipt) {
            alert('Could not find receipt details.');
            return;
        }
        let txn = data.receipt;
        let totalAmount = parseFloat(txn.total_amount || 0);
        let subtotal = totalAmount / 1.18;
        let vat = totalAmount - subtotal;

        document.getElementById('recTxnId').innerText = txn.transaction_id || ('#TXN-' + txn.id);
        document.getElementById('recDate').innerText = txn.created_at ? new Date(txn.created_at).toLocaleDateString() : new Date().toLocaleDateString();
        document.getElementById('recTime').innerText = txn.created_at ? new Date(txn.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) : new Date().toLocaleTimeString();
        document.getElementById('recSubtotal').innerText = 'TZS ' + Math.round(subtotal).toLocaleString();
        document.getElementById('recVat').innerText = 'TZS ' + Math.round(vat).toLocaleString();
        document.getElementById('recTotal').innerText = 'TZS ' + Math.round(totalAmount).toLocaleString();
        document.getElementById('recMethod').innerText = txn.payment_method || 'Cash';
        
        let cashierName = (txn.cashier && txn.cashier.name) ? txn.cashier.name : "{{ auth()->user()?->name ?? 'Cashier' }}";
        let cashierElem = document.getElementById('recCashier');
        if (cashierElem) cashierElem.innerText = cashierName;

        let custName = (txn.customer && txn.customer.name) ? txn.customer.name : ((txn.patient && txn.patient.name) ? txn.patient.name : null);
        if(custName) {
            document.getElementById('recCustomer').innerText = custName;
            document.getElementById('recCustomerSection').style.display = 'block';
        } else {
            document.getElementById('recCustomerSection').style.display = 'none';
        }

        let items = txn.items || [];
        let itemsHtml = '';
        items.forEach(item => {
            let itemName = item.drug ? item.drug.name : (item.item_name || 'Medicine');
            let qty = item.quantity || 1;
            let price = item.unit_price || item.price || 0;
            let lineTotal = qty * price;
            itemsHtml += `
                <div class="d-flex justify-content-between mb-1">
                    <small style="width: 40%; text-align: left;">${itemName.substring(0, 16)}</small>
                    <small style="width: 15%; text-align: center;">${qty}</small>
                    <small style="width: 20%; text-align: right;">${parseFloat(price).toLocaleString()}</small>
                    <small style="width: 25%; text-align: right;">${parseFloat(lineTotal).toLocaleString()}</small>
                </div>
            `;
        });
        document.getElementById('recItems').innerHTML = itemsHtml;

        let modalEl = document.getElementById('receiptModal');
        let modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modal.show();

        if (andPrint) {
            setTimeout(() => {
                window.print();
            }, 400);
        }
    })
    .catch(err => {
        alert('Failed to load receipt: ' + err.message);
    });
}

function printReceiptNow() {
    window.print();
}
</script>

<!-- ========================================================================= -->
<!-- MODAL: THERMAL RECEIPT PRINT TEMPLATE                                     -->
<!-- ========================================================================= -->
<div class="modal fade" id="receiptModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-body text-center p-4">
        <div class="rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center mb-3" style="width: 52px; height: 52px;">
            <i class="bi bi-receipt fs-2"></i>
        </div>
        <h5 class="fw-bold text-dark mb-3">Customer Tax Invoice / Receipt</h5>
        
        <div class="p-3 rounded mb-3 text-start font-monospace small border bg-white" id="printableReceiptArea" style="max-width: 290px; margin: 0 auto;">
            <!-- Header -->
            <div class="text-center fw-bold mb-2">
                @if(!empty($SYS_SETTINGS['logo_path']))
                    <div class="mb-1 text-center">
                        <img src="{{ asset($SYS_SETTINGS['logo_path']) }}" alt="Logo" style="max-height: 44px; max-width: 140px; object-fit: contain;">
                    </div>
                @endif
                <div class="fs-6"><?= htmlspecialchars($SYS_SETTINGS['company_name'] ?? 'DAWAFY PHARMACY') ?></div>
                <small class="text-muted d-block"><?= htmlspecialchars($SYS_SETTINGS['address'] ?? 'Tanzania') ?></small>
                <small class="text-muted d-block">Phone: <?= htmlspecialchars($SYS_SETTINGS['phone'] ?? '+255 700 000 000') ?></small>
            </div>
            <div class="text-center border-top border-bottom py-1 mb-2">
                <strong>TAX INVOICE / RECEIPT</strong>
            </div>

            <!-- Tax Info -->
            <div class="mb-2">
                <small>TIN: <?= htmlspecialchars($SYS_SETTINGS['tin'] ?? '101-001-001') ?></small><br>
                <small>VRN: <?= htmlspecialchars($SYS_SETTINGS['vat_reg'] ?? 'VAT-001') ?></small>
            </div>

            <!-- Transaction Details -->
            <div class="mb-2">
                <small>Invoice No:</small> <strong id="recTxnId"></strong><br>
                <small>Date:</small> <strong id="recDate"></strong><br>
                <small>Time:</small> <strong id="recTime"></strong>
            </div>

            <!-- Customer Info -->
            <div class="mb-2" id="recCustomerSection" style="display: none;">
                <small>Customer:</small> <strong id="recCustomer"></strong>
            </div>

            <!-- Items Header -->
            <div class="border-top border-bottom py-1 mb-1 fw-bold">
                <div class="d-flex justify-content-between">
                    <small>ITEM</small>
                    <small>QTY</small>
                    <small>PRICE</small>
                    <small>TOTAL</small>
                </div>
            </div>

            <!-- Items List -->
            <div id="recItems" class="mb-2"></div>

            <!-- Totals -->
            <div class="border-top pt-1">
                <div class="d-flex justify-content-between mb-1">
                    <small>Subtotal:</small>
                    <small id="recSubtotal">TZS 0</small>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <small>VAT (18%):</small>
                    <small id="recVat">TZS 0</small>
                </div>
                <div class="d-flex justify-content-between fw-bold border-top pt-1">
                    <small>TOTAL:</small>
                    <small id="recTotal" class="text-dark">TZS 0</small>
                </div>
            </div>

            <!-- Payment Info -->
            <div class="border-top pt-1 mt-2">
                <div class="d-flex justify-content-between">
                    <small>Payment Method:</small>
                    <small id="recMethod"></small>
                </div>
                <div class="d-flex justify-content-between">
                    <small>Cashier:</small>
                    <small id="recCashier">{{ auth()->user()?->name ?? 'Cashier' }}</small>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center mt-3 border-top pt-2">
                <small class="text-muted">Thank you for being our customer!</small><br>
                <small class="text-muted">Powered by Dawafy PMS</small>
            </div>
        </div>

        <button class="btn btn-primary w-100 mb-2 rounded-pill fw-bold shadow-sm" onclick="printReceiptNow()">
            <i class="bi bi-printer me-1"></i> Print Thermal Receipt
        </button>
        <button class="btn btn-outline-secondary w-100 rounded-pill" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<style>
@media print {
    body * {
        visibility: hidden !important;
    }
    #receiptModal, #receiptModal * {
        visibility: visible !important;
    }
    #receiptModal {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        margin: 0;
        padding: 0;
        background: white;
    }
    .modal-dialog {
        max-width: 100% !important;
        margin: 0 !important;
    }
    .modal-content {
        border: none !important;
        box-shadow: none !important;
    }
    .modal-body {
        padding: 0 !important;
    }
    #receiptModal button, #receiptModal .bi-receipt {
        display: none !important;
    }
    #printableReceiptArea {
        border: none !important;
        width: 100% !important;
        max-width: 100% !important;
    }
}
</style>

