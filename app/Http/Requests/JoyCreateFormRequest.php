<?php

namespace App\Http\Requests;

use App\Models\Joytel;
use Illuminate\Foundation\Http\FormRequest;

class JoyCreateFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'cat_name' => 'required|string|max:255',
            'product_name' => 'required|string|max:255|unique:joytels,product_name',
            'supplier' => 'required|string|max:255',
            'product_type' => 'required|string|max:255',
            'locations' => 'required|array|min:1',
            'locations.*' => 'string',
            'activation_policy' => 'nullable|string|max:255',
            'del_time' => 'nullable|string|max:255',
            'files' => 'nullable|array',
            'files.*' => 'image|mimes:jpg,jpeg,png|max:2048',
            'status' => 'required|string|in:0,1',
            'rows_json' => ['required', 'json', function ($attribute, $value, $fail) {
                $rows = json_decode($value, true);
                if (!is_array($rows) || empty($rows)) {
                    $fail('Rows JSON must be a non-empty array.');
                    return;
                }

                $productCodes = [];
                $existingProductCodes = Joytel::pluck('plan')->map(function ($plan) {
                    // if plan is already an array, just use it
                    $planArray = is_string($plan) ? json_decode($plan, true) : $plan;
                    return collect($planArray)->pluck('product_code')->all();
                })->flatten()->all();

                foreach ($rows as $index => $row) {
                    $rowNumber = $index + 1;
                    $requiredFields = [
                        'product_code'   => 'Product code',
                        'data'           => 'Data',
                        'service_day'    => 'Service day',
                        'traffic_type'   => 'Traffic type',
                        'price_cny'      => 'Price CNY',
                        'network_type'   => 'Network type',
                        'expiration_date' => 'Expiration date',
                        'description'    => 'Description',
                    ];

                    foreach ($requiredFields as $key => $label) {
                        if (!isset($row[$key]) || empty(trim($row[$key]))) {
                            $fail("Row #" . $rowNumber . " must have $label.");
                        }
                    }

                    // service day
                    // $serviceDay = strtolower(trim($row['service_day']));
                    // if (!preg_match('/^(day|([1-9]|[12][0-9]|30)\s*days?|([1-9]|[12][0-9]|30)day)$/', $serviceDay)) {
                    //     throw new \Exception("Row plan #{$rowNumber} has invalid Service day format: {$row['service_day']}");
                    // }

                    // Check duplicates in current submission
                    if (in_array($row['product_code'], $productCodes)) {
                        $fail("Duplicate product_code in current submission: " . $row['product_code']);
                    }

                    // Check duplicates against existing DB
                    if (in_array($row['product_code'], $existingProductCodes)) {
                        $fail("product_code already exists in database: " . $row['product_code']);
                    }

                    $productCodes[] = $row['product_code'];
                }
            }]
        ];
    }
}
