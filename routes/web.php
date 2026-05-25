<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\CustomerAuthController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\AdminController;
use App\Http\Controllers\Backend\BlogCategoryController;
use App\Http\Controllers\Backend\BlogController;
use App\Http\Controllers\Backend\ContactUsController;
use App\Http\Controllers\Backend\CurrencyController;
use App\Http\Controllers\Backend\CustomerController;
use App\Http\Controllers\Backend\FooterPageController;
use App\Http\Controllers\Backend\GeneralSettingController;
use App\Http\Controllers\Backend\JoytelController;
use App\Http\Controllers\Backend\JoyUsageLocationController;
use App\Http\Controllers\Backend\OrderController;
use App\Http\Controllers\Backend\PageController;
use App\Http\Controllers\Backend\RoamOrderController;
use App\Http\Controllers\Backend\RoamController;
use App\Http\Controllers\Backend\RoamPhysicalController;
use App\Http\Controllers\Backend\SubCategoryController;
use App\Http\Controllers\Frontend\ESimController;
use App\Http\Controllers\Frontend\FrontendJoytelController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\PhysicalSimController;

Route::get('/', function () {
    return redirect()->route('Index');
});

Route::get('/login', function () {
    return redirect()->route('user.login');
});

/// ==================
// Public Routes
// ==================
Route::get('/admin/login', [LoginController::class, 'adminLogin'])->name('login.admin');
Route::post('/login', [LoginController::class, 'login'])->name('login');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ==================
// User Routes
// ==================


Route::get('/home', [HomeController::class, 'index'])->name('Index');
Route::get('/about', [HomeController::class, 'about'])->name('About');
Route::get('/faq', [HomeController::class, 'faq'])->name('Faq');
Route::get('/blog-article', [HomeController::class, 'blog'])->name('Blog');
Route::get('/contact', [HomeController::class, 'contact'])->name('Contact');
Route::post('/contact', [ContactUsController::class, 'store'])->name('contact.store');

// user
Route::middleware('guest:customers')->group(function () {
    Route::get('/register', [CustomerAuthController::class, 'showRegister'])->name('user.register');
    Route::post('/register', [CustomerAuthController::class, 'register'])->name('customer.register.submit');

    Route::get('/user/login', [CustomerAuthController::class, 'showLogin'])->name('user.login');
    Route::post('/user/login', [CustomerAuthController::class, 'login'])->name('customer.login.submit');

    Route::get('/forgot-password', [CustomerAuthController::class, 'showForgotPassword'])->name('customer.password.request');
    Route::post('/forgot-password', [CustomerAuthController::class, 'sendPasswordResetOtp'])->name('customer.password.email');

    Route::get('/customer/google/redirect', [CustomerAuthController::class, 'googleRedirect'])->name('customer.google.redirect');
    Route::get('/customer/google/callback', [CustomerAuthController::class, 'googleCallback'])->name('customer.google.callback');
    Route::get('/auth/google/call-back', [CustomerAuthController::class, 'googleCallback']);

    Route::get('/customer/email/verify', [CustomerAuthController::class, 'showVerificationNotice'])->name('verification.notice');
    Route::post('/customer/email/verify', [CustomerAuthController::class, 'verifyEmailOtp'])->name('verification.verify.otp');
    Route::delete('/customer/email/resend', [CustomerAuthController::class, 'resendEmailOtp'])->name('verification.resend.otp');
});

