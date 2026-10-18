@extends('layouts.app')

@section('content')
<div class="laravel-hero-banner mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3 animate-fade-in">
    <div>
        <h2 class="fw-bold mb-1 text-dark"><i class="bi bi-shield-plus me-2 text-primary"></i> Health Insurance & Electronic Verification Gateways</h2>
        <p class="text-muted mb-0 small">Automated real-time card verification, API integration for NHIF, Jubilee & Strategis, and electronic claims management.</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-primary rounded-pill px-3 shadow-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#testVerifyModal">
            <i class="bi bi-credit-card-2-front me-1"></i> Verify Member Card
        </button>
        <a href="{{ route('insurance.create_claim') }}" class="btn btn-success rounded-pill px-3 shadow-sm fw-semibold">
            <i class="bi bi-file-earmark-plus me-1"></i> + File Manual Claim
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Metric Overview Counters (Decorated Executive Style) -->
<div class="row g-3 mb-4 animate-fade-in">
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="metric-widget metric-blue">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="metric-title">Active Gateways</div>
                    <div class="metric-value text-primary">{{ count($providers) }} Integrated</div>
                </div>
                <div class="metric-icon"><i class="bi bi-shield-check"></i></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="metric-widget metric-cyan">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="metric-title">Gateway Status</div>
                    <div class="metric-value text-info">Sandbox Ready</div>
                </div>
                <div class="metric-icon"><i class="bi bi-hdd-network"></i></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="metric-widget metric-yellow">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="metric-title">Pending Claims</div>
                    <div class="metric-value text-warning">{{ $claims->where('status', 'Pending')->count() }}</div>
                </div>
                <div class="metric-icon"><i class="bi bi-hourglass-top"></i></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="metric-widget metric-green">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="metric-title">Approved Claims</div>
                    <div class="metric-value text-success">{{ $claims->where('status', 'Approved')->count() }}</div>
                </div>
                <div class="metric-icon"><i class="bi bi-check2-all"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- SECTION 1: ELECTRONIC INSURANCE GATEWAYS (NHIF, JUBILEE, STRATEGIS) -->
