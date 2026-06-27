<?php

use App\Http\Controllers\API\AssetController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\BankAccountController;
use App\Http\Controllers\API\CustomizationController;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\GroupController;
use App\Http\Controllers\API\NotificationsController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\API\SiteJobController;
use App\Http\Controllers\API\SubscriptionController;
use App\Http\Controllers\API\TemplateController;
use App\Http\Controllers\API\UsersController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\MobileMoneyController;
use App\Http\Controllers\API\WalletsController;
use App\Http\Controllers\API\EscrowsController;
use App\Http\Controllers\API\FlutterwaveWebhookController;
use App\Http\Controllers\API\WithdrawalsController;
use App\Http\Controllers\API\ReviewController;

//route for auth
Route::prefix('auth')->group(function () {
    Route::post('/register', [UsersController::class, 'register']);
    Route::post('/verify-registration', [UsersController::class, 'verifyRegistration']);
    Route::post('/resend-registration-otp', [UsersController::class, 'resendRegistrationOtp']);
    Route::post('/login', [UsersController::class, 'login']);
    Route::post('/sms-verification-one', [AuthController::class, 'smsVerificationOne']);
    Route::post('/sms-verification-two', [AuthController::class, 'smsVerificationTwo']);
});


//route for recover-password
Route::prefix('recover-password')->group(function () {
    Route::post('/one', [AuthController::class, 'recoverPasswordOne']);
    Route::post('/two', [AuthController::class, 'recoverPasswordTwo']);
});

//route for email verification
Route::prefix('email-verification')->group(function () {
    Route::post('/send-code', [AuthController::class, 'sendEmailVerificationCode']);
    Route::post('/verify', [AuthController::class, 'verifyEmail']);
});

//route for dashboard
Route::prefix('dashboard')->group(function () {
    Route::post('/note', [DashboardController::class, 'note']);
    Route::post('/contact-us', [DashboardController::class, 'contactus']);
    Route::get('/faqs', [DashboardController::class, 'faqs']);
    Route::get('/settings', [DashboardController::class, 'getAllSettings']);
    Route::get('/states/{country_id}', [DashboardController::class, 'getStates']);
});

//public marketplace routes
Route::get('/assets', [AssetController::class, 'index']);
Route::get('/assets/most-liked', [AssetController::class, 'mostLiked']);
Route::get('/assets/show/{id}', [AssetController::class, 'show']);
Route::get('/assets/user/{user_id}', [AssetController::class, 'getUserAssets'])->where('user_id', '[0-9]+');
Route::get('/templates', [TemplateController::class, 'index']);
Route::get('/templates/show/{id}', [TemplateController::class, 'show']);
Route::get('/templates/user/{user_id}', [TemplateController::class, 'getUserTemplates'])->where('user_id', '[0-9]+');
Route::get('/groups', [GroupController::class, 'index']);
Route::get('/groups/show/{id}', [GroupController::class, 'show']);
Route::get('/jobs', [SiteJobController::class, 'index']);
Route::get('/jobs/show/{id}', [SiteJobController::class, 'show']);
Route::get('/subscriptions/plans', [SubscriptionController::class, 'plans']);

// Top Firms landing-page slideshare (public – only Firm subscribers appear)
Route::get('/top-firms', [ProfileController::class, 'topFirms']);
Route::get('/designers', [ProfileController::class, 'designers']);

//public profile routes
Route::get('/profile/{user_id}', [ProfileController::class, 'getProfile'])->where('user_id', '[0-9]+');
Route::get('/profile/user-portfolios/{user_id}', [ProfileController::class, 'getUserPortfolios'])->where('user_id', '[0-9]+');

// Flutterwave payment callback (public — Flutterwave redirects browser here)
Route::get('/wallet/topup/callback', [WalletsController::class, 'topupCallback']);
Route::get('/assets/purchase/callback', [AssetController::class, 'purchaseCallback']);
Route::get('/templates/purchase/callback', [TemplateController::class, 'purchaseCallback']);
Route::get('/groups/subscribe/callback', [GroupController::class, 'subscribeCallback']);

// Flutterwave webhooks (public — server-to-server events)
Route::post('/flutterwave/webhook', [FlutterwaveWebhookController::class, 'handle']);

