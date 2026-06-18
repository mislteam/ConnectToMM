<?php

namespace App\Imports;

class JoytelEsimImport extends JoytelImport
{
    protected function validateProductType($row, $rowNumber)
    {
        if (stripos($row['type'], 'esim') === false) {
            throw new \Exception(
                "Row {$rowNumber}: This import is for eSIM only. Recharge product detected."
            );
        }
    }
}
