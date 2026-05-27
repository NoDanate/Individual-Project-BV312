<?php

class Tools{
    // Подключение к БД
    static function connect($host = 'localhost',$user = 'postgres',$pass = 'lap-Fedor12!',$dbname = 'AudioBook'){
            try{
                $dsn = "pgsql:host=$host;dbname=$dbname";
                $pdo = new PDO($dsn, $user, $pass);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                return $pdo;
            }
            catch(PDOException $e){
                die("Connection failed: "  . $e->getMessage());
            }
    }
    // Регистрация в базе данных
    static function register($name, $pass, $imagepath){
        $name = trim(htmlspecialchars($name));
        $pass = trim(htmlspecialchars($pass));

        if($name == "" || $pass == ""){
            echo "<h3><span style='color:red;'>Заполните все поля!</span></h3>";
            return false;
        }

        if(strlen($name) < 3 || strlen($name) > 30 
        || strlen($pass) < 3 || strlen($pass) > 30){
            echo "<h3><span style='color:red;'>Длина строки должна быть от 3 до 30 символов</span></h3>";
            return false;
        }

        Tools::connect();
        $customer=new Account($name, $pass, $imagepath);
        $err=$customer->intoDb();
        if($err){
            if($err==1062)
                echo "<h3><span style='color:red;'>Логин уже занят</span></h3>";
            else
                echo "<h3><span style='color:red;'>Ошибка. Код" .$err. "!</span></h3>";
            return false;
        }
        return true;
    }
    // Авторизация
    static function login($name, $pass){
        $name = trim(htmlspecialchars($name));
        $pass = trim(htmlspecialchars($pass));

        if($name == "" || $pass == ""){
            echo "<h3><span style='color:red;'>Заполните все поля!</span></h3>";
            return false;
        }

        if(strlen($name) < 3 || strlen($name) > 30 
        || strlen($pass) < 3 || strlen($pass) > 30){
            echo "<h3><span style='color:red;'>Длина строки должна быть от 3 до 30 символов</span></h3>";
            return false;
        }

        Tools::connect();

        $customer = Account::fromDbName($name);
        if($customer && $pass === $customer->pass){
            if(($customer->roleid) === 1){
                $_SESSION['admin'] = $customer->login;
                $_SESSION['reg'] = $customer->login;
            }
            else{
                $_SESSION['user'] = $customer->login;
                $_SESSION['reg'] = $customer->login;
            }
            return true;
        } else {
            echo '<div class="alert alert-warning">Неверный пароль</div>';
        }
        return false;
    }
}
// Класс аккаунта пользователя
class Account{
    public $id;
    public $login;
    public $pass;//сделать через md5
    public $roleid;

    function __construct($login, $pass, $id){
        $this->login=$login;
        $this->pass=$pass;
        $this->id=$id;
        if ($login=='admin_books'){
            $this->roleid = 1;
        }
        else{
            $this->roleid = 2;
        }
    }
    // Добавление пользователя в БД
    function intoDb(){
        try{
            $pdo=Tools::connect();
            $ps=$pdo->prepare("INSERT INTO account (login, pass, roleid)
            VALUES (:login, :pass, :roleid, :imagepath)");
            $ps->execute([
                ':login' => $this->login,
                ':pass' => $this->pass,
                ':roleid' => $this->roleid ?? 2
            ]);
            
        } catch(PDOException $e){
            $err=$e->getMessage();
            if(strpos($err, 'duplicate key') !== false)
                return 1062;
            else
                return $e->getMessage();
        }
    }
    // Получение пользователя по id в таблице из БД
    static function fromDb($id){
        $customer=null;
        try{
            $pdo=Tools::connect();
            $ps=$pdo->prepare("SELECT * FROM account WHERE id=?");
            $ps->execute([$id]);
            $row=$ps->fetch(PDO::FETCH_ASSOC);
            $customer=new Account($row['login'],$row['pass'], $row['id']);
            return $customer;
        } catch (PDOException $e){
            echo $e->getMessage();
            return false;
        }
    }
    // Получение пользователя по имени(логину) из базы данных
    static function fromDbName($Name){
        $customer=null;
        try{
            $pdo=Tools::connect();
            $ps=$pdo->prepare("SELECT * FROM account WHERE login=?");
            $ps->execute([$Name]);
            $row=$ps->fetch(PDO::FETCH_ASSOC);
            if($row){
                $customer=new Account($row['login'],$row['pass'], $row['id']);
            }
            return $customer;
        } catch (PDOException $e){
            echo $e->getMessage();
            return false;
        }
    }
}

class Book{
    public $id, $name, $author, $genre, $description, $imagepath, $speaker, $bookpath;

