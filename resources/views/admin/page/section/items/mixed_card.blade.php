<x-form-input label="{{ 'Item Card Title ' . $loop->iteration }}" name="items[{{ $item->id }}][title]"
    :value="$item->title" placeholder="Enter Item Heading" required />

<x-form-text-area label="Item Description" name="items[{{ $item->id }}][description]" :value="$item->description"
    placeholder="Enter Item Description" />

<x-form.filepond-input name="items[{{ $item->id }}][item_image]" label="Item Image">
    <div class="mt-3">
        @if ($item->item_image)
            <a target="_blank" href="{{ asset('item/' . $item->item_image) }}"
                alt="item-image">{{ asset('item/' . $item->item_image) }}</a>
        @endif
    </div>
</x-form.filepond-input>
