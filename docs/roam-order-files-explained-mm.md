# Roam Order Files Explanation (Burmese)

ဒီ document က Roam Order implementation ထဲက file တစ်ခုချင်းစီ ဘာတာဝန်ယူလဲဆိုတာကို မြန်မာလိုရှင်းပြထားတာပါ။

## Overall Flow

Roam Order feature တစ်ခုလုံးရဲ့ အဓိက flow က ဒီလိုသွားပါတယ်။

1. Customer က package ရွေးပြီး cart ထဲထည့်တယ်
2. Checkout page မှာ ICCID နဲ့ payment method ဖြည့်တယ်
3. System က local database ထဲ draft order အရင် create လုပ်တယ်
4. Customer က payment page မှာ bank transfer လုပ်ပြီး slip upload တင်တယ်
5. Admin က payment approve လုပ်တယ်
6. System က approve ပြီးမှ Roam API ကိုခေါ်ပြီး actual provisioning လုပ်တယ်
7. Success ဖြစ်ရင် ICCID, activation data, PDF link, status တွေကို sync လုပ်တယ်
8. Customer account မှာ order detail ကိုပြန်ကြည့်နိုင်တယ်

## Controllers

### `app/Http/Controllers/Auth/LoginController.php`

- Admin login/logout အတွက်သုံးတဲ့ controller ပါ
- Roam Order feature သီးသန့် logic မရှိပေမယ့် admin panel ထဲဝင်ပြီး Roam orders ကို approve, retry, refund လုပ်ဖို့ ဒီ auth flow လိုပါတယ်

### `app/Http/Controllers/Backend/OrderController.php`

- Admin ဘက်က Roam order management အဓိက controller ပါ
- Roam orders list ကို grouped reference အလိုက်ပြပေးတယ်
- Order detail page ထုတ်ပေးတယ်
- Customer တင်ထားတဲ့ payment slip ရှိမရှိစစ်ပြီး payment approve လုပ်တယ်
- Roam API failed orders ကို retry လုပ်ပေးတယ်
- Refund logic ကို internal payment refund / Roam API refund ဆိုပြီးခွဲကိုင်တယ်

### `app/Http/Controllers/Backend/RoamOrderController.php`

- Roam orders ကို JSON/API style နဲ့ manage လုပ်ဖို့သီးသန့် controller ပါ
- Order list/filter JSON response ထုတ်ပေးတယ်
- Admin create payload နဲ့ frontend-like payload နှစ်မျိုးလုံးကိုလက်ခံပြီး order create လုပ်နိုင်တယ်
- Specific order ကို show လုပ်တယ်
- Roam API data နဲ့ sync လုပ်တယ်
- Manual status transition လုပ်တယ်
- PDF email / PDF link ပို့ဖို့ handler ခေါ်ပေးတယ်

### `app/Http/Controllers/Frontend/HomeController.php`

- Customer profile နဲ့ customer-side order history ကိုကိုင်ပါတယ်
- Roam orders နဲ့ Joytel orders ကို tab အလိုက်ခွဲပြတယ်
- `roamOrderDetail()` မှာ outer order id တစ်ခုအောက်က Roam orders အားလုံးကိုစုစည်းပြီး detail page ထုတ်ပေးတယ်
- Customer ဖက်ကမြင်ရမယ့် order status label, amount, product name summary တွေလည်းဒီ controller ကပြင်ဆင်ပေးတယ်

### `app/Http/Controllers/Frontend/PhysicalSimController.php`

- Roam Physical SIM package listing, package detail, cart, checkout preparation logic တွေကိုကိုင်ပါတယ်
- Cart ထဲ item ထည့်တဲ့အချိန် `api_code`, `dp_info`, `plan_type`, `order_type`, `service_type` တွေကို session cart ထဲထည့်သိမ်းတယ်
- Recharge / New order အလိုက် quantity ခွင့်ပြု/မပြု စစ်တယ်
- Physical checkout page အတွက် ICCID length rules, item labels, subtotal စတာတွေကိုပြင်ဆင်ပေးတယ်

### `app/Http/Controllers/Frontend/RoamCheckoutController.php`

- Frontend checkout submit လုပ်တဲ့ controller ပါ
- Customer checkout form ကလာတဲ့ ICCID numbers, payment method, terms ကို validate လုပ်တယ်
- Recharge order တွေအတွက် ICCID digit length ကိုစစ်တယ်
- `RoamIccidSupportService` နဲ့ selected package ကို အဲဒီ ICCID က support လုပ်မလားစစ်တယ်
- Valid ဖြစ်ရင် `RoamOrderDraftService` နဲ့ draft orders create လုပ်တယ်
- Payment page ကို redirect လုပ်တယ်
- Payment slip upload တင်တာကို handle လုပ်ပြီး `raw_response.payment.slip` ထဲသိမ်းတယ်

