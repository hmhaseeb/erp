<x-modal 
    :isOpen="$isModalOpen" 
    :title="$isEditMode ? 'Edit Product Item' : 'Register New Product'"
    size="modal-lg modal-dialog-centered modal-dialog-scrollable"
    submitAction="saveProduct"
    :isEditMode="$isEditMode"
    saveText="Save Product"
    updateText="Update Product">

    <!-- 1. Basic Information -->
    <div class="form-section-title">1. Basic Information</div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Product Code / SKU <span class="text-danger">*</span></label>
            <input type="text" wire:model="product_code" class="form-control @error('product_code') is-invalid @enderror" placeholder="e.g. PROD-00001">
            @error('product_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Barcode (Optional)</label>
            <input type="text" wire:model="barcode" class="form-control @error('barcode') is-invalid @enderror" placeholder="Scan or enter barcode...">
            @error('barcode') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 mb-3">
            <label class="form-label">Product Name <span class="text-danger">*</span></label>
            <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror" placeholder="Enter product title or description">
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Brand / Manufacturer</label>
            <input type="text" wire:model="brand" class="form-control" placeholder="e.g. Apple, Dell, HP">
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Category</label>
            <x-searchable-select wire:model="category_id" class="form-select @error('category_id') is-invalid @enderror" placeholder="Select Category">
                <option value="">Select Category</option>
                @foreach($categories as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
            </x-searchable-select>
            @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Unit of Measure</label>
            <x-searchable-select wire:model="unit_id" class="form-select @error('unit_id') is-invalid @enderror" placeholder="Select Unit">
                <option value="">Select Unit</option>
                @foreach($units as $u)
                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                @endforeach
            </x-searchable-select>
            @error('unit_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <!-- 2. Pricing & VAT -->
    <div class="form-section-title mt-2">2. Pricing & VAT Details</div>
    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label">Purchase / Cost Price (AED) <span class="text-danger">*</span></label>
            <input type="number" step="0.01" wire:model="purchase_price" class="form-control @error('purchase_price') is-invalid @enderror">
            @error('purchase_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Selling Price (AED) <span class="text-danger">*</span></label>
            <input type="number" step="0.01" wire:model="sales_price" class="form-control @error('sales_price') is-invalid @enderror">
            @error('sales_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">VAT Tax %</label>
            <input type="number" step="0.01" wire:model="tax_percent" class="form-control @error('tax_percent') is-invalid @enderror">
            @error('tax_percent') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <!-- 3. Inventory & Warehouse -->
    <div class="form-section-title mt-2">3. Inventory & Warehouse</div>
    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label">Min Stock Alert Level</label>
            <input type="number" step="1" wire:model="min_stock" class="form-control @error('min_stock') is-invalid @enderror">
            @error('min_stock') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        @if(!$isEditMode)
            <div class="col-md-4 mb-3">
                <label class="form-label">Opening Stock Quantity</label>
                <input type="number" step="1" wire:model="opening_stock" class="form-control @error('opening_stock') is-invalid @enderror">
                @error('opening_stock') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        @endif
        <div class="{{ $isEditMode ? 'col-md-8' : 'col-md-4' }} mb-3">
            <label class="form-label">Warehouse Location</label>
            <input type="text" wire:model="warehouse" class="form-control">
        </div>
    </div>

    <!-- 4. Product Image & Presentation -->
    <div class="form-section-title mt-2">4. Product Image & Presentation</div>
    <div class="row">
        <div class="col-md-12 mb-3">
            <label class="form-label">Product Image</label>
            <div class="p-3 bg-light rounded border d-flex align-items-center gap-3">
                <div>
                    @if ($image)
                        <img src="{{ $image->temporaryUrl() }}" alt="Preview" class="rounded border shadow-sm object-fit-cover" style="width: 76px; height: 76px;">
                    @elseif ($existingImage)
                        <img src="{{ asset('storage/' . $existingImage) }}" alt="Existing Product" class="rounded border shadow-sm object-fit-cover" style="width: 76px; height: 76px;">
                    @else
                        <div class="rounded border bg-white text-muted d-flex align-items-center justify-content-center shadow-sm" style="width: 76px; height: 76px;">
                            <i class="bx bx-image font-size-32"></i>
                        </div>
                    @endif
                </div>
                <div class="flex-grow-1">
                    <input type="file" wire:model="image" accept="image/*" class="form-control form-control-sm @error('image') is-invalid @enderror">
                    <div wire:loading wire:target="image" class="text-primary font-size-12 mt-1">
                        <i class="bx bx-loader-alt bx-spin me-1"></i> Uploading & previewing image...
                    </div>
                    @error('image') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    <div class="d-flex justify-content-between align-items-center mt-1">
                        <small class="text-muted font-size-11">Formats: JPG, PNG, WEBP, SVG (Max: 2MB)</small>
                        @if($image || $existingImage)
                            <button type="button" wire:click="removeImage" class="btn btn-sm btn-link text-danger p-0 font-size-12">
                                <i class="bx bx-trash me-1"></i> Remove Image
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-2">
        <label class="form-label">Description / Notes</label>
        <textarea wire:model="description" class="form-control" rows="2" placeholder="Optional product specifications, model numbers, or notes..."></textarea>
    </div>
</x-modal>
