<?php
// ★一番上に書く（お約束）
// 次のページでも「誰がログインしているか」情報を保持する
session_start();

// エラーメッセージの初期化
$error_message = '';
// sql結果格納配列の初期化
$stmt = [];

// $pdo が見つからない問題
// 
try {
    // データベース接続情報の例
    //$pdo = new PDO('mysql:host=localhost;dbname=twitter;charset=utf8', 'root', '');
    // Docker用に書き換え
    $pdo = new PDO("mysql:dbname=twitter;host=mysql;port=3306;charset=utf8mb4", "user", "password");
} catch (PDOException $e) {
    exit('データベース接続失敗。' . $e->getMessage());
}




// coment.php:6 The key "inital-scale" is not recognized and ignored.
// ここでエラー？

// もしかしてエスケープ処理？テキストエリアの処理いるのかも？？


// 20260327 ここはいらないかも？
// ログインしていない場合はログイン画面に跳ね返す（セキュリティ）
if (!isset($_SESSION['user_name'])) {
    header('Location: login.php');
    exit;
}






    // テキストエリアを読み込んでDBにINSERTする
    // user_id

    //ボタンが押された時の処理
    if (isset($_POST["registration"])) {

        if ($_SERVER["REQUEST_METHOD"] === "POST") {

            // コメント入力のチェック
            if(empty($_POST["text"])){
                //コメントが空の時
                $error_message = "記事コメントを入力してください";
            }else{
                //コメントが入力されている時
       
                // 修正後（XAMPPの標準設定）
                //$dsn = "mysql:dbname=twitter;host=localhost;charset=utf8mb4"; // [ ] は不要
                //$username = "root";  // XAMPPのデフォルトユーザーは root
                //$password = "";      // XAMPPの初期パスワードは「空（何もなし）」

                // Docker用に書き換え
                $dsn = "mysql:dbname=twitter;host=mysql;port=3306;charset=utf8mb4";
                // .env の指定通りに変更
                $username = "user";      
                $password = "password";

                

                // 入力値の受け取り
                // ここでユーザーIDが必要なのでセッションにいれる必要がある
                $user_id = $_SESSION["user_id"];
                $text = $_POST["text"];





            }
        

            
            // 入力されている場合の処理
                // INSERTする
                // プリペアードステートメントで書いていく
                $sql = "INSERT INTO tweets (user_id,tweet)
                        VALUES(:user_id,:tweet)";

                //dbhとはデータベース ハンドルの略
                $stmt = $pdo->prepare($sql);
                $stmt->execute(array(':user_id' => $user_id, ':tweet' => $text));


                
                // 記事追加成功画面に遷移する
                header("Location:tweetList.php");
                exit; // 移動後はセッションを必ず終了させる

        }


    }




?>
<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="style1.css" type="text/css">
    </head>
    <body>
        <h1>記事投稿画面</h1>
        <br>

        <!--
        // まず、ログインが必要
        <br>
        // 現在、誰としてログインしているかわかるとよい？
        <br>
        <br>

        // 記事一覧が欲しい
        <br>

-->
        <form method="POST">

            <h2><?php echo htmlspecialchars($_SESSION['user_name'],ENT_QUOTES); ?> さん</h2>
            <br>
            記事コメントを書いてください
            <br>
            <textarea name="text"></textarea>
            <br>
            <input type="submit" name="registration" value="記事コメント登録">
        </form>
        <a href="logout.php">ログアウト</a>
    </body>
</html>