Route::middleware('jwt.auth')->group(function () {

    //route for auth
    Route::prefix('auth')->group(function () {
        Route::post('/update', [UsersController::class, 'update']);
        Route::post('/delete-user', [AuthController::class, 'deleteUser']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::post('/update-geolocation', [AuthController::class, 'updateGeoLocation']);
        Route::post('/get-user-by-phone-number', [AuthController::class, 'getUserByPhoneNumber']);
        Route::post('/change-password', [AuthController::class, 'chnagePassword']);
        Route::post('/change-pin', [AuthController::class, 'chnagePin']);
        Route::get('/my-referrals', [UsersController::class, 'my_referrals']);
        Route::get('/all-users', [AuthController::class, 'fetchAllUsers']);
    });

    //route for bank-account
    Route::prefix('bank-account')->group(function () {
        Route::get('/', [BankAccountController::class, 'index']);
        Route::post('/store', [BankAccountController::class, 'store']);
        Route::post('/update', [BankAccountController::class, 'update']);
        Route::post('/destroy', [BankAccountController::class, 'destroy']);
    });

    //route for currencies
    Route::get('/currencies', [App\Http\Controllers\API\CurrencyController::class, 'index']);

    //route for mobile-money
    Route::prefix('mobile-money')->group(function () {
        Route::get('/', [MobileMoneyController::class, 'index']);
        Route::post('/store', [MobileMoneyController::class, 'store']);
        Route::post('/destroy', [MobileMoneyController::class, 'destroy']);
    });

    //route for notifications
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationsController::class, 'index']);
        Route::post('/setting', [NotificationsController::class, 'setting']);
        Route::post('/read', [NotificationsController::class, 'readNotification']);
        Route::post('/read-all', [NotificationsController::class, 'readAllNotification']);
    });

    //route for profile
    Route::prefix('profile')->group(function () {
        Route::get('/me', [ProfileController::class, 'getMyProfile']);
        Route::post('/update', [ProfileController::class, 'updateProfile']);
        Route::get('/skills/mine', [ProfileController::class, 'getMySkills']);
        Route::post('/skill/add', [ProfileController::class, 'addSkill']);
        Route::post('/skill/remove', [ProfileController::class, 'removeSkill']);
        Route::get('/portfolios/mine', [ProfileController::class, 'getMyPortfolios']);
        Route::post('/portfolio/store', [ProfileController::class, 'storePortfolio']);
        Route::post('/portfolio/update', [ProfileController::class, 'updatePortfolio']);
        Route::post('/portfolio/destroy', [ProfileController::class, 'destroyPortfolio']);
        Route::post('/portfolio/media/add', [ProfileController::class, 'addPortfolioMedia']);
        Route::post('/portfolio/media/remove', [ProfileController::class, 'removePortfolioMedia']);
        Route::post('/identity/upload', [ProfileController::class, 'uploadIdentity']);
    });

    //route for assets
    Route::prefix('assets')->group(function () {
        Route::get('/mine', [AssetController::class, 'myAssets']);
        Route::post('/store', [AssetController::class, 'store']);
        Route::post('/update', [AssetController::class, 'update']);
        Route::post('/destroy', [AssetController::class, 'destroy']);
        Route::post('/file/add', [AssetController::class, 'addFile']);
        Route::post('/file/remove', [AssetController::class, 'removeFile']);
        Route::post('/purchase', [AssetController::class, 'purchaseAsset'])->middleware('throttle:10,1');
        Route::post('/toggle-favorite', [AssetController::class, 'toggleFavorite']);
        Route::post('/apply-for-customization', [AssetController::class, 'applyForCustomizationRole']);
        Route::get('/customization-designers', [AssetController::class, 'getCustomizationDesigners']);
        Route::get('/file/{file_id}/download', [AssetController::class, 'downloadAssetFile']);
    });

    //route for templates
    Route::prefix('templates')->group(function () {
        Route::get('/mine', [TemplateController::class, 'myTemplates']);
        Route::post('/store', [TemplateController::class, 'store']);
        Route::post('/update', [TemplateController::class, 'update']);
        Route::post('/destroy', [TemplateController::class, 'destroy']);
        Route::post('/file/add', [TemplateController::class, 'addFile']);
        Route::post('/file/remove', [TemplateController::class, 'removeFile']);
        Route::post('/purchase', [TemplateController::class, 'purchaseTemplate'])->middleware('throttle:10,1');
        Route::get('/file/{file_id}/download', [TemplateController::class, 'downloadTemplateFile']);
    });

    //route for groups
    Route::prefix('groups')->group(function () {
        Route::get('/mine', [GroupController::class, 'myGroups']);
        Route::post('/store', [GroupController::class, 'store']);
        Route::post('/update', [GroupController::class, 'update']);
        Route::post('/destroy', [GroupController::class, 'destroy']);
        Route::post('/subscribe', [GroupController::class, 'subscribe'])->middleware('throttle:10,1');
        Route::post('/unsubscribe', [GroupController::class, 'unsubscribe']);
        Route::get('/my-subscriptions', [GroupController::class, 'mySubscriptions']);
    });

    //route for jobs
    Route::prefix('jobs')->group(function () {
        Route::get('/mine', [SiteJobController::class, 'myJobs']);
        Route::post('/store', [SiteJobController::class, 'store']);
        Route::post('/update', [SiteJobController::class, 'update']);
        Route::post('/destroy', [SiteJobController::class, 'destroy']);
        Route::post('/apply', [SiteJobController::class, 'apply']);
        Route::post('/withdraw-application', [SiteJobController::class, 'withdrawApplication']);
        Route::get('/my-applications', [SiteJobController::class, 'myApplications']);
        Route::get('/{id}/applications', [SiteJobController::class, 'jobApplications']);
        Route::post('/application/update-status', [SiteJobController::class, 'updateApplicationStatus']);
    });

    //route for customizations
    Route::prefix('customizations')->group(function () {
        Route::get('/received', [CustomizationController::class, 'myReceivedRequests']);
        Route::get('/sent', [CustomizationController::class, 'mySentRequests']);
        Route::get('/show/{id}', [CustomizationController::class, 'show']);
        Route::get('/chat/{customization_id}', [CustomizationController::class, 'chat']);
        Route::post('/store', [CustomizationController::class, 'store']);
        Route::post('/chat/send-message', [CustomizationController::class, 'sendMessage']);
        Route::post('/update-status', [CustomizationController::class, 'updateStatus']);
        Route::post('/accept', [CustomizationController::class, 'accept']);
        Route::post('/reject', [CustomizationController::class, 'reject']);
        Route::post('/milestone/add', [CustomizationController::class, 'addMilestone']);
        Route::post('/milestone/remove', [CustomizationController::class, 'removeMilestone']);
        Route::post('/price-adjustment/add', [CustomizationController::class, 'addPriceAdjustment']);
        Route::post('/price-adjustment/request', [CustomizationController::class, 'requestPriceAdjustment']);
        Route::post('/price-adjustment/respond', [CustomizationController::class, 'respondPriceAdjustment']);
        Route::post('/revision/request', [CustomizationController::class, 'requestRevision']);
        Route::post('/revision/respond', [CustomizationController::class, 'respondRevision']);
    });

    //route for subscriptions
    Route::prefix('subscriptions')->group(function () {
        Route::get('/my', [SubscriptionController::class, 'mySubscription']);
        Route::post('/subscribe', [SubscriptionController::class, 'subscribe']);
        Route::post('/cancel', [SubscriptionController::class, 'cancel']);
        Route::get('/access', [SubscriptionController::class, 'accessInfo']);
    });

    //route for wallet
    Route::prefix('wallet')->group(function () {
        Route::get('/histories', [WalletsController::class, 'histories']);
        Route::post('/topup/initiate', [WalletsController::class, 'initiateTopup'])->middleware('throttle:10,1');
    });

    //route for escrows
    Route::prefix('escrows')->group(function () {
        Route::get('/', [EscrowsController::class, 'index']);
        Route::post('/store', [EscrowsController::class, 'store'])->middleware('throttle:10,1');
        Route::post('/cancel', [EscrowsController::class, 'cancel'])->middleware('throttle:10,1');
        Route::post('/debit', [EscrowsController::class, 'debitEscrow'])->middleware('throttle:10,1');
    });

    //route for withdrawals
    Route::prefix('withdrawals')->group(function () {
        Route::post('/store', [WithdrawalsController::class, 'store'])->middleware('throttle:5,1');
        Route::get('/histories', [WithdrawalsController::class, 'histories']);
        Route::post('/verify-receiver', [WithdrawalsController::class, 'verifyReceiver']);
    });

    //route for authenticated dashboard
    Route::prefix('dashboard')->group(function () {
        Route::get('/analytics', [DashboardController::class, 'analytics']);
    });

    //route for reviews
    Route::prefix('reviews')->group(function () {
        Route::get('/my', [ReviewController::class, 'my']);
        Route::get('/{type}/{id}', [ReviewController::class, 'forItem']);
        Route::post('/store', [ReviewController::class, 'store']);
    });

    Broadcast::routes();

});
