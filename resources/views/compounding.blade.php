@extends('layouts.app')

@section('content')
<!-- Daawafy Executive Extemporaneous Drug Compounding Operations View (AI Compounding Specialist Included) -->
<style>
    #compoundingTabs .nav-link {
        color: #475569 !important;
        font-weight: 500;
        border-radius: 4px;
        padding: 0.45rem 1rem;
        margin-right: 0.35rem;
        border: 1px solid #cbd5e1;
        background: #ffffff;
    }
    #compoundingTabs .nav-link.active {
        color: #ffffff !important;
        background-color: #1d4ed8 !important;
        border-color: #1d4ed8 !important;
        box-shadow: none;
    }
    .cursor-pointer {
        cursor: pointer;
    }
</style>

<div class="laravel-hero-banner mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-capsule-pill me-2 text-primary"></i> Drug Compounding Operations</h2>
        <p class="text-muted mb-0">Extemporaneous preparations, ingredient deductions, clinical guidance, and batch mixing.</p>
    </div>
    <div>
        <button class="btn btn-primary px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#addFormulaModal">
            <i class="bi bi-plus-circle me-1"></i> + Create Formula
        </button>
    </div>
</div>

<?php if(isset($msg)): ?>
    <div class="alert alert-success alert-dismissible fade show px-3 py-2 mb-3" role="alert" style="border-radius: 4px;">
        <i class="bi bi-check-circle-fill me-2"></i> <?= htmlspecialchars($msg) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if(isset($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show px-3 py-2 mb-3" role="alert" style="border-radius: 4px;">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Clinical Compounding Pharmacopeia & Formula Search -->
<div class="card mb-4">
    <div class="card-header bg-light d-flex align-items-center gap-2 py-2 px-3">
        <i class="bi bi-journal-medical text-primary"></i>
        <h6 class="mb-0 fw-semibold text-dark">Clinical Compounding Reference & Formula Lookup</h6>
    </div>
    <div class="card-body p-3">
        <p class="text-muted small mb-2">Search standard USP/BP/TMDA extemporaneous formulas, required active ingredients, dosage strengths, and beyond-use dates (BUD).</p>
        
        <div class="row g-2 mb-2">
            <div class="col-md-10">
                <input type="text" id="aiCompoundQuery" class="form-control" placeholder="Search formula name (e.g. Paracetamol 120mg/5ml, Calamine Lotion, Amoxicillin 125mg/5ml)...">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100" onclick="consultAICompounding()">
                    <i class="bi bi-search me-1"></i> Search
                </button>
            </div>
        </div>

        <!-- Quick Standard Formula Buttons -->
        <div class="d-flex flex-wrap align-items-center gap-1 mt-2">
            <span class="small text-muted me-1">Common Preparations:</span>
            <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size: 0.75rem;" onclick="quickAiConsult('Paracetamol Mixture 120mg/5ml')">Paracetamol Mixture</button>
            <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size: 0.75rem;" onclick="quickAiConsult('Calamine Lotion 15%')">Calamine Lotion</button>
            <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size: 0.75rem;" onclick="quickAiConsult('Amoxicillin Paediatric Suspension 125mg/5ml')">Amoxicillin Suspension</button>
            <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size: 0.75rem;" onclick="quickAiConsult('Salicylic Acid Ointment 5%')">Salicylic Acid 5%</button>
        </div>
    </div>
</div>

<!-- Clinical Recommendation Result Card -->
<div id="aiCompoundingResultCard" class="card mb-4 d-none">
    <div class="card-header bg-light border-bottom d-flex justify-content-between align-items-center py-2 px-3">
        <h6 class="fw-semibold text-dark mb-0" id="aiResultTitle"><i class="bi bi-journal-check me-1"></i> Clinical Compounding Specification</h6>
        <div>
            <button class="btn btn-sm btn-laravel-green rounded-pill px-3 me-2 fw-bold shadow-sm" onclick="autoFillFormulaModal()">
                <i class="bi bi-lightning-charge-fill me-1"></i> ⚡ Auto-Fill Formula Fields
            </button>
            <button class="btn btn-sm btn-outline-secondary rounded-circle" onclick="document.getElementById('aiCompoundingResultCard').classList.add('d-none')">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    </div>
    <div class="card-body p-4">
        <div class="row g-4 mb-3">
            <div class="col-md-7">
                <h6 class="fw-bold text-dark mb-2"><i class="bi bi-basket3 text-primary me-1"></i> Recommended Raw Ingredients & Proportions:</h6>
                <div class="table-responsive mb-3">
                    <table class="table table-bordered align-middle small mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Ingredient Name</th>
                                <th>Quantity / Proportion</th>
                            </tr>
                        </thead>
                        <tbody id="aiIngredientsTbody">
                            <!-- Populated via JS -->
                        </tbody>
                    </table>
                </div>

                <h6 class="fw-bold text-dark mb-2"><i class="bi bi-list-check text-primary me-1"></i> Standard Compounding Procedure:</h6>
                <div class="p-3 bg-light rounded border text-dark font-monospace small" style="white-space: pre-line;" id="aiInstructionsText">
                    <!-- Populated via JS -->
                </div>
            </div>
            <div class="col-md-5">
                <div class="p-3 bg-warning bg-opacity-10 border border-warning border-opacity-25 rounded-3 mb-3">
                    <h6 class="fw-bold text-dark mb-1"><i class="bi bi-clock-history text-warning me-1"></i> Beyond-Use Date (BUD) & Stability:</h6>
                    <small class="d-block text-dark fw-semibold" id="aiShelfLifeText">14 days refrigerated (2°C - 8°C)</small>
                </div>

                <div class="p-3 bg-primary bg-opacity-10 border border-primary border-opacity-25 rounded-3">
                    <h6 class="fw-bold text-primary mb-2"><i class="bi bi-shield-check me-1"></i> Clinical Safety & Quality Control:</h6>
                    <div class="small text-dark" style="white-space: pre-line;" id="aiClinicalAdviceText">
                        <!-- Populated via JS -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card-laravel animate-fade-in mb-4">
    <div class="card-header bg-white border-bottom p-3">
        <ul class="nav nav-pills" id="compoundingTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" id="execute-tab" data-bs-toggle="tab" data-bs-target="#execute" type="button" role="tab">
                    <i class="bi bi-gear-wide-connected me-1"></i> Execute Production Batch
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="formulas-tab" data-bs-toggle="tab" data-bs-target="#formulas" type="button" role="tab">
                    <i class="bi bi-journal-medical me-1"></i> Master Formulas Directory
                </button>
            </li>
        </ul>
    </div>

    <div class="card-body p-4">
        <div class="tab-content">
            <!-- Production Batch Tab -->
            <div class="tab-pane fade show active" id="execute" role="tabpanel">
                <div class="p-3 border rounded-3 bg-light mb-4">
                    <h5 class="fw-bold text-dark mb-3"><i class="bi bi-play-circle me-2 text-primary"></i> Run Compounding Production</h5>
                    <form method="POST" action="{{ route('compounding.index') }}">
                        @csrf
                        <div class="row g-3 align-items-end">
                            <div class="col-md-6">
                                <label class="fw-bold text-muted small mb-1">Select Master Formula</label>
                                <select name="formula_id" class="form-select rounded-pill px-3" required>
                                    <option value="">Choose formula to compound...</option>
                                    <?php foreach($formulas as $f): ?>
                                        <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['name']) ?> (Outputs: <?= htmlspecialchars($f['output_name']) ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="fw-bold text-muted small mb-1">Batches to Produce</label>
                                <input type="number" name="batches_to_produce" class="form-control rounded-pill px-3" min="1" value="1" required>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" name="execute_compounding" class="btn btn-laravel-primary rounded-pill w-100 fw-bold" onclick="return confirm('Execute this compounding process? Raw ingredient stocks will be deducted automatically.')">
                                    <i class="bi bi-lightning-fill me-1"></i> Execute Production
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Recent Compounding Logs -->
                <h6 class="fw-bold text-dark mb-3"><i class="bi bi-clock-history me-1 text-primary"></i> Compounding Logs</h6>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th># Log ID</th>
                                <th>Formula Name</th>
                                <th>Batches Produced</th>
                                <th>Est. Production Cost</th>
                                <th>Date Executed</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($compounding_logs)): ?>
                                <tr><td colspan="5" class="text-center py-4 text-muted">No compounding logs recorded yet.</td></tr>
                            <?php else: ?>
                                <?php foreach($compounding_logs as $l): ?>
                                    <tr>
                                        <td><strong class="text-primary font-monospace">#CMP-<?= sprintf('%04d', $l['id']) ?></strong></td>
                                        <td><strong class="text-dark"><?= htmlspecialchars($l['formula_name'] ?? 'Formula') ?></strong></td>
                                        <td><span class="badge-laravel-blue"><?= number_format($l['batches_produced']) ?> Batches</span></td>
                                        <td><strong class="text-success">TZS <?= number_format($l['total_cost'], 2) ?></strong></td>
                                        <td><small class="text-muted"><?= date('d M Y, H:i', strtotime($l['created_at'])) ?></small></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Formulas Directory Tab -->
            <div class="tab-pane fade" id="formulas" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold text-dark mb-0"><i class="bi bi-journal-check me-2 text-primary"></i> Formulated Drug Preparation Catalog</h5>
                    <button class="btn btn-sm btn-laravel-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#addFormulaModal">
                        <i class="bi bi-plus-circle me-1"></i> New Formula
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Formula Name</th>
                                <th>Output Product</th>
                                <th>Output Qty per Batch</th>
                                <th>Instructions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($formulas)): ?>
                                <tr><td colspan="4" class="text-center py-4 text-muted">No compounding formulas saved. Click "+ Create Formula".</td></tr>
                            <?php else: ?>
                                <?php foreach($formulas as $f): ?>
                                    <tr>
                                        <td><strong class="text-dark"><i class="bi bi-recipe me-1 text-primary"></i> <?= htmlspecialchars($f['name']) ?></strong></td>
                                        <td><span class="badge-laravel-green"><?= htmlspecialchars($f['output_name']) ?></span></td>
                                        <td><strong class="text-primary"><?= number_format($f['output_quantity']) ?> units</strong></td>
                                        <td><small class="text-muted"><?= htmlspecialchars($f['instructions'] ?: 'Standard preparation instructions.') ?></small></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Add Formula -->
