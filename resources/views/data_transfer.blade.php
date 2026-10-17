@extends('layouts.app')

@section('content')
<!-- Daawafy Executive Data Transfer View -->
<div class="laravel-hero-banner mb-4 d-flex justify-content-between align-items-center animate-fade-in">
    <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-arrow-left-right me-2 text-info"></i> Data Import, Export & Migration Hub</h2>
        <p class="text-white-50 mb-0">Secure encrypted backup exports, CSV/JSON data migration, and audit history logs.</p>
    </div>
    <div>
        <button class="btn-laravel-green text-white shadow" data-bs-toggle="modal" data-bs-target="#exportDataModal">
            <i class="bi bi-download me-1"></i> Export Data
        </button>
        <button class="btn-laravel-primary shadow ms-2" data-bs-toggle="modal" data-bs-target="#importDataModal">
            <i class="bi bi-upload me-1"></i> Import CSV/JSON
        </button>
    </div>
</div>

<?php if(isset($msg)): ?>
    <div class="alert alert-success rounded-pill px-4 mb-4 animate-fade-in"><i class="bi bi-check-circle me-2"></i> <?= $msg ?></div>
<?php endif; ?>
<?php if(isset($error)): ?>
    <div class="alert alert-danger rounded-pill px-4 mb-4 animate-fade-in"><i class="bi bi-exclamation-triangle me-2"></i> <?= $error ?></div>
<?php endif; ?>

<!-- System Cards -->
<div class="row g-3 mb-4 animate-fade-in">
    <div class="col-md-4">
        <div class="metric-widget metric-blue">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="metric-title">Total Transfer Logs</div>
                    <div class="metric-value text-primary"><?= count($logs) ?></div>
                </div>
                <div class="metric-icon"><i class="bi bi-arrow-repeat"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="metric-widget metric-green">
            <div class="d-flex align-items-center justify-content-between">
                <?php $exports = count(array_filter($logs, fn($l) => $l['transfer_type'] === 'export')); ?>
                <div>
                    <div class="metric-title">Data Exports</div>
                    <div class="metric-value text-success"><?= $exports ?></div>
                </div>
                <div class="metric-icon"><i class="bi bi-download"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="metric-widget metric-yellow">
            <div class="d-flex align-items-center justify-content-between">
                <?php $imports = count(array_filter($logs, fn($l) => $l['transfer_type'] === 'import')); ?>
                <div>
                    <div class="metric-title">Data Imports</div>
                    <div class="metric-value text-warning"><?= $imports ?></div>
                </div>
                <div class="metric-icon"><i class="bi bi-upload"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- Main Table Card -->
<div class="card-laravel animate-fade-in">
    <div class="card-header bg-white border-bottom">
        <h5 class="mb-0 text-primary"><i class="bi bi-journal-text me-2"></i> Data Transfer & Migration History</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-laravel mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Transfer Type</th>
                        <th>Data Category</th>
                        <th>Status</th>
                        <th>Encryption</th>
                        <th>Date & Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($logs)): ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted">No data transfers logged.</td></tr>
                    <?php else: ?>
                        <?php foreach($logs as $idx => $l): ?>
                            <tr>
                                <td class="text-muted"><?= $idx + 1 ?></td>
                                <td>
                                    <?php if($l['transfer_type'] === 'export'): ?>
                                        <span class="badge-laravel-green"><i class="bi bi-download me-1"></i> Export</span>
                                    <?php else: ?>
                                        <span class="badge-laravel-blue"><i class="bi bi-upload me-1"></i> Import</span>
                                    <?php endif; ?>
                                </td>
                                <td><strong class="text-dark"><?= htmlspecialchars(ucfirst($l['data_type'])) ?></strong></td>
                                <td>
                                    <?php if($l['status'] === 'success'): ?>
                                        <span class="badge-laravel-green"><i class="bi bi-check-circle me-1"></i> Success</span>
                                    <?php else: ?>
                                        <span class="badge-laravel-danger"><i class="bi bi-x-circle me-1"></i> Failed</span>
                                    <?php endif; ?>
                                </td>
                                <td><small class="text-muted font-monospace"><?= htmlspecialchars($l['encryption_used'] ?: 'Standard') ?></small></td>
                                <td><small class="text-muted"><?= date('d M Y, H:i', strtotime($l['created_at'])) ?></small></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal 1: Export Data -->
<div class="modal fade" id="exportDataModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title"><i class="bi bi-download me-2"></i> Export Pharmacy Data</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="{{ route('addons.data_transfer') }}">
        @csrf
        <input type="hidden" name="export_data" value="1">
        <div class="modal-body p-4">
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Select Data Category <span class="text-danger">*</span></label>
                <select name="data_type" class="form-select rounded-pill px-3" required>
                    <option value="drugs">Drug Inventory Catalogue</option>
                    <option value="sales">Sales & Transactions History</option>
                    <option value="customers">Customer Directory</option>
                    <option value="suppliers">Suppliers List</option>
                </select>
            </div>
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" name="encryption" id="encryptCheck" checked>
                <label class="form-check-label fw-bold text-muted small" for="encryptCheck">Enable AES-256 Encryption Security</label>
            </div>
        </div>
        <div class="modal-footer border-0 bg-light">
            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-laravel-green text-white rounded-pill px-4 fw-bold"><i class="bi bi-download me-1"></i> Generate Export File</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal 2: Import Data -->
<div class="modal fade" id="importDataModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="bi bi-upload me-2"></i> Import Pharmacy Data File</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="{{ route('addons.data_transfer') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="import_data" value="1">
        <div class="modal-body p-4">
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Select Data Target Category <span class="text-danger">*</span></label>
                <select name="data_type" class="form-select rounded-pill px-3" required>
                    <option value="drugs">Drug Inventory Catalogue</option>
                    <option value="customers">Customer Directory</option>
                    <option value="suppliers">Suppliers List</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Upload CSV or JSON File <span class="text-danger">*</span></label>
                <input type="file" name="import_file" class="form-control rounded-pill px-3" accept=".csv, .json" required>
            </div>
        </div>
        <div class="modal-footer border-0 bg-light">
            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-laravel-primary rounded-pill px-4 fw-bold"><i class="bi bi-upload me-1"></i> Start Import</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
