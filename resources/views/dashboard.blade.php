@extends('layouts.app')

@section('content')
<!-- Traditional Pharmacy Dashboard View -->
<div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom animate-fade-in">
    <div>
        <div class="d-flex align-items-center gap-2">
            <h4 class="fw-bold text-dark mb-0">Pharmacy Dashboard</h4>
            <span class="badge bg-success text-success rounded-pill px-2 py-1"><span class="status-dot status-dot-live me-1"></span> Live Active</span>
        </div>
        <small class="text-muted">Summary of daily sales, low stock alerts, and real-time counter activity</small>
    </div>
    <div>
        <a href="{{ route('pos.index') }}" class="btn btn-primary btn-sm px-3 shadow-sm">
            <i class="bi bi-cart-plus me-1"></i> Open POS Terminal
        </a>
    </div>
</div>

<!-- Stat Counters (Semantic Colored Metric Cards with Staggered Animations) -->
<div class="row g-3 mb-4">
    <div class="col-md-3 animate-fade-in-up delay-1">
        <div class="metric-widget metric-green">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="metric-title">Today's Sales</div>
                    <div class="metric-value">TZS <?= number_format($metrics['today_sales']) ?></div>
                    <small class="text-muted"><?= $metrics['txn_count'] ?> transactions</small>
                </div>
                <div class="metric-icon">
                    <i class="bi bi-cash-stack"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 animate-fade-in-up delay-2">
        <div class="metric-widget metric-blue">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="metric-title">Sales Count</div>
                    <div class="metric-value"><?= $metrics['txn_count'] ?></div>
                    <small class="text-muted">Completed tickets</small>
                </div>
                <div class="metric-icon">
                    <i class="bi bi-receipt"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 animate-fade-in-up delay-3">
        <div class="metric-widget metric-yellow">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="metric-title">Low Stock Alert</div>
                    <div class="metric-value"><?= $metrics['low_stock'] ?></div>
                    <small class="text-muted">Below reorder minimum</small>
                </div>
                <div class="metric-icon">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 animate-fade-in-up delay-4">
        <div class="metric-widget metric-red">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="metric-title">Expiring Soon</div>
                    <div class="metric-value"><?= $metrics['expiring'] ?></div>
                    <small class="text-muted">Within 6 months</small>
                </div>
                <div class="metric-icon">
                    <i class="bi bi-clock-history"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Transactions Table Card -->
<div class="card animate-fade-in-up delay-5">
    <div class="card-header bg-light d-flex justify-content-between align-items-center py-2 px-3">
        <h6 class="mb-0 fw-semibold text-dark"><i class="bi bi-clock-history me-1"></i> Recent Sales Transactions</h6>
        <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-secondary py-1 px-2" style="font-size: 0.75rem;">View All Reports</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead>
                    <tr>
                        <th>Receipt #</th>
                        <th>Date & Time</th>
                        <th>Amount (TZS)</th>
                        <th>Payment Method</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($metrics['recent_txns'])): ?>
                        <tr><td colspan="5" class="text-center py-4 text-muted">No sales recorded today yet.</td></tr>
                    <?php endif; ?>
                    <?php foreach($metrics['recent_txns'] as $t): ?>
                        <tr>
                            <td class="fw-semibold">#<?= $t['id'] ?></td>
                            <td><?= date('d M Y, H:i', strtotime($t['created_at'])) ?></td>
                            <td class="fw-semibold">TZS <?= number_format($t['total_amount']) ?></td>
                            <td><?= htmlspecialchars($t['payment_method']) ?></td>
                            <td><span class="badge bg-success">Paid</span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
