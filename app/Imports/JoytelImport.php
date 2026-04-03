<?php

namespace App\Imports;

use App\Models\Joytel;
use App\Models\JoyUsageLocation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;

class JoytelImport implements ToCollection, WithHeadingRow, SkipsOnFailure
{
    use SkipsFailures;

    public int $inserted = 0;
    public int $updated  = 0;
    public int $skipped  = 0;

    protected array $expectedColumns = [
        'category_name',
        'product_name',
        'usage_location',
        'supplier',
        'product_type',
        'data',
        'service_day',
        'traffic_type',
        'price_cny',
        'network_type',
        'description',
        'product_code',
        'remark',
        'expiration_date',
        'code_status'
    ];

    public function collection(Collection $rows)
    {
        // Validate heading first
        if ($rows->isEmpty()) {
            throw new \Exception("Excel file is empty.");
        }

        $firstRow = $rows->first()->keys()->toArray();

        // Normalize header names to avoid numeric keys
        $firstRow = array_map(fn($col) => is_string($col) ? trim($col) : (string)$col, $firstRow);

        // Check missing and unexpected columns
        $missingColumns = array_diff($this->expectedColumns, $firstRow);
        $extraColumns   = array_diff($firstRow, $this->expectedColumns);

        if ($missingColumns || $extraColumns) {
            $messages = [];
            if ($missingColumns) {
                $messages[] = "Missing columns: " . implode(', ', $missingColumns);
            }
            if ($extraColumns) {
                $messages[] = "Unexpected columns: " . implode(', ', $extraColumns);
            }

            // Limit the output to first 5 unexpected columns to avoid huge dumps
            if ($extraColumns && count($extraColumns) > 5) {
                $extra = array_slice($extraColumns, 0, 5);
                $messages[count($messages) - 1] = "Unexpected columns: " . implode(', ', $extra) . ", ...";
            }

            throw new \Exception("Excel column validation failed. " . implode('; ', $messages));
        }

        $buffer = [];
        $seenCodes = [];
        $rowNumber = 1;

        //  Validate & buffer all rows first
        foreach ($rows as $row) {
            $rowNumber++;

            // Skip completely empty rows
            if (empty(array_filter($row->toArray()))) {
                continue;
            }

            // Skip rows without product_name
            if (empty(trim($row['product_name'] ?? ''))) {
                continue;
            }

            // convert row to array by index
            $rowData = [
                'category_name'   => trim($row['category_name'] ?? ''),
                'product_name'    => trim($row['product_name'] ?? ''),
                'usage_location'  => trim($row['usage_location'] ?? ''),
                'supplier'        => trim($row['supplier'] ?? ''),
                'product_type'    => trim($row['product_type'] ?? ''),
                'data'            => trim($row['data'] ?? ''),
                'service_day'     => trim($row['service_day'] ?? ''),
                'traffic_type'    => trim($row['traffic_type'] ?? ''),
                'price_cny'       => $row['price_cny'] ?? '',
                'network_type'    => trim($row['network_type'] ?? ''),
                'description'     => trim($row['description'] ?? ''),
                'product_code'    => trim($row['product_code'] ?? ''),
                'remark'          => trim($row['remark'] ?? ''),
                'expiration_date' => $row['expiration_date'] ?? '',
                'code_status'     => $row['code_status'] ?? 1,
            ];

            $this->validateProductType($rowData, $rowNumber);

            $validator = Validator::make($rowData, [
                'category_name'  => 'required|string',
                'product_name'   => 'required|string',
                'usage_location' => 'required|string',
                'supplier'       => 'required|string',
                'product_type'   => 'required|string',
                'data'           => 'required|string',
                'service_day'    => 'required|string',
                'traffic_type'   => 'required|in:Daily Type,Total Type,Unlimited Type',
                'price_cny'      => 'required|numeric',
                'network_type'   => 'required|string',
                'description'    => 'required|string',
                'product_code'   => 'required|string',
                'remark'         => 'nullable|string',
                'expiration_date' => 'required',
                'code_status'    => 'nullable|in:0,1',
            ]);

            if ($validator->fails()) {
                throw new \Exception(
                    "Row {$rowNumber}: " .
                        implode(', ', $validator->errors()->all())
                );
            }

            // duplicate code inside excel
            if (in_array($rowData['product_code'], $seenCodes)) {
                throw new \Exception(
                    "Row {$rowNumber}: Duplicate product_code {$rowData['product_code']}"
                );
            }

            $seenCodes[] = $rowData['product_code'];

            $buffer[] = $rowData;
        }

        // Insert/Update DB inside a transaction ---
        DB::transaction(function () use ($buffer) {

            $grouped = collect($buffer)->groupBy('product_name');

            foreach ($grouped as $productName => $items) {

                $first = $items->first();

                // Split usage_location into array
                $locations = array_map('trim', explode(',', $first['usage_location']));

                // Prepare plan array
                $plans = $items->map(function ($row) {
                    return [
                        'data'            => $row['data'],
                        'service_day'     => $row['service_day'],
                        'traffic_type'    => $row['traffic_type'],
                        'price_cny'       => (float)$row['price_cny'],
                        'network_type'    => $row['network_type'],
                        'description'     => $row['description'],
                        'product_code'    => $row['product_code'],
                        'remark'          => $row['remark'] ?? '',
                        'expiration_date' => $row['expiration_date'],
                        'code_status'     => $row['code_status'] ?? 1,
                    ];
                })->toArray();

                // Insert new usage locations if not exist
                foreach ($locations as $loc) {
                    JoyUsageLocation::firstOrCreate(['location' => $loc], ['status' => 1]);
                }

                // Check if product exists
                $existing = Joytel::where('product_name', $productName)->first();

                if ($existing) {

                    $existingPlans = collect($existing->plan)->keyBy('product_code');
                    foreach ($plans as $plan) {
                        $existingPlans[$plan['product_code']] = $plan;
                    }

                    $mergedPlans = $existingPlans->values()->toArray();

                    if (
                        json_encode($mergedPlans) != json_encode($existing->plan) ||
                        json_encode($locations) != json_encode($existing->usage_location)
                    ) {
                        $existing->update([
                            'category_name'  => $first['category_name'],
                            'usage_location' => $locations,
                            'supplier'       => $first['supplier'],
                            'product_type'   => $first['product_type'],
                            'plan'           => $mergedPlans,
                        ]);
                        $this->updated++;
                    } else {
                        $this->skipped++;
                    }
                } else {
                    Joytel::create([
                        'category_name'  => $first['category_name'],
                        'product_name'   => $productName,
                        'usage_location' => $locations,
                        'supplier'       => $first['supplier'],
                        'product_type'   => $first['product_type'],
                        'plan'           => $plans,
                    ]);
                    $this->inserted++;
                }
            }
        });
    }

    /**
     * Child classes override this
     */
    protected function validateProductType($row, $rowNumber)
    {
        // default: allow all
    }
}
