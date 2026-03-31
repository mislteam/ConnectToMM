<x-backend.section sectionTitle="Category" activeTitle="All Categories" :logo="$logo" :title="$title"
    columnName="Category Name" :isCreateBtn="true" route="blog.category.create">
    @forelse ($categories as $category)
        <tr>
            <td class="ps-3"></td>
            <td>
                <h5 class="m-0"><a href="#" class="link-reset">{{ $loop->iteration }}</a>
                </h5>
            </td>
            <td>
                {{ $category->cat_name }}
            </td>
            <td>
                <div class="d-flex justify-content-center gap-1">
                    <a href="{{ route('blog.category.edit', $category->id) }}"
                        class="btn btn-light btn-icon btn-sm rounded-circle"><i class="ti ti-edit fs-lg"></i></a>
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="5">
                <p class="text-center">Nothing Found.</p>
            </td>
        </tr>
    @endforelse
</x-backend.section>
