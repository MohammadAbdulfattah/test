@extends('layouts.app')
@section('title', __('gbs::lang.Map'))

@section('content')
<style>
    .marker-pin {
        width: 30px;
        height: 30px;
        border-radius: 50% 50% 50% 0;
        background-color: #c30b82;
        position: absolute;
        transform: rotate(-45deg);
        left: 50%;
        top: 50%;
        margin-left: -15px;
        margin-top: -15px;
    }
    
    /* .custom-icon {
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -100%);
        color: white;
        font-size: 16px;
    } */
    </style>
    

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('gbs::lang.Map')</h1>
    </section>
  
    <!-- Main content -->
    <section class="content no-print">
        @component('components.filters', ['title' => __('report.filters')]) 
        <div class="col-md-3">
            <div class="form-group">
                <label for="customer">@lang('gbs::lang.customer_name'):</label>
                {!! Form::text('customer-search',null, ['class' => 'form-control', 'id' => 'customer-search']) !!}
                
    
    </div>
    </div> 
     <div class="col-md-3">
        <div class="form-group">
            <label for="cg_filter">@lang('lang_v1.customer_group'):</label>
            {!! Form::select('cg_filter', $customer_groups, null, ['class' => 'form-control', 'id' => 'cg_filter']) !!}
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label for="tags">@lang('gbs::lang.tags'):</label>
            {!! Form::select('tag_filter', $tags, null, ['class' => 'form-control', 'id' => 'tag_filter']) !!}
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            {!! Form::label('assigned_to', __('lang_v1.assigned_to') . ':') !!}
            {!! Form::select('assigned_to', $users, null, ['class' => 'form-control', 'style' => 'width:100%', 'id' => 'assigned_to']) !!}
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label for="status_filter">@lang('sale.status'):</label>
            {!! Form::select(
                'status_filter',
                ['active' => __('business.is_active'), 'inactive' => __('lang_v1.inactive')],
                'active', {{-- ✅ اجعل القيمة الافتراضية هي "active" --}}
                ['class' => 'form-control', 'id' => 'status_filter']
            ) !!}
        </div>
    </div>
    <div class="col-md-3">
    <div class="form-group">
        {!! Form::label('parent_sector_id', __('lang_v1.customer_sector')) !!}
        {!! Form::select('parent_sector_id', $parent_sectors, request('parent_sector_id'), ['class' => 'form-control', 'id' => 'parent_sector_id']) !!}
    </div>
    
   
</div>
<div class="col-md-3">
<div class="form-group" id="child_sector_div" style="display: none;">
    {!! Form::label('sector_id', __('lang_v1.customer_sub_sector')) !!}
    {!! Form::select('sector_id', [], request('sector_id'), ['class' => 'form-control', 'id' => 'sector_id']) !!}
</div>
</div>
    
    
    @endcomponent
        <div id="map" style="width: 100%; height: 600px;"></div>
        <br><br><br>
        <div class="row">
      
            @component('components.widget', ['class' => 'box-solid'])
        <ul class="nav nav-tabs" id="reportTabs">
            <li class="active"><a data-toggle="tab" href="#sector_tab">@lang('lang_v1.customer_sector')</a></li>
            <li><a data-toggle="tab" href="#tag_tab">@lang('gbs::lang.tags')</a></li>
            <li><a data-toggle="tab" href="#group_tab">@lang('lang_v1.customer_group')</a></li>

          </ul>
          
          <div class="tab-content">
            <div id="sector_tab" class="tab-pane fade in active">
              <table class="table table-bordered" id="sector_table">
                <thead>
                  <tr>
                    
                    <th>@lang('gbs::lang.customer_sector')</th>
                    <th>@lang('gbs::lang.parent_sector')</th>
                    <th>@lang('gbs::lang.customers_number')</th>
                  </tr>
                </thead>
              </table>
            </div>
            <div id="tag_tab" class="tab-pane fade">
                
              <table class="table table-bordered table-responsive"  style="width: 100%;" id="tag_table">
                <thead>
                  <tr>
                  
                    <th>@lang('gbs::lang.tags')</th>
                    <th>@lang('gbs::lang.tag_color')</th>
                    <th>@lang('gbs::lang.customers_number')</th>
                    
                  </tr>
                </thead>
              </table>
            </div>
            <div id="group_tab" class="tab-pane fade">
                <table class="table table-bordered" style="width: 100%;" id="group_table">
                  <thead>
                    <tr>
                      <th>@lang('lang_v1.customer_group')</th>
                      <th>@lang('gbs::lang.customers_number')</th>
                    </tr>
                  </thead>
                </table>
              </div>
              
          </div>
        </div>
   
        @endcomponent
    </section>
    
      