Route::middleware('auth:customers')->group(function () {
    Route::post('/customer/logout', [CustomerAuthController::class, 'logout'])->name('customer.logout');

    // joy checkout
    Route::post('/joytel-package/cart/{joytel}', [FrontendJoytelController::class, 'cart'])->name('joytelpackage.cart');
    Route::get('/joytel-package/checkout', [FrontendJoytelController::class, 'checkout'])->name('joytelpackage.checkout');

    // roam checkout
    Route::prefix('roam')->group(function () {
        Route::post('/esim/cart', [ESimController::class, 'cart'])->name('roam.esim.cart');
        Route::get('/esim/cart/page', [ESimController::class, 'cartPage'])->name('roam.esim.cartpage');
        Route::get('/esim/checkout', [ESimController::class, 'checkout'])->name('roam.esim.checkout');
        Route::patch('/esim/cart/{key}', [ESimController::class, 'updateCartQuantity'])->name('roam.esim.cart.update');
        Route::delete('/esim/remove-cart/{key}', [ESimController::class, 'removeCart']);

        Route::post('/physical/cart', [PhysicalSimController::class, 'cart'])->name('roam.physical.cart');
        Route::get('/physical/cart/page', [PhysicalSimController::class, 'cartPage'])->name('roam.physical.cartpage');
        Route::get('/physical/checkout', [PhysicalSimController::class, 'checkout'])->name('roam.physical.checkout');
        Route::patch('/physical/cart/{key}', [PhysicalSimController::class, 'updateCartQuantity'])->name('roam.physical.cart.update');
        Route::delete('/physical/remove-cart/{key}', [PhysicalSimController::class, 'removeCart']);
    });

    Route::get('/customer/profile', [HomeController::class, 'customerProfile'])->name('customer.profile.index');
    Route::post('/customer/profile/edit/{customer}/{edit_type}', [HomeController::class, 'customerEdit'])->name('frontend.customer.edit');

    // order detail page
    Route::get('/customer/order-detail', [HomeController::class, 'orderDetail'])->name('customer.order.detail');
});

// e-sim
Route::get('/esim/roam', [ESimController::class, 'roam'])->name('esim.roam');

// e-sim roam search
Route::get('/search/esim-roam', [ESimController::class, 'roamSearch'])->name('esim.roamsearch');
Route::get('/roam-package/view/{id}', [ESimController::class, 'roamView'])->name('esim.roampackageview');

// physical sim roam
Route::get('/physical-sim/roam', [PhysicalSimController::class, 'roamPhysical'])->name('physical.roam');

//physical sim roam search
Route::get('/search/physical-roam', [PhysicalSimController::class, 'roamPhysicalSearch'])->name('physical.roamsearch');
Route::get('/roamphysical-package/view/{id}', [PhysicalSimController::class, 'roamPhysicalView'])->name('physical.roampackageview');


Route::prefix("joytel")->group(function () {
    // esim
    Route::get("/esim/search", [FrontendJoytelController::class, "esimIndex"])->name("esimIndex"); // search page
    Route::get("/esim/packages", [FrontendJoytelController::class, "esimSearch"])->name("esim.search"); // search and show packages

    Route::get("/package/{joytel}", [FrontendJoytelController::class, "packageView"])->name("joytel.packageview"); // show each package

    // physical
    Route::get('/physical-sim/search', [FrontendJoytelController::class, 'physicalIndex'])->name('physicalIndex');
    Route::get("/physical-sim/packages", [FrontendJoytelController::class, "physicalSearch"])->name("physical.search"); // search and show packages

});


