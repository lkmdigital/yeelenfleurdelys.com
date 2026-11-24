<?php

use App\Http\Controllers\Ops\AudienceController as OpsAudienceController;
use App\Http\Controllers\Ops\AuthController as OpsAuthController;
use App\Http\Controllers\Ops\DashboardController as OpsDashboardController;
use App\Http\Controllers\Ops\LogsController as OpsLogsController;
use App\Http\Controllers\Ops\MegaBoController as OpsMegaBoController;
use App\Http\Controllers\Ops\MonitoringController as OpsMonitoringController;
use App\Http\Controllers\Ops\PaymentsController as OpsPaymentsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthController as WebAuth;
use App\Http\Controllers\Web\PublicController as WebPublic;
use App\Http\Controllers\Web\BookingController as WebBooking;
use App\Http\Controllers\Web\ClientController as WebClient;
use App\Http\Controllers\Web\Host;
use App\Http\Controllers\Web\Admin;


/*
|--------------------------------------------------------------------------
| Site web (Blade) — mêmes adresses que l'ancien site Next.js
|--------------------------------------------------------------------------
| Les contrôleurs Web\* appellent l'API interne (App\Support\InternalApi) :
| toute la logique métier reste dans les contrôleurs API, déjà testés.
*/

// ── Site public ──────────────────────────────────────────────────────────
Route::get('/', [WebPublic::class, 'home'])->name('home');
Route::get('/accommodations', [WebPublic::class, 'accommodations'])->name('accommodations.index');
Route::get('/accommodations/{id}', [WebPublic::class, 'accommodation'])->name('accommodations.show');
Route::get('/rooms/{id}', [WebPublic::class, 'room'])->name('rooms.show');
Route::get('/faq', [WebPublic::class, 'faq'])->name('faq');
Route::get('/help', [WebPublic::class, 'help'])->name('help');
Route::get('/contact', [WebPublic::class, 'contact'])->name('contact');
Route::post('/contact', [WebPublic::class, 'sendContact'])->middleware('throttle:5,1')->name('contact.send');
Route::get('/{slug}', [WebPublic::class, 'legal'])->whereIn('slug', ['cgu', 'cgv', 'confidentialite'])->name('legal');
Route::get('/retrouver-reservation', [WebPublic::class, 'lookup'])->name('booking.lookup');
Route::post('/retrouver-reservation', [WebPublic::class, 'doLookup'])->middleware('throttle:10,1')->name('booking.lookup.submit');
Route::get('/review/booking/{token}', [WebPublic::class, 'reviewByToken'])->name('review.token');
Route::post('/review/booking/{token}', [WebPublic::class, 'submitReviewByToken'])->middleware('throttle:10,1')->name('review.token.submit');
Route::get('/paiement/{token}', [WebPublic::class, 'payByToken'])->name('pay.token');
Route::get('/favorites', [WebPublic::class, 'favorites'])->name('favorites');
Route::post('/favorites/{accommodation}', [WebPublic::class, 'toggleFavorite'])->middleware('auth')->name('favorites.toggle');

// ── Réservation ──────────────────────────────────────────────────────────
Route::get('/bookings/new', [WebBooking::class, 'create'])->name('bookings.new');
Route::post('/bookings', [WebBooking::class, 'store'])->middleware('throttle:10,1')->name('bookings.store');
Route::get('/bookings/success', [WebBooking::class, 'success'])->name('bookings.success');
Route::get('/bookings', [WebBooking::class, 'index'])->name('bookings.index');
Route::pattern('ref', '[A-Za-z0-9_-]{1,80}');
Route::get('/bookings/{ref}', [WebBooking::class, 'show'])->name('bookings.show');
Route::get('/bookings/{ref}/cancel', [WebBooking::class, 'cancelForm'])->name('bookings.cancel');
Route::post('/bookings/{ref}/cancel', [WebBooking::class, 'cancel'])->middleware('throttle:5,1')->name('bookings.cancel.submit');
Route::get('/bookings/{ref}/payment', [WebBooking::class, 'payment'])->name('bookings.payment');
Route::post('/bookings/{ref}/payment', [WebBooking::class, 'pay'])->middleware('throttle:15,1')->name('bookings.pay');
Route::get('/bookings/{ref}/payment/status', [WebBooking::class, 'paymentStatus'])->middleware('throttle:20,1')->name('bookings.payment.status');
Route::get('/bookings/{ref}/review', fn ($ref) => redirect()->route('client.reviews'));
Route::post('/bookings/{ref}/messages', [WebBooking::class, 'message'])->middleware(['auth', 'throttle:10,1'])->name('bookings.message');

