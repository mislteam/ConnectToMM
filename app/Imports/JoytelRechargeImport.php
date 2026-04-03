<?php

namespace App\Imports;

class JoytelRechargeImport extends JoytelImport
{
    protected function validateProductType($row, $rowNumber)
    {
        if (stripos($row['product_type'], 'recharge') === false) {
            throw new \Exception(
                "Row {$rowNumber}: This import is for Recharge only. eSIM product detected."
            );
        }
    }
}
