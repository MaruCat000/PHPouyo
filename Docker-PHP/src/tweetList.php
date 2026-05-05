<?php
// DB接続
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
    // データベース接続情報の例
    //$pdo = new PDO('mysql:host=localhost;dbname=twitter;charset=utf8', 'root', '');
    // Docker用に書き換え
    $pdo = new PDO("mysql:dbname=twitter;host=mysql;port=3306;charset=utf8mb4", "user", "password");
} catch (PDOException $e) {
    exit('データベース接続失敗。' . $e->getMessage());
}

// 修正後（XAMPPの標準設定）
            //$dsn = "mysql:dbname=twitter;host=localhost;charset=utf8mb4"; // [ ] は不要
            //$username = "root";  // XAMPPのデフォルトユーザーは root
            //$password = "";      // XAMPPの初期パスワードは「空（何もなし）」

            // Docker用に書き換え
            $dsn = "mysql:dbname=twitter;host=mysql;port=3306;charset=utf8mb4";
            // .env の指定通りに変更
            $username = "user";      
            $password = "password";


                    /* 20260225 start
                    // SQLインジェクション対策
                    // 「プリペアドステートメント（prepare）」 を使う
                    // SQL文に直接変数を入れず、? にしておく
                    $sql = "SELECT * FROM  WHERE email = ? AND password = ?";
                    $stmt = $pdo->prepare($sql);

                    // あとから安全に変数を流し込む
                    $stmt->execute([$usermail, $inputPassword]);

                    // 結果を受け取る
                    $user = $stmt->fetch();
                    20260225 end
                    */

                    // ユーザーからの入力がないので、? を使う必要はない
                    //$sql = "SELECT * FROM tweets"; 
                    // ユーザーIDから、ユーザー名を取得したいので
                    // JOIN（結合）を使ってデータを取得
                    $sql = "SELECT tweets.*, 
                            users.first_name,users.last_name
                            FROM tweets 
                            LEFT JOIN users ON tweets.user_id = users.id";
    
                    $stmt = $pdo->query($sql); // 準備不要なので直接 query() でOK

                    $allTweets = $stmt->fetchAll(PDO::FETCH_ASSOC);


                    /* 20260225 start ここでセッションが上書き？いらない処理をしていた
                    // 次のページでも誰がログインしているかわかるように
                    // 「セッション（Session）」を使う
                    // 認証成功！セッションにユーザー情報を保持
                    //$_SESSION['user_id'] = $user['id'];
                    //$_SESSION['user_name'] = $user['first_name'] . " " . $user['last_name'];
                    //$_SESSION['user_name'] = ($user['first_name'] ?? '') . " " . ($user['last_name'] ?? '');
                    // セッションにツイートIDを保持
                    //$_SESSION['tweet_id'] = $tweets['id']; 
                    20260225 end
                    */
                
                    // このままではエラーしてしまう
                    //$_SESSION['tweet_id'] = $tweets['id'];



// /* MySQLに接続 */

// /* ホスト名設定 */
// define('DB_HOST', 'localhost');
// /* データベース名 */
// define('DB_NAME', '');
// /* 接続ユーザー名 */
// define('DB_USER', '');
// /* 接続パスワード */
// define('DB_PASS', '');

// /* エラーを代入する変数 */
// $ERROR = array();

// /* 接続エラーを取得するための記述 */
// try {

// 	/* データベース操作用のオブジェクトを作成 */
// 	$db = new PDO('mysql:dbname='.DB_NAME.';host='.DB_HOST.';charset=utf8', DB_USER, DB_PASS);

// 	/* データベースを操作するSQL文 */
// 	$sql = 'SELECT * FROM posts WHERE category_id=?';
// 	/* SQL文として渡す入力データ */
// 	$q = array('100');

// 	/* SQL文を実行するための準備 */
// 	$sth = $db->prepare($sql);
// 	/* SQL文を実行 */
// 	$sth->execute($q);

// 	/* データベースからの結果を連想配列の配列で受け取る */
// 	$r = $sth->fetchAll(PDO::FETCH_ASSOC);

// /* 接続エラーの例外を処理 */
// } catch(PDOException $e) {

// 	$ERROR[] = $e->getMessage();
// }




?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,inital-scale=1.0">
    <link rel="stylesheet" href="style1.css" type="text/css">
    <title>ツイート一覧</title>
    <!-- 20260326 後々、ここのスタイルを変更していこうと考えている
                    もっとツイッターぽく？
-->
<!--    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; }
    </style>
-->
</head>
<body>
    <h1>こんにちは、<?php echo htmlspecialchars($_SESSION['user_name'],ENT_QUOTES); ?>さん！</h1>
    <h2>ツイート一覧</h2>
    <!--
    // ユーザーIDでなくて、ユーザー名を表示したい
    // コメント一覧ボタンで、sessionでログインユーザー名とツイートIDを渡したい
    -->
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>ツイート内容</th>
                <th>ユーザー名</th>
                <th>コメント一覧へ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($allTweets as $tweet): ?>
                <tr>
                    <td><?= htmlspecialchars($tweet['id'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($tweet['tweet'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($tweet['first_name'], ENT_QUOTES, 'UTF-8').
                    " ".htmlspecialchars($tweet['last_name'], ENT_QUOTES, 'UTF-8') ?>
                </td>
                    <td>
                        <form method="POST" action="replyList.php">
                            <!-- ここのtweet_idをSESSIONにしたほうがよさそう。replyListをリダイレクトするときに必要-->
                            <input type="hidden" name="tweet_id" value="<?= $tweet['id'] ?>">
                            <input type="submit" name="search" value="コメント一覧">
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <form method="POST" action="addcomment.php">
        <input type="submit" value="記事投稿">
    </form>
    <a href="logout.php">ログアウト</a>
</body>
</html>