// ── Connexion / inscription ──────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [WebAuth::class, 'showLogin'])->name('login');
        Route::post('/login', [WebAuth::class, 'login'])->middleware('throttle:10,1')->name('login.submit');
        Route::get('/register', [WebAuth::class, 'showRegister'])->name('register');
        Route::post('/register', [WebAuth::class, 'register'])->middleware('throttle:5,1')->name('register.submit');
        Route::get('/forgot-password', [WebAuth::class, 'showForgot'])->name('password.request');
        Route::post('/forgot-password', [WebAuth::class, 'forgot'])->middleware('throttle:5,1')->name('password.email');
        Route::get('/reset-password', [WebAuth::class, 'showReset'])->name('password.reset');
        Route::post('/reset-password', [WebAuth::class, 'reset'])->middleware('throttle:5,1')->name('password.update');
        Route::get('/verify-otp', [WebAuth::class, 'showOtp'])->name('otp');
        Route::post('/verify-otp', [WebAuth::class, 'verifyOtp'])->middleware('throttle:10,1')->name('otp.submit');
        Route::get('/two-factor', [WebAuth::class, 'show2fa'])->name('2fa');
        Route::post('/two-factor', [WebAuth::class, 'verify2fa'])->middleware('throttle:10,1')->name('2fa.submit');
        Route::get('/activate', [WebAuth::class, 'showActivate'])->name('activate');
        Route::post('/activate', [WebAuth::class, 'activate'])->middleware('throttle:5,1')->name('activate.submit');
        Route::get('/activate-staff', [WebAuth::class, 'showActivateStaff'])->name('activate-staff');
        Route::post('/activate-staff', [WebAuth::class, 'activateStaff'])->middleware('throttle:5,1')->name('activate-staff.submit');
        Route::get('/oauth-callback', [WebAuth::class, 'oauthCallback'])->name('oauth.callback');
    });
    Route::post('/logout', [WebAuth::class, 'logout'])->name('logout');
});
Route::get('/dashboard/admin/login', fn () => redirect()->route('login'))->name('admin.login');
Route::get('/dashboard', fn () => redirect(\App\Support\Nav::home(auth()->user())))->middleware('auth')->name('dashboard');

// ── Espace client ────────────────────────────────────────────────────────
Route::prefix('dashboard/user')->name('client.')->middleware('auth')->controller(WebClient::class)->group(function () {
    Route::get('/', 'dashboard')->name('dashboard');
    // Lien hérité des e-mails « entreprise » (pas d'espace entreprise sur ce site).
    Route::redirect('/entreprise', '/dashboard/user/programme');
    Route::get('/reservations', 'reservations')->name('reservations');
    Route::get('/voyages', 'trips')->name('trips');
    Route::get('/inbox', 'inbox')->name('inbox');
    Route::post('/inbox', 'inboxReply')->name('inbox.reply');
    Route::get('/favoris', 'favorites')->name('favorites');
    Route::get('/programme', 'programme')->name('programme');
    Route::post('/programme/voucher', 'claimVoucher')->name('programme.voucher');
    Route::get('/paiements', 'payments')->name('payments');
    Route::get('/avoirs', 'credits')->name('credits');
    Route::get('/avis', 'reviews')->name('reviews');
    Route::post('/avis', 'storeReview')->name('reviews.store');
    Route::get('/documents', 'documents')->name('documents');
    Route::post('/documents', 'uploadIdentity')->name('documents.upload');
    Route::get('/notifications', 'notifications')->name('notifications');
    Route::post('/notifications/read-all', 'readAllNotifications')->name('notifications.read-all');
    Route::get('/profil', 'profile')->name('profile');
    Route::put('/profil', 'updateProfile')->name('profile.update');
    Route::post('/profil/avatar', 'uploadAvatar')->name('profile.avatar');
    Route::get('/parametres', 'settings')->name('settings');
    Route::post('/parametres/password', 'changePassword')->name('settings.password');
    Route::get('/aide', 'help')->name('help');
});

