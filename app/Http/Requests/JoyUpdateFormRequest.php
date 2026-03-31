<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use App\Models\Joytel;

class JoyUpdateFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cat_name' => 'required|string|max:255',
            'product_name' => 'required|string|max:255',
            'supplier' => 'required|string|max:255',
            'product_type' => 'required|string|max:255',
            'locations' => 'required|array|min:1',
            'locations.*' => 'string',
            'activation_policy' => 'nullable|string|max:255',
            'del_time' => 'nullable|string|max:255',
            'status' => 'required|in:0,1',
            // existing photos kept as strings
            'old_photos' => 'nullable|array',
            'old_photos.*' => 'string',

            // files may contain new uploads; validate manually in withValidator()
            'files' => 'nullable|array',
            'files.*' => 'nullable',

            'esim_id' => 'nullable|integer',

            'rows_json' => ['required', 'json', function ($attribute, $value, $fail) {
                $rows = json_decode($value, true);

                if (!is_array($rows) || empty($rows)) {
                    $fail('Rows JSON must be a non-empty array.');
                    return;
                }

                $productCodes = [];

                // route testing esim or physical
                // $id = $this->route('esim')?->id;
                $id = optional($this->route('esim'))->id ?? optional($this->route('recharge'))->id;

                $existingProductCodes = Joytel::when($id, fn($q) => $q->where('id', '!=', $id))
                    ->pluck('plan')
                    ->map(function ($plan) {
                        $planArray = is_string($plan) ? json_decode($plan, true) : $plan;
                        return collect($planArray)
                            ->pluck('product_code')
                            ->map(fn($code) => trim((string)$code))
                            ->filter()
                            ->all();
                    })
                    ->flatten()
                    ->all();

                foreach ($rows as $index => $row) {
                    $rowNumber = $index + 1;
                    $requiredFields = [
                        'product_code'    => 'Product code',
                        'data'            => 'Data',
                        'service_day'     => 'Service day',
                        'traffic_type'    => 'Traffic type',
                        'price_cny'       => 'Price CNY',
                        'network_type'    => 'Network type',
                        'expiration_date' => 'Expiration date',
                        'description'     => 'Description',
                    ];

                    // Required field check
                    foreach ($requiredFields as $key => $label) {
                        if (!isset($row[$key]) || $row[$key] === null || trim((string)$row[$key]) === '') {
                            $fail("Row #{$rowNumber} must have {$label}.");
                        }
                    }

                    // service day
                    // $serviceDay = strtolower(trim($row['service_day']));
                    // if (!preg_match('/^(day|([1-9]|[12][0-9]|30)\s*days?|([1-9]|[12][0-9]|30)day)$/', $serviceDay)) {
                    //     throw new \Exception("Row plan #{$rowNumber} has invalid Service day format: {$row['service_day']}");
                    // }

                    $rowCode = trim((string)$row['product_code']);

                    // Duplicate in current submission
                    if (in_array($rowCode, $productCodes)) {
                        $fail("Duplicate product_code in current submission: {$rowCode}");
                    }

                    // Duplicate in DB excluding current eSIM
                    if (in_array($rowCode, $existingProductCodes)) {
                        $fail("product_code already exists in database: {$rowCode}");
                    }

                    $productCodes[] = $rowCode;
                }
            }]
        ];
    }


    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $uploadedFiles = $this->file('files', []);

            if (!empty($uploadedFiles) && is_array($uploadedFiles)) {
                foreach ($uploadedFiles as $index => $file) {

                    if ($file === null) {
                        continue;
                    }

                    if (!is_object($file) || !method_exists($file, 'getClientOriginalName')) {
                        continue;
                    }

                    $single = Validator::make(
                        ['f' => $file],
                        ['f' => 'image|mimes:jpg,jpeg,png|max:2048'],
                        [],
                        ['f' => "files.{$index}"]
                    );

                    if ($single->fails()) {
                        foreach ($single->errors()->get('f') as $msg) {
                            $validator->errors()->add("files.{$index}", $msg);
                        }
                    }
                }
            }
        });
    }
}
