<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Controllers\NewPasswordController;
use Laravel\Fortify\Http\Controllers\EmailVerificationPromptController;
use Laravel\Fortify\Http\Controllers\VerifyEmailController;
use Laravel\Fortify\Http\Controllers\EmailVerificationNotificationController;
use Laravel\Fortify\Http\Controllers\ProfileInformationController;
use Laravel\Fortify\Http\Controllers\PasswordController;
use Laravel\Fortify\Http\Controllers\TwoFactorAuthenticationController;
use Laravel\Fortify\Features;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        $this->registerFortifyRoutes();
    }

    protected function registerFortifyRoutes()
    {
        Route::group(['middleware' => config('fortify.middleware', ['web']), 'namespace' => null], function () {
            if (Features::enabled(Features::resetPasswords())) {
                Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])
                    ->middleware(['guest:'.config('fortify.guard')])
                    ->name('password.reset.custom');

                Route::post('/reset-password', [NewPasswordController::class, 'store'])
                    ->middleware(['guest:'.config('fortify.guard')])
                    ->name('password.update.custom');
            }

            if (Features::enabled(Features::emailVerification())) {
                Route::get('/email/verify', [EmailVerificationPromptController::class, '__invoke'])
                    ->middleware(['auth:'.config('fortify.guard')])
                    ->name('verification.notice.custom');

                Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
                    ->middleware(['auth:'.config('fortify.guard'), 'signed', 'throttle:6,1'])
                    ->name('verification.verify.custom');

                Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
                    ->middleware(['auth:'.config('fortify.guard'), 'throttle:6,1'])
                    ->name('verification.send.custom');
            }

            if (Features::enabled(Features::updateProfileInformation())) {
                Route::put('/user/profile-information', [ProfileInformationController::class, 'update'])
                    ->middleware(['auth:'.config('fortify.guard')])
                    ->name('user-profile-information.update.custom');
            }

            if (Features::enabled(Features::updatePasswords())) {
                Route::put('/user/password', [PasswordController::class, 'update'])
                    ->middleware(['auth:'.config('fortify.guard')])
                    ->name('user-password.update.custom');
            }

            if (Features::enabled(Features::twoFactorAuthentication())) {
                Route::post('/user/two-factor-authentication', [TwoFactorAuthenticationController::class, 'store'])
                    ->middleware(['auth:'.config('fortify.guard')])
                    ->name('two-factor.enable.custom');

                Route::delete('/user/two-factor-authentication', [TwoFactorAuthenticationController::class, 'destroy'])
                    ->middleware(['auth:'.config('fortify.guard')])
                    ->name('two-factor.disable.custom');
            }
        });
    }
}
