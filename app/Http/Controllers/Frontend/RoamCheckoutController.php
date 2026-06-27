<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RoamOrder;
use App\Services\Roam\RoamIccidSupportService;
use App\Services\Roam\RoamOrderDraftService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class RoamCheckoutController extends Controller
{
    public function placeOrder(
        Request $request,
        RoamOrderDraftService $draftService,
        RoamIccidSupportService $iccidSupportService
    ) {
        $validated = $request->validate([
            'iccid_numbers' => ['nullable', 'array'],
            'payment_method' => ['required', 'in:direct_bank_transfer'],
            'terms' => ['accepted'],
        ]);

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

        $result = $draftService->createDraftOrdersFromCart(
            $customer,
            $cart,
            $iccidNumbersByIndex,
            $validated['payment_method']
        );

        session()->forget([
            'roam_order_cart',
            'iccid_numbers',
        ]);
        session()->put('roam_last_outer_order_id', $result['outer_order_id']);

        // Next step: payment page (mock for now; replace with gateway redirect when integrating Dinger Pay).
        return redirect()->route('roam.payment.show', ['outerOrderId' => $result['outer_order_id']]);
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
        $paymentSetting = \App\Models\PaymentSetting::orderBy('id')->get();
        if ($paymentMethod === 'direct_bank_transfer') {
            $credentials = $paymentSetting->first()?->directBankCredentials;
        } else if ($paymentMethod == 'UAB Pay') {
            // $credentials = $paymentSetting->last()?->uabCredential;
        }

        if ($orders->isEmpty()) {
            return redirect()->route('customer.roam.order.detail')->with('error', 'Order not found.');
        }

        $slipPath = data_get($orders->first()?->raw_response, 'payment.slip.path');
        $statusView = $this->buildPaymentStatusView($orders, !empty($slipPath));

        return view('frontend.payment', [
            'outer_order_id' => $outerOrderId,
            'orders' => $orders,
            'total' => $orders->sum(fn($order) => (float) $order->billable_total_price),
            'payment_status_view' => $statusView,
            'credentials' => $credentials,
        ]);
    }

    public function uploadPaymentSlip(Request $request, string $outerOrderId)
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
}
