@extends('layouts.app')
@section('title', 'Product inventory')
@push('styles')<link href="https://cdn.datatables.net/2.2.2/css/dataTables.bootstrap5.min.css" rel="stylesheet">@endpush
@section('content')
<div class="page-heading"><div><div class="eyebrow">EVERYTHING IN ITS PLACE</div><h1>Product inventory</h1><p class="text-secondary">A clear view of what you have, and what's next.</p></div><a class="btn btn-primary" href="{{ route('products.create') }}"><i class="bi bi-plus-lg"></i> Add product</a></div>
<div class="stats-grid">
    <div class="stat-card"><div class="stat-top">Total products <span class="stat-icon"><i class="bi bi-box-seam"></i></span></div><div class="stat-value" id="stat-total">{{ number_format($stats['total']) }}</div><div class="stat-foot">Across your entire catalog</div></div>
    <div class="stat-card"><div class="stat-top">Active products <span class="stat-icon"><i class="bi bi-check2-circle"></i></span></div><div class="stat-value" id="stat-active">{{ number_format($stats['active']) }}</div><div class="stat-foot"><i class="bi bi-circle-fill" style="font-size:5px"></i> Ready for business</div></div>
    <div class="stat-card"><div class="stat-top">Low stock <span class="stat-icon" style="background:#fcf6e8;color:#b49a55"><i class="bi bi-graph-down-arrow"></i></span></div><div class="stat-value" id="stat-low">{{ number_format($stats['low']) }}</div><div class="stat-foot">5 units or fewer · excludes archived</div></div>
    <div class="stat-card"><div class="stat-top">Inventory value <span class="stat-icon"><i class="bi bi-wallet2"></i></span></div><div class="stat-value" id="stat-value" style="font-size:24px">₹{{ number_format((float)$stats['value'], 0) }}</div><div class="stat-foot">At regular price · INR</div></div>
</div>
<section class="panel">
    <div class="panel-title"><div><h2>All products <span class="count-label" id="catalog-count">{{ $stats['total'] }} total</span></h2><p class="panel-caption">A good home for every product.</p></div><span class="table-heading-note"><i class="bi bi-arrow-down-up me-1"></i> Click a column to sort</span></div>
    <div class="table-toolbar"><div class="search-field"><i class="bi bi-search"></i><label for="product-search" class="visually-hidden">Search products</label><input id="product-search" class="form-control" placeholder="Search by name, SKU, or status…" type="search"></div><div class="d-flex gap-2"><label for="status-filter" class="visually-hidden">Filter by status</label><select id="status-filter" class="form-select filter-select"><option value="">All statuses</option value="active">Active</option><option value="draft">Draft</option><option value="archived">Archived</option></select><button id="refresh-table" class="btn btn-outline-secondary" aria-label="Refresh inventory" title="Refresh inventory"><i class="bi bi-arrow-clockwise"></i></button></div></div>
    <div class="table-responsive"><table id="products-table" class="table table-hover w-100"><thead><tr><th>ID</th><th>Image</th><th>Product name</th><th>SKU</th><th>Price (₹)</th><th>Stock</th><th>Status</th><th>Published</th><th>Actions</th></tr></thead><tbody></tbody></table></div>
</section>
<div class="table-note"><i class="bi bi-shield-check"></i> Your catalog is private. Only you can view and manage these products.</div>
@endsection
@push('scripts')
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.bootstrap5.min.js"></script>
<script>
$(function(){
    $.fn.dataTable.ext.errMode='none';
    const table=$('#products-table').DataTable({
        processing:true,serverSide:true,pageLength:10,lengthMenu:[10,25,50,100],order:[[0,'desc']],
        dom:'rt<"table-bottom"li p>',
        ajax:{url:@json(route('products.data')),data:function(d){d.status=$('#status-filter').val();},error:function(xhr){showMessage(requestError(xhr));}},
        columns:[{data:'id',name:'id'},{data:'image',name:'image',orderable:false,searchable:false},{data:'name',name:'name'},{data:'sku',name:'sku'},{data:'price',name:'price'},{data:'stock',name:'stock'},{data:'status',name:'status'},{data:'published_at',name:'published_at'},{data:'actions',name:'actions',orderable:false,searchable:false}],
        language:{emptyTable:'Your catalog starts here. Add your first product.',zeroRecords:'No products match your search.',lengthMenu:'Show _MENU_',info:'_START_–_END_ of _TOTAL_ products',infoEmpty:'0 products',paginate:{previous:'‹',next:'›'}},
        createdRow:function(row,data){if(Number(data.stock)<=5){$('td',row).eq(5).addClass('stock-low');}}
    });
    let timer;
    $('#product-search').on('input',function(){clearTimeout(timer);const value=this.value;timer=setTimeout(function(){table.search(value).draw();},350);});
    $('#status-filter').on('change',function(){table.ajax.reload();});
    $('#refresh-table').on('click',function(){window.location.reload();});
    $('#products-table').on('click','.js-delete-product',function(){
        if(!window.confirm('Delete this product and its image permanently?'))return;
        const button=$(this);button.prop('disabled',true);
        $.ajax({url:button.attr('data-url'),type:'DELETE',success:function(response){
            showMessage(response.message,'success');
            const stats=response.stats;
            $('#stat-total').text(Number(stats.total).toLocaleString('en-IN'));
            $('#stat-active').text(Number(stats.active).toLocaleString('en-IN'));
            $('#stat-low').text(Number(stats.low).toLocaleString('en-IN'));
            $('#stat-value').text('₹'+Number(stats.value).toLocaleString('en-IN',{maximumFractionDigits:0}));
            $('#catalog-count').text(stats.total+' total');
            table.ajax.reload(function(){const info=table.page.info();if(info.page>0&&info.start>=info.recordsDisplay){table.page('previous').draw('page');}},false);
        },error:function(xhr){showMessage(requestError(xhr));},complete:function(){button.prop('disabled',false);}});
    });
    if(new URLSearchParams(window.location.search).has('deleted')){
        showMessage('Product deleted successfully.','success');
        history.replaceState({},'',@json(route('products.index')));
    }
});
</script>
@endpush
