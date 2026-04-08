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

?>



<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,inital-scale=1.0">
    <link rel="stylesheet" href="style1.css" type="text/css">
    <title>コメントを書き込みました！</title>
</head>
<body>
    <?php echo htmlspecialchars($_SESSION['user_name'],ENT_QUOTES); ?> さん
    <br>
    <?php echo htmlspecialchars($_SESSION['user_id'],ENT_QUOTES); ?> :user_id
    <br>
    <?php echo htmlspecialchars($_SESSION['tweet_id'],ENT_QUOTES); ?> :tweet_id
<!-- tweet_idがセッションで渡ってない
 Warning: Undefined array key "tweet_id" in C:\xampp\htdocs\test\comment2.php on line 38
-->
<a href="logout.php">ログアウト</a>
</body>
</html>