## Models

### `app/Models/RoamOrder.php`

- Roam order master model ပါ
- `our_status` နဲ့ `roam_status` constants, labels, transition rules အားလုံးကိုဒီ model ထဲမှာ define လုပ်ထားတယ်
- Refund method labels တွေလည်းဒီမှာရှိတယ်
- `customer()` relation နဲ့ customer ကိုချိတ်ထားတယ်
- `items()` relation နဲ့ `RoamOrderItem` records တွေကိုချိတ်ထားတယ်
- `customer_status_label` accessor က customer view မှာဖော်ပြမယ့် status text ကိုထုတ်ပေးတယ်
- `billable_total_price` accessor က actual total amount ကိုတွက်ပေးတယ်

### `app/Models/RoamOrderItem.php`

- Roam order တစ်ခုအောက်က SIM/card-level detail model ပါ
- ICCID, mobile number, activation code, SM-DP+, APN, validity, used MB, PDF URL စတာတွေကိုသိမ်းတယ်
- API response ထဲက card data တွေကို row တစ်ခုချင်းစီခွဲသိမ်းဖို့အသုံးပြုတယ်

## Services

### `app/Services/Roam/OrderStateMachineService.php`

- `our_status` ကိုဘယ် status ကနေ ဘယ် status သို့သွားခွင့်ရှိလဲ စစ်ပေးတယ်
- Invalid transition ဖြစ်ရင် exception ပစ်တယ်
- Order lifecycle ကို rule-based နဲ့ထိန်းချုပ်ဖို့သုံးတယ်

### `app/Services/Roam/RoamCheckoutFlowService.php`

- Cart ကနေ order create flow ကို service layer မှာစုစည်းထားတဲ့ service ပါ
- Cart items normalize လုပ်တယ်
- Payload build လုပ်တယ်
- Place order လုပ်ပြီး local status ကို Pending Payment စဖြစ်အောင်ညှိပေးတယ်
- Current implementation မှာ draft-first flow သုံးနေပေမယ့် ဒီ service က direct placement flow အတွက် support ပေးနေတယ်

### `app/Services/Roam/RoamIccidSupportService.php`

- Recharge order အတွက် user ထည့်တဲ့ ICCID က selected package ကို support လုပ်မလုပ် စစ်ပေးတဲ့ service ပါ
- Roam API `getIccidSupportPackageInfo` endpoint ကိုခေါ်တယ်
- Cart item ထဲက selected SKU / api_code ကို resolve လုပ်တယ်
- Returned support packages နဲ့ user selection ကို match လုပ်တယ်
- Unsupported ဖြစ်ရင် customer-friendly error message ပြန်ပေးတယ်

### `app/Services/Roam/RoamOrderDraftService.php`

- Payment မတိုင်ခင် local database ထဲ draft order create လုပ်ပေးတဲ့ service ပါ
- Roam API ကိုဒီအဆင့်မှာမခေါ်သေးဘူး
- `roam_order_num` unique required ဖြစ်လို့ temporary `TMP-...` order number ထုတ်ပေးတယ်
- Shared `outer_order_id` တစ်ခုအောက်မှာ cart item တစ်ခုချင်းစီကို order တစ်ခုစီ create လုပ်တယ်
- Cart item, iccid numbers, draft flag တွေကို `raw_response` ထဲသိမ်းတယ်

### `app/Services/Roam/RoamOrderService.php`

- Roam API integration အဓိက service ပါ
- API authenticate လုပ်တယ်
- New order / recharge order endpoint ရွေးပြီး request payload build လုပ်တယ်
- Existing draft order ကို actual upstream order အဖြစ် provision လုပ်နိုင်တယ်
- `getOrderInfo`, `queryOrder`, `handlerEsimPdf`, `refundOrder` စတဲ့ Roam API calls တွေလုပ်တယ်
- API response ကို local `roam_orders` နဲ့ `roam_order_items` tables ထဲ sync လုပ်တယ်
- Temporary order number ကို real upstream order number နဲ့ replace လုပ်ပေးနိုင်တယ်

### `app/Services/Roam/RoamPaymentFlowService.php`

