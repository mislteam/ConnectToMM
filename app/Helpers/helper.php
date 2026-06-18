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

if (!function_exists('parseProductName')) {

    function parseProductName($name)
    {
        $name = trim((string) $name);

        $result = [
            'product_name' => $name,
            'data' => null,
            'traffic_type' => null,
            'service_day' => null,
        ];

    //     if (preg_match('/(\d+)\s*[-]?\s*days?/i', $name, $dayMatch)) {

    //         $result['service_day'] = (int)$dayMatch[1] . 'day';
    //     }

    //      // /day style (500MB/day → daily plan)
    //    if (preg_match('/\/\s*day\b/i', $name)) {
    //         $result['service_day'] = 'day';
    //     }

    //     // (Charge from 3 days)
    //    if (preg_match('/\(([^)]*Charge\s*from\s*[^)]*)\)/i', $name, $cMatch)) {
    //         $result['service_day'] = '(' . trim($cMatch[1]) . ')';
    //     }

    if (preg_match('/(\d+)\s*[-]?\s*days?/i', $name, $dayMatch)) {

        $result['service_day'] = (int)$dayMatch[1] . 'day';

    } elseif (preg_match('/\/\s*day\b/i', $name)) {

        $result['service_day'] = 'day';

    }
    
    if (preg_match('/\(([^)]*Charge\s*from\s*[^)]*)\)/i', $name, $cMatch)) {

        $result['service_day'] = '(' . trim($cMatch[1]) . ')';
    }

        

        // 500MB / 22GB / 500MB/day
        if (preg_match('/(\d+(?:\.\d+)?)\s*(MB|GB)(?:\s*\/\s*day)?/i',$name,$dataMatch)) {

            $result['data'] =
                $dataMatch[1] . strtoupper($dataMatch[2]);

        }elseif (preg_match('/unlimited\s+data/i', $name)) {

            $result['data'] = 'Unlimited Data';
            
        }elseif (preg_match('/\bunlimited(?:\s*\/\s*day)?\b/i', $name)) {

            $result['data'] = 'Unlimited';

        }elseif (preg_match('/\b(\d+\s*MAX|MAX)\b/i', $name, $maxMatch)) {

            $result['data'] = 'Unlimited (' . strtoupper(str_replace(' ', '', $maxMatch[1])) . ')';

        }elseif (preg_match('/full\s+(?:unlimited|speed)/i', $name)) {

            $result['data'] = 'Unlimited';

        }

        // data
        // if (preg_match('/(\d+(?:\.\d+)?)\s*(MB|GB)(?:\s*\/\s*day)?/i', $name, $dataMatch)) {

        //     $result['data'] = $dataMatch[1] . strtoupper($dataMatch[2]);

        // } elseif (preg_match('/unlimited\s+data/i', $name)) {

        //     $result['data'] = 'Unlimited Data';

        // } elseif (preg_match('/\((\d*MAX)\)/i', $name, $maxMatch)) {

        //     $maxValue = strtoupper($maxMatch[1]);

        //     $result['data'] = 'Unlimited (' . $maxValue . ')';

        // }

        // if (preg_match('/\/\s*day|days|daily/i', $name)) {
        //     $result['traffic_type'] = 'daily';
        // } elseif (preg_match('/unlimited|MAX|max/i', $name)) {
        //     $result['traffic_type'] = 'unlimited';
        // } elseif (preg_match('/total/i', $name)) {


        //     $result['traffic_type'] = 'total';
        // }

        if (preg_match('/\btotal\b/i', $name)) {

            $result['traffic_type'] = 'total';

        } elseif (preg_match('/\bunlimited\b|\bmax\b|MAX|\bspeed\b/i', $name)) {

            $result['traffic_type'] = 'unlimited';

        } elseif (preg_match('/\/\s*day|daily|DAY/i', $name)) {

            $result['traffic_type'] = 'daily';
        }
                

        $cleanName = preg_replace([
             '/^\s*\[[^\]]+\]\s*/i',
            '/\s*-\s*\d+\s*days?\b.*$/i',
            '/\s*-\s*(?:Total\s+\d+(?:\.\d+)?\s*(?:MB|GB)|\d+\s*(?:MB|GB|MAX)?\s*\/\s*day|Unlimited\s*\/\s*day|\d+\s*days?)\b.*$/i',
            '/\s*\([^)]*(?:\d+(?:\.\d+)?\s*(?:MB|GB)|unlimited\s+data|total|\/\s*day)[^)]*\)\s*$/i',
            '/\s*-\s*\([^)]*(?:\d+(?:\.\d+)?\s*(?:MB|GB)|unlimited\s+data|total|\/\s*day)[^)]*\)\s*$/i',
            '/\s*\([^)]*(?:MB|GB|MAX|day|daily|charge|total)[^)]*\)\s*/i',
        ], '', $name);

        $result['product_name'] = trim($cleanName, " \t\n\r\0\x0B-");

        return $result;
    }
}
