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

// Функция для обработки коллбэков
function handleCallback($callback_data, $chat_id) {
    global $telegram;

    if (strpos($callback_data, 'category_') === 0) {
        $category_id = str_replace('category_', '', $callback_data);
        showSubCategories($chat_id, $category_id);
    } elseif (strpos($callback_data, 'subcategory_') === 0) {
        $subcategory_id = str_replace('subcategory_', '', $callback_data);
        showProducts($chat_id, $subcategory_id);
    } elseif (strpos($callback_data, 'product_') === 0) {
        $product_id = str_replace('product_', '', $callback_data);
        addToCart($chat_id, $product_id);
    }
    // Добавьте дополнительные обработки коллбэков, если это необходимо
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
    $sql = "SELECT id, name, price FROM products WHERE sub_category_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $subcategory_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $inlineKeyboard = [];
        while ($row = $result->fetch_assoc()) {
            $inlineKeyboard[] = [
                ['text' => $row['name'] . " - " . $row['price'] . "₽", 'callback_data' => 'product_' . $row['id']]
            ];
        }

        $replyMarkup = [
            'inline_keyboard' => $inlineKeyboard
        ];

        sendMessage($chat_id, "Выберите товар:", $replyMarkup);
    } else {
        sendMessage($chat_id, "Товары пока отсутствуют.");
    }

    $conn->close();
}

?>