// ── Espace gérant (un compte = un établissement) ─────────────────────────
Route::prefix('dashboard/host')->name('host.')->middleware(['auth', 'web.host'])->group(function () {
    Route::get('/', [Host\DashboardController::class, 'index'])->name('dashboard');

    Route::controller(Host\PropertyController::class)->group(function () {
        Route::get('/property', 'edit')->name('property');
        Route::put('/property', 'update')->name('property.update');
        Route::post('/property/media', 'uploadMedia')->name('property.media');
        Route::post('/property/media/{image}/primary', 'primaryMedia')->name('property.media.primary');
        Route::delete('/property/media/{image}', 'deleteMedia')->name('property.media.delete');
        Route::post('/property/submit', 'submit')->name('property.submit');
    });

    Route::controller(Host\RoomController::class)->prefix('rooms')->name('rooms')->group(function () {
        Route::get('/', 'index');
        Route::get('/new', 'create')->name('.create');
        Route::post('/', 'store')->name('.store');
        Route::get('/{room}/edit', 'edit')->name('.edit');
        Route::put('/{room}', 'update')->name('.update');
        Route::delete('/{room}', 'destroy')->name('.destroy');
        Route::get('/{room}/images', 'images')->name('.images');
        Route::post('/{room}/images', 'uploadImage')->name('.images.upload');
        Route::post('/{room}/images/{image}/primary', 'primaryImage')->name('.images.primary');
        Route::delete('/{room}/images/{image}', 'deleteImage')->name('.images.delete');
        Route::get('/{room}/calendar', 'calendar')->name('.calendar');
        Route::post('/{room}/calendar', 'updateCalendar')->name('.calendar.update');
        Route::post('/{room}/price-periods', 'storePeriod')->name('.periods.store');
        Route::delete('/{room}/price-periods/{period}', 'destroyPeriod')->name('.periods.destroy');
    });

    Route::get('/calendar', [Host\CalendarController::class, 'index'])->name('calendar');

    Route::controller(Host\BookingController::class)->group(function () {
        Route::get('/reservations', 'index')->name('reservations');
        Route::get('/reservations/export', 'export')->name('reservations.export');
        Route::get('/bookings/requests', 'requests')->name('requests');
        Route::get('/bookings/{booking}', 'show')->name('bookings.show');
        Route::post('/bookings/{booking}/{action}', 'act')->whereIn('action', ['approve', 'refuse', 'cancel', 'complete'])->name('bookings.act');
        Route::post('/bookings/{booking}/messages', 'message')->name('bookings.message');
        Route::get('/clients', 'clients')->name('clients');
        Route::get('/inbox', 'inbox')->name('inbox');
        Route::post('/inbox', 'inboxReply')->name('inbox.reply');
        Route::post('/check-in', 'checkIn')->name('checkin');
    });

    Route::controller(Host\ReviewController::class)->group(function () {
        Route::get('/reviews', 'index')->name('reviews');
        Route::post('/reviews/{review}/reply', 'reply')->name('reviews.reply');
    });

    Route::controller(Host\PromotionController::class)->prefix('promotions')->name('promotions')->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store')->name('.store');
        Route::put('/{promotion}', 'update')->name('.update');
        Route::post('/{promotion}/toggle', 'toggle')->name('.toggle');
        Route::delete('/{promotion}', 'destroy')->name('.destroy');
    });

    Route::controller(Host\FinanceController::class)->group(function () {
        Route::get('/finances', 'finances')->name('finances');
        Route::get('/stats', 'stats')->name('stats');
        Route::get('/programme', 'programme')->name('programme');
        Route::put('/programme', 'updateProgramme')->name('programme.update');
        Route::post('/finances/withdraw', 'withdraw')->name('finances.withdraw');
    });

    Route::controller(Host\StaffController::class)->prefix('staff')->name('staff')->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store')->name('.store');
        Route::put('/{staff}', 'update')->name('.update');
        Route::delete('/{staff}', 'destroy')->name('.destroy');
    });

    Route::controller(Host\SettingsController::class)->group(function () {
        Route::get('/settings', 'index')->name('settings');
        Route::put('/settings/profile', 'updateProfile')->name('settings.profile');
        Route::post('/settings/password', 'password')->name('settings.password');
        Route::put('/settings/notifications', 'notifications')->name('settings.notifications');
        Route::post('/settings/two-factor/{action}', 'twoFactor')->whereIn('action', ['setup', 'enable', 'disable'])->name('settings.2fa');
    });

    // Anciennes adresses du site Next.js (liens présents dans des e-mails).
    Route::redirect('/bookings', '/dashboard/host/reservations');
    Route::redirect('/profile', '/dashboard/host/settings');
    Route::redirect('/revenue', '/dashboard/host/finances');
    Route::redirect('/analytics', '/dashboard/host/stats');
    Route::get('/accommodations/{any}', fn () => redirect()->route('host.property'))->where('any', '.*');
});

