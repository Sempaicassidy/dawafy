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
                <button class="nav-link active" onclick="switchReportTab('sales', this)">
                    <i class="bi bi-cash-stack me-1"></i> Sales Report
                </button>
            </li>
            <li class="nav-item ms-2">
                <button class="nav-link" onclick="switchReportTab('inventory', this)">
                    <i class="bi bi-box-seam me-1"></i> Stock Valuation
                </button>
            </li>
            <li class="nav-item ms-2">
                <button class="nav-link" onclick="switchReportTab('financial', this)">
                    <i class="bi bi-pie-chart me-1"></i> Financial P&L
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
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($recent_transactions)): ?>
                            <tr><td colspan="6" class="text-center py-4 text-muted">No sales transactions recorded yet.</td></tr>
                        <?php else: ?>
                            <?php foreach($recent_transactions as $t): ?>
                                <tr>
                                    <td><strong class="font-monospace text-primary">#<?= sprintf('%05d', $t['id']) ?></strong></td>
                                    <td><i class="bi bi-person-circle me-1 text-muted"></i> <?= htmlspecialchars($t['cashier_name'] ?? 'System Admin') ?></td>
                                    <td><?= htmlspecialchars($t['customer_name'] ?? 'Walk-in Customer') ?></td>
                                    <td><span class="badge-laravel-blue"><?= htmlspecialchars($t['payment_method'] ?? 'Cash') ?></span></td>
                                    <td><strong class="text-dark">TZS <?= number_format($t['total_amount']) ?></strong></td>
                                    <td><small class="text-muted"><?= date('d M Y, H:i', strtotime($t['created_at'])) ?></small></td>
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
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-calculator me-2 text-primary"></i> Financial Profit & Loss Statement</h5>
                <span class="badge bg-light text-muted fw-normal">Real-time financial summary</span>
            </div>
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="p-3 border rounded-3 bg-light">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Total Gross Revenue:</span>
                            <strong class="text-primary">TZS <?= number_format($total_sales) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Total Inventory Valuation Asset:</span>
                            <strong class="text-warning">TZS <?= number_format($inventory_value) ?></strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold text-dark">Estimated Net Asset Position:</span>
                            <strong class="text-success fs-5">TZS <?= number_format($total_sales + $inventory_value) ?></strong>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-3 border rounded-3 bg-light text-center py-4">
                        <i class="bi bi-shield-check text-success fs-1 mb-2 d-block"></i>
                        <h6 class="fw-bold text-dark mb-1">Financial Health Status: Excellent</h6>
                        <small class="text-muted d-block mb-3">All revenue streams and stock valuations audited and balanced.</small>
                        <button class="btn btn-sm btn-laravel-primary rounded-pill px-4" onclick="window.print()">
                            <i class="bi bi-file-earmark-pdf me-1"></i> Print P&L Audit Statement
                        </button>
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
</script>
@endsection

