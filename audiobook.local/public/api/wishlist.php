<?php
include_once('config.php');

$user = getCurrentUser();
if (!$user) {
    sendResponse(false, null, 'Необходима авторизация');
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    try {
        $books = BookUser::getUserBooks($user['id']);
        
        $wishlist = [];
        foreach ($books as $book) {
            $ratingData = Book::getBookRating($book->id);
            
            $wishlist[] = [
                'id' => $book->id,
                'name' => $book->name,
                'author' => $book->author,
                'genre' => $book->genre,
                'imageUrl' => $book->imagepath,
                'speaker' => $book->speaker,
                'rating' => $ratingData['avg'],
                'ratingCount' => $ratingData['count']
            ];
        }
        
        sendResponse(true, ['books' => $wishlist]);
        
    } catch (PDOException $e) {
        sendResponse(false, null, 'Ошибка сервера');
    }
    
} elseif ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $bookId = (int)($input['book_id'] ?? 0);
    $action = $input['action'] ?? '';
    
    if (!$bookId) {
        sendResponse(false, null, 'ID книги не указан');
    }
    
    try {
        if ($action === 'add') {
            $bookUser = new BookUser($user['id'], $bookId, null);
            $result = $bookUser->intoDb();
            
            if ($result === true) {
                sendResponse(true, null, 'Книга добавлена в избранное');
            } elseif ($result === false) {
                sendResponse(false, null, 'Книга уже в избранном');
            }
        } elseif ($action === 'remove') {
            $result = BookUser::deleteFromDb($user['id'], $bookId);
            
            if ($result === true) {
                sendResponse(true, null, 'Книга удалена из избранного');
            }
        } else {
            sendResponse(false, null, 'Неверное действие');
        }
        
    } catch (PDOException $e) {
        sendResponse(false, null, 'Ошибка сервера');
    }
    
} else {
    sendResponse(false, null, 'Метод не поддерживается');
}
?>