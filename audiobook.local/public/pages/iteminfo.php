<?php
session_start();
include_once('classes.php');

if(isset($_POST['submit_rating']) && isset($_POST['book_id']) && isset($_POST['rating'])) {
    $book_id = (int)$_POST['book_id'];
    $rating = (int)$_POST['rating'];
    
    if(isset($_SESSION['reg']) && $_SESSION['reg'] != '') {
        $user_id = BookUser::getUserIdByLogin($_SESSION['reg']);
        if($user_id) {
            if($rating >= 1 && $rating <= 5) {
                $result = BookUser::updateRating($user_id, $book_id, $rating);
                if($result === true) {
                    header("Location: itemInfo.php?name=" . $book_id . "&rated=1");
                    exit;
                }
            }
        }
    }
}

if(!isset($_GET['name']) || empty($_GET['name'])){
    echo '<div class="container mt-5">
            <div class="alert alert-danger">Товар не найден</div>
          </div>';
    exit;
}

$itemId = intval($_GET['name']);
$item = Book::fromDb($itemId);

if(!$item){
    echo '<div class="container mt-5">
            <div class="alert alert-warning">Товар с таким ID не существует</div>
          </div>';
    exit;
}

$speakers = Book::getBookSpeakers($item->name, $item->author);
$hasMultipleSpeakers = count($speakers) > 1;

$user = (!isset($_SESSION['reg']) || $_SESSION['reg'] == '') 
    ? "cart_" . $item->id 
    : $_SESSION['reg'] . "_" . $item->id;

$ratingData = Book::getBookRating($itemId);
$avgRating = $ratingData['avg'];
$ratingCount = $ratingData['count'];

$userRating = null;
$user_id = null;
if(isset($_SESSION['reg']) && $_SESSION['reg'] != ''){
    $user_id = BookUser::getUserIdByLogin($_SESSION['reg']);
    if($user_id){
        $userRating = BookUser::getUserRating($user_id, $itemId);
    }
}

$isInWishlist = false;
if($user_id) {
    $isInWishlist = BookUser::isBookInWishlist($user_id, $itemId);
}

