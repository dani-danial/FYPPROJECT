<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;

// Debug route - REMOVE IN PRODUCTION
Route::get('/debug/toyyibpay', function () {
    $secretKey = env('TOYYIBPAY_SECRET_KEY');
    $categoryCode = env('TOYYIBPAY_CATEGORY_CODE');
    $apiUrl = env('TOYYIBPAY_API_URL');
    
    return response()->json([
        'secret_key' => $secretKey,
        'category_code' => $categoryCode,
        'api_url' => $apiUrl,
        'secret_key_length' => strlen($secretKey),
        'message' => 'Check your .env file for TOYYIBPAY_SECRET_KEY and TOYYIBPAY_CATEGORY_CODE'
    ]);
});

// Test API call
Route::get('/debug/toyyibpay-test', function () {
    $secretKey = env('TOYYIBPAY_SECRET_KEY');
    $categoryCode = env('TOYYIBPAY_CATEGORY_CODE');
    $apiUrl = env('TOYYIBPAY_API_URL');
    
    $testPayload = [
        'userSecretKey' => $secretKey,
        'categoryCode' => $categoryCode,
        'billName' => 'TEST BILL',
        'billDescription' => 'Test payment',
        'billPriceSetting' => 1,
        'billPayorInfo' => 1,
        'billAmount' => 100,
        'billReturnUrl' => route('dashboard'),
        'billCallbackUrl' => route('dashboard'),
        'billExternalReferenceNo' => 'TEST_' . time(),
        'billTo' => 'Test User',
        'billEmail' => 'test@example.com',
        'billPhone' => '0123456789',
    ];
    
    try {
        $response = Http::withoutVerifying()
            ->timeout(30)
            ->asForm()
            ->post($apiUrl, $testPayload);
        
        return response()->json([
            'status' => $response->status(),
            'body' => $response->body(),
            'parsed' => $response->json(),
            'headers' => $response->headers(),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
        ], 500);
    }
});