    function __construct($id, $name, $author, $genre, $description, $imagepath, $speaker, $bookpath){
        $this->id = $id;
        $this->name = $name;
        $this->author = $author;
        $this->genre = $genre;
        $this->description = $description;
        $this->imagepath = $imagepath;
        $this->speaker = $speaker;
        $this->bookpath = $bookpath;
    }
    // Добавление в БД
    function intoDb(){
        try{
            $pdo=Tools::connect();
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $ps=$pdo->prepare("INSERT INTO book (name, author, genre, description, imagepath, speaker, bookpath)
            VALUES (:name, :author, :genre, :description, :imagepath, :speaker, :bookpath)");
            $ps->execute([
                ':name' => $this->name,
                ':author' => $this->author,
                ':genre' => $this->genre,
                ':description' => $this->description,
                ':imagepath' => $this->imagepath,
                ':speaker' => $this->speaker,
                ':bookpath' => $this->bookpath,
            ]);
            return true;
        } catch(PDOException $e){
            return $e->getMessage();
        }
    }
    // Получение информации о книге по id из БД
    static function fromDb($id){
        $customer=null;
        try{
            $pdo=Tools::connect();
            $ps=$pdo->prepare("SELECT * FROM book WHERE id=?");
            $ps->execute([$id]);
            $row=$ps->fetch(PDO::FETCH_ASSOC);
            if($row){
                $customer=new Book($row['id'], $row['name'],$row['author'],
                $row['genre'],$row['description'],
                $row['imagepath'], $row['speaker'], 
                $row['bookpath']);
            }
            return $customer;
        } catch (PDOException $e){
            echo $e->getMessage();
            return false;
        }
    }
    //Получение массива книг по жанру (если жанр пустой то просто список всех книг)
    static function GetItems($genre){
        $items = [];
        try{
            $pdo = Tools::connect();
            if($genre == ''){
                $ps = $pdo->prepare('SELECT * FROM book ORDER BY id');
                $ps->execute();
            } else {
                $ps = $pdo->prepare('SELECT * FROM book WHERE genre = ? ORDER BY id');
                $ps->execute([$genre]);
            }

            while($row = $ps->fetch(PDO::FETCH_ASSOC)){
                $item = new Book(
                    $row['id'],
                    $row['name'],
                    $row['author'],
                    $row['genre'],
                    $row['description'],
                    $row['imagepath'],
                    $row['speaker'],
                    $row['bookpath']
                );
                $items[] = $item;
            }
            return $items;
        } catch (PDOException $e){
            echo $e->getMessage();
            return false;
        }
    }
    // Получение рейтинга книги по id 
    static function getBookRating($book_id){
        try{
            $pdo = Tools::connect();
            $ps = $pdo->prepare("
                SELECT AVG(rate) as avg_rate, COUNT(*) as count_rates 
                FROM bookuser 
                WHERE book_id = ? AND rate IS NOT NULL
            ");
            $ps->execute([$book_id]);
            $result = $ps->fetch(PDO::FETCH_ASSOC);
            
            if($result && $result['count_rates'] > 0){
                return [
                    'avg' => round($result['avg_rate'], 2),
                    'count' => $result['count_rates']
                ];
            }
            return ['avg' => 0, 'count' => 0];
        } catch(PDOException $e){
            return ['avg' => 0, 'count' => 0];
        }
    }
    // Отображение карточки книги в каталоге
    function Draw(){
        $id = htmlspecialchars($this->id);
        $name= htmlspecialchars($this->name);
        $author = htmlspecialchars($this->author);
        $genre = htmlspecialchars($this->genre);
        $image = htmlspecialchars($this->imagepath);
        
        $ratingData = self::getBookRating($this->id);
        $avgRating = $ratingData['avg'];
        $ratingCount = $ratingData['count'];
        
        if ($avgRating > 0 && $ratingCount > 0){
            $fullStars = floor($avgRating);
            $halfStar = ($avgRating - $fullStars) >= 0.5;

            $starRating = '';

            for($i = 1; $i <= 5; $i++) {
                if($i <= $fullStars) {
                    $starRating .= "★";
                } elseif($halfStar && $i == $fullStars + 1) {
                    $starRating .= "⯪";
                } else {
                    $starRating .= "☆";
                }
            }
            $starRating .= " ({$ratingCount})";
        }
        else{
            $starRating = 'нет оценок';
        }

        $buttonText = 'В желаемое';
        $buttonAction = "addToWishlist($id)";
        $buttonClass = 'btn-outline-success';
        
        if(isset($_SESSION['reg']) && $_SESSION['reg'] != '') {
            $userId = BookUser::getUserIdByLogin($_SESSION['reg']);
            if($userId && BookUser::isBookInWishlist($userId, $this->id)) {
                $buttonText = 'В желаемом';
                $buttonAction = "removeFromWishlist($id)";
                $buttonClass = 'btn-outline-danger';
            }
        }

        echo <<<HTML
            <div class='col-12 col-sm-6 col-md-4 col-lg-3 mb-4'>
                <div class='card h-100 shadow-sm border-1'>
                    <div class='position-relative'>
                        <a href='pages/itemInfo.php?name=$id' target='_blank'>
                            <img src='$image'
                                class='card-img-top'
                                alt='$name'
                                style='height:200px; object-fit:cover;'>
                        </a>
                    </div>
                    <div class='card-body d-flex flex-column'>
                        <h5 class='card-title text-primary mb-2'>
                            <a href='pages/itemInfo.php?name=$id' target='_blank'
                            class='text-decoration-none text-primary fw-semibold'>
                                $name
                            </a>
                        </h5>
                        <h6 class='card-title text-primary mb-2'>
                            <a href='pages/itemInfo.php?name=$id' target='_blank'
                            class='text-decoration-none text-primary fw-semibold'>
                                $author
                            </a>
                        </h6>
                        <p class='card-text text-muted small flex-grow-1'
                        style='overflow:auto; max-height:70px;'>
                            $genre
                        </p>
                        <p>
                            $starRating
                        </p>
                        <div class='card-footer bg-transparent border-0 p-0'>
                            <button class='btn $buttonClass w-100 mt-2 gap-2'
                            onclick="$buttonAction">
                                <i class="bi bi-bookmark-plus me-2"></i>$buttonText
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        HTML;
    }
    // Отображение информации о аудиокниге в избранном
    function DrawCart(){
        $id = htmlspecialchars($this->id);
        $name = htmlspecialchars($this->name);
        $author = htmlspecialchars($this->author);
        $genre = htmlspecialchars($this->genre);
        $speaker = htmlspecialchars($this->speaker);
        $image = htmlspecialchars($this->imagepath);

        echo <<<HTML
        <div class='card mb-3 shadow-sm border-1'>  
            <div class='row g-0 align-items-center p-3'>
                <div class='col-3 col-sm-2'>
                    <a href='pages/itemInfo.php?name=$id' target='_blank'>
                        <img src="$image" alt='$name' class='img-fluid rounded' style='max-height: 100px; object-fit:cover;'>
                    </a>
                </div>
                <div class='col-6 col-sm-7'>
                    <h6 class='card-title text-primary mb-1'>
                        <a href='pages/itemInfo.php?name=$id' target='_blank' class='text-decoration-none'>
                            $name
                        </a>
                    </h6>
                    <p class='text-muted small mb-1'>Автор: $author</p>
                    <p class='text-muted small mb-1'>Жанр: $genre</p>
                    <p class='text-muted small mb-0'>Рассказчик: $speaker</p>
                </div>
                <div class='col-3 col-sm-3 text-end'>
                    <button type="button" class='btn btn-sm btn-outline-danger' onclick='removeFromWishlist($id)'>
                        <i class="bi bi-trash"></i> Удалить
                    </button>
                </div>
            </div>
        </div>
        HTML;
    }
    //Получение уникальных книг (так как есть разные рассказчики, а книга одна)
    static function GetUniqueItems($genre = '') {
        $items = [];
        try {
            $pdo = Tools::connect();
            if ($genre == '') {
                $ps = $pdo->prepare('
                    SELECT DISTINCT ON (name, author) 
                        id, name, author, genre, description, imagepath, speaker, bookpath
                    FROM book 
                    ORDER BY name, author, id
                ');
                $ps->execute();
            } else {
                $ps = $pdo->prepare('
                    SELECT DISTINCT ON (name, author) 
                        id, name, author, genre, description, imagepath, speaker, bookpath
                    FROM book 
                    WHERE genre = ? 
                    ORDER BY name, author, id
                ');
                $ps->execute([$genre]);
            }

            while($row = $ps->fetch(PDO::FETCH_ASSOC)) {
                $item = new Book(
                    $row['id'],
                    $row['name'],
                    $row['author'],
                    $row['genre'],
                    $row['description'],
                    $row['imagepath'],
                    $row['speaker'],
                    $row['bookpath']
                );
                $items[] = $item;
            }
            return $items;
        } catch (PDOException $e) {
            echo $e->getMessage();
            return false;
        }
    }
    // Получение всех расказчиков у книги
    static function getBookSpeakers($name, $author) {
        $speakers = [];
        try {
            $pdo = Tools::connect();
            $ps = $pdo->prepare("
                SELECT id, speaker, bookpath 
                FROM book 
                WHERE name = ? AND author = ?
                ORDER BY speaker
            ");
            $ps->execute([$name, $author]);
            
            while($row = $ps->fetch(PDO::FETCH_ASSOC)) {
                $speakers[] = [
                    'id' => $row['id'],
                    'speaker' => $row['speaker'],
                    'bookpath' => $row['bookpath']
                ];
            }
            return $speakers;
        } catch (PDOException $e) {
            return [];
        }
    }
    //Поиск в уникальных книгах по имени и автору
    static function searchUniqueBooks($query) {
        $books = [];
        try {
            $pdo = Tools::connect();
            $searchTerm = "%{$query}%";
            
            $ps = $pdo->prepare("
                SELECT DISTINCT ON (name, author) 
                    id, name, author, genre, description, imagepath, speaker, bookpath
                FROM book 
                WHERE LOWER(name) LIKE LOWER(?) 
                OR LOWER(author) LIKE LOWER(?)
                ORDER BY name, author, id
            ");
            
            $ps->execute([$searchTerm, $searchTerm]);
            
            while($row = $ps->fetch(PDO::FETCH_ASSOC)) {
                $book = new Book(
                    $row['id'],
                    $row['name'],
                    $row['author'],
                    $row['genre'],
                    $row['description'],
                    $row['imagepath'],
                    $row['speaker'],
                    $row['bookpath']
                );
                $books[] = $book;
            }
            return $books;
        } catch(PDOException $e) {
            return [];
        }
    }
}
// Класс таблицы BookUser (для работы избранного и оценивания)
class BookUser {
    public $user_id;
    public $book_id;
    public $rate;

    function __construct($user_id, $book_id, $rate) {
        $this->user_id = $user_id;
        $this->book_id = $book_id;
        $this->rate = $rate;
    }
    // Добавление записи в таблицу, для избранного и оценивания
    function intoDb() {
        try {
            $pdo = Tools::connect();
            
            $check = $pdo->prepare("SELECT user_id FROM bookuser WHERE user_id = ? AND book_id = ?");
            $check->execute([$this->user_id, $this->book_id]);
            
            if ($check->fetch()) {
                return false;
            }
            
            $ps = $pdo->prepare("INSERT INTO bookuser (user_id, book_id, rate) VALUES (:user_id, :book_id, :rate)");
            $ps->execute([
                ':user_id' => $this->user_id,
                ':book_id' => $this->book_id,
                ':rate' => $this->rate
            ]);
            return true;
        } catch(PDOException $e) {
            return $e->getMessage();
        }
    }
    // Удаление записи из таблицы
    static function deleteFromDb($user_id, $book_id) {
        try {
            $pdo = Tools::connect();
            $ps = $pdo->prepare("DELETE FROM bookuser WHERE user_id = ? AND book_id = ?");
            $ps->execute([$user_id, $book_id]);
            return true;
        } catch(PDOException $e) {
            return $e->getMessage();
        }
    }
    // Получение всех книг, которые содержатся у пользователя в избранном
    static function getUserBooks($user_id) {
        $books = [];
        try {
            $pdo = Tools::connect();
            $ps = $pdo->prepare("
                SELECT book.* FROM book 
                JOIN bookuser ON book.id = bookuser.book_id 
                WHERE bookuser.user_id = ?
            ");
            $ps->execute([$user_id]);
            
            while($row = $ps->fetch(PDO::FETCH_ASSOC)) {
                $book = new Book(
                    $row['id'],
                    $row['name'],
                    $row['author'],
                    $row['genre'],
                    $row['description'],
                    $row['imagepath'],
                    $row['speaker'],
                    $row['bookpath']
                );
                $books[] = $book;
            }
            return $books;
        } catch(PDOException $e) {
            return [];
        }
    }
    // Проверка на наличие книги в избранном
    static function isBookInWishlist($user_id, $book_id) {
        try {
            $pdo = Tools::connect();
            $ps = $pdo->prepare("SELECT user_id FROM bookuser WHERE user_id = ? AND book_id = ?");
            $ps->execute([$user_id, $book_id]);
            return $ps->fetch() ? true : false;
        } catch(PDOException $e) {
            return false;
        }
    }
    // Получение id пользователя по логину
    static function getUserIdByLogin($login) {
        try {
            $pdo = Tools::connect();
            $ps = $pdo->prepare("SELECT id FROM account WHERE login = ?");
            $ps->execute([$login]);
            $result = $ps->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['id'] : null;
        } catch(PDOException $e) {
            return null;
        }
    }
    // Перезапить оценки, выданной пользователем
    static function updateRating($user_id, $book_id, $rate) {
        try {
            $pdo = Tools::connect();
            
            $check = $pdo->prepare("SELECT user_id FROM bookuser WHERE user_id = ? AND book_id = ?");
            $check->execute([$user_id, $book_id]);
            
            if ($check->fetch()) {
                $ps = $pdo->prepare("UPDATE bookuser SET rate = ? WHERE user_id = ? AND book_id = ?");
                $ps->execute([$rate, $user_id, $book_id]);
            } else {
                $ps = $pdo->prepare("INSERT INTO bookuser (user_id, book_id, rate) VALUES (?, ?, ?)");
                $ps->execute([$user_id, $book_id, $rate]);
            }
            return true;
        } catch(PDOException $e) {
            return $e->getMessage();
        }
    }
    // Получение оценки, выданной пользователем
    static function getUserRating($user_id, $book_id) {
        try {
            $pdo = Tools::connect();
            $ps = $pdo->prepare("SELECT rate FROM bookuser WHERE user_id = ? AND book_id = ?");
            $ps->execute([$user_id, $book_id]);
            $result = $ps->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['rate'] : null;
        } catch(PDOException $e) {
            return null;
        }
    }
}
?>