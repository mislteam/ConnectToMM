<x-backend.section sectionTitle="Blog" activeTitle="All Blog" :logo="$logo" :title="$title" columnName="Blog Title"
    :isCreateBtn="true" route="blog.create">
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
                    <a href="{{ route('blog.edit', $blog->id) }}" class="btn btn-light btn-icon btn-sm rounded-circle"><i
                            class="ti ti-edit fs-lg"></i></a>
                    <a href="#" data-id="{{ $blog->id }}" data-bs-toggle="modal" data-bs-target="#blog-delete"
                        class="btn btn-light btn-icon btn-sm rounded-circle delete-blog-btn">
                        <i class="ti ti-trash fs-lg"></i>
                    </a>
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
    <div class="modal fade" id="blog-delete" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <p>Are you sure you want to delete this Blog?</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</x-backend.section>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let blog_id = null;
        document.querySelectorAll(".delete-blog-btn").forEach((btn) => {
            btn.addEventListener("click", function() {
                blog_id = this.getAttribute("data-id");
            });
        });
        document
            .getElementById("confirmDeleteBtn")
            .addEventListener("click", function() {
                if (!blog_id) return;

                fetch(`/blogs/delete-blog/${blog_id}`, {
                        method: "DELETE",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector(
                                'meta[name="csrf-token"]'
                            ).content,
                        },
                    })
                    .then((res) => {
                        if (!res.ok) throw new Error("Failed to delete Blog.");
                        return res.json();
                    })
                    .then(() => {
                        const modal = bootstrap.Modal.getInstance(
                            document.getElementById("blog-delete")
                        );
                        modal.hide();

                        document
                            .querySelector(`[data-id="${blog_id}"]`)
                            ?.closest("tr")
                            ?.remove();
                        window.location.reload();
                    })
                    .catch((err) => {
                        console.error(err);
                    });
            });
    })
</script>