$currentBookId = $itemId;
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($item->name) ?> - Аудиокниги</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        .main-image {
            width: 100%;
            height: 400px;
            object-fit: contain;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 10px;
            background: #f8f9fa;
        }
        .rating {
            color: #ffc107;
            font-size: 1.2rem;
        }
        .rating-stars {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
        }
        .star-rating input {
            display: none;
        }
        .star-rating label {
            color: #ddd;
            font-size: 2rem;
            cursor: pointer;
            transition: color 0.2s;
        }
        .star-rating label:hover,
        .star-rating label:hover ~ label,
        .star-rating input:checked ~ label {
            color: #ffc107;
        }
        .user-rating-info {
            margin-top: 10px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container mt-4 mb-5">

        <a href="javascript:history.back()" class="btn btn-outline-primary mb-4">
            <i class="bi bi-arrow-left"></i> Вернуться к каталогу
        </a>

        <?php if(isset($_GET['rated']) && $_GET['rated'] == 1): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> Ваша оценка сохранена!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-lg border-0 rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="text-center mb-3">
                            <img src="../<?= htmlspecialchars($item->imagepath) ?>" 
                                 alt="<?= htmlspecialchars($item->name) ?>" 
                                 class="main-image" 
                                 id="mainImage">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h2 class="text-primary mb-3"><?= htmlspecialchars($item->author) ?></h2>

                        <div class="mb-4">
                            <span class="fw-bold fs-2 text-primary"><?= $item->name?></span>
                        </div>

                        <div class="mb-3">
                            <span class="text-secondary"><?= htmlspecialchars($item->genre) ?></span>
                            
                            <?php if($hasMultipleSpeakers): ?>
                            <div class="mt-2">
                                <label for="speakerSelect" class="form-label fw-semibold">Выберите рассказчика:</label>
                                <select class="form-select" id="speakerSelect" onchange="changeSpeaker()">
                                    <?php foreach($speakers as $index => $speakerData): ?>
                                        <option value="<?= $speakerData['id'] ?>" 
                                                data-bookpath="<?= htmlspecialchars($speakerData['bookpath']) ?>"
                                                <?= ($speakerData['id'] == $item->id) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($speakerData['speaker']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php else: ?>
                            <span class="text-secondary ms-2">Рассказчик: <?= htmlspecialchars($item->speaker) ?></span>
                            <?php endif; ?>
                        </div>

                        <!-- Отображение рейтинга -->
                        <div class="rating mb-3">
                            <div class="rating-stars">
                                <?php 
                                if ($avgRating > 0 && $ratingCount > 0){
                                    $fullStars = floor($avgRating);
                                    $halfStar = ($avgRating - $fullStars) >= 0.5;
                                    
                                    for($i = 1; $i <= 5; $i++) {
                                        if($i <= $fullStars) {
                                            echo '<span class="text-warning">★</span>';
                                        } elseif($halfStar && $i == $fullStars + 1) {
                                            echo '<span class="text-warning">⯪</span>';
                                        } else {
                                            echo '<span class="text-secondary">☆</span>';
                                        }
                                    }
                                ?>
                                <span class="text-muted ms-2">
                                    <?= number_format($avgRating, 2) ?> (<?= $ratingCount ?>)
                                </span>
                                <?php
                                } else {
                                ?>
                                <span class="text-secondary">☆☆☆☆☆</span>
                                <span class="text-muted ms-2">Нет оценок</span>
                                <?php
                                }
                                ?>
                            </div>
                        </div>

                         <!-- Форма для выставления рейтинга -->
                        <div class="user-rating-info mb-4">
                            <h6 class="fw-semibold mb-2">
                                <i class="bi bi-star-fill text-warning me-1"></i>
                                <?= isset($_SESSION['reg']) ? 'Ваша оценка' : 'Оцените книгу' ?>
                            </h6>
                            
                            <?php if(isset($_SESSION['reg']) && $_SESSION['reg'] != ''): ?>
                                <form method="post" id="ratingForm">
                                    <input type="hidden" name="book_id" value="<?= $item->id ?>">
                                    <div class="star-rating">
                                        <?php for($i = 5; $i >= 1; $i--): ?>
                                            <input type="radio" 
                                                   name="rating" 
                                                   value="<?= $i ?>" 
                                                   id="star<?= $i ?>" 
                                                   <?= ($userRating == $i) ? 'checked' : '' ?>>
                                            <label for="star<?= $i ?>" title="Оценка <?= $i ?> из 5">★</label>
                                        <?php endfor; ?>
                                    </div>
                                    <input type="submit" name="submit_rating" value="Оценить" class="btn btn-sm btn-primary mt-2">
                                </form>
                                
                                <?php if($userRating): ?>
                                <p class="text-success mt-2 mb-0">
                                    <i class="bi bi-check-circle"></i> 
                                    Вы поставили оценку <?= $userRating ?> из 5
                                </p>
                                <?php endif; ?>
                            <?php else: ?>
                                <p class="text-muted">
                                    <i class="bi bi-info-circle"></i> 
                                    <a href="../index.php?page=3" class="text-decoration-none">Авторизуйтесь</a>, чтобы оценить книгу
                                </p>
                            <?php endif; ?>
                        </div>

                        <!-- Кнопка добавления/удаления из желаемого -->
                        <?php if($isInWishlist): ?>
                            <button class="btn btn-outline-danger btn-lg w-100 mb-4" 
                                    onclick="removeFromWishlist(getCurrentBookId())">
                                <i class="bi bi-bookmark-dash me-2"></i>Удалить из желаемого
                            </button>
                        <?php else: ?>
                            <button class="btn btn-success btn-lg w-100 mb-4" 
                                    onclick="addToWishlist(getCurrentBookId())">
                                <i class="bi bi-bookmark-plus me-2"></i>Добавить в желаемое
                            </button>
                        <?php endif; ?>

                        <!-- Аудиоплеер -->
                        <div class="card bg-light border-0 mb-4">
                            <div class="card-body">
                                <h5 class="card-title fw-semibold mb-3">
                                    <p>Прослушать фрагмент</p>
                                </h5>
                                <audio id="audioPlayer" src="../<?= htmlspecialchars($item->bookpath) ?>" class="w-100" controls></audio>
                            </div>
                        </div>

                        <div class="card bg-light border-0">
                            <div class="card-body">
                                <h5 class="card-title fw-semibold mb-3">
                                    <i class="bi bi-info-circle me-2"></i>Описание
                                </h5>
                                <p class="card-text" style="white-space: pre-line;">
                                    <?= nl2br(htmlspecialchars($item->description)) ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Похожие по жанру книги -->
        <?php
        $similarItems = Book::GetUniqueItems($item->genre);
        if($similarItems && count($similarItems) > 1):
        ?>
        <div class="card shadow-lg border-0 rounded-4">
            <div class="card-header bg-primary text-white rounded-top-4">
                <h4 class="mb-0">Может заинтересовать</h4>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <?php 
                    $displayed = 0;
                    foreach($similarItems as $similarItem):
                        if($similarItem->id != $item->id && $displayed < 4):
                            $displayed++;
                            $similarUser = (!isset($_SESSION['reg']) || $_SESSION['reg'] == '') 
                                ? "cart_" . $similarItem->id 
                                : $_SESSION['reg'] . "_" . $similarItem->id;
                            
                            $similarInWishlist = false;
                            if($user_id) {
                                $similarInWishlist = BookUser::isBookInWishlist($user_id, $similarItem->id);
                            }
                    ?>
                    <div class="col-6 col-md-3">
                        <div class="card h-100 shadow-sm border-1">
                            <a href="itemInfo.php?name=<?= $similarItem->id ?>">
                                <img src="../<?= htmlspecialchars($similarItem->imagepath) ?>" 
                                     class="card-img-top" 
                                     alt="<?= htmlspecialchars($similarItem->name) ?>"
                                     style="height:150px; object-fit:cover;">
                            </a>
                            <div class="card-body p-2">
                                <h6 class="card-title text-primary">
                                    <a href="itemInfo.php?name=<?= $similarItem->id ?>" 
                                       class="text-decoration-none">
                                        <?= htmlspecialchars($similarItem->name) ?>
                                    </a>
                                </h6>
                                <p class="small text-muted"><?= htmlspecialchars($similarItem->author) ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <?php if($similarInWishlist): ?>
                                        <button class="btn btn-sm btn-outline-danger" 
                                                onclick="removeFromWishlist(<?= $similarItem->id ?>)">
                                            <i class="bi bi-bookmark-dash"></i>
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-outline-success" 
                                                onclick="addToWishlist(<?= $similarItem->id ?>)">
                                            <i class="bi bi-bookmark-plus"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    const speakersData = <?= json_encode($speakers) ?>;
    let currentBookId = <?= $itemId ?>;
    
    function getCurrentBookId() {
        const select = document.getElementById('speakerSelect');
        if (select) {
            return select.value;
        }
        return currentBookId;
    }
    
    function changeSpeaker() {
        const select = document.getElementById('speakerSelect');
        const selectedOption = select.options[select.selectedIndex];
        const bookpath = selectedOption.getAttribute('data-bookpath');
        const audioPlayer = document.getElementById('audioPlayer');
        const newBookId = selectedOption.value;
        
        if (bookpath && audioPlayer) {
            audioPlayer.src = '../' + bookpath;
            audioPlayer.load();
        }
        
        currentBookId = newBookId;
        
        const speakerSpan = document.querySelector('.text-secondary.ms-2');
        if (speakerSpan) {
            speakerSpan.textContent = 'Рассказчик: ' + selectedOption.text;
        }
        
        checkWishlistStatus(newBookId);
    }
    
    function checkWishlistStatus(bookId) {
        <?php if(isset($_SESSION['reg']) && $_SESSION['reg'] != ''): ?>
        fetch('../pages/wishlist_handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=check&book_id=' + bookId
        })
        .then(response => response.json())
        .then(data => {
            const wishlistButton = document.querySelector('.btn-success, .btn-outline-danger');
            if (wishlistButton) {
                if (data.inWishlist) {
                    wishlistButton.className = 'btn btn-outline-danger btn-lg w-100 mb-4';
                    wishlistButton.innerHTML = '<i class="bi bi-bookmark-dash me-2"></i>Удалить из желаемого';
                    wishlistButton.onclick = function() { removeFromWishlist(getCurrentBookId()); };
                } else {
                    wishlistButton.className = 'btn btn-success btn-lg w-100 mb-4';
                    wishlistButton.innerHTML = '<i class="bi bi-bookmark-plus me-2"></i>Добавить в желаемое';
                    wishlistButton.onclick = function() { addToWishlist(getCurrentBookId()); };
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
        <?php endif; ?>
    }

    function addToWishlist(bookId) {
        <?php if(!isset($_SESSION['reg']) || $_SESSION['reg'] == ''): ?>
            alert('Необходимо авторизоваться, чтобы добавить книгу в желаемое');
            return;
        <?php endif; ?>
        
        fetch('../pages/wishlist_handler.php', {
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
            fetch('../pages/wishlist_handler.php', {
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
</body>
</html>