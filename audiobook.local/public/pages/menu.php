<?php 
$page = $page ?? 1;
?>

<nav>
    <div class="navbar navbar-expand bg-primary navbar-dark rounded-3 mb-4">
        <div class="containter d-flex flex-column align-items-center justify-content-center">
            <a class="navbar-brand fw-bold fs-3 text-white mx-auto" href="index.php">Audio Books</a>
            <ul class="navbar-nav ms-auto d-flex flex-row gap-3">
                    <li class="nav-item">
                        <a class=" nav-link <?php echo ($page == 1) ? 'active fw-bold' : 'text-light'; ?>" href="index.php?page=1">Каталог</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($page == 2) ? 'active fw-bold' : 'text-light'; ?>" href="index.php?page=2">Закладки</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($page == 3) ? 'active fw-bold' : 'text-light'; ?>" href="index.php?page=3">Регистрация</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($page == 4) ? 'active fw-bold' : 'text-light'; ?>" href="index.php?page=4">Панель администратора</a>
                    </li>
                </ul>
            </div>
            <!-- Поиск -->
                <form action="index.php" method="get" class="d-flex">
                    <input type="hidden" name="page" value="1">
                    <input class="form-control me-2" 
                           type="search" 
                           name="search"
                           placeholder="Поиск книги или автора..." 
                           value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>"
                           style="min-width: 250px;">
                    <button class="btn btn-light" type="submit">Найти</button>
                    <?php if(isset($_GET['search']) && $_GET['search'] != ''): ?>
                        <a href="index.php?page=1" class="btn btn-outline-light ms-2">Сбросить</a>
                    <?php endif; ?>
                </form>
        </div>
        
    </div>
</nav>

