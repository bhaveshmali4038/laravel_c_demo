@extends('layouts.app')
@section('title', $product->exists ? 'Edit product' : 'Add product')
@section('content')
@php($editing = $product->exists)
<a class="back-link" href="{{ route('products.index') }}"><i class="bi bi-arrow-left"></i> Back to inventory</a>
<div class="page-heading"><div><div class="eyebrow">{{ $editing ? 'A FEW FINISHING TOUCHES' : 'MAKE ROOM FOR SOMETHING NEW' }}</div><h1>{{ $editing ? 'Edit product' : 'Add a product' }}</h1><p class="text-secondary">The details make all the difference. Fields marked * are required.</p></div></div>
<form action="{{ $editing ? route('products.update', $product) : route('products.store') }}" method="POST" enctype="multipart/form-data" class="js-lock-submit">
    @csrf @if ($editing) @method('PUT') @endif
    <div class="row g-4"><div class="col-xl-8"><section class="panel"><div class="section-body">
        <h2 class="section-heading"><i class="bi bi-box-seam me-2 text-secondary"></i>Product information</h2>
        <div class="row"><div class="col-md-7"><x-input name="name" label="Product name" :value="$product->name" required minlength="3" maxlength="150" placeholder="e.g. Ceramic coffee mug" /></div><div class="col-md-5"><x-input name="sku" label="SKU" :value="$product->sku" required maxlength="24" placeholder="PRD-MUG001" /></div></div>
        <div class="mb-4"><label for="description" class="form-label">Description</label><textarea name="description" id="description" rows="5" maxlength="5000" class="form-control @error('description') is-invalid @enderror" placeholder="What makes this product special?">{{ old('description', $product->description) }}</textarea>@error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror<div class="form-help">Optional. Up to 5,000 characters.</div></div>
        <h2 class="section-heading mt-4">Pricing & inventory</h2>
        <div class="row"><div class="col-md-4"><x-input name="price" label="Regular price (₹)" type="number" :value="$product->price" required step="0.01" min="0.01" max="9999999.99" placeholder="0.00" /></div><div class="col-md-4"><x-input name="sale_price" label="Sale price (₹)" type="number" :value="$product->sale_price" step="0.01" min="0" max="9999999.98" placeholder="Optional" /></div><div class="col-md-4"><x-input name="stock" label="Stock quantity" type="number" :value="$product->stock" required step="1" min="0" max="1000000" /></div></div>
        <div class="row"><div class="col-md-6"><div class="mb-3"><label for="status" class="form-label">Status *</label><select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>@foreach (['draft','active','archived'] as $status)<option value="{{ $status }}" @selected(old('status', $product->status) === $status)>{{ ucfirst($status) }}</option>@endforeach</select>@error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror</div></div><div class="col-md-6"><x-input name="published_at" label="Publication date" type="date" :value="$product->published_at?->format('Y-m-d')" :max="now()->toDateString()" /></div></div>
        <div class="upload-tip"><i class="bi bi-info-circle me-1"></i> Active products need at least one unit in stock and a publication date that is today or earlier. Sale prices must be lower than regular prices.</div>
    </div></section></div><div class="col-xl-4"><section class="panel"><div class="section-body">
        <h2 class="section-heading">A picture of your product</h2>
        <div class="mb-4"><img id="image-preview" @if($product->image_url) src="{{ $product->image_url }}" @endif alt="Product image preview" class="image-preview {{ $product->image_url ? '' : 'd-none' }}"><div id="image-placeholder" class="image-placeholder {{ $product->image_url ? 'd-none' : '' }}"><i class="bi bi-image"></i><span>A little visual goes a long way.</span></div></div>
        <x-input name="image" label="Upload image" type="file" accept=".jpg,.jpeg,.png,.webp" :required="!$editing" />
        <p class="form-help">JPG, PNG, or WebP · Up to 2 MB<br>100×100 to 4000×4000 pixels</p>
        @if ($editing)<p class="form-help">Leave this empty to keep the current image.</p>@if ($product->image_path)<input type="hidden" name="remove_image" value="0"><div class="form-check mt-3"><input class="form-check-input" name="remove_image" id="remove_image" type="checkbox" value="1" @checked(old('remove_image'))><label class="form-check-label" for="remove_image">Remove current image</label></div>@endif @endif
        <div class="upload-tip mt-4">After a validation error, please select your upload again. Browsers don't restore file selections.</div>
    </div></section></div></div>
    <div class="d-flex gap-2 mt-4"><button class="btn btn-primary px-4" type="submit"><i class="bi bi-check2"></i>{{ $editing ? 'Save changes' : 'Create product' }}</button><a class="btn btn-outline-secondary" href="{{ route('products.index') }}">Cancel</a></div>
</form>
@endsection
@push('scripts')
<script>
$(function(){
    const existing=@json($product->image_url);
    let previewUrl=null;
    function showPreview(source){if(source){$('#image-preview').attr('src',source).removeClass('d-none');$('#image-placeholder').addClass('d-none');}else{$('#image-preview').removeAttr('src').addClass('d-none');$('#image-placeholder').removeClass('d-none');}}
    function restore(){showPreview($('#remove_image').is(':checked')?null:existing);}
    function release(){if(previewUrl){URL.revokeObjectURL(previewUrl);previewUrl=null;}}
    $('#image').on('change',function(){
        release();const file=this.files[0];if(!file){restore();return;}
        if(!['image/jpeg','image/png','image/webp'].includes(file.type)||file.size>2*1024*1024){window.alert('Choose a JPG, PNG, or WebP image up to 2 MB.');this.value='';restore();return;}
        $('#remove_image').prop('checked',false);previewUrl=URL.createObjectURL(file);showPreview(previewUrl);
    });
    $('#remove_image').on('change',function(){if(this.checked){$('#image').val('');release();}restore();});
    function publicationRequired(){$('#published_at').prop('required',$('#status').val()==='active');}
    $('#status').on('change',publicationRequired);publicationRequired();restore();
    $(window).on('beforeunload',release);
});
</script>
@endpush
