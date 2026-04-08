<?php
// エラーメッセージの初期化
$error_message = '';
$stmt = []; // foreachでエラーが出ないよう空の配列で初期化

if (isset($_POST["search"])) {
    $mail = $_POST["mail"] ?? '';
    $psw  = $_POST["psw"] ?? '';

    if (empty($mail)) {
        $error_message = 'メールアドレスを入力してください。';
    } elseif (empty($psw)) {
        $error_message = 'パスワードを入力してください。';
    } else {
        try {
            // 1. データベース接続
            $dsn = "mysql:dbname=twitter;host=localhost;charset=utf8mb4";
            $username = "root"; // 環境に合わせて変更
            $password = "";     // 環境に合わせて変更
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // 配列形式で取得
            ];
            $pdo = new PDO($dsn, $username, $password, $options);

            // 2. SQL作成（プレースホルダ「?」を使うのが安全！）
            // ※本来パスワードはハッシュ化して比較しますが、一旦形を整えます
            $sql = "SELECT * FROM users WHERE email = ? AND password = ?";
            $stmt = $pdo->prepare($sql);

            // 3. 実行
            $stmt->execute([$mail, $psw]);

        } catch (PDOException $e) {
            $error_message = "DBエラー: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>ログイン画面</title>
</head>
<body>
    <h1>ログイン画面</h1>

    <?php if ($error_message): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        メールアドレス:
        <input type="text" name="mail" value="<?php echo htmlspecialchars($mail ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <br>
        パスワード:
        <input type="password" name="psw"> <br>
        <input type="submit" name="search" value="ログイン">
    </form>

    <hr>

    <h2>検索結果</h2>
    <table border="1">
        <tr><th>ID</th><th>User Name</th></tr>
        <?php foreach ($stmt as $row): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8'); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>

// SHA256を使った実装例
<?php
// --- ログイン処理の核心部分 ---

if (isset($_POST["search"])) {
    $mail = $_POST["mail"];
    $psw  = $_POST["psw"]; // ユーザーが入力した生のパスワード

    // 1. 入力されたパスワードをSHA256でハッシュ化する
    $hashed_input_psw = hash('sha256', $psw);

    try {
        $pdo = new PDO($dsn, $username, $password, $options);

        // 2. DBから「入力されたメールアドレス」かつ「ハッシュ化したパスワード」が一致するユーザーを探す
        $sql = "SELECT * FROM users WHERE email = ? AND password = ?";
        $stmt = $pdo->prepare($sql);
        
        // 実行（ハッシュ化した方のパスワードを渡す）
        $stmt->execute([$mail, $hashed_input_psw]);
        
        $user = $stmt->fetch();

        if ($user) {
            echo "ログイン成功！";
        } else {
            echo "メールアドレスまたはパスワードが違います。";
        }
    } catch (PDOException $e) {
        echo "エラー: " . $e->getMessage();
    }
}
?>



// 現代のPHP（PHP 5.5以降）では、より安全で強力な password_hash() 関数を使うのが業界標準です。

推奨される方法（password_hash）:

保存時: $hash = password_hash($password, PASSWORD_DEFAULT);

照合時: password_verify($password, $stored_hash)