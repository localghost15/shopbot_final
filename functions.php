<?php
require_once 'config.php';
require 'vendor/autoload.php';

use Telegram\Bot\Api;

$telegram = new Api(BOT_TOKEN);  // Используем токен из config.php

// Функция отправки сообщения
function sendMessage($chat_id, $message) {
    global $telegram;
    $telegram->sendMessage([
        'chat_id' => $chat_id,
        'text' => $message
    ]);
}

// Другие функции остаются без изменений...

?>
