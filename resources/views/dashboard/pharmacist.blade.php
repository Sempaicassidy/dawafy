<!-- Pharmacist Clinical Operations Command Center Header -->
<div class="laravel-hero-banner mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3 pb-2 border-bottom animate-fade-in">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1 rounded-pill fw-bold" style="font-size: 0.75rem;">
                <i class="bi bi-shield-check me-1"></i> Clinical & Dispensing Ops
            </span>
            <span class="text-muted small">| Pharmacist Shift Center</span>
        </div>
        <h2 class="fw-bold mb-1 text-dark">Pharmacist Clinical Dashboard</h2>
        <p class="text-muted mb-0 small">Medicine dispensing, real-time shelf search, FEFO expiry tracking, and prescription safety oversight.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('prescriptions.index') }}" class="btn btn-outline-secondary text-dark rounded-pill px-3 btn-sm fw-semibold">
            <i class="bi bi-file-earmark-medical me-1 text-primary"></i> Prescriptions (POM)
        </a>
        <a href="{{ route('contacts.index') }}" class="btn btn-outline-secondary text-dark rounded-pill px-3 btn-sm fw-semibold">
            <i class="bi bi-person-lines-fill me-1 text-success"></i> Patient Directory
        </a>
        <a href="{{ route('pos.index') }}" class="btn btn-primary rounded-pill px-4 btn-sm fw-bold shadow-sm">
            <i class="bi bi-capsule me-1"></i> Dispense Medicine (POS)
        </a>
    </div>
</div>

<!-- ========================================================================= -->
<!-- 1. GLOBAL DRUG SEARCH BAR (Baa Kubwa ya Kutafuta Dawa ya Haraka)         -->
<!-- ========================================================================= -->
<div class="card-laravel mb-4 border-primary border-2 shadow-sm animate-fade-in" style="background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);">
    <div class="card-body p-4">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <label class="form-label fw-bold text-dark mb-0 fs-5">
                <i class="bi bi-search text-primary me-2"></i> Global Drug & Stock Quick Finder
            </label>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 small">
                Instant Shelf & Stock Lookup
            </span>
        </div>
        <p class="text-muted small mb-3">
            Search by <strong>Brand Name</strong> (e.g., Panadol, Amoxil, Cataflam) or <strong>Generic Name</strong> (e.g., Paracetamol, Amoxicillin, Diclofenac) to instantly check stock count, dosage form, shelf location, and expiry dates before dispensing.
        </p>
        <div class="input-group input-group-lg shadow-sm">
            <span class="input-group-text bg-white border-end-0 text-muted px-3">
                <i class="bi bi-search fs-4 text-primary"></i>
            </span>
            <input type="text" 
                   id="pharmacistDrugSearch" 
                   class="form-control form-control-lg border-start-0 ps-0" 
                   placeholder="Type drug name, brand, generic, or scan barcode..." 
                   autocomplete="off"
                   onkeyup="searchDrugsGlobal(this.value)">
            <button class="btn btn-outline-secondary px-3" type="button" onclick="clearDrugSearch()" title="Clear search">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <!-- Live Search Results Container -->
        <div id="drugSearchResults" class="mt-3 d-none">
            <div class="d-flex justify-content-between align-items-center mb-2 px-1">
                <span class="small fw-bold text-dark" id="resultsCountText">Matching Medications</span>
                <span class="text-muted small" style="font-size: 0.75rem;">Click 'Dispense in POS' to load medicine directly into billing</span>
            </div>
            <div class="table-responsive rounded-3 border bg-white shadow-sm" style="max-height: 380px; overflow-y: auto;">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted small sticky-top">
                        <tr>
                            <th>Medication Name</th>
                            <th>Generic Name</th>
                            <th>Dosage & Form</th>
                            <th>Available Stock</th>
                            <th>Price (TZS)</th>
                            <th>Earliest Expiry</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody id="drugSearchResultsBody">
                        <!-- Populated by JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- 1.5 PAID ORDERS READY FOR DISPENSATION (Separated Workflow Queue)         -->
