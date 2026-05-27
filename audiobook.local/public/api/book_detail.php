<?php
include_once('config.php');
// В этом файле формируется информация о книге
$bookId = $_GET['id'] ?? 0;
$requestUserId = $_GET['user_id'] ?? 0;  

if (!$bookId) {
    sendResponse(false, null, 'ID книги не указан');
}

try {
    $book = Book::fromDb($bookId);
    if (!$book) {
        sendResponse(false, null, 'Книга не найдена');
    }
    
    $user = getCurrentUser();
    $userId = $user['id'] ?? $requestUserId; 
    
    $ratingData = Book::getBookRating($bookId);
    
    $inWishlist = false;
    $userRating = null;
    if ($userId) {
        $inWishlist = BookUser::isBookInWishlist($userId, $bookId);
        $userRating = BookUser::getUserRating($userId, $bookId);
    }
    
    $speakers = Book::getBookSpeakers($book->name, $book->author);
    
    $similarBooks = [];
    $allBooks = Book::GetUniqueItems($book->genre);
    foreach ($allBooks as $similar) {
        if ($similar->id != $bookId && count($similarBooks) < 4) {
            $similarBooks[] = [
                'id' => $similar->id,
                'name' => $similar->name,
                'author' => $similar->author,
                'imageUrl' =>'http://192.168.1.47/' . $similar->imagepath
                //'imageUrl' =>'http://172.20.10.2/' . $similar->imagepath
            ];
        }
    }
    
    $bookData = [
        'id' => $book->id,
        'name' => $book->name,
        'author' => $book->author,
        'genre' => $book->genre,
        'description' => $book->description,
        'imageUrl' => 'http://192.168.1.47/' . $book->imagepath,
        //'imageUrl' =>'http://172.20.10.2/' . $similar->imagepath,
        'speaker' => $book->speaker,
        'audioUrl' => 'http://192.168.1.47/' . $book->bookpath,
        //'audioUrl' => 'http://172.20.10.2/' . $book->bookpath,
        'rating' => $ratingData['avg'],
        'ratingCount' => $ratingData['count'],
        'inWishlist' => $inWishlist,
        'userRating' => $userRating,
        'speakers' => array_map(function($s) {
            return [
                'id' => $s['id'],
                'name' => $s['speaker'],
                'audioUrl' =>'http://192.168.1.47/' . $s['bookpath']
                //'audioUrl' =>'http://172.20.10.2/' . $s['bookpath']
            ];
        }, $speakers),
        'similarBooks' => $similarBooks
    ];
    
    sendResponse(true, $bookData);
    
} catch (PDOException $e) {
    sendResponse(false, null, 'Ошибка сервера: ' . $e->getMessage());
}
?>