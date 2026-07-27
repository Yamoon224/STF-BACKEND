<?php

use App\Http\Controllers\Api\NewsletterSubscriberController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('newsletter/unsubscribe/{subscriber}', [NewsletterSubscriberController::class, 'unsubscribeViaSignedLink'])
    ->name('newsletter.unsubscribe')
    ->middleware('signed');
