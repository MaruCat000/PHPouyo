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
    $pdo = new PDO('mysql:host=localhost;dbname=twitter;charset=utf8', 'root', '');
} catch (PDOException $e) {
    exit('データベース接続失敗。' . $e->getMessage());
}


// 20260327 ここはいらないかも？
// ログインしていない場合はログイン画面に跳ね返す（セキュリティ）
if (!isset($_SESSION['user_name'])) {
    header('Location: login.php');
    exit;
}




// 前のloginページから検索結果をひいてくる？セッションを使ったらよさそう
// e-mailが一致したデータをDBに上書きする→セッションでひいてきているので判定はidでもいけそう
// first_name
// last_name
// email
// password

//ボタンが押された時の処理
if (isset($_POST["registration"])) {

    if ($_SERVER["REQUEST_METHOD"] === "POST") {

        // 入力のチェック
        if(empty($_POST["fname"])){
            $error_message = "ファーストネームを入力してください";
        }elseif (empty($_POST["lname"])) {
            $error_message = 'ラストネームを入力してください。';
        }elseif (empty($_POST["email"])) {
            $error_message = 'メールアドレスを入力してください。';
        //}elseif(empty($_POST["psw"])){
            //$error_message = 'パスワードを入力してください。';
        }else{

            // 修正後（XAMPPの標準設定）
            $dsn = "mysql:dbname=twitter;host=localhost;charset=utf8mb4"; // [ ] は不要
            $username = "root";  // XAMPPのデフォルトユーザーは root
            $password = "";      // XAMPPの初期パスワードは「空（何もなし）」


            // 入力値を取得
            $id    = $_SESSION['user_id'];
            $fname = $_POST["fname"];
            $lname = $_POST["lname"];
            $email = $_POST["email"];

           
            // 入力されている場合の処理
            // UPDATEする
            // プリペアードステートメントで書いていく
            $sql = "UPDATE users 
                    SET first_name = :first_name, 
                        last_name  = :last_name, 
                        email      = :email";

            // 基本的にパスワードは変更がない限り更新しないと考えて
            // パスワードが入力されている場合のみ、SQLに追加
            if (!empty($_POST["psw"])) {
                $hashpsw = hash("sha256", $_POST["psw"]);
                $sql .= ", password = :password";
            }

            // idが一致する箇所と指定する
            $sql .= " WHERE id = :id";

            $stmt = $pdo->prepare($sql);

            // バインドするパラメータの準備
            $params = [
                ':first_name' => $fname,
                ':last_name'  => $lname,
                ':email'      => $email,
                ':id'         => $id
            ];

            // パスワードがある場合のみ配列に追加
            if (!empty($_POST["psw"])) {
                $params[':password'] = $hashpsw;
            }

            // 実行
            $stmt->execute($params);

            // セッション情報の更新（画面表示用）
            $_SESSION['fname'] = $fname;
            $_SESSION['lname'] = $lname;
            $_SESSION['email'] = $email;
            $_SESSION['user_name'] = $fname . " " . $lname;

            // 完了後のリダイレクト（例：一覧画面へ）
            header("Location: tweetList.php");
            exit;
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
        <h1>ユーザー編集</h1>
        <br>
<!--        // ・first_name, last_name, email, passwordを登録する
        <br>
        // ・passwordはSHA256でハッシュ化して登録する
        <br>
        <br>

        // まず、DBから編集したいユーザー情報を取得するひつようあり
        // 20260220 三宅さん：ログインしてからの画面なので検索されたあとになっている
        <br>
        // 以下はいらない
        // 「検索」ボタン
        <br>
        // firstname、lastname、e-mailで検索できると便利
        <br>
-->
            <form method="POST">
            ファーストネーム:
            <input type="text" name="fname" value=<?php echo htmlspecialchars($_SESSION['fname']?? '', ENT_QUOTES, 'UTF-8'); ?>>
            <br>
            ラストネーム:
            <input type="text" name="lname" value=<?php echo htmlspecialchars($_SESSION['lname']?? '', ENT_QUOTES, 'UTF-8'); ?>>
            <br>
            e-mail:
            <input type="text" name="email" value=<?php echo htmlspecialchars($_SESSION['email']?? '', ENT_QUOTES, 'UTF-8'); ?>>
            <br>
            パスワード:
<!-- ここでエラーする：パスワードは空にしておく？ -->
            <input type="password" name="psw">
            <br>
            <!-- <input type="submit" value="検索"> -->
            <input type="submit" name="registration" value="登録">
        </form>
        <a href="logout.php">ログアウト</a>
    </body>
</html>