<?php

namespace App\Imports;

use App\Models\JoytelPhysical;

class JoytelRechargeImport extends JoytelImport
{
    protected string $modelClass = JoytelPhysical::class;

    protected function validateProductType($row, $rowNumber)
    {
        if (stripos($row['type'], 'recharge') === false) {
            throw new \Exception(
                "Row {$rowNumber}: This import is for Recharge only. eSIM product detected."
            );
        }
    }
}