<div class="modal fade" id="addFormulaModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="bi bi-journal-plus me-2"></i> Create Compounding Master Formula</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="{{ route('compounding.index') }}">
        @csrf
        <div class="modal-body p-4">
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="fw-bold text-muted small mb-1">Formula Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="formulaNameInput" class="form-control rounded-pill px-3" placeholder="e.g. Paracetamol Mixture 120mg/5ml" required>
                </div>
                <div class="col-md-6">
                    <label class="fw-bold text-muted small mb-1">Output Product Medicine <span class="text-danger">*</span></label>
                    <select name="output_drug_id" id="formulaOutputDrugSelect" class="form-select rounded-pill px-3" required>
                        <option value="">Select target medicine...</option>
                        <?php foreach($drugs as $d): ?>
                            <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?> (<?= htmlspecialchars($d['brand_name'] ?: 'Generic') ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-12">
                    <label class="fw-bold text-muted small mb-1">Output Batch Quantity <span class="text-danger">*</span></label>
                    <input type="number" name="output_quantity" id="formulaOutputQtyInput" class="form-control rounded-pill px-3" placeholder="Number of units produced per batch" min="1" value="100" required>
                </div>
                <div class="col-md-12">
                    <label class="fw-bold text-muted small mb-1">Preparation & Clinical Procedure</label>
                    <textarea name="instructions" id="formulaInstructionsTextarea" class="form-control rounded px-3" rows="4" placeholder="Step-by-step compounding procedure..."></textarea>
                </div>
            </div>

            <hr>
            <h6 class="fw-bold text-dark mb-2"><i class="bi bi-basket3 me-1 text-primary"></i> Formula Ingredients List</h6>
            <div id="ingredientsContainer">
                <div class="row g-2 mb-2 ingredient-row">
                    <div class="col-md-7">
                        <select name="ingredients[]" class="form-select rounded-pill px-3" required>
                            <option value="">Select raw ingredient medicine...</option>
                            <?php foreach($drugs as $d): ?>
                                <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="number" step="any" name="quantities[]" class="form-control rounded-pill px-3" placeholder="Quantity needed" min="0.01" required>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-outline-danger btn-sm rounded-circle mt-1" onclick="removeIngredientRow(this)"><i class="bi bi-trash"></i></button>
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill mt-2" onclick="addIngredientRow()">
                <i class="bi bi-plus-lg me-1"></i> Add Another Ingredient
            </button>
        </div>
        <div class="modal-footer border-0 bg-light">
            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" name="add_formula" class="btn btn-laravel-primary rounded-pill px-4 fw-bold"><i class="bi bi-check-circle me-1"></i> Save Formula</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
let lastAiData = null;

function quickAiConsult(compoundName) {
    document.getElementById('aiCompoundQuery').value = compoundName;
    consultAICompounding();
}

function consultAICompounding() {
    let query = document.getElementById('aiCompoundQuery').value.trim();
    if(!query) return alert('Tafadhali andika jina la compounding mfano Paracetamol Mixture au Calamine Lotion');

    let fd = new FormData();
    fd.append('compound_query', query);

    fetch('api_ai_compounding.php', { method: 'POST', body: fd })
        .then(res => res.json())
        .then(res => {
            if(res.success && res.data) {
                lastAiData = res.data;
                let d = res.data;
                document.getElementById('aiResultTitle').innerHTML = `<i class="bi bi-journal-medical me-2"></i> AI Clinical Guidance: ${d.compound_title}`;
                document.getElementById('aiShelfLifeText').innerText = d.stability_shelf_life || '14 days refrigerated';
                document.getElementById('aiInstructionsText').innerText = d.instructions || '';
                document.getElementById('aiClinicalAdviceText').innerText = d.clinical_advice || '';

                let ingHtml = '';
                if(d.ingredients && d.ingredients.length > 0) {
                    d.ingredients.forEach(i => {
                        ingHtml += `
                            <tr>
                                <td><strong class="text-dark"><i class="bi bi-check2-circle text-success me-1"></i>${i.name}</strong></td>
                                <td><span class="badge-laravel-blue">${i.quantity} ${i.unit}</span></td>
                            </tr>
                        `;
                    });
                }
                document.getElementById('aiIngredientsTbody').innerHTML = ingHtml;
                document.getElementById('aiCompoundingResultCard').classList.remove('d-none');
                document.getElementById('aiCompoundingResultCard').scrollIntoView({ behavior: 'smooth' });
            } else {
                alert(res.message || 'Error fetching AI Compounding advice.');
            }
        });
}

function autoFillFormulaModal() {
    if(!lastAiData) return;
    let d = lastAiData;

    document.getElementById('formulaNameInput').value = d.formula_name || d.compound_title;
    document.getElementById('formulaOutputQtyInput').value = d.output_quantity || 100;
    document.getElementById('formulaInstructionsTextarea').value = d.instructions + "\n\n" + (d.clinical_advice || '');

    new bootstrap.Modal(document.getElementById('addFormulaModal')).show();
}

function addIngredientRow() {
    let container = document.getElementById('ingredientsContainer');
    let firstRow = container.querySelector('.ingredient-row');
    if(firstRow) {
        let clone = firstRow.cloneNode(true);
        clone.querySelectorAll('input').forEach(i => i.value = '');
        clone.querySelectorAll('select').forEach(s => s.selectedIndex = 0);
        container.appendChild(clone);
    }
}

function removeIngredientRow(btn) {
    let rows = document.querySelectorAll('.ingredient-row');
    if(rows.length > 1) {
        btn.closest('.ingredient-row').remove();
    }
}
</script>
@endsection
