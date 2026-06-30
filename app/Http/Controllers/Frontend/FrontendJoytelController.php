<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\JoytelEsim;
use App\Models\JoytelPhysical;
use App\Models\JoyUsageLocation;
use App\Models\PriceList;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FrontendJoytelController extends Controller
{
    public function esimIndex()
    {
        return $this->esimSearchPage();
    }

    public function physicalIndex(Request $request)
    {
        return $this->physicalSearchPage($request);
    }

    private function esimSearchPage()
    {
        $usage_locations = JoyUsageLocation::where('status', 1)->pluck('location')->values();
        $section = Section::where('section_key', 'need_more_help')->first();

        $query = JoytelEsim::whereRaw('LOWER(type) LIKE ?', ['%esim%']);

        $query->whereIn('product_name', function ($subquery) {
            $subquery->select('plan')
                ->from('price_lists')
                ->whereNotNull('plan')
                ->where('exchange_rate', '>', 0);
        });

        $orderTabs = getOrderTypes('joytel_order_types', 'esim');
        $selectedSimType = collect($orderTabs)->keys()->first();

        $packages = $query->where('status', 1)
            ->get()
            ->unique('product_name');

        return view('frontend.joytel.esim.search', compact(
            'usage_locations',
            'packages',
            'section',
            'orderTabs',
            'selectedSimType'
        ));
    }

    private function physicalSearchPage($request)
    {
        $usage_locations = JoyUsageLocation::where('status', 1)->pluck('location')->values();
        $section = Section::where('section_key', 'need_more_help')->first();

        $query = JoytelPhysical::whereRaw('LOWER(type) LIKE ?', ['%recharge%']);


        $query->whereIn('product_name', function ($subquery) {
            $subquery->select('plan')
                ->from('price_lists')
                ->whereNotNull('plan')
                ->where('exchange_rate', '>', 0);
        });



        $packages = $query->where('status', 1)
            ->get()
            ->unique('product_name');

        $orderTabs = getOrderTypes('joytel_order_types', 'physical');
        $selectedSimType = collect($orderTabs)->keys()->first();

        return view('frontend.joytel.physical.search', compact(
            'usage_locations',
            'packages',
            'section',
            'orderTabs',
            'selectedSimType'
        ));
    }

    public function esimSearch(Request $request)
    {
        return $this->esimPackages($request);
    }

    public function physicalSearch(Request $request)
    {
        return $this->physicalPackages($request);
    }

    private function esimPackages(Request $request)
    {
        $simType = $this->normalizeSimType($request->input('type', session('sim_type', 'new_esim')));
        if (!in_array($simType, ['new_esim', 'recharge_esim'], true)) {
            $simType = 'new_esim';
        }

        session(['sim_type' => $simType]);

        $validated = $request->validate([
            'locations' => 'required|array',
            'locations.*' => 'string'
        ]);

        $query = JoytelEsim::whereRaw('LOWER(type) LIKE ?', ['%esim%']);

        foreach ($validated['locations'] as $location) {
            $query->where(function ($q) use ($location) {
                $q->whereJsonContains('coverage', $location)
                    ->orWhereRaw("JSON_SEARCH(coverage, 'one', ?) IS NOT NULL", [$location . '%']);
            });
        }

        $query->whereIn('product_name', function ($subquery) {
            $subquery->select('plan')
                ->from('price_lists')
                ->whereNotNull('plan')
                ->where('exchange_rate', '>', 0);
        });

        $packages = $query->where('status', 1)
            ->get()
            ->unique('product_name');

        return view('frontend.joytel.esim.packages', compact('packages'));
    }

    private function physicalPackages(Request $request)
    {
        $validated = $request->validate([
            'locations' => 'required|array',
            'locations.*' => 'string'
        ]);

        $query = JoytelPhysical::whereRaw('LOWER(type) LIKE ?', ['%recharge%']);

        foreach ($validated['locations'] as $location) {
            $query->where(function ($q) use ($location) {
                $q->whereJsonContains('coverage', $location)
                    ->orWhereRaw("JSON_SEARCH(coverage, 'one', ?) IS NOT NULL", [$location . '%']);
            });
        }

        $query->whereIn('product_name', function ($subquery) {
            $subquery->select('plan')
                ->from('price_lists')
                ->whereNotNull('plan')
                ->where('exchange_rate', '>', 0);
        });

        $packages = $query->where('status', 1)
            ->get()
            ->unique('product_name');

        return view('frontend.joytel.physical.packages', compact('packages'));
    }


    // jotel add to cart
    public function cart($joytel, Request $request)
    {
        $request->validate([
            'sday' => 'required',
            'sdata' => 'required',
            'display_price' => 'required',
            'qty' => 'required',
        ]);

        $joytel = $this->resolveJoytelProduct((int) $joytel, $request->input('joytel_type'));

        $cart = [
            'joytel' => $joytel->id,
            'joytel_type' => $joytel instanceof JoytelPhysical ? 'physical' : 'esim',
            'service_day' => $request->sday,
            'service_data' => $request->sdata,
            'qty' => $request->qty,
            'price' => $request->display_price
        ];

        $joytelCart = session()->get('joytel_cart', []);
        $joytelCart[] = $cart;
        session(['joytel_cart' => $joytelCart]);

        return view('frontend.joytel.cart', [
            'joytel' => $joytel,
            'service_day' => $request->sday,
            'service_data' => $request->sdata,
            'qty' => $request->qty,
            'price' => $request->display_price
        ]);
    }

    // joytel checkout
    public function checkout()
    {
        $cart = session('joytel_cart');
        if (!$cart) {
            return redirect()->back()->with('error', 'Cart is Empty!');
        }
        $joytel = $this->resolveJoytelProduct((int) $cart['joytel'], $cart['joytel_type'] ?? null);
        return view('frontend.joytel.check-out', [
            'joytel' => $joytel,
            'service_day' => $cart['service_day'],
            'service_data' => $cart['service_data'],
            'qty' => $cart['qty'],
            'price' => $cart['price']
        ]);
    }


    public function esimPackageView($id, Request $request)
    {
        $simType = $this->normalizeSimType($request->input('sim_type', session('sim_type', 'new_esim')));

        session(['sim_type' => $simType]);

        $joytel = JoytelEsim::findOrFail($id);

        $packages = JoytelEsim::where('product_name', $joytel->product_name)
            ->where('status', 1)
            ->get();

        $traffic_types = $packages->pluck('traffic_type')->unique()->values();

        $daily_types = $packages->where('traffic_type', 'daily')->values();
        $total_types = $packages->where('traffic_type', 'total')->values();
        $unlimited_types = $packages->where('traffic_type', 'unlimited')->values();

        $service_days = $packages->pluck('service_day')->unique();

        $validPlans = PriceList::where('exchange_rate', '>', 0)
            ->pluck('plan')
            ->filter()
            ->unique()
            ->toArray();

        $random_packages = JoytelEsim::where('status', 1)
            ->where('id', '!=', $joytel->product_name)
            ->whereIn('product_name', $validPlans)
            ->inRandomOrder()
            ->get()
            ->unique('product_name');

        $network_types = $packages->pluck('network')->unique();

        $price_lists = PriceList::latest()->get();

        $joytel_type_label = 'E-SIM';

        return view('frontend.joytel.esim.package-view', compact(
            'joytel',
            'packages',
            'traffic_types',
            'daily_types',
            'total_types',
            'unlimited_types',
            'service_days',
            'random_packages',
            'network_types',
            'price_lists',
            'joytel_type_label',
            'simType'
        ));
    }


    public function physicalPackageView($id)
    {
        $joytel = JoytelPhysical::findOrFail($id);

        $packages = JoytelPhysical::where('product_name', $joytel->product_name)
            ->where('status', 1)
            ->get();

        $traffic_types = $packages->pluck('traffic_type')->unique()->values();

        $daily_types = $packages->where('traffic_type', 'daily')->values();
        $total_types = $packages->where('traffic_type', 'total')->values();
        $unlimited_types = $packages->where('traffic_type', 'unlimited')->values();

        $service_days = $packages->pluck('service_day')->unique();

        $validPlans = PriceList::where('exchange_rate', '>', 0)
            ->pluck('plan')
            ->filter()
            ->unique()
            ->toArray();

        $random_packages = JoytelPhysical::where('status', 1)
            ->where('product_name', '!=', $joytel->product_name)
            ->whereIn('product_name', $validPlans)
            ->inRandomOrder()
            ->get()
            ->unique('product_name');

        $network_types = $packages->pluck('network')->unique();


        $price_lists = PriceList::latest()->get();

        $joytel_type_label = 'Physical SIM';

        return view('frontend.joytel.physical.package-view', compact(
            'joytel',
            'packages',
            'traffic_types',
            'daily_types',
            'total_types',
            'unlimited_types',
            'service_days',
            'random_packages',
            'network_types',
            'price_lists',
            'joytel_type_label'
        ));
    }


    private function normalizeSimType(?string $simType): string
    {
        $simType = strtolower(trim((string) $simType));

        if ($simType === '' || !in_array($simType, ['new_esim', 'recharge_esim', 'new_physical', 'recharge_physical'], true)) {
            return 'new_esim';
        }

        return $simType;
    }
}
