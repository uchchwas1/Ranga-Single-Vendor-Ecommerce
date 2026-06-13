<?php

declare(strict_types=1);

return [

    'failed' => 'These credentials do not match our records.',
    'password' => 'The provided password is incorrect.',
    'throttle' => 'Too many login attempts. Please try again in :seconds seconds.',
    'inactive' => 'This account has been deactivated.',
    'registered' => 'Registration successful. Please verify your email address.',
    'logged_in' => 'Logged in successfully.',
    'logged_out' => 'Logged out successfully.',

    'login_status' => [
        'success' => 'Successful',
        'failed' => 'Failed',
        'blocked' => 'Blocked',
    ],

    '2fa' => [
        'challenge_issued' => 'Two-factor verification required.',
        'challenge_invalid' => 'The two-factor challenge is invalid or has expired.',
        'code_invalid' => 'The provided two-factor code is invalid.',
        'enabled_pending_confirmation' => 'Scan the QR code and confirm with a one-time code.',
        'confirmed' => 'Two-factor authentication enabled.',
        'disabled' => 'Two-factor authentication disabled.',
    ],

    'social' => [
        'token_invalid' => 'The provider access token is invalid.',
        'email_missing' => 'The provider did not supply an email address.',
        'default_name' => 'Customer',
    ],

    'verification' => [
        'verified' => 'Email address verified successfully.',
        'already_verified' => 'Email address is already verified.',
        'invalid' => 'The verification link is invalid.',
        'sent' => 'Verification email sent.',
    ],

];
