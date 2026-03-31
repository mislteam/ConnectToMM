<x-form-input label="{{ 'Value ( ' . $loop->iteration . ' )' }}" name="items[{{ $item->id }}][title]" :value="$item->title"
    placeholder="Enter Item Heading" />
