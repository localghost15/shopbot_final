<?php


// Подключение функций
require_once 'functions.php';

// Логирование входящих данных для проверки
error_log("Полученные данные от Telegram: " . file_get_contents('php://input'));

// Получение данных от Telegram
$update = json_decode(file_get_contents('php://input'), TRUE);

// Проверка на наличие callback или текстового сообщения
$chat_id = $update['message']['chat']['id'] ?? null;
$text = $update['message']['text'] ?? null;
$callback_query = $update['callback_query'] ?? null;

// Обработка callback данных
if ($callback_query) {
    $callback_data = $callback_query['data'];
    $chat_id = $callback_query['message']['chat']['id'];
    handleCallback($callback_data, $chat_id);

// Обработка текстовых сообщений
} elseif ($chat_id && $text) {
    if ($text == '/start') {
        sendMessage($chat_id, "Добро пожаловать в наш магазин! Выберите категорию:");
        showCategories($chat_id); // Функция для отображения категорий
    }
}

// Обработка других команд (например, для корзины и т.д.)

?>
