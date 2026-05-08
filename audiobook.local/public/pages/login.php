<?php 
include_once('pages/classes.php');

if ((isset($_SESSION["user"]))):
?>
    <form action="index.php<?= isset($_GET['page']) ? '?page=' . intval($_GET['page']) : ''?>" 
    method="post" class="d-flex justify-content-end align-items-center gap-2 mb-3">
        <h5 class="mb-0">
            Привет, <span class="text-primary"><?= htmlspecialchars($_SESSION['user'])?></span>
        </h5>
        <button type="submit" name="logout" class="btn btn-sm btn-secondary">Выйти</button>
    </form>

<?php elseif((isset($_SESSION["admin"]))):
?>
     <form action="index.php<?= isset($_GET['page']) ? '?page=' . intval($_GET['page']) : ''?>" 
    method="post" class="d-flex justify-content-end align-items-center gap-2 mb-3">
        <h5 class="mb-0">
            Привет, <span class="text-primary"><?= htmlspecialchars($_SESSION['admin'])?></span>
        </h5>
        <button type="submit" name="logout" class="btn btn-sm btn-secondary">Выйти</button>
    </form>

<?php else:
    
?>  
    <form action="index.php<?= isset($_GET['page']) ? '?page=' . intval($_GET['page']) : ''?>" 
    method="post" class="d-flex justify-content-end align-items-center gap-2 mb-3">
        <input type="text" name="login" class="form-control form-control-sm w-auto" placeholder="Введите логин">
        <input type="password" name="pass" class="form-control form-control-sm w-auto" placeholder="Введите пароль">
        <button type="submit" name="login_submit" class="btn btn-sm btn-primary">Войти</button>
    </form>

<?php endif; ?>