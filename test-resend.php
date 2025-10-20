<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Mail;

try {
    Mail::raw('🎉 Success! Your Resend integration is working!

This is a test email from CBHLC enrollment system.

Configuration:
- Domain: cbhlc.com
- From: '.config('mail.from.address').'
- Mailer: '.config('mail.default').'

If you received this email, your DNS records are properly configured and Resend is ready for production!', function ($message) {
        $message->to('mkcastro24@gmail.com')
            ->subject('✅ CBHLC Resend Test - Email Working!');
    });

    echo "✅ Email sent successfully!\n";
    echo 'From: '.config('mail.from.address')."\n";
    echo 'Mailer: '.config('mail.default')."\n";
    echo "\nCheck your inbox (and spam folder) for the test email.\n";
    echo "If using test@example.com, check your Resend dashboard logs instead.\n";
} catch (\Exception $e) {
    echo '❌ Error: '.$e->getMessage()."\n";
    echo "\nTroubleshooting:\n";
    echo "1. Verify domain is verified in Resend dashboard\n";
    echo "2. Check RESEND_API_KEY is set correctly in .env\n";
    echo "3. Ensure DNS records have propagated fully\n";
}
