<x-form-input label="Button Text" name="items[{{ $item->id }}][button_text]" placeholder="Enter Button Text" required
    :value="$item->button_text" />

<x-form-input label="Button URL" name="items[{{ $item->id }}][button_url]" placeholder="Enter Button URL" required
    :value="$item->button_url" />
