<?php
//セッションを開始
session_start();

//データベース接続の詳細
$host = 'localhost';
$dbname = 'fitappdata';
$db_username = 'Yuji_fit';
$db_password = 'userListAdmin';

//データベースに接続
try{
    $conn = new PDO("mysql:host=$host:dbname=$dbname;charset=utf8",$db_username,$db_password)
    $conn->setAttribute(PDO::ATTR_ERRMODE,PDO::ERROMODE_EXCEPTION);
}catch(PDOException $e){
    die("データベース接続エラー:".$e->getMessage());
}

//メニューIDをセッションしてから取得
if(isset($_SESSION['menuID'])){
    $menuID = $_SESSION['menuID'];

    // トレーニング管理テーブルからメニューを取得
    try {
        $sql = "SELECT * FROM トレーニング管理 WHERE menuID = :menuID";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':menuID', $menuID);
        $stmt->execute();
        $menu = $stmt->fetch(PDO::FETCH_ASSOC);


        if ($menu) {
            $menu_content = $menu['menu'];
            $menu_date = $menu['date'];
        } else {
            die("メニューが見つかりません。");
        }
    } catch (PDOException $e) {
        die("メニューデータ取得エラー: " . $e->getMessage());
    }
}else{
    die("メニューIDがセッションに存在しません。");
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>トレーニングメニュー</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <!-- カスタムCSS -->
    <link rel="stylesheet" href="menu.css">
</head>
<body>
    <div class="container">
        <h1 class="mt-5">あなたのトレーニングメニュー</h1>
        <p class="lead">作成日: <?php echo htmlspecialchars($menu_date, ENT_QUOTES, 'UTF-8'); ?></p>
        <div class="card mt-3">
            <div class="card-body">
                <pre><?php echo htmlspecialchars($menu_content, ENT_QUOTES, 'UTF-8'); ?></pre>
            </div>
        </div>
        <a href="calender.php" class="btn btn-secondary mt-3">トレーニングをスケジュールする</a>
    </div>

    <!-- Bootstrap JS & Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
