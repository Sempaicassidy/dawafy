@extends('layouts.app')

@section('content')
<!-- Sales Velocity & Demand Forecasting View -->
<div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
    <div>
        <h4 class="fw-bold text-dark mb-0">Sales Velocity & Stock Forecasting</h4>
        <small class="text-muted">Inventory movement rate, slow-moving items, and demand trends</small>
    </div>
    <div>
        <button class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#whatsappReportModal">
            <i class="bi bi-whatsapp me-1"></i> Send Summary to WhatsApp
        </button>
    </div>
</div>

<!-- Metrics Overview -->
<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card p-3 mb-0">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small text-uppercase fw-semibold">Analyzed Medicines</div>
                    <div class="h4 fw-bold text-dark my-1"><?= count($sales_analysis) ?></div>
                </div>
                <div class="text-secondary fs-3"><i class="bi bi-capsule"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 mb-0">
            <div class="d-flex align-items-center justify-content-between">
                <?php $critical = count(array_filter($sales_analysis, fn($x) => $x['risk_level'] === 'Critical')); ?>
                <div>
                    <div class="text-muted small text-uppercase fw-semibold">Zero Sales (30 Days)</div>
                    <div class="h4 fw-bold text-dark my-1"><?= $critical ?></div>
                </div>
                <div class="text-secondary fs-3"><i class="bi bi-exclamation-octagon"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 mb-0">
            <div class="d-flex align-items-center justify-content-between">
                <?php $high_risk = count(array_filter($sales_analysis, fn($x) => $x['risk_level'] === 'High')); ?>
                <div>
                    <div class="text-muted small text-uppercase fw-semibold">Slow Moving</div>
                    <div class="h4 fw-bold text-dark my-1"><?= $high_risk ?></div>
                </div>
                <div class="text-secondary fs-3"><i class="bi bi-speedometer"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 mb-0">
            <div class="d-flex align-items-center justify-content-between">
                <?php $fast = count(array_filter($sales_analysis, fn($x) => $x['velocity'] === 'Fast')); ?>
                <div>
                    <div class="text-muted small text-uppercase fw-semibold">Fast Moving</div>
                    <div class="h4 fw-bold text-dark my-1"><?= $fast ?></div>
                </div>
                <div class="text-secondary fs-3"><i class="bi bi-graph-up-arrow"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- Main Table Card -->
<div class="card-laravel animate-fade-in">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-primary"><i class="bi bi-graph-up-arrow me-2"></i> Inventory Sales Velocity & Stockout Risk</h5>
        <div class="btn-group btn-group-sm">
            <button class="btn btn-outline-primary active rounded-pill-start px-3" onclick="filterTable('all')">All Items</button>
            <button class="btn btn-outline-danger px-3" onclick="filterTable('critical')">Critical Risk</button>
            <button class="btn btn-outline-warning rounded-pill-end px-3" onclick="filterTable('slow')">Slow Moving</button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-laravel mb-0" id="salesTable">
                <thead>
                    <tr>
                        <th>Medicine & Category</th>
                        <th>Current Stock</th>
                        <th>30-Day Sales</th>
                        <th>Daily Rate</th>
                        <th>Stockout Est.</th>
                        <th>Velocity Status</th>
                        <th>AI Smart Insights</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($sales_analysis as $item): ?>
                        <tr data-risk="<?= $item['risk_level'] ?>">
                            <td>
                                <strong class="text-dark d-block"><?= htmlspecialchars($item['drug']['name']) ?></strong>
                                <span class="badge-laravel-blue"><?= htmlspecialchars($item['drug']['category']) ?></span>
                            </td>
                            <td><strong class="text-dark"><?= $item['drug']['total_stock'] ?> units</strong></td>
                            <td><?= $item['drug']['sold_30days'] ?> units</td>
                            <td><span class="badge-laravel-green"><?= $item['daily_rate'] ?> / day</span></td>
                            <td>
                                <?php if($item['days_to_stockout'] < 30): ?>
                                    <span class="badge-laravel-danger"><?= $item['days_to_stockout'] ?> Days</span>
                                <?php else: ?>
                                    <span class="text-muted small"><?= $item['days_to_stockout'] == 999 ? 'N/A' : $item['days_to_stockout'] . ' Days' ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($item['velocity'] === 'Fast'): ?>
                                    <span class="badge-laravel-green"><i class="bi bi-lightning-fill me-1"></i> Fast</span>
                                <?php elseif($item['velocity'] === 'Normal'): ?>
                                    <span class="badge-laravel-blue"><i class="bi bi-check-circle me-1"></i> Normal</span>
                                <?php elseif($item['velocity'] === 'Slow'): ?>
                                    <span class="badge-laravel-yellow"><i class="bi bi-arrow-down me-1"></i> Slow</span>
                                <?php else: ?>
                                    <span class="badge-laravel-danger"><i class="bi bi-x-circle me-1"></i> Critical</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if(empty($item['insights'])): ?>
                                    <span class="text-success small"><i class="bi bi-check-lg me-1"></i> Stock movement optimal</span>
                                <?php else: ?>
                                    <?php foreach($item['insights'] as $insight): ?>
                                        <div class="small text-secondary mb-1"><?= $insight ?></div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Send WhatsApp Report -->
