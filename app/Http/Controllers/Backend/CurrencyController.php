<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\GeneralSetting;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    public function index()
    {
        $currencies = Currency::where('name', '!=', 'profit')->get();
        $profit_amount = Currency::where('name', 'profit')->value('value');
        $profit_id = Currency::where('name', 'profit')->value('id');

        $data = $this->getData([
            "currencies" => $currencies,
            "profit_amount" => $profit_amount,
            "profit_id" => $profit_id
        ]);

        return view('admin.currency.index', $data);
    }

    public function edit(Currency $currency)
    {
        $data = $this->getData([
            'currency' => $currency
        ]);
        return view('admin.currency.edit', $data);
    }

    public function update(Currency $currency, Request $request)
    {
        $validated = $request->validate([
            'value' => 'required'
        ]);
        $currency->value = $validated['value'];
        $currency->save();
        return redirect()->route('currency.index')->with('success', 'Update successfully!');
    }

    private function getData(array $extra = [])
    {
        $baseData = [
            "logo" => GeneralSetting::where('type', 'file')->first(),
            "title" => GeneralSetting::where('type', 'string')->first()
        ];
        return array_merge($baseData, $extra);
    }
}