// ── Administration globale ───────────────────────────────────────────────
Route::prefix('dashboard/admin')->name('admin.')->middleware(['auth', 'web.admin'])->group(function () {
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/analytics', [Admin\DashboardController::class, 'analytics'])->name('analytics');

    Route::controller(Admin\AccommodationController::class)->prefix('accommodations')->name('accommodations')->group(function () {
        Route::get('/', 'index');
        Route::get('/new', 'create')->name('.create');
        Route::post('/', 'store')->name('.store');
        Route::get('/{id}', 'show')->name('.show');
        Route::put('/{id}', 'update')->name('.update');
        Route::post('/{id}/status', 'status')->name('.status');
    });

    Route::controller(Admin\UserController::class)->group(function () {
        Route::get('/hosts', 'hosts')->name('hosts');
        Route::get('/hosts/{id}', 'host')->name('hosts.show');
        Route::post('/hosts/{id}/{action}', 'hostAct')->whereIn('action', ['validate', 'reject', 'suspend'])->name('hosts.act');
        Route::get('/users', 'index')->name('users');
        Route::get('/users/new', 'create')->name('users.create');
        Route::post('/users', 'store')->name('users.store');
        Route::get('/users/{id}', 'show')->name('users.show');
        Route::put('/users/{id}', 'update')->name('users.update');
        Route::post('/users/{id}/{action}', 'act')->whereIn('action', ['block', 'unblock', 'reset-password'])->name('users.act');
        Route::delete('/users/{id}', 'destroy')->name('users.destroy');
    });

    Route::controller(Admin\BookingController::class)->group(function () {
        Route::get('/reservations', 'index')->name('reservations');
        Route::get('/reservations/{id}', 'show')->name('reservations.show');
        Route::post('/reservations/{id}/{action}', 'act')->whereIn('action', ['approve', 'refuse', 'cancel', 'complete'])->name('reservations.act');
    });

    Route::controller(Admin\ContentController::class)->group(function () {
        Route::get('/reviews', 'reviews')->name('reviews');
        Route::post('/reviews/{id}/{action}', 'reviewAct')->whereIn('action', ['approve', 'hide'])->name('reviews.act');
        Route::get('/promotions', 'promotions')->name('promotions');
        Route::post('/promotions/{id}/toggle', 'togglePromotion')->name('promotions.toggle');
        Route::get('/programme', 'programme')->name('programme');
        Route::get('/juridique', 'legal')->name('legal');
        Route::put('/juridique/{slug}', 'updateLegal')->name('legal.update');
        Route::get('/journal-activite', 'activity')->name('activity');
    });

    Route::controller(Admin\FinanceController::class)->group(function () {
        Route::get('/comptabilite', 'accounting')->name('accounting');
        Route::get('/paiements', 'payments')->name('payments');
        Route::post('/paiements/{id}/{action}', 'paymentAct')->whereIn('action', ['confirm', 'reconcile'])->name('payments.act');
        Route::post('/retraits/{id}/{action}', 'withdrawalAct')->whereIn('action', ['approve', 'reject'])->name('withdrawals.act');
        Route::put('/comptabilite/commission', 'commissionRate')->name('accounting.rate');
        Route::post('/comptabilite/commissions/payees', 'markCommissionsPaid')->name('accounting.mark-paid');
    });

    Route::controller(Admin\SettingsController::class)->group(function () {
        Route::get('/settings/{tab?}', 'index')->name('settings');
        Route::put('/settings', 'update')->name('settings.update');
        Route::put('/settings/payment-methods/{id}', 'paymentMethod')->name('settings.payment-method');
    });

    Route::redirect('/revenue', '/dashboard/admin/comptabilite');
});

