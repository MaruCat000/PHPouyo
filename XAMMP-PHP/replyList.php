<?php
// DB接続
// ★一番上に書く（お約束）
// 次のページでも「誰がログインしているか」情報を保持する
session_start();

// エラーメッセージの初期化
$error_message = '';
// sql結果格納配列の初期化
$stmt = [];
// リダイレクトで$parentTweetがとれないため
$allReplys = [];
$parentTweet = ['tweet' => 'ツイートが見つかりません']; // 初期値

//$tweet_id = $_POST['tweet_id'] ?? null;
// リダイレクト用にSESSIONを用意
// ここが余分だった。前の画面addReply.phpで持ってくるので不要
//$_SESSION['current_tweet_id'] = $_POST['tweet_id'] ?? null;

// まずPOSTがあるか確認、なければSESSIONから取り出す
$tweet_id = $_POST['tweet_id'] ?? $_SESSION['current_tweet_id'] ?? null;
// 取り出したIDをSESSIONに保存しておく（次回のアクセスのため）
if ($tweet_id) {
    $_SESSION['current_tweet_id'] = $tweet_id;
}


// 99行目の修正：$pdo が見つからない問題
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


                // 前のページから POST で送られてきた tweet_id を受け取る
                // この部分に、SESSIONを足す・・・
                //$tweet_id = $_POST['tweet_id'] ?? null;

                if ($tweet_id) {
                // SQL文の準備

                    $sql = "SELECT replys.*, 
                        users.first_name, users.last_name
                        FROM replys
                        LEFT JOIN 
                            users ON replys.user_id = users.id
                        WHERE 
                            replys.tweet_id = ?";

                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$tweet_id]); // 安全にIDを流し込む


                    // PDO::FETCH_ASSOC は、データベースから取得した結果を「カラム名（列の名前）をキーにした連想配列」として受け取るための設定（フェッチモード）
                    // PDO::FETCH_ASSOC を使わない場合（デフォルト）
                    // 余計な「数字のキー」が入るため、メモリを無駄に使い、中身もごちゃごちゃする
                    $allReplys = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    // ここでセッションで渡されたツイートIDから、ツイート本文を取得しておく？
                    $sql2 = "SELECT tweet FROM tweets
                            WHERE id = ?";

                    $stmt2 = $pdo->prepare($sql2);
                    $stmt2->execute([$tweet_id]); // 安全にIDを流し込む
                    $result = $stmt2->fetch(PDO::FETCH_ASSOC);
    
                    if ($result) {
                        $parentTweet = $result;
                    }

//　このあたり？「ツイートが見つかりません」になってしまう
// リダイレクトしたらPOSTでわたってきてないから

                    // ここを追加 1件だけ取得する fetch() 
                    //$parentTweet = $stmt2->fetch(PDO::FETCH_ASSOC);




                    // 次のページcomment.phpにセッションを渡す
//                    $_SESSION['tweet_id'] = $tweet_id;
//                  $_SESSION['user_id'] = $user['id'];
//                  $_SESSION['user_name'] = ($user['first_name'] ?? '') . " " . ($user['last_name'] ?? '');
                }


?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,inital-scale=1.0">
    <link rel="stylesheet" href="style1.css" type="text/css">
    <title>リプライ一覧</title>
<!--    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; }
    </style>
-->
</head>
<body>
    <h1>こんにちは、<?php echo htmlspecialchars($_SESSION['user_name'],ENT_QUOTES); ?>さん！</h1>
    <h2>リプライ一覧</h2>
    <!--// 元のツイートを取得したい -->
    <h3><?= htmlspecialchars($parentTweet['tweet'], ENT_QUOTES, 'UTF-8') ?></h3>
    <!--
    Warning: Undefined variable $parentTweet in C:\xampp\htdocs\test\replyList.php on line 89
    Warning: Trying to access array offset on value of type null in C:\xampp\htdocs\test\replyList.php on line 89
    parentTweetがとれない→変数の位置
    -->
    <!--    
    // ユーザーIDでなくて、ユーザー名を表示したい
    // コメント一覧ボタンで、sessionでログインユーザー名とツイートIDを渡したい
    // 
-->
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>コメント内容</th>
                <th>ユーザー名</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($allReplys as $reply): ?>
                <tr>
                    <td><?= htmlspecialchars($reply['id'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($reply['reply'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($reply['first_name'], ENT_QUOTES, 'UTF-8').
                    " ".htmlspecialchars($reply['last_name'], ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <form method="POST" action="addReply.php">
        <!-- ここでセッション？
                hiddenとかでもいいし、tweet_id,user_idを渡す必要があるように思う
            -->
            <!-- 20260327 tweet_idを直した -->
             <!-- 20260327 コメントアウト
        <input type="hidden" name="tweet_id" value="<?= $tweet_id ?>">
        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
        <input type="hidden" name="user_name" value="<?= $_SESSION['user_name'] ?>">
            -->
        <!--
            <input type="hidden" name="tweet_id" value="<?= htmlspecialchars($tweet_id, ENT_QUOTES) ?>">
        -->
        <input type="submit" name="search" value="コメント入力">
    </form>
    <a href="logout.php">ログアウト</a>
</body>
</html>
