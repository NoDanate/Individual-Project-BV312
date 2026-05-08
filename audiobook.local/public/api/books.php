<?php
include_once('config.php');

$method = $_SERVER['REQUEST_METHOD'];
$genre = $_GET['genre'] ?? '';
$search = $_GET['search'] ?? '';
$page = (int)($_GET['page'] ?? 1);
$requestUserId = (int)($_GET['user_id'] ?? 0);
$limit = 20;
$offset = ($page - 1) * $limit;

try {
    $pdo = Tools::connect();
    
    $sql = "SELECT DISTINCT ON (name, author) 
                id, name, author, genre, description, imagepath, speaker, bookpath
            FROM book";
    
    $whereConditions = [];
    $params = [];
    
    if (!empty($genre)) {
        $whereConditions[] = "genre = ?";
        $params[] = $genre;
    }
    
    if (!empty($search)) {
        $whereConditions[] = "(LOWER(name) LIKE LOWER(?) OR LOWER(author) LIKE LOWER(?))";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    if (!empty($whereConditions)) {
        $sql .= " WHERE " . implode(' AND ', $whereConditions);
    }
    
    $sql .= " ORDER BY name, author, id LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    $books = [];
    $user = getCurrentUser();
    $userId = $user['id'] ?? $requestUserId;
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $ratingData = Book::getBookRating($row['id']);
        
        $inWishlist = false;
        if ($userId) {
            $inWishlist = BookUser::isBookInWishlist($userId, $row['id']);
        }
        
        $speakers = Book::getBookSpeakers($row['name'], $row['author']);
        
        $books[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'author' => $row['author'],
            'genre' => $row['genre'],
            'description' => $row['description'],
            'imageUrl' => 'http://192.168.1.47/' . $row['imagepath'],
            'speaker' => $row['speaker'],
            'audioUrl' => 'http://192.168.1.47/' . $row['bookpath'],
            'rating' => $ratingData['avg'],
            'ratingCount' => $ratingData['count'],
            'inWishlist' => $inWishlist,
            'speakers' => array_map(function($s) {
                return [
                    'id' => $s['id'],
                    'name' => $s['speaker'],
                    'audioUrl' => $s['bookpath']
                ];
            }, $speakers)
        ];
    }
    
    sendResponse(true, [
        'books' => $books,
        'page' => $page,
        'hasMore' => count($books) == $limit
    ]);
    
} catch (PDOException $e) {
    sendResponse(false, null, 'Ошибка сервера: ' . $e->getMessage());
}
?>