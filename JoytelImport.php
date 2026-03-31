<?php

namespace App\Imports;

use App\Models\Joytel;
use App\Models\JoyUsageLocation;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;

class JoytelImport implements ToModel, WithHeadingRow, SkipsOnFailure
{
    use SkipsFailures;

    public function model(array $row)
    {
        $row = array_filter($row, function ($value, $key) {
            return is_string($key) && trim($key) !== '';
        }, ARRAY_FILTER_USE_BOTH); // get both key + value

        /*
        $newArray = [
            CLEANED_KEY => CLEANED_VALUE
        ];
        e.g, "category_name" => "eSIM"
        */
        $normalize = array_combine(
            array_map(function ($key) { // for key (category_name, product_name, etc...)
                $key = preg_replace('/^\xEF\xBB\xBF/', '', $key);
                $key = preg_replace('/[^\p{L}\p{N}_]+/u', '_', $key);
                $key = preg_replace('/_+/', '_', $key);
                return strtolower(trim($key, '_ '));
            }, array_keys($row)),
            array_map(function ($value) { // for value => $value = eSIM (category_name)
                return $value === null ? '' : (is_string($value) ? trim($value) : $value);
            }, $row)
        );

        // db expected keys from excel
        $expected = [
            'category_name',
            'product_name',
            'usage_location',
            'supplier',
            'product_type',
            'plan'
        ];

        $data = [];
        foreach ($expected as $k) {
            $data[$k] = $normalize[$k] ?? '';
        }

        // skip completely empty rows
        if (collect($data)->filter()->isEmpty()) {
            return null;
        }

        // validations
        $validator = Validator::make($data, [
            'category_name'  => 'required|string|max:255',
            'product_name'   => 'required|string|max:255',
            'usage_location' => 'required',
            'supplier'       => 'required|string',
            'product_type'   => 'required|string',
            'plan'           => 'required',
        ]);

        if ($validator->fails()) {
            throw new \Exception(implode('; ', $validator->errors()->all()));
        }

        // for product name
        static $seenProductNames = [];

        if (
            in_array($data['product_name'], $seenProductNames) ||
            Joytel::where('product_name', $data['product_name'])->exists()
        ) {
            return null;
        }

        $seenProductNames[] = $data['product_name'];

        // for plan
        $planString = $data['plan'];

        if (!is_string($planString)) {
            throw new \Exception("Plan column must be a valid JSON string.");
        }

        $planData = json_decode($planString, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Invalid plan JSON: " . json_last_error_msg());
        }

        if (!is_array($planData) || array_keys($planData) !== range(0, count($planData) - 1)) {
            throw new \Exception("Plan must be a JSON array of objects.");
        }

        static $existingProductCodes = null;

        if ($existingProductCodes === null) {
            $existingProductCodes = Joytel::pluck('plan')
                ->map(function ($plan) {
                    $decoded = is_string($plan) ? json_decode($plan, true) : $plan;
                    return collect($decoded ?: [])->pluck('product_code')->all();
                })
                ->flatten()
                ->all();
        }

        $seenCodes = [];

        foreach ($planData as $i => &$plan) {

            $plan['code_status'] = isset($plan['code_status']) && $plan['code_status'] !== ''
                ? (int) $plan['code_status']
                : 1;

            if (!in_array($plan['code_status'], [0, 1], true)) {
                throw new \Exception("Product code_status must be 0 or 1.");
            }

            $required = [
                'product_code',
                'data',
                'service_day',
                'traffic_type',
                'price_cny',
                'network_type',
                'description',
                'expiration_date',
            ];

            foreach ($required as $field) {
                if (!isset($plan[$field]) || trim((string) $plan[$field]) === '') {
                    throw new \Exception("Plan row #" . ($i + 1) . " missing {$field}");
                }
            }

            if (!in_array($plan['traffic_type'], ['Daily Type', 'Total Type', 'Unlimited Type'])) {
                throw new \Exception("Invalid traffic type in plan row #" . ($i + 1));
            }

            if (!is_numeric($plan['price_cny'])) {
                throw new \Exception("Price must be numeric in plan row #" . ($i + 1));
            }

            $plan['price_cny'] = (float) $plan['price_cny'];
            $code = $plan['product_code'];

            if (
                in_array($code, $seenCodes) ||
                in_array($code, $existingProductCodes)
            ) {
                return null;
            }

            $seenCodes[] = $code;
            $existingProductCodes[] = $code;
        }

        $locations = array_map('trim', explode(',', $data['usage_location']));
        $existingLocations = JoyUsageLocation::pluck('location')->all();

        foreach (collect($locations)->unique() as $location) {
            if (!in_array($location, $existingLocations)) {
                JoyUsageLocation::create([
                    'location' => $location,
                    'status'   => 1
                ]);
                $existingLocations[] = $location;
            }
        }

        return new Joytel([
            'category_name'  => $data['category_name'],
            'product_name'   => $data['product_name'],
            'usage_location' => $locations,
            'supplier'       => $data['supplier'],
            'product_type'   => $data['product_type'],
            'plan'           => $planData,
        ]);
    }
}
