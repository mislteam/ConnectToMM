@extends('admin.layouts.index')
@section('title', 'Footer Page')
@section('content')
    <div class="container-fluid">
        <div class="page-title-head d-flex align-items-center">
            <div class="flex-grow-1 py-3">
                <h4 class="fs-sm fw-bold m-0 text-black">Footer</h4>
                <ol class="breadcrumb m-0 py-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Home</a></li>
                    <li class="breadcrumb-item active text-black">Footer</li>
                </ol>
            </div>
            <div class="d-flex gap-2 justify-content-end">
                <a href="{{ route('footer.contact.index') }}" class="btn btn-primary">Back</a>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <form action="{{ route('footer.contact.update', $info->id) }}" method="post" enctype="multipart/form-data">
                    @csrf
                    @method('patch')
                    <div class="card">
                        <div class="card-header d-block p-3">
                            <h4 class="card-title mb-1">Edit Contact Information</h4>
                        </div>
                        <div class="card-body">
                            <x-form-text-area label="Description" name="description" :value="$info->description" />

                            <x-form-input label="Phone" name="phone" :value="$info->phone" placeholder="Enter phone"
                                required />

                            <x-form-input label="Email" name="email" type="email" :value="$info->email"
                                placeholder="Enter email" required />

                            <div class="form-group row mb-3">
                                <label class="col-sm-2 col-form-label"><strong>Other Social Media Link</strong></label>
                                <div class="col-sm-10">
                                    <div class="row">
                                        <div id="socialLinkGroup">
                                            <div id="socialLinksWrapper">
                                                @forelse ($info->social_media_links??[] as $i => $value)
                                                    <div class="row social-group">
                                                        <!-- title -->
                                                        <div class="col-md-3">
                                                            <x-form.social-input name="title" :index="$i"
                                                                placeholder="Enter Title" inputClass="title-input"
                                                                :value="$value['title'] ?? ''" />
                                                        </div>

                                                        <!-- icon -->
                                                        <div class="col-md-4">
                                                            <x-form.social-input name="icon_name" :index="$i"
                                                                placeholder="Enter Icon Name" inputClass="icon-input"
                                                                :value="$value['icon'] ?? ''">
                                                                <span class="fs-10 mb-0">
                                                                    * You can search icon in
                                                                    <a href="https://fontawesome.com" target="_blank"
                                                                        class="link-secondary">here</a>
                                                                </span>
                                                            </x-form.social-input>
                                                        </div>

                                                        <!-- url -->
                                                        <div class="col-md-3">
                                                            <x-form.social-input name="other_social_url" :index="$i"
                                                                placeholder="Enter Social URL" inputClass="url-input"
                                                                :value="$value['link'] ?? ''" />
                                                        </div>

                                                        @if ($i === 0)
                                                            <div class="col-md-2">
                                                                <button type="button" id="addSocialLink"
                                                                    class="btn btn-sm btn-dark">Add
                                                                    ++</button>
                                                            </div>
                                                        @else
                                                            <div class="col-md-2">
                                                                <button type="button"
                                                                    class="btn btn-danger btn-sm deleteRow">Delete</button>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @empty
                                                    <div class="row social-group mb-2">
                                                        <div class="col-md-3">
                                                            <x-form.social-input name="title" index="0"
                                                                placeholder="Enter Title" inputClass="title-input" />
                                                        </div>
                                                        <div class="col-md-4">
                                                            <x-form.social-input name="icon_name" index="0"
                                                                placeholder="Enter Icon Name" inputClass="icon-input">
                                                                <span class="fs-10 mb-0">
                                                                    * You can search icon in
                                                                    <a href="https://fontawesome.com" target="_blank"
                                                                        class="link-secondary">here</a>
                                                                </span>
                                                            </x-form.social-input>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <x-form.social-input name="other_social_url" index="0"
                                                                placeholder="Enter Social URL" inputClass="url-input" />
                                                        </div>
                                                        <div class="col-md-2">
                                                            <button type="button" id="addSocialLink"
                                                                class="btn btn-sm btn-dark">Add
                                                                ++</button>
                                                        </div>
                                                    </div>
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="card-footer">
                            <div class="d-flex gap-2 justify-content-end">
                                <button type="submit" class="btn btn-primary">Update</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let wrapper = document.getElementById('socialLinksWrapper');
            let addBtn = document.getElementById('addSocialLink');

            addBtn.addEventListener('click', function() {
                let index = wrapper.children.length;
                let row = document.createElement('div');
                row.className = 'row social-group mb-2';

                row.innerHTML = `
                    <div class="col-md-3 mb-2">
                        <input type="text" name="title[${index}]" class="form-control title-input" placeholder="Enter Title">
                    </div>
                    <div class="col-md-4 mb-2">
                        <input type="text" name="icon_name[${index}]" class="form-control icon-input" placeholder="Enter Icon Name">
                        <span class="fs-10 mb-0">
                            * You can search icon in
                            <a href="https://fontawesome.com" target="_blank" class="link-secondary">here</a>
                        </span>
                    </div>
                    <div class="col-md-3 mb-2">
                        <input type="text" name="other_social_url[${index}]" class="form-control url-input" placeholder="Enter Social URL">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger btn-sm deleteRow">Delete</button>
                    </div>
            `;

                wrapper.appendChild(row);
            });
            wrapper.addEventListener('click', function(e) {
                if (e.target && e.target.classList.contains('deleteRow')) {
                    e.target.closest('.social-group').remove();
                    let deleteBtns = wrapper.querySelectorAll('.deleteRow');
                    wrapper.querySelectorAll('.social-group').forEach((row, idx) => {
                        row.querySelectorAll('input').forEach(input => {
                            let name = input.getAttribute('name').split('[')[0];
                            input.setAttribute('name', name + `[${idx}]`);
                        });
                    });
                }
            });
        });
    </script>
@endsection
