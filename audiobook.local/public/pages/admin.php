<?php 
if(!isset($_SESSION['admin'])){
    echo '<div><span style="color:red">Только для админов!!!!! Прочь!!!!! ))))</span></div>';
    exit;
}

// Обработка удаления
if(isset($_GET['delete']) && is_numeric($_GET['delete'])){
    $id = (int)$_GET['delete'];
    try {
        $pdo = Tools::connect();
        $ps = $pdo->prepare("SELECT imagepath, bookpath FROM book WHERE id = ?");
        $ps->execute([$id]);
        $book = $ps->fetch(PDO::FETCH_ASSOC);
        
        if($book){
            if(file_exists($book['imagepath'])) unlink($book['imagepath']);
            if(file_exists($book['bookpath'])) unlink($book['bookpath']);
            
            $ds = $pdo->prepare("DELETE FROM bookuser WHERE book_id = ?");
            $ds->execute([$id]);
            $ps = $pdo->prepare("DELETE FROM book WHERE id = ?");
            $ps->execute([$id]);
            
            echo '<div class="alert alert-success">Книга успешно удалена!</div>';
        }
    } catch(PDOException $e){
        echo '<div class="alert alert-danger">Ошибка удаления: ' . $e->getMessage() . '</div>';
    }
}

// Обработка редактирования
if(isset($_POST['updatebtn'])){
    $id = (int)$_POST['id'];
    $name = trim(htmlspecialchars($_POST['name']));
    $author = trim(htmlspecialchars($_POST['author']));
    $genre = trim(htmlspecialchars($_POST['genre']));
    $speaker = trim(htmlspecialchars($_POST['speaker']));
    $description = trim(htmlspecialchars($_POST['description']));
    
    try {
        $pdo = Tools::connect();
        
        $ps = $pdo->prepare("SELECT imagepath, bookpath FROM book WHERE id = ?");
        $ps->execute([$id]);
        $current = $ps->fetch(PDO::FETCH_ASSOC);
        
        $imagepath = $current['imagepath'];
        $bookpath = $current['bookpath'];
        
        if(is_uploaded_file($_FILES['imagepath']['tmp_name'])){
            if(file_exists($imagepath)) unlink($imagepath);

            $imagepath = "images/" . $_FILES['imagepath']['name'];
            move_uploaded_file($_FILES['imagepath']['tmp_name'], $imagepath);
        }
        
        if(is_uploaded_file($_FILES['bookpath']['tmp_name'])){
            if(file_exists($bookpath)) unlink($bookpath);

            $bookpath = "books/" . $_FILES['bookpath']['name'];
            move_uploaded_file($_FILES['bookpath']['tmp_name'], $bookpath);
        }
        
        $ps = $pdo->prepare("UPDATE book SET name = ?, author = ?, genre = ?, description = ?, imagepath = ?, speaker = ?, bookpath = ? WHERE id = ?");
        $ps->execute([$name, $author, $genre, $description, $imagepath, $speaker, $bookpath, $id]);
        
        echo '<div class="alert alert-success">Книга "' . $name . '" успешно обновлена!</div>';
        
    } catch(PDOException $e){
        echo '<div class="alert alert-danger">Ошибка обновления: ' . $e->getMessage() . '</div>';
    }
}

// Обработка добавления
if(isset($_POST['addbtn'])){
    if(is_uploaded_file($_FILES['imagepath']['tmp_name'])){
        $imagepath = "images/" . $_FILES['imagepath']['name'];
        move_uploaded_file($_FILES['imagepath']['tmp_name'], $imagepath);
    }
    if(is_uploaded_file($_FILES['bookpath']['tmp_name'])){
        $bookpath = "books/" . $_FILES['bookpath']['name'];
        move_uploaded_file($_FILES['bookpath']['tmp_name'], $bookpath);
    }

    $name = trim(htmlspecialchars($_POST['name']));
    $author = trim(htmlspecialchars($_POST['author']));
    $genre = trim(htmlspecialchars($_POST['genre']));
    $speaker = trim(htmlspecialchars($_POST['speaker']));
    $info = trim(htmlspecialchars($_POST['description']));
    $rates = '{}';

    $item = new Book($id = 0, $name, $author, $genre, $info, $imagepath, $speaker, $bookpath);
    $item->intoDb();

    echo '<div class="alert alert-success">Книга <strong>' . $name . '</strong> успешно добавлена!</div>';
}

//редактирование или добавление
$mode = isset($_GET['mode']) ? $_GET['mode'] : 'list';
$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;


$editBook = null;
if($mode == 'edit' && $editId > 0){
    $editBook = Book::fromDb($editId);
}
?>

