<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Joytel;
use App\Models\JoyUsageLocation;
use App\Models\PriceList;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FrontendJoytelController extends Controller
{
    // esim search page
    public function esimIndex()
    {
        return $this->renderSearchPage('frontend.joytel.esim.search');
    }

    // physical search page
    public function physicalIndex()
    {
        return $this->renderSearchPage('frontend.joytel.physical.search');
    }

    // esim search and show packages
    public function esimSearch(Request $request)
    {
        return $this->renderPackages('frontend.joytel.esim.packages', 'esim', $request);
    }

    // physical search and show packages
    public function physicalSearch(Request $request)
    {
        return $this->renderPackages('frontend.joytel.physical.packages', 'recharge', $request);
    }

    // show each package
    public function packageView(Joytel $joytel)
    {
        // Determine SIM type
        $type = $joytel->product_type;

        if (stripos($type, 'eSIM') !== false) {
            $joytel_type_label = 'E-SIM';
        } elseif (stripos($type, 'JOY SIM Recharge') !== false) {
            $joytel_type_label = 'Physical SIM';
        } else {
            $joytel_type_label = 'Unknown SIM Type';
        }

        $packages = collect($joytel->plan)->where('code_status', 1)->values();

        $traffic_types = $packages->pluck('traffic_type')->unique()->values();

        $daily_types = $packages->where("traffic_type", "Daily Type")->values();

        $product_type = $joytel->product_type;

        $daily_days = collect();
        $service_days = $daily_types->pluck('service_day')->unique();
        if (!$service_days->contains('day')) {
            $daily_days = $service_days->map(function ($day) {
                return (int)filter_var($day, FILTER_SANITIZE_NUMBER_INT);
            });
        };

        $validPlans = PriceList::pluck('plan')->toArray();

        $random_packages = Joytel::where('product_type', $product_type)
            ->where('status', 1)
            ->where('id', '!=', $joytel->id)
            ->whereIn('product_name', $validPlans)
            ->inRandomOrder()
            ->take(3)
            ->get();

        $total_types = $packages->where("traffic_type", "Total Type")->values();

        $unlimited_types = $packages->where("traffic_type", "Unlimited Type")->values();

        $network_types = $packages->pluck('network_type')->unique();
        $price_lists = PriceList::latest()->get();
        //$price_lists = PriceList::all()->keyBy('product_code');

        return view('frontend.joytel.package-view', compact(
            'joytel',
            'joytel_type_label',
            'daily_types',
            'total_types',
            'unlimited_types',
            'daily_days',
            'random_packages',
            'network_types',
            'traffic_types',
            'price_lists'
        ));
    }

    private function renderSearchPage($route)
    {
        $usage_locations = JoyUsageLocation::where('status', 1)->pluck('location')->values();
        $routeName = request()->route()->getName();
        $section = Section::where('section_key', 'need_more_help')->first();

        // Start query
        if (str_contains($routeName, 'esim')) {
            $query = Joytel::where('product_type', 'LIKE', '%esim%');
        } else {
            $query = Joytel::where('product_type', 'LIKE', '%recharge%');
        }

        // NEW FILTER
        $query->whereIn('product_name', function ($subquery) {
            $subquery->select('plan')
                ->from('price_lists')
                ->whereNotNull('plan');
        });

        $packages = $query->where('status', 1)
            ->take(3)
            ->get();

        return view($route, compact('usage_locations', 'packages', 'section'));
    }

    private function renderPackages($route, $keyword, $request)
    {
        $validated = Validator::make($request->all(), [
            'locations' => 'required|array',
            'locations.*' => 'string'
        ])->validate();

        $query = Joytel::whereRaw('LOWER(product_type) LIKE ?', ['%' . $keyword . '%']);
        foreach ($validated['locations'] as $location) {
            $query->whereJsonContains('usage_location', $location);
        }

        /** * NEW FILTER: Only show packages where the product_name exists 
         * in the price_lists table under the 'plan' column.
         */
        $query->whereIn('product_name', function ($subquery) {
            $subquery->select('plan')
                ->from('price_lists')
                ->whereNotNull('plan');
        });

        $packages = $query->where('status', 1)->get();

        return view($route, compact('packages'));
    }
}