// Espace interne "Ops" (Blade + Vite + Tailwind), réservé aux administrateurs, indépendant
// du site public et de l'auth Sanctum de l'API — voir EnsureOpsAdmin.
Route::prefix('ops')->name('ops.')->group(function () {
    Route::get('/login', [OpsAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [OpsAuthController::class, 'login'])->name('login.submit');
    Route::post('/logout', [OpsAuthController::class, 'logout'])->name('logout');

    // Signal de vie pour un service externe de surveillance (jeton dans l'adresse, aucune session).
    Route::get('/heartbeat/{token}', [OpsMonitoringController::class, 'heartbeat'])->name('heartbeat');

    Route::middleware('ops.admin')->group(function () {
        Route::get('/', [OpsDashboardController::class, 'index'])->name('dashboard');

        Route::get('/audience', [OpsAudienceController::class, 'index'])->name('audience.index');

        Route::prefix('mega-bo')->name('megabo.')->group(function () {
            Route::get('/', [OpsMegaBoController::class, 'index'])->name('index');
            Route::post('/question', [OpsMegaBoController::class, 'ask'])->middleware('throttle:20,1')->name('ask');
            Route::post('/envoyer', [OpsMegaBoController::class, 'send'])->middleware('throttle:6,1')->name('send');
            Route::get('/rapports/{report}', [OpsMegaBoController::class, 'report'])->name('report');
        });

        Route::prefix('supervision')->name('monitoring.')->group(function () {
            Route::get('/', [OpsMonitoringController::class, 'index'])->name('index');
            Route::post('/run', [OpsMonitoringController::class, 'runNow'])->name('run');
            Route::get('/incidents', [OpsMonitoringController::class, 'incidents'])->name('incidents');
            Route::get('/erreurs', [OpsMonitoringController::class, 'errors'])->name('errors');
            Route::get('/captures/{path}', [OpsMonitoringController::class, 'screenshot'])->name('screenshot')->where('path', '[A-Za-z0-9._-]+');
            Route::get('/reglages', [OpsMonitoringController::class, 'settings'])->name('settings');
            Route::post('/reglages', [OpsMonitoringController::class, 'saveSettings'])->name('settings.save');
            Route::post('/reglages/test', [OpsMonitoringController::class, 'testAlert'])->name('settings.test');
        });

        Route::prefix('payments')->name('payments.')->group(function () {
            Route::get('/', [OpsPaymentsController::class, 'index'])->name('index');
            Route::post('/{payment}/reconcile', [OpsPaymentsController::class, 'reconcile'])->name('reconcile');
        });

        Route::prefix('logs')->name('logs.')->group(function () {
            Route::get('/', [OpsLogsController::class, 'index'])->name('index');
            Route::get('/tail', [OpsLogsController::class, 'tail'])->name('tail');
        });
    });
});

