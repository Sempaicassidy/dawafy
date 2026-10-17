@extends('layouts.app')

@section('content')
<div class="laravel-hero-banner mb-4 d-flex justify-content-between align-items-center animate-fade-in">
    <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-file-earmark-text me-2 text-info"></i> Purchase Order Details: {{ $purchaseOrder->po_number ?? 'PO-#' . $purchaseOrder->id }}</h2>
        <p class="text-white-50 mb-0">Supplier: {{ $purchaseOrder->supplier?->name ?? 'Distributor' }} | Status: <span class="badge bg-warning">{{ $purchaseOrder->status ?? 'Pending' }}</span></p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('procurement.index') }}" class="btn btn-outline-light rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i> Back to Orders
        </a>
        @if(($purchaseOrder->status ?? 'Pending') === 'Pending')
            <form action="{{ route('procurement.receive', $purchaseOrder) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success rounded-pill px-4" onclick="return confirm('Confirm receipt of this order into inventory?')">
                    <i class="bi bi-box-seam me-1"></i> Receive Order
                </button>
            </form>
        @endif
    </div>
</div>

<div class="row g-4 animate-fade-in">
    <div class="col-md-4">
        <div class="card-laravel mb-4">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-bold text-primary"><i class="bi bi-truck me-2"></i> Supplier Information</h6>
            </div>
            <div class="card-body">
                <h5 class="fw-bold text-dark">{{ $purchaseOrder->supplier?->name ?? 'N/A' }}</h5>
                <p class="mb-1 text-muted small"><i class="bi bi-person me-2"></i> {{ $purchaseOrder->supplier?->contact_person ?? 'Not specified' }}</p>
                <p class="mb-1 text-muted small"><i class="bi bi-telephone me-2"></i> {{ $purchaseOrder->supplier?->phone ?? 'N/A' }}</p>
                <p class="mb-1 text-muted small"><i class="bi bi-envelope me-2"></i> {{ $purchaseOrder->supplier?->email ?? 'N/A' }}</p>
                <p class="mb-0 text-muted small"><i class="bi bi-geo-alt me-2"></i> {{ $purchaseOrder->supplier?->address ?? 'N/A' }}</p>
            </div>
        </div>

        <div class="card-laravel">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-bold text-primary"><i class="bi bi-info-circle me-2"></i> Order Summary</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Order Date:</span>
                    <strong>{{ $purchaseOrder->order_date ? date('d M Y', strtotime($purchaseOrder->order_date)) : date('d M Y', strtotime($purchaseOrder->created_at)) }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Expected Delivery:</span>
                    <strong>{{ $purchaseOrder->expected_delivery_date ? date('d M Y', strtotime($purchaseOrder->expected_delivery_date)) : 'Not scheduled' }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Created By:</span>
                    <strong>{{ $purchaseOrder->createdBy?->name ?? $purchaseOrder->createdBy?->username ?? 'Authorized Staff' }}</strong>
                </div>
                <hr>
                <div class="d-flex justify-content-between mb-0">
                    <span class="fw-bold fs-6">Total Amount:</span>
                    <strong class="text-success fs-5">TZS {{ number_format($purchaseOrder->total_amount, 2) }}</strong>
                </div>
                @if($purchaseOrder->notes)
                    <div class="mt-3 p-2 bg-light rounded small">
                        <strong>Notes:</strong> {{ $purchaseOrder->notes }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card-laravel">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-bold text-primary"><i class="bi bi-capsule me-2"></i> Ordered Line Items</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-laravel mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Medicine Item</th>
                                <th>Quantity</th>
                                <th>Unit Price (TZS)</th>
                                <th>Subtotal (TZS)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($purchaseOrder->items as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <strong>{{ $item->drug?->name ?? 'Pharmaceutical Item' }}</strong>
                                        @if($item->drug?->strength)
                                            <span class="badge bg-light text-dark ms-1">{{ $item->drug->strength }}</span>
                                        @endif
                                    </td>
                                    <td><span class="badge bg-primary bg-opacity-10 text-primary px-3 py-1">{{ $item->quantity }}</span></td>
                                    <td>{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="fw-bold">{{ number_format($item->subtotal, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No items recorded for this purchase order.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
