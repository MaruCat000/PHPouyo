<?php
// ★一番上に書く（お約束）
// 次のページでも「誰がログインしているか」情報を保持する
session_start();

// coment.php:6 The key "inital-scale" is not recognized and ignored.
// ここでエラー？

// もしかしてエスケープ処理？テキストエリアの処理いるのかも？？

// ログインしていない場合はログイン画面に跳ね返す（セキュリティ）
if (!isset($_SESSION['user_name'])) {
    header('Location: login.php');
    exit;
}


// エラーメッセージの初期化
$error_message = '';

// $pdo が見つからない問題
// 
try {
    // データベース接続情報の例
    $pdo = new PDO('mysql:host=localhost;dbname=twitter;charset=utf8', 'root', '');
} catch (PDOException $e) {
    exit('データベース接続失敗。' . $e->getMessage());
}

// 修正後（XAMPPの標準設定）
    $dsn = "mysql:dbname=twitter;host=localhost;charset=utf8mb4"; // [ ] は不要
    $username = "root";  // XAMPPのデフォルトユーザーは root
    $password = "";      // XAMPPの初期パスワードは「空（何もなし）」



    // テキストエリアを読み込んでDBにINSERTする
    // tweet_id
    // user_id
    // reply
    //ボタンが押された時の処理
    if (isset($_POST["registration"])) {

        if ($_SERVER["REQUEST_METHOD"] === "POST") {

        
            // 入力値の受け取り
            $tweet_id = $_SESSION["tweet_id"];
            // ここでユーザーIDが必要なのでセッションにいれる必要がある
            $user_id = $_SESSION["user_id"];
            $text = $_POST["text"];



            // 20260326 デバッグしてみる
            // 入力値を受け取れているか
            echo htmlspecialchars($_SESSION['tweet_id'],ENT_QUOTES);
            echo htmlspecialchars($_SESSION['user_id'],ENT_QUOTES);
            echo htmlspecialchars($text),ENT_QUOTES; 
            exit;

            // DB接続エラーがでる



            // バリデーション（空チェック）
            if (empty($text)) {
                $error_message = 'コメントを入力してください。';

            } else {

            
            // 入力されている場合の処理
                // INSERTする
                // 20260319 以下を編集する
                // プリペアードステートメントで書いていく
                // 20260326 ここが問題？
                // キーがおかしい？
                // カンマが1つ多かった。削除してみたがまだ動かない。
                $sql = "INSERT INTO replys (tweet_id,user_id,reply)
                        VALUES(:tweet_id,:user_id,:reply)";

                //dbhとはデータベース ハンドルの略
                $stmt = $pdo->prepare($sql);
                $stmt->execute(array(':tweet_id' => $tweet_id,':user_id' => $user_id, ':reply' => $text));



                // コメント追加成功画面に遷移する
                header("Location:comment2.php");
                exit; // 移動後はセッションを必ず終了させる

        }


    }
}



?>

<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,inital-scale=1.0">
        <link rel="stylesheet" href="style1.css" type="text/css">
    </head>
    <body>
        コメント画面
        <br>

        <!--
        // まず、ログインが必要
        <br>
        // 現在、誰としてログインしているかわかるとよい？
        <br>
        <br>

        // 記事一覧が欲しい
        <br>
        // どの記事に対してのコメントなのか
        <br>
        // 記事から「コメント画面」に飛べるとよい？
        <br>
-->

        <form method="POST">
<!-- formのactionを書くことで、遷移するだけはできた
 書かないと遷移しないし、INSERTも動かない
-->

            <?php echo htmlspecialchars($_SESSION['user_name'],ENT_QUOTES); ?> さん
            コメントを書いてください
            <br>
            <textarea name="text"></textarea>
            <br>
<!-- ここのボタンのnameがなかった-->
            <input type="submit" name="search" value="コメント登録">
        </form>
        <a href="logout.php">ログアウト</a>
    </body>
</html>
