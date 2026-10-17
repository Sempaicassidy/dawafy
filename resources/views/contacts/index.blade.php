@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom animate-fade-in">
    <div>
        <div class="d-flex align-items-center gap-2">
            <h4 class="fw-bold text-dark mb-0"><i class="bi bi-journal-bookmark-fill text-primary me-2"></i> Address Book & Operational Directory</h4>
            <span class="badge bg-primary text-white rounded-pill px-3 py-1">Directory Hub</span>
        </div>
        <small class="text-muted">Essential contacts for Suppliers & Wholesalers, Chronic Care & Debtor Patients, Store Staff, and Insurance Providers</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('procurement.create') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
            <i class="bi bi-cart-plus me-1"></i> Order Supplies (PO)
        </a>
        <div class="dropdown">
            <button class="btn btn-primary btn-sm rounded-pill px-3 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-plus-circle me-1"></i> Add Contact
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">
                <li><a class="dropdown-item py-2" href="#" data-bs-toggle="modal" data-bs-target="#addStaffModal"><i class="bi bi-person-plus-fill text-primary me-2"></i> Register Store Staff</a></li>
                <li><a class="dropdown-item py-2" href="#" data-bs-toggle="modal" data-bs-target="#addSupplierModal"><i class="bi bi-truck text-primary me-2"></i> Add Supplier / Wholesaler</a></li>
                <li><a class="dropdown-item py-2" href="#" data-bs-toggle="modal" data-bs-target="#addPatientModal"><i class="bi bi-person-heart text-success me-2"></i> Add Patient / Customer</a></li>
                <li><a class="dropdown-item py-2" href="#" data-bs-toggle="modal" data-bs-target="#addInsuranceModal"><i class="bi bi-shield-plus text-danger me-2"></i> Add Insurance Provider</a></li>
            </ul>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success py-2 px-3 mb-3 rounded-pill d-flex align-items-center shadow-sm">
        <i class="bi bi-check-circle-fill me-2 fs-5"></i> {{ session('success') }}
    </div>
@endif

@if(isset($errors) && $errors->any())
    <div class="alert alert-danger py-2 px-3 mb-3 rounded-4 shadow-sm">
        <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-2"></i> Could not save record:</div>
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<!-- Quick Overview KPI Cards -->
<div class="row g-3 mb-4 animate-fade-in">
    <div class="col-md-3">
        <div class="metric-widget metric-blue p-3 rounded-4 shadow-sm border-0">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="metric-title text-muted small fw-semibold">Suppliers & Wholesalers</div>
                    <div class="metric-value fs-3 fw-bold text-dark">{{ $stats['total_suppliers'] }}</div>
                    <small class="text-primary">Wholesalers & manufacturers</small>
                </div>
                <div class="metric-icon fs-2 text-primary opacity-75"><i class="bi bi-truck"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="metric-widget metric-green p-3 rounded-4 shadow-sm border-0">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="metric-title text-muted small fw-semibold">Patients & Customers</div>
                    <div class="metric-value fs-3 fw-bold text-dark">{{ $stats['total_patients'] }}</div>
                    <small class="text-success">{{ $stats['chronic_patients_count'] }} chronic care patients</small>
                </div>
                <div class="metric-icon fs-2 text-success opacity-75"><i class="bi bi-people-fill"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="metric-widget metric-yellow p-3 rounded-4 shadow-sm border-0">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="metric-title text-muted small fw-semibold">Store Staff</div>
                    <div class="metric-value fs-3 fw-bold text-dark">{{ $stats['total_staff'] }}</div>
                    <small class="text-warning text-dark">Manager, Pharmacist, Cashier</small>
                </div>
                <div class="metric-icon fs-2 text-warning opacity-75"><i class="bi bi-person-badge"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="metric-widget metric-red p-3 rounded-4 shadow-sm border-0">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="metric-title text-muted small fw-semibold">Insurance Providers</div>
                    <div class="metric-value fs-3 fw-bold text-dark">{{ $stats['total_insurance'] }}</div>
                    <small class="text-danger">NHIF & Private Insurance</small>
                </div>
                <div class="metric-icon fs-2 text-danger opacity-75"><i class="bi bi-shield-plus"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- Main Address Book Navigation Tabs -->
