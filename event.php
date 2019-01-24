<html>
<head>
	<title><?php echo $mydate=$_POST['date'];?></title>
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
	<?php /* DB 前処理 */
	// DB接続
	$dsn = 'mysql:dbname=データベース名;host=localhost';
	$user = 'ユーザ名';
	$password = 'パスワード';
	$pdo = new PDO($dsn, $user, $password);

	/* テーブル削除
	$stmt = $pdo->prepare('show tables from データベース名 like :tblname');
	$stmt -> execute(array(':tblname' => events));
	if ($stmt->rowCount() > 0) {
		$query = "drop table if exists ".events;
		$pdo -> exec($query);
	}
	/**/

	// DB 作成
	$sql = "CREATE TABLE if not exists events"
					. "("
					. "usermail TEXT,"
					. "id INT,"
					. "mydate TEXT,"
					. "title TEXT,"
					. "detail TEXT,"
					. "color char(20)"
					. ");";
	$stmt = $pdo -> query($sql);
	?>

	<center>
	<?php /* 送信ボタンによって処理分岐（追加・削除） */
	echo '<div class="top"><u>'.$mydate.'</u></div>';

	$checked = 0;
	switch($_POST['action']) {
		case 'add':
				if (empty($_POST['title'])) {
					$error = "<br>error!<br>Please enter any event title.<br>";
					$checked = 1;
				} else {
					// 各要素確保
					$title = $_POST['title'];
					$detail = $_POST['detail'];
					$color = $_POST['color'];

					$sql = $pdo -> prepare("SELECT COUNT(*) FROM events");
					$sql -> execute();
					$id = $sql->fetchColumn() + 1;

					try {
						$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
						$pdo->beginTransaction();

						// DB にデータを入れるSQL文
						$sql = $pdo -> prepare("INSERT INTO events (usermail, id, mydate,title,detail,color) VALUES (:usermail, :id, :mydate, :title, :detail, :color)");
						// パラメータ指定
						$params = array(':usermail'=>$usermail, ':id'=>$id, ':mydate'=>$mydate, ':title'=>$title, ':detail'=>$detail, ':color'=>$color);
						// SQL 実行
						$sql -> execute($params);

						$pdo->commit();
					} catch ( Exception $e ) {
					  $pdo->rollback();
						print("error<br>". $e->getMessage());
					}
				}
				break;
		case 'delete':
				// 削除対象番号 確保
				$delid = $_POST['delid'];

				// ずらし用変数 確保
				$check = 0;
				// DBの全データ確保
				$sql = 'SELECT * FROM events ORDER BY id';
				$results = $pdo -> query($sql);
				/* 処理 */
				foreach ($results as $row) {
					$id = $row['id'];
					if ($id == $delid) {
						$check = 1;
						/* 削除処理 */
						try {
							$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
							$pdo->beginTransaction();

							$sql = $pdo -> prepare("DELETE FROM events WHERE id=:delid");
							$params = array(':delid'=>$delid);
							$sql -> execute($params);

							$pdo->commit();
						} catch ( Exception $e ) {
							$pdo->rollback();
							print("error<br>". $e->getMessage());
						}
					} else if ($check == 1 && $id > $delid) {
						/* id をずらす */
						$new_id = $id - 1;
						$sql = "UPDATE events SET id = $new_id WHERE id = $id";
						$result = $pdo -> query($sql);
					}
				}
				break;
	}
	?>

	<?php
	$sql = $pdo -> prepare("SELECT * FROM events WHERE usermail=:usermail and mydate = :mydate");
	$params = array(':usermail'=>$usermail, ':mydate'=>$mydate);
	$sql -> execute($params);
	$results = $sql->fetchAll();

	if (empty($results)) {
		$e_print .= "予定なし<br>";
	} else {
		foreach ($results as $row) {
			$text = str_replace("\r\n", "<br />", $row['detail']);
			$e_print .= "<a class={$row['color']} style='font-size:x-large'>{$row['title']}</a>";
			if(!empty($text)) { $e_print .= "<div class='txtbox' align='left'>{$text}</div>"; }
			else { $e_print .= '<br>'; }
			$e_print .= <<<EOM
<form method="post" action="event.php" class="inline">
	<input type="hidden" name="date" value={$mydate}>
	<input type="hidden" name="delid" value={$row['id']}>
	<button type="submit" class="btn" name="action" value="delete">削除</button>
</form>
<form method="post" action="editevent.php" class="inline">
	<input type="hidden" name="editid" value={$row['id']}>
	<button type="submit" class="btn">編集</button>
</form>
<br><br>
EOM;
		}
	}
	?>

	<div class="hidden_box">
		<label for="hidden_label"><h1 class="circle">予定表示</h1></label>
		<input type="checkbox" id="hidden_label" class="none" checked/>
		<div class="hidden_show">
			<?php echo $e_print; ?>
		</div>
	</div>
	<div class="hidden_box">
		<label for="hidden_label2"><h1 class="circle">予定追加</h1></label>
		<input type="checkbox" id="hidden_label2" class="none" <?php if($checked==1) echo 'checked'; ?>/>
		<div class="hidden_show">
			<form method="post" action="event.php">
				<h3 class="sideline">タイトル</h3><br>
				<input type="text" class="form" name="title" placeholder="event title" value=<?php echo $_POST['title'] ?>>
				<br>
				<h3 class="sideline">詳細</h3><br>
				<textarea name="detail" class="form" cols="50" rows="10" placeholder="event detail"><?php echo $_POST['detail'] ?></textarea>
				<br>
				<h3 class="sideline">色</h3><br>
				<input type="radio" value="pink" name="color" <?php if($_POST['color']=="pink" || empty($_POST['color'])) echo "checked";?>><a class="pink">pink</a>
				<input type="radio" value="blue" name="color" <?php if($_POST['color']=="blue") echo "checked";?>><a class="blue">sky blue</a>
				<input type="radio" value="green" name="color" <?php if($_POST['color']=="green") echo "checked";?>><a class="green">lime green</a><br>
				<input type="radio" value="purple" name="color" <?php if($_POST['color']=="purple") echo "checked";?>><a class="purple">purple</a>
				<input type="radio" value="orange" name="color" <?php if($_POST['color']=="orange") echo "checked";?>><a class="orange">orange</a>
				<input type="radio" value="gray" name="color" <?php if($_POST['color']=="gray") echo "checked";?>><a class="gray">gray</a>
				<br><br><br>
				<input type="hidden" name="date" value=<?php echo $mydate;?>>
				<button type="submit" class="btn" name="action" value="add">追加</button><br><br>
			</form>
			<div style="color:red;"><?php echo $error; ?></div>
			<br>
		</div>
	</div>
	<hr>
	<form class="black">
		<a href="schedule.php" class="line_btn">HOME</a>
	</form>
	</center>
	<br><br>
</body>
</html>
