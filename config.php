<?php
// Настройки для подключения к базе данных
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_DATABASE', 'shopbot');

// Токен вашего бота
define('BOT_TOKEN', '7041153979:AAEBvqUVg-UBYDQMf-rZpsa0XTB2lqEXzpk');

// Функция для получения подключения к базе данных
function getDatabaseConnection() {
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}
?>