- Paid ဖြစ်ပြီးသား order group ကို finalize လုပ်ဖို့သုံးတဲ့ service ပါ
- Pending Payment -> Paid -> On Hold -> API Processing -> API Success / Completed ဆိုတဲ့ progression ကိုကူညီတယ်
- Sync success ဖြစ်ရင် completed သို့တိုးတယ်
- Completed ဖြစ်ပြီး email မပို့ရသေးရင် PDF email ပို့ဖို့ကြိုးစားတယ်

### `app/Services/Roam/RoamProvisioningFlowService.php`

- Admin payment approve ပြီးမှ actual provisioning လုပ်တဲ့ အရေးကြီးဆုံး post-payment service ပါ
- Draft orders တွေကို Paid/On Hold/API Processing သို့တိုးပြီး Roam API ကိုခေါ်တယ်
- Success ဖြစ်ရင် completed သို့ finalize လုပ်တယ်
- Failed ဖြစ်ရင် API_FAILED သို့ပြောင်းတယ်
- Retry failed orders logic ကိုလည်းဒီ service ထဲမှာကိုင်ထားတယ်

## Database

### `database/migrations/2026_05_06_000001_create_roam_orders_table.php`

- `roam_orders` table ကိုတည်ဆောက်တယ်
- Customer, sku, price_id, api_code, service type, order type, quantity, pricing, status, purchase date စတဲ့ order master data တွေကိုသိမ်းဖို့ columns ထည့်ထားတယ်
- `raw_response` JSON column က local request, API request, API response, payment slip, refund metadata စတာတွေကိုသိမ်းဖို့အသုံးဝင်တယ်
- Query performance အတွက် indexes တွေလည်းထည့်ထားတယ်

### `database/migrations/2026_05_06_000002_create_roam_order_items_table.php`

- `roam_order_items` table ကိုတည်ဆောက်တယ်
- Parent `roam_order_id` နဲ့ချိတ်ထားတယ်
- ICCID, mobile number, activation code, PDF URL, validity, date range, raw card payload ကိုသိမ်းတယ်
- Order တစ်ခုအောက်မှာ card records အများကြီးရှိနိုင်တာကြောင့် item table သီးသန့်ခွဲထားတာပါ

### `database/seeders/BannerSeeder.php`

- Checkout, Payment, Order Detail, My Account စတဲ့ pages တွေအတွက် banner seed data ထည့်တယ်
- Roam order flow pages မှာ banner component ကဒီ data ကိုသုံးနိုင်ပါတယ်

## Admin Views

### `resources/views/admin/layouts/sidebar.blade.php`

- Admin sidebar menu ထဲမှာ order menu ကို Roam Orders နဲ့ Joytel Orders ဆိုပြီးခွဲပြထားတယ်
- Order-related routes တွေ active ဖြစ်တဲ့အချိန် menu state မှန်အောင် control လုပ်တယ်

### `resources/views/admin/order/joytel-list.blade.php`

- Joytel orders အတွက် view placeholder ပါ
- Roam order system နဲ့ Joytel orders ကို UI အဆင့်မှာခွဲသုံးဖို့ထားထားတာဖြစ်တယ်

### `resources/views/admin/order/roam-list.blade.php`

- Admin Roam orders list page ပါ
- Stats cards တွေပြတယ်
- Grouped order references, customer, amount, status, created date စတာတွေပြတယ်
- Admin က Roam orders overview ကြည့်ဖို့ main screen ဖြစ်တယ်

### `resources/views/admin/order/roam-order-view.blade.php`

- Admin Roam order detail page ပါ
- Order meta, payment info, SIM details, item table, summary စတာတွေပြတယ်
- Approve Payment, Retry Roam API, Refund စတဲ့ action buttons တွေဒီ page မှာရှိတယ်

### `resources/views/admin/order/index.blade.php`

- ဒီ file ကိုဖျက်ထားပါတယ်
- အရင် combined admin order page ဖြစ်နိုင်ပြီး အခု Roam/Joytel split views နဲ့အစားထိုးထားပါတယ်

## Frontend Views

### `resources/views/components/alert.blade.php`

- Session success/error/validation messages တွေကို SweetAlert popup နဲ့ပြတဲ့ shared component ပါ
- ICCID support validation failure လိုမျိုး error popup HTML ကိုလည်းပြပေးတယ်

### `resources/views/frontend/esim/checkout.blade.php`

- eSIM checkout form ပါ
- Selected cart items summary ကိုပြတယ်
- ICCID input fields ကို item အလိုက်ပြတယ်
- Payment method နဲ့ terms checkbox ပါတယ်
- Form submit ကို `roam.place-order` route သို့ပို့တယ်

### `resources/views/frontend/esim/roam-package-view.blade.php`

