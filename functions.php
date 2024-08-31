<?php
require_once 'config.php';
require 'vendor/autoload.php';

use Telegram\Bot\Api;

$telegram = new Api(BOT_TOKEN);  // Используем токен из config.php

// Функция отправки сообщения
function sendMessage($chat_id, $message, $replyMarkup = null) {
    global $telegram;
    $params = [
        'chat_id' => $chat_id,
        'text' => $message
    ];
    if ($replyMarkup) {
        $params['reply_markup'] = json_encode($replyMarkup);
    }
    $telegram->sendMessage($params);
}


// Функция для отображения категорий
function showCategories($chat_id) {
    $conn = getDatabaseConnection();
    $sql = "SELECT id, name FROM categories";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $inlineKeyboard = [];
        while ($row = $result->fetch_assoc()) {
            $inlineKeyboard[] = [
                ['text' => $row['name'], 'callback_data' => 'category_' . $row['id']]
            ];
        }

        $replyMarkup = [
            'inline_keyboard' => $inlineKeyboard
        ];

        sendMessage($chat_id, "Выберите категорию:", $replyMarkup);
    } else {
        sendMessage($chat_id, "Категории пока отсутствуют.");
    }

    $conn->close();
}
?>
