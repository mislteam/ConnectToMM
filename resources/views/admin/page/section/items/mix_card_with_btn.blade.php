<x-form-input label="{{ 'Item Card Title ' . $loop->iteration }}" name="items[{{ $item->id }}][title]"
    :value="$item->title" placeholder="Enter Item Heading" required />

<x-form-text-area label="Item Description" name="items[{{ $item->id }}][description]" :value="$item->description"
    placeholder="Enter Item Description" />

<x-form-input label="{{ 'Button Text ' . $loop->iteration }}" name="items[{{ $item->id }}][button_text]"
    :value="$item->button_text" placeholder="Enter Button Text" required />

<x-form-input label="{{ 'Button Url ' . $loop->iteration }}" name="items[{{ $item->id }}][button_url]"
    :value="$item->button_url" placeholder="Enter Button Url" required />

<x-form.filepond-input name="items[{{ $item->id }}][item_image]" label="Item Image">
    <div class="mt-3">
        @if ($item->item_image)
            <a target="_blank" href="{{ asset('section/' . $item->item_image) }}"
                alt="item-image">{{ asset('section/' . $item->item_image) }}</a>
        @endif
    </div>
</x-form.filepond-input>