<!-- ========================================================================= -->
@php
    $readyTxns = $metrics['ready_to_dispense'] ?? collect();
@endphp
<div class="card-laravel mb-4 bg-white border border-start border-4 border-success shadow-sm animate-fade-in">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-success text-white fw-bold px-2 py-1 rounded-pill" style="font-size: 0.72rem;">
                    <i class="bi bi-bag-check-fill me-1"></i> Ready for Handover
                </span>
                <span class="text-muted small">| Cashier Payment Verified</span>
            </div>
            <h5 class="fw-bold text-dark mb-0">
                <i class="bi bi-capsule text-success me-2"></i> Paid Prescriptions Ready to Dispense ({{ $readyTxns->count() }})
            </h5>
            <small class="text-muted">Cashier has collected payment for these prescriptions. Verify items and click "Dispense in POS" to finalize inventory deduction and hand over medication.</small>
        </div>
        <div>
            <a href="{{ route('pos.index') }}" class="btn btn-sm btn-success rounded-pill px-3 fw-bold">
                <i class="bi bi-box-arrow-up-right me-1"></i> Open POS Dispense Queue
            </a>
        </div>
    </div>
    <div class="card-body p-3">
        @if($readyTxns->isEmpty())
            <div class="text-center py-4 text-muted">
                <i class="bi bi-clipboard-check fs-2 text-success d-block mb-1"></i>
                <p class="mb-0 fw-semibold">No paid prescriptions waiting for dispensation.</p>
                <small class="text-muted">When a cashier approves payment for an order you prescribed, it will immediately appear here.</small>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted small">
                        <tr>
                            <th>Receipt / Order</th>
                            <th>Patient</th>
                            <th>Cashier Approval</th>
                            <th>Total Billed</th>
                            <th>Time</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($readyTxns as $rt)
                            <tr>
                                <td>
                                    <strong class="text-dark">{{ $rt->transaction_id ?? ('#TXN-' . $rt->id) }}</strong>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle ms-1" style="font-size: 0.68rem;">PAID</span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $rt->customer?->name ?? 'Walk-in Patient' }}</div>
                                    <small class="text-muted">{{ $rt->customer?->phone ?? 'No phone' }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <i class="bi bi-cash-coin me-1 text-success"></i> {{ $rt->cashier?->name ?? 'Cashier' }} ({{ $rt->payment_method ?? 'Cash' }})
                                    </span>
                                </td>
                                <td>
                                    <strong class="text-dark fs-6 tabular-nums">TZS {{ number_format($rt->total_amount) }}</strong>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $rt->created_at->format('H:i') }} ({{ $rt->created_at->diffForHumans() }})</small>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('pos.index') }}" class="btn btn-sm btn-outline-success fw-bold rounded-pill px-3">
                                        <i class="bi bi-box-arrow-up-right me-1"></i> Handover & Deduct Stock
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<!-- ========================================================================= -->
<!-- 2. CLINICAL & OPERATIONAL QUICK METRICS                                   -->
<!-- ========================================================================= -->
<div class="row g-3 mb-4 animate-fade-in">
    <!-- Patients Dispensed Today -->
    <div class="col-sm-6 col-lg-3">
        <div class="card-laravel p-3 h-100 bg-white border border-start border-4 border-primary shadow-sm">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold">My Dispensed Today</span>
                <div class="rounded-circle bg-primary-subtle text-primary p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                    <i class="bi bi-person-check fs-5"></i>
                </div>
            </div>
            <h3 class="fw-bold text-dark mb-1">{{ number_format($metrics['pharmacist']['my_today_txns_count']) }}</h3>
            <div class="d-flex align-items-center gap-1 text-muted" style="font-size: 0.75rem;">
                <i class="bi bi-receipt text-primary"></i> Patient encounters served by you
            </div>
        </div>
    </div>

    <!-- Low Stock Alert -->
    <div class="col-sm-6 col-lg-3">
        <div class="card-laravel p-3 h-100 bg-white border border-start border-4 border-warning shadow-sm">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold">Low Stock Warnings</span>
                <div class="rounded-circle bg-warning-subtle text-warning p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                    <i class="bi bi-exclamation-triangle fs-5"></i>
                </div>
            </div>
            <h3 class="fw-bold text-warning mb-1">{{ count($metrics['low_stock_drugs']) }}</h3>
            <div class="d-flex align-items-center gap-1 text-muted" style="font-size: 0.75rem;">
                <i class="bi bi-arrow-down-right-circle text-warning"></i> Items below reorder threshold
            </div>
        </div>
    </div>

    <!-- Near Expiry Alert (FEFO) -->
    <div class="col-sm-6 col-lg-3">
        <div class="card-laravel p-3 h-100 bg-white border border-start border-4 border-danger shadow-sm">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold">Near Expiry (FEFO)</span>
                <div class="rounded-circle bg-danger-subtle text-danger p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                    <i class="bi bi-calendar-x fs-5"></i>
                </div>
            </div>
            <h3 class="fw-bold text-danger mb-1">{{ count($metrics['pharmacist']['near_expiry_fefo']) }}</h3>
            <div class="d-flex align-items-center gap-1 text-danger" style="font-size: 0.75rem;">
                <i class="bi bi-hourglass-split"></i> Expiring this or next month
            </div>
        </div>
    </div>

    <!-- Pending Insurance Verifications -->
    <div class="col-sm-6 col-lg-3">
        <div class="card-laravel p-3 h-100 bg-white border border-start border-4 border-info shadow-sm">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold">Pending Insurance</span>
                <div class="rounded-circle bg-info-subtle text-info p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                    <i class="bi bi-shield-plus fs-5"></i>
                </div>
            </div>
            <h3 class="fw-bold text-info mb-1">{{ $metrics['insurance']['pending_count'] }}</h3>
            <div class="d-flex align-items-center gap-1 text-muted" style="font-size: 0.75rem;">
                <i class="bi bi-clock-history text-info"></i> Claims awaiting pre-dispense check
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- 3. DETAILED CLINICAL WORKSPACES (FEFO Expiry & Low Stock Watchlists)      -->
<!-- ========================================================================= -->
<div class="row g-4 mb-4">
    <!-- Left Column: FEFO Priority Dispensing (Near-Expiry Medicines) -->
    <div class="col-lg-7">
        <div class="card-laravel h-100 bg-white border shadow-sm">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold text-dark mb-0">
                        <i class="bi bi-exclamation-octagon text-danger me-2"></i> Near-Expiry Dispense Priority (FEFO)
                    </h5>
                    <small class="text-muted">First Expired, First Out: Push these specific batches first to avoid medicine wastage</small>
                </div>
                <a href="{{ route('inventory.index', ['tab' => 'expired']) }}" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                    View All Expiring
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-muted small">
                            <tr>
                                <th>Medicine & Batch</th>
                                <th>Units In Stock</th>
                                <th>Expiry Date</th>
                                <th>Days Remaining</th>
                                <th class="text-end">Dispense</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($metrics['pharmacist']['near_expiry_fefo'] as $batch)
                                @php
                                    $expDate = \Carbon\Carbon::parse($batch->expiry_date);
                                    $daysLeft = (int)now()->diffInDays($expDate, false);
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $batch->drug?->name ?? 'Medicine' }}</div>
                                        <small class="text-muted">Batch: <code>{{ $batch->batch_number ?? 'N/A' }}</code></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary-subtle text-dark px-2 py-1 rounded-pill fw-bold">
                                            {{ number_format($batch->quantity) }} units
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-danger fw-semibold">{{ $expDate->format('d M Y') }}</span>
                                    </td>
                                    <td>
                                        @if($daysLeft <= 0)
                                            <span class="badge bg-danger text-white rounded-pill px-2">Expired</span>
                                        @elseif($daysLeft <= 30)
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2">
                                                {{ $daysLeft }} days left
                                            </span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2">
                                                {{ $daysLeft }} days left
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('pos.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                            <i class="bi bi-cart-plus me-1"></i> Dispense
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="bi bi-check2-circle text-success fs-3 d-block mb-1"></i>
                                        All current active batches have safe, distant expiry dates.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Low Stock Warnings & Prescriptions -->
    <div class="col-lg-5">
        <div class="card-laravel mb-4 bg-white border shadow-sm">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold text-dark mb-0">
                        <i class="bi bi-bell text-warning me-2"></i> Low Stock Warnings
                    </h5>
                    <small class="text-muted">Do not prescribe or promise to patients without stock check</small>
                </div>
                <a href="{{ route('inventory.index', ['tab' => 'out_of_stock']) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    Reorder List
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 260px; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-muted small sticky-top">
                            <tr>
                                <th>Medicine</th>
                                <th>Current Stock</th>
                                <th>Reorder Level</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($metrics['low_stock_drugs']->take(6) as $drug)
                                @php
                                    $currStock = $drug->batches ? $drug->batches->sum('quantity') : 0;
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $drug->name }}</div>
                                        <small class="text-muted">{{ $drug->dosage_form ?? 'Tablet' }}</small>
                                    </td>
                                    <td>
                                        @if($currStock <= 0)
                                            <span class="badge bg-danger text-white rounded-pill px-2">Out of Stock (0)</span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2">
                                                {{ $currStock }} left
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-muted small">
                                        Min: {{ $drug->reorder_level }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-3 text-muted">
                                        <i class="bi bi-shield-check text-success fs-4 d-block mb-1"></i>
                                        All inventory items are currently above safe reorder levels.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pending Insurance Verification Checklist -->
        <div class="card-laravel bg-white border shadow-sm">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold text-dark mb-0">
                    <i class="bi bi-shield-plus text-info me-2"></i> Pending Insurance Claims
                </h6>
                <a href="{{ route('insurance.index') }}" class="btn btn-sm btn-outline-info rounded-pill px-3">
                    Claims Desk
                </a>
            </div>
            <div class="card-body p-3">
                @if($metrics['insurance']['pending_count'] > 0)
                    <div class="alert alert-info border-0 rounded-3 mb-0 small">
                        <div class="fw-bold mb-1"><i class="bi bi-info-circle me-1"></i> Pre-Dispensing Action Required</div>
                        You have <strong>{{ $metrics['insurance']['pending_count'] }}</strong> insurance claims awaiting verification before dispensing. Ensure patient membership is verified before releasing medicines.
                    </div>
                @else
                    <div class="text-center text-muted py-3 small">
                        <i class="bi bi-check-circle-fill text-success fs-4 d-block mb-1"></i>
                        No pending insurance verifications. Ready to dispense.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- 4. MY RECENT DISPENSING HISTORY (Shift Audit for Pharmacist)              -->
<!-- ========================================================================= -->
<div class="card-laravel bg-white border shadow-sm animate-fade-in mb-4">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="fw-bold text-dark mb-0">
                <i class="bi bi-clock-history text-primary me-2"></i> My Recent Patient Dispensing Encounters
            </h5>
            <small class="text-muted">Direct dispensing history performed by your account during this shift</small>
        </div>
        <a href="{{ route('reports.index', ['tab' => 'sales', 'my_sales' => '1']) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
            <i class="bi bi-receipt me-1"></i> My Full Sales Report
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small">
                    <tr>
                        <th>Receipt #</th>
                        <th>Patient / Customer</th>
                        <th>Medicines Dispensed</th>
                        <th>Payment Method</th>
                        <th>Time</th>
                        <th class="text-end">Details</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($metrics['pharmacist']['my_recent_dispenses'] as $txn)
                        <tr>
                            <td class="fw-bold text-primary">#{{ str_pad($txn->id, 5, '0', STR_PAD_LEFT) }}</td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $txn->patient?->name ?? 'Walk-in Patient' }}</div>
                                <small class="text-muted">{{ $txn->patient?->phone ?? 'No phone' }}</small>
                            </td>
                            <td>
                                @if($txn->items && $txn->items->count() > 0)
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($txn->items->take(3) as $it)
                                            <span class="badge bg-light text-dark border rounded-pill" style="font-size: 0.75rem;">
                                                {{ $it->drug?->name ?? 'Med' }} ({{ $it->quantity }})
                                            </span>
                                        @endforeach
                                        @if($txn->items->count() > 3)
                                            <span class="badge bg-light text-muted border rounded-pill" style="font-size: 0.75rem;">
                                                +{{ $txn->items->count() - 3 }} more
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted small">Dispensed items</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-dark border px-2 py-1 rounded-pill">
                                    {{ $txn->payment_method ?? 'Cash' }}
                                </span>
                            </td>
                            <td class="text-muted small">
                                {{ $txn->created_at ? $txn->created_at->format('H:i, d M') : '-' }}
                            </td>
                            <td class="text-end">
                                <a href="{{ route('reports.index', ['tab' => 'sales', 'my_sales' => '1']) }}" class="btn btn-sm btn-light border rounded-pill px-3">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="bi bi-capsule text-muted fs-3 d-block mb-1"></i>
                                No dispensing encounters recorded by your account yet today. Open POS to start dispensing.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- 5. MY STOCK REFILL REQUESTS (Mnyororo wa Kuomba Stoki na Meneja)           -->
<!-- ========================================================================= -->
@php
    $myRefills = $metrics['pending_refills'] ?? collect();
@endphp
<div class="card-laravel bg-white border border-start border-4 border-warning shadow-sm animate-fade-in mb-4">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-warning text-dark fw-bold px-2 py-1 rounded-pill" style="font-size: 0.72rem;">
                    <i class="bi bi-box-arrow-in-down me-1"></i> Stock Replenishment
                </span>
                <span class="text-muted small">| Dispensary to Main Store</span>
            </div>
            <h5 class="fw-bold text-dark mb-0">
                <i class="bi bi-boxes text-warning me-2"></i> Dispensary Stock Refill Requests
            </h5>
            <small class="text-muted">Track refill requests submitted to Manager. When approved, new batch inventory is automatically credited to the dispensary.</small>
        </div>
        <a href="{{ route('pos.index') }}" class="btn btn-sm btn-outline-warning text-dark fw-bold rounded-pill px-3">
            <i class="bi bi-plus-circle me-1"></i> Request from POS
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small">
                    <tr>
                        <th>Drug Name</th>
                        <th>Classification</th>
                        <th>Requested Qty</th>
                        <th>Current Dispensary Stock</th>
                        <th>Status</th>
                        <th>Requested At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($myRefills as $rf)
                        <tr>
                            <td>
                                <strong class="text-dark">{{ $rf->drug?->name ?? 'Medicine' }}</strong>
                                <small class="text-muted d-block">{{ $rf->notes ?? 'Stock low on active shelf' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                    {{ $rf->drug?->tmda_short ?? 'POM' }}
                                </span>
                            </td>
                            <td>
                                <strong class="text-dark fs-6">{{ $rf->requested_quantity }} units</strong>
                            </td>
                            <td>
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                    {{ $rf->drug?->stock ?? 0 }} in shelf
                                </span>
                            </td>
                            <td>
                                @if($rf->status === 'pending')
                                    <span class="badge bg-warning text-dark px-2 py-1">
                                        <i class="bi bi-hourglass-split me-1"></i> Awaiting Manager Approval
                                    </span>
                                @elseif($rf->status === 'approved')
                                    <span class="badge bg-success text-white px-2 py-1">
                                        <i class="bi bi-check-circle me-1"></i> Approved ({{ $rf->approved_quantity }} units)
                                    </span>
                                @else
                                    <span class="badge bg-danger text-white px-2 py-1">Rejected</span>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">{{ $rf->created_at->diffForHumans() }}</small>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="bi bi-box2 text-muted fs-3 d-block mb-1"></i>
                                No active pending stock refill requests. You can request stock refills for any yellow/red item directly in the POS screen.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Global Drug Search Script -->
<script>
let searchTimeout = null;

function searchDrugsGlobal(query) {
    clearTimeout(searchTimeout);
    const resultsContainer = document.getElementById('drugSearchResults');
    const resultsBody = document.getElementById('drugSearchResultsBody');
    const resultsCountText = document.getElementById('resultsCountText');

    if (!query || query.trim().length < 2) {
        resultsContainer.classList.add('d-none');
        return;
    }

    searchTimeout = setTimeout(() => {
        fetch(`{{ route('pos.search') }}?q=${encodeURIComponent(query.trim())}`)
            .then(res => res.json())
            .then(data => {
                resultsBody.innerHTML = '';
                if (!data || data.length === 0) {
                    resultsCountText.innerText = `No medications found matching "${query}"`;
                    resultsBody.innerHTML = `
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="bi bi-question-circle fs-3 text-warning d-block mb-1"></i>
                                No medicine found for "<strong>${query}</strong>". Check spelling or verify with inventory management.
                            </td>
                        </tr>
                    `;
                } else {
                    resultsCountText.innerText = `Found ${data.length} matching medication${data.length > 1 ? 's' : ''}`;
                    data.forEach(item => {
                        let tmda = item.tmda_short || 'POM';
                        let stockLight = item.stock_light;
                        let stock = parseInt(item.stock) || 0;
                        let reorder = parseInt(item.reorder_level) || 10;
                        if(!stockLight) {
                            if(stock <= 0) stockLight = 'red';
                            else if(stock <= reorder) stockLight = 'yellow';
                            else stockLight = 'green';
                        }

                        let stockBadge = '';
                        if(stockLight === 'red') {
                            stockBadge = '<span class="badge bg-danger text-white rounded-pill px-2">🔴 Out of Stock (0)</span>';
                        } else if(stockLight === 'yellow') {
                            stockBadge = `<span class="badge bg-warning text-dark border border-warning rounded-pill px-2">🟡 Low Stock (${stock})</span>`;
                        } else {
                            stockBadge = `<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2">🟢 In Stock (${stock})</span>`;
                        }

                        const expiryText = item.expiry_date 
                            ? `<span class="text-muted small">${item.expiry_date}</span>`
                            : '<span class="text-muted small">Standard</span>';

                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>
                                <strong class="text-dark">${item.name}</strong>
                                ${item.brand_name ? `<div class="small text-muted">Brand: ${item.brand_name}</div>` : ''}
                            </td>
                            <td><span class="text-muted small">${item.generic_name || '-'}</span></td>
                            <td>
                                <span class="badge bg-light text-dark border">${item.dosage_form || 'Tablet'}</span>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-1">${tmda}</span>
                            </td>
                            <td>${stockBadge}</td>
                            <td class="fw-bold text-dark">TZS ${Number(item.price || 0).toLocaleString()}</td>
                            <td>${expiryText}</td>
                            <td class="text-end">
                                <a href="{{ route('pos.index') }}" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold">
                                    <i class="bi bi-cart3 me-1"></i> Dispense in POS
                                </a>
                            </td>
                        `;
                        resultsBody.appendChild(tr);
                    });
                }
                resultsContainer.classList.remove('d-none');
            })
            .catch(err => {
                console.error("Search error:", err);
            });
    }, 250);
}

function clearDrugSearch() {
    const input = document.getElementById('pharmacistDrugSearch');
    input.value = '';
    document.getElementById('drugSearchResults').classList.add('d-none');
    input.focus();
}
</script>
