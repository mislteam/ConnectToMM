<x-backend.section sectionTitle="Category" activeTitle="All Categories" :logo="$logo" :title="$title"
    permission="blog.category.create" columnName="Category Name" :isCreateBtn="true" route="blog.category.create">
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
                    <x-action-button :url="route('blog.category.edit', $category->id)" permission="blog.category.edit" icon="ti-edit" />
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
