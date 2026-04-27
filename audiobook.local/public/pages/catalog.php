<?php
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';

if ($searchQuery != '') {
    $items = Book::searchUniqueBooks($searchQuery);
} else {
    $items = Book::GetUniqueItems('');
}
?>

<div class="container my-4">
    <?php if ($searchQuery != ''): ?>
        <div class="mb-4">
            <h3>Результаты поиска: "<?= htmlspecialchars($searchQuery) ?>"</h3>
            <p class="text-muted">Найдено книг: <?= count($items) ?></p>
        </div>
    <?php endif; ?>

    <div class="row g-4" id="result">
        <?php 
        if($items && count($items) > 0){
            foreach ($items as $item){
                $item->Draw();
            }
        } else {
            if ($searchQuery != '') {
                echo '
                    <div class="col-12 text-center py-5">
                        <div class="alert alert-info shadow-sm w-50 mx-auto">
                            <h4>Ничего не найдено</h4>
                            <p>По запросу "' . htmlspecialchars($searchQuery) . '" ничего не найдено.</p>
                            <a href="index.php?page=1" class="btn btn-primary mt-2">Вернуться в каталог</a>
                        </div>
                    </div>
                ';
            } else {
                echo '
                    <div class="col-12 text-center py-5">
                        <div class="alert alert-warning shadow-sm w-50 mx-auto">
                            Товары не найдены
                        </div>
                    </div>
                ';
            }
        }
        ?>
    </div>
</div>

<script>
function addToWishlist(bookId) {
    <?php if(!isset($_SESSION['reg']) || $_SESSION['reg'] == ''): ?>
        alert('Необходимо авторизоваться, чтобы добавить книгу в желаемое');
        return;
    <?php endif; ?>
    
    fetch('pages/wishlist_handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=add&book_id=' + bookId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
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
                alert(data.message);
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