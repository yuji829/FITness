<?php
//セッションを開始
session_start();

//データベース接続の詳細
$host = 'localhost';
$dbname = 'fitappdata';
$db_username = 'Yuji-fit';
$db_password = 'userListAdmin';

//Composerのオートローダーを読み込む
require 'vendor/autoload.php';

use GuzzleHttp\Client;
use Orhanerday\OpenAi\OpenAi;

//ChatGPTのAPIキーを設定
$open_ai_key = 'MY-key';
$open_ai = new OpenAi($open_ai_key);

//デーアベースに接続
try{
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8,$db_username,$db_password");
    $conn->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
}catch(PDOException $e){
    die("データベース接続エラー:".$e->getMessage());
}

//フォームから送信されたデータを取得・サニタイズ
$gender = filter_input(INPUT_POST,'gender',FILTER_SANITIZE_STRING);
$height = filter_input(INPUT_POST,'height',FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION);
$weight = filter_input(INPUT_POST,'weight',FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION);
$age = filter_input(INPUT_POST,'age',FILTER_SANITIZE_NUMBER_INT);
$exercise_history = filter_input(INPUT_POST,'exercise_history',FILTER_SANITIZE_STRING);
$body_part = filter_input(INPUT_POST,'body_part',FILTER_SANITIZE_STRING);
$medical_conditions = filter_input(INPUT_POST,'medical_conditions',FILTER_SANITIZE_STRING);
$exercise_time = filter_input(INPUT_POST,'exercise_time',FILTER_SANITIZE_NUMBER_INT);

//ユーザーIDをセッションから取得
if(isset($_SESSION['username'])){
    $username = $_SESSION['username'];

    //ユーザーIDを取得
    $stmt = $conn->prepare("SELECT UserID FROM users WHERE UserName = username");
    $stmt = bindParam('username',$username);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if($user){
        $userID = $user['UserID'];
    }else{
        die("ユーザーが見つかりません。");
    }
}else{
    die("セッションが有効ではありません。ログインしてください。");
}

//データがすべてそろっているか確認
if($gender && $height && $weight && $age && $exercise_history && $body_part && $exercise_time){
    //chatgptテーブルにデータを挿入
    try{
        $sql = "INSERT INTO CT (gender,height, weight, age ,ExerciseHistory, parts, conditions, time, UserID)
        VALUES (:gender, :height, :weight, :age, :exercise_history, :parts, :conditions, :time, userID)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':height',$height);
        $stmt->bindParam(':weight',$weight);
        $stmt->bindParam(':age',$age);
        $stmt->bindParam(':exercise_history',$exercise_history);
        $stmt->bindParam(':parts',$body_part);
        $stmt->bindParam(':condition',$medical_conditions);
        $stmt->bindParam(':gender',$gender);
        $stmt->bindParam(':time',$exercise_time);
        $stmt->bindParam(':userID',$userID);
        $stmt->execute();

        //挿入したレコードのIDを取得
        $chatGPTID = $conn->lastInsertId();
    }catch(PDOException $e){
        die("挿入にエラー:".$e->getMessage());
    }

    //ChatGPTに送信するメッセージ作成
    $user_info = "性別: $gender, 身長: $height cm, 体重: $weight kg, 年齢: $age 歳, 運動歴: $exercise_history, 鍛えたい部位: $body_part, 既往症: $medical_conditions, 運動時間: $exercise_time 分";

    //ChatGPT APIを呼び出す
    $response = $open_ai->chat([
        'model'=>'gpt-3.5-turbo',
        'messages'=>[
            [
                'role'=>'system',
                'content'=>'あなたはフィットネスの専門家です。ユーザーに適したトレーニングメニューを作成してください。'
            ],
            [
                'role'=>'user',
                'content'=>$user_info
            ],
        ],
    ]);

    //レスポンスをデコード
    $response_list = json_decode($response,true);

    if(isset($response_list['choices'][0]['message']['content'])){
        $training_menu = $response_list['choices'][0]['message']['content'];

        //menuテーブルにトレーニングメニューを保存
        try{
            $sql = "INSERT INTO トレーニング管理(data,menu,ChatGPTID) VALUES (:data, :menu, :chatGPTID)";
            $stmt = $conn->prepare($sql);
            $current_data = data('Y-m-d');
            $stmt->bindParam(':data',$current_data);
            $stmt->bindParam(':menu',$training_menu);
            $stmt->bindParam(':chatGPTID',$chatGPTID);
            $stmt->execute();

            //menuIDを取得
            $menuID = $conn->lastInsertId();
        }catch(PDOException $e){
            die("メニューで挿入エラー:".$e->getMessage());
        }
        //メニューIDをセッションに保存してmenu.phpにリダイレクト
        $_SESSION['menuID'] = $menuID;
        header("Location:menu.php");
        exit();
    }else{
        die("ChatGPTからのレスポンスが正しくありません。");
    }
}else{
    die("必要なデータが不足しています。");
}

?>
<br>