<?php $__env->startSection('content'); ?>
<!-- Daawafy Executive SaaS Global Audit Trail View -->
<div class="laravel-hero-banner mb-4 d-flex justify-content-between align-items-center animate-fade-in">
    <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-clock-history me-2 text-info"></i> Global Audit Trail & Compliance Logs</h2>
        <p class="text-white-50 mb-0">Track platform security events, administrative changes, and user activities across all pharmacy tenants.</p>
    </div>
    <div>
        <a href="<?php echo e(route('saas.dashboard')); ?>" class="btn btn-outline-light rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i> SaaS Dashboard
        </a>
    </div>
</div>

<!-- Audit Trail Table Card -->
<div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4 animate-fade-in">
    <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-journal-text me-2 text-primary"></i> System Activity Logs</h5>
        <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill">Total Recorded: <?php echo e($logs->total()); ?></span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4">Timestamp</th>
                        <th>User</th>
                        <th>Pharmacy / Tenant</th>
                        <th>Action</th>
                        <th>Details</th>
                        <th class="pe-4 text-end">IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td class="ps-4 text-nowrap">
                            <span class="fw-semibold text-dark"><?php echo e($log->created_at ? $log->created_at->format('M d, Y') : '—'); ?></span>
                            <small class="text-muted d-block"><?php echo e($log->created_at ? $log->created_at->format('H:i:s') : ''); ?></small>
                        </td>
                        <td>
                            <?php if($log->user): ?>
                                <div class="fw-semibold text-dark"><?php echo e($log->user->name ?? $log->user->username); ?></div>
                                <code class="text-muted small"><?php echo e($log->user->username); ?></code>
                            <?php else: ?>
                                <span class="text-muted fst-italic">System / Unknown</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($log->tenant): ?>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-1">
                                    <i class="bi bi-building me-1"></i> <?php echo e($log->tenant->pharmacy_name ?? 'Branch'); ?>

                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2 py-1">
                                    <i class="bi bi-globe me-1"></i> Platform SaaS
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                                $act = strtolower($log->action ?? '');
                                $badgeClass = 'bg-secondary';
                                if (str_contains($act, 'create') || str_contains($act, 'add') || str_contains($act, 'register')) {
                                    $badgeClass = 'bg-success';
                                } elseif (str_contains($act, 'delete') || str_contains($act, 'remove') || str_contains($act, 'deactivate')) {
                                    $badgeClass = 'bg-danger';
                                } elseif (str_contains($act, 'update') || str_contains($act, 'edit') || str_contains($act, 'contract')) {
                                    $badgeClass = 'bg-warning text-dark';
                                } elseif (str_contains($act, 'login') || str_contains($act, 'support')) {
                                    $badgeClass = 'bg-info text-dark';
                                }
                            ?>
                            <span class="badge <?php echo e($badgeClass); ?> rounded-pill px-2 py-1">
                                <?php echo e($log->action); ?>

                            </span>
                        </td>
                        <td class="text-wrap" style="max-width: 320px;">
                            <span class="text-secondary small"><?php echo e($log->details ?? '—'); ?></span>
                        </td>
                        <td class="pe-4 text-end text-nowrap">
                            <code class="text-muted small"><?php echo e($log->ip_address ?? '—'); ?></code>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                            Hakuna kumbukumbu za ukaguzi zilizopatikana (No audit logs recorded yet).
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if($logs->hasPages()): ?>
    <div class="card-footer bg-white py-3 px-4 border-top">
        <div class="d-flex justify-content-between align-items-center">
            <small class="text-muted">Inaonyesha <?php echo e($logs->firstItem()); ?> hadi <?php echo e($logs->lastItem()); ?> kati ya kumbukumbu <?php echo e($logs->total()); ?></small>
            <div>
                <?php echo e($logs->links('pagination::bootstrap-5')); ?>

            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\wamp64\www\pms\resources\views/saas_audit_logs.blade.php ENDPATH**/ ?>