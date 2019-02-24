<html>
<head>
	<title>Schedule -edit</title>
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
		// サインインしていない場合
		exit("<center>Please sign in → <a href='login.php'>SIGN IN page</a></center>");
	}
	?>
	<div align="center"><?php echo $username."'s page"; ?></div>
	<hr>
	<?php
	// DB接続
	$dsn = 'mysql:dbname=データベース名;host=localhost';
	$user = 'ユーザ名';
	$password = 'パスワード';
	$pdo = new PDO($dsn, $user, $password);
	?>

	<?php
	/* 編集処理 */
	if ($_POST['action'] == "edit") {
		$newtitle = $_POST['newtitle'];
		if (empty($newtitle)) {
			$error = "<br>error!<br>Please enter any event title.<br>";
		} else {
			$newdetail = $_POST['newdetail'];
			$newcolor = $_POST['newcolor'];
			$editid = $_POST['editid'];

			try {
				$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$pdo->beginTransaction();

				$sql = $pdo -> prepare("UPDATE events SET title=:newtitle, detail=:newdetail, color=:newcolor WHERE id=:editid");
				$params = array(':editid'=>$editid, ':newtitle'=>$newtitle, ':newdetail'=>$newdetail, ':newcolor'=>$newcolor);
				$sql -> execute($params);

				$pdo->commit();
			} catch ( Exception $e ) {
				$pdo->rollback();
				print("error<br>". $e->getMessage());
			}
		}
	}

	/* 現在の予定情報取得（変更後も適用） */
	$editid = $_POST['editid'];

	$sql = $pdo -> prepare("SELECT * FROM events WHERE id = :editid");
	$params = array(':editid'=>$editid);
	$sql -> execute($params);
	$result = $sql->fetch(PDO::FETCH_ASSOC);
	?>

	<center>
	<h1 class="circle">予定編集</h1><br>
	<form method="post">
		<h3 class="sideline">タイトル</h3><br>
		<input type="text" class="form" name="newtitle" value="<?php echo $result['title']; ?>"><br>
		<h3 class="sideline">詳細</h3><br>
		<textarea name="newdetail" class="form" cols="50" rows="10"><?php echo $result['detail']; ?></textarea><br>
		<h3 class="sideline">色</h3><br>
		<input type="radio" value="pink" name="newcolor" <?php if($result['color']=="pink") echo "checked";?>><a class="pink">pink</a>
		<input type="radio" value="blue" name="newcolor" <?php if($result['color']=="blue") echo "checked";?>><a class="blue">sky blue</a>
		<input type="radio" value="green" name="newcolor" <?php if($result['color']=="green") echo "checked";?>><a class="green">lime green</a><br>
		<input type="radio" value="purple" name="newcolor" <?php if($result['color']=="purple") echo "checked";?>><a class="purple">purple</a>
		<input type="radio" value="orange" name="newcolor" <?php if($result['color']=="orange") echo "checked";?>><a class="orange">orange</a>
		<input type="radio" value="gray" name="newcolor" <?php if($result['color']=="gray") echo "checked";?>><a class="gray">gray</a>
		<input type="hidden" name="editid" value=<?php echo $editid  ?>>
		<br><br><br>
		<button type="submit" class="btn" name="action" value="edit">確定</button><br>
	</form>
	<div style="color:red;"><?php echo $error; ?></div>
	<br>
	<hr>
	<form method="post" name="back" class="black" action="event.php">
		<input type="hidden" name="date" value=<?php echo $result['mydate'] ?>>
		<a href="javascript:back.submit()" class="line_btn">BACK</a>
	</form>
	<form class="black">
		<a href="schedule.php" class="line_btn">HOME</a>
	</form>
	</center>
	<br><br>
</body>
</html>
