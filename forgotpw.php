<html>
<head>
	<title>FORGOT PASSWORD</title>
	<meta charset="utf-8">
	<link rel="stylesheet" href="sign.css">
</head>
<body>
	<br><br><br>
	<div class="sitename" align="center"><u>My Job Hunting Note</u></div>
	<br><br><br>

	<?php
	// DB接続
	$dsn = 'mysql:dbname=データベース名;host=localhost';
	$user = 'ユーザ名';
	$password = 'パスワード';
	$pdo = new PDO($dsn, $user, $password);

	// エラーメッセージ用配列 確保
	$error = array();
	$error2 = array();

	switch($_POST['action']) {
		case 'send mail':
				$name = $_POST['username'];
				$mail = $_POST['mail'];

				$check = 0;
				if (empty($name)) { $error['name'] = "PleaseInput!"; $check++; }
				if (empty($mail)) { $error['mail'] = "PleaseInput!"; $check++; }

				if ($check == 0) {
					$sql = $pdo -> prepare("SELECT * FROM users WHERE name = :name");
					$params = array(':name'=>$name);
					$sql -> execute($params);
					$result = $sql->fetch();

					if (empty($result)) {
						$error2[] = "Not exists its username.<br>";
					} else if ($mail != $result['mail']) {
						$error2[] = "Wrong mail address.<br>";
					} else {
						/* メール送信 */
						mb_language("Japanese");
						mb_internal_encoding("UTF-8");
						$tmp_pass = sha1(uniqid(rand(),1));

						$to      = $mail;
						$subject = '[My Job Hunting Note]パスワード再登録のご案内';
						$message .= <<<EOM
「My Job Hunting Note」のご愛用、誠にありがとうございます。

以下のパスワード再登録用のURLにアクセスして、登録手続きにお進みください。

（なお、このメールにお心当たりのない方は、このメールは破棄していただいて結構です。）


EOM;
						$message .= 'http://サーバURL/changemlpw.php?ml='.$mail.'&pw='.$tmp_pass."\n\n";
						$headers = 'From: '.mb_encode_mimeheader("MyJobHuntingNote運営");
						$headers .= '<noreply@mjhn.be>'."\r\n";

						// 送信実行
						if (mb_send_mail($to, $subject, $message, $headers)) {
							$error2[] = "Sent Mail! Please check!!<br>";

							// 仮メール＆パスワード確保
							$sql = $pdo -> prepare("INSERT INTO tmp_users (tmp_mail, pass) VALUES (:tmp_mail, :pass)");
							$params = array(':tmp_mail'=>$mail, ':pass'=>$tmp_pass);
							$sql -> execute($params);
							$mail = "";
							$name = "";
						} else {
							$error2[] = "Could Not Send Mail!<br>";
						}
					}
				}
				break;
	}
	?>

	<form method="post" class="form">
		<div class="login-wrap">
		<div class="login-html">
			<input id="tab" type="radio" name="tab" class="sign-up" checked>
			<label for="tab" class="tab">CHANGE PASSWORD</a></label>
			<div class="login-form">
				<div class="sign-up-htm">
					<br><br><br>
					<div class="group">
						<label for="user" class="label">Username</label>
						<input id="user" type="text" class="input" name="username" value="<?php echo $name; ?>" placeholder=<?php echo $error['name']; ?>>
					</div>
					<div class="group">
						<label for="pass" class="label">E-Mail</label>
						<input id="pass" type="text" class="input" name="mail" value="<?php echo $mail; ?>" placeholder=<?php echo $error['mail']; ?>>
					</div>
					<br>
					<div class="group">
						<input type="submit" class="button" name="action" value="send mail">
					</div>
					<div class="error">
						<a><?php foreach ($error2 as $value) { echo $value; } ?></a>
					</div>
					<div class="hr"></div>
					<div class="foot-lnk">
						<a href="login.php">Sign In?</a>
					</div>
				</div>
			</div>
		</div>
		</div>
	</form>
	<br><br>
</body>
</html>
