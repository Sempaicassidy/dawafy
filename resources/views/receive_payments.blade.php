@extends('layouts.app')

@section('title', 'Receive Payments - Cashier Desk')

@section('content')
<div class="container-fluid px-0">
    <!-- Top Command Header -->
    <div class="laravel-hero-banner mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3 pb-3 border-bottom animate-fade-in">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-success text-white fw-bold px-2 py-1 rounded-pill" style="font-size: 0.75rem;">
                    <i class="bi bi-cash-coin me-1"></i> Cashier Payment Desk
                </span>
                <span class="text-muted small">| Separated Workflow</span>
            </div>
            <h2 class="fw-bold mb-1 text-dark">Receive Payments & Billing Desk</h2>
            <p class="text-muted mb-0 small">Collect patient payments for prescriptions sent by pharmacists, issue official receipts, and authorize medicine dispensation.</p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <button type="button" class="btn btn-outline-primary rounded-pill px-3 btn-sm fw-bold shadow-sm" onclick="fetchPendingQueue(true)">
                <i class="bi bi-arrow-clockwise me-1" id="refreshIcon"></i> Refresh Queue
            </button>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary rounded-pill px-3 btn-sm fw-semibold">
                <i class="bi bi-speedometer2 me-1"></i> Cashier Dashboard
            </a>
            <a href="{{ route('shifts.index') }}" class="btn btn-outline-secondary rounded-pill px-3 btn-sm fw-semibold">
                <i class="bi bi-clock-history me-1 text-info"></i> Shift Logs
            </a>
        </div>
    </div>

    <!-- Live Counter Cards -->
    <div class="row g-3 mb-4 animate-fade-in">
        <div class="col-sm-6 col-md-4">
            <div class="card-laravel p-3 bg-white border border-start border-4 border-warning shadow-sm h-100">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem;">Pending Prescriptions</span>
                    <span class="badge bg-warning text-dark fw-bold rounded-pill" id="pendingBadgeCount">{{ $pendingPayments->count() }}</span>
                </div>
                <h3 class="fw-bold text-dark mb-0 tabular-nums" id="pendingSummaryCount">{{ $pendingPayments->count() }}</h3>
                <small class="text-muted">Awaiting customer payment</small>
            </div>
        </div>
        <div class="col-sm-6 col-md-4">
            <div class="card-laravel p-3 bg-white border border-start border-4 border-success shadow-sm h-100">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem;">Receipts Paid Today</span>
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">{{ $todayPaidReceipts->count() }}</span>
                </div>
                <h3 class="fw-bold text-success mb-0 tabular-nums">{{ $todayPaidReceipts->count() }}</h3>
                <small class="text-muted">Cleared & sent to dispensing</small>
            </div>
        </div>
        <div class="col-sm-12 col-md-4">
            <div class="card-laravel p-3 bg-white border border-start border-4 border-primary shadow-sm h-100">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem;">Today's Revenue Collected</span>
                    <i class="bi bi-wallet2 text-primary fs-5"></i>
                </div>
                <h3 class="fw-bold text-primary mb-0 tabular-nums">TZS {{ number_format($todayPaidReceipts->sum('total_amount')) }}</h3>
                <small class="text-muted">Total collected in today's shift</small>
            </div>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="card-laravel bg-white p-3 mb-4 border shadow-sm">
        <div class="row g-2 align-items-center">
            <div class="col-md-6 col-lg-7">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted">
                        <i class="bi bi-search text-primary"></i>
                    </span>
                    <input type="text" 
                           id="queueSearchInput" 
                           class="form-control border-start-0" 
                           placeholder="Filter by Patient Name, Phone number, Invoice #, or Pharmacist..."
                           onkeyup="filterQueueCards(this.value)">
                    <button class="btn btn-outline-secondary" type="button" onclick="clearQueueSearch()">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
            <div class="col-md-6 col-lg-5 text-md-end">
                <span class="text-muted small me-2" id="autoSyncLabel">
                    <i class="bi bi-broadcast text-success me-1"></i> Live queue auto-sync active
                </span>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs (Pending Queue vs Today's Receipts) -->
    <ul class="nav nav-pills mb-4" id="pills-tab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active fw-bold px-4 rounded-pill" id="pills-pending-tab" data-bs-toggle="pill" data-bs-target="#pills-pending" type="button" role="tab">
                <i class="bi bi-clock-history me-1 text-warning"></i> Pending Payments (<span id="navPendingCount">{{ $pendingPayments->count() }}</span>)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold px-4 rounded-pill" id="pills-history-tab" data-bs-toggle="pill" data-bs-target="#pills-history" type="button" role="tab">
                <i class="bi bi-receipt-cutoff me-1 text-success"></i> Today's Paid Receipts ({{ $todayPaidReceipts->count() }})
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="pills-tabContent">
        <!-- ================================================================= -->
        <!-- TAB 1: PENDING PAYMENTS QUEUE                                      -->
        <!-- ================================================================= -->
        <div class="tab-pane fade show active" id="pills-pending" role="tabpanel">
            <div id="pendingCardsContainer">
                @if($pendingPayments->isEmpty())
                    <div class="card-laravel bg-white border shadow-sm p-5 text-center text-muted" id="emptyStateBox">
                        <i class="bi bi-check2-circle fs-1 text-success d-block mb-2"></i>
                        <h5 class="fw-bold text-dark mb-1">No Pending Payments Right Now</h5>
                        <p class="mb-0 text-muted small">When a pharmacist prepares a prescription and clicks "Send to Cashier", it will appear here immediately.</p>
                    </div>
                @else
                    <div class="row g-3" id="pendingOrdersGrid">
                        @foreach($pendingPayments as $pt)
                            @php
                                $custName = $pt->customer?->name ?? 'Walk-in Patient';
                                $custPhone = $pt->customer?->phone ?? 'No phone';
                                $pharmName = $pt->pharmacist?->name ?? 'Pharmacist';
                            @endphp
                            <div class="col-md-6 col-lg-4 pending-card-col" 
                                 id="card-order-{{ $pt->id }}"
                                 data-search="{{ strtolower($pt->transaction_id . ' ' . $custName . ' ' . $custPhone . ' ' . $pharmName) }}">
                                <div class="card h-100 border shadow-sm rounded-4 border-start border-4 border-warning bg-white">
                                    <div class="card-header bg-white border-bottom py-2 px-3 d-flex justify-content-between align-items-center rounded-top-4">
                                        <div>
                                            <strong class="text-dark">{{ $pt->transaction_id ?? ('#TXN-' . $pt->id) }}</strong>
                                            <span class="badge bg-warning text-dark ms-1" style="font-size: 0.68rem;">Pending</span>
                                        </div>
                                        <small class="text-muted" title="{{ $pt->created_at->format('d M Y H:i:s') }}">
                                            <i class="bi bi-clock me-1"></i> {{ $pt->created_at->diffForHumans() }}
                                        </small>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <div class="rounded-circle bg-primary-subtle text-primary p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                                <i class="bi bi-person-fill fs-5"></i>
                                            </div>
                                            <div>
                                                <strong class="text-dark d-block lh-sm">{{ $custName }}</strong>
                                                <small class="text-muted">{{ $custPhone }}</small>
                                            </div>
                                        </div>

                                        <div class="mb-2">
                                            <small class="text-muted d-block" style="font-size: 0.72rem;">Prescribed By:</small>
                                            <span class="badge bg-light text-secondary border">
                                                <i class="bi bi-person-badge me-1"></i> {{ $pharmName }}
                                            </span>
                                        </div>

                                        <!-- Items List -->
                                        <div class="bg-light p-2 rounded-3 border mb-3" style="max-height: 120px; overflow-y: auto;">
                                            <table class="table table-sm table-borderless align-middle mb-0" style="font-size: 0.78rem;">
                                                <tbody>
                                                    @foreach($pt->items as $item)
                                                        <tr>
                                                            <td class="text-dark fw-semibold ps-0">{{ $item->drug?->name ?? 'Medicine' }}</td>
                                                            <td class="text-center text-muted">&times; {{ $item->quantity }}</td>
                                                            <td class="text-end text-dark pe-0">TZS {{ number_format($item->quantity * $item->price) }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>

                                        <!-- Amount & Action -->
                                        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                            <div>
                                                <small class="text-muted text-uppercase d-block" style="font-size: 0.68rem;">Total Bill</small>
                                                <strong class="text-success fs-5 tabular-nums">TZS {{ number_format($pt->total_amount) }}</strong>
                                            </div>
                                            <button type="button" class="btn btn-success fw-bold rounded-pill px-4 shadow-sm" onclick="openReceivePaymentModal({{ $pt->id }})">
                                                <i class="bi bi-credit-card me-1"></i> Receive Payment
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- ================================================================= -->
        <!-- TAB 2: TODAY'S PAID RECEIPTS (Audit & Re-print)                    -->
        <!-- ================================================================= -->
        <div class="tab-pane fade" id="pills-history" role="tabpanel">
            <div class="card-laravel bg-white border shadow-sm">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold text-dark mb-0"><i class="bi bi-receipt text-success me-2"></i> Today's Cleared Receipts</h5>
                        <small class="text-muted">Prescriptions billed and approved today. Ready or completed dispensing.</small>
                    </div>
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2 fw-bold">
                        {{ $todayPaidReceipts->count() }} Payments Collected
                    </span>
                </div>
                <div class="card-body p-0">
                    @if($todayPaidReceipts->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-receipt-cutoff fs-1 d-block mb-2"></i>
                            <p class="mb-0 fw-semibold">No payments collected yet in today's shift.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light text-muted small">
                                    <tr>
                                        <th>Receipt / Order</th>
                                        <th>Patient / Customer</th>
                                        <th>Prescribed By</th>
                                        <th>Payment Method</th>
                                        <th>Amount</th>
                                        <th>Time Paid</th>
                                        <th class="text-end">Receipt Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($todayPaidReceipts as $rt)
                                        <tr>
                                            <td>
                                                <strong class="text-dark">{{ $rt->transaction_id ?? ('#TXN-' . $rt->id) }}</strong>
                                                <span class="badge bg-success text-white ms-1" style="font-size: 0.68rem;">PAID</span>
                                            </td>
                                            <td>
                                                <div class="fw-semibold text-dark">{{ $rt->customer?->name ?? 'Walk-in Patient' }}</div>
                                                <small class="text-muted">{{ $rt->customer?->phone ?? 'No phone' }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-secondary border">
                                                    {{ $rt->pharmacist?->name ?? 'Pharmacist' }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                                    {{ $rt->payment_method ?? 'Cash' }}
                                                </span>
                                            </td>
                                            <td>
                                                <strong class="text-dark tabular-nums">TZS {{ number_format($rt->total_amount) }}</strong>
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $rt->updated_at->format('H:i') }} ({{ $rt->updated_at->diffForHumans() }})</small>
                                            </td>
                                            <td class="text-end">
                                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold" onclick="reprintReceipt({{ $rt->id }})">
                                                    <i class="bi bi-printer me-1"></i> Print Receipt
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
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: RECEIVE PAYMENT DIALOG                                             -->
<!-- ========================================================================= -->
<div class="modal fade" id="receivePayModal" tabindex="-1" aria-labelledby="receivePayModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-success text-white py-3 px-4">
                <div>
                    <h5 class="modal-title fw-bold mb-0" id="receivePayModalLabel">
                        <i class="bi bi-cash-coin me-2"></i> Receive Patient Payment
                    </h5>
                    <small class="text-white-50">Approve payment & notify pharmacist to release medicines</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="rpmTxnIdInput">

                <!-- Order Meta Card -->
                <div class="bg-light p-3 rounded-3 border mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-primary text-white fw-bold px-2 py-1" id="rpmOrderNo">#TXN-0</span>
                        <span class="text-muted small" id="rpmOrderTime"></span>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <small class="text-muted d-block" style="font-size: 0.72rem;">Patient Name</small>
                            <strong class="text-dark d-block" id="rpmPatientName">Walk-in Patient</strong>
                            <small class="text-muted" id="rpmPatientPhone">-</small>
                        </div>
                        <div class="col-6 text-end">
                            <small class="text-muted d-block" style="font-size: 0.72rem;">Prescribed By</small>
                            <span class="badge bg-info-subtle text-info border border-info-subtle" id="rpmPharmacistName">Pharmacist</span>
                        </div>
                    </div>
                </div>

                <!-- Prescription Items Breakdown -->
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary text-uppercase mb-1" style="font-size: 0.72rem;">Prescription Items</label>
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
                            <tbody id="rpmItemsBody"></tbody>
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
                        <span class="fs-4 fw-bold text-success tabular-nums" id="rpmTotalAmount">TZS 0</span>
                    </div>
                </div>

                <!-- Payment Method Selector -->
                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark mb-1">
                        Select Payment Method <span class="text-danger">*</span>
                    </label>
                    @php
                        $rpmActiveMethods = \App\Models\PaymentMethod::getActiveMethods();
                    @endphp
                    <select id="rpmPaymentMethod" class="form-select form-select-lg fw-semibold shadow-sm" onchange="toggleRpmInsuranceFields()">
                        @foreach($rpmActiveMethods as $pm)
                            <option value="{{ $pm->name }}" {{ $pm->name === 'Cash' ? 'selected' : '' }}>
                                @if($pm->name === 'Cash') 💵 Pesa Taslimu (Cash)
                                @elseif($pm->name === 'Mobile Money') 📱 Mobile Money (M-Pesa / Tigo Pesa / Airtel / HaloPesa)
                                @elseif($pm->name === 'Card') 💳 Kadi ya Benki (POS / Visa / Mastercard)
                                @elseif($pm->name === 'Insurance') 🏥 Bima ya Afya (NHIF / Jubilee / Strategis)
                                @elseif($pm->name === 'Wallet') 👛 Customer Wallet (Akaunti ya Mteja)
                                @else {{ $pm->display_name }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Real-time Insurance Verification Section (Visible when Insurance is selected) -->
                <div id="rpmInsuranceContainer" class="p-3 mb-3 rounded-4 border bg-light shadow-sm" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold small text-primary"><i class="bi bi-shield-check me-1"></i> Electronic Insurance Verification</span>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill small">Real-time Gateway</span>
                    </div>

                    <div class="mb-2">
                        <label class="small text-secondary fw-semibold mb-1">Insurance Provider <span class="text-danger">*</span></label>
                        <select id="rpmInsProvider" class="form-select form-select-sm rounded-3">
                            <option value="1">NHIF Tanzania (National Health Insurance)</option>
                            <option value="2">Jubilee Insurance (Smart EDI)</option>
                            <option value="3">Strategis Insurance Tanzania</option>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="small text-secondary fw-semibold mb-1">Card / Member Number <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <input type="text" id="rpmInsCardNo" class="form-control font-monospace rounded-start-3" placeholder="e.g. 01-88492014 or JUB-4491">
                            <button class="btn btn-primary px-3 rounded-end-3" type="button" id="btnVerifyRpmCard" onclick="verifyInsuranceCardInReceivePay()">
                                <i class="bi bi-search me-1"></i> Verify Card
                            </button>
                        </div>
                        <small class="text-muted d-block mt-1" style="font-size: 0.72rem;">Try: <code>01-88492014</code> (NHIF), <code>JUB-4491</code> (Jubilee), <code>STRAT-1029</code> (Strategis).</small>
                    </div>

                    <!-- Verified Badge Banner -->
                    <div id="rpmInsVerifyBadge" class="mt-2 mb-2" style="display: none;">
                        <div class="card p-2 rounded-3 shadow-sm border border-success bg-success-subtle" id="rpmInsBadgeCard">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="badge bg-success rounded-pill px-2 py-1" id="rpmInsStatusBadge">ACTIVE</span>
                                <span class="small font-monospace fw-bold text-success" id="rpmInsAuthBadge">AUTH: ---</span>
                            </div>
                            <strong class="text-dark small d-block" id="rpmInsMemberName">---</strong>
                            <small class="text-muted" id="rpmInsScheme">Scheme: ---</small>
                        </div>
                    </div>

                    <input type="hidden" id="rpmInsVerifiedMember">
                    <input type="hidden" id="rpmInsVerifiedAuth">
                    <input type="hidden" id="rpmInsVerifiedScheme">

                    <div class="row g-2 mt-1">
                        <div class="col-6">
                            <label class="small text-secondary fw-semibold mb-1">Prescription / Form 2C #</label>
                            <input type="text" id="rpmInsReferral" class="form-control form-control-sm rounded-3 font-monospace" placeholder="e.g. 2C-90142">
                        </div>
                        <div class="col-6">
                            <label class="small text-secondary fw-semibold mb-1">Doctor / Prescriber</label>
                            <input type="text" id="rpmInsDoctor" class="form-control form-control-sm rounded-3" placeholder="e.g. Dr. Mushi">
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="button" class="btn btn-success btn-lg w-100 rounded-pill fw-bold shadow-sm" id="rpmSubmitBtn" onclick="submitPaymentApproval()">
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
<!-- MODAL: THERMAL RECEIPT PRINT TEMPLATE                                     -->
<!-- ========================================================================= -->
<div class="modal fade" id="receiptModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-body text-center p-4">
        <div class="rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center mb-3" style="width: 52px; height: 52px;">
            <i class="bi bi-check-lg fs-2"></i>
        </div>
        <h5 class="fw-bold text-success mb-3">Payment Approved Successfully!</h5>
        
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
window.deskPendingOrders = @json($pendingPayments->keyBy('id'));
window.deskPaidReceipts = @json($todayPaidReceipts->keyBy('id'));

function openReceivePaymentModal(txnId) {
    let t = window.deskPendingOrders[txnId];
    if(!t) {
        alert('Could not find order details. Please refresh the queue.');
        return;
    }

    document.getElementById('rpmTxnIdInput').value = t.id;
    document.getElementById('rpmOrderNo').innerText = t.transaction_id || ('#TXN-' + t.id);
    
    let custName = (t.customer && t.customer.name) ? t.customer.name : ((t.patient && t.patient.name) ? t.patient.name : 'Walk-in Patient');
    let custPhone = (t.customer && t.customer.phone) ? t.customer.phone : ((t.patient && t.patient.phone) ? t.patient.phone : 'No phone');
    document.getElementById('rpmPatientName').innerText = custName;
    document.getElementById('rpmPatientPhone').innerText = custPhone;

    let pharmName = (t.pharmacist && t.pharmacist.name) ? t.pharmacist.name : 'Pharmacist';
    document.getElementById('rpmPharmacistName').innerText = pharmName;

    let createdAt = t.created_at ? new Date(t.created_at).toLocaleTimeString() : '';
    document.getElementById('rpmOrderTime').innerText = createdAt;

    let total = parseFloat(t.total_amount) || 0;
    document.getElementById('rpmTotalAmount').innerText = 'TZS ' + total.toLocaleString();

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
    document.getElementById('rpmItemsBody').innerHTML = itemsHtml;
    document.getElementById('rpmPaymentMethod').value = 'Cash';
    toggleRpmInsuranceFields();

    new bootstrap.Modal(document.getElementById('receivePayModal')).show();
}

function toggleRpmInsuranceFields() {
    let method = document.getElementById('rpmPaymentMethod').value;
    let container = document.getElementById('rpmInsuranceContainer');
    if (container) {
        container.style.display = (method === 'Insurance') ? 'block' : 'none';
    }
}

function verifyInsuranceCardInReceivePay() {
    let providerId = document.getElementById('rpmInsProvider').value;
    let cardNo = document.getElementById('rpmInsCardNo').value.trim();
    let btn = document.getElementById('btnVerifyRpmCard');
    let badgeArea = document.getElementById('rpmInsVerifyBadge');
    let badgeCard = document.getElementById('rpmInsBadgeCard');

    if(!cardNo) {
        alert('Tafadhali ingiza nambari ya kadi ya bima!');
        document.getElementById('rpmInsCardNo').focus();
        return;
    }

    btn.disabled = true;
    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Verifying...`;

    fetch('{{ route('insurance.verify_card') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            provider_id: providerId,
            card_number: cardNo
        })
    })
    .then(async r => {
        const text = await r.text();
        try {
            return JSON.parse(text);
        } catch(e) {
            throw new Error('Seva haikurejesha JSON sahihi: ' + text.substring(0, 80));
        }
    })
    .then(res => {
        btn.disabled = false;
        btn.innerHTML = `<i class="bi bi-search me-1"></i> Verify Card`;
        badgeArea.style.display = 'block';

        if(res.is_valid) {
            badgeCard.className = 'card p-2 rounded-3 shadow-sm border border-success bg-success-subtle';
            document.getElementById('rpmInsStatusBadge').className = 'badge bg-success rounded-pill px-2 py-1';
            document.getElementById('rpmInsStatusBadge').innerText = 'ACTIVE (UTHIBITISHO UMEFAULU)';
            document.getElementById('rpmInsAuthBadge').innerText = res.authorization_code || 'AUTH: APPROVED';
            document.getElementById('rpmInsMemberName').innerText = res.member_name || 'Verified Member';
            document.getElementById('rpmInsScheme').innerText = 'Mpango / Scheme: ' + (res.scheme || 'Standard Cover');

            document.getElementById('rpmInsVerifiedMember').value = res.member_name || '';
            document.getElementById('rpmInsVerifiedAuth').value = res.authorization_code || '';
            document.getElementById('rpmInsVerifiedScheme').value = res.scheme || '';
        } else {
            badgeCard.className = 'card p-2 rounded-3 shadow-sm border border-danger bg-danger-subtle';
            document.getElementById('rpmInsStatusBadge').className = 'badge bg-danger rounded-pill px-2 py-1';
            document.getElementById('rpmInsStatusBadge').innerText = 'HAIRUHUSIWI / ' + (res.status || 'EXPIRED');
            document.getElementById('rpmInsAuthBadge').innerText = 'NO AUTH CODE';
            document.getElementById('rpmInsMemberName').innerText = res.member_name || 'Unknown Member';
            document.getElementById('rpmInsScheme').innerText = res.message || 'Kadi hii haijathibitishwa na bima.';

            document.getElementById('rpmInsVerifiedMember').value = '';
            document.getElementById('rpmInsVerifiedAuth').value = '';
            document.getElementById('rpmInsVerifiedScheme').value = '';
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = `<i class="bi bi-search me-1"></i> Verify Card`;
        alert('Kosa wakati wa kuwasiliana na seva ya bima: ' + err.message);
    });
}

function submitPaymentApproval() {
    let txnId = document.getElementById('rpmTxnIdInput').value;
    let paymentMethod = document.getElementById('rpmPaymentMethod').value;
    let submitBtn = document.getElementById('rpmSubmitBtn');

    if(!txnId) return;

    let fd = new FormData();
    fd.append('payment_method', paymentMethod);

    if(paymentMethod === 'Insurance') {
        let cardNo = document.getElementById('rpmInsCardNo').value.trim();
        let providerId = document.getElementById('rpmInsProvider').value;
        if(!cardNo) {
            alert('Tafadhali ingiza nambari ya kadi ya bima kabla ya kuidhinisha malipo!');
            document.getElementById('rpmInsCardNo').focus();
            return;
        }

        fd.append('insurance_provider_id', providerId);
        fd.append('card_number', cardNo);
        fd.append('member_number', cardNo);
        fd.append('member_name', document.getElementById('rpmInsVerifiedMember').value || '');
        fd.append('authorization_code', document.getElementById('rpmInsVerifiedAuth').value || '');
        fd.append('scheme_name', document.getElementById('rpmInsVerifiedScheme').value || '');
        fd.append('referral_number', document.getElementById('rpmInsReferral').value.trim());
    }

    submitBtn.disabled = true;
    submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> Approving Payment...`;

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
            // Hide modal
            let payModalEl = document.getElementById('receivePayModal');
            let payModal = bootstrap.Modal.getInstance(payModalEl);
            if(payModal) payModal.hide();

            // Populate receipt modal
            let txn = data.transaction || window.deskPendingOrders[txnId] || {};
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

            let custName = (txn.customer && txn.customer.name) ? txn.customer.name : ((txn.patient && txn.patient.name) ? txn.patient.name : null);
            if(custName) {
                document.getElementById('recCustomer').innerText = custName;
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

            // Remove card from grid
            let cardEl = document.getElementById('card-order-' + txnId);
            if(cardEl) cardEl.remove();
            delete window.deskPendingOrders[txnId];

            // Re-fetch queue to update counters
            fetchPendingQueue(false);

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

function reprintReceipt(txnId) {
    let txn = window.deskPaidReceipts[txnId];
    if(!txn) {
        alert('Receipt details not found.');
        return;
    }

    let totalAmount = parseFloat(txn.total_amount || 0);
    let subtotal = totalAmount / 1.18;
    let vat = totalAmount - subtotal;

    document.getElementById('recTxnId').innerText = txn.transaction_id || ('#TXN-' + txnId);
    document.getElementById('recDate').innerText = txn.updated_at ? new Date(txn.updated_at).toLocaleDateString() : new Date().toLocaleDateString();
    document.getElementById('recTime').innerText = txn.updated_at ? new Date(txn.updated_at).toLocaleTimeString() : new Date().toLocaleTimeString();
    document.getElementById('recSubtotal').innerText = 'TZS ' + Math.round(subtotal).toLocaleString();
    document.getElementById('recVat').innerText = 'TZS ' + Math.round(vat).toLocaleString();
    document.getElementById('recTotal').innerText = 'TZS ' + totalAmount.toLocaleString();
    document.getElementById('recMethod').innerText = txn.payment_method || 'Cash';

    let custName = (txn.customer && txn.customer.name) ? txn.customer.name : ((txn.patient && txn.patient.name) ? txn.patient.name : null);
    if(custName) {
        document.getElementById('recCustomer').innerText = custName;
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

    new bootstrap.Modal(document.getElementById('receiptModal')).show();
}

function printReceiptNow() {
    window.print();
}

function fetchPendingQueue(showSpin) {
    let icon = document.getElementById('refreshIcon');
    if(showSpin && icon) icon.classList.add('spin-animation');

    fetch(`{{ route('pos.pending_payments') }}`, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        if(icon) icon.classList.remove('spin-animation');
        if(!Array.isArray(data)) return;

        // Update counts
        document.getElementById('pendingBadgeCount').innerText = data.length;
        document.getElementById('pendingSummaryCount').innerText = data.length;
        document.getElementById('navPendingCount').innerText = data.length;

        window.deskPendingOrders = {};
        data.forEach(t => { window.deskPendingOrders[t.id] = t; });

        let container = document.getElementById('pendingCardsContainer');
        if(!container) return;

        if(data.length === 0) {
            container.innerHTML = `
                <div class="card-laravel bg-white border shadow-sm p-5 text-center text-muted" id="emptyStateBox">
                    <i class="bi bi-check2-circle fs-1 text-success d-block mb-2"></i>
                    <h5 class="fw-bold text-dark mb-1">No Pending Payments Right Now</h5>
                    <p class="mb-0 text-muted small">When a pharmacist prepares a prescription and clicks "Send to Cashier", it will appear here immediately.</p>
                </div>
            `;
            return;
        }

        let html = '<div class="row g-3" id="pendingOrdersGrid">';
        data.forEach(pt => {
            let custName = (pt.customer && pt.customer.name) ? pt.customer.name : ((pt.patient && pt.patient.name) ? pt.patient.name : 'Walk-in Patient');
            let custPhone = (pt.customer && pt.customer.phone) ? pt.customer.phone : ((pt.patient && pt.patient.phone) ? pt.patient.phone : 'No phone');
            let pharmName = (pt.pharmacist && pt.pharmacist.name) ? pt.pharmacist.name : 'Pharmacist';
            let totalFormatted = parseFloat(pt.total_amount || 0).toLocaleString();
            let items = Array.isArray(pt.items) ? pt.items : [];

            let itemsRows = items.map(item => `
                <tr>
                    <td class="text-dark fw-semibold ps-0">${item.drug ? item.drug.name : (item.name || 'Medicine')}</td>
                    <td class="text-center text-muted">&times; ${item.quantity || item.qty || 1}</td>
                    <td class="text-end text-dark pe-0">TZS ${parseFloat((item.quantity || 1) * (item.price || 0)).toLocaleString()}</td>
                </tr>
            `).join('');

            html += `
                <div class="col-md-6 col-lg-4 pending-card-col" 
                     id="card-order-${pt.id}"
                     data-search="${(pt.transaction_id + ' ' + custName + ' ' + custPhone + ' ' + pharmName).toLowerCase()}">
                    <div class="card h-100 border shadow-sm rounded-4 border-start border-4 border-warning bg-white">
                        <div class="card-header bg-white border-bottom py-2 px-3 d-flex justify-content-between align-items-center rounded-top-4">
                            <div>
                                <strong class="text-dark">${pt.transaction_id || ('#TXN-' + pt.id)}</strong>
                                <span class="badge bg-warning text-dark ms-1" style="font-size: 0.68rem;">Pending</span>
                            </div>
                            <small class="text-muted">
                                <i class="bi bi-clock me-1"></i> Just now
                            </small>
                        </div>
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <div class="rounded-circle bg-primary-subtle text-primary p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                    <i class="bi bi-person-fill fs-5"></i>
                                </div>
                                <div>
                                    <strong class="text-dark d-block lh-sm">${custName}</strong>
                                    <small class="text-muted">${custPhone}</small>
                                </div>
                            </div>

                            <div class="mb-2">
                                <small class="text-muted d-block" style="font-size: 0.72rem;">Prescribed By:</small>
                                <span class="badge bg-light text-secondary border">
                                    <i class="bi bi-person-badge me-1"></i> ${pharmName}
                                </span>
                            </div>

                            <div class="bg-light p-2 rounded-3 border mb-3" style="max-height: 120px; overflow-y: auto;">
                                <table class="table table-sm table-borderless align-middle mb-0" style="font-size: 0.78rem;">
                                    <tbody>
                                        ${itemsRows}
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                <div>
                                    <small class="text-muted text-uppercase d-block" style="font-size: 0.68rem;">Total Bill</small>
                                    <strong class="text-success fs-5 tabular-nums">TZS ${totalFormatted}</strong>
                                </div>
                                <button type="button" class="btn btn-success fw-bold rounded-pill px-4 shadow-sm" onclick="openReceivePaymentModal(${pt.id})">
                                    <i class="bi bi-credit-card me-1"></i> Receive Payment
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        container.innerHTML = html;
    })
    .catch(() => {
        if(icon) icon.classList.remove('spin-animation');
    });
}

function filterQueueCards(query) {
    let q = (query || '').toLowerCase().trim();
    document.querySelectorAll('.pending-card-col').forEach(col => {
        let text = col.getAttribute('data-search') || '';
        if(text.includes(q)) {
            col.classList.remove('d-none');
        } else {
            col.classList.add('d-none');
        }
    });
}

function clearQueueSearch() {
    let input = document.getElementById('queueSearchInput');
    if(input) {
        input.value = '';
        filterQueueCards('');
    }
}

// Auto-sync polling every 15 seconds
setInterval(() => { fetchPendingQueue(false); }, 15000);
</script>

<style>
.spin-animation {
    animation: spin 1s linear infinite;
}
@keyframes spin {
    100% { transform: rotate(360deg); }
}
</style>
@endsection
