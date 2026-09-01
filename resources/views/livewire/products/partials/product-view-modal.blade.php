@if($viewProduct)
    <div x-data="{
            closing: false,
            close() {
                if (this.closing) return;
                this.closing = true;
                $wire.closeViewModal();
            }
         }"
         @keydown.escape.window.stop="close()"
         @click.self.stop="close()"
         class="modal fade show d-block erp-modal-backdrop" 
         style="background: rgba(0,0,0,0.55); z-index: 1055;" 
         tabindex="-1" 
         role="dialog" 
         aria-modal="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable erp-modal-dialog" @click.stop>
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-light py-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary font-size-12">{{ $viewProduct->product_code }}</span>
                        <h5 class="modal-title mb-0 text-dark fw-bold">{{ $viewProduct->name }}</h5>
                    </div>
                    <button type="button" class="btn-close" @click.stop="close()" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-4 mb-4">
                        <!-- Left: Product Image -->
                        <div class="col-md-4 text-center">
                            <div class="p-2 border rounded bg-light d-flex align-items-center justify-content-center" style="min-height: 200px; height: 100%;">
                                @if($viewProduct->image)
                                    <img src="{{ asset('storage/' . $viewProduct->image) }}" alt="{{ $viewProduct->name }}" class="img-fluid rounded object-fit-contain" style="max-height: 200px; max-width: 100%;">
                                @else
                                    <div class="text-muted py-4">
                                        <i class="bx bx-image font-size-48 d-block mb-1"></i>
                                        <span class="font-size-12">No Picture Uploaded</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Right: Basic Specs & Status -->
                        <div class="col-md-8">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <span class="badge {{ $viewProduct->status ? 'badge-soft-success' : 'badge-soft-secondary' }} font-size-12">
                                        {{ $viewProduct->status ? 'Active in Catalog' : 'Inactive' }}
                                    </span>
                                    @if($viewProduct->current_stock <= 0)
                                        <span class="badge badge-soft-danger font-size-12 ms-1">Out of Stock</span>
                                    @elseif($viewProduct->current_stock <= $viewProduct->min_stock)
                                        <span class="badge badge-soft-warning font-size-12 ms-1">Low Stock Alert</span>
                                    @else
                                        <span class="badge badge-soft-success font-size-12 ms-1">In Stock</span>
                                    @endif
                                </div>
                                <span class="text-muted font-size-12">Registered: {{ $viewProduct->created_at ? $viewProduct->created_at->format('d M Y') : '-' }}</span>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-sm table-borderless mb-0 font-size-13">
                                    <tbody>
                                        <tr>
                                            <th class="text-muted ps-0" style="width: 130px;">Category:</th>
                                            <td class="fw-semibold text-dark">{{ $viewProduct->category->name ?? 'Uncategorized' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="text-muted ps-0">Brand / Maker:</th>
                                            <td class="fw-semibold text-dark">{{ $viewProduct->brand ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="text-muted ps-0">Barcode:</th>
                                            <td><code>{{ $viewProduct->barcode ?? '-' }}</code></td>
                                        </tr>
                                        <tr>
                                            <th class="text-muted ps-0">Unit of Measure:</th>
                                            <td><span class="badge badge-soft-secondary">{{ $viewProduct->unit->name ?? 'Default Unit' }}</span></td>
                                        </tr>
                                        <tr>
                                            <th class="text-muted ps-0">Warehouse:</th>
                                            <td class="text-dark">{{ $viewProduct->warehouse ?? 'Main Warehouse' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Financial & Stock Summary Cards -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-3 col-6">
                            <div class="p-3 bg-light rounded border text-center">
                                <span class="text-muted font-size-12 d-block">Cost Price</span>
                                <h5 class="mb-0 text-dark fw-bold font-monospace">AED {{ number_format($viewProduct->purchase_price, 2) }}</h5>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="p-3 bg-light rounded border text-center">
                                <span class="text-muted font-size-12 d-block">Retail Price</span>
                                <h5 class="mb-0 text-success fw-bold font-monospace">AED {{ number_format($viewProduct->sales_price, 2) }}</h5>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="p-3 bg-light rounded border text-center">
                                <span class="text-muted font-size-12 d-block">VAT Rate</span>
                                <h5 class="mb-0 text-info fw-bold font-monospace">{{ number_format($viewProduct->tax_percent, 1) }}%</h5>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="p-3 bg-light rounded border text-center">
                                <span class="text-muted font-size-12 d-block">Gross Margin</span>
                                @php
                                    $margin = $viewProduct->sales_price > 0 ? (($viewProduct->sales_price - $viewProduct->purchase_price) / $viewProduct->sales_price) * 100 : 0;
                                @endphp
                                <h5 class="mb-0 text-primary fw-bold font-monospace">{{ number_format($margin, 1) }}%</h5>
                            </div>
                        </div>
                    </div>

                    <!-- Stock Level Row -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded border">
                                <span class="text-muted font-size-12 d-block">Current Stock on Hand</span>
                                <h4 class="mb-0 text-dark fw-bold font-monospace mt-1">
                                    {{ number_format($viewProduct->current_stock, 2) }} <small class="font-size-13 text-muted">{{ $viewProduct->unit->name ?? '' }}</small>
                                </h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded border">
                                <span class="text-muted font-size-12 d-block">Min Stock Threshold</span>
                                <h4 class="mb-0 text-muted fw-bold font-monospace mt-1">
                                    {{ number_format($viewProduct->min_stock, 2) }} <small class="font-size-13 text-muted">{{ $viewProduct->unit->name ?? '' }}</small>
                                </h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded border">
                                <span class="text-muted font-size-12 d-block">Total Asset Valuation</span>
                                <h4 class="mb-0 text-primary fw-bold font-monospace mt-1">
                                    AED {{ number_format($viewProduct->current_stock * $viewProduct->weighted_cost, 2) }}
                                </h4>
                            </div>
                        </div>
                    </div>

                    <!-- Description Section -->
                    @if($viewProduct->description)
                        <div class="mb-4">
                            <label class="form-label text-muted font-size-12 fw-bold text-uppercase">Description / Specifications</label>
                            <div class="p-3 bg-light rounded border font-size-13 text-dark">
                                {{ $viewProduct->description }}
                            </div>
                        </div>
                    @endif

                    <!-- Recent Stock Activity -->
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label text-muted font-size-12 fw-bold text-uppercase mb-0">Recent Stock Activity (Last 6 Transactions)</label>
                            <a href="{{ route('products.stock') }}?product_id={{ $viewProduct->id }}" class="btn btn-sm btn-link p-0 font-size-12">View Full Ledger</a>
                        </div>
                        <div class="table-responsive border rounded">
                            <table class="table table-sm table-hover mb-0 font-size-12">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Movement</th>
                                        <th class="text-end">Qty In</th>
                                        <th class="text-end">Qty Out</th>
                                        <th class="text-end">Unit Cost</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($viewProduct->stockMovements as $mov)
                                        <tr>
                                            <td>{{ $mov->date }}</td>
                                            <td>
                                                <span class="badge badge-soft-info">{{ $mov->movement_type }}</span>
                                            </td>
                                            <td class="text-end text-success font-monospace">{{ $mov->quantity_in > 0 ? '+' . number_format($mov->quantity_in, 2) : '-' }}</td>
                                            <td class="text-end text-danger font-monospace">{{ $mov->quantity_out > 0 ? '-' . number_format($mov->quantity_out, 2) : '-' }}</td>
                                            <td class="text-end font-monospace text-muted">AED {{ number_format($mov->unit_cost, 2) }}</td>
                                            <td class="text-muted">{{ $mov->notes ?? '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-3 text-muted">No stock movements recorded for this product yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 d-flex flex-column-reverse flex-sm-row justify-content-sm-end gap-2">
                    <button type="button" class="btn btn-secondary w-100 w-sm-auto" @click.stop="close()">Close</button>
                    <button type="button" wire:click="editProduct({{ $viewProduct->id }})" class="btn btn-primary w-100 w-sm-auto">
                        <i class="bx bx-edit me-1"></i> Edit Product
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
