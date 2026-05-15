@extends('frontend.layouts.index')
@section('title', 'Order Detail')

@section('content')
    @include('components.alert')
    <style>
        .order-card {
            border: 1px solid #eee;
            border-radius: 9px;
            background: #fff;
            box-shadow: 0 10px 35px rgba(0, 0, 0, .08);
            overflow: hidden;
            margin-bottom: 30px;
        }

        .order-card-header {
            background: linear-gradient(135deg, #2d285f, #b94b00);
            color: #fff;
            padding: 10px 15px;
            /* reduced */
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            border-radius: 10px 10px 0 0;
        }

        .order-id {
            font-size: 16px;
            /* smaller */
            font-weight: 700;
            margin: 0;
        }

        .payment-badge {
            background: rgba(255, 255, 255, 0.15);
            color: #fff;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .order-card-body {
            padding: 14px;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 18px;
        }

        .detail-item {
            background: #fafafa;
            border: 1px solid #eee;
            border-radius: 12px;
            padding: 15px 18px;
        }

        .detail-label {
            color: #777;
            font-size: 13px;
            margin-bottom: 5px;
        }

        .detail-value {
            font-size: 16px;
            font-weight: 700;
            word-break: break-word;
        }

        .order-actions {
            padding: 0 28px 28px;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn-renew,
        .btn-upload {
            border: none;
            border-radius: 10px;
            padding: 12px 24px;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-renew {
            background: #b94b00;
            color: #fff;
        }

        .btn-upload {
            background: #004aad;
            color: #fff;
        }

        .btn-renew:hover,
        .btn-upload:hover {
            color: #fff;
            opacity: .9;
        }

        @media(max-width:768px) {
            .detail-grid {
                grid-template-columns: 1fr;
            }

            .order-card-header {
                align-items: flex-start;
            }
        }
    </style>
    <x-banner key="order_detail" />

    <section class="py-5">
        <div class="container">
            <div class="col-xl-12">
                {{-- UAB Pay Order --}}
                <div class="order-card">
                    <div class="order-card-header">
                        <div>
                            <div class="detail-label text-white-50">Order ID</div>
                            <div class="order-id">#JOYTEL-20100</div>
                        </div>
                        <div class="payment-badge">
                            <i class="fa fa-credit-card"></i> Pay UAB Pay
                        </div>
                    </div>

                    <div class="order-card-body">
                        <div class="detail-grid">
                            <div class="detail-item">
                                <div class="detail-label">Product Name</div>
                                <div class="detail-value">eSim-AIS-SIM2FLY399</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Price</div>
                                <div class="detail-value">1,000 MMK</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Qty</div>
                                <div class="detail-value">1</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Data</div>
                                <div class="detail-value">1GB</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Valid Days</div>
                                <div class="detail-value">30 Days</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">ICCID Number</div>
                                <div class="detail-value">8988303000000614227</div>
                            </div>
                        </div>
                    </div>

                    <div class="order-actions">
                        <a href="#" class="btn-renew">
                            <i class="fa fa-refresh"></i> Renew
                        </a>
                        <a href="#" class="btn-upload">
                            <i class="fa fa-upload"></i> Upload Payment Slip
                        </a>
                    </div>
                </div>

                {{-- Direct Transfer Order --}}
                <div class="order-card">
                    <div class="order-card-header">
                        <div>
                            <div class="detail-label text-white-50">Order ID</div>
                            <div class="order-id">#JOYTEL-20100</div>
                        </div>
                        <div class="payment-badge">
                            <i class="fa fa-exchange"></i> Pay Direct Transfer
                        </div>
                    </div>

                    <div class="order-card-body">
                        <div class="detail-grid">
                            <div class="detail-item">
                                <div class="detail-label">Product Name</div>
                                <div class="detail-value">eSim-AIS-SIM2FLY399</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Price</div>
                                <div class="detail-value">1,000 MMK</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Qty</div>
                                <div class="detail-value">1</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Data</div>
                                <div class="detail-value">1GB</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Valid Days</div>
                                <div class="detail-value">30 Days</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">ICCID Number</div>
                                <div class="detail-value">8988303000000614227</div>
                            </div>
                        </div>
                    </div>

                    <div class="order-actions">
                        <a href="#" class="btn-renew">
                            <i class="fa fa-refresh"></i> Renew
                        </a>
                    </div>
                </div>

            </div>

        </div>
    </section>
@endsection