<div class="card shadow-sm border-0 rounded-4 mb-4 bg-white animate-fade-in">
    <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-router me-2 text-primary"></i> Electronic Verification Gateways (API Configurations)</h5>
            <small class="text-muted">Miundombinu ya uhakiki wa kadi moja kwa moja. Badilisha API Keys na mazingira (Sandbox / Live) kulingana na mahitaji.</small>
        </div>
    </div>
    <div class="card-body p-4">
        <div class="row g-4">
            @foreach($providers as $p)
                @php
                    $isNhif = str_contains(strtoupper($p->code ?? ''), 'NHIF');
                    $isJubilee = str_contains(strtoupper($p->code ?? ''), 'JUBILEE');
                    $isStrategis = str_contains(strtoupper($p->code ?? ''), 'STRATEGIS');
                    
                    $themeClass = $isNhif ? 'border-primary' : ($isJubilee ? 'border-success' : 'border-purple');
                    $badgeClass = $p->api_environment === 'production' ? 'bg-success text-white' : 'bg-warning text-dark';
                    $iconClass = $isNhif ? 'bi-hospital' : ($isJubilee ? 'bi-shield-shaded' : 'bi-award');
                @endphp
                <div class="col-12 col-lg-4">
                    <div class="card shadow-sm border-0 border-start border-4 {{ $themeClass }} rounded-4 h-100 p-3 bg-white d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-light p-2 d-flex align-items-center justify-content-center shadow-sm" style="width: 42px; height: 42px;">
                                        <i class="bi {{ $iconClass }} fs-4 text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-0 text-dark">{{ $p->name }}</h6>
                                        <small class="text-muted font-monospace">{{ $p->code }}</small>
                                    </div>
                                </div>
                                <span class="badge {{ $badgeClass }} rounded-pill px-2 py-1 small">
                                    {{ strtoupper($p->api_environment ?? 'sandbox') }}
                                </span>
                            </div>

                            <form method="POST" action="{{ route('insurance.update_settings', $p->id) }}" id="formProvider{{ $p->id }}">
                                @csrf
                                <div class="mb-2">
                                    <label class="fw-semibold text-secondary small mb-1">Facility / Provider Code</label>
                                    <input type="text" name="facility_code" class="form-control form-control-sm font-monospace" 
                                           value="{{ $p->facility_code }}" placeholder="e.g. TZ-NHIF-90412">
                                </div>

                                <div class="mb-2">
                                    <label class="fw-semibold text-secondary small mb-1">API Key / Client ID</label>
                                    <input type="text" name="api_key" class="form-control form-control-sm font-monospace" 
                                           value="{{ $p->api_key }}" placeholder="Enter API Key">
                                </div>

                                <div class="mb-2">
                                    <label class="fw-semibold text-secondary small mb-1">API Secret / Password</label>
                                    <div class="input-group input-group-sm">
                                        <input type="password" name="api_secret" id="secretField{{ $p->id }}" 
                                               class="form-control font-monospace" value="{{ $p->api_secret }}" placeholder="Enter Secret">
                                        <button class="btn btn-outline-secondary" type="button" onclick="toggleSecretVisibility('secretField{{ $p->id }}')">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="mb-2">
                                    <label class="fw-semibold text-secondary small mb-1">Base Gateway URL</label>
                                    <input type="url" name="api_base_url" class="form-control form-control-sm font-monospace small" 
                                           value="{{ $p->api_base_url }}" placeholder="https://api.gateway.or.tz/v1">
                                </div>

                                <div class="mb-3">
                                    <label class="fw-semibold text-secondary small mb-1">Environment Mode</label>
                                    <select name="api_environment" class="form-select form-select-sm">
                                        <option value="sandbox" {{ ($p->api_environment ?? 'sandbox') === 'sandbox' ? 'selected' : '' }}>🧪 Sandbox Mode (Sample / Test Keys)</option>
                                        <option value="production" {{ ($p->api_environment ?? '') === 'production' ? 'selected' : '' }}>🚀 Production Mode (Live Official Keys)</option>
                                    </select>
                                </div>

                                <div class="d-flex gap-2 mb-2">
                                    <button type="submit" class="btn btn-sm btn-primary rounded-pill flex-grow-1 fw-semibold">
                                        <i class="bi bi-save me-1"></i> Save Keys
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="testConnection({{ $p->id }}, '{{ $p->name }}')">
                                        <i class="bi bi-broadcast"></i> Test
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Connection Test Result Toast Area -->
                        <div id="connResult{{ $p->id }}" class="small mt-2" style="display: none;"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- SECTION 2: CLAIMS TABLE & RECONCILIATION -->
