<?php
session_start();
// セッションの中身をすべて消す
$_SESSION = [];
// セッション自体を破棄する
session_destroy();

// ログイン画面へ戻る
header('Location: login.php');
exit;