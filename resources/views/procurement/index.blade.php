@extends('layouts.app')

@section('content')
<div class="laravel-hero-banner mb-4 d-flex justify-content-between align-items-center animate-fade-in">
    <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-cart-check me-2 text-info"></i> Drug Procurement & Purchase Orders</h2>
        <p class="text-white-50 mb-0">Create, monitor, and approve purchase orders from pharmaceutical distributors.</p>
    </div>
    <div>
        <a href="{{ route('procurement.create') }}" class="btn-laravel-green text-white shadow text-decoration-none">
            <i class="bi bi-plus-circle me-1"></i> + New Purchase Order
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success rounded-pill px-4 mb-4 animate-fade-in">
        <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
    </div>
@endif

<div class="row g-3 mb-4 animate-fade-in">
    <div class="col-md-4">
        <div class="metric-widget metric-blue">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="metric-title">Total Orders</div>
                    <div class="metric-value text-primary">{{ count($purchaseOrders) }}</div>
                </div>
                <div class="metric-icon"><i class="bi bi-file-earmark-text"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="metric-widget metric-yellow">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="metric-title">Pending Orders</div>
                    <div class="metric-value text-warning">{{ $purchaseOrders->where('status', 'Pending')->count() }}</div>
                </div>
                <div class="metric-icon"><i class="bi bi-hourglass-split"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="metric-widget metric-green">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="metric-title">Received Orders</div>
                    <div class="metric-value text-success">{{ $purchaseOrders->where('status', 'Received')->count() }}</div>
                </div>
                <div class="metric-icon"><i class="bi bi-check-circle-fill"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="card-laravel animate-fade-in">
    <div class="card-header bg-white border-bottom">
        <h5 class="mb-0 text-primary"><i class="bi bi-list-stars me-2"></i> Purchase Order Records</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-laravel mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>PO #</th>
                        <th>Supplier</th>
                        <th>Status</th>
                        <th>Created By</th>
                        <th>Order Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchaseOrders as $idx => $po)
                        <tr>
                            <td class="text-muted">{{ $idx + 1 }}</td>
                            <td><strong class="text-dark font-monospace">PO-{{ str_pad($po->id, 5, '0', STR_PAD_LEFT) }}</strong></td>
                            <td>{{ $po->supplier?->name ?? 'General Supplier' }}</td>
                            <td>
                                @if($po->status === 'Received')
                                    <span class="badge-laravel-green"><i class="bi bi-check-circle me-1"></i> Received</span>
                                @elseif($po->status === 'Cancelled')
                                    <span class="badge-laravel-danger">Cancelled</span>
                                @else
                                    <span class="badge-laravel-yellow"><i class="bi bi-clock me-1"></i> Pending</span>
                                @endif
                            </td>
                            <td><small class="text-muted">{{ $po->createdBy?->name ?? 'Staff' }}</small></td>
                            <td><small class="text-muted">{{ $po->created_at ? $po->created_at->format('d M Y') : 'N/A' }}</small></td>
                            <td class="text-end">
                                @if($po->status === 'Pending')
                                    <form action="{{ route('procurement.receive', $po->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-laravel-green text-white rounded-pill px-3 shadow-sm">
                                            <i class="bi bi-check-lg me-1"></i> Receive
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted small"><i class="bi bi-check2-all text-success me-1"></i> Completed</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No purchase orders found. Click "+ New Purchase Order" to create one.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
