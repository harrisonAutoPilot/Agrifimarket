<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CalculatorController;
use App\Http\Controllers\CreditController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DealerController;
use App\Http\Controllers\DemandController;
use App\Http\Controllers\InsuranceController;
use App\Http\Controllers\LogisticsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicSiteController;
use App\Http\Controllers\SupplyController;
use App\Http\Controllers\TradeController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicSiteController::class, 'home'])->name('home');
Route::get('/prices', [PublicSiteController::class, 'prices'])->name('prices.index');
Route::get('/prices/export', [PublicSiteController::class, 'exportPrices'])->name('prices.export');
Route::get('/orders', [PublicSiteController::class, 'orders'])->name('orders.index');
Route::get('/marketplace', [PublicSiteController::class, 'marketplace'])->name('marketplace.index');
Route::get('/marketplace/{listing}', [PublicSiteController::class, 'listing'])->name('marketplace.show');
Route::post('/marketplace/{listing}/inquire', [PublicSiteController::class, 'inquire'])->name('marketplace.inquire');
Route::get('/hubs', [PublicSiteController::class, 'hubs'])->name('hubs');
Route::get('/support', [PublicSiteController::class, 'support'])->name('support');
Route::post('/support', [PublicSiteController::class, 'storeSupport'])->name('support.store');
Route::get('/legal/{page}', [PublicSiteController::class, 'legal'])->name('legal');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:8,1');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/onboarding', [OnboardingController::class, 'show'])->name('onboarding');
    Route::post('/onboarding', [OnboardingController::class, 'update'])->name('onboarding.update');
    Route::post('/onboarding/documents', [OnboardingController::class, 'storeDocument'])->name('onboarding.documents');
});

Route::middleware(['auth', 'approved'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
    Route::match(['get', 'post'], '/calculator', [CalculatorController::class, 'index'])->name('calculator.index');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{alert}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/password', [ProfileController::class, 'password'])->name('profile.password');

    Route::get('/supplies', [SupplyController::class, 'index'])->name('supplies.index');
    Route::get('/supplies/new', [SupplyController::class, 'create'])->name('supplies.create');
    Route::post('/supplies', [SupplyController::class, 'store'])->name('supplies.store');
    Route::get('/supplies/{supply}/edit', [SupplyController::class, 'edit'])->name('supplies.edit');
    Route::put('/supplies/{supply}', [SupplyController::class, 'update'])->name('supplies.update');
    Route::post('/supplies/{supply}/status', [SupplyController::class, 'status'])->name('supplies.status');

    Route::get('/demands', [DemandController::class, 'index'])->name('demands.index');
    Route::get('/demands/board', [DemandController::class, 'board'])->name('demands.board');
    Route::get('/demands/new', [DemandController::class, 'create'])->name('demands.create');
    Route::post('/demands', [DemandController::class, 'store'])->name('demands.store');
    Route::get('/demands/{demand}/edit', [DemandController::class, 'edit'])->name('demands.edit');
    Route::put('/demands/{demand}', [DemandController::class, 'update'])->name('demands.update');
    Route::post('/demands/{demand}/status', [DemandController::class, 'status'])->name('demands.status');
    Route::get('/demands/{demand}/accept', [DemandController::class, 'acceptForm'])->name('demands.accept');
    Route::post('/demands/{demand}/accept', [DemandController::class, 'accept'])->name('demands.accept.store');

    Route::get('/trades', [TradeController::class, 'index'])->name('trades.index');
    Route::get('/trades/{trade}', [TradeController::class, 'show'])->name('trades.show');
    Route::get('/trades/{trade}/statement', [TradeController::class, 'statement'])->name('trades.statement');
    Route::post('/trades/{trade}/contract', [TradeController::class, 'contract'])->name('trades.contract');
    Route::post('/trades/{trade}/haul/{quote}', [TradeController::class, 'acceptHaul'])->name('trades.haul');
    Route::post('/trades/{trade}/cover/{quote}', [TradeController::class, 'acceptCover'])->name('trades.cover');
    Route::post('/trades/{trade}/transition', [TradeController::class, 'transition'])->name('trades.transition');
    Route::post('/trades/{trade}/pay', [TradeController::class, 'pay'])->name('trades.pay');
    Route::post('/trades/{trade}/dispute', [TradeController::class, 'dispute'])->name('trades.dispute');

    Route::get('/matches', [TradeController::class, 'matches'])->name('matches.index');
    Route::post('/matches/{match}/agree', [TradeController::class, 'agree'])->name('matches.agree');
    Route::post('/matches/{match}/dismiss', [TradeController::class, 'dismiss'])->name('matches.dismiss');

    Route::get('/fleet', [LogisticsController::class, 'fleet'])->name('logistics.fleet');
    Route::post('/fleet', [LogisticsController::class, 'storeTruck'])->name('logistics.trucks.store');
    Route::post('/fleet/{truck}/availability', [LogisticsController::class, 'availability'])->name('logistics.trucks.availability');
    Route::post('/fleet/rates', [LogisticsController::class, 'storeRate'])->name('logistics.rates');
    Route::get('/hauls', [LogisticsController::class, 'jobs'])->name('logistics.jobs');
    Route::post('/trades/{trade}/quote', [LogisticsController::class, 'quote'])->name('logistics.quote');

    Route::get('/cover', [InsuranceController::class, 'index'])->name('insurance.index');
    Route::post('/cover/products', [InsuranceController::class, 'storeProduct'])->name('insurance.products');
    Route::post('/trades/{trade}/cover-quote', [InsuranceController::class, 'quote'])->name('insurance.quote');

    Route::get('/credit', [CreditController::class, 'index'])->name('credit.index');
    Route::post('/credit/{bundle}/apply', [CreditController::class, 'apply'])->name('credit.apply');
    Route::get('/credit/applications/{application}', [CreditController::class, 'show'])->name('credit.show');
    Route::post('/credit/applications/{application}/contribute', [CreditController::class, 'contribute'])->name('credit.contribute');
    Route::post('/credit/applications/{application}/pickup', [CreditController::class, 'pickup'])->name('credit.pickup');
    Route::post('/credit/applications/{application}/repay', [CreditController::class, 'repay'])->name('credit.repay');

    Route::get('/shop-desk', [DealerController::class, 'index'])->name('dealer.index');
    Route::post('/shop-desk', [DealerController::class, 'store'])->name('dealer.store');
});

