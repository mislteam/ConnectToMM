<?php

// Bannner Image
if (!function_exists('get_banner')) {
    function get_banner($type)
    {
        $banner =  \App\Models\Banner::where('banner_type', $type)->first();
        if ($banner) {
            return $banner->image;
        }
    }
}


if (!function_exists('get_section')) {
    function get_section($key)
    {
        $section = \App\Models\Section::where('section_key', $key)->first();
        return $section;
    }
}

if (!function_exists('image_delete')) {
    function image_delete($path, $old_image)
    {
        if ($old_image) {
            $oldPath = public_path($path . $old_image);
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }
    }
}

if (!function_exists('store_image')) {
    function store_image($file, $folder)
    {
        $file_name = time() . '_' . $file->getClientOriginalName();
        $file->move(public_path($folder), $file_name);
        return $file_name;
    }
}

if (!function_exists('section_keys_by_page')) {
    function section_keys_by_page(string $page)
    {
        return collect(config('sections'))
            ->filter(fn($section) => $section['page'] === $page)
            ->keys()
            ->values()
            ->toArray();
    }
}
