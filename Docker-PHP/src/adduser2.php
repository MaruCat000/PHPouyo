<?php
// ★このページでも一番上に書く
session_start();

// ログインしていない場合はログイン画面に跳ね返す（セキュリティ）
if (!isset($_SESSION['user_name'])) {
    header('Location: login.php');
    exit;
}
?>


<!DOCTYPE html>
<html>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,inital-scale=1.0">
    <link rel="stylesheet" href="style1.css" type="text/css">
<body>
    <h1>こんにちは、<?php echo htmlspecialchars($_SESSION['user_name'],ENT_QUOTES); ?>さん！</h1>
    <p>登録成功しました。</p>
    <form method="POST" action="tweetlist.php">
        <input type="submit" name="search" value="ツイート一覧">
    </form>
    <a href="logout.php">ログアウト</a>
</body>
</html>