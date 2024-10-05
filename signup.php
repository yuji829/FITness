<?php
// セッションを開始
session_start();

// データベース接続の詳細
$host = 'localhost';
$dbname = 'fitappdata';
$username = 'Yuji_fit';
$password = 'userListAdmin';

// データベース接続
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("データベース接続エラー: " . $e->getMessage());
}

// フォームから送信されたデータの処理
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['username']) && isset($_POST['password'])) {
        $userName = $_POST['username'];
        $userPass = password_hash($_POST['password'], PASSWORD_DEFAULT); // パスワードをハッシュ化

        // データベースに挿入
        try {
            $sql = "INSERT INTO users (UserName, UserPass) VALUES (:username, :password)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':username', $userName);
            $stmt->bindParam(':password', $userPass);
            $stmt->execute();

            // サインアップ完了後、ユーザー情報入力画面へリダイレクト
            $_SESSION['username'] = $userName;
            header("Location: chatgpt.html");
            exit();

        } catch (PDOException $e) {
            echo "サインアップエラー: " . $e->getMessage();
        }
    } else {
        echo "すべてのフィールドを入力してください。";
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>サインアップ</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
     <!--外部ファイルcss-->
     <link rel="stylesheet" href="signup.css">
   
</head>
<body>
    <div class="container">
        <h2 class="mt-5">サインアップ</h2>
        <form action="signup.php" method="POST">
            <div class="form-group">
                <label for="username">ユーザー名:</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">パスワード:</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">サインアップ</button>
        </form>
    </div>
   <!--外部ファイルjavascript-->
   <script src="signup.js"></script>
</body>
</html>
