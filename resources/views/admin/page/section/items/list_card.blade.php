<x-form-input label="Value" name="items[{{ $item->id }}][title]" :value="$item->title" placeholder="Enter Item Heading" />

<x-form.filepond-input name="items[{{ $item->id }}][item_image]" label="Item Image">
    <div class="mt-3">
        @if ($item->item_image)
            <a target="_blank" href="{{ asset('section/' . $item->item_image) }}"
                alt="item-image">{{ asset('section/' . $item->item_image) }}</a>
        @endif
    </div>
</x-form.filepond-input>
