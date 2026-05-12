<?php
session_start();

include_once('../pages/classes.php');

header('Content-Type: application/json; charset=utf-8');

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
// Отправка ответа клиенту
function sendResponse($success, $data = null, $message = '') {
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
// Получение информации об авторизованном пользователе
function getCurrentUser() {
    if (isset($_SESSION['reg']) && $_SESSION['reg'] != '') {
        $userId = BookUser::getUserIdByLogin($_SESSION['reg']);
        if ($userId) {
            return [
                'id' => $userId,
                'login' => $_SESSION['reg']
            ];
        }
    }
    return null;
}
?>