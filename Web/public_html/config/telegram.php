<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Telegram SOS Routing
    |--------------------------------------------------------------------------
    |
    | Add each Telegram group/channel chat id in .env. The nearest configured
    | area to the SOS GPS point receives the alert. If no area has a chat id,
    | the fallback chat id is used.
    |
    */

    'fallback_chat_id' => env('TELEGRAM_CHAT_ID', '-5239844775'),

    'areas' => [
        [
            'name' => 'Jasin',
            'chat_id' => env('TELEGRAM_CHAT_ID_JASIN', '-5218216770'),
            'lat' => 2.3090,
            'lng' => 102.4280,
        ],
        [
            'name' => 'Bandaraya Melaka',
            'chat_id' => env('TELEGRAM_CHAT_ID_BANDARAYA_MELAKA', '-5194855799'),
            'lat' => 2.1896,
            'lng' => 102.2501,
        ],
        [
            'name' => 'Ayer Keroh',
            'chat_id' => env('TELEGRAM_CHAT_ID_AYER_KEROH', '-4990154840'),
            'lat' => 2.2663,
            'lng' => 102.2800,
        ],
        [
            'name' => 'Melaka General',
            'chat_id' => env('TELEGRAM_CHAT_ID_MELAKA_GENERAL', '-5239844775'),
            'lat' => 2.2057,
            'lng' => 102.2563,
        ],
    ],
];
