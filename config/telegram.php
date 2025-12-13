<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Telegram Bot Token
    |--------------------------------------------------------------------------
    |
    | Your Telegram bot token obtained from @BotFather on Telegram.
    |
    */

    'token' => env('TELEGRAM_BOT_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Telegram API Proxy URL
    |--------------------------------------------------------------------------
    |
    | The proxy URL that forwards requests to Telegram Bot API.
    | This proxy bypasses sanctions by routing requests through a proxy server.
    | Default: https://snowy-tree-5c79.pk74ever.workers.dev
    |
    */

    'proxy_url' => env('TELEGRAM_PROXY_URL', 'https://snowy-tree-5c79.pk74ever.workers.dev'),

    /*
    |--------------------------------------------------------------------------
    | Admin Chat ID
    |--------------------------------------------------------------------------
    |
    | The Telegram chat ID where order notifications will be sent.
    | This should be the admin's chat ID or a group chat ID.
    |
    */

    'admin_chat_id' => env('TELEGRAM_ADMIN_CHAT_ID'),

];

