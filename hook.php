<?php
require_once 'functions.php';

// Получение данных от Telegram
$update = json_decode(file_get_contents('php://input'), TRUE);

// Извлечение данных сообщения
$chat_id = $update['message']['chat']['id'] ?? null;
$text = $update['message']['text'] ?? null;
$callback_query = $update['callback_query'] ?? null;

if ($callback_query) {
    $callback_data = $callback_query['data'];
    $chat_id = $callback_query['message']['chat']['id'];
    handleCallback($callback_data, $chat_id);
} elseif ($chat_id && $text) {
    if ($text == '/start') {
        sendMessage($chat_id, "2 Добро пожаловать в наш магазин! Выберите категорию:");
        // Отправить кнопки категорий
    }
    // Обработка других команд
}
?>
