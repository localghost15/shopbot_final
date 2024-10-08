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

    if (!$result) {
        sendMessage($chat_id, "Ошибка при запросе категорий: " . $conn->error);
        return;
    }

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


// Функция для обработки коллбэков
function handleCallback($callback_data, $chat_id) {
    if (strpos($callback_data, 'category_') === 0) {
        $category_id = str_replace('category_', '', $callback_data);
        showSubCategories($chat_id, $category_id);
    } elseif (strpos($callback_data, 'subcategory_') === 0) {
        $subcategory_id = str_replace('subcategory_', '', $callback_data);
        showProducts($chat_id, $subcategory_id);
    } elseif (strpos($callback_data, 'product_') === 0) {
        $product_id = str_replace('product_', '', $callback_data);
        addToCart($chat_id, $product_id);
    } elseif ($callback_data == 'back_to_categories') {
        showCategories($chat_id);
    }
}
// Функция для отображения подкатегорий
function showSubCategories($chat_id, $category_id) {
    $conn = getDatabaseConnection();
    $sql = "SELECT id, name FROM sub_categories WHERE category_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $category_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $inlineKeyboard = [];
        while ($row = $result->fetch_assoc()) {
            $inlineKeyboard[] = [
                ['text' => $row['name'], 'callback_data' => 'subcategory_' . $row['id']]
            ];
        }

        $replyMarkup = [
            'inline_keyboard' => $inlineKeyboard
        ];

        sendMessage($chat_id, "Выберите подкатегорию:", $replyMarkup);
    } else {
        sendMessage($chat_id, "Подкатегории пока отсутствуют.");
    }

    $conn->close();
}

// Функция для отображения товаров
function showProducts($chat_id, $subcategory_id) {
    $conn = getDatabaseConnection();
    $sql = "SELECT id, name, description, price, image_url FROM products WHERE sub_category_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $subcategory_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result) {
        sendMessage($chat_id, "Ошибка при запросе товаров: " . $conn->error);
        return;
    }

    if ($result->num_rows > 0) {
        $inlineKeyboard = [];
        while ($row = $result->fetch_assoc()) {
            // Кнопка для добавления товара в корзину
            $inlineKeyboard[] = [
                ['text' => 'Добавить в корзину', 'callback_data' => 'add_to_cart_' . $row['id']]
            ];

            // Отправка сообщения с картинкой и описанием
            $message = "{$row['name']}\nЦена: {$row['price']}₽\nОписание: {$row['description']}";
            sendPhoto($chat_id, $row['image_url'], $message, $inlineKeyboard);
        }

        // Кнопка "Назад" для возврата в список категорий
        $backButton = [
            'inline_keyboard' => [
                [['text' => 'Назад к категориям', 'callback_data' => 'back_to_categories']]
            ]
        ];
        sendMessage($chat_id, "Выберите действие:", $backButton);

    } else {
        sendMessage($chat_id, "Товары пока отсутствуют.");
    }

    $conn->close();
}
function sendPhoto($chat_id, $photo_url, $caption, $replyMarkup = null) {
    global $telegram;
    $params = [
        'chat_id' => $chat_id,
        'photo' => $photo_url,
        'caption' => $caption
    ];
    if ($replyMarkup) {
        $params['reply_markup'] = json_encode($replyMarkup);
    }
    $telegram->sendPhoto($params);
}


?>