<div class="card shadow-sm border-0 rounded-4">
    <div class="card-header bg-light p-2 rounded-top-4 border-bottom">
        <ul class="nav nav-pills nav-fill gap-2" id="contactTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active rounded-pill fw-semibold py-2" id="suppliers-tab" data-bs-toggle="tab" data-bs-target="#suppliers-pane" type="button" role="tab">
                    <i class="bi bi-truck me-1"></i> 1. Suppliers & Wholesalers <span class="badge bg-secondary ms-1">{{ count($suppliers) }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill fw-semibold py-2" id="patients-tab" data-bs-toggle="tab" data-bs-target="#patients-pane" type="button" role="tab">
                    <i class="bi bi-heart-pulse me-1"></i> 2. Patients & Customers <span class="badge bg-secondary ms-1">{{ count($patients) }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill fw-semibold py-2" id="staff-tab" data-bs-toggle="tab" data-bs-target="#staff-pane" type="button" role="tab">
                    <i class="bi bi-people me-1"></i> 3. Store Staff Directory <span class="badge bg-secondary ms-1">{{ count($staff) }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill fw-semibold py-2" id="insurance-tab" data-bs-toggle="tab" data-bs-target="#insurance-pane" type="button" role="tab">
                    <i class="bi bi-shield-check me-1"></i> 4. Insurance Providers <span class="badge bg-secondary ms-1">{{ count($insuranceProviders) }}</span>
                </button>
            </li>
        </ul>
    </div>
    <div class="card-body p-0">
        <div class="tab-content" id="contactTabsContent">
            
            <!-- TAB 1: SUPPLIERS -->
            <div class="tab-pane fade show active p-3" id="suppliers-pane" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-building me-1 text-primary"></i> Wholesale Distributors & Medicine Suppliers</h6>
                    <button class="btn btn-outline-primary btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
                        <i class="bi bi-plus-circle me-1"></i> Register New Supplier
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Company / Supplier Name</th>
                                <th>Contact Person</th>
                                <th>Phone & WhatsApp</th>
                                <th>Email</th>
                                <th>Location / Address</th>
                                <th class="text-end">Quick Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($suppliers as $s)
                            <tr>
                                <td class="fw-bold text-dark">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-primary-subtle text-primary p-2 text-center" style="width: 36px; height: 36px;">
                                            <i class="bi bi-truck"></i>
                                        </div>
                                        <div>
                                            {{ $s->name }}
                                            <small class="text-muted d-block font-monospace">ID: #SUP-{{ str_pad($s->id, 4, '0', STR_PAD_LEFT) }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $s->contact_person ?: 'Sales Representative / Agent' }}</td>
                                <td>
                                    @if(!empty($s->phone))
                                        <a href="tel:{{ $s->phone }}" class="text-decoration-none fw-semibold text-dark me-2">
                                            <i class="bi bi-telephone-fill text-success me-1"></i> {{ $s->phone }}
                                        </a>
                                        @php
                                            $cleanPhone = preg_replace('/[^0-9]/', '', $s->phone);
                                            if(str_starts_with($cleanPhone, '0')) $cleanPhone = '255' . substr($cleanPhone, 1);
                                        @endphp
                                        <a href="https://wa.me/{{ $cleanPhone }}?text={{ urlencode('Hello, I would like to place a medicine order from Dawafy Pharmacy...') }}" target="_blank" class="badge bg-success-subtle text-success border border-success-subtle text-decoration-none rounded-pill px-2 py-1">
                                            <i class="bi bi-whatsapp"></i> WhatsApp
                                        </a>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if(!empty($s->email))
                                        <a href="mailto:{{ $s->email }}" class="text-decoration-none text-muted small">
                                            <i class="bi bi-envelope me-1"></i> {{ $s->email }}
                                        </a>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td><small class="text-muted">{{ $s->address ?: 'Tanzania' }}</small></td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <a href="{{ route('procurement.create') }}?supplier_id={{ $s->id }}" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-1" title="Create Purchase Order (PO)">
                                            <i class="bi bi-cart-check me-1"></i> Order Medicines
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No suppliers registered yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 2: PATIENTS & CHRONIC / DEBT -->
            <div class="tab-pane fade p-3" id="patients-pane" role="tabpanel">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <div>
                        <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-person-heart me-1 text-danger"></i> Patient & Customer Directory (Chronic Care & Credit)</h6>
                        <small class="text-muted">Customer contact records for monthly prescription refills and credit/debt reminders via WhatsApp or SMS</small>
                    </div>
                    <div class="d-flex gap-1">
                        <a href="{{ route('contacts.index') }}" class="btn btn-sm {{ empty($filter) ? 'btn-dark' : 'btn-outline-secondary' }} rounded-pill px-3">All ({{ $stats['total_patients'] }})</a>
                        <a href="{{ route('contacts.index') }}?filter=chronic" class="btn btn-sm {{ $filter === 'chronic' ? 'btn-danger' : 'btn-outline-danger' }} rounded-pill px-3">
                            <i class="bi bi-heart-pulse me-1"></i> Chronic Care ({{ $stats['chronic_patients_count'] }})
                        </a>
                        <a href="{{ route('contacts.index') }}?filter=debt" class="btn btn-sm {{ $filter === 'debt' ? 'btn-warning text-dark' : 'btn-outline-warning text-dark' }} rounded-pill px-3">
                            <i class="bi bi-cash-stack me-1"></i> Credit Customers ({{ $stats['debtor_patients_count'] }})
                        </a>
                        <button class="btn btn-primary btn-sm rounded-pill px-3 ms-2" data-bs-toggle="modal" data-bs-target="#addPatientModal">
                            <i class="bi bi-person-plus me-1"></i> Register Patient
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Customer / Patient Name</th>
                                <th>Phone & Contact</th>
                                <th>Medical Condition (Chronic Care)</th>
                                <th>Credit / Debt Record</th>
                                <th>Address / Residence</th>
                                <th class="text-end">Notifications (WhatsApp / SMS)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($patients as $p)
                            <tr>
                                <td class="fw-bold text-dark">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-success-subtle text-success p-2 text-center" style="width: 36px; height: 36px;">
                                            <i class="bi bi-person"></i>
                                        </div>
                                        <div>
                                            {{ $p->name }}
                                            <small class="text-muted d-block">{{ $p->gender ? ucfirst($p->gender) : 'Customer' }} {{ $p->date_of_birth ? '• ' . $p->date_of_birth->age . ' yrs' : '' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if(!empty($p->phone))
                                        <a href="tel:{{ $p->phone }}" class="text-decoration-none fw-semibold text-dark">
                                            <i class="bi bi-telephone-fill text-primary me-1"></i> {{ $p->phone }}
                                        </a>
                                    @else
                                        <span class="text-muted small">No Phone</span>
                                    @endif
                                </td>
                                <td>
                                    @if(!empty($p->chronic_condition))
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1 fw-bold">
                                            <i class="bi bi-heart-pulse-fill me-1"></i> {{ $p->chronic_condition }}
                                        </span>
                                    @else
                                        <span class="badge bg-light text-secondary border rounded-pill px-2 py-1">General Customer</span>
                                    @endif
                                </td>
                                <td>
                                    @php $debt = (float)($p->outstanding_debt ?? 0); @endphp
                                    @if($debt > 0)
                                        <span class="badge bg-danger rounded-pill px-3 py-1 fw-bold fs-7">
                                            Outstanding: TZS {{ number_format($debt) }}
                                        </span>
                                    @else
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1">
                                            Zero Balance (0 TZS)
                                        </span>
                                    @endif
                                </td>
                                <td><small class="text-muted">{{ $p->address ?: 'Standard Residence' }}</small></td>
                                <td class="text-end">
                                    @if(!empty($p->phone))
                                        @php
                                            $cleanPhone = preg_replace('/[^0-9]/', '', $p->phone);
                                            if(str_starts_with($cleanPhone, '0')) $cleanPhone = '255' . substr($cleanPhone, 1);
                                            
                                            // Construct custom contextual reminder message
                                            if ($debt > 0) {
                                                $waMsg = "Hello {$p->name}, this is a gentle reminder regarding your outstanding credit balance of TZS " . number_format($debt) . " at Dawafy Pharmacy. Please visit or make payment via our pharmacy account. Thank you!";
                                            } elseif(!empty($p->chronic_condition)) {
                                                $waMsg = "Hello {$p->name}, your health matters to us. This is a reminder regarding your monthly prescription refill for {$p->chronic_condition}. Your medicines are ready at Dawafy Pharmacy. Welcome!";
                                            } else {
                                                $waMsg = "Hello {$p->name}, thank you for being a valued customer of Dawafy Pharmacy. Wishing you good health!";
                                            }
                                        @endphp
                                        <div class="d-inline-flex gap-1">
                                            <a href="https://wa.me/{{ $cleanPhone }}?text={{ urlencode($waMsg) }}" target="_blank" class="btn btn-sm btn-success rounded-pill px-3 py-1" title="Send WhatsApp Reminder">
                                                <i class="bi bi-whatsapp me-1"></i> WhatsApp
                                            </a>
                                            <a href="sms:{{ $p->phone }}?body={{ urlencode($waMsg) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-1" title="Send SMS">
                                                <i class="bi bi-chat-dots"></i>
                                            </a>
                                        </div>
                                    @else
                                        <span class="text-muted small">Add phone to notify</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No patients match this filter criteria.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 3: STAFF CONTACTS -->
            <div class="tab-pane fade p-3" id="staff-pane" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-people-fill me-1 text-primary"></i> Store Staff & Access Roles (User Roles & Permissions)</h6>
                        <small class="text-muted">Contact directory for pharmacists, cashiers, and store managers</small>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#addStaffModal">
                            <i class="bi bi-person-plus-fill me-1"></i> Register Staff
                        </button>
                        <a href="{{ route('users.index') }}" class="btn btn-outline-primary btn-sm rounded-pill">
                            <i class="bi bi-gear me-1"></i> Manage Users & Permissions
                        </a>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Full Name</th>
                                <th>Username</th>
                                <th>Role & Permissions</th>
                                <th>Staff Phone</th>
                                <th>Device / Session Status</th>
                                <th class="text-end">Quick Contact</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($staff as $u)
                            <tr>
                                <td class="fw-bold text-dark">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-secondary-subtle text-secondary p-2 text-center" style="width: 36px; height: 36px;">
                                            <i class="bi bi-person"></i>
                                        </div>
                                        <div>
                                            {{ $u['full_name'] ?: 'Staff Member' }}
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge bg-light text-dark border font-monospace">{{ $u['username'] }}</span></td>
                                <td>
                                    @if($u['role'] === 'Manager')
                                        <span class="badge bg-primary px-3 py-1"><i class="bi bi-shield-check me-1"></i> Manager (Full Store Operations)</span>
                                    @elseif($u['role'] === 'Pharmacist')
                                        <span class="badge bg-success px-3 py-1"><i class="bi bi-capsule me-1"></i> Pharmacist (POS & Inventory)</span>
                                    @elseif($u['role'] === 'Cashier')
                                        <span class="badge bg-info text-dark px-3 py-1"><i class="bi bi-cart me-1"></i> Cashier (POS Checkout Only)</span>
                                    @else
                                        <span class="badge bg-secondary px-2 py-1">{{ $u['role'] }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if(!empty($u['phone']))
                                        <a href="tel:{{ $u['phone'] }}" class="text-decoration-none fw-semibold text-dark">
                                            <i class="bi bi-telephone-fill text-success me-1"></i> {{ $u['phone'] }}
                                        </a>
                                    @else
                                        <span class="text-muted small">No Phone</span>
                                    @endif
                                </td>
                                <td>
                                    @if(!empty($u['is_online']))
                                        <span class="badge bg-success rounded-pill px-2 py-1"><i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i> Active Online</span>
                                    @else
                                        <span class="badge bg-light text-muted border rounded-pill px-2 py-1">Offline</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if(!empty($u['phone']))
                                        @php
                                            $cleanStaffPhone = preg_replace('/[^0-9]/', '', $u['phone']);
                                            if(str_starts_with($cleanStaffPhone, '0')) $cleanStaffPhone = '255' . substr($cleanStaffPhone, 1);
                                        @endphp
                                        <a href="https://wa.me/{{ $cleanStaffPhone }}" target="_blank" class="btn btn-sm btn-outline-success rounded-pill px-3 py-0">
                                            <i class="bi bi-whatsapp me-1"></i> Message
                                        </a>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 4: INSURANCE PROVIDERS -->
            <div class="tab-pane fade p-3" id="insurance-pane" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-shield-plus me-1 text-danger"></i> Insurance Providers Directory (NHIF & Private Insurers)</h6>
                        <small class="text-muted">Claims desk contact records and pending claims tracking</small>
                    </div>
                    <button class="btn btn-outline-danger btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#addInsuranceModal">
                        <i class="bi bi-plus-circle me-1"></i> Register Insurance Provider
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Insurance Fund / Company</th>
                                <th>Claims Officer (Contact Person)</th>
                                <th>Phone & Email</th>
                                <th>Pending Claims</th>
                                <th>Amount Receivable</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($insuranceProviders as $prov)
                            <tr>
                                <td class="fw-bold text-dark">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-danger-subtle text-danger p-2 text-center" style="width: 36px; height: 36px;">
                                            <i class="bi bi-shield-check"></i>
                                        </div>
                                        <div>
                                            {{ $prov['name'] }}
                                            <small class="text-muted d-block">{{ $prov['address'] ?: 'Headquarters' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $prov['contact_person'] ?: 'Claims Officer' }}</td>
                                <td>
                                    @if(!empty($prov['phone']))
                                        <a href="tel:{{ $prov['phone'] }}" class="text-decoration-none fw-semibold text-dark d-block">
                                            <i class="bi bi-telephone-fill text-primary me-1"></i> {{ $prov['phone'] }}
                                        </a>
                                    @endif
                                    @if(!empty($prov['email']))
                                        <a href="mailto:{{ $prov['email'] }}" class="text-decoration-none text-muted small">
                                            <i class="bi bi-envelope me-1"></i> {{ $prov['email'] }}
                                        </a>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $prov['pending_claims_count'] > 0 ? 'bg-warning text-dark' : 'bg-success' }} rounded-pill px-3 py-1 fw-bold">
                                        {{ $prov['pending_claims_count'] }} Claims Pending
                                    </span>
                                </td>
                                <td>
                                    <strong class="text-danger fs-6">TZS {{ number_format($prov['pending_amount']) }}</strong>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('insurance.index') }}" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1">
                                        <i class="bi bi-eye me-1"></i> View Claims
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No insurance providers registered yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal: New Patient with Chronic Condition & Debt -->
<div class="modal fade" id="addPatientModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4 border-0 shadow">
      <div class="modal-header bg-light text-dark py-2 px-3 rounded-top-4">
        <h6 class="modal-title fw-semibold"><i class="bi bi-person-heart me-1 text-danger"></i> Register New Patient / Customer</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="{{ route('patients.store') }}">
        @csrf
        <div class="modal-body p-4">
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Full Customer / Patient Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control rounded-pill px-3" placeholder="e.g. John Doe" required>
            </div>
            <div class="row g-2 mb-3">
                <div class="col-md-6">
                    <label class="fw-bold text-muted small mb-1">Phone Number (SMS / WhatsApp)</label>
                    <input type="text" name="phone" class="form-control rounded-pill px-3" placeholder="0712345678">
                </div>
                <div class="col-md-6">
                    <label class="fw-bold text-muted small mb-1">Gender</label>
                    <select name="gender" class="form-select rounded-pill px-3">
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Chronic Medical Condition</label>
                <input type="text" name="chronic_condition" class="form-control rounded-pill px-3" placeholder="e.g. Diabetes, Hypertension, Asthma">
                <small class="text-muted">Used for scheduling automated monthly prescription refill reminders.</small>
            </div>
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Initial Outstanding Debt / Credit (TZS)</label>
                <input type="number" step="0.01" name="outstanding_debt" class="form-control rounded-pill px-3" value="0">
                <small class="text-muted">Balance owed if medication was dispensed on credit.</small>
            </div>
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Residential Address / Location</label>
                <input type="text" name="address" class="form-control rounded-pill px-3" placeholder="e.g. Mbezi Beach, House No. 14">
            </div>
        </div>
        <div class="modal-footer border-0 bg-light rounded-bottom-4">
            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Save Patient</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal: New Supplier -->
<div class="modal fade" id="addSupplierModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4 border-0 shadow">
      <div class="modal-header bg-light text-dark py-2 px-3 rounded-top-4">
        <h6 class="modal-title fw-semibold"><i class="bi bi-truck me-1 text-primary"></i> Register Supplier / Wholesaler</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="{{ route('suppliers.store') }}">
        @csrf
        <div class="modal-body p-4">
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Supplier Company Name / Wholesaler <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control rounded-pill px-3" placeholder="e.g. Medics Wholesalers Ltd" required>
            </div>
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Contact Person (Sales Rep / Agent)</label>
                <input type="text" name="contact_person" class="form-control rounded-pill px-3" placeholder="e.g. Sales Manager / Representative">
            </div>
            <div class="row g-2 mb-3">
                <div class="col-md-6">
                    <label class="fw-bold text-muted small mb-1">Phone Number / WhatsApp <span class="text-danger">*</span></label>
                    <input type="text" name="phone" class="form-control rounded-pill px-3" placeholder="0755123456" required>
                </div>
                <div class="col-md-6">
                    <label class="fw-bold text-muted small mb-1">Email Address</label>
                    <input type="email" name="email" class="form-control rounded-pill px-3" placeholder="sales@supplier.co.tz">
                </div>
            </div>
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Office / Store Location</label>
                <input type="text" name="address" class="form-control rounded-pill px-3" placeholder="e.g. Kariakoo, Dar es Salaam">
            </div>
        </div>
        <div class="modal-footer border-0 bg-light rounded-bottom-4">
            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Save Supplier</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal: New Insurance Provider -->
<div class="modal fade" id="addInsuranceModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4 border-0 shadow">
      <div class="modal-header bg-light text-dark py-2 px-3 rounded-top-4">
        <h6 class="modal-title fw-semibold"><i class="bi bi-shield-plus me-1 text-danger"></i> Register Insurance Provider</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="{{ route('insurance.store_provider') }}">
        @csrf
        <div class="modal-body p-4">
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Insurance Fund / Company Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control rounded-pill px-3" placeholder="e.g. NHIF, Jubilee Insurance, Strategis" required>
            </div>
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Claims Officer (Contact Person)</label>
                <input type="text" name="contact_person" class="form-control rounded-pill px-3" placeholder="e.g. Pharmacy Claims Desk">
            </div>
            <div class="row g-2 mb-3">
                <div class="col-md-6">
                    <label class="fw-bold text-muted small mb-1">Claims Desk Phone</label>
                    <input type="text" name="phone" class="form-control rounded-pill px-3" placeholder="0222123456">
                </div>
                <div class="col-md-6">
                    <label class="fw-bold text-muted small mb-1">Claims Email</label>
                    <input type="email" name="email" class="form-control rounded-pill px-3" placeholder="claims@insurance.co.tz">
                </div>
            </div>
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Office Address</label>
                <input type="text" name="address" class="form-control rounded-pill px-3" placeholder="e.g. CBD, Dar es Salaam">
            </div>
        </div>
        <div class="modal-footer border-0 bg-light rounded-bottom-4">
            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">Save Insurance Provider</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal: Register Store Staff -->
<div class="modal fade" id="addStaffModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4 border-0 shadow">
      <div class="modal-header bg-light text-dark py-2 px-3 rounded-top-4">
        <h6 class="modal-title fw-semibold"><i class="bi bi-person-plus-fill me-1 text-primary"></i> Register New Staff Member</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="{{ route('users.store') }}">
        @csrf
        <div class="modal-body p-4">
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Full Name <span class="text-danger">*</span></label>
                <input type="text" name="full_name" class="form-control rounded-pill px-3" placeholder="e.g. John Doe" required>
            </div>
            <div class="row g-2 mb-3">
                <div class="col-md-6">
                    <label class="fw-bold text-muted small mb-1">Username (Login ID) <span class="text-danger">*</span></label>
                    <input type="text" name="username" class="form-control rounded-pill px-3" placeholder="e.g. jdoe" required>
                </div>
                <div class="col-md-6">
                    <label class="fw-bold text-muted small mb-1">Staff Phone Number <span class="text-danger">*</span></label>
                    <input type="text" name="phone" class="form-control rounded-pill px-3" placeholder="e.g. 0755123456" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Role & System Permissions <span class="text-danger">*</span></label>
                <select name="role" class="form-select rounded-pill px-3" required>
                    <option value="" disabled selected>Select Role</option>
                    <option value="Manager">Manager (Full Operational Control & Reports)</option>
                    <option value="Pharmacist">Pharmacist (POS Sales, Inventory & Dispensing)</option>
                    <option value="Cashier">Cashier (Point of Sale Checkout Only)</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Temporary Password <span class="text-danger">*</span></label>
                <input type="password" name="password" class="form-control rounded-pill px-3" placeholder="Min. 4 characters" minlength="4" required>
                <small class="text-muted">Staff member will use this password to log in.</small>
            </div>
        </div>
        <div class="modal-footer border-0 bg-light rounded-bottom-4">
            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Register Staff</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
