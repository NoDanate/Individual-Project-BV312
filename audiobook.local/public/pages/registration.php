<?php 
include_once("classes.php");

if(!isset($_POST['regbtn'])){
?>
<form action="index.php?page=3" method="post" enctype="multipart/form-data">
  <div class="form-group">
    <label for="login">Логин:</label>
    <input type="text" class="form-control" name="login" required>
  </div>

  <div class="form-group">
    <label for="pass1">Пароль:</label>
    <input type="password" class="form-control" name="pass1" required>
  </div>

  <div class="form-group">
    <label for="pass2">Пароль 2х:</label>
    <input type="password" class="form-control" name="pass2" required>
  </div>

  <div class="form-group">
    <label for="imagepath">Выберите изображение:</label>
    <input type="file" class="form-control" name="imagepath" required>
  </div>

  <button type="submit" class="btn btn-primary" name="regbtn">Register</button>
</form>
<?php
} else {
    if(is_uploaded_file($_FILES['imagepath']['tmp_name'])){
        $path="images/".$_FILES['imagepath']['name'];
        move_uploaded_file($_FILES['imagepath']['tmp_name'], $path);
    }
    if(Tools::register($_POST['login'], $_POST['pass1'], $path)){
        echo '<h3><span style="color:green;">Новый пользователь добавлен</span></h3>';
    }
}
?>