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

// セッションからIDを取得（前画面でセットした名前と合わせる）
// リダイレクトの前の画面で $_SESSION['current_tweet_id'] としたので、ここでも合わせる
$tweet_id = $_SESSION['current_tweet_id'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;



    // テキストエリアを読み込んでDBにINSERTする
    // tweet_id
    // user_id
    // reply
    //ボタンが押された時の処理
    if (isset($_POST["registration"])) {
        $text = $_POST["text"] ?? '';

        if ($_SERVER["REQUEST_METHOD"] === "POST") {

            // コメント入力のチェック
            if(empty($_POST["text"])){
                //コメントが空の時
                $error_message = "コメントを入力してください";
            }elseif( !$tweet_id || !$user_id){
                // IDが取れてない場合のエラー回避
                $error_message = "セッションエラー：もう一度一覧からやり直してください。";
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

                
                // 20260327 ここでエラー
                // tweet_idがセッションで渡ってきてない
                // 入力値の受け取り
                /* 20260408 start ここが不要
                $tweet_id = $_SESSION["tweet_id"];
                // ここでユーザーIDが必要なのでセッションにいれる必要がある
                $user_id = $_SESSION["user_id"];
                $text = $_POST["text"];
                20260408 end
                */

                // 20260326 デバッグしてみる
                // 入力値を受け取れているか
//                echo htmlspecialchars($tweet_id);
                // 20260327 ここのuser_idが取得できていない
//                echo htmlspecialchars($user_id);
//                echo htmlspecialchars($text); 
//                exit;


            // 入力されている場合の処理
                // INSERTする
                // 20260319 以下を編集する
                // プリペアードステートメントで書いていく
                // 20260326 ここが問題？
                // キーがおかしい？
                // カンマが1つ多かった。削除してみたがまだ動かない。→解決
                $sql = "INSERT INTO replys (tweet_id,user_id,reply)
                        VALUES(:tweet_id,:user_id,:reply)";

                //dbhとはデータベース ハンドルの略
                $stmt = $pdo->prepare($sql);
                $stmt->execute(array(':tweet_id' => $tweet_id,':user_id' => $user_id, ':reply' => $text));


                // SESSIONを渡してリダイレクトできるか
                // 必要なのは
                // tweet_id
                // user_id
                
                //$_SESSION['tweet_id'] = $tweet_id;
                $_SESSION['user_id'] = $user_id;
                // 違う親となるツイートがわたってない
                $_SESSION['current_tweet_id'] = $tweet_id;




                // NULL書き込みがある　余分なINSERT文の処理が発生している


                // 20270327 次のページcomment2.phpに遷移できる
                //          DBに書き込みできたが、user_idが取得できてなくてNULLで書き込みになっている

                // コメント追加成功画面に遷移する
                header("Location:replyList.php");
                exit; // 移動後はセッションを必ず終了させる
            }

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
        <h1>リプライ書き込み画面</h1>
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
<!-- 20260327 ここのユーザー名が取得できなくなった-->
            <h2>
            <?php echo htmlspecialchars($_SESSION['user_name'],ENT_QUOTES); ?> さん</h2>
            リプライコメントを書いてください
            <br>
            <textarea name="text"></textarea>
            <br>
<!-- ここのボタンのnameがなかった-->
            <input type="submit" name="registration" value="リプライ登録">
        </form>
        <a href="logout.php">ログアウト</a>
    </body>
</html>
