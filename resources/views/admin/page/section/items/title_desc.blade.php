<x-form-input label="Item Title" name="items[{{ $item->id }}][title]" :value="$item->title"
    placeholder="Enter Item Title" />

<x-form-text-area label="Item Description" name="items[{{ $item->id }}][description]" :value="$item->description"
    :isrequired="false" placeholder="Enter Item Description" />
