<?php

use Illuminate\Support\Facades\Http;

// Test various Toyyibpay URL formats
Route::get('/debug/test-payment-urls', function () {
    // Create a test bill first
    $secretKey = env('TOYYIBPAY_SECRET_KEY');
    $categoryCode = env('TOYYIBPAY_CATEGORY_CODE');
    $apiUrl = env('TOYYIBPAY_API_URL');
    
    $testPayload = [
        'userSecretKey' => $secretKey,
        'categoryCode' => $categoryCode,
        'billName' => 'URL TEST',
        'billDescription' => 'Testing different URL formats',
        'billPriceSetting' => 1,
        'billPayorInfo' => 1,
        'billAmount' => 100,
        'billReturnUrl' => route('dashboard'),
        'billCallbackUrl' => route('dashboard'),
        'billExternalReferenceNo' => 'URL_TEST_' . time(),
        'billTo' => 'Test User',
        'billEmail' => 'test@example.com',
        'billPhone' => '0123456789',
    ];
    
    $response = Http::withoutVerifying()
        ->timeout(30)
        ->asForm()
        ->post($apiUrl, $testPayload);
    
    $billCode = null;
    if ($response->successful()) {
        $data = $response->json();
        $billCode = $data[0]['BillCode'] ?? null;
    }
    
    if (!$billCode) {
        return response()->json(['error' => 'Failed to create bill'], 400);
    }
    
    // Test various URL formats
    $baseUrl = 'https://dev.toyyibpay.com';
    $urls = [
        'Format 1: /bill/' => $baseUrl . '/bill/' . $billCode,
        'Format 2: /index.php/bill/' => $baseUrl . '/index.php/bill/' . $billCode,
        'Format 3: /?billCode=' => $baseUrl . '/?billCode=' . $billCode,
        'Format 4: /index.php/?billCode=' => $baseUrl . '/index.php/?billCode=' . $billCode,
        'Format 5: Direct with query' => $baseUrl . '/index.php/api/bill?code=' . $billCode,
    ];
    
    return response()->json([
        'billCode' => $billCode,
        'test_urls' => $urls,
        'instruction' => 'Try clicking each URL to see which one works for direct payment'
    ]);
});
