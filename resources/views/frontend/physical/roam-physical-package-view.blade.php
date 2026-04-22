@extends('frontend.layouts.index')
@section('title', 'Roam Physical-SIM Package View')
@section('content')

    <!-- Sub-Banner -->
    <div class="sub-banner">
        <section class="banner-section">
            <figure class="mb-0 bgshape">
                <img src="{{ asset('assets/images/homebanner-bgshape.png') }}" alt="" class="img-fluid">
            </figure>
            <div class="container">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-12">
                        <div class="banner_content">
                            <h1>{{ $sku->country_name }}</h1>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <div class="box">
            <span class="mb-0 text-size-16">Our Service</span><span class="mb-0 text-size-16 dash">-</span><span
                class="mb-0 text-size-16">Physical-SIM</span><span class="mb-0 text-size-16 dash">-</span><span
                class="mb-0 text-size-16 box_span">Roam</span>
        </div>
    </div>
    <!--Services section-->
    <section class="service-section">
        <div class="container">
            <div class="row mb-5">
                @php
                    $pkg = App\Models\RoamPhysical::where('sku_id', $sku->sku_id)->first();

                @endphp
                <div class="col-lg-6 col-md-6 mb-5">
                    <!-- <div id="productGlleryIndicators" class="carousel slide" data-ride="carousel"> -->
                    <div id="productGlleryIndicators" class="" data-ride="carousel">
                        <!-- <ol class="carousel-indicators">
                                                                                                                <li data-target="#productGlleryIndicators" data-slide-to="0" class="active">
                                                                                                                    <img class="d-block w-100 border" src="{{ file_exists(public_path('storage/upload/roam/' . $pkg->image)) ? asset('storage/upload/roam/' . $pkg->image) : asset($pkg->image ?? 'assets/images/package.jpg') }}">
                                                                                                                </li>
                                                                                                                <li data-target="#productGlleryIndicators" data-slide-to="1">
                                                                                                                    <img class="d-block w-100 border" src="{{ file_exists(public_path('storage/upload/roam/' . $pkg->image)) ? asset('storage/upload/roam/' . $pkg->image) : asset($pkg->image ?? 'assets/images/package.jpg') }}">
                                                                                                                </li>
                                                                                                                <li data-target="#productGlleryIndicators" data-slide-to="2">
                                                                                                                    <img class="d-block w-100 border" src="{{ file_exists(public_path('storage/upload/roam/' . $pkg->image)) ? asset('storage/upload/roam/' . $pkg->image) : asset($pkg->image ?? 'assets/images/package.jpg') }}">
                                                                                                                </li>
                                                                                                              </ol> -->
                        <div class="carousel-inner">
                            <div class="carousel-item active">
                                <img class="d-block w-100"
                                    src="{{ file_exists(public_path('storage/upload/roam/' . $pkg->image)) ? asset('storage/upload/roam/' . $pkg->image) : asset($pkg->image ?? 'assets/images/package.jpg') }}">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100"
                                    src="{{ file_exists(public_path('storage/upload/roam/' . $pkg->image)) ? asset('storage/upload/roam/' . $pkg->image) : asset($pkg->image ?? 'assets/images/package.jpg') }}">
                            </div>
                            <div class="carousel-item">
                                <img class="d-block w-100"
                                    src="{{ file_exists(public_path('storage/upload/roam/' . $pkg->image)) ? asset('storage/upload/roam/' . $pkg->image) : asset($pkg->image ?? 'assets/images/package.jpg') }}">
                            </div>
                        </div>
                        <!-- <a class="carousel-control-prev" href="#productGlleryIndicators" role="button" data-slide="prev">
                                                                                                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                                                                                                <span class="sr-only">Previous</span>
                                                                                                              </a>
                                                                                                              <a class="carousel-control-next" href="#productGlleryIndicators" role="button" data-slide="next">
                                                                                                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                                                                                                <span class="sr-only">Next</span>
                                                                                                              </a> -->
                    </div>
                </div>
                <div class="col-lg-6 col-md-6">
                    <h4>{{ $sku->country_name }}</h4>
                    <div class="row">
                        <div class="col-lg-4 col-md-4 col-sm-12 col-12">
                            <label class="font-weight-bold">Provider : </label>
                        </div>
                        <div class="col-lg-8 col-md-8 col-sm-12 col-12">
                            <div class="content">
                                <label class="text">Roam</label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-4 col-md-4 col-sm-12 col-12">
                            <label class="font-weight-bold">Network : </label>
                        </div>
                        <div class="col-lg-8 col-md-8 col-sm-12 col-12">
                            <div class="content">
                                @php
                                    $operators = collect($activePackages[0]['networkVos'])
                                        ->pluck('operator')
                                        ->unique()
                                        ->join(', ');
                                @endphp
                                <label class="text">{{ $operators }}</label>
                                <br>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-4 col-md-4 col-sm-12 col-12">
                            <label class="font-weight-bold">Coverage : </label>
                        </div>
                        <div class="col-lg-8 col-md-8 col-sm-12 col-12">
                            <div class="content">
                                <label class="text">{{ implode(', ', $roam->support_country) }}</label>
                            </div>
                        </div>
                    </div>
                    @php
                        $premark = $activePackages[0]['premark'];
                        $premarkLines = array_filter(array_map('trim', preg_split('/<br>|\n/', $premark)));
                    @endphp

                    @foreach ($premarkLines as $line)
                        @php
                            [$label, $value] = array_pad(explode(':', $line, 2), 2, '');
                            $label = trim($label);
                            $value = trim($value);
                        @endphp
                        @if ($label && $value && strcasecmp($label, 'Data Allowance at Full Speed') !== 0)
                            <div class="row mb-2">
                                <div class="col-lg-4 col-md-4 col-sm-12 col-12">
                                    <label class="font-weight-bold">{{ $label }} :</label>
                                </div>
                                <div class="col-lg-8 col-md-8 col-sm-12 col-12">
                                    <div class="content">
                                        <label class="text">{{ $value }}</label>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                    @php
                        $validPriceListCodes = $pricelists
                            ->where('exchange_rate', '>', 0)
                            ->pluck('product_code')
                            ->toArray();

                        $validPackages = collect($activePackages)
                            ->filter(function ($plan) use ($validPriceListCodes) {
                                return in_array($plan['priceid'], $validPriceListCodes);
                            })
                            ->values();

                        $hasValidPlans = $validPackages->isNotEmpty();
                    @endphp
                    @if ($hasValidPlans)
                        <form class="form-design">
                            <div class="form-group">
                                <label class="font-weight-bold">Type of Plan</label>
                                <div id="trafficType" class="btn-group btn-group-toggle d-flex flex-wrap"
                                    data-toggle="buttons">
                                    <label class="btn btn-outline-secondary m-1 active ">
                                        <input type="radio" name="tType" value="Daily" checked>Daily Type
                                    </label>
                                    <label class="btn btn-outline-secondary m-1 rounded ">
                                        <input type="radio" name="tType" value="Unlimited"> Unlimited Type
                                    </label>
                                    <label class="btn btn-outline-secondary m-1 rounded ">
                                        <input type="radio" name="tType" value="Total"> Total Type
                                    </label>
                                </div>
                            </div>
                            <!-- Day Variation -->
                            <div class="form-group">
                                <label for="serviceDaySlect" class="font-weight-bold">Service Days</label>
                                <div id="serviceDay" class="btn-group btn-group-toggle d-flex flex-wrap"
                                    data-toggle="buttons">
                                    @php
                                        $uniqueDays = collect($activePackages)->pluck('days')->unique()->sort();
                                    @endphp

                                    @foreach ($uniqueDays as $day)
                                        <label class="btn btn-outline-secondary m-1 {{ $loop->first ? 'active' : '' }}">
                                            <input type="radio" name="sday" value="{{ $day }} Days"
                                                data-day="{{ $day }}" {{ $loop->first ? 'checked' : '' }}>
                                            {{ $day }} Days
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Data Variation-->
                            <div class="form-group">
                                <label class="font-weight-bold">Data</label>
                                <div id="dataPlan" class="btn-group btn-group-toggle d-flex flex-wrap"
                                    data-toggle="buttons">
                                    @foreach ($validPackages as $plan)
                                        @php
                                            $flow = $plan['flows'] . ' ' . $plan['unit'];
                                            $portalPrice = round(
                                                ($plan['price'] ?? 0) + ($plan['openCardFee'] ?? 0),
                                                2,
                                            );
                                        @endphp
                                        <label class="btn btn-outline-secondary m-1 rounded d-none"
                                            data-day="{{ $plan['days'] }}" data-price="{{ $portalPrice }}"
                                            data-priceid="{{ $plan['priceid'] }}" data-sku="{{ $roam['sku_id'] }}">
                                            <input type="radio" name="sdata" value="{{ $flow }}"
                                                data-day="{{ $plan['days'] }}" data-price="{{ $portalPrice }}"
                                                data-priceid="{{ $plan['priceid'] }}">
                                            {{ $flow }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>


                            <!-- Qty Field -->
                            <div class="form-group">
                                <label class="font-weight-bold">Quantity</label>
                                <div class="input-group quantity-wrapper">
                                    <button class="btn btn-outline-secondary qty-minus" type="button">-</button>
                                    <input type="number" id="qty" class="form-control text-center" value="1"
                                        min="1" max="100">
                                    <button class="btn btn-outline-secondary qty-plus" type="button">+</button>
                                </div>
                            </div>
                            <div class="form-group d-none" id="planDescriptionGroup">
                                <label class="font-weight-bold">Description</label>
                                <p id="planDescription" class="mb-0"
                                    style="font-size: 13px; line-height: 1.35; margin-top: 2px;"></p>
                            </div>
                            <div class="form-group">
                                <p id="priceDisplay" class="h5"></p>
                            </div>

                            <!-- Price Display -->
                            <!-- <div class="form-group">
                                                                                                                    <label class="font-weight-bold">Price</label>
                                                                                                                    <p id="priceDisplay" class="h5 text-success mb-0">Select a plan</p>
                                                                                                                </div> -->
                            <!-- Add to Cart -->
                            <a href="cart-esim-roam.html" id="addToCartBtn" class="button_text">Add To Cart</a>
                        </form>
                    @else
                        <div class="alert alert-warning">This plan is currently not available for sale.</div>
                    @endif
                </div>
            </div>
            <h4 class="mb-4">You may also like</h4>
            <div class="services-data">
                <div class="row">
                    @forelse ($randomSkus as $package)
                        @php
                            $itemRoam = App\Models\RoamPhysical::where('sku_id', $package->sku_id)->first();
                            $itemPriceList = App\Models\PriceList::where('plan', $package->sku_id)
                                ->where('dp_status', 1)
                                ->first();

                            $lowestPrice = null;

                            if ($itemRoam && !empty($itemRoam->packages)) {
                                $priceMap = App\Models\PriceList::where('plan', $package->sku_id)
                                    ->where('dp_status', 1)
                                    ->pluck('exchange_rate', 'product_code');

                                $lowestPrice = collect($itemRoam->packages)
                                    ->filter(fn($pkg) => isset($pkg['priceid']) && $pkg['status'] == 1)
                                    ->map(function ($pkg) use ($priceMap) {
                                        if (!isset($priceMap[$pkg['priceid']])) {
                                            return null;
                                        }
                                        $portalPrice = ($pkg['price'] ?? 0) + ($pkg['openCardFee'] ?? 0);
                                        return $portalPrice * $priceMap[$pkg['priceid']];
                                    })
                                    ->filter()
                                    ->min();
                            }
                        @endphp
                        <div class="col-lg-4 col-md-4 col-sm-12 col-12">
                            <div class="service-box">
                                <figure class="img img2 mb-3">
                                    <img src="{{ file_exists(public_path('storage/upload/roam/' . $pkg->image)) ? asset('storage/upload/roam/' . $pkg->image) : asset($pkg->image ?? 'assets/images/package.jpg') }}"
                                        alt="{{ $package->country_name ?? 'Package' }}" class="img-fluid">
                                </figure>
                                <div class="content">

                                    <h4>{{ $package->country_name ?? 'Unnamed Package' }}</h4>
                                    @if ($lowestPrice)
                                        <p class="text-size-16">From {{ number_format($lowestPrice) }} MMK</p>
                                    @else
                                        <p class="text-size-16 text-danger">Not available</p>
                                    @endif
                                    <a href="{{ route('physical.roampackageview', ['id' => $package->sku_id]) }}"
                                        class="more">View Offer</a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <p>No packages found for the selected countries.</p>
                        </div>
                    @endforelse

                </div>
            </div>
        </div>
    </section>
    <!-- jQuery first -->

    <script src="{{ asset('assets/js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const hasValidPlans = @json($hasValidPlans);

            if (!hasValidPlans) {
                const elements = ['trafficType', 'serviceDay', 'dataPlan', 'priceDisplay'];
                elements.forEach(id => {
                    if (document.getElementById(id)) document.getElementById(id).innerHTML = '';
                });
                return;
            }

            const allPackages = @json($validPackages);
            const priceLists = @json($pricelists->where('exchange_rate', '>', 0)->values());

            const service_day_box = document.getElementById('serviceDay');
            const dataBox = document.getElementById('dataPlan');
            const total_price = document.getElementById('priceDisplay');
            const planDescription = document.getElementById('planDescription');
            const planDescriptionGroup = document.getElementById('planDescriptionGroup');
            const displayPriceInput = document.getElementById('display_price');
            const qtyInput = document.getElementById('qty');
            const trafficTypeBox = document.getElementById('trafficType');

            function isProductValid(priceId) {
                // Ensure comparison works regardless of string or integer type
                return priceLists.some(p => String(p.product_code) === String(priceId));
            }

            function getExchangeRate(priceId) {
                const item = priceLists.find(p => String(p.product_code) === String(priceId));
                return item ? item.exchange_rate : 0;
            }

            function normalizeType(pkg) {
                const rawType = String(pkg.showName || '').toLowerCase();
                const supportDaypass = Number(pkg.supportDaypass || 0);
                const hasDaypassDetail = Number(pkg.hadDaypassDetail || 0);

                if (rawType.includes('unlimited')) {
                    return 'unlimited';
                }

                if (
                    rawType.includes('daypass') ||
                    rawType.includes('daily') ||
                    supportDaypass === 1 ||
                    hasDaypassDetail === 1
                ) {
                    return 'daily';
                }

                return 'total';
            }

            function getPackagesForType(selectedType) {
                return allPackages.filter(pkg => normalizeType(pkg) === selectedType && isProductValid(pkg
                    .priceid));
            }

            function getSelectedType() {
                const activeInput = trafficTypeBox ? trafficTypeBox.querySelector('input[name="tType"]:checked') :
                    null;
                return (activeInput ? activeInput.value : 'Daily').toLowerCase();
            }

            function getSelectedDayValue() {
                const activeInput = service_day_box ?
                    service_day_box.querySelector('input[name="sday"]:checked') :
                    null;

                if (!activeInput) {
                    return null;
                }

                const dayFromData = activeInput.dataset.day ? parseInt(activeInput.dataset.day, 10) : null;
                if (!Number.isNaN(dayFromData) && dayFromData !== null) {
                    return dayFromData;
                }

                const parsed = parseInt(activeInput.value, 10);
                return Number.isNaN(parsed) ? null : parsed;
            }

            function extractPlanDescription(premark) {
                if (!premark) {
                    return '';
                }

                const lines = String(premark)
                    .split(/<br>|\n/)
                    .map(line => line.trim())
                    .filter(Boolean);

                if (!lines.length) {
                    return '';
                }

                const firstLine = lines[0];
                if (/^plan type\s*:/i.test(firstLine)) {
                    return '';
                }

                return firstLine.split(':').slice(1).join(':').trim() || firstLine;
            }

            function updatePlanDescription(text) {
                if (!planDescription || !planDescriptionGroup) {
                    return;
                }

                planDescription.innerText = text || '';
                planDescriptionGroup.classList.toggle('d-none', !text);
            }

            function filterTrafficTypes() {
                const typesWithData = new Set();

                allPackages.forEach(pkg => {
                    const mappedType = normalizeType(pkg);

                    if (isProductValid(pkg.priceid)) {
                        typesWithData.add(mappedType);
                    } else {
                        console.warn(
                            `PriceID ${pkg.priceid} (${pkg.showName}) not found in price_lists table.`);
                    }
                });

                let firstVisibleInput = null;

                trafficTypeBox.querySelectorAll('label').forEach(label => {
                    const input = label.querySelector('input[name="tType"]');
                    if (input) {
                        const typeValue = input.value.toLowerCase();
                        // Check if this type exists in our set of valid types
                        if (!typesWithData.has(typeValue)) {
                            label.style.setProperty('display', 'none', 'important');
                        } else {
                            label.style.display = 'inline-block';
                            if (!firstVisibleInput) firstVisibleInput = input;
                        }
                    }
                });

                if (firstVisibleInput) {
                    firstVisibleInput.checked = true;
                    const label = firstVisibleInput.closest('label');
                    if (label) label.classList.add('active');
                    renderServiceDays();
                }
            }

            function renderDataPlans(plans, selectedDay, preferredFlowKey = null) {
                dataBox.innerHTML = '';
                const validPlans = plans.filter(plan => String(plan.days) === String(selectedDay));

                if (!validPlans.length) {
                    dataBox.innerHTML = '<span class="text-danger">No data for this day.</span>';
                    updatePlanDescription('');
                    updatePriceDisplay();
                    return;
                }

                validPlans.forEach((plan, index) => {
                    const rate = getExchangeRate(plan.priceid);
                    const portalPrice = (parseFloat(plan.price) || 0) + (parseFloat(plan.openCardFee) || 0);
                    const calculatedPrice = Math.round(portalPrice * rate);
                    const dataLabel = `${plan.flows} ${plan.unit}`;

                    const label = document.createElement('label');
                    label.className =
                        `btn btn-outline-secondary m-1 rounded ${index === 0 ? 'active' : ''}`;
                    label.innerHTML = `
                <input type="radio" name="sdata" value="${dataLabel}"
                    data-flow="${dataLabel}"
                    data-day="${plan.days}"
                    data-price="${calculatedPrice}"
                    data-priceid="${plan.priceid}"
                    ${index === 0 ? 'checked' : ''}>
                ${dataLabel}
            `;
                    const input = label.querySelector('input[name="sdata"]');
                    const description = extractPlanDescription(plan.premark);
                    label.dataset.description = description;
                    if (input) {
                        input.dataset.description = description;
                    }
                    dataBox.appendChild(label);
                });

                updatePlanDescription(validPlans[0] ? extractPlanDescription(validPlans[0].premark) : '');
                updatePriceDisplay();
            }

            function renderServiceDays(preferredDay = null, preferredFlowKey = null) {
                const activeInput = trafficTypeBox.querySelector('input[name="tType"]:checked');
                if (!activeInput) return;

                const selectedType = activeInput.value.toLowerCase();
                const filteredPackages = getPackagesForType(selectedType);

                const uniqueDays = [...new Set(filteredPackages.map(p => p.days))].sort((a, b) => a - b);
                service_day_box.innerHTML = '';

                if (!uniqueDays.length) {
                    dataBox.innerHTML = '<span class="text-danger">No data for this plan type.</span>';
                    updatePlanDescription('');
                    updatePriceDisplay();
                    return;
                }

                const dayToSelect = preferredDay && uniqueDays.includes(Number(preferredDay)) ?
                    Number(preferredDay) :
                    uniqueDays[0];

                uniqueDays.forEach((day, index) => {
                    const label = document.createElement('label');
                    const shouldSelect = String(day) === String(dayToSelect);
                    label.className = `btn btn-outline-secondary m-1 ${shouldSelect ? 'active' : ''}`;
                    label.innerHTML = `
                <input type="radio" name="sday" value="${day}" data-day="${day}" ${shouldSelect ? 'checked' : ''}>
                ${day} day
            `;
                    service_day_box.appendChild(label);
                });

                renderDataPlans(filteredPackages, dayToSelect, preferredFlowKey);
            }

            function updatePriceDisplay() {
                const selectedData = dataBox.querySelector('input[name="sdata"]:checked');
                const qty = parseInt(qtyInput.value) || 1;

                if (selectedData) {
                    const total = parseFloat(selectedData.dataset.price || 0) * qty;
                    total_price.innerText = `Total Price: ${total.toLocaleString()} MMK`;
                    if (displayPriceInput) displayPriceInput.value = total;
                    return;
                }

                total_price.innerText = 'Total Price: 0 MMK';
                if (displayPriceInput) displayPriceInput.value = '';
            }

            // Handlers
            trafficTypeBox.addEventListener('click', function(e) {
                const label = e.target.closest('label');
                if (!label || !trafficTypeBox.contains(label)) return;

                const input = label.querySelector('input[name="tType"]');
                if (!input) return;

                trafficTypeBox.querySelectorAll('label').forEach(item => item.classList.remove('active'));
                label.classList.add('active');
                input.checked = true;

                renderServiceDays();
            });

            service_day_box.addEventListener('click', function(e) {
                const label = e.target.closest('label');
                if (!label || !service_day_box.contains(label)) return;

                const input = label.querySelector('input[name="sday"]');
                if (!input) return;

                service_day_box.querySelectorAll('label').forEach(l => l.classList.remove('active'));
                label.classList.add('active');
                input.checked = true;

                const selectedType = getSelectedType();
                const filtered = getPackagesForType(selectedType);
                renderDataPlans(filtered, parseInt(input.dataset.day || input.value, 10));
            });

            dataBox.addEventListener('click', function(e) {
                const label = e.target.closest('label');
                if (!label || !dataBox.contains(label)) return;

                const input = label.querySelector('input[name="sdata"]');
                if (!input) return;

                dataBox.querySelectorAll('label').forEach(item => item.classList.remove('active'));
                label.classList.add('active');
                input.checked = true;
                updatePlanDescription(input.dataset.description || label.dataset.description || '');
                updatePriceDisplay();
            });

            qtyInput.addEventListener('input', updatePriceDisplay);
            qtyInput.addEventListener('change', updatePriceDisplay);

            document.querySelector('.qty-plus').addEventListener('click', () => {
                qtyInput.value = parseInt(qtyInput.value || 1) + 1;
                updatePriceDisplay();
            });
            document.querySelector('.qty-minus').addEventListener('click', () => {
                if (parseInt(qtyInput.value || 1) > 1) qtyInput.value = parseInt(qtyInput.value) - 1;
                updatePriceDisplay();
            });

            filterTrafficTypes();
        });
    </script>
@endsection
