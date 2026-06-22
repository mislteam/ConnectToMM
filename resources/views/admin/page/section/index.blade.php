<x-backend.section sectionTitle="Section" activeTitle="All Sections" :logo="$logo" :title="$title"
    columnName="Section Name">
    @forelse ($sections as $section)
        <tr>
            <td class="ps-3"></td>
            <td>
                <h5 class="m-0"><a href="#" class="link-reset">{{ $loop->iteration }}</a>
                </h5>
            </td>
            <td>
                {{ $section->eyebrow_text }}
            </td>
            <td>
                <div class="d-flex justify-content-center gap-1">
                    <x-action-button :url="route('page.section.edit', [$section->section_key, $section->id])" permission="page.edit" icon="ti-edit" />
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
