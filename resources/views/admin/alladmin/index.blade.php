@extends('admin.layouts.index')
@section('title', 'All Admin')
@section('content') 
        <div class="container-fluid">     
            @include('components.alert')            
                <div class="page-title-head d-flex align-items-center">
                    <div class="flex-grow-1 py-3">
                        <h4 class="fs-sm fw-bold m-0 text-black">Admin</h4>
                        <ol class="breadcrumb m-0 py-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Home</a></li>
                            <li class="breadcrumb-item active text-black">All Admin</li>
                        </ol>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-12">
                        <div data-table data-table-rows-per-page="5" class="card">
                            <div class="card-header border-light justify-content-between">
                                <div class="d-flex gap-2">
                                    <div class="app-search">
                                        <input data-table-search type="search" name="search" class="form-control" placeholder="Search admin..."  value="{{ request('search') }}" >
                                        <i data-lucide="search" class="app-search-icon text-muted"></i>
                                    </div>
                                    <button data-table-delete-selected class="btn btn-danger d-none">Delete</button>
                                </div>
                                </form>

                                <div class="d-flex align-items-center gap-2">
                                    <span class="me-2 fw-semibold">Filter By:</span>

                                    <!-- Role Type Filter -->
                                    <div class="app-search">
                                        <select data-table-filter="roles" class="form-select form-control my-1 my-md-0" >
                                            <option value="All">Role</option>
                                            <option value="administrator">Administrator</option>
                                            <option value="editor">Editor</option>
                                        </select>
                                        <i data-lucide="shield" class="app-search-icon text-muted"></i>
                                    </div>

                                   <!-- Status Filter -->
                                    <div class="app-search">
                                        <select data-table-filter="status" class="form-select form-control my-1 my-md-0">
                                            <option value="All">Status</option>
                                            <option value="Active">Active</option>
                                            <option value="Inactive">Inactive</option>
                                        </select>
                                        <i data-lucide="user-check" class="app-search-icon text-muted"></i>
                                    </div>

                                    <!-- Records Per Page -->
                                    <div>
                                        <select data-table-set-rows-per-page class="form-select form-control my-1 my-md-0">
                                            <option value="5">5</option>
                                            <option value="10">10</option>
                                            <option value="15">15</option>
                                            <option value="20">20</option>
                                        </select>
                                    </div>

                                   <a href="{{ route('create.admin') }}" class="btn btn-primary">Create Admin</a>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-custom table-centered table-select table-hover w-100 mb-0">
                                    <thead class="bg-light align-middle bg-opacity-25 thead-sm">
                                        <tr class="text-uppercase fs-xxs">
                                            <th class="ps-3" style="width: 1%;">
                                                <input data-table-select-all class="form-check-input form-check-input-light fs-14 mt-0" type="checkbox" id="select-all-files" value="option">
                                            </th>
                                            <th data-table-sort>No</th>
                                            <th data-table-sort="user">User</th>
                                            <th data-table-sort data-column="roles">Role</th>
                                            <th data-table-sort>Last Updated</th>
                                            <th data-table-sort data-column="status">Status</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Row 1 -->
                                        @foreach ($users as  $user)
                                        <tr>
                                            <td class="ps-3"><input class="form-check-input form-check-input-light fs-14 file-item-check mt-0" type="checkbox"  name="selected_users[]" value="{{ $user->id }}" @if($user->role === 'administrator') disabled @endif></td>
                                            <td>
                                                <h5 class="m-0"><a href="#" class="link-reset">{{ $loop->iteration }}</a>
                                                </h5>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-start gap-2">
                                                    <div>
                                                        <h5 class="fs-base mb-0"><a data-sort="user" href="#" class="link-reset">{{ $user->name }}</a></h5>
                                                        <p class="text-muted fs-xs mb-0">{{ $user->email }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $user->role }}</td>
                                            <td> {{ $user->updated_at->format('d M, Y') }}<small class="text-muted">{{ $user->updated_at->format('g:i A') }}</small></td>
                                            <td><span class="badge badge-soft-success text-success badge-label">{{ $user->status == 0 ? 'Active' : 'Inactive' }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex justify-content-center gap-1">
                                                     <a href="{{ route('view.admin', $user->id) }}" class="btn btn-light btn-icon btn-sm rounded-circle"><i class="ti ti-eye fs-lg"></i></a>
                                                    <a href="{{ route('admin.edit', $user->id) }}" class="btn btn-light btn-icon btn-sm rounded-circle"><i class="ti ti-edit fs-lg"></i></a>
                                                    <form method="POST" action="{{ route('admin.destroy', $user->id) }}" class="delete-form d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button"
                                                            class="btn btn-light btn-icon btn-sm rounded-circle delete-btn"
                                                            @if($user->role === 'administrator') disabled @endif>
                                                            <i class="ti ti-trash fs-lg"></i>
                                                        </button>
                                                    </form>                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        <div class="card-footer border-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <div data-table-pagination-info="admin">
                                    Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} entries
                                </div>
                                <div data-table-pagination>
                                    {{ $users->links('pagination::bootstrap-5') }}
                                </div>
                            </div>
                        </div>
                        </div>

                    </div><!-- end col -->
                </div><!-- end row -->                 
            </div>
            <!-- container -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.querySelectorAll('.delete-btn').forEach(function(button) {
        button.addEventListener('click', function() {
            let form = this.closest('form');

            Swal.fire({
                title: 'Are you sure?',
                text: "This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e3342f',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@endsection