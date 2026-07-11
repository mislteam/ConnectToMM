<?php

namespace App\Payment\Providers\Uab\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Payment\Providers\Uab\DTO\CallbackData;
use App\Payment\Providers\Uab\Http\Requests\CallbackNotifyRequest;
use App\Payment\Providers\Uab\Http\Requests\CallbackRedirectRequest;
use App\Payment\Providers\Uab\Services\Callback\CallbackService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class CallbackController extends Controller
{
    public function __construct(
        private readonly CallbackService $callbackService,
    ) {}

    public function notify(CallbackNotifyRequest $request): JsonResponse
    {
        $payload = $request->validated();

        $result = $this->callbackService->handle(new CallbackData(
            requestId: (string) $payload['RequestID'],
            transactionId: $payload['TransactionID'] ?? null,
            transactionReferenceNumber: $payload['TransactionReferenceNumber'] ?? null,
            status: $payload['RespDescription'] ?? null,
            signature: $request->header('X-Auth-Signature'),
            payload: $payload,
            headers: [
                'X-Auth-Timestamp' => $request->header('X-Auth-Timestamp'),
                'X-Auth-Nonce' => $request->header('X-Auth-Nonce'),
                'X-Auth-AccessKey' => $request->header('X-Auth-AccessKey'),
            ],
            eventType: 'notify',
            uri: $request->path(),
            url: $request->url(),
        ));

        return response()->json($result, 200);
    }

    public function success(CallbackRedirectRequest $request): RedirectResponse
    {
        $payload = $request->validated();

        $result = $this->callbackService->handleSuccessRedirect(new CallbackData(
            requestId: (string) $payload['RequestID'],
            transactionId: $payload['TransactionID'] ?? null,
            transactionReferenceNumber: $payload['TransactionReferenceNumber'] ?? null,
            status: 'SUCCESS',
            signature: $payload['Signature'],
            payload: $payload,
            eventType: 'success',
            uri: $request->path(),
            url: $request->url(),
        ));

        return $this->redirectToPaymentResult(
            $result,
            'success',
            'Your payment was completed successfully.'
        );
    }

    public function cancel(CallbackRedirectRequest $request): RedirectResponse
    {
        $payload = $request->validated();

        $result = $this->callbackService->handleCancelRedirect(new CallbackData(
            requestId: (string) $payload['RequestID'],
            transactionId: $payload['TransactionID'] ?? null,
            transactionReferenceNumber: $payload['TransactionReferenceNumber'] ?? null,
            status: 'CANCELLED',
            signature: $payload['Signature'],
            payload: $payload,
            eventType: 'cancel',
            uri: $request->path(),
            url: $request->url(),
        ));

        return $this->redirectToPaymentResult(
            $result,
            'error',
            'Your payment was cancelled.'
        );
    }

    private function redirectToPaymentResult(array $result, string $flashKey, string $flashMessage): RedirectResponse
    {
        $outerOrderId = trim((string) ($result['outer_order_id'] ?? ''));

        if ($outerOrderId !== '') {
            return redirect()
                ->route('roam.payment.show', ['outerOrderId' => $outerOrderId])
                ->with($flashKey, $flashMessage);
        }

        return redirect()
            ->route('customer.roam.order.detail')
            ->with($flashKey, $flashMessage);
    }
}
