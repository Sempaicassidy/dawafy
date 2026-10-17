@extends('layouts.app')

@section('content')
<!-- Daawafy Executive Cloud Backup View -->
<div class="laravel-hero-banner mb-4 d-flex justify-content-between align-items-center animate-fade-in">
    <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-cloud-arrow-up me-2 text-info"></i> Automated Cloud Backup & Database Recovery</h2>
        <p class="text-white-50 mb-0">Automated Google Drive, AWS S3, and local SQL database snapshots with zero data loss protection.</p>
    </div>
    <div>
        <form method="POST" action="{{ route('addons.backup') }}" class="d-inline">
            @csrf
            <input type="hidden" name="manual_backup" value="1">
            <input type="hidden" name="backup_type" value="full">
            <button type="submit" class="btn-laravel-green text-white shadow fw-bold">
                <i class="bi bi-cloud-arrow-up-fill me-1"></i> + Create Instant Backup Snapshot
            </button>
        </form>
    </div>
</div>

<?php if(isset($msg)): ?>
    <div class="alert alert-success rounded-pill px-4 mb-4 animate-fade-in"><i class="bi bi-check-circle me-2"></i> <?= $msg ?></div>
<?php endif; ?>
<?php if(isset($error)): ?>
    <div class="alert alert-danger rounded-pill px-4 mb-4 animate-fade-in"><i class="bi bi-exclamation-triangle me-2"></i> <?= $error ?></div>
<?php endif; ?>

<!-- Stats Overview -->
<div class="row g-3 mb-4 animate-fade-in">
    <div class="col-md-3">
        <div class="metric-widget metric-blue">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="metric-title">Total Backups</div>
                    <div class="metric-value text-primary"><?= count($logs) ?></div>
                </div>
                <div class="metric-icon"><i class="bi bi-hdd-stack"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="metric-widget metric-green">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="metric-title">Backup Health</div>
                    <div class="metric-value text-success">Healthy</div>
                </div>
                <div class="metric-icon"><i class="bi bi-shield-check"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="metric-widget metric-yellow">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="metric-title">Automated Schedule</div>
                    <div class="metric-value text-warning"><?= htmlspecialchars(ucfirst($config['backup_frequency'])) ?></div>
                </div>
                <div class="metric-icon"><i class="bi bi-clock-history"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="metric-widget metric-purple">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="metric-title">Storage Provider</div>
                    <div class="metric-value"><?= htmlspecialchars($config['backup_provider'] ?: 'Local SQL') ?></div>
                </div>
                <div class="metric-icon"><i class="bi bi-cloud-arrow-up"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- Main Table Card -->
<div class="card-laravel animate-fade-in">
    <div class="card-header bg-white border-bottom">
        <h5 class="mb-0 text-primary"><i class="bi bi-journal-check me-2"></i> Database Snapshot & Restore History</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-laravel mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Backup Snapshot File</th>
                        <th>Type</th>
                        <th>File Size</th>
                        <th>Status</th>
                        <th>Date & Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($logs)): ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted">No database backups generated yet. Click "+ Create Instant Backup Snapshot".</td></tr>
                    <?php else: ?>
                        <?php foreach($logs as $idx => $l): 
                            $createdAtRaw = $l['created_at'] ?? $l['created_on'] ?? $l['backup_date'] ?? $l['timestamp'] ?? null;
                            $formattedDate = $createdAtRaw ? date('d M Y, H:i', strtotime((string)$createdAtRaw)) : 'Just now';
                        ?>
                            <tr>
                                <td class="text-muted"><?= $idx + 1 ?></td>
                                <td><strong class="text-dark font-monospace"><?= htmlspecialchars(basename((string)($l['file_path'] ?? ''))) ?></strong></td>
                                <td><span class="badge-laravel-blue"><?= htmlspecialchars(ucfirst((string)($l['backup_type'] ?? 'full'))) ?></span></td>
                                <td><?= round((float)($l['file_size'] ?? 0) / 1024, 2) ?> KB</td>
                                <td>
                                    <?php if(($l['status'] ?? '') === 'success'): ?>
                                        <span class="badge-laravel-green"><i class="bi bi-check-circle me-1"></i> Verified</span>
                                    <?php else: ?>
                                        <span class="badge-laravel-danger"><i class="bi bi-x-circle me-1"></i> Failed</span>
                                    <?php endif; ?>
                                </td>
                                <td><small class="text-muted"><?= htmlspecialchars($formattedDate) ?></small></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
