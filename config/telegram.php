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
    | Telegram API URL
    |--------------------------------------------------------------------------
    |
    | The URL endpoint for your Telegram API proxy/wrapper.
    |
    */

    'url' => env('TELEGRAM_API_URL'),

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

