@php
    $walletBalance = (int) ($wallet_balance ?? optional($customer?->customerWallet)->balance ?? 0);
@endphp

<div class="wallet-payment-panel">
    <div class="wallet-payment-heading">
        <span>Your Wallet</span>
        <strong>{{ number_format($walletBalance) }}</strong>
    </div>
    <div class="wallet-payment-divider"></div>
    <label class="wallet-payment-choice" for="paymentWallet">
        <input type="radio" value="wallet" id="paymentWallet" name="payment_method" required
            {{ $selectedPaymentMethod === 'wallet' ? 'checked' : '' }}>
        <span>Pay with Wallet</span>
    </label>
</div>
