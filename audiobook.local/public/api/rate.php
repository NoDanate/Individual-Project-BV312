<?php
include_once('config.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, null, 'Метод не поддерживается');
}

$input = json_decode(file_get_contents('php://input'), true);
$bookId = (int)($input['book_id'] ?? 0);
$rating = (int)($input['rating'] ?? 0);
$requestUserId = (int)($input['user_id'] ?? 0);

$user = getCurrentUser();
$finalUserId = $user['id'] ?? $requestUserId;

if (!$bookId) {
    sendResponse(false, null, 'ID книги не указан');
}

if (!$finalUserId) {
    sendResponse(false, null, 'ID пользователя не указан');
}

if ($rating < 1 || $rating > 5) {
    sendResponse(false, null, 'Оценка должна быть от 1 до 5');
}

try {
    $result = BookUser::updateRating($finalUserId, $bookId, $rating);
    
    if ($result === true) {
        $newRating = Book::getBookRating($bookId);
        sendResponse(true, [
            'rating' => $newRating['avg'],
            'ratingCount' => $newRating['count'],
            'userRating' => $rating
        ], 'Оценка сохранена');
    } else {
        sendResponse(false, null, 'Ошибка при сохранении оценки');
    }
    
} catch (Exception $e) {
    sendResponse(false, null, 'Ошибка сервера: ' . $e->getMessage());
}
?>