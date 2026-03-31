<x-form-input label="Image Url" name="items[{{ $item->id }}][button_url]" :value="$item->button_url"
    placeholder="Enter Image URL" required />

<x-form.filepond-input name="items[{{ $item->id }}][item_image]" label="Item Image">
    <div class="mt-3">
        @if ($item->item_image)
            <a target="_blank" href="{{ asset('section/' . $item->item_image) }}"
                alt="item-image">{{ asset('section/' . $item->item_image) }}</a>
        @endif
    </div>
</x-form.filepond-input>
