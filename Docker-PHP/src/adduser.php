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
    //$pdo = new PDO('mysql:host=localhost;dbname=twitter;charset=utf8', 'root', '');
    // Docker用に書き換え
    $pdo = new PDO("mysql:dbname=twitter;host=mysql;port=3306;charset=utf8mb4", "user", "password");
} catch (PDOException $e) {
    exit('データベース接続失敗。' . $e->getMessage());
}



//登録ボタンが押された時の処理
if (isset($_POST["registration"])) {

    if ($_SERVER["REQUEST_METHOD"] === "POST") {

        // 入力値の受け取り
        $fname = $_POST["fname"] ?? '';
        $lname = $_POST["lname"] ?? '';
        $usermail = $_POST["mail"] ?? '';
        $psw = $_POST["psw"] ?? '';

        // バリデーション（空チェック）
        if (empty($usermail)) {
            $error_message = 'メールアドレスを入力してください。';
        } elseif (empty($psw)) {
            $error_message = 'パスワードを入力してください。';
        } elseif (empty($fname)) {
            $error_message = 'ファーストネームを入力してください。';
        } elseif (empty($lname)) {
            $error_message = 'ラストネームを入力してください。';
        } else {
        // 全て入力されている場合の処理

            // メールアドレスを格納
            $usermail = $_POST["mail"];
        

            // 20260312 ここのDB接続の設定は一番上のほうがわかりやすい気がする
            // 修正後（XAMPPの標準設定）
            //$dsn = "mysql:dbname=twitter;host=localhost;charset=utf8mb4"; // [ ] は不要
            //$username = "root";  // XAMPPのデフォルトユーザーは root
            //$password = "";      // XAMPPの初期パスワードは「空（何もなし）」

            // Docker用に書き換え
            $dsn = "mysql:dbname=twitter;host=mysql;port=3306;charset=utf8mb4";
            // .env の指定通りに変更
            $username = "user";      
            $password = "password";


            // パスワードをハッシュ化して取得
            //$hashedPassword = hash("sha256", "my_secure_password");
            $inputPassword = hash("sha256", $_POST["psw"]);


            // ファーストネームを格納
            $fname = $_POST["fname"];

            // ラストネームを格納
            $lname = $_POST["lname"];




            // 20260312 ここから下のSQL処理を書き換えていく
            // INSERT文の前にSELECT文で検索して重複チェックする
            // 二段階で書く(if文？)

            



            // SQLインジェクションを防ぐため、一度変数に入れておく
            //$sql = "SELECT * FROM users WHERE email='".$_POST["mail"] ."' AND password='".["$inputPassword"]."')";
            //$sql = "SELECT * FROM users WHERE email='".$_POST["mail"] ."' AND password='".$inputPassword."'";

                // 20260313 ↓mailの入力有無をコメントアウト
                //if(@$_POST["mail"] != "" ){ //maillおよびパスワードの入力有無を確認・・。ここのif分判定いらない。↑で判定しているから
                    // SQLインジェクション: query() の中に直接 $_POST を入れるのは厳禁。悪意のある入力でDBを操作されるリスクがある
                    //$stmt = $pdo->query("SELECT * FROM users WHERE email='".$_POST["mail"] ."' OR password LIKE  '%".$_POST["psw"]."%')"); //SQL文を実行して、結果を$stmtに代入する。

                    // SQLインジェクション対策
                    // 「プリペアドステートメント（prepare）」 を使う
                    // SQL文に直接変数を入れず、? にしておく
                    $sql = "SELECT * FROM users WHERE email = ?";
                    $stmt = $pdo->prepare($sql);

                    // あとから安全に変数を流し込む
                    $stmt->execute([$usermail]);

                    // 結果を受け取る
                    $result = $stmt->fetch();
                    
                    // 20260313 すでにあるユーザーで追加処理みてみたら、この行でエラー
                    // デバッグ用
                    //var_dump($stmt); // $queryの中身を表示
                    //exit; // ここで処理を止める

                    // 20260313 動作確認のために、取得したデータを表示してみる
                    // 名前の取得がうまくいっている
                    /*
                    if ($result) {
                        echo "ユーザーが見つかりました！ 名前: " . $result['first_name']." ".$result['last_name']; // 'name'は実際のカラム名に合わせてください
                    } else {
                        echo "そのメールアドレスのユーザーは存在しません。";
                    }
                    exit;
                    */




                    // 検索した結果、メールアドレスの一致が0件以上あったらエラー処理
                    if($result > 0){

                        //$err_msg['email'] = MSG06; //このE-mailは既に使用されています。
                        $error_message = 'このメールアドレスは既に使用されています。';
                    


                        // ここでデバック用出力してみたい
                        //


                    }else{

                        // ここでINSERT文を書いて実行
                        // sqlメモ
                        //$sql = "INSERT INTO products (product_name, display_size, cpu, bat)
                        //VALUES ('$product_name', '$display_size', '$cpu', '$bat')";
                        // IDについて考える：MAX値を検索して+1する？
                        // DBを見てみたら、IDはAUTO_INCREMENTと書いてある（自動で入る）

                        /* SQLの例
                        $stmt = $dbh->prepare('INSERT INTO users(email,pass,login_time) VALUES (:email, :pass, :login_time)');

                        $stmt->execute(array(':email' => $email,':pass' => $pass, ':login_time' => date('Y-m-d H:i:s')));

                        header("Location:mypage.php");
                        */
                    
                        // プリペアードステートメントで書いていく
                        $sql = "INSERT INTO users (first_name,last_name,email,password)
                        VALUES(:first_name,:last_name,:email,:password)";

                        //dbhとはデータベース ハンドルの略
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute(array(':first_name' => $fname,':last_name' => $lname, ':email' => $usermail ,'password' => $inputPassword));

                        // ユーザー追加成功画面：ログイン画面？に移動する
                        // 下のif文のuserがnullでないときに処理する
                        // と、考えて読んでいくと、idが存在している(取得する必要がでてくる)

                        // セッションで、名前を取得しておく
                        // 20260313 ユーザーエラー判定をコメントアウト(ユーザー追加されたなら、ユーザー情報はあるはず)
                        //if ($user) {
                            // 認証成功！
                            // 次のページでも誰がログインしているかわかるように
                            // 「セッション（Session）」を使う
                            // 認証成功！セッションにユーザー情報を保持
                            // ユーザーIDは自動付与なので、検索しないと取得できないのでコメントアウト
                            //$_SESSION['user_id'] = $user['id'];
                            $_SESSION['user_name'] = $fname . " " . $lname;
                        //}
                        

                        // ユーザー追加成功画面に遷移する
                        header("Location:adduser2.php");
                        exit; // 移動後はセッションを必ず終了させる



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
        ユーザー追加
        <!--
        // ・first_name, last_name, email, passwordを登録する
        <br>
        // ・passwordはSHA256でハッシュ化して登録する
        <br>
        <br>

        // まず、存在しないユーザーか、DB検索する必要あり
        <br>

        // 「検索」ボタン？もしくは「登録」ボタン押下後にDB検索でエラーはく
        
        <br>// firstnameとlastnameがすでにDBに存在するとき
        <br>
            // 20260220 三宅さん：重複チェックはemaiだけでOK
        <br>
        // 検索ボタンなしで、登録ボタンのみで検索して反映してもよいような気がする
        // なぜなら、ボタン毎の処理を書かなくてすむのでシンプル
-->


            <h3><?php echo $error_message ?></h3>

            <form method="POST">
            ファーストネーム:
            <input type="text" name="fname" size="15">
            <br>
            ラストネーム:
            <input type="text" name="lname" size="15">
            <br>
            e-mail:
            <input type="text" name="mail" size="15">
            <br>
            パスワード:
            <input type="password" name="psw" size="15">
            <br>
            <input type="submit" name="registration" value="登録">
        </form>
    </body>
</html>