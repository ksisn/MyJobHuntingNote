<html>
<head>
	<title>HOME -User Info</title>
	<meta charset="utf-8">
	<link rel="stylesheet" href="design.css">
</head>
<body>
	<br><div class="sitename" align="center"><u>My Job Hunting Note</u></div><br>
	<hr>
	<?php
	session_start();
	$username = $_SESSION['username'];
	$usermail = $_SESSION['mail'];
	if (empty($usermail)) {
		exit("<center>Please sign in → <a href='login.php'>SIGN IN page</a></center>");
	}
	?>
	<div align="center"><?php echo $username."'s page"; ?></div>
	<hr>

	<nav>
		<ul>
			<li><a href="activity.php">Activity</a></li>
			<li><a href="conote.php">Company Note</a></li>
			<li><a href="resume.php">Resume</a></li>
			<li><a href="schedule.php">Schedule</a></li>
			<li class="current"><a href="config.php">User Info</a></li>
		</ul>
	</nav>
	<br><br>

	<?php
	// DB接続
	$dsn = 'mysql:dbname=データベース名;host=localhost';
	$user = 'ユーザ名';
	$password = 'パスワード';
	$pdo = new PDO($dsn, $user, $password);
	?>
	<?php
	if(empty($checked)) { $checked = 0; }
	$error = array();

	switch ($_POST['action']) {
		case 'change name':
				$checked = 1;
				$new_name = $_POST['new_name'];
				if (empty($new_name)) {
					$error[0] = "Please enter any user name.<br>";
					$checked = 1;
				} else {
					try {
						$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
						$pdo->beginTransaction();

						$sql = $pdo -> prepare("UPDATE users SET name=:new_name WHERE mail=:mail");
						$params = array(':new_name'=>$new_name, ':mail'=>$usermail);
						$sql -> execute($params);

						$pdo->commit();
					} catch ( Exception $e ) {
						$pdo->rollback();
						print("error<br>". $e->getMessage());
					}
					$name = $new_name;
				}
				break;

		case 'change mail':
				$checked = 2;
				$send_mail = $_POST['new_mail'];
				$pass1 = $_POST['pass'];
				$tmp_pass = sha1(uniqid(rand(),1));

				// パスワードがあってるか
				$sql = $pdo -> prepare("SELECT * FROM users WHERE mail = :mail");
				$params = array(':mail'=>$usermail);
				$sql -> execute($params);
				$result = $sql->fetch();

				if ($result['pass'] != $pass1) { $error[1] = "Wrong password!<br>"; }
				else {
					/* メール送信 */
					mb_language("Japanese");
					mb_internal_encoding("UTF-8");

					$to      = $send_mail;
					$subject = '[My Job Hunting Note]登録メール変更のご案内';
					$message .= <<<EOM
「My Job Hunting Note」のご愛用、誠にありがとうございます。

以下の登録メール変更用のURLにアクセスして、登録確定をしてください。

（なお、このメールにお心当たりのない方は、このメールは破棄していただいて結構です。）


EOM;
					$message .= 'http://サーバURL/changemlpw.php?ml0='.$usermail.'&ml='.$send_mail.'&pw='.$tmp_pass."\n\n";
					$headers = 'From: '.mb_encode_mimeheader("MyJobHuntingNote運営");
					$headers .= '<noreply@mjhn.be>'."\r\n";

					// 送信実行
					if (mb_send_mail($to, $subject, $message, $headers)) {
						$error[1] = "Sent Mail! Please check!!<br>";

						// 仮メール＆パスワード確保
						$sql = $pdo -> prepare("INSERT INTO tmp_users (tmp_mail, pass) VALUES (:tmp_mail, :pass)");
						$params = array(':tmp_mail'=>$send_mail, ':pass'=>$tmp_pass);
						$sql -> execute($params);
						$send_mail = "";
						$pass1 = "";
					} else {
						$error[1] = "Could Not Send Mail!<br>";
					}
				}
				break;

		case 'change pass':
				$checked = 3;
				$pass2 = $_POST['pass'];
				$tmp_pass = sha1(uniqid(rand(),1));


				// パスワードがあってるか
				$sql = $pdo -> prepare("SELECT * FROM users WHERE mail = :mail");
				$params = array(':mail'=>$usermail);
				$sql -> execute($params);
				$result = $sql->fetch();

				if ($result['pass'] != $pass2) { $error[2] = "Wrong password!<br>"; }
				else {
					/* メール送信 */
					mb_language("Japanese");
					mb_internal_encoding("UTF-8");

					$to      = $usermail;
					$subject = '[My Job Hunting Note]パスワード変更のご案内';
					$message .= <<<EOM
「My Job Hunting Note」のご愛用、誠にありがとうございます。

以下のパスワード変更用のURLにアクセスして、登録手続きにお進みください。

（なお、このメールにお心当たりのない方は、このメールは破棄していただいて結構です。）


EOM;
					$message .= 'http://サーバURL/changemlpw.php?ml='.$usermail.'&pw='.$tmp_pass."\n\n";
					$headers = 'From: '.mb_encode_mimeheader("MyJobHuntingNote運営");
					$headers .= '<noreply@mjhn.be>'."\r\n";

					// 送信実行
					if (mb_send_mail($to, $subject, $message, $headers)) {
						$error[2] = "Sent Mail! Please check!!<br>";

						// 仮メール＆パスワード確保
						$sql = $pdo -> prepare("INSERT INTO tmp_users (tmp_mail, pass) VALUES (:tmp_mail, :pass)");
						$params = array(':tmp_mail'=>$usermail, ':pass'=>$tmp_pass);
						$sql -> execute($params);
						$pass2 = "";
					} else {
						$error[2] = "Could Not Send Mail!<br>";
					}
				}
				break;

		case 'logout':
				$_SESSION = array();
				header("Location: login.php");
				exit();
				break;

		case 'user delete':
				/* 削除処理 */
				try {
					$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
					$pdo->beginTransaction();

					$sql = $pdo -> prepare("DELETE FROM events WHERE usermail=:usermail");
					$params = array(':usermail'=>$usermail);
					$sql -> execute($params);

					$sql = $pdo -> prepare("DELETE FROM users WHERE mail=:usermail");
					$params = array(':usermail'=>$usermail);
					$sql -> execute($params);

					$pdo->commit();
					header("Location: login.php");
					exit;
				} catch ( Exception $e ) {
					$pdo->rollback();
					print("error<br>". $e->getMessage());
				}
				break;
	}
	?>
	<?php
	$sql = $pdo -> prepare("SELECT * FROM users WHERE mail = :mail");
	$params = array(':mail'=>$usermail);
	$sql -> execute($params);
	$result = $sql->fetch();
	?>
	<center>
	<table class="info">
		<tbody>
			<tr>
				<th>ユーザーネーム</th>
				<td><?php echo $name=$result['name']?></td>
			</tr>
			<tr>
				<th>メールアドレス</th>
				<td><?php echo $result['mail']?></td>
			</tr>
			<tr>
				<th>パスワード</th>
				<td><?php echo str_repeat('*', strlen($result['pass'])); ?></td>
			</tr>
		</tbody>
	</table>

	<a style="font-size:small">※メールアドレス・パスワードの変更にはメール認証が必要です</a>

	<div class="hidden_box">
		<label for="hidden_label1"><a class="sideborder">ユーザネーム変更</a></label>
		<input type="checkbox" id="hidden_label1" class="none"<?php if($checked==1) echo 'checked'; ?>/>
		<div class="hidden_show">
			<div style="color:red;"><?php echo $error[0]; ?></div>
			<form method="post">
			<table class="change">
				<tbody>
					<tr>
						<td><input type="text" class="form" name="new_name" value="<?php echo $name; ?>" placeholder="username"></td>
					</tr>
					<tr>
						<td><button tyoe="submit" class="btn" name="action" value="change name">CHANGE</button></td>
					</tr>
				</tbody>
			</table>
			</form>
		</div>
	</div>
	<div class="hidden_box">
		<label for="hidden_label2"><a class="sideborder">メールアドレス変更</a></label>
		<input type="checkbox" id="hidden_label2" class="none" <?php if($checked==2) echo 'checked'; ?>/>
		<div class="hidden_show">
			<div style="color:red;"><?php echo $error[1]; ?></div>
			<form method="post">
			<table class="change">
				<tbody>
					<tr>
						<td><input type="text" class="form" name="new_mail" value="<?php echo $send_mail; ?>" placeholder="new mail address"></td>
					</tr>
					<tr>
						<td><input type="password" class="form" name="pass" value="<?php echo $pass1; ?>" placeholder="now password"></td>
					</tr>
					<tr>
						<td><button tyoe="submit" class="btn" name="action" value="change mail">SEND E-MAIL</button></td>
					</tr>
				</tbody>
			</table>
			</form>
		</div>
	</div>
	<div class="hidden_box">
		<label for="hidden_label3"><a class="sideborder">パスワード変更</a></label>
		<input type="checkbox" id="hidden_label3" class="none" <?php if($checked==3) echo 'checked'; ?>/>
		<div class="hidden_show">
			<div style="color:red;"><?php echo $error[2]; ?></div>
			<form method="post">
			<table class="change">
				<tbody>
					<tr>
						<td><input type="password" class="form" name="pass" value="<?php echo $pass2; ?>" placeholder="now password"></td>
					</tr>
					<tr>
						<td><button tyoe="submit" class="btn" name="action" value="change pass">SEND E-MAIL</button></td>
					</tr>
				</tbody>
			</table>
			</form>
		</div>
	</div>
	<br><br>
	<hr>
	<br>
	<form method="post" name="logout" class="red">
		<input type="hidden" name="action" value="logout"/>
		<a href="javascript:logout.submit()" class="line_btn">ログアウト</a>
	</form>
	<br>
	<form method="post" name="userdelete" class="red" action="config.php">
		<input type="hidden" name="action" value="user delete"/>
		<a href="javascript:userdelete.submit()" class="line_btn">ユーザ削除</a>
	</form>
	<br><br>
	</center>
</body>
</html>
