<?php
include_once('config.php');

$method = $_SERVER['REQUEST_METHOD'];

$user = getCurrentUser();

if (!$user) {
    $userId = $_GET['user_id'] ?? null;
    
    if (!$userId) {
        $input = json_decode(file_get_contents('php://input'), true);
        $userId = $input['user_id'] ?? null;
    }
    
    if ($userId) {
        try {
            $account = Account::fromDb($userId);
            if ($account) {
                $user = [
                    'id' => $account->id,
                    'login' => $account->login
                ];
                $_SESSION['reg'] = $account->login;
                $_SESSION['user_id'] = $account->id;
            }
        } catch (Exception $e) {}
    }
}

if (!$user) {
    sendResponse(false, null, 'Необходима авторизация. Передайте user_id');
}

if ($method === 'GET') {
    try {
        $books = BookUser::getUserBooks($user['id']);
        
        $baseUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/';
        $wishlist = [];
        
        foreach ($books as $book) {
            $ratingData = ['avg' => 0, 'count' => 0];
            try {
                $ratingData = Book::getBookRating($book->id);
            } catch (Exception $e) {}
            
            $wishlist[] = [
                'id' => (int)$book->id,
                'name' => $book->name ?? '',
                'author' => $book->author ?? '',
                'genre' => $book->genre ?? '',
                'imageUrl' => $baseUrl . $book->imagepath,
                'speaker' => $book->speaker ?? '',
                'rating' => (float)$ratingData['avg'],
                'ratingCount' => (int)$ratingData['count'],
                'audioUrl' => $baseUrl . $book->bookpath
            ];
        }
        
        sendResponse(true, ['books' => $wishlist]);
        
    } catch (Exception $e) {
        sendResponse(false, null, 'Ошибка сервера: ' . $e->getMessage());
    }
    
} elseif ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $bookId = (int)($input['book_id'] ?? 0);
    $action = $input['action'] ?? '';
    $requestUserId = (int)($input['user_id'] ?? 0);
    
    $finalUserId = $user['id'] ?? $requestUserId;
    
    if (!$bookId) {
        sendResponse(false, null, 'ID книги не указан');
    }
    
    if (!$finalUserId) {
        sendResponse(false, null, 'ID пользователя не указан');
    }
    
    try {
        if ($action === 'add') {
            $bookUser = new BookUser($finalUserId, $bookId, null);
            $result = $bookUser->intoDb();
            
            if ($result === true) {
                sendResponse(true, null, 'Книга добавлена в избранное');
            } elseif ($result === false) {
                sendResponse(false, null, 'Книга уже в избранном');
            } else {
                sendResponse(false, null, 'Ошибка: ' . $result);
            }
        } elseif ($action === 'remove') {
            $result = BookUser::deleteFromDb($finalUserId, $bookId);
            
            if ($result === true) {
                sendResponse(true, null, 'Книга удалена из избранного');
            } else {
                sendResponse(false, null, 'Ошибка при удалении');
            }
        } else {
            sendResponse(false, null, 'Неверное действие');
        }
        
    } catch (Exception $e) {
        sendResponse(false, null, 'Ошибка сервера: ' . $e->getMessage());
    }
}
?>