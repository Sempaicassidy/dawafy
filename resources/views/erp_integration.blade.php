@extends('layouts.app')

@section('content')
<!-- Daawafy Executive ERP Integration View -->
<div class="laravel-hero-banner mb-4 d-flex justify-content-between align-items-center animate-fade-in">
    <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-diagram-3 me-2 text-info"></i> Enterprise ERP & Financial Integration Hub</h2>
        <p class="text-white-50 mb-0">Bi-directional automated inventory and financial ledger sync with Tally, SAP, Odoo, and QuickBooks.</p>
    </div>
    <div>
        <button class="btn-laravel-green text-white shadow" data-bs-toggle="modal" data-bs-target="#configureErpModal">
            <i class="bi bi-plug-fill me-1"></i> + Configure ERP Endpoint
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
    <div class="col-md-3">
        <div class="metric-widget metric-blue">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="metric-title">Configured Endpoints</div>
                    <div class="metric-value text-primary"><?= count($erp_configs) ?></div>
                </div>
                <div class="metric-icon"><i class="bi bi-link-45deg"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="metric-widget metric-green">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="metric-title">Last Sync Status</div>
                    <div class="metric-value text-success">Active</div>
                </div>
                <div class="metric-icon"><i class="bi bi-check-all"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="metric-widget metric-yellow">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="metric-title">Automated Frequency</div>
                    <div class="metric-value text-warning">Realtime</div>
                </div>
                <div class="metric-icon"><i class="bi bi-arrow-repeat"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="metric-widget metric-red">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="metric-title">Sync Errors</div>
                    <div class="metric-value text-danger">0</div>
                </div>
                <div class="metric-icon"><i class="bi bi-shield-check"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- Main ERP Grid / Table Card -->
<div class="card-laravel animate-fade-in mb-4">
    <div class="card-header bg-white border-bottom">
        <h5 class="mb-0 text-primary"><i class="bi bi-cpu me-2"></i> Configured Enterprise Connectors</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-laravel mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>ERP Platform</th>
                        <th>API Endpoint URL</th>
                        <th>Sync Frequency</th>
                        <th>Last Synced</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($erp_configs)): ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted">No ERP integrations configured yet. Click "+ Configure ERP Endpoint" to set up Tally/Odoo/SAP.</td></tr>
                    <?php else: ?>
                        <?php foreach($erp_configs as $idx => $e): ?>
                            <tr>
                                <td class="text-muted"><?= $idx + 1 ?></td>
                                <td><strong class="text-dark d-block"><?= htmlspecialchars($e['erp_name']) ?></strong></td>
                                <td><span class="font-monospace small text-primary"><?= htmlspecialchars($e['api_endpoint']) ?></span></td>
                                <td><span class="badge-laravel-blue"><?= htmlspecialchars(ucfirst($e['sync_frequency'])) ?></span></td>
                                <td><small class="text-muted"><?= $e['last_sync'] ? date('d M Y, H:i', strtotime($e['last_sync'])) : 'Never' ?></small></td>
                                <td><span class="badge-laravel-green"><i class="bi bi-check-circle me-1"></i> Connected</span></td>
                                <td class="text-end">
                                    <form method="POST" action="{{ route('addons.erp') }}" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="erp_id" value="<?= $e['id'] ?>">
                                        <button type="submit" name="test_connection" class="btn btn-sm btn-laravel-primary rounded-pill me-1">
                                            <i class="bi bi-wifi me-1"></i> Test
                                        </button>
                                        <button type="submit" name="manual_sync" class="btn btn-sm btn-laravel-green rounded-pill text-white">
                                            <i class="bi bi-arrow-repeat me-1"></i> Sync Now
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Configure ERP -->
<div class="modal fade" id="configureErpModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="bi bi-diagram-3 me-2"></i> Configure Enterprise ERP Endpoint</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="{{ route('addons.erp') }}">
        @csrf
        <input type="hidden" name="save_erp" value="1">
        <div class="modal-body p-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="fw-bold text-muted small mb-1">ERP Platform <span class="text-danger">*</span></label>
                    <select name="erp_name" class="form-select rounded-pill px-3" required>
                        <option value="Tally Prime">Tally Prime (XML/REST Server)</option>
                        <option value="Odoo ERP">Odoo ERP (JSON-RPC API)</option>
                        <option value="SAP Business One">SAP Business One (Service Layer)</option>
                        <option value="QuickBooks Online">QuickBooks Online</option>
                        <option value="Custom REST API">Custom Corporate REST API</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="fw-bold text-muted small mb-1">Sync Frequency <span class="text-danger">*</span></label>
                    <select name="sync_frequency" class="form-select rounded-pill px-3" required>
                        <option value="realtime">Realtime (Instant per transaction)</option>
                        <option value="hourly">Hourly Batch Sync</option>
                        <option value="daily">Daily End-of-Day Sync</option>
                    </select>
                </div>
                <div class="col-md-12">
                    <label class="fw-bold text-muted small mb-1">API Endpoint URL <span class="text-danger">*</span></label>
                    <input type="url" name="api_endpoint" class="form-control rounded-pill px-3" placeholder="https://erp.company.com/api/v1/sync" required>
                </div>
                <div class="col-md-6">
                    <label class="fw-bold text-muted small mb-1">API Client Key / User ID</label>
                    <input type="text" name="api_key" class="form-control rounded-pill px-3" placeholder="API Key">
                </div>
                <div class="col-md-6">
                    <label class="fw-bold text-muted small mb-1">API Secret / Bearer Token</label>
                    <input type="password" name="api_secret" class="form-control rounded-pill px-3" placeholder="Secret key">
                </div>
            </div>
        </div>
        <div class="modal-footer border-0 bg-light">
            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-laravel-primary rounded-pill px-4 fw-bold"><i class="bi bi-check-circle me-1"></i> Save Configuration</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
