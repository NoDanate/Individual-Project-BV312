<?php
session_start();
include_once('classes.php');

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if (!isset($_POST['action']) || !isset($_POST['book_id'])) {
    $response['message'] = 'Неверные параметры';
    echo json_encode($response);
    exit;
}

$book_id = (int)$_POST['book_id'];
$action = $_POST['action'];

$user_id = null;
if (isset($_SESSION['reg']) && $_SESSION['reg'] != '') {
    $user_id = BookUser::getUserIdByLogin($_SESSION['reg']);
}

if (!$user_id) {
    $response['message'] = 'Необходимо авторизоваться';
    echo json_encode($response);
    exit;
}

if ($action == 'add') {
    $bookUser = new BookUser($user_id, $book_id, NULL);
    $result = $bookUser->intoDb();
    
    if ($result === true) {
        $response['success'] = true;
        $response['message'] = 'Книга добавлена в желаемое';
    } elseif ($result === false) {
        $response['message'] = 'Книга уже в желаемом';
    } else {
        $response['message'] = 'Ошибка при добавлении: ' . $result;
    }
} elseif ($action == 'remove') {
    $result = BookUser::deleteFromDb($user_id, $book_id);
    
    if ($result === true) {
        $response['success'] = true;
        $response['message'] = 'Книга удалена из желаемого';
    } else {
        $response['message'] = 'Ошибка при удалении: ' . $result;
    }
}

echo json_encode($response);
?>