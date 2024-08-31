<?php

// Подключение автозагрузчика Composer
require 'vendor/autoload.php';

use Telegram\Bot\Api;

// Инициализация Telegram API
$telegram = new Api('7041153979:AAEBvqUVg-UBYDQMf-rZpsa0XTB2lqEXzpk');

// Получаем обновления из webhook
$updates = $telegram->getWebhookUpdates();

// Извлекаем chat_id и текст сообщения
$chat_id = $updates->getMessage()->getChat()->getId();
$text = $updates->getMessage()->getText();

// Обработка команды /start
if ($text === '/start') {
    $response = "Добро пожаловать в наш магазин! Выберите категорию:";
    $telegram->sendMessage([
        'chat_id' => $chat_id,
        'text' => $response
    ]);

    // Вызовем функцию для отображения категорий
    showCategories($telegram, $chat_id);
}

// Функция для отображения категорий
function showCategories($telegram, $chat_id) {
    $categories = getCategories();

    if (!empty($categories)) {
        $inlineKeyboard = [];
        foreach ($categories as $category) {
            $inlineKeyboard[] = [
                ['text' => $category['name'], 'callback_data' => 'category_' . $category['id']]
            ];
        }

        $replyMarkup = [
            'inline_keyboard' => $inlineKeyboard
        ];

        $telegram->sendMessage([
            'chat_id' => $chat_id,
            'text' => 'Выберите категорию:',
            'reply_markup' => json_encode($replyMarkup)
        ]);
    } else {
        $telegram->sendMessage([
            'chat_id' => $chat_id,
            'text' => 'Категории пока отсутствуют.'
        ]);
    }
}

// Функция подключения к базе данных
function getDatabaseConnection() {
    $servername = "localhost"; // Ваш сервер базы данных
    $username = "root"; // Ваше имя пользователя базы данных
    $password = ""; // Ваш пароль базы данных
    $dbname = "shopbot"; // Название вашей базы данных

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
}

// Функция для получения категорий
function getCategories() {
    $conn = getDatabaseConnection();
    $sql = "SELECT id, name FROM categories";
    $result = $conn->query($sql);

    $categories = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
    }
    $conn->close();
    return $categories;
}

?>
