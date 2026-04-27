<?php 
session_start();
include_once("pages/classes.php");
$page = isset($_GET['page']) ? intval($_GET['page']) : 1?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Online Shop</title>
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet">
  <link rel="stylesheet" href="css/style1.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
</head>
<body>
  <div class="container mt4">
    <!-- Хэдер -->
    <div class="row mb-3">
      <header class="col-12 text-center">
        <h1>Добро пожаловать в сервис аудиокниг</h1>
        <?php include_once("pages/login.php"); ?>
      </header>
    </div>

    <!-- Меню -->
    <div class="row mb-4">
      <nav class="col-12">
        <?php include_once('pages/menu.php') ?>
      </nav>
    </div>

    <!--Основная часть-->
    <div class="row">
      <section class="col-12">
        <?php 
        switch ($page){
          case 1:
            include_once('pages/catalog.php');
            break;
          case 2:
            include_once('pages/cart.php');
            break;
          case 3:
            include_once('pages/registration.php');
            break;
          case 4:
            include_once('pages/admin.php');
            break;
          default:
            echo "<h3>Страница не найдена</h3>";
        }
         ?>
      </section>
    </div>

    <div class="row mt-4">
        <footer class="col-12 text-center text-muted">
          Ilia inc &copy; <?php echo date('Y'); ?>
        </footer>
    </div>
  </div>

<script
  src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>
</body>
</html>