@extends('layouts.app')

@section('content')
<!-- Daawafy Executive Reports & Analytics View (White Tab Contrast & Master Layout) -->
<style>
    .card-header-reports {
        background: #f1f3f5;
        padding: 0.5rem 0.75rem;
        border-top-left-radius: 3px;
        border-top-right-radius: 3px;
        border-bottom: 1px solid #dee2e6;
    }
    #reportTabs .nav-link {
        color: #495057 !important;
        background-color: transparent !important;
        border: 1px solid #ced4da !important;
        padding: 0.35rem 0.85rem;
        font-weight: 500;
        border-radius: 3px;
        font-size: 0.8125rem;
    }
    #reportTabs .nav-link:hover {
        background-color: #e9ecef !important;
        color: #212529 !important;
    }
    #reportTabs .nav-link.active {
        color: #ffffff !important;
        background-color: #343a40 !important;
        border-color: #343a40 !important;
        box-shadow: none;
    }
</style>

<div class="laravel-hero-banner mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-graph-up-arrow me-2 text-primary"></i> Reports & Financial Analytics</h2>
        <p class="text-muted mb-0">Sales revenue, profit margins, stock valuation, and audit logs.</p>
    </div>
    <div>
        <button class="btn btn-outline-secondary px-3 shadow-sm" style="border-radius: 4px;" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Export / Print Report
        </button>
    </div>
</div>

<div class="card-laravel mb-4">
    <div class="card-header-reports">
        <ul class="nav nav-pills card-header-pills" id="reportTabs">
            <li class="nav-item">
                <button class="nav-link active" id="tabBtnSales" onclick="switchReportTab('sales', this)">
                    <i class="bi bi-cash-stack me-1"></i> Sales Report
                </button>
            </li>
            <li class="nav-item ms-2">
                <button class="nav-link" id="tabBtnInventory" onclick="switchReportTab('inventory', this)">
                    <i class="bi bi-box-seam me-1"></i> Stock Valuation
                </button>
            </li>
            <li class="nav-item ms-2">
                <button class="nav-link" id="tabBtnFinancial" onclick="switchReportTab('financial', this)">
                    <i class="bi bi-pie-chart me-1"></i> Profit & Loss (P&L)
                </button>
            </li>
        </ul>
    </div>
    <div class="card-body p-4">
        <!-- Top Metrics Summary Row -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="metric-widget metric-green">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="metric-title">Total Sales Revenue</div>
                            <div class="metric-value">TZS <?= number_format($total_sales) ?></div>
                        </div>
                        <div class="metric-icon"><i class="bi bi-cash-coin"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="metric-widget metric-blue">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="metric-title">Total Dispensed Orders</div>
                            <div class="metric-value"><?= number_format($total_orders) ?></div>
                        </div>
                        <div class="metric-icon"><i class="bi bi-receipt"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="metric-widget metric-purple">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="metric-title">Inventory Asset Valuation</div>
                            <div class="metric-value">TZS <?= number_format($inventory_value) ?></div>
                        </div>
                        <div class="metric-icon"><i class="bi bi-box-seam"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 1: Sales Report Panel -->
        <div id="tabPanelSales" class="report-panel">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-receipt me-2 text-primary"></i> Recent Sales Transactions</h5>
                <span class="badge bg-light text-muted fw-normal">Showing latest transactions</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Txn ID</th>
                            <th>Cashier</th>
                            <th>Customer</th>
                            <th>Payment Method</th>
                            <th>Total Amount</th>
                            <th>Date & Time</th>
                            <th class="text-end">Receipt Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($recent_transactions)): ?>
                            <tr><td colspan="7" class="text-center py-4 text-muted">No sales transactions recorded yet.</td></tr>
                        <?php else: ?>
                            <?php foreach($recent_transactions as $t): ?>
                                <tr>
                                    <td><strong class="font-monospace text-primary">#<?= sprintf('%05d', $t['id']) ?></strong></td>
                                    <td><i class="bi bi-person-circle me-1 text-muted"></i> <?= htmlspecialchars($t['cashier_name'] ?? 'System Admin') ?></td>
                                    <td><?= htmlspecialchars($t['customer_name'] ?? 'Walk-in Customer') ?></td>
                                    <td><span class="badge-laravel-blue"><?= htmlspecialchars($t['payment_method'] ?? 'Cash') ?></span></td>
                                    <td><strong class="text-dark">TZS <?= number_format($t['total_amount']) ?></strong></td>
                                    <td><small class="text-muted"><?= date('d M Y, H:i', strtotime($t['created_at'])) ?></small></td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-1">
                                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold" onclick="viewReceiptDetails(<?= $t['id'] ?>)" title="View and read receipt details">
                                                <i class="bi bi-eye me-1"></i> Read Receipt
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2" onclick="viewReceiptDetails(<?= $t['id'] ?>, true)" title="Reprint receipt">
                                                <i class="bi bi-printer"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab 2: Stock Valuation Panel -->
        <div id="tabPanelInventory" class="report-panel d-none">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-boxes me-2 text-primary"></i> Inventory Valuation Breakdown</h5>
                <span class="badge bg-light text-muted fw-normal">Valuation based on current stock x unit price</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Medicine Name</th>
                            <th>Brand / Manufacturer</th>
                            <th>Category</th>
                            <th>In-Stock Quantity</th>
                            <th>Unit Price</th>
                            <th>Total Stock Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($stock_valuation_list)): ?>
                            <tr><td colspan="6" class="text-center py-4 text-muted">No stock valuation records available.</td></tr>
                        <?php else: ?>
                            <?php foreach($stock_valuation_list as $s): ?>
                                <tr>
                                    <td><strong class="text-dark"><?= htmlspecialchars($s['name']) ?></strong></td>
                                    <td><small class="text-muted"><?= htmlspecialchars($s['brand_name'] ?? 'Generic') ?></small></td>
                                    <td><span class="badge-laravel-green"><?= htmlspecialchars($s['category'] ?? 'POM') ?></span></td>
                                    <td><span class="fw-bold text-primary"><?= number_format($s['quantity']) ?> units</span></td>
                                    <td>TZS <?= number_format($s['price']) ?></td>
                                    <td><strong class="text-success">TZS <?= number_format($s['total_val']) ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab 3: Financial P&L Panel -->
        <div id="tabPanelFinancial" class="report-panel d-none">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-calculator me-2 text-primary"></i> Financial Profit & Loss Statement (P&L)</h5>
                <span class="badge bg-light text-muted fw-normal">Real-time revenue & expense ledger</span>
            </div>
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="p-3 border rounded-3 bg-light">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted"><i class="bi bi-graph-up-arrow text-success me-1"></i> Total Gross Revenue (Sales):</span>
                            <strong class="text-success fs-6">+ TZS <?= number_format($total_sales) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted"><i class="bi bi-arrow-down-right text-danger me-1"></i> Operating Expenses Outflow:</span>
                            <strong class="text-danger fs-6">- TZS <?= number_format($total_expenses) ?></strong>
                        </div>
                        <hr class="my-2">
                        @php
                            $netOperatingIncome = $total_sales - $total_expenses;
                        @endphp
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-bold text-dark">Net Operating Cash Flow:</span>
                            <strong class="<?= $netOperatingIncome >= 0 ? 'text-success' : 'text-danger' ?> fs-5">
                                TZS <?= number_format($netOperatingIncome) ?>
                            </strong>
                        </div>
                        <div class="d-flex justify-content-between text-muted small pt-1 border-top">
                            <span><i class="bi bi-box-seam text-warning me-1"></i> Stock Inventory Asset Valuation:</span>
                            <strong class="text-dark">TZS <?= number_format($inventory_value) ?></strong>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-3 border rounded-3 bg-light">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-pie-chart text-primary me-1"></i> Expenses by Category</h6>
                            <a href="{{ route('expenses.index') }}" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-0" style="font-size: 0.75rem;">Manage Expenses</a>
                        </div>
                        @if($expenses_by_category->isEmpty())
                            <div class="text-center py-3 text-muted small">
                                No expenses recorded yet.
                            </div>
                        @else
                            <div class="table-responsive" style="max-height: 180px; overflow-y: auto;">
                                <table class="table table-sm table-hover mb-0" style="font-size: 0.8125rem;">
                                    <tbody>
                                        @foreach($expenses_by_category as $exp)
                                            <tr>
                                                <td class="text-dark fw-semibold">{{ $exp->category }}</td>
                                                <td class="text-muted text-center">{{ $exp->count }} entries</td>
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
        </div>
    </div>
