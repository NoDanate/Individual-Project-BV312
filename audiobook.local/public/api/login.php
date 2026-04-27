<?php
include_once('config.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, null, 'Метод не поддерживается');
}

$input = json_decode(file_get_contents('php://input'), true);
$login = $input['login'] ?? '';
$password = $input['password'] ?? '';

if (empty($login) || empty($password)) {
    sendResponse(false, null, 'Логин и пароль обязательны');
}

try {
    $pdo = Tools::connect();
    
    $stmt = $pdo->prepare("SELECT id, login, pass, roleid, imagepath FROM account WHERE login = ?");
    $stmt->execute([$login]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && $user['pass'] === $password) {
        $_SESSION['reg'] = $user['login'];
        if ($user['roleid'] == 1) {
            $_SESSION['admin'] = $user['login'];
        }
        
        $userData = [
            'id' => $user['id'],
            'login' => $user['login'],
            'avatar' => $user['imagepath'],
            'isAdmin' => $user['roleid'] == 1
        ];
        
        sendResponse(true, $userData, 'Успешная авторизация');
    } else {
        sendResponse(false, null, 'Неверный логин или пароль');
    }
} catch (PDOException $e) {
    sendResponse(false, null, 'Ошибка сервера: ' . $e->getMessage());
}
?>