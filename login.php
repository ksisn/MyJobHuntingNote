<html>
<head>
	<title>SIGN IN / SIGN UP</title>
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
	// make TABLE（tmp_users: 仮ユーザ情報）
	$sql = "CREATE TABLE if not exists tmp_users"
					. "("
					. "tmp_mail TEXT,"
					. "pass TEXT"
					. ");";
	$stmt = $pdo -> query($sql);

	// エラーメッセージ用配列
	$error = array();
	$error2 = array();
	// $checked: 現在の表示（SIGN IN / SIGN UP）を管理するための変数
	if (empty($checked)) { $checked = 0; }

	// sign in / send mail
	switch($_POST['action']) {
		case 'sign in':
				$checked = 0;
				$name = $_POST['username'];
				$mail = $_POST['mail'];
				$pass = $_POST['pass'];

				// $check: エラー検知用（$check=0:エラーなし）
				$check = 0;
				if (empty($name)) { $error['name'] = "PleaseInput!"; $check++; }
				if (empty($mail)) { $error['mail'] = "PleaseInput!"; $check++; }
				if (empty($pass)) { $error['pass'] = "PleaseInput!"; $check++; }

				if ($check == 0) {
					$sql = $pdo -> prepare("SELECT * FROM users WHERE mail = :mail");
					$params = array(':mail'=>$mail);
					$sql -> execute($params);
					$result = $sql->fetch();

					if (empty($result)) {
						$error2[0] = "Wrong mail address.<br>";
					} else if ($name != $result['name']) {
						$error2[0] = "Not exists its username.<br>";
					} else if ($pass != $result['pass']) {
						$error2[0] = "Wrong password.<br>";
					} else {
						/* ログイン */
						session_regenerate_id(true);
						$_SESSION['username'] = $name;
						$_SESSION['mail'] = $mail;
						$_SESSION['pass'] = $pass;
						header("Location: config.php");
						exit();
					}
				}
				break;

		case 'send mail':
				$checked = 1;
				$send_mail = $_POST['new_mail'];
				$tmp_pass = sha1(uniqid(rand(),1));

				if (empty($send_mail)) { $error['mail2'] = "PleaseInput!"; }
				else {
					// 登録済みユーザーか
					$sql = $pdo -> prepare("SELECT * FROM users WHERE mail = :mail");
					$params = array(':mail'=>$send_mail);
					$sql -> execute($params);
					$result = $sql->fetch();

					if (!empty($result)) { $error2[1] = "That email address has already been registered.<br>"; }
					else {
						/* メール送信 */
						mb_language("Japanese");
						mb_internal_encoding("UTF-8");

						$to      = $send_mail;
						$subject = '[My Job Hunting Note]新規登録のご案内';
						$message = <<<EOM
このたびは「My Job Hunting Note」のご利用、誠にありがとうございます。

以下の新規登録用のURLにアクセスして、登録手続きにお進みください。

（なお、このメールにお心当たりのない方は、このメールは破棄していただいて結構です。）


EOM;
						$message .= 'http://サーバURL/signup.php?ml='.$send_mail.'&pw='.$tmp_pass."\n\n";
						$headers = 'From: '.mb_encode_mimeheader("MyJobHuntingNote運営");
						$headers .= '<noreply@mjhn.be>'."\r\n";
						$pfrom = 'Return-Path :管理者メールアドレス';

						// 送信実行
						if (mb_send_mail($to, $subject, $message, $headers, $pfrom)) {
							$error2[1] = "Sent Mail! Please check!!<br>";

							// 仮メール＆パスワード確保
							$sql = $pdo -> prepare("INSERT INTO tmp_users (tmp_mail, pass) VALUES (:tmp_mail, :pass)");
							$params = array(':tmp_mail'=>$send_mail, ':pass'=>$tmp_pass);
							$sql -> execute($params);
							$send_mail = "";
						} else {
							$error2[1] = "Could Not Send Mail!<br>";
						}
					}
				}
				break;
	}
	?>


	<form method="post" class="form">
		<div class="login-wrap">
		<div class="login-html">
			<input id="tab-1" type="radio" name="tab" class="sign-in" <?php if ($checked==0) { echo "checked";} ?>>
			<label for="tab-1" class="tab">Sign In</label>
			<input id="tab-2" type="radio" name="tab" class="sign-up" <?php if ($checked==1) { echo "checked";} ?>>
			<label for="tab-2" class="tab">Sign Up</label>
			<div class="login-form">
				<div class="sign-in-htm">
					<br>
					<div class="group">
						<label for="user" class="label">Username</label>
						<input id="user" type="text" class="input" name="username" value="<?php echo $name; ?>" placeholder=<?php echo $error['name']; ?>>
					</div>
					<div class="group">
						<label for="pass" class="label">E-Mail</label>
						<input id="pass" type="text" class="input" name="mail" value="<?php echo $mail; ?>" placeholder=<?php echo $error['mail']; ?>>
					</div>
					<div class="group">
						<label for="pass" class="label">Password</label>
						<input id="pass" type="password" class="input" data-type="password" name="pass" value="<?php echo $pass; ?>" placeholder=<?php echo $error['pass']; ?>>
					</div>
					<br>
					<div class="group">
						<input type="submit" class="button" name="action" value="sign in">
					</div>
					<div class="error">
						<a><?php echo $error2[0]; ?></a>
					</div>
					<div class="hr"></div>
					<div class="foot-lnk">
						<a href="forgotpw.php">Forgot Password?</a>
					</div>
				</div>
				<div class="sign-up-htm">
					<br><br><br><br><br><br>
					<div class="group">
						<label for="pass" class="label">E-mail</label>
						<input id="pass" type="text" class="input" name="new_mail" value="<?php echo $send_mail; ?>" placeholder=<?php echo $error['mail2']; ?>>
					</div>
					<br>
					<div class="group">
						<input type="submit" class="button" name="action" value="send mail">
					</div>
					<div class="error">
						<a><?php echo $error2[1]; ?></a>
					</div>
					<div class="hr"></div>
					<div class="foot-lnk">
						<label for="tab-1">Already Member?</a>
					</div>
				</div>
			</div>
		</div>
		</div>
	</form>
</body>
</html>