<div class="container mt-4 mb-5">
    
    <?php if($mode == 'list'): ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Управление книгами</h2>
            <a href="index.php?page=4&mode=add" class="btn btn-success">
                <i class="bi bi-plus-circle"></i> Добавить новую книгу
            </a>
        </div>
        
        <?php
        $books = Book::GetItems('');
        if($books && count($books) > 0):
        ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Обложка</th>
                        <th>Название</th>
                        <th>Автор</th>
                        <th>Жанр</th>
                        <th>Рассказчик</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($books as $book): ?>
                    <tr>
                        <td><?= $book->id ?></td>
                        <td>
                            <img src="<?= htmlspecialchars($book->imagepath) ?>" 
                                 alt="<?= htmlspecialchars($book->name) ?>" 
                                 style="width: 50px; height: 50px; object-fit: cover;">
                        </td>
                        <td><?= htmlspecialchars($book->name) ?></td>
                        <td><?= htmlspecialchars($book->author) ?></td>
                        <td><?= htmlspecialchars($book->genre) ?></td>
                        <td><?= htmlspecialchars($book->speaker) ?></td>
                        <td>
                            <a href="index.php?page=4&mode=edit&edit=<?= $book->id ?>" 
                               class="btn btn-sm btn-primary me-1">
                                <i class="bi bi-pencil"></i> Ред.
                            </a>
                            <a href="index.php?page=4&delete=<?= $book->id ?>" 
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Вы уверены, что хотите удалить книгу «<?= htmlspecialchars($book->name) ?>»?')">
                                <i class="bi bi-trash"></i> Уд.
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="alert alert-info">Книг пока нет. <a href="index.php?page=4&mode=add">Добавить первую книгу</a></div>
        <?php endif; ?>
        
    <?php elseif($mode == 'add' || $mode == 'edit'): ?>
        <div class="card shadow-lg border-0 rounded-4">
            <div class="card-header bg-primary text-white text-center rounded-top-4">
                <h4 class="mb-0"><?= $mode == 'add' ? 'Добавить новую книгу' : 'Редактировать книгу' ?></h4>
            </div>
            <div class="card-body p-4">
                <form action="index.php?page=4" method="post" enctype="multipart/form-data">
                    <?php if($mode == 'edit' && $editBook): ?>
                        <input type="hidden" name="id" value="<?= $editBook->id ?>">
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">Название</label>
                        <input type="text" class="form-control" name="name" required
                               value="<?= $editBook ? htmlspecialchars($editBook->name) : '' ?>">
                    </div>

                    <div class="mb-3">
                        <label for="genre" class="form-label fw-semibold">Жанр</label>
                        <input type="text" class="form-control" name="genre" required
                               value="<?= $editBook ? htmlspecialchars($editBook->genre) : '' ?>">
                    </div>

                    <div class="mb-3">
                        <label for="author" class="form-label fw-semibold">Автор</label>
                        <input type="text" class="form-control" name="author" required
                               value="<?= $editBook ? htmlspecialchars($editBook->author) : '' ?>">  
                    </div>

                    <div class="mb-3">
                        <label for="speaker" class="form-label fw-semibold">Рассказчик</label>
                        <input type="text" class="form-control" name="speaker" required
                               value="<?= $editBook ? htmlspecialchars($editBook->speaker) : '' ?>">  
                    </div>
                    
                    <div class="mb-3">
                        <label class="fw-semibold form-label" for="description">Описание</label>
                        <textarea class="form-control" rows="4" name="description" required><?= $editBook ? htmlspecialchars($editBook->description) : '' ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="fw-semibold form-label" for="imagepath">Выберите картинку 
                            <?php if($mode == 'edit'): ?>
                                <span class="text-muted">(оставьте пустым, чтобы не менять)</span>
                            <?php endif; ?>
                        </label>
                        <?php if($mode == 'edit' && $editBook): ?>
                            <div class="mb-2">
                                <img src="<?= htmlspecialchars($editBook->imagepath) ?>" 
                                     alt="Текущая обложка" 
                                     style="max-width: 150px; max-height: 150px; object-fit: cover;">
                                <p class="text-muted small mt-1">Текущий файл: <?= basename($editBook->imagepath) ?></p>
                            </div>
                        <?php endif; ?>
                        <input type="file" accept="image/*" class="form-control" name="imagepath"
                               <?= $mode == 'add' ? 'required' : '' ?>>
                    </div>

                    <div class="mb-4">
                        <label class="fw-semibold form-label" for="bookpath">Выберите аудиокнигу 
                            <?php if($mode == 'edit'): ?>
                                <span class="text-muted">(оставьте пустым, чтобы не менять)</span>
                            <?php endif; ?>
                        </label>
                        <?php if($mode == 'edit' && $editBook): ?>
                            <p class="text-muted small">Текущий файл: <?= basename($editBook->bookpath) ?></p>
                        <?php endif; ?>
                        <input type="file" accept="audio/*" class="form-control" name="bookpath"
                               <?= $mode == 'add' ? 'required' : '' ?>>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button name="<?= $mode == 'add' ? 'addbtn' : 'updatebtn' ?>" 
                                type="submit" class="btn btn-primary">
                            <?= $mode == 'add' ? 'Добавить' : 'Сохранить изменения' ?>
                        </button>
                        <a href="index.php?page=4" class="btn btn-secondary">Отмена</a>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
    
</div>