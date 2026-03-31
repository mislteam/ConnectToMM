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

    public int $inserted = 0;
    public int $updated  = 0;
    public int $skipped  = 0;

    public function model(array $row)
    {
        // Clean empty keys
        $row = array_filter($row, function ($value, $key) {
            return is_string($key) && trim($key) !== '';
        }, ARRAY_FILTER_USE_BOTH);

        // Normalize headers and values
        $normalize = array_combine(
            array_map(function ($key) {
                $key = preg_replace('/^\xEF\xBB\xBF/', '', $key);
                $key = preg_replace('/[^\p{L}\p{N}_]+/u', '_', $key);
                $key = preg_replace('/_+/', '_', $key);
                return strtolower(trim($key, '_ '));
            }, array_keys($row)),
            array_map(function ($value) {
                return $value === null ? '' : (is_string($value) ? trim($value) : $value);
            }, $row)
        );

        $expected = ['category_name', 'product_name', 'usage_location', 'supplier', 'product_type', 'plan'];
        $data = [];
        foreach ($expected as $k) {
            $data[$k] = $normalize[$k] ?? '';
        }

        // Skip empty row
        if (collect($data)->filter()->isEmpty()) return null;

        // Validate basic fields
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

        $planString = $data['plan'];
        if (!is_string($planString)) {
            throw new \Exception("Plan column must be a valid JSON string.");
        }

        $planData = json_decode($planString, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Invalid plan JSON for product {$data['product_name']}: " . json_last_error_msg());
        }

        if (!is_array($planData) || array_keys($planData) !== range(0, count($planData) - 1)) {
            throw new \Exception("Plan must be a JSON array of objects for product {$data['product_name']}");
        }

        // Normalize plan codes
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
            $plan['code_status'] = isset($plan['code_status']) && $plan['code_status'] !== '' ? (int)$plan['code_status'] : 1;
            if (!in_array($plan['code_status'], [0, 1], true)) {
                throw new \Exception("Product code_status must be 0 or 1 for product {$data['product_name']}");
            }

            $required = ['product_code', 'data', 'service_day', 'traffic_type', 'price_cny', 'network_type', 'description', 'expiration_date'];
            foreach ($required as $field) {
                if (!isset($plan[$field]) || trim((string)$plan[$field]) === '') {
                    throw new \Exception("Plan row #" . ($i + 1) . " missing {$field} for product {$data['product_name']}");
                }
            }

            if (!in_array($plan['traffic_type'], ['Daily Type', 'Total Type', 'Unlimited Type'])) {
                throw new \Exception("Invalid traffic type in plan row #" . ($i + 1) . " for product {$data['product_name']}");
            }

            if (!is_numeric($plan['price_cny'])) {
                throw new \Exception("Price must be numeric in plan row #" . ($i + 1) . " for product {$data['product_name']}");
            }

            $plan['price_cny'] = (float)$plan['price_cny'];
            $code = $plan['product_code'];

            // Track seen codes for duplicates in the same file
            if (in_array($code, $seenCodes)) {
                throw new \Exception("Duplicate product_code {$code} in import for product {$data['product_name']}");
            }
            $seenCodes[] = $code;
        }

        // Prepare locations
        $locations = array_map('trim', explode(',', $data['usage_location']));
        $existingLocations = JoyUsageLocation::pluck('location')->all();
        foreach (collect($locations)->unique() as $location) {
            if (!in_array($location, $existingLocations)) {
                JoyUsageLocation::create(['location' => $location, 'status' => 1]);
                $existingLocations[] = $location;
            }
        }

        // Check if product exists
        $existingProduct = Joytel::where('product_name', $data['product_name'])->first();

        if ($existingProduct) {
            $needsUpdate = false;

            // Compare simple fields
            foreach (['category_name', 'product_type', 'supplier'] as $field) {
                if ($existingProduct->$field != $data[$field]) {
                    $needsUpdate = true;
                    break;
                }
            }

            // Compare usage_location array
            if (!$needsUpdate && $existingProduct->usage_location != $locations) {
                $needsUpdate = true;
            }

            // Compare plan JSON
            if (!$needsUpdate) {
                $existingPlan = $existingProduct->plan ?? [];
                if ($existingPlan != $planData) {
                    $needsUpdate = true;
                }
            }

            if ($needsUpdate) {
                $existingProduct->update([
                    'category_name'  => $data['category_name'],
                    'usage_location' => $locations,
                    'supplier'       => $data['supplier'],
                    'product_type'   => $data['product_type'],
                    'plan'           => $planData,
                ]);
                $this->updated++;
            } else {
                $this->skipped++;
            }

            return null; // skip creating new
        }

        // Insert new product
        $this->inserted++;
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
