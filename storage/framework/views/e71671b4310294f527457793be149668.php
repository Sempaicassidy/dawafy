<?php $__env->startSection('content'); ?>
<!-- Traditional Suppliers View -->
<div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
    <div>
        <h4 class="fw-bold text-dark mb-0">Supplier Management</h4>
        <small class="text-muted">Pharmaceutical distributors, wholesalers, contacts, and vendor catalog</small>
    </div>
    <div>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
            <i class="bi bi-plus me-1"></i> Add Supplier
        </button>
    </div>
</div>

<?php if (!empty($msg)): ?>
    <div class="alert alert-success alert-dismissible fade show p-2 px-3 mb-3" role="alert" style="border-radius: 4px;">
        <i class="bi bi-check-circle me-1"></i> <?= htmlspecialchars($msg) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show p-2 px-3 mb-3" role="alert" style="border-radius: 4px;">
        <i class="bi bi-exclamation-triangle me-1"></i> <?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold text-dark">Authorized Suppliers</h6>
        <span class="badge bg-secondary"><?= count($suppliers) ?> Total</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Supplier Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Address</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($suppliers)): ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted"><i class="bi bi-info-circle me-1"></i> No suppliers registered yet. Click "+ Add Supplier" to create one.</td></tr>
                    <?php endif; ?>
                    <?php foreach($suppliers as $idx => $s): ?>
                        <tr>
                            <td class="text-muted"><?= $idx + 1 ?></td>
                            <td class="fw-bold text-dark"><i class="bi bi-building me-1 text-primary"></i> <?= htmlspecialchars($s['name']) ?></td>
                            <td><?= htmlspecialchars($s['phone'] ?: 'N/A') ?></td>
                            <td><?= htmlspecialchars($s['email'] ?: 'N/A') ?></td>
                            <td><small class="text-muted"><?= htmlspecialchars($s['address'] ?: 'N/A') ?></small></td>
                            <td class="text-end" style="white-space: nowrap;">
                                <div class="d-inline-flex gap-1">
                                    <button class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick='openEditSupplierModal(<?= json_encode($s, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>
                                        <i class="bi bi-pencil me-1"></i> Edit
                                    </button>
                                    <form method="POST" action="<?php echo e(route('suppliers.destroy', $s['id'])); ?>" class="d-inline mb-0" onsubmit="return confirm('Are you sure you want to delete this supplier?');">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Add Supplier -->
<div class="modal fade" id="addSupplierModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="bi bi-shop me-2"></i> Add New Supplier</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="<?php echo e(route('suppliers.store')); ?>">
        <?php echo csrf_field(); ?>
        <div class="modal-body p-4">
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Supplier Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control rounded-pill px-3" placeholder="e.g. MSD Tanzania, Kairuki Distributors" required>
            </div>
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Phone Number</label>
                <input type="text" name="phone" class="form-control rounded-pill px-3" placeholder="e.g. 0712345678">
            </div>
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Email Address</label>
                <input type="email" name="email" class="form-control rounded-pill px-3" placeholder="e.g. info@supplier.co.tz">
            </div>
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Physical Address</label>
                <textarea name="address" class="form-control rounded px-3" rows="2" placeholder="City, Street, Location..."></textarea>
            </div>
        </div>
        <div class="modal-footer border-0 bg-light">
            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-laravel-primary rounded-pill px-4 fw-bold"><i class="bi bi-check-circle me-1"></i> Save Supplier</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal: Edit Supplier -->
<div class="modal fade" id="editSupplierModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i> Edit Supplier</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="<?php echo e(route('suppliers.update')); ?>">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="supplier_id" id="editSupplierId">
        <div class="modal-body p-4">
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Supplier Name <span class="text-danger">*</span></label>
                <input type="text" name="name" id="editSupplierName" class="form-control rounded-pill px-3" required>
            </div>
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Phone Number</label>
                <input type="text" name="phone" id="editSupplierPhone" class="form-control rounded-pill px-3">
            </div>
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Email Address</label>
                <input type="email" name="email" id="editSupplierEmail" class="form-control rounded-pill px-3">
            </div>
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Physical Address</label>
                <textarea name="address" id="editSupplierAddress" class="form-control rounded px-3" rows="2"></textarea>
            </div>
        </div>
        <div class="modal-footer border-0 bg-light">
            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-laravel-primary rounded-pill px-4 fw-bold"><i class="bi bi-check-circle me-1"></i> Update Supplier</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function openEditSupplierModal(s) {
    document.getElementById('editSupplierId').value = s.id;
    document.getElementById('editSupplierName').value = s.name || '';
    document.getElementById('editSupplierPhone').value = s.phone || '';
    document.getElementById('editSupplierEmail').value = s.email || '';
    document.getElementById('editSupplierAddress').value = s.address || '';
    new bootstrap.Modal(document.getElementById('editSupplierModal')).show();
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\wamp64\www\pms\resources\views/suppliers.blade.php ENDPATH**/ ?>