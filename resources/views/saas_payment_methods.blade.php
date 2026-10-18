@extends('layouts.app')

@section('content')
<!-- Daawafy Executive SaaS Payment Methods Governance View -->
<div class="laravel-hero-banner mb-4 d-flex justify-content-between align-items-center animate-fade-in">
    <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-credit-card-2-front me-2 text-warning"></i> Platform Payment Methods Governance</h2>
        <p class="text-white-50 mb-0">Simamia na wezesha au zima njia za malipo (Enable / Disable) kwenye maduka na matawi yote ya mfumo wa Daawafy.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('saas.dashboard') }}" class="btn btn-outline-light rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i> SaaS Dashboard
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success rounded-pill px-4 mb-4 animate-fade-in shadow-sm"><i class="bi bi-check-circle me-2"></i> {{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger rounded-pill px-4 mb-4 animate-fade-in shadow-sm"><i class="bi bi-exclamation-triangle me-2"></i> {{ session('error') }}</div>
@endif

<!-- Quick Metrics Overview -->
<div class="row g-3 mb-4 animate-fade-in">
    <div class="col-md-3">
        <div class="metric-widget metric-blue">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="metric-title">Jumla ya Njia za Malipo</div>
                    <div class="metric-value text-primary">{{ $totalMethods }}</div>
                </div>
                <div class="metric-icon"><i class="bi bi-wallet2"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="metric-widget metric-green">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="metric-title">Zilizo Hewani (Enabled)</div>
                    <div class="metric-value text-success">{{ $activeCount }}</div>
                </div>
                <div class="metric-icon"><i class="bi bi-check2-circle"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="metric-widget metric-purple">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="metric-title">Zilizozimwa (Disabled)</div>
                    <div class="metric-value {{ $disabledCount > 0 ? 'text-danger' : 'text-muted' }}">{{ $disabledCount }}</div>
                </div>
                <div class="metric-icon"><i class="bi bi-slash-circle"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="metric-widget" style="border-left: 4px solid #f59e0b;">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="metric-title">Njia ya Msingi (Locked)</div>
                    <div class="metric-value text-warning" style="font-size: 1.25rem;">Cash (100% On)</div>
                </div>
                <div class="metric-icon text-warning"><i class="bi bi-shield-lock-fill"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- Super Admin Guidance Notice -->
<div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-primary-subtle text-primary-emphasis d-flex flex-row align-items-center gap-3">
    <div class="fs-2 text-primary">
        <i class="bi bi-shield-shaded"></i>
    </div>
    <div class="flex-grow-1">
        <h6 class="fw-bold mb-1"><i class="bi bi-info-circle me-1"></i> Kanuni ya Udhibiti wa Njia za Malipo</h6>
        <p class="mb-0 small" style="line-height: 1.4;">
            Ukizima (Disable) njia yoyote hapa chini, haitaonekana tena kwenye skrini za <strong>POS (Dispense)</strong>, <strong>Dawati la Cashier (Receive Payments)</strong>, wala kwenye miamala mipya katika matawi yote. Njia ya <strong>Cash (Pesa Taslimu)</strong> haiwezi kuzimwa kamwe kwa sababu ni njia ya msingi ya uendeshaji wa duka la dawa.
        </p>
    </div>
</div>

<!-- Payment Methods Cards Grid -->
<div class="row g-4 mb-4">
    @foreach($methods as $pm)
        @php
            $isCash = ($pm->is_locked || strcasecmp($pm->name, 'Cash') === 0);
            $bgBadge = $pm->is_enabled ? 'bg-success-subtle text-success border-success-subtle' : 'bg-danger-subtle text-danger border-danger-subtle';
        @endphp
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm rounded-4 transition-all position-relative" style="background: #ffffff; border-top: 4px solid {{ $pm->is_enabled ? '#10b981' : '#ef4444' }} !important;" id="pmCard_{{ $pm->id }}">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <!-- Header with Icon & Status -->
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-3 p-2 d-flex align-items-center justify-content-center shadow-sm" style="width: 44px; height: 44px; background: {{ $pm->is_enabled ? '#ecfdf5' : '#fef2f2' }}; color: {{ $pm->is_enabled ? '#059669' : '#dc2626' }};">
                                    <i class="bi {{ $pm->icon ?: 'bi-cash' }} fs-4"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-0 text-dark">{{ $pm->name }}</h5>
                                    <small class="text-muted font-monospace" style="font-size: 0.72rem;">CODE: {{ strtoupper($pm->code) }}</small>
                                </div>
                            </div>
                            <div>
                                <span class="badge {{ $bgBadge }} border rounded-pill px-3 py-1 small fw-bold" id="badgeStatus_{{ $pm->id }}">
                                    @if($pm->is_enabled)
                                        <i class="bi bi-check-circle-fill me-1"></i> ACTIVE
                                    @else
                                        <i class="bi bi-x-circle-fill me-1"></i> DISABLED
                                    @endif
                                </span>
                            </div>
                        </div>

                        <!-- Display Name -->
                        <div class="mb-2">
                            <strong class="d-block text-secondary small fw-semibold">Jina Linaloonekana (Display Name):</strong>
                            <span class="text-dark fw-bold" style="font-size: 0.95rem;">{{ $pm->display_name }}</span>
                        </div>

                        <!-- Description -->
                        <p class="text-muted small mb-3" style="min-height: 48px; font-size: 0.82rem; line-height: 1.4;">
                            {{ $pm->description ?: 'Hakuna maelezo yaliyowekwa kwa sasa.' }}
                        </p>
                    </div>

                    <!-- Footer Controls -->
                    <div class="border-top pt-3 d-flex align-items-center justify-content-between mt-2">
                        @if($isCash)
                            <div class="d-flex align-items-center text-muted small fw-semibold">
                                <i class="bi bi-shield-lock-fill text-warning fs-5 me-2"></i>
                                <span>Core Method (Always Active)</span>
                            </div>
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" checked disabled style="cursor: not-allowed; width: 45px; height: 24px;">
                            </div>
                        @else
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1" onclick="openEditModal({{ json_encode($pm) }})">
                                <i class="bi bi-pencil me-1"></i> Badili
                            </button>

                            <div class="d-flex align-items-center gap-2">
                                <span class="small text-muted fw-semibold" id="toggleLabel_{{ $pm->id }}">
                                    {{ $pm->is_enabled ? 'Imewashwa' : 'Imezimwa' }}
                                </span>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           role="switch" 
                                           id="switch_{{ $pm->id }}" 
                                           {{ $pm->is_enabled ? 'checked' : '' }} 
                                           style="width: 45px; height: 24px; cursor: pointer;"
                                           onchange="togglePaymentMethodAjax({{ $pm->id }}, '{{ $pm->name }}')">
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<!-- Modal for Editing Payment Method Details -->
<div class="modal fade" id="editPaymentMethodModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="" method="POST" id="editPaymentMethodForm">
                @csrf
                <div class="modal-header bg-dark text-white py-3 px-4 rounded-top-4">
                    <h6 class="modal-title mb-0 fw-bold"><i class="bi bi-sliders me-2 text-warning"></i> Badili Taarifa za Njia ya Malipo</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="fw-bold text-muted small mb-1">Jina Rasmi la Mfumo (System Name)</label>
                        <input type="text" id="editMethodName" class="form-control rounded-pill px-3 bg-light" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold text-dark small mb-1">Jina Linaloonekana kwa Watumiaji <span class="text-danger">*</span></label>
                        <input type="text" name="display_name" id="editDisplayName" class="form-control rounded-pill px-3" required>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold text-dark small mb-1">Maelezo ya Njia Hii ya Malipo</label>
                        <textarea name="description" id="editDescription" class="form-control rounded-3 p-3" rows="3" placeholder="Maelezo kuhusu jinsi njia hii inavyotumika..."></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="fw-bold text-muted small mb-1">Mpangilio (Sort Order)</label>
                        <input type="number" name="sort_order" id="editSortOrder" class="form-control rounded-pill px-3 w-50" min="1" max="10">
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light rounded-bottom-4 px-4 py-3">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Ghairi</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                        <i class="bi bi-check-circle me-1"></i> Hifadhi Mabadiliko
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Toast Notification container for AJAX feedback -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080;">
    <div id="statusToast" class="toast align-items-center text-white border-0 rounded-4 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body fw-semibold" id="toastMessage">
                Action completed.
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<script>
function togglePaymentMethodAjax(methodId, methodName) {
    const sw = document.getElementById('switch_' + methodId);
    const label = document.getElementById('toggleLabel_' + methodId);
    const badge = document.getElementById('badgeStatus_' + methodId);
    const card = document.getElementById('pmCard_' + methodId);
    const toastEl = document.getElementById('statusToast');
    const toastMsg = document.getElementById('toastMessage');

    const originalState = !sw.checked;
    sw.disabled = true;

    fetch(`{{ url('/saas-dashboard/payment-methods') }}/${methodId}/toggle`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(async r => {
        const text = await r.text();
        try {
            return JSON.parse(text);
        } catch(e) {
            throw new Error('Seva haikutoa majibu sahihi: ' + text.substring(0, 80));
        }
    })
    .then(data => {
        sw.disabled = false;
        if(data.success) {
            if(data.is_enabled) {
                label.innerText = 'Imewashwa';
                badge.className = 'badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 small fw-bold';
                badge.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> ACTIVE';
                card.style.borderTop = '4px solid #10b981';
            } else {
                label.innerText = 'Imezimwa';
                badge.className = 'badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1 small fw-bold';
                badge.innerHTML = '<i class="bi bi-x-circle-fill me-1"></i> DISABLED';
                card.style.borderTop = '4px solid #ef4444';
            }

            toastEl.className = 'toast align-items-center text-white bg-success border-0 rounded-4 shadow-lg show';
            toastMsg.innerText = data.message;
            setTimeout(() => {
                toastEl.classList.remove('show');
            }, 4000);
        } else {
            sw.checked = originalState;
            alert(data.message || 'Kosa limejitokeza wakati wa kubadili hali ya njia ya malipo.');
        }
    })
    .catch(err => {
        sw.disabled = false;
        sw.checked = originalState;
        toastEl.className = 'toast align-items-center text-white bg-danger border-0 rounded-4 shadow-lg show';
        toastMsg.innerText = 'Hitilafu: ' + err.message;
        setTimeout(() => {
            toastEl.classList.remove('show');
        }, 5000);
    });
}

function openEditModal(pm) {
    document.getElementById('editPaymentMethodForm').action = `{{ url('/saas-dashboard/payment-methods') }}/${pm.id}/update`;
    document.getElementById('editMethodName').value = pm.name;
    document.getElementById('editDisplayName').value = pm.display_name || pm.name;
    document.getElementById('editDescription').value = pm.description || '';
    document.getElementById('editSortOrder').value = pm.sort_order || 1;

    const modal = new bootstrap.Modal(document.getElementById('editPaymentMethodModal'));
    modal.show();
}
</script>
@endsection