@endsection
@section('javascript')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<script>
    const searchCustomerUrl = "{{ route('customers.search') }}";
</script>



<script>
    document.addEventListener('DOMContentLoaded', function () {
        const map = L.map('map').setView([33.3152, 44.3661], 11);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        let markers = {};

        const customers = @json($customers); 
        customers.forEach(addCustomerMarker);

        
//         function addCustomerMarker(customer) {
//             const lat = parseFloat(customer.latitude);
//     const lng = parseFloat(customer.longitude);

//     if (isNaN(lat) || isNaN(lng)) {
//         console.warn("Invalid coordinates for customer:", customer);
//         return;
//     }
//     if (customer.latitude && customer.longitude) {
//         const marker = L.marker([parseFloat(customer.latitude), parseFloat(customer.longitude)]).addTo(map);

//         let popupContent = `<strong>${customer.name}</strong><br>`;
//         popupContent += `📞 ${customer.mobile ?? ''}<br>`;
//         popupContent += `📍 ${customer.coordinates ?? 'بدون عنوان'}<br>`;
//         popupContent += `<br>🧾 <strong>إجمالي المبيعات:</strong> ${customer.total_invoice ?? 0} <br>`;
//         popupContent += `💰 <strong>الرصيد المستحق:</strong> ${customer.balance_due ?? 0} <br>`;


//         if (customer.tags && customer.tags.length > 0) {
//             popupContent += `<div><strong> التاغات:</strong><br>`;
//             customer.tags.forEach(tag => {
//                 popupContent += `<span style="background:${tag.color}; color:white; padding:2px 6px; border-radius:4px; margin:2px 4px 0 0; display:inline-block; font-size:12px;">
//                     ${tag.name}
//                 </span>`;
//             });
//             popupContent += `</div>`;
//         }

//         marker.bindPopup(popupContent);
//         markers[customer.id] = marker;
//     }
// }
function addCustomerMarker(customer) {
    const lat = parseFloat(customer.latitude);
    const lng = parseFloat(customer.longitude);

    if (isNaN(lat) || isNaN(lng)) {
        console.warn("Invalid coordinates for customer:", customer);
        return;
    }

    let markerOptions = {};
    if (customer.tags && customer.tags.length > 0) {
        const tagColor = customer.tags[0].color || '#3388ff';
        const icon = L.divIcon({
            className: "custom-div-icon",
            html: `<div style="background-color:${tagColor};" class="marker-pin"></div>`,
            iconSize: [30, 42],
            iconAnchor: [15, 42],
            popupAnchor: [0, -40],
        });
        markerOptions.icon = icon;
    }

    const marker = L.marker([lat, lng], markerOptions).addTo(map);

    let popupContent = `<strong>${customer.name}</strong><br>`;
    popupContent += `📞 ${customer.mobile ?? ''}<br>`;
    popupContent += `📍 ${customer.coordinates ?? 'بدون عنوان'}<br>`;
    popupContent += `<br>🧾 <strong>إجمالي المبيعات:</strong> ${customer.total_invoice ?? 0} <br>`;
    popupContent += `💰 <strong>الرصيد المستحق:</strong> ${customer.balance_due ?? 0} <br>`;

    if (customer.tags && customer.tags.length > 0) {
        popupContent += `<div><strong> التاغات:</strong><br>`;
        customer.tags.forEach(tag => {
            popupContent += `<span style="background:${tag.color}; color:white; padding:2px 6px; border-radius:4px; margin:2px 4px 0 0; display:inline-block; font-size:12px;">
                ${tag.name}
            </span>`;
        });
        popupContent += `</div>`;
    }

    marker.bindPopup(popupContent);
    markers[customer.id] = marker;
}


        function clearMapMarkers() {
            for (const id in markers) {
                map.removeLayer(markers[id]);
            }
            markers = {};
        }

        function handleFiltersChange() {
            const query = document.getElementById('customer-search').value.trim();
            const groupId = document.getElementById('cg_filter').value;
            const tagId = document.getElementById('tag_filter').value;
            const assignedTo = document.getElementById('assigned_to').value;
            const isActive = document.getElementById('status_filter').value;
            const parentSectorId = document.getElementById('parent_sector_id').value;
            const sectorId = document.getElementById('sector_id').value;
          
            const url = new URL("{{ route('customers.search') }}");
            if (query.length >= 2) url.searchParams.append('q', query);
            if (groupId) url.searchParams.append('customer_group_id', groupId);
            if (tagId) url.searchParams.append('tag_id', tagId);
            if (assignedTo) url.searchParams.append('assigned_to', assignedTo);
            if (isActive) url.searchParams.append('status_filter', isActive);
            if (parentSectorId) url.searchParams.append('parent_sector_id', parentSectorId);
            if (sectorId) url.searchParams.append('sector_id', sectorId);
            fetch(url)
                .then(res => res.json())
                .then(data => {
                    clearMapMarkers();

                    if (!data || data.length === 0) {
                        alert("لم يتم العثور على نتائج.");
                        map.setView([33.3152, 44.3661], 11); 
                        return;
                    }

                    data.forEach(addCustomerMarker);
                    const bounds = data.map(c => [parseFloat(c.latitude), parseFloat(c.longitude)]);
                    map.fitBounds(bounds);
                });
        }

        
        document.getElementById('customer-search').addEventListener('change', handleFiltersChange);
        document.getElementById('cg_filter').addEventListener('change', handleFiltersChange);
        document.getElementById('tag_filter').addEventListener('change', handleFiltersChange);
        document.getElementById('assigned_to').addEventListener('change', handleFiltersChange);
        document.getElementById('status_filter').addEventListener('change', handleFiltersChange);
        document.getElementById('parent_sector_id').addEventListener('change', handleFiltersChange);
        document.getElementById('sector_id').addEventListener('change', handleFiltersChange);

    });
    $('#parent_sector_id').change(function() {
        var parent_id = $(this).val();
        if (parent_id) {
            $.ajax({
                url: '/sectors/children/' + parent_id,
                method: 'GET',
                success: function(data) {
                    if (data.length > 0) {
                        $('#sector_id').empty();
                        $.each(data, function(index, sector) {
                            $('#sector_id').append('<option value="' + sector.id + '">' + sector.name + '</option>');
                        });
                        $('#child_sector_div').show();
                    } else {
                        $('#child_sector_div').hide();
                        $('#sector_id').empty();
                    }
                }
            });
        } else {
            $('#child_sector_div').hide();
            $('#sector_id').empty();
        }
    });

    $('#sector_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ url("gbs/reports/sectors") }}',
        columns: [
  
    { data: 'name', name: 'name' },
    { data: 'parent_name', name: 'parent_name' },
    { data: 'customer_count', name: 'customer_count' }
]

    });
    $('#tag_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ url("gbs/reports/tags") }}',
        columns: [
           
            { data: 'name', name: 'name' },
            { data: 'color', name: 'color' },
            { data: 'customer_count', name: 'customer_count' }
        ]
    });
    $('#group_table').DataTable({
    processing: true,
    serverSide: true,
    ajax: '{{ url("gbs/reports/customer-groups") }}',
    columns: [
        { data: 'name', name: 'customer_groups.name' },
         { data: 'customers_count', name: 'customers_count' }
    ]
});

</script>
@endsection
