<?php 
echo '<form action="index.php?page=2" method="post">';

if(!isset($_SESSION['reg']) || $_SESSION['reg'] == '') {
    echo '<div class="alert alert-warning">Для просмотра желаемого необходимо <a href="index.php?page=3">авторизоваться</a></div>';
} else {
    $userId = BookUser::getUserIdByLogin($_SESSION['reg']);
    
    if($userId) {
        $books = BookUser::getUserBooks($userId);
        
        if(empty($books)) {
            echo '<div class="alert alert-info">Ваш список желаемого пуст</div>';
        } else {
            echo '<div class="row row-cols-1 g-3">';
            foreach($books as $book) {
                $book->DrawCart();
            }
            echo '</div>';
        }
    }
}
?>

<script>
function removeFromWishlist(bookId) {
    if(confirm('Удалить книгу из желаемого?')) {
        fetch('pages/wishlist_handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=remove&book_id=' + bookId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Произошла ошибка');
        });
    }
}
</script>