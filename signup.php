<html>
<head>
	<title>SIGN UP</title>
	<meta charset="utf-8">
	<link rel="stylesheet" href="sign.css">
</head>
<body>
	<br><br><br>
	<div class="sitename" align="center"><u>My Job Hunting Note</u></div>
	<br><br><br>
	<?php
	session_start();

	// DB接続
	$dsn = 'mysql:dbname=データベース名;host=localhost';
	$user = 'ユーザ名';
	$password = 'パスワード';
	$pdo = new PDO($dsn, $user, $password);

	// make TABLE（users: 登録ユーザ情報）
	$sql = "CREATE TABLE if not exists users"
					. "("
					. "name TEXT,"
					. "mail TEXT,"
					. "pass TEXT"
					. ");";
	$stmt = $pdo -> query($sql);

	// エラーメッセージ用配列 確保
	$error = array();

	switch($_POST['action']) {
		case 'sign up':
				$new_name = $_POST['new_username'];
				$new_mail = $_GET['ml'];
				$new_pass1 = $_POST['new_pass1'];
				$new_pass2 = $_POST['new_pass2'];

				$check = 0;
				if (empty($new_name)) { $error['name'] = "PleaseInput!"; $check++; }
				if (empty($new_pass1)) { $error['pass'] = "PleaseInput!"; $check++;}
				else if ($new_pass1 != $new_pass2) { $error['miss'] = "Mistake in password re-entry.<br>"; $check++;}


				if ($check == 0) {
					try {
						$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
						$pdo->beginTransaction();

						// users にデータを入れる
						$sql = $pdo -> prepare("INSERT INTO users (name,mail,pass) VALUES (:name, :mail, :pass)");
						$params = array(':name'=>$new_name, ':mail'=>$new_mail, ':pass'=>$new_pass1);
						$sql -> execute($params);

						$pdo->commit();
						// セッション変数をセット
						session_regenerate_id(true);
						$_SESSION['username'] = $new_name;
						$_SESSION['mail'] = $new_mail;
						$_SESSION['pass'] = $new_pass1;

						/* 新規登録用 tmpデータ 削除 */
						try {
							$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
							$pdo->beginTransaction();

							$sql = $pdo -> prepare("DELETE FROM tmp_users WHERE tmp_mail=:new_mail");
							$params = array(':new_mail'=>$new_mail);
							$sql -> execute($params);

							$pdo->commit();
						} catch ( Exception $e ) {
							$pdo->rollback();
							print("error<br>". $e->getMessage());
						}
						/* サインアップ */
						header("Location: config.php");
						exit();
					} catch ( Exception $e ) {
						$pdo->rollback();
						print("error<br>". $e->getMessage());
					}
				}
				break;
	}

	/* URLが有効か（メールで送ったURLか） チェック */
	$tmp_mail = $_GET['ml'];
	$tmp_pass = $_GET['pw'];

	$sql = $pdo -> prepare("SELECT * FROM tmp_users WHERE tmp_mail=:tmp_mail and pass=:tmp_pass");
	$params = array(':tmp_mail'=>$tmp_mail, ':tmp_pass'=>$tmp_pass);
	$sql -> execute($params);
	$result = $sql->fetch();

	if (empty($result)) {
		// 不適切なURL
		exit("<center>Invalid URL error...</center>");
	}
	?>


	<form method="post" class="form">
		<div class="login-wrap">
		<div class="login-html">
			<input id="tab" type="radio" name="tab" class="sign-up" checked>
			<label for="tab" class="tab">Sign Up</label>
			<div class="login-form">
				<div class="sign-up-htm">
					<br><br><br>
					<div class="group">
						<label for="user" class="label">Username</label>
						<input id="user" type="text" class="input" name="new_username" value="<?php echo $new_name; ?>" placeholder=<?php echo $error['name']; ?>>
					</div>
					<div class="group">
						<label for="pass" class="label">Password</label>
						<input id="pass" type="password" class="input" data-type="password" name="new_pass1" value="<?php echo $new_pass1; ?>" placeholder=<?php echo $error['pass']; ?>>
					</div>
					<div class="group">
						<label for="pass" class="label">Repeat Password</label>
						<input id="pass" type="password" class="input" data-type="password" name="new_pass2" value="<?php echo $new_pass2; ?>">
					</div>
					<br>
					<div class="group">
						<input type="submit" class="button" name="action" value="sign up">
					</div>
					<div class="error">
						<a><?php echo '<br>'.$error['miss']; ?></a>
					</div>
				</div>
			</div>
		</div>
		</div>
	</form>
	<br><br>
</body>
</html>