Route::middleware(['auth', 'approved', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'home'])->name('admin.home');
    Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
    Route::get('/users/{user}', [AdminController::class, 'showUser'])->name('admin.users.show');
    Route::post('/users/{user}', [AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::post('/users/{user}/credit', [AdminController::class, 'creditUser'])->name('admin.users.credit');
    Route::post('/documents/{document}', [AdminController::class, 'reviewDocument'])->name('admin.documents');
    Route::post('/listings/{listing}', [AdminController::class, 'reviewListing'])->name('admin.listings');
    Route::post('/trucks/{truck}', [AdminController::class, 'reviewTruck'])->name('admin.trucks');
    Route::post('/credit/{application}', [AdminController::class, 'approveCredit'])->name('admin.credit');
    Route::get('/prices', [AdminController::class, 'prices'])->name('admin.prices');
    Route::post('/prices', [AdminController::class, 'storePrice'])->name('admin.prices.store');
    Route::get('/trades', [AdminController::class, 'trades'])->name('admin.trades');
    Route::get('/disputes', [AdminController::class, 'disputes'])->name('admin.disputes');
    Route::post('/disputes/{dispute}', [AdminController::class, 'resolveDispute'])->name('admin.disputes.resolve');
    Route::get('/hubs', [AdminController::class, 'centers'])->name('admin.centers');
    Route::post('/hubs', [AdminController::class, 'storeCenter'])->name('admin.centers.store');
    Route::get('/bundles', [AdminController::class, 'bundles'])->name('admin.bundles');
    Route::post('/bundles', [AdminController::class, 'storeBundle'])->name('admin.bundles.store');
    Route::get('/reports', [AdminController::class, 'reports'])->name('admin.reports');
    Route::get('/reports/trades.csv', [AdminController::class, 'exportTrades'])->name('admin.reports.export');
});