</div>

<script>
function switchReportTab(tabKey, btnElement) {
    // Reset tabs buttons
    document.querySelectorAll('#reportTabs .nav-link').forEach(btn => btn.classList.remove('active'));
    if(btnElement) btnElement.classList.add('active');

    // Hide all panels
    document.getElementById('tabPanelSales').classList.add('d-none');
    document.getElementById('tabPanelInventory').classList.add('d-none');
    document.getElementById('tabPanelFinancial').classList.add('d-none');

    // Show target panel
    if(tabKey === 'sales') document.getElementById('tabPanelSales').classList.remove('d-none');
    else if(tabKey === 'inventory') document.getElementById('tabPanelInventory').classList.remove('d-none');
    else if(tabKey === 'financial') document.getElementById('tabPanelFinancial').classList.remove('d-none');
}

document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const tab = urlParams.get('tab') || window.location.hash.replace('#', '');
    if (tab === 'financial' || tab === 'profit_loss' || tab === 'pnl') {
        const btn = document.getElementById('tabBtnFinancial');
        if (btn) switchReportTab('financial', btn);
    } else if (tab === 'inventory' || tab === 'valuation') {
        const btn = document.getElementById('tabBtnInventory');
        if (btn) switchReportTab('inventory', btn);
    } else if (tab === 'sales') {
        const btn = document.getElementById('tabBtnSales');
        if (btn) switchReportTab('sales', btn);
    }
});

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
        
        let cashierName = (txn.cashier && txn.cashier.name) ? txn.cashier.name : (txn.user ? txn.user.name : "{{ auth()->user()?->name ?? 'Cashier' }}");
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
@endsection

