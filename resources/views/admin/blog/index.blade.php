<x-backend.section sectionTitle="Blog" activeTitle="All Blog" :logo="$logo" :title="$title" columnName="Blog Title"
    permission="blog.create" :isCreateBtn="true" route="blog.create">
    @forelse ($blogs as $blog)
        <tr>
            <td class="ps-3"></td>
            <td>
                <h5 class="m-0"><a href="#" class="link-reset">{{ $loop->iteration }}</a>
                </h5>
            </td>
            <td>
                {{ $blog->title }}
            </td>
            <td>
                <div class="d-flex justify-content-center gap-1">
                    <x-action-button :url="route('blog.edit', $blog->id)" permission="blog.edit" icon="ti-edit" />

                    <x-action-button :data-id="$blog->id" permission="blog.delete" icon="ti-trash"
                        target-name="blog-delete" class="delete-btn"
                        data-url="{{ '/blogs/delete-blog/' . $blog->id }}" />

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
    <x-delete-modal-box id="blog-delete" message="Are you sure you want to delete this Blog?" />
</x-backend.section>
