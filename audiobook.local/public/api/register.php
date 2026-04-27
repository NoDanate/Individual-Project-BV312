<?php
include_once('config.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, null, 'Метод не поддерживается');
}

$input = json_decode(file_get_contents('php://input'), true);
$login = trim(htmlspecialchars($input['login'] ?? ''));
$password = $input['password'] ?? '';
$avatar = $input['avatar'] ?? 'images/default_avatar.png';

if (strlen($login) < 3 || strlen($login) > 30) {
    sendResponse(false, null, 'Логин должен быть от 3 до 30 символов');
}

if (strlen($password) < 3 || strlen($password) > 30) {
    sendResponse(false, null, 'Пароль должен быть от 3 до 30 символов');
}

try {
    $pdo = Tools::connect();
    
    $stmt = $pdo->prepare("SELECT id FROM account WHERE login = ?");
    $stmt->execute([$login]);
    if ($stmt->fetch()) {
        sendResponse(false, null, 'Пользователь с таким логином уже существует');
    }
    
    $roleid = 2;
    $stmt = $pdo->prepare("INSERT INTO account (login, pass, roleid, imagepath) VALUES (?, ?, ?, ?)");
    $stmt->execute([$login, $password, $roleid, $avatar]);
    
    $userId = $pdo->lastInsertId();
    
    sendResponse(true, [
        'id' => $userId,
        'login' => $login,
        'avatar' => $avatar
    ], 'Регистрация успешна');
    
} catch (PDOException $e) {
    sendResponse(false, null, 'Ошибка сервера: ' . $e->getMessage());
}
?>