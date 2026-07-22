<?php

namespace App\Http\Controllers\Frontend;

use App\Payment\Providers\Uab\Contracts\HostedPaymentInterface;
use App\Http\Controllers\Controller;
use App\Payment\Providers\Uab\DTO\HostedPaymentRequestData;
use App\Payment\Providers\Uab\Enums\Currency;
use App\Payment\Providers\Uab\Enums\PaymentMethod;
use App\Models\UabPaymentTransaction;
use App\Models\RoamOrder;
use App\Services\OrderNotificationService;
use App\Services\Roam\RoamIccidSupportService;
use App\Services\Roam\RoamOrderDraftService;
use App\Services\Roam\RoamProvisioningFlowService;
use App\Payment\Providers\Uab\Services\UabCredentialService;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class RoamCheckoutController extends Controller
{
    public function placeOrder(
        Request $request,
        RoamOrderDraftService $draftService,
        RoamIccidSupportService $iccidSupportService,
        RoamProvisioningFlowService $provisioning
    ) {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:50'],
            'iccid_numbers' => ['nullable', 'array'],
            'payment_method' => ['required', 'in:direct_bank_transfer,uabpay,uab_pay,UAB Pay,wallet'],
            'terms' => ['accepted'],
        ]);
        $paymentMethod = $this->normalizePaymentMethod($validated['payment_method']);
        $paymentSetting = \App\Models\PaymentSetting::orderBy('id')->get()->keyBy('id');
        $activePaymentMethods = [
            'direct_bank_transfer' => (bool) $paymentSetting->get(\App\Models\PaymentSetting::DIRECT_BANK_TRANSFER_ID)?->status,
            'uabpay' => (bool) $paymentSetting->get(\App\Models\PaymentSetting::ONLINE_PAYMENT_ID)?->status,
            'wallet' => (bool) $paymentSetting->get(\App\Models\PaymentSetting::WALLET_ID)?->status,
        ];

        if (empty($activePaymentMethods[$paymentMethod])) {
            return back()
                ->withInput()
                ->withErrors(['payment_method' => 'Selected payment method is currently unavailable.']);
        }

        $billingPhone = preg_replace('/\D+/', '', (string) $validated['customer_phone']);
        if (strlen($billingPhone) < 8) {
            return back()
                ->withInput()
                ->withErrors(['customer_phone' => 'Phone number must be at least 8 digits.']);
        }

        $checkoutCustomerData = [
            'full_name' => trim((string) $validated['customer_name']),
            'email' => trim((string) $validated['customer_email']),
            'phone' => $billingPhone,
        ];

        Auth::shouldUse('customers');
        $customer = auth()->user();

        $cart = (array) session('roam_order_cart', []);

        // Validate ICCID length for Recharge Physical SIM:
        // - FiROAM Global SIM => 19 digits (dp_info != 21)
        // - FiROAM Asia SIM   => 18 digits (dp_info == 21)
        $iccidNumbersByIndex = (array) ($validated['iccid_numbers'] ?? []);
        $cartItems = array_values(array_filter($cart, static fn($item) => is_array($item)));
        if (array_key_exists('sku_id', $cart) || array_key_exists('sku', $cart)) {
            $cartItems = [$cart];
        }

        foreach ($cartItems as $index => $item) {
            $orderType = strtolower((string) ($item['order_type'] ?? ''));
            $serviceType = strtolower((string) ($item['service_type'] ?? ''));
            if ($serviceType === '') {
                $simType = strtolower((string) ($item['sim_type'] ?? ''));
                $serviceType = str_contains($simType, 'physical') ? 'physical' : 'esim';
            }
            $dpInfo = (int) ($item['dp_info'] ?? 0);
            $cartRoute = $serviceType === 'physical'
                ? route('roam.physical.cartpage')
                : route('roam.esim.cartpage');

            if ($orderType !== 'recharge') {
                continue;
            }

            $iccids = (array) ($iccidNumbersByIndex[$index] ?? []);
            if (empty(array_filter(array_map(static fn($iccid) => preg_replace('/\D+/', '', (string) $iccid), $iccids)))) {
                return $this->redirectWithIccidError(
                    $index,
                    'ICCID is required for recharge orders.',
                    $cartRoute
                );
            }

            foreach ($iccids as $iccid) {
                $digits = preg_replace('/\\D+/', '', (string) $iccid);
                if ($digits === '') {
                    return $this->redirectWithIccidError(
                        $index,
                        'ICCID must contain digits only.',
                        $cartRoute
                    );
                }

                if ($serviceType === 'physical') {
                    $expectedLength = $dpInfo === 21 ? 18 : 19;
                    if (strlen($digits) !== $expectedLength) {
                        $label = $dpInfo === 21 ? 'FiROAM Asia SIM' : 'FiROAM Global SIM';
                        return $this->redirectWithIccidError(
                            $index,
                            "ICCID for {$label} must be exactly {$expectedLength} digits.",
                            $cartRoute
                        );
                    }
                } elseif ($serviceType === 'esim') {
                    if (!in_array(strlen($digits), [18, 19], true)) {
                        return $this->redirectWithIccidError(
                            $index,
                            'ICCID for eSIM Recharge must be 18 or 19 digits.',
                            $cartRoute
                        );
                    }
                } else {
                    return $this->redirectWithIccidError(
                        $index,
                        'Invalid service type for ICCID validation.',
                        $cartRoute
                    );
                }
            }
        }

        try {
            $supportFailures = $iccidSupportService->validateCartSelections($cartItems, $iccidNumbersByIndex);
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error_popup_html', e($e->getMessage()));
        }

        if (!empty($supportFailures)) {
            $firstFailure = $supportFailures[0];
            $cartRoute = str_contains(strtolower((string) ($cartItems[$firstFailure['index']]['service_type'] ?? '')), 'physical')
                ? route('roam.physical.cartpage')
                : route('roam.esim.cartpage');

            return $this->redirectWithIccidError(
                (int) $firstFailure['index'],
                (string) $firstFailure['message'],
                $cartRoute
            );
        }

        try {
            $result = $draftService->createDraftOrdersFromCart(
                $customer,
                $cart,
                $iccidNumbersByIndex,
                $paymentMethod
            );

            if ($paymentMethod === 'uabpay') {
                $this->persistUabCheckoutCustomerMetadata($result['orders'], $checkoutCustomerData);
            }

            if ($paymentMethod === 'wallet') {
                $provisioning->provisionAfterPayment($customer, $result['outer_order_id']);
            }
        } catch (\Throwable $e) {
            $response = back()->withInput();

            if ($paymentMethod === 'wallet' && $this->shouldShowWalletTopupLink($e->getMessage())) {
                return $response->with('error_popup_html', $this->buildWalletTopupPopup($e->getMessage()));
            }

            return $response->with('error', $e->getMessage());
        }

        session()->forget([
            'roam_order_cart',
            'iccid_numbers',
        ]);
        session()->put('roam_last_outer_order_id', $result['outer_order_id']);

        return redirect()->route('roam.payment.show', ['outerOrderId' => $result['outer_order_id']]);
    }

    public function startUabPayment(
        string $outerOrderId,
        HostedPaymentInterface $hostedPaymentService,
        UabCredentialService $uabCredentialService
    ) {
        Auth::shouldUse('customers');
        $customer = auth()->user();

        $orders = $this->getCustomerOrders($customer->id, $outerOrderId);
        if ($orders->isEmpty()) {
            return redirect()->route('customer.roam.order.detail')->with('error', 'Order not found.');
        }

        if (!$orders->every(fn(RoamOrder $order) => strtolower((string) $order->payment_method) === 'uabpay')) {
            return redirect()->route('roam.payment.show', ['outerOrderId' => $outerOrderId])
                ->with('error', 'This UAB payment session is only available for UAB Pay orders.');
        }

        if ($orders->every(fn(RoamOrder $order) => (int) $order->our_status !== RoamOrder::OUR_STATUS_PENDING_PAYMENT)) {
            return redirect()->route('roam.payment.show', ['outerOrderId' => $outerOrderId])
                ->with('error', 'This order is no longer waiting for payment.');
        }

        try {
            return $this->redirectToHostedUabPayment($orders, $hostedPaymentService, $uabCredentialService, $customer);
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('roam.payment.show', ['outerOrderId' => $outerOrderId])
                ->with('error', 'Unable to start UAB payment right now. Please try again in a moment.');
        }
    }

    public function showPayment(string $outerOrderId)
    {
        Auth::shouldUse('customers');
        $customer = auth()->user();

        $orders = \App\Models\RoamOrder::query()
            ->with('items')
            ->where('customer_id', $customer->id)
            ->where('outer_order_id', $outerOrderId)
            ->latest()
            ->get();

        $paymentMethod = $orders->first()?->payment_method;
        $credentials = null;
        $payment_method = payment_method_label($paymentMethod);
        $paymentActionUrl = null;
        $paymentSetting = \App\Models\PaymentSetting::orderBy('id')->get()->keyBy('id');
        if ($paymentMethod === 'direct_bank_transfer') {
            $directPayment = $paymentSetting->get(\App\Models\PaymentSetting::DIRECT_BANK_TRANSFER_ID);
            $credentials = $directPayment?->directBankCredentials;
            $payment_method = $directPayment?->type ?? payment_method_label($paymentMethod);
        } elseif ($paymentMethod === 'uabpay' || $paymentMethod === 'UAB Pay') {
            $payment_method = payment_method_display_label($paymentMethod, $outerOrderId)
                ?? $paymentSetting->get(\App\Models\PaymentSetting::ONLINE_PAYMENT_ID)?->type
                ?? payment_method_label($paymentMethod);
            $paymentActionUrl = data_get($orders->first()?->raw_response, 'payment.uab.payment_url');
        }

        if ($orders->isEmpty()) {
            return redirect()->route('customer.roam.order.detail')->with('error', 'Order not found.');
        }

        $slipPath = data_get($orders->first()?->raw_response, 'payment.slip.path');
        $statusView = strtolower((string) $paymentMethod) === 'uabpay'
            ? $this->buildUabPaymentStatusView(
                $orders,
                UabPaymentTransaction::query()
                    ->where('merchant_reference', $outerOrderId)
                    ->latest('id')
                    ->first(),
                $paymentActionUrl
            )
            : $this->buildPaymentStatusView($orders, !empty($slipPath));

        return view('frontend.payment', [
            'outer_order_id' => $outerOrderId,
            'orders' => $orders,
            'total' => $orders->sum(fn($order) => (float) $order->billable_total_price),
            'payment_status_view' => $statusView,
            'credentials' => $credentials,
            'payment_method' => $payment_method,
            'payment_action_url' => $paymentActionUrl,
        ]);
    }

    public function uploadPaymentSlip(Request $request, string $outerOrderId, OrderNotificationService $notifications)
    {
        Auth::shouldUse('customers');
        $customer = auth()->user();

        $validated = $request->validate([
            'payment_slip' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ]);

        $orders = RoamOrder::query()
            ->where('customer_id', $customer->id)
            ->where('outer_order_id', $outerOrderId)
            ->orderBy('id')
            ->get();

        if ($orders->isEmpty()) {
            return redirect()->route('customer.roam.order.detail')->with('error', 'Order not found.');
        }

        if ($orders->every(fn(RoamOrder $order) => (int) $order->our_status !== RoamOrder::OUR_STATUS_PENDING_PAYMENT)) {
            return redirect()->route('roam.payment.show', ['outerOrderId' => $outerOrderId])
                ->with('error', 'Payment slip can only be updated while the order is pending payment.');
        }

        $existingSlipPath = data_get($orders->first()?->raw_response, 'payment.slip.path');
        $uploadedFile = $validated['payment_slip'];
        $storedPath = $uploadedFile->store('payment-slips', 'public');

        foreach ($orders as $order) {
            $rawResponse = (array) ($order->raw_response ?? []);
            $payment = (array) data_get($rawResponse, 'payment', []);
            $payment['slip'] = [
                'path' => $storedPath,
                'original_name' => $uploadedFile->getClientOriginalName(),
                'mime_type' => $uploadedFile->getClientMimeType(),
                'uploaded_at' => now()->toDateTimeString(),
            ];
            $rawResponse['payment'] = $payment;
            $order->raw_response = $rawResponse;
            $order->save();
        }

        $notifications->paymentSlipUploaded($orders->first()->refresh());

        if ($existingSlipPath && $existingSlipPath !== $storedPath && Storage::disk('public')->exists($existingSlipPath)) {
            Storage::disk('public')->delete($existingSlipPath);
        }

        return redirect()->route('roam.payment.show', ['outerOrderId' => $outerOrderId])
            ->with('success', 'Payment slip uploaded successfully. Our admin team can review it now.');
    }

    private function redirectWithIccidError(int $index, string $message, string $cartRoute)
    {
        $popupHtml = e($message) . '<br><br><a href="' . e($cartRoute) . '">Go back to Cart page</a>';

        return back()
            ->withInput()
            ->withErrors([
                "iccid_numbers.{$index}.0" => $message,
            ])
            ->with('error_popup_html', $popupHtml);
    }

    private function normalizePaymentMethod(string $paymentMethod): string
    {
        return in_array($paymentMethod, ['uabpay', 'uab_pay', 'UAB Pay'], true)
            ? 'uabpay'
            : $paymentMethod;
    }

    private function buildWalletTopupPopup(string $message): string
    {
        return e($message) . '<br><br><a href="' . e(route('frontend.user.wallet')) . '">Top Up Wallet</a>';
    }

    private function shouldShowWalletTopupLink(string $message): bool
    {
        $message = strtolower($message);

        return str_contains($message, 'wallet') || str_contains($message, 'balance') || str_contains($message, 'top up');
    }

    private function buildPaymentStatusView($orders, bool $hasUploadedSlip): array
    {
        $allRefunded = $orders->every(
            fn(RoamOrder $order) => (int) $order->our_status === RoamOrder::OUR_STATUS_REFUNDED
        );
        $hasRoamRefund = $orders->contains(
            fn(RoamOrder $order) => (int) $order->our_status === RoamOrder::OUR_STATUS_COMPLETED
                && (int) $order->roam_status === RoamOrder::ROAM_STATUS_CANCELLED
        );
        $hasPendingPayment = $orders->contains(
            fn(RoamOrder $order) => (int) $order->our_status === RoamOrder::OUR_STATUS_PENDING_PAYMENT
        );
        $hasFailed = $orders->contains(
            fn(RoamOrder $order) => (int) $order->our_status === RoamOrder::OUR_STATUS_API_FAILED
        );
        $hasAdminCancelled = $orders->contains(
            fn(RoamOrder $order) => (int) $order->our_status === RoamOrder::OUR_STATUS_ADMIN_CANCELLED
        );
        $hasCancelled = $orders->contains(
            fn(RoamOrder $order) => (int) $order->our_status === RoamOrder::OUR_STATUS_CANCELLED
        );
        $allCompleted = $orders->every(
            fn(RoamOrder $order) => (int) $order->our_status === RoamOrder::OUR_STATUS_COMPLETED
        );
        $isOngoing = $orders->contains(
            fn(RoamOrder $order) => in_array((int) $order->our_status, [
                RoamOrder::OUR_STATUS_PAID,
                RoamOrder::OUR_STATUS_ON_HOLD,
                RoamOrder::OUR_STATUS_API_PROCESSING,
                RoamOrder::OUR_STATUS_API_SUCCESS,
            ], true)
        );

        if ($allRefunded || $hasRoamRefund) {
            return [
                'badge' => 'Refunded',
                'title' => 'This order has been refunded.',
                'message' => 'If you need help with a replacement order, please contact our support team.',
                'tone' => 'info',
                'show_upload_form' => false,
                'show_payment_guide' => false,
                'show_bank_accounts' => false,
            ];
        }

        if ($allCompleted) {
            return [
                'badge' => 'Completed',
                'title' => 'Your order is successful.',
                'message' => 'Your payment was approved and your order has been completed successfully.',
                'tone' => 'success',
                'show_upload_form' => false,
                'show_payment_guide' => false,
                'show_bank_accounts' => false,
            ];
        }

        if ($isOngoing) {
            return [
                'badge' => 'Ongoing',
                'title' => 'Payment approved. Your order is being processed.',
                'message' => 'Our team is provisioning your order now. You can check the latest order details below or from your account page.',
                'tone' => 'primary',
                'show_upload_form' => false,
                'show_payment_guide' => false,
                'show_bank_accounts' => false,
            ];
        }

        if ($hasFailed) {
            return [
                'badge' => 'Failed',
                'title' => 'We could not complete this order.',
                'message' => 'Please contact support or check your order detail page for the latest status update.',
                'tone' => 'danger',
                'show_upload_form' => false,
                'show_payment_guide' => false,
                'show_bank_accounts' => false,
            ];
        }

        if ($hasAdminCancelled) {
            return [
                'badge' => 'Admin Cancel',
                'title' => 'This order was cancelled by admin.',
                'message' => 'You can place a new order whenever you are ready.',
                'tone' => 'danger',
                'show_upload_form' => false,
                'show_payment_guide' => false,
                'show_bank_accounts' => false,
            ];
        }

        if ($hasCancelled) {
            return [
                'badge' => 'Cancelled',
                'title' => 'This order has been cancelled.',
                'message' => 'You can place a new order whenever you are ready.',
                'tone' => 'danger',
                'show_upload_form' => false,
                'show_payment_guide' => false,
                'show_bank_accounts' => false,
            ];
        }

        if ($hasPendingPayment && $hasUploadedSlip) {
            return [
                'badge' => 'Waiting Approval',
                'title' => 'Upload complete. Waiting for payment approval.',
                'message' => 'Your payment slip has been received. Our admin team will review it and approve your payment soon.',
                'tone' => 'warning',
                'show_upload_form' => true,
                'show_payment_guide' => true,
                'show_bank_accounts' => true,
                'upload_label' => 'Reupload Payment Slip',
                'upload_button_text' => 'Replace Slip',
            ];
        }

        return [
            'badge' => 'Pending Payment',
            'title' => 'Please complete your payment.',
            'message' => 'Transfer the amount below and upload your payment slip to continue with your order.',
            'tone' => 'warning',
            'show_upload_form' => true,
            'show_payment_guide' => true,
            'show_bank_accounts' => true,
            'upload_label' => 'Upload Payment Slip',
            'upload_button_text' => 'Upload Slip',
        ];
    }

    private function buildUabPaymentStatusView(Collection $orders, ?UabPaymentTransaction $paymentTransaction, ?string $paymentActionUrl): array
    {
        $status = strtoupper((string) ($paymentTransaction?->status?->value ?? $paymentTransaction?->status ?? 'PENDING'));

        return match ($status) {
            'SUCCESS' => [
                'badge' => 'Paid',
                'title' => 'Your payment was completed successfully.',
                'message' => 'Please contact support if you would like to continue this order.',
                'tone' => 'success',
                'show_upload_form' => false,
                'show_payment_guide' => false,
                'show_bank_accounts' => false,
                'show_payment_button' => false,
            ],
            'CANCELLED' => [
                'badge' => 'Cancelled',
                'title' => 'Your payment was cancelled.',
                'message' => 'Please contact support if you would like to continue this order.',
                'tone' => 'danger',
                'show_upload_form' => false,
                'show_payment_guide' => false,
                'show_bank_accounts' => false,
                'show_payment_button' => false,
            ],
            'FAILED', 'EXPIRED' => [
                'badge' => $status === 'EXPIRED' ? 'Expired' : 'Failed',
                'title' => $status === 'EXPIRED'
                    ? 'Your payment session expired.'
                    : 'Your payment could not be completed.',
                'message' => 'Please contact support if you would like to continue this order.',
                'tone' => 'warning',
                'show_upload_form' => false,
                'show_payment_guide' => false,
                'show_bank_accounts' => false,
                'show_payment_button' => false,
                'payment_button_url' => route('roam.uab.pay', ['outerOrderId' => $orders->first()?->outer_order_id]),
                'payment_button_text' => 'Start New UAB Payment',
            ],
            default => [
                'badge' => 'Pending Payment',
                'title' => 'Continue your payment with Online payment',
                'message' => 'Continue to the payment page to complete your order.',
                'tone' => 'warning',
                'show_upload_form' => false,
                'show_payment_guide' => false,
                'show_bank_accounts' => false,
                'show_payment_button' => true,
                'payment_button_url' => $paymentActionUrl
                    ?: route('roam.uab.pay', ['outerOrderId' => $orders->first()?->outer_order_id]),
                'payment_button_text' => 'Continue to Payment',
            ],
        };
    }

    private function redirectToHostedUabPayment(
        Collection $orders,
        HostedPaymentInterface $hostedPaymentService,
        UabCredentialService $uabCredentialService,
        $customer
    ) {
        $outerOrderId = (string) $orders->first()?->outer_order_id;
        $customerData = $this->resolveUabCustomerData($orders, $customer);
        if ($customerData['phone'] === '') {
            return redirect()->route('roam.payment.show', ['outerOrderId' => $outerOrderId])
                ->with('error', 'Please update your phone number before continuing to payment.');
        }

        $merchantBillingDefaults = $this->resolveMerchantBillingDefaults($uabCredentialService);
        [$forename, $surname] = $this->splitCustomerName($customerData['full_name']);

        $hostedCheckout = $hostedPaymentService->createHostedCheckout(
            new HostedPaymentRequestData(
                requestId: $this->generateUabRequestId(),
                merchantReference: $outerOrderId,
                invoiceNo: $this->generateInvoiceNo($outerOrderId),
                orderNo: $outerOrderId,
                amount: number_format($orders->sum(fn($order) => (float) $order->billable_total_price), 2, '.', ''),
                currency: Currency::MMK,
                paymentMethod: PaymentMethod::UABPAY,
                gatewayPaymentMethods: $this->resolveGatewayPaymentMethods($uabCredentialService),
                billToAddressLine1: $merchantBillingDefaults['address_line1'],
                billToAddressLine2: $merchantBillingDefaults['address_line2'],
                billToAddressCity: $merchantBillingDefaults['city'],
                billToAddressPostalCode: $merchantBillingDefaults['postal_code'],
                billToAddressState: $merchantBillingDefaults['state'],
                billToAddressCountry: $merchantBillingDefaults['country'],
                billToForename: $forename,
                billToSurname: $surname,
                billToPhone: $customerData['phone'],
                billToEmail: $customerData['email'],
                expiredInSeconds: 300,
                remark: 'Roam order payment for ' . $outerOrderId,
                userDefined1: $outerOrderId,
            )
        );

        $this->persistUabPaymentMetadata(
            $orders,
            array_merge($customerData, $merchantBillingDefaults),
            $hostedCheckout
        );

        if ($hostedCheckout->paymentHtml !== null) {
            return response($hostedCheckout->paymentHtml)
                ->header('Content-Type', 'text/html; charset=UTF-8');
        }

        return redirect()->away($hostedCheckout->paymentUrl);
    }

    private function getCustomerOrders(int $customerId, string $outerOrderId): Collection
    {
        return RoamOrder::query()
            ->with('items')
            ->where('customer_id', $customerId)
            ->where('outer_order_id', $outerOrderId)
            ->latest()
            ->get();
    }

    private function splitCustomerName(string $fullName): array
    {
        $parts = preg_split('/\s+/', trim($fullName)) ?: [];
        $parts = array_values(array_filter($parts, static fn($part) => $part !== ''));

        if (count($parts) <= 1) {
            return [$parts[0] ?? 'Customer', 'Customer'];
        }

        $forename = array_shift($parts);

        return [$forename, implode(' ', $parts)];
    }

    private function generateUabRequestId(): string
    {
        return 'REQ' . now()->format('YmdHis') . str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    private function generateInvoiceNo(string $outerOrderId): string
    {
        $sanitized = preg_replace('/[^A-Za-z0-9]/', '', $outerOrderId) ?? '';
        $sanitized = $sanitized !== '' ? $sanitized : now()->format('YmdHis');

        return substr('INV' . $sanitized, 0, 32);
    }

    private function persistUabPaymentMetadata(Collection $orders, array $billingData, $hostedCheckout): void
    {
        foreach ($orders as $order) {
            $rawResponse = (array) ($order->raw_response ?? []);
            $payment = (array) data_get($rawResponse, 'payment', []);
            $payment['uab'] = [
                'request_id' => $hostedCheckout->requestId,
                'transaction_id' => $hostedCheckout->transactionId,
                'payment_url' => $hostedCheckout->paymentUrl,
                'payment_html_returned' => $hostedCheckout->paymentHtml !== null,
                'status' => $hostedCheckout->status->value,
                'provider_response' => $hostedCheckout->providerResponse,
                'billing' => $billingData,
            ];
            $rawResponse['payment'] = $payment;
            $order->raw_response = $rawResponse;
            $order->save();
        }
    }

    private function persistUabCheckoutCustomerMetadata(Collection $orders, array $customerData): void
    {
        foreach ($orders as $order) {
            $rawResponse = (array) ($order->raw_response ?? []);
            $payment = (array) data_get($rawResponse, 'payment', []);
            $payment['uab'] = array_merge((array) ($payment['uab'] ?? []), [
                'customer' => $customerData,
            ]);
            $rawResponse['payment'] = $payment;
            $order->raw_response = $rawResponse;
            $order->save();
        }
    }

    private function resolveUabCustomerData(Collection $orders, $customer): array
    {
        $storedCustomerData = (array) data_get($orders->first()?->raw_response, 'payment.uab.customer', []);
        $storedBillingData = (array) data_get($orders->first()?->raw_response, 'billing', []);
        $phone = $this->normalizeUabPhone(
            $storedCustomerData['phone']
                ?? $storedBillingData['phone']
                ?? $customer?->phone
                ?? ''
        );

        return [
            'full_name' => trim((string) ($storedCustomerData['full_name'] ?? $storedBillingData['full_name'] ?? $customer?->name ?? 'Customer')),
            'email' => trim((string) ($storedCustomerData['email'] ?? $storedBillingData['email'] ?? $customer?->email ?? '')),
            'phone' => $phone,
        ];
    }

    private function normalizeUabPhone(mixed $phone): string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone) ?? '';

        return strlen($digits) >= 8 ? $digits : '';
    }

    private function resolveMerchantBillingDefaults(UabCredentialService $uabCredentialService): array
    {
        $credentials = $uabCredentialService->getActiveCredential();

        $defaults = [
            'address_line1' => trim((string) ($credentials->billingAddressLine1 ?? '')),
            'address_line2' => trim((string) ($credentials->billingAddressLine2 ?? '')),
            'city' => trim((string) ($credentials->billingCity ?? '')),
            'postal_code' => trim((string) ($credentials->billingPostalCode ?? '')),
            'state' => trim((string) ($credentials->billingState ?? '')),
            'country' => strtoupper(trim((string) ($credentials->billingCountry ?? ''))),
        ];

        foreach ($defaults as $field => $value) {
            if ($value === '') {
                abort(500, "UAB billing default [{$field}] is not configured.");
            }
        }

        return $defaults;
    }

    private function resolveGatewayPaymentMethods(UabCredentialService $uabCredentialService): string
    {
        $credentials = $uabCredentialService->getActiveCredential();
        $configuredMethods = trim((string) ($credentials->gatewayPaymentMethods ?? ''));

        return $configuredMethods !== '' ? $configuredMethods : PaymentMethod::gatewayOptions();
    }
}
