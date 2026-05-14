<?php

use App\Providers\RouteServiceProvider;
use Laravel\Fortify\Features;

return [
    'guard' => 'web',
    'middleware' => ['web'],
    'passwords' => 'users',
    'username' => 'email',
    'email' => 'email',
    'views' => true,
    'home' => '/dashboard',
    'prefix' => '',
    'domain' => null,
    'limiters' => [
        'login' => null,
        'two-factor' => null,
    ],
    'redirects' => [
        'login' => '/dashboard',
        'logout' => '/',
        'password-confirmation' => null,
        'register' => '/dashboard',
        'email-verification' => '/dashboard',
        'password-reset' => '/dashboard',
    ],
    'features' => [
        Features::registration(),
        Features::resetPasswords(),
        Features::emailVerification(),
        Features::updateProfileInformation(),
        Features::updatePasswords(),
        Features::twoFactorAuthentication([
            'confirm' => true,
            'confirmPassword' => true,
        ]),
    ],
];