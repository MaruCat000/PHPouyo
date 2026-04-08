<?php

// ★一番上に書く（お約束）
// 次のページでも「誰がログインしているか」情報を保持する
session_start();

// エラーメッセージの初期化
$error_message = '';
// sql結果格納配列の初期化
$stmt = [];


// 99行目の修正：$pdo が見つからない問題
// 
try {

    // 20260302 PDOで接続を保持したい（PDO::ATTR_PERSISTENT => true）

    // データベース接続情報の例
    $pdo = new PDO('mysql:host=localhost;dbname=twitter;charset=utf8', 'root', '');
} catch (PDOException $e) {
    exit('データベース接続失敗。' . $e->getMessage());
}



//検索ボタンが押された時の処理
if (isset($_POST["search"])) {

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        // メールアドレス入力のチェック
        if (empty($_POST["mail"])) {
            $error_message = 'メールアドレスを入力してください。';
        // パスワード入力のチェック
        }elseif(empty($_POST["psw"])){
            $error_message = 'パスワードを入力してください。';
        }else{
            // メールアドレスを格納
            $usermail = $_POST["mail"];

            // メールアドレスとパスワードが入力されていたら認証する
        

            // メールアドレスもパスワードも入力されているとき、DB検索する
            // 20260217
            //データベースへ接続
            /*
            /$dsn = "mysql:dbname=[接続するDB名];host=localhost;charset=utf8mb4";
            $username = "[DBに接続するユーザ名]";
            $password = "[パスワード]";
            $options = [];
            */
            
            // start 20260218 エラー修正のためコメントアウト
            /*
            $dsn = "mysql:dbname=[twitter];host=localhost;charset=utf8mb4";
            $username = "";
            $password = "";
            $options = [];
            $pdo = new PDO($dsn, $username, $password, $options);
            end 20260218 エラー修正のためコメントアウト
            */

            // 修正後（XAMPPの標準設定）
            $dsn = "mysql:dbname=twitter;host=localhost;charset=utf8mb4"; // [ ] は不要
            $username = "root";  // XAMPPのデフォルトユーザーは root
            $password = "";      // XAMPPの初期パスワードは「空（何もなし）」




            // パスワードをハッシュ化して取得
            //$hashedPassword = hash("sha256", "my_secure_password");
            $inputPassword = hash("sha256", $_POST["psw"]);

            //if ($inputPassword === $hashedPassword) {
            //    echo "認証成功！";  // 成功するが、脆弱
            //}




            // SQLインジェクションを防ぐため、一度変数に入れておく
            //$sql = "SELECT * FROM users WHERE email='".$_POST["mail"] ."' AND password='".["$inputPassword"]."')";
            //$sql = "SELECT * FROM users WHERE email='".$_POST["mail"] ."' AND password='".$inputPassword."'";

                if(@$_POST["mail"] != "" OR @$_POST["psw"] != ""){ //maillおよびパスワードの入力有無を確認・・。ここのif分判定いらない。↑で判定しているから
                    // SQLインジェクション: query() の中に直接 $_POST を入れるのは厳禁。悪意のある入力でDBを操作されるリスクがある
                    //$stmt = $pdo->query("SELECT * FROM users WHERE email='".$_POST["mail"] ."' OR password LIKE  '%".$_POST["psw"]."%')"); //SQL文を実行して、結果を$stmtに代入する。

                    // SQLインジェクション対策
                    // 「プリペアドステートメント（prepare）」 を使う
                    // SQL文に直接変数を入れず、? にしておく
                    $sql = "SELECT * FROM users WHERE email = ? AND password = ?";
                    $stmt = $pdo->prepare($sql);

                    // あとから安全に変数を流し込む
                    $stmt->execute([$usermail, $inputPassword]);

                    // 結果を受け取る
                    $user = $stmt->fetch();

                    if ($user) {
                        // 認証成功！
                        // 次のページでも誰がログインしているかわかるように
                        // 「セッション（Session）」を使う
                        // 認証成功！セッションにユーザー情報を保持
                        $_SESSION['user_id'] = $user['id'];
                        //$_SESSION['user_name'] = $user['first_name'] . " " . $user['last_name'];
                        $_SESSION['user_name'] = ($user['first_name'] ?? '') . " " . ($user['last_name'] ?? '');
                        // セッションのuser_nameは使わないに統一したい


                        // セッションにidとe-mailとpasswordを入れておくとユーザー編集にわたったとき便利
                        $_SESSION['fname'] = $user['first_name'];
                        $_SESSION['lname'] = $user['last_name'];
                        $_SESSION['email'] = $user['email'];
                        // パスワードがハッシュ値なので注意
                        $_SESSION['psw'] = $user['password'];

                        // 別のPHPファイル（例：home.php）に移動する
                        header('Location: home.php');
                        exit; // 移動後は必ず終了させる
                    } else {
                        // 認証失敗
                        echo "メールアドレスまたはパスワードが間違っています。";
                    }

/* 20260407 start デバッグ用をコメントアウト
                    // （おまけ）画面下部のテーブル表示用に、全ユーザーを取得してみる場合
                    $stmt_all = $pdo->query("SELECT email, password FROM users");
                    $search_results = $stmt_all->fetchAll();


                    
                    // デバッグ用（あとで消す）
                    // $user が取得できた（ログイン成功した）時だけハッシュを表示する
                    if ($user) {
                        echo "DBのハッシュ（例）: " . htmlspecialchars($user['password'], ENT_QUOTES, 'UTF-8');
                    }
   
                    $inputPassword = hash("sha256", $_POST["psw"]);
                        echo "入力されたハッシュ: " . $inputPassword . "<br>";
20260407 start デバッグ用をコメントアウト
*/



                    // 20260218 ここでエラー
                    // $pdo（接続情報）が空っぽ（null）なのに、それを使ってSQLを実行しようとした」ために発生
                    // 1. 解決策：$pdo の位置を整理する
                    // $pdo を作る場所がバラバラになっている。「データベースへの接続」は、SQLを使うよりも前に、確実に行われる場所にまとめる
        
               

                    //$stmt = $pdo -> query($sql);

                    
                    /* 20260224 start
                    // 以下いらなくない？上でハッシュ化したパスワードと一致したところをSQL文で検索しているので

                    // とりあえずメールアドレスでデータを取得して
                    // パスワードを比較する？
                    // DBにあるのはすでにハッシュ化されたパスワード？

                    // このあたりで
                    // ハッシュ化のメモ
                    //$hashedPassword = hash("sha256", "password");
                    $hashedPassword =  "password";
                    //$inputPassword = hash("sha256", "my_secure_password");

                    if ($inputPassword === $hashedPassword) {
                        echo "認証成功！";  // 成功するが、脆弱
                    }
                    20260224 end
                    */

                    }
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
        <h1>ログイン画面</h1>
        <br>
        <!--// パスワードの認証には、SHA256を使う -->

        <!-- // ハッシュ化のメモ
        $hashedPassword = hash("sha256", "my_secure_password");
        $inputPassword = hash("sha256", "my_secure_password");

        if ($inputPassword === $hashedPassword) {
            echo "認証成功！";  // 成功するが、脆弱
        }
        -->
        <!--
        // 接続チェックのため
        // DBからハッシュ化される前のパスワードを取得しておいたほうがいい
-->


        <form method="POST">
            メールアドレス:
            <input type="text" name="mail">
            <br>
            パスワード:
            <input type="password" name="psw">
            <br>
            <input type="submit" name="search" value="ログイン">
        </form>

        <!--
        // 20260217ここでエラー
        // まだ中身が入っていない（定義されていない）変数を、無理やり foreach で回そうとした」ために発生
        // 具体的には、ページを表示した直後（ログインボタンを押す前）はSQLが実行されないため、変数 $stmt が空っぽの状態なのに、HTMLの下の方にある foreach ($stmt as $row) が動こうとして「そんな変数知らないよ！」と怒られている状態
-->
        <?php 
        /* start 20260218 改変→のためコメントアウト
        <table>
            <tr><th>ID</th><th>User Name</th></tr>
            <!-- ここでPHPのforeachを使って結果をループさせる -->
            <?php foreach ($stmt as $row): ?>
                <tr><td><?php echo $row[0]?></td><td><?php echo $row[1]?></td></tr>
            <?php endforeach; ?>
        </table>
        end
        */
        ?>

        <!--// 20260218 改変
        -->
<!-- 20260407 start デバッグ用をコメントアウト
        <table>
            <tr><th>mail</th><th>passward</th></tr>
            <?php if (!empty($stmt)): ?>
                <?php foreach ($stmt as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row[0], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($row[1], ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </table>


<h3>登録ユーザー一覧（デバッグ用）</h3>
    <table>
        <tr><th>Email</th><th>Hashed Password</th></tr>
        <?php if (!empty($search_results)): ?>
            <?php foreach ($search_results as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($row['password'], ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="2">データがありません。ログインを試すと表示されます。</td></tr>
        <?php endif; ?>
    </table>
20260407 start デバッグ用をコメントアウト
-->




<?php
// PHPでフォームから送信されたデータ（メールアドレスなど）を安全に表示するための基本的なスクリプト
/*if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $mail = $_POST["mail"];
    echo htmlspecialchars($mail, ENT_QUOTES, 'UTF-8');
}
*/
?>


    </body>
</html>