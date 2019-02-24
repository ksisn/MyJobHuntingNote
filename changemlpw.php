<html>
<head>
	<title>CHANGE MAIL/PASS</title>
	<meta charset="utf-8">
	<link rel="stylesheet" href="design.css">
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

	/* URLが有効か（メールで送ったURLか） チェック*/
	$tmp_mail = $_GET['ml'];
	$tmp_pass = $_GET['pw'];
	$now_mail = $_GET['ml0'];

	$sql = $pdo -> prepare("SELECT * FROM tmp_users WHERE tmp_mail=:tmp_mail and pass=:tmp_pass");
	$params = array(':tmp_mail'=>$tmp_mail, ':tmp_pass'=>$tmp_pass);
	$sql -> execute($params);
	$result = $sql->fetch();

	if (empty($result)) {
		exit("<center>Invalid URL error...</center>");
	}


	/* 登録メール変更 */
	if (!empty($now_mail)) {
		/* 登録メールアドレス変更処理 */
		try {
			$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$pdo->beginTransaction();

			$sql = $pdo -> prepare("UPDATE users SET mail=:new_mail WHERE mail=:mail");
			$params = array(':new_mail'=>$tmp_mail, ':mail'=>$now_mail);
			$sql -> execute($params);

			$pdo->commit();
		} catch ( Exception $e ) {
			$pdo->rollback();
			print("error<br>". $e->getMessage());
		}

		/* tmpデータ 削除 */
		try {
			$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$pdo->beginTransaction();

			$sql = $pdo -> prepare("DELETE FROM tmp_users WHERE tmp_mail=:tmp_mail");
			$params = array(':tmp_mail'=>$tmp_mail);
			$sql -> execute($params);

			$pdo->commit();
		} catch ( Exception $e ) {
			$pdo->rollback();
			print("error<br>". $e->getMessage());
		}
		$_SESSION = array();

		// メアド変更完了→ログインページへ
		$html = "<center>";
		$html .= "<div><h3>登録メールアドレス変更処理が完了しました。</h3></div>";
		$html .= "<div>Please re-sign in → <a href='login.php'>SIGN IN page</a><div>";
		$html .= "</center>";
		exit($html);
	}
	?>
	<?php
	/* パスワード変更 */
	$error = "";
	if ($_POST['action'] == 'change') {
		$new_pass = $_POST['new_pass'];
		$retry = $_POST['retry'];

		/* パスワード変更処理 */
		if (empty($new_pass)) { $error = "Please input password.<br>"; }
		else if ($new_pass != $retry) { $error = "Mistake in password re-entry.<br>"; }
		else {
			try {
				$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$pdo->beginTransaction();

				$sql = $pdo -> prepare("UPDATE users SET pass=:new_pass WHERE mail=:mail");
				$params = array(':new_pass'=>$new_pass, ':mail'=>$tmp_mail);
				$sql -> execute($params);

				$pdo->commit();
			} catch ( Exception $e ) {
				$pdo->rollback();
				print("error<br>". $e->getMessage());
			}

			/* tmpデータ 削除 */
			try {
				$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$pdo->beginTransaction();

				$sql = $pdo -> prepare("DELETE FROM tmp_users WHERE tmp_mail =:tmp_mail");
				$params = array(':tmp_mail'=>$tmp_mail);
				$sql -> execute($params);

				$pdo->commit();
			} catch ( Exception $e ) {
				$pdo->rollback();
				print("error<br>". $e->getMessage());
			}
			$_SESSION = array();

			// パスワード変更完了→ログインページへ
			$html = "<center>";
			$html .= "<div><h3>パスワード変更処理が完了しました。</h3></div>";
			$html .= "<div>Please re-sign in → <a href='login.php'>SIGN IN page</a><div>";
			$html .= "</center>";
			exit($html);
		}
	}
	?>
	<center>
	<div><?php echo $error; ?></div>
	<form method="post">
	<table class="change">
		<tbody>
			<tr>
				<td><input type="password" class="form" name="new_pass" value="<?php echo $new_pass; ?>" placeholder="new password"></td>
			</tr>
			<tr>
				<td><input type="password" class="form" name="retry" value="<?php echo $retry; ?>" placeholder="repeat new password"></td>
			</tr>
			<tr>
				<td><button tyoe="submit" class="ellipse" name="action" value="change">確定</button></td>
			</tr>
		</tbody>
	</table>
	</form>
	</center>
</body>
</html>
