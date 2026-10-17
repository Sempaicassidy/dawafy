@extends('layouts.app')

@section('content')
<!-- Daawafy Executive Sub-Categories View (100% Professional English) -->
<div class="laravel-hero-banner mb-4 d-flex justify-content-between align-items-center animate-fade-in">
    <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-tags-fill me-2 text-info"></i> Sub-Categories Directory</h2>
        <p class="text-white-50 mb-0">Pharmacological classes & therapeutic types (Anti-pain, Antibiotics, Antipyretics, etc.).</p>
    </div>
    <div>
        <button class="btn-laravel-primary" data-bs-toggle="modal" data-bs-target="#addSubCatModal">
            <i class="bi bi-plus-circle me-1"></i> + Add Sub-Category
        </button>
    </div>
</div>

<!-- Stats Cards -->
<div class="row g-3 mb-4 animate-fade-in">
    <div class="col-md-4">
        <div class="metric-widget metric-blue">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="metric-title">Total Sub-Categories</div>
                    <div class="metric-value text-primary"><?= count($sub_categories) ?></div>
                </div>
                <div class="metric-icon"><i class="bi bi-tags"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="metric-widget metric-green">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="metric-title">Main Categories Linked</div>
                    <div class="metric-value text-success"><?= count($main_categories) ?></div>
                </div>
                <div class="metric-icon"><i class="bi bi-capsule"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="metric-widget metric-yellow">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="metric-title">Categorized Items</div>
                    <?php $total_cat = array_sum(array_column($sub_categories, 'drug_count')); ?>
                    <div class="metric-value text-warning"><?= $total_cat ?></div>
                </div>
                <div class="metric-icon"><i class="bi bi-box-seam"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- Table Card -->
<div class="card-laravel animate-fade-in">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-list-stars me-2"></i> Sub-Category Directory</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-laravel mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Sub-Category Name</th>
                        <th>Description</th>
                        <th>Linked Category</th>
                        <th>Total Drugs</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($sub_categories as $idx => $sc): ?>
                        <tr>
                            <td class="text-muted"><?= $idx + 1 ?></td>
                            <td>
                                <strong class="text-dark"><i class="bi bi-tag-fill me-1 text-primary"></i> <?= htmlspecialchars($sc['name']) ?></strong>
                            </td>
                            <td><small class="text-muted"><?= htmlspecialchars($sc['description'] ?: 'N/A') ?></small></td>
                            <td>
                                <?php if($sc['main_category_name']): ?>
                                    <span class="badge-laravel-blue"><?= htmlspecialchars($sc['main_category_name']) ?></span>
                                <?php else: ?>
                                    <span class="badge bg-light text-muted border">All Categories</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge-laravel-green"><?= $sc['drug_count'] ?> drugs</span></td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-primary rounded-pill me-1" onclick="openEditSubModal(<?= $sc['id'] ?>, '<?= addslashes($sc['name']) ?>', '<?= addslashes($sc['description'] ?? '') ?>', '<?= $sc['category_id'] ?? '' ?>')">
                                    <i class="bi bi-pencil"></i> Edit
                                </button>
                                <a href="{{ route('sub_categories.destroy', $sc['id']) }}" class="btn btn-sm btn-outline-danger rounded-pill" onclick="return confirm('Delete sub-category?');">
                                    <i class="bi bi-trash"></i> Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Add Sub-Category -->
<div class="modal fade" id="addSubCatModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="bi bi-plus-circle-fill me-2"></i> Add New Sub-Category</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="{{ route('sub_categories.store') }}">
        @csrf
        <div class="modal-body p-4">
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Sub-Category Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control rounded-pill px-3" placeholder="e.g. Anti-pain, Antibiotics, Antipyretics" required>
            </div>
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Linked Main Category</label>
                <select name="category_id" class="form-select rounded-pill px-3">
                    <option value="">-- All Categories --</option>
                    <?php foreach($main_categories as $mc): ?>
                        <option value="<?= $mc['id'] ?>"><?= htmlspecialchars($mc['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Description</label>
                <textarea name="description" class="form-control rounded px-3" rows="3" placeholder="Description or clinical scope..."></textarea>
            </div>
        </div>
        <div class="modal-footer border-0 bg-light">
            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-laravel-primary rounded-pill px-4 fw-bold"><i class="bi bi-check-circle me-1"></i> Save Sub-Category</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal: Edit Sub-Category -->
<div class="modal fade" id="editSubCatModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i> Edit Sub-Category</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="{{ route('sub_categories.update') }}">
        @csrf
        <input type="hidden" name="id" id="editSubId">
        <div class="modal-body p-4">
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Sub-Category Name <span class="text-danger">*</span></label>
                <input type="text" name="name" id="editSubName" class="form-control rounded-pill px-3" required>
            </div>
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Linked Main Category</label>
                <select name="category_id" id="editSubCategory" class="form-select rounded-pill px-3">
                    <option value="">-- All Categories --</option>
                    <?php foreach($main_categories as $mc): ?>
                        <option value="<?= $mc['id'] ?>"><?= htmlspecialchars($mc['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Description</label>
                <textarea name="description" id="editSubDescription" class="form-control rounded px-3" rows="3"></textarea>
            </div>
        </div>
        <div class="modal-footer border-0 bg-light">
            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-laravel-primary rounded-pill px-4 fw-bold"><i class="bi bi-check-circle me-1"></i> Update Sub-Category</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function openEditSubModal(id, name, desc, catId) {
    document.getElementById('editSubId').value = id;
    document.getElementById('editSubName').value = name;
    document.getElementById('editSubDescription').value = desc;
    document.getElementById('editSubCategory').value = catId;
    new bootstrap.Modal(document.getElementById('editSubCatModal')).show();
}
</script>
@endsection
