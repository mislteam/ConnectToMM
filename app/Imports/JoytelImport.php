<?php

namespace App\Imports;

use App\Models\JoytelEsim;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\JoytelCoupon;
use App\Models\PriceList;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class JoytelImport implements WithMultipleSheets, SkipsOnFailure, SkipsUnknownSheets
{
    use SkipsFailures;

    public int $inserted = 0;
    public int $updated  = 0;
    public int $skipped  = 0;

    protected string $modelClass = JoytelEsim::class;

    public function sheets(): array
    {
        return [
            0 => new JoytelSheetImport($this),
            1 => new JoytelSheetImport($this),
        ];
    }

    public function onUnknownSheet($sheetName) {}

    protected array $expectedColumns = [
        'product name' => ['productname', 'product_name'],
        'price' => ['price'],
        'code' => ['code'],
        'coverage' => ['coverage'],
        'type' => ['type'],
        'product description' => ['product_description'],
        'memo' => ['memo'],
        'activation type' => ['activation_type'],
        'provider' => ['provider'],
        'network' => ['network'],
        'hotspot' => ['hotspot'],
        'recharge' => ['recharge'],
    ];

    public function importRows(Collection $rows)
    {
        if ($rows->isEmpty()) {
            return;
        }

        $firstRow = array_map(fn($col) => is_string($col) ? trim($col) : (string) $col, $rows->first()->keys()->toArray());
        $missingColumns = collect($this->expectedColumns)
            ->filter(fn($aliases) => empty(array_intersect($aliases, $firstRow)))
            ->keys()
            ->toArray();

        if ($missingColumns) {
            throw new \Exception('Excel column validation failed. Missing columns: ' . implode(', ', $missingColumns));
        }

        $buffer = [];
        $seenCodes = [];
        $validationErrors = [];
        $invalidRows = [];
        $rowNumber = 1;

        foreach ($rows as $row) {
            $rowNumber++;

            if (empty(array_filter($row->toArray(), fn($value) => $value !== null && trim((string) $value) !== ''))) {
                continue;
            }

            $originalProductName = trim((string) $this->value($row, ['productname', 'product_name']));

            if ($originalProductName === '') {
                $this->addValidationError($validationErrors, $rowNumber, 'Missing product name');
                continue;
            }

            $parsedProductName = parseProductName($originalProductName);

            $rowData = [
                'product_name' => $parsedProductName['product_name'],
                'data' => $parsedProductName['data'],
                'traffic_type' => $parsedProductName['traffic_type'],
                'service_day' => $parsedProductName['service_day'],
                'price' => $this->value($row, ['price']),
                'code' => trim((string) $this->value($row, ['code'])),
                'coverage' => $this->parseCoverage($this->value($row, ['coverage'])),
                'type' => trim((string) $this->value($row, ['type'])),
                'product_description' => trim((string) $this->value($row, ['product_description'])),
                'memo' => trim((string) $this->value($row, ['memo'])),
                'activation_type' => trim((string) $this->value($row, ['activation_type'])),
                'provider' => trim((string) $this->value($row, ['provider'])),
                'network' => trim((string) $this->value($row, ['network'])),
                'hotspot' => trim((string) $this->value($row, ['hotspot'])),
                'recharge' => trim((string) $this->value($row, ['recharge'])),
                'status' => 1,
            ];

            // $missingParsedFields = $this->missingParsedFields($rowData);

            // if ($missingParsedFields) {
            //     $validationErrors[] = "Row {$rowNumber}: Missing " . implode(', ', $missingParsedFields);
            //     continue;
            // }

            $missingParsedFields = $this->missingParsedFields($rowData);

            if ($missingParsedFields) {
                $invalidRows[] = $rowNumber;
                continue;
            }

            try {
                $this->validateProductType($rowData, $rowNumber);
            } catch (\Exception $e) {
                $this->addValidationError(
                    $validationErrors,
                    $rowNumber,
                    $this->normalizeRowErrorMessage($e->getMessage())
                );
                continue;
            }

            $validator = Validator::make($rowData, [
                'product_name' => 'required|string',
                'data' => 'required|string',
                'traffic_type' => 'required|in:daily,unlimited,total',
                'service_day' => 'required|string',
                'price' => 'required|numeric',
                'code' => 'required|string',
                'coverage' => 'required|array|min:1',
                'coverage.*' => 'required|string',
                'type' => 'required|string',
                'product_description' => 'required|string',
                'memo' => 'nullable|string',
                'activation_type' => 'nullable|string',
                'provider' => 'required|string',
                'network' => 'required|string',
                'hotspot' => 'required|string',
                'recharge' => 'required|string',
                'status' => 'required|in:0,1',
            ]);

            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $message) {
                    $this->addValidationError($validationErrors, $rowNumber, $message);
                }
                continue;
            }

            if (in_array($rowData['code'], $seenCodes, true)) {
                $this->addValidationError($validationErrors, $rowNumber, "Duplicate code {$rowData['code']}");
                continue;
            }

            $seenCodes[] = $rowData['code'];
            $buffer[] = $rowData;
        }

        if (!empty($invalidRows)) {
            throw new \Exception('Invalid package format detected in rows: ' . $this->formatRows($invalidRows));
        }


        if ($validationErrors) {
            throw new \Exception($this->formatValidationErrors($validationErrors));
        }

        // DB::transaction(function () use ($buffer) {
        //     foreach ($buffer as $row) {
        //         /** @var class-string<Model> $modelClass */
        //         $modelClass = $this->modelClass;
        //         $existing = $modelClass::where('code', $row['code'])->first();

        //         if ($existing) {
        //             $dirty = collect($row)->contains(fn($value, $key) => $existing->{$key} != $value);

        //             if ($dirty) {
        //                 $existing->update($row);
        //                 $this->updated++;
        //             } else {
        //                 $this->skipped++;
        //             }

        //             continue;
        //         }

        //         $modelClass::create($row);
        //         $this->inserted++;
        //     }
        // });
        DB::transaction(function () use ($buffer) {
            foreach ($buffer as $row) {
                /** @var class-string<Model> $modelClass */
                $modelClass = $this->modelClass;

                $existing = $modelClass::where('code', $row['code'])
                    ->lockForUpdate()
                    ->first();

                if ($existing) {
                    $oldProductName = $existing->product_name ?? null;
                    $newProductName = $row['product_name'] ?? null;

                    $existing->fill($row);

                    if (! $existing->isDirty()) {
                        $this->skipped++;
                        continue;
                    }

                    $productNameChanged = $oldProductName
                        && $newProductName
                        && $oldProductName !== $newProductName;

                    $existing->save();

                    if ($productNameChanged) {
                        $this->syncProductNameReferences(
                            productCode: $row['code'],
                            oldProductName: $oldProductName,
                            newProductName: $newProductName
                        );
                    }

                    $this->updated++;
                    continue;
                }

                $modelClass::create($row);
                $this->inserted++;
            }
        });
    }

    private function syncProductNameReferences(string $productCode, string $oldProductName, string $newProductName): void
    {
        // 1. PriceList update
        PriceList::where('product_code', $productCode)
            ->where('plan', $oldProductName)
            ->update([
                'plan' => $newProductName,
            ]);

        // 2. Coupon JSON product_names update
        JoytelCoupon::whereJsonContains('product_names', $oldProductName)
            ->chunkById(100, function ($coupons) use ($oldProductName, $newProductName) {
                foreach ($coupons as $coupon) {
                    $productNames = $coupon->product_names;

                    if (! is_array($productNames)) {
                        $productNames = json_decode($productNames, true) ?: [];
                    }

                    // ["All"] ဆိုရင် product name update မလိုဘူး
                    if (in_array('All', $productNames, true)) {
                        continue;
                    }

                    $changed = false;

                    foreach ($productNames as $index => $productName) {
                        if ($productName === $oldProductName) {
                            $productNames[$index] = $newProductName;
                            $changed = true;
                        }
                    }

                    if ($changed) {
                        $coupon->update([
                            'product_names' => array_values($productNames),
                        ]);
                    }
                }
            });
    }

    protected function validateProductType($row, $rowNumber)
    {
        // Child imports can restrict eSIM/recharge here.
    }

    private function missingParsedFields(array $rowData): array
    {
        $fields = [
            'data' => 'data',
            'traffic_type' => 'traffic type',
            'service_day' => 'service day',
        ];

        return collect($fields)
            ->filter(fn($label, $field) => trim((string) ($rowData[$field] ?? '')) === '')
            ->values()
            ->toArray();
    }

    private function addValidationError(array &$errors, int $rowNumber, string $message): void
    {
        $message = trim($message);

        if ($message === '') {
            $message = 'Invalid row data';
        }

        $key = mb_strtolower($message);

        if (!isset($errors[$key])) {
            $errors[$key] = [
                'message' => $message,
                'rows' => [],
            ];
        }

        $errors[$key]['rows'][] = $rowNumber;
    }

    private function normalizeRowErrorMessage(string $message): string
    {
        return trim(preg_replace('/^Row\s+\d+\s*:\s*/i', '', $message));
    }

    private function formatValidationErrors(array $errors): string
    {
        $requiredRows = [];
        $messages = [];

        foreach ($errors as $error) {

            if (str_contains($error['message'], 'field is required')) {

                $requiredRows = array_merge($requiredRows, $error['rows']);
            } else {

                $messages[] = $this->formatRows($error['rows']) . ': ' . $error['message'];
            }
        }

        if (!empty($requiredRows)) {

            $requiredRows = array_unique($requiredRows);
            sort($requiredRows);

            $messages[] = $this->formatRows($requiredRows) . ': Missing required fields.';
        }

        return implode(' | ', $messages);
    }

    private function formatRows(array $rows): string
    {
        $rows = collect($rows)
            ->map(fn($row) => (int) $row)
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        if (empty($rows)) {
            return 'Rows';
        }

        $ranges = [];
        $start = $rows[0];
        $previous = $rows[0];

        foreach (array_slice($rows, 1) as $row) {
            if ($row === $previous + 1) {
                $previous = $row;
                continue;
            }

            $ranges[] = $start === $previous ? (string) $start : "{$start}-{$previous}";
            $start = $previous = $row;
        }

        $ranges[] = $start === $previous ? (string) $start : "{$start}-{$previous}";

        return (count($rows) === 1 ? 'Row ' : 'Rows ') . implode(', ', $ranges);
    }

    private function value($row, array $keys)
    {
        foreach ($keys as $key) {
            if (isset($row[$key])) {
                return $row[$key];
            }
        }

        return null;
    }

    private function parseCoverage($coverage): ?array
    {
        if (is_array($coverage)) {
            $locations = array_values(array_filter(array_map('trim', $coverage)));

            return $locations ?: null;
        }

        $coverage = trim((string) $coverage);

        if ($coverage === '') {
            return null;
        }

        if (str_starts_with($coverage, '[')) {
            $decoded = json_decode($coverage, true);

            if (is_array($decoded)) {
                $locations = array_values(array_filter(array_map('trim', $decoded)));

                return $locations ?: null;
            }
        }

        // $coverage = preg_replace('/^\s*\d+\s*destinations?\s*(?:\:|\x{FF1A})\s*/iu', '', $coverage);
        $coverage = str_replace(':', ', ', $coverage);

        $locations = collect(explode(',', $coverage))
            ->map(fn($location) => trim($location))
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        return $locations ?: null;
    }
}

class JoytelSheetImport implements ToCollection, WithHeadingRow
{
    public function __construct(private JoytelImport $import) {}

    public function collection(Collection $rows)
    {
        $this->import->importRows($rows);
    }
}