<div class="card shadow-sm border-0 rounded-4 bg-white animate-fade-in">
    <div class="card-header bg-white border-bottom py-3 px-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-receipt-cutoff me-2 text-primary"></i> Filed Insurance Claims & Reimbursements</h5>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-muted border px-3 py-2 rounded-pill small">
                Total Claims Value: <strong>TZS {{ number_format($claims->sum('amount')) }}</strong>
            </span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small">
                    <tr>
                        <th class="ps-4">Claim #</th>
                        <th>Provider</th>
                        <th>Member Details</th>
                        <th>Authorization / Pre-Auth</th>
                        <th>Claim Amount (TZS)</th>
                        <th>Status</th>
                        <th>Date Filed</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($claims as $c)
                        <tr>
                            <td class="ps-4">
                                <strong class="font-monospace text-dark">{{ $c->claim_number }}</strong>
                                <small class="text-muted d-block font-monospace">Txn #{{ $c->transaction_id }}</small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border px-2 py-1">
                                    {{ $c->insuranceProvider?->name ?? 'Insurance' }}
                                </span>
                            </td>
                            <td>
                                <strong class="d-block text-dark">{{ $c->member_name ?: ($c->transaction?->patient?->name ?? 'Patient') }}</strong>
                                <small class="text-muted font-monospace">Card: {{ $c->member_number ?: 'N/A' }}</small>
                                @if($c->scheme_name)
                                    <small class="text-primary d-block">{{ $c->scheme_name }}</small>
                                @endif
                            </td>
                            <td>
                                @if($c->authorization_code)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace px-2 py-1">
                                        <i class="bi bi-check-circle me-1"></i> {{ $c->authorization_code }}
                                    </span>
                                @else
                                    <span class="text-muted small">Not Verified</span>
                                @endif
                            </td>
                            <td class="fw-bold text-dark">TZS {{ number_format($c->amount) }}</td>
                            <td>
                                @if($c->status === 'Approved')
                                    <span class="badge bg-success rounded-pill px-2 py-1"><i class="bi bi-check-circle me-1"></i> Approved</span>
                                @elseif($c->status === 'Rejected')
                                    <span class="badge bg-danger rounded-pill px-2 py-1">Rejected</span>
                                @else
                                    <span class="badge bg-warning text-dark rounded-pill px-2 py-1"><i class="bi bi-clock me-1"></i> Pending</span>
                                @endif
                            </td>
                            <td class="small text-muted">{{ $c->created_at?->format('d M Y, H:i') }}</td>
                            <td class="text-end pe-4">
                                @if($c->status === 'Pending')
                                    <form action="{{ route('insurance.approve_claim', $c->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success rounded-pill px-2 me-1" title="Approve Claim">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('insurance.reject_claim', $c->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2" title="Reject Claim">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted small"><i class="bi bi-shield-check text-success me-1"></i> Processed</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-shield-slash fs-2 d-block mb-2 text-muted"></i>
                                Hakuna madai ya bima yaliyorekodiwa kwa sasa.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: LIVE TEST CARD VERIFIER                                            -->
<!-- ========================================================================= -->
<div class="modal fade" id="testVerifyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-dark text-white py-3 px-4 rounded-top-4">
                <h6 class="modal-title mb-0 fw-bold"><i class="bi bi-credit-card-2-front me-2 text-primary"></i> Real-time Member Card Verification Tool</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="fw-semibold text-secondary small mb-1">Select Insurance Provider</label>
                    <select id="simProviderId" class="form-select rounded-3">
                        @foreach($providers as $p)
                            <option value="{{ $p->id }}" data-code="{{ $p->code }}">{{ $p->name }} ({{ strtoupper($p->api_environment) }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="fw-semibold text-secondary small mb-1">Card / Member Number</label>
                    <div class="input-group">
                        <input type="text" id="simCardNumber" class="form-control rounded-start-3 font-monospace" placeholder="e.g. 01-98234821 or JUB-9941" value="01-88492014">
                        <button class="btn btn-primary px-3 rounded-end-3" type="button" id="btnRunVerification" onclick="runLiveVerification()">
                            <i class="bi bi-search me-1"></i> Verify
                        </button>
                    </div>
                    <small class="text-muted mt-1 d-block">Sample active numbers: <code>01-88492014</code>, <code>JUB-4491</code>, <code>STRAT-1029</code>. Type <code>EXPIRED</code> to test expired card.</small>
                </div>

                <!-- Verification Result Display Area -->
                <div id="simResultArea" style="display: none;" class="mt-3">
                    <div class="card border rounded-4 p-3 shadow-sm" id="simResultCard">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge rounded-pill px-2 py-1" id="simCardStatusBadge">ACTIVE</span>
                            <span class="small font-monospace text-muted" id="simAuthCodeBadge">AUTH: ---</span>
                        </div>
                        <h5 class="fw-bold text-dark mb-1" id="simMemberName">---</h5>
                        <div class="small text-muted mb-2" id="simScheme">Scheme: ---</div>
                        <div class="p-2 rounded bg-light small" id="simMessage"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2 px-4 border-top">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function toggleSecretVisibility(id) {
    const el = document.getElementById(id);
    if (el) {
        el.type = el.type === 'password' ? 'text' : 'password';
    }
}

function testConnection(providerId, providerName) {
    const resultDiv = document.getElementById('connResult' + providerId);
    resultDiv.style.display = 'block';
    resultDiv.innerHTML = `<span class="text-muted"><i class="spinner-border spinner-border-sm me-1"></i> Testing connection to ${providerName}...</span>`;

    fetch(`{{ url('/insurance/providers') }}/${providerId}/test-connection`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(async r => {
        const text = await r.text();
        try {
            return JSON.parse(text);
        } catch(e) {
            throw new Error('Jibu lisilo sahihi kutoka seva: ' + text.substring(0, 80));
        }
    })
    .then(data => {
        if (data.success) {
            resultDiv.innerHTML = `<div class="alert alert-success py-1 px-2 mb-0 rounded-3 small">
                <i class="bi bi-check-circle-fill me-1"></i> ${data.message} (${data.latency_ms}ms)
            </div>`;
        } else {
            resultDiv.innerHTML = `<div class="alert alert-danger py-1 px-2 mb-0 rounded-3 small">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> ${data.message}
            </div>`;
        }
    })
    .catch(err => {
        resultDiv.innerHTML = `<div class="alert alert-danger py-1 px-2 mb-0 rounded-3 small">
            <i class="bi bi-x-circle me-1"></i> Failed to connect: ${err.message}
        </div>`;
    });
}

function runLiveVerification() {
    const providerId = document.getElementById('simProviderId').value;
    const cardNumber = document.getElementById('simCardNumber').value.trim();
    const btn = document.getElementById('btnRunVerification');
    const resultArea = document.getElementById('simResultArea');
    const cardEl = document.getElementById('simResultCard');

    if (!cardNumber) {
        alert('Tafadhali ingiza nambari ya kadi!');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = `<span class="spinner-border spinner-border-sm"></span>`;

    fetch('{{ route('insurance.verify_card') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            provider_id: providerId,
            card_number: cardNumber
        })
    })
    .then(async r => {
        const text = await r.text();
        try {
            return JSON.parse(text);
        } catch(e) {
            throw new Error('Jibu lisilo sahihi kutoka seva: ' + text.substring(0, 80));
        }
    })
    .then(res => {
        btn.disabled = false;
        btn.innerHTML = `<i class="bi bi-search me-1"></i> Verify`;
        resultArea.style.display = 'block';

        if (res.is_valid) {
            cardEl.className = 'card border border-success rounded-4 p-3 shadow-sm bg-success-subtle';
            document.getElementById('simCardStatusBadge').className = 'badge bg-success rounded-pill px-2 py-1';
            document.getElementById('simCardStatusBadge').innerText = 'ACTIVE (UTHIBITISHO UMEFAULU)';
            document.getElementById('simAuthCodeBadge').innerText = res.authorization_code || 'AUTH: GENERATED';
            document.getElementById('simMemberName').innerText = res.member_name || 'Verified Member';
            document.getElementById('simScheme').innerText = 'Mpango / Scheme: ' + (res.scheme || 'Standard Cover');
            document.getElementById('simMessage').innerText = res.message || 'Mwanachama ana sifa za kupatiwa dawa.';
        } else {
            cardEl.className = 'card border border-danger rounded-4 p-3 shadow-sm bg-danger-subtle';
            document.getElementById('simCardStatusBadge').className = 'badge bg-danger rounded-pill px-2 py-1';
            document.getElementById('simCardStatusBadge').innerText = 'HAIRUHUSIWI / ' + (res.status || 'EXPIRED');
            document.getElementById('simAuthCodeBadge').innerText = 'NO AUTH CODE';
            document.getElementById('simMemberName').innerText = res.member_name || 'Unknown / Blocked';
            document.getElementById('simScheme').innerText = 'Mpango / Scheme: ' + (res.scheme || 'N/A');
            document.getElementById('simMessage').innerText = res.message || 'Kadi hii haijathibitishwa na bima.';
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = `<i class="bi bi-search me-1"></i> Verify`;
        alert('Kosa wakati wa kuhakiki: ' + err.message);
    });
}
</script>
@endsection