- Roam eSIM package detail page ပါ
- Package image, provider, network, coverage, selectable plans, quantity UI ကိုပြတယ်
- Add to cart အတွက် user selection form ပါတယ်

### `resources/views/frontend/payment.blade.php`

- Order create ပြီးနောက် customer ကိုပို့တဲ့ payment page ပါ
- Outer order id, total amount, payment status badge, transfer instructions ကိုပြတယ်
- Bank account info ကိုပြတယ်
- Payment slip upload / reupload form ကိုပြတယ်
- Status အလိုက် “Pending Payment”, “Waiting Approval”, “Completed”, “Refunded” စတဲ့ messaging ကိုပြတယ်

### `resources/views/frontend/physical/checkout.blade.php`

- Physical SIM checkout page ပါ
- Recharge physical order အတွက် dp_info ပေါ်မူတည်ပြီး ICCID 18/19 digits validation UI ပါတယ်
- Selected items summary နဲ့ payment section ကိုပြတယ်

### `resources/views/frontend/physical/roam-physical-package-view.blade.php`

- Roam physical SIM package detail page ပါ
- Country, provider, network, coverage, package attributes, valid plans, plan selector, quantity selector တွေကိုပြတယ်
- Add to cart form မှာ physical-specific data တွေပါဝင်တယ်

### `resources/views/frontend/user/profile.blade.php`

- Customer profile page ပါ
- Profile edit form အပြင် Roam/Joytel order history tabs ပါတယ်
- Roam orders ကို grouped rows အနေနဲ့ amount, status, created time, detail link နဲ့ပြတယ်

### `resources/views/frontend/user/roam-order-detail.blade.php`

- Customer ဘက်က Roam order detail page ပါ
- Outer order id အောက်က orders အားလုံးကို card view နဲ့ပြတယ်
- Amount, payment method, status, SIM details, ICCID, PDF links, renew-related info စတာတွေကိုထုတ်ပြတယ်
- Pending payment ဖြစ်သေးရင် payment page ပြန်သွားနိုင်အောင် action ပါတတ်တယ်

### `resources/views/frontend/user/order-detail.blade.php`

- ဒီ file ကိုဖျက်ထားပါတယ်
- အရင် generic order detail page ဖြစ်နိုင်ပြီး အခု Roam-specific detail page နဲ့အစားထိုးထားပါတယ်

## Routes

### `routes/web.php`

- Roam feature routes အားလုံးကိုချိတ်ထားတဲ့ main route file ပါ
- Customer side routes:
  - Roam eSIM cart / checkout
  - Roam physical cart / checkout
  - Place order
  - Payment page
  - Upload payment slip
  - Customer Roam order detail
- Admin side routes:
  - Roam orders JSON/API routes
  - Roam order list/detail pages
  - Approve payment
  - Retry Roam API
  - Refund

## CSS / UI Support

### `public/assets/css/style.css`

- Global frontend styling file ပါ
- Typography, colors, buttons, layout styles ကို manage လုပ်တယ်
- Roam payment/profile/order detail pages တွေက shared base styles အများကြီးဒီ file ကိုမှီတယ်

## Quick Summary by Role

### Order Creation

- `RoamCheckoutController.php`
- `RoamOrderDraftService.php`
- `PhysicalSimController.php`
- `resources/views/frontend/*checkout*.blade.php`

### API Integration

- `RoamOrderService.php`
- `RoamIccidSupportService.php`
- `RoamProvisioningFlowService.php`

### Status Management

- `RoamOrder.php`
- `OrderStateMachineService.php`
- `OrderController.php`

### Customer Pages

- `HomeController.php`
- `resources/views/frontend/payment.blade.php`
- `resources/views/frontend/user/profile.blade.php`
- `resources/views/frontend/user/roam-order-detail.blade.php`

### Admin Pages

- `OrderController.php`
- `RoamOrderController.php`
- `resources/views/admin/order/roam-list.blade.php`
- `resources/views/admin/order/roam-order-view.blade.php`

## Final Note

ဒီ implementation ရဲ့အဓိက design point က "payment မတိုင်ခင် Roam API ကိုမခေါ်ဘဲ draft order အရင်သိမ်းထားပြီး admin approve ပြီးမှ actual provisioning လုပ်မယ်" ဆိုတဲ့ပုံစံပါ။ အဲဒါကြောင့် payment slip workflow, draft order numbers, admin approval, retry/refund logic တွေကဒီ feature ထဲမှာအရေးကြီးဆုံးအစိတ်အပိုင်းတွေဖြစ်ပါတယ်။
