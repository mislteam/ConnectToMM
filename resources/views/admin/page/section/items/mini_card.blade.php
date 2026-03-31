<x-form-input label="{{ 'Item Card Title ' . $loop->iteration }}" name="items[{{ $item->id }}][title]"
    :value="$item->title" placeholder="Enter Item Heading" required />

<x-form.filepond-input name="items[{{ $item->id }}][item_image]" label="Item Image" :isrequired="true">
    <div class="mt-3">
        @if ($item->item_image)
            <a target="_blank" href="{{ asset('section/' . $item->item_image) }}"
                alt="item-image">{{ asset('section/' . $item->item_image) }}</a>
        @endif
    </div>
</x-form.filepond-input>