Route::middleware(['auth'])->group(function () {
    //dashboard
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard.admin');

    // customer
    Route::get('/all-customer', [CustomerController::class, 'index'])->name('customer.index');
    Route::get('/customer/show/{customer}', [CustomerController::class, 'show'])->name('customer.show');
    Route::get('/customer/edit/{customer}', [CustomerController::class, 'edit'])->name('customer.edit');
    Route::post('/customer/update/{id}', [CustomerController::class, 'update'])->name('customer.update');
    Route::post('/customer/change-password', [CustomerController::class, 'changePassword'])->name('customer.change-password');

    // order
    Route::get('/all-orders', [OrderController::class, 'index'])->name('order.index');

    // All Admin Users
    Route::get('/show', [AdminController::class, 'index'])->name('show.admin');
    Route::get('/create', [AdminController::class, 'create'])->name('create.admin');
    Route::get('/admin/view/{id}', [AdminController::class, 'view'])->name('view.admin');
    Route::post('/store', [AdminController::class, 'store'])->name('admin.store');
    Route::delete('/admin/destory/{id}', [AdminController::class, 'destroy'])->name('admin.destroy');

    //admin update 
    Route::get('/edit/{id}', [AdminController::class, 'edit'])->name('admin.edit');
    Route::post('/admin/update/{id}', [AdminController::class, 'update'])->name('admin.update');

    Route::post('/admin/change-password', [AdminController::class, 'changePassword'])->name('admin.change-password');

    // currency index
    Route::get('/currency', [CurrencyController::class, 'index'])->name('currency.index');
    Route::get('/currency/edit/{currency}', [CurrencyController::class, 'edit'])->name('currency.edit');
    Route::patch('/currency/update/{currency}', [CurrencyController::class, 'update'])->name('currency.update');

    Route::get('/edit-usd-currency/{usdCurrency}', [CurrencyController::class, 'editUsdCurrency'])->name('usdcurrency.edit');
    Route::put('/edit-usd-currency/{usdCurrency}', [CurrencyController::class, 'updateUsdCurrency'])->name('usdcurrency.update');
    Route::prefix('setting')->group(function () {

        // General Setting 

        Route::get('admin/general', [GeneralSettingController::class, 'index'])->name('generalIndex');
        Route::get('/general/edit/{type}', [GeneralSettingController::class, 'edit'])->name('generalEdit');
        Route::patch('/general/update/{type}', [GeneralSettingController::class, 'update'])->name('generalUpdate');
    });

    Route::prefix('roam')->group(function () {
        // Roam
        Route::get('/roam-esim', [RoamController::class, 'Esimindex'])->name('roamEsimIndex');
        Route::get('/roam-api', [RoamController::class, 'Apiindex'])->name('roamApiIndex');
        Route::get('/roam-sku', [RoamController::class, 'Skuindex'])->name('roamSkuIndex');
        Route::get('/esim/update-data', [RoamController::class, 'UpdateData'])->name('updateData');
        Route::get('/physical/update-data', [RoamPhysicalController::class, 'UpdateData'])->name('physical.updateData');
        //sku list
        Route::get('/roam-skus', [RoamController::class, 'StoreSkus'])->name('roamStoreSku');
        Route::post('/roam-skus/toggle-status/{skuid}', [RoamController::class, 'toggleStatus'])->name('roam-skus.toggle-status');
        Route::post('/roam-physicalskus/toggle-status/{skuid}', [RoamPhysicalController::class, 'toggleStatus'])->name('roam-physicalskus.toggle-status');
        // get packages
        Route::get('/roam-skus/packages', [RoamController::class, 'syncSkusAndPackages'])->name('roamSkuPackages');

        Route::get('/roam-esim/edit/{skuid}', [RoamController::class, 'RoamesimEdit'])->name('roamEsimEdit');
        Route::put('/roam/{id}', [RoamController::class, 'update'])->name('roam.update');
        Route::get('/physical/edit/{skuid}', [RoamPhysicalController::class, 'RoamphysicalEdit'])->name('roamPhysicalEdit');
        Route::put('/roamphysical/{id}', [RoamPhysicalController::class, 'update'])->name('roamphysical.update');
        // Roam Physical
        Route::get('/roam-physicalsku', [RoamPhysicalController::class, 'Skuindex'])->name('roamphysical.SkuIndex');
        Route::get('/roam-physical', [RoamPhysicalController::class, 'Physicalindex'])->name('roamphysical.Index');
        Route::get('/roam-physicalskus/packages', [RoamPhysicalController::class, 'syncPhysicalSkusAndPackages'])->name('roamphysical.SkuPackages');



        //Api Credentials
        Route::post('/roam-api/store', [RoamController::class, 'store'])->name('roam-api.store');

        Route::prefix('orders')->group(function () {
            Route::get('/', [RoamOrderController::class, 'index'])->name('roam.orders.index');
            Route::get('/create', [RoamOrderController::class, 'create'])->name('roam.orders.create');
            Route::post('/', [RoamOrderController::class, 'store'])->name('roam.orders.store');
            Route::get('/{roamOrder}', [RoamOrderController::class, 'show'])->name('roam.orders.show');
            Route::post('/{roamOrder}/sync', [RoamOrderController::class, 'sync'])->name('roam.orders.sync');
            Route::post('/{roamOrder}/transition', [RoamOrderController::class, 'transition'])->name('roam.orders.transition');
            Route::post('/{roamOrder}/send-pdf', [RoamOrderController::class, 'sendPdf'])->name('roam.orders.send-pdf');
        });


        //manage status

        Route::post('/roam/update-package-status', [RoamController::class, 'updatePackageStatus'])->name('roam.updatePackageStatus');
        Route::post('/roamphysical/update-package-status', [RoamPhysicalController::class, 'updatePackageStatus'])->name('roamphysical.updatePackageStatus');

        //manage price
        Route::post('/pricelist/store', [RoamController::class, 'updateExchangeRate'])->name('pricelist.store');
        Route::post('/physical-pricelist/store', [RoamPhysicalController::class, 'updatePhysicalExchangeRate'])->name('physicalpricelist.store');
    });

    // joytel
    Route::prefix('joytel')->group(function () {
        // esim
        Route::get('/esim', [JoytelController::class, 'esim'])->name('esim.index');
        Route::get('/esim-edit/{esim}', [JoytelController::class, 'esimEdit'])->name('esim.edit');
        Route::patch('/esim-update/{esim}', [JoytelController::class, 'updateEsim'])->name('esim.update');

        // status + manage price
        Route::post('/update-code-status', [JoytelController::class, 'updateCodeStatus'])->name('update.code.status');
        Route::post('/update-price', [JoytelController::class, 'updateExchangeRate'])->name('update.price');

        // delete sim
        Route::delete('/delete-esim/{id}', [JoytelController::class, 'destroy']);

        // physical
        Route::get('/physical-sim', [JoytelController::class, 'physical'])->name('physical.index');
        Route::get('/physical-edit/{recharge}', [JoytelController::class, 'editPhysical'])->name('physical.edit');
        Route::patch('/physical-update/{recharge}', [JoytelController::class, 'updatePhysical'])->name('physical.update');

        // eSIM import
        Route::post('/import-esim', [JoytelController::class, 'importEsim'])
            ->name('joytel.import.esim');

        // Recharge import
        Route::post('/import-recharge', [JoytelController::class, 'importRecharge'])
            ->name('joytel.import.recharge');
    });

    // currency index
    Route::get('/currency', [CurrencyController::class, 'index'])->name('currency.index');
    Route::get('/currency/edit/{currency}', [CurrencyController::class, 'edit'])->name('currency.edit');
    Route::patch('/currency/update/{currency}', [CurrencyController::class, 'update'])->name('currency.update');

    // region
    Route::prefix('region')->group(function () {
        Route::get('/list', [JoyUsageLocationController::class, 'index'])->name('region.index');
        Route::get('/create', [JoyUsageLocationController::class, 'create'])->name('region.create');
        Route::post('/store', [JoyUsageLocationController::class, 'store'])->name('region.store');
        Route::get('/edit/{region}', [JoyUsageLocationController::class, 'edit'])->name('region.edit');
        Route::put('/update/{region}', [JoyUsageLocationController::class, 'update'])->name('region.update');
    });

    Route::prefix('subcategory')->group(function () {
        //Sub Category
        Route::get('/sub-categories', [SubCategoryController::class, 'index'])->name('subcategoryIndex');
        Route::get('/sub-category/create', [SubCategoryController::class, 'create'])->name('subcategoryCreate');
        Route::post('/sub-category/store', [SubCategoryController::class, 'store'])->name('subcategoryStore');
        Route::get('/sub-category/edit/{subCategory}', [SubCategoryController::class, 'edit'])->name('subcategoryEdit');
        Route::patch('/sub-category/update/{subcategory}', [SubCategoryController::class, 'update'])->name('subcategoryUpdate');
    });

    Route::prefix('page')->group(function () {
        // home
        Route::get('/home', [PageController::class, 'homeIndex'])->name('page.home.index');
        Route::get('/aboutus', [PageController::class, 'aboutIndex'])->name('page.about.index');
        Route::get('/common-section', [PageController::class, 'commonIndex'])->name('page.common.index');
        // section
        Route::get('/section/{section_key}/edit/{section}', [PageController::class, 'sectionEdit'])->name('page.section.edit');
        Route::patch('/edit/{section}', [PageController::class, 'sectionUpdate'])->name('page.section.update');

        // faqs
        Route::get('/faq', [PageController::class, 'faqIndex'])->name('page.faq.index');
        Route::get('/faq/create', [PageController::class, 'faqCreate'])->name('page.faq.create');
        Route::post('/faq/create', [PageController::class, 'faqStore'])->name('page.faq.store');
        Route::get('/faq/edit/{faq}', [PageController::class, 'faqEdit'])->name('page.faq.edit');
        Route::patch('/faq/edit/{faq}', [PageController::class, 'faqUpdate'])->name('page.faq.update');
        Route::delete('/faq/delete/{faq}', [PageController::class, 'faqDelete']);

        //banners
        Route::get('/banner', [PageController::class, 'bannerIndex'])->name('page.banner.index');
        Route::get('/banner/edit/{banner}', [PageController::class, 'bannerEdit'])->name('page.banner.edit');
        Route::patch('/banner/edit/{banner}', [PageController::class, 'bannerUpdate'])->name('page.banner.update');

        // contact info
        Route::get('/contact-info', [FooterPageController::class, 'contactIndex'])->name('footer.contact.index');
        Route::get('/contact-info/edit/{info}', [FooterPageController::class, 'contactEdit'])->name('footer.contact.edit');
        Route::patch('/contact-info/edit/{info}', [FooterPageController::class, 'contactUpdate'])->name('footer.contact.update');

        // support
        Route::get('/support', [FooterPageController::class, 'supportIndex'])->name('footer.support.index');
        Route::get('/support/edit/{support}', [FooterPageController::class, 'supportEdit'])->name('footer.support.edit');
        Route::patch('/support/edit/{support}', [FooterPageController::class, 'supportUpdate'])->name('footer.support.update');

        // important links
        Route::get('important-link', [FooterPageController::class, 'importantIndex'])->name('footer.important.index');
        Route::get('important-link/edit/{link}', [FooterPageController::class, 'importantEdit'])->name('footer.important.edit');
        Route::patch('important-link/edit/{link}', [FooterPageController::class, 'importantUpdate'])->name('footer.important.update');
    });

    Route::prefix('category')->group(function () {
        Route::get('/', [BlogCategoryController::class, 'index'])->name('blog.category.index');
        Route::get('/create', [BlogCategoryController::class, 'create'])->name('blog.category.create');
        Route::post('/create', [BlogCategoryController::class, 'store'])->name('blog.category.store');
        Route::get('/edit/{category}', [BlogCategoryController::class, 'edit'])->name('blog.category.edit');
        Route::patch('/edit/{category}', [BlogCategoryController::class, 'update'])->name('blog.category.update');
    });

    Route::prefix('blogs')->group(function () {
        Route::get('/', [BlogController::class, 'index'])->name('blog.index');
        Route::get('/create', [BlogController::class, 'create'])->name('blog.create');
        Route::post('/create', [BlogController::class, 'store'])->name('blog.store');
        Route::get('/edit/{blog}', [BlogController::class, 'edit'])->name('blog.edit');
        Route::patch('/edit/{blog}', [BlogController::class, 'update'])->name('blog.update');
        Route::delete('/delete-blog/{blog}', [BlogController::class, 'destory']);
    });

    Route::prefix('messages')->group(function () {
        Route::get('/', [ContactUsController::class, 'index'])->name('message.index');
        Route::get('/show/{message}', [ContactUsController::class, 'show'])->name('message.show');
    });
});