<div class="modal fade" id="whatsappReportModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title"><i class="bi bi-whatsapp me-2"></i> Send AI Report to Manager WhatsApp</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form id="whatsappReportForm">
        <div class="modal-body p-4">
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Manager Phone Number <span class="text-danger">*</span></label>
                <input type="text" id="managerPhone" class="form-control rounded-pill px-3" placeholder="e.g. 255712345678" required>
            </div>
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Report Focus</label>
                <select id="reportType" class="form-select rounded-pill px-3">
                    <option value="summary">Full Executive AI Summary</option>
                    <option value="critical">Critical Risk & Dead Stock Items Only</option>
                    <option value="stockout">Impending Stockouts (Next 30 Days)</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Custom Manager Note (Optional)</label>
                <textarea id="customMessage" class="form-control rounded-3" rows="2" placeholder="e.g. Please review low velocity items for promotion."></textarea>
            </div>
            <div id="whatsappStatus"></div>
        </div>
        <div class="modal-footer border-0 bg-light">
            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold text-white"><i class="bi bi-send me-1"></i> Send Report</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function filterTable(filter) {
    const rows = document.querySelectorAll('#salesTable tbody tr');
    rows.forEach(row => {
        const risk = row.getAttribute('data-risk');
        if(filter === 'all') {
            row.style.display = '';
        } else if(filter === 'critical') {
            row.style.display = (risk === 'Critical' || risk === 'High') ? '' : 'none';
        } else if(filter === 'slow') {
            row.style.display = (risk === 'Critical' || risk === 'High' || risk === 'Medium') ? '' : 'none';
        }
    });
}

document.getElementById('whatsappReportForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const phone = document.getElementById('managerPhone').value;
    const reportType = document.getElementById('reportType').value;
    const customMessage = document.getElementById('customMessage').value;
    const statusDiv = document.getElementById('whatsappStatus');
    
    statusDiv.innerHTML = '<div class="alert alert-info py-2"><i class="bi bi-hourglass-split me-1"></i> Sending report via WhatsApp...</div>';
    
    fetch('api_whatsapp_report.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `phone=${encodeURIComponent(phone)}&report_type=${reportType}&custom_message=${encodeURIComponent(customMessage)}`
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            statusDiv.innerHTML = '<div class="alert alert-success py-2 d-flex justify-content-between align-items-center"><div><i class="bi bi-check-circle me-1"></i> Report generated! Opening WhatsApp...</div><a href="' + data.wa_url + '" target="_blank" class="btn btn-sm btn-success rounded-pill text-white fw-bold"><i class="bi bi-whatsapp me-1"></i> Open WhatsApp</a></div>';
            if(data.wa_url) {
                window.open(data.wa_url, '_blank');
            }
        } else {
            statusDiv.innerHTML = '<div class="alert alert-danger py-2"><i class="bi bi-exclamation-circle me-1"></i> Error: ' + data.message + '</div>';
        }
    })
    .catch(err => {
        statusDiv.innerHTML = '<div class="alert alert-danger py-2"><i class="bi bi-exclamation-circle me-1"></i> Error: ' + err.message + '</div>';
    });
});
</script>
@endsection
