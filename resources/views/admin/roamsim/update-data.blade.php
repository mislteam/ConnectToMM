 @extends('admin.layouts.index')
 @section('title', 'Update Data')
 @section('content')
 @include('components.alert')
 <style>
#loadingOverlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255,255,255,0.3);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 99999;
}
.spinner {
    width: 60px;
    height: 60px;
    border: 6px solid #ddd;
    border-top-color: #007bff;
    border-radius: 50%;
    animation: spin 0.9s linear infinite;
}
@keyframes spin {
    100% { transform: rotate(360deg); }
}
</style>

<div id="loadingOverlay">
    <div class="spinner"></div>
</div>
     <div class="container-fluid">
         <div class="page-title-head d-flex align-items-center">
             <div class="flex-grow-1 py-3">
                 <h4 class="fs-sm fw-bold m-0 text-black">Update Data</h4>
                 <ol class="breadcrumb m-0 py-0">
                     <li class="breadcrumb-item"><a href="javascript: void(0);">Home</a></li>
                     <li class="breadcrumb-item active text-black">Update Data</li>
                 </ol>
             </div>
         </div>

         <div class="row">
             <div class="col-12">
                 <div data-table data-table-rows-per-page="20" class="card">
                     <div class="card-header fw-semibold">Esim SKU Data</div>
                     <div class="table-responsive mb-5">
                         <table class="table table-custom table-centered table-select table-hover w-100 mb-0">
                             <thead class="bg-light align-middle bg-opacity-25 thead-sm">
                                 <tr class="text-uppercase fs-xxs">
                                    
                                     <th data-table-sort>No</th>
                                     <th data-table-sort>SKU ID</th>
                                     <th class="">Country Name</th>
                                 </tr>
                             </thead>
                             <tbody>
                                @forelse ($newSkus as $index => $sku)
                                    <tr class="table-success">
                                        <td>
                                           <h5 class="fs-sm mb-0 fw-medium">{{ $loop->iteration }}</h5>
                                        </td>
                                        <td>
                                            <h5 class="text-nowrap fs-base mb-0 lh-base">{{ $sku->sku_id }}</h5>
                                    
                                        </td>
                                        <td class="">
                                            <h5 class="text-nowrap fs-base mb-0 lh-base">{{ $sku->country_name }}</h5>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">No new SKUs found</td>
                                    </tr>
                                 @endforelse
                                 
                             </tbody>
                         </table>
                     </div>
                    
                     <div class="card-footer border-0">
                         <div class="d-flex justify-content-between align-items-center">
                             <div data-table-pagination-info="name"></div>
                             <div data-table-pagination></div>
                         </div>
                     </div>
                      <!-- <div class="mt-2 mb-4 d-flex gap-2 justify-content-end">
                            <a href="{{ route('roamSkuPackages') }}" class="btn btn-primary text-end"> Sync... </a>
                        </div> -->
                 </div>
             </div><!-- end col -->
         </div><!-- end row -->


         <!-- for roam package new data list -->

          <div class="row">
             <div class="col-12">
                 <div data-table data-table-rows-per-page="50" class="card">
                     <div class="card-header fw-semibold">Roam Pacakge Data</div>
                     <div class="table-responsive mb-5">
                         <table class="table table-custom table-centered table-select table-hover w-100 mb-0">
                             <thead class="bg-light align-middle bg-opacity-25 thead-sm">
                                 <tr class="text-uppercase fs-xxs">
                                    
                                     <th data-table-sort>No</th>
                                     <th data-table-sort>Pkg Pid</th>
                                     <th class="">Package Plan</th>
                                 </tr>
                             </thead>
                             <tbody>
                                @forelse ($newPackages as $index => $pkg)
                                    <tr class="table-success">
                                        <td>
                                           <h5 class="fs-sm mb-0 fw-medium">{{ $loop->iteration }}</h5>
                                        </td>
                                        <td>
                                            <h5 class="text-nowrap fs-base mb-0 lh-base">{{ $pkg['pid'] ?? '-' }}</h5>
                                    
                                        </td>
                                        <td class="">
                                            <h5 class="text-nowrap fs-base mb-0 lh-base"> {{ $pkg['flows'] ?? '' }} {{ $pkg['unit'] ?? '' }} : {{ $pkg['showName'] == '' ? 'Fixed : '. $pkg['days'] . 'days' : ($pkg['showName'] == 'Unlimited DayPass : '. $pkg['days'] . 'days'  ? 'Unilimited' : 'Daypass : '. $pkg['days'] . 'days') }}</h5>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">No new packages found</td>
                                    </tr>
                                 @endforelse
                                 
                             </tbody>
                         </table>
                     </div>
                    
                     <div class="card-footer border-0">
                         <div class="d-flex justify-content-between align-items-center">
                             <div data-table-pagination-info="name"></div>
                             <div data-table-pagination></div>
                         </div>
                     </div>
                     
                 </div>
                  <div class="mt-2 mb-4 d-flex gap-2 justify-content-end">
                            <a href="{{ route('roamSkuPackages') }}" id="syncBtn" class="btn btn-primary text-end"> Sync... </a>
                        </div>
             </div><!-- end col -->
         </div><!-- end row -->
     </div>
     <!-- container -->


<script>
document.addEventListener('DOMContentLoaded', function() {
    const syncButton = document.querySelector('#syncBtn');

    if (syncButton) {
        syncButton.addEventListener('click', function() {
            document.getElementById('loadingOverlay').style.display = 'flex';
        });
    }
});
</script>
 @endsection