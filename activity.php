<html>
<head>
	<title>HOME -Activity</title>
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
			<li class="current"><a href="activity.php">Activity</a></li>
			<li><a href="conote.php">Company Note</a></li>
			<li><a href="resume.php">Resume</a></li>
			<li><a href="schedule.php">Schedule</a></li>
			<li><a href="config.php">User Info</a></li>
		</ul>
	</nav>
	<br><br><br>

	<?php
	// DB接続
	$dsn = 'mysql:dbname=データベース名;host=localhost';
	$user = 'ユーザ名';
	$password = 'パスワード';
	$pdo = new PDO($dsn, $user, $password);

	/* テーブル削除
	$stmt = $pdo->prepare('show tables from データベース名 like :tblname');
	$stmt -> execute(array(':tblname' => activity));
	if ($stmt->rowCount() > 0) {
		$query = "drop table if exists ".activity;
		$pdo -> exec($query);
	}
	$stmt = $pdo->prepare('show tables from データベース名 like :tblname');
	$stmt -> execute(array(':tblname' => category));
	if ($stmt->rowCount() > 0) {
		$query = "drop table if exists ".category;
		$pdo -> exec($query);
	}
	/**/

	// DB 作成
	$sql = "CREATE TABLE if not exists activity"
					. "("
					. "usermail TEXT,"
					. "id INT,"
					. "title TEXT,"
					. "detail TEXT,"
					. "category TEXT,"
					. "mydate TEXT"
					. ");";
	$stmt = $pdo -> query($sql);
	$sql = "CREATE TABLE if not exists category"
					. "("
					. "usermail TEXT,"
					. "category TEXT"
					. ");";
	$stmt = $pdo -> query($sql);
	?>
	<?php
	$error = "";
	switch($_POST['action']) {
		case 'add':
				// 各変数確保
				$sql = $pdo -> prepare("SELECT COUNT(*) FROM activity");
				$sql -> execute();
				$id = $sql->fetchColumn() + 1;

				$title = $_POST['title'];
				$detail = $_POST['detail'];
				$category = $_POST['category'];
				$mydate = date("Y/m/d");

				$new = 0;
				if (empty($title)) { $error = "error!<br>Please enter any title.<br>"; }
				else if (empty($detail)) { $error = "error!<br>Please enter any detail.<br>"; }
				else if ($category == "new category") {
					if (empty($_POST['new_category'])) { $error = "error!<br>Please enter any new category.<br>"; }
					else {
						$category = $_POST['new_category'];
						$new = 1;

						// すでにあるカテゴリー or empty を追加しようとしてないか
						$sql = $pdo -> prepare("SELECT * FROM category WHERE usermail=:usermail and category=:category");
						$params = array(':usermail'=>$usermail, ':category'=>$category);
						$sql -> execute($params);
						$results = $sql->fetchAll();
						if (!empty($results)) { $error = "error!<br>Already exist category!<br>"; }
					}
				}

				if (empty($error)) {
					try {
						$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
						$pdo->beginTransaction();

						$sql = $pdo -> prepare("INSERT INTO activity (usermail,id,title,detail,mydate,category) VALUES (:usermail,:id,:title,:detail,:mydate, :category)");
						$params = array(':usermail'=>$usermail, ':title'=>$title, ':id'=>$id,
														':detail'=>$detail, ':mydate'=>$mydate, ':category'=>$category
														);
						$sql -> execute($params);

						$pdo->commit();

					} catch ( Exception $e ) {
						$pdo->rollback();
						print("error<br>". $e->getMessage());
					}

					if ($new == 1) {
						try {
							$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
							$pdo->beginTransaction();

							$sql = $pdo -> prepare("INSERT INTO category (usermail,category) VALUES (:usermail,:category)");
							$params = array(':usermail'=>$usermail, ':category'=>$category);
							$sql -> execute($params);

							$pdo->commit();

						} catch ( Exception $e ) {
							$pdo->rollback();
							print("error<br>". $e->getMessage());
						}
					}
					// 各変数初期化
					$title = ""; $detail = ""; $category = "";
				}
				break;
	}



	$sql = $pdo -> prepare("SELECT * FROM activity WHERE usermail=:usermail ORDER BY mydate");
	$params = array(':usermail'=>$usermail);
	$sql -> execute($params);
	$results = $sql->fetchAll();

	$sql = $pdo -> prepare("SELECT * FROM category WHERE usermail=:usermail");
	$params = array(':usermail'=>$usermail);
	$sql -> execute($params);
	$results2 = $sql->fetchAll();
	?>

		<div id="main">
			<div style="color:red;"><?php echo $error; ?></div>
			<div id="content">
				<?php
				$id = $_GET['id'];
				$cg = $_GET['cg'];
				$month = $_GET['month'];
				$num = $_GET['num'];
				/* 全て */
				if (empty($id) && empty($cg) && empty($month) && empty($num) && !empty($results)) {
					echo '<h2 class="head">All Activity</h2>';
					$print = "";
					foreach($results as $value) {
						$print .= <<<EOM
<div>
<a href="http://サーバURL/activity.php?id={$value['id']}" style="text-decoration:none;"><h3 class="title">{$value['title']}</h3></a>
<a class="sub">{$value['mydate']}</a>
<a class="cg"><u>{$value['category']}</u></a><br>
<div class="body">{$value['detail']}</div>
</div>
<br><br>
EOM;
					}
					echo $print;

				/* 月 指定 */
				} else if (!empty($month) && !empty($num) && !empty($results)) {
					echo '<h2 class="head">'.$month.'</h2>';
					$print = "";
					foreach($results as $value) {
						if ($month == substr($value['mydate'], 0, -3)) {
							$print .= <<<EOM
<div>
<a href="http://サーバURL/activity.php?id={$value['id']}" style="text-decoration:none;"><h3 class="title">{$value['title']}</h3></a>
<a class="sub">{$value['mydate']}</a>
<a class="cg"><u>{$value['category']}</u></a><br>
<div class="body">{$value['detail']}</div>
</div>
<br><br>
EOM;
						}
					}
						echo $print;

				/* カテゴリー指定 */
				} else if (!empty($cg) && !empty($results)) {
					echo '<h2 class="head">'.$cg."</h2>";
					foreach($results as $value) {
						if ($cg == $value['category']) {
							echo <<<EOM
<div>
<a href="http://サーバURL/activity.php?id={$value['id']}" style="text-decoration:none;"><h3 class="title">{$value['title']}</h3></a>
<a class="sub">{$value['mydate']}</a>
<a class="cg"><u>{$value['category']}</u></a><br>
<div class="body">{$value['detail']}</div>
</div>
<br><br>
EOM;
						}
					}

				/* 指定記事 */
				} else if (!empty($results)) {
					foreach($results as $value) {
						if ($id == $value['id']) {
							$text = str_replace("\r\n", "<br />", $value['detail']);
							echo <<<EOM
<div>
<h2 class="title">{$value['title']}</h2>
<a class="sub">{$value['mydate']}</a>
<a class="cg"><u>{$value['category']}</u></a><br>
<div class="text">{$text}</div>
</div>
<br><br>
EOM;
						}
					}
				}
				?>
			</div>
			<hr>
			<div id="content">
				<a name="add"><h3>Add new Activity</h3></a>
				<form method="post">
					<input type="text" name="title" class="form" value="<?php echo $title; ?>" placeholder="title"/><br>
					<textarea rows="4" name="detail" class="txtbox" placeholder="detail"><?php echo $detail; ?></textarea><br>
					<select name="category">
						<?php
						$print = "<option value='new category' selected>new category</option>\n";
						$count = 0;
						foreach($results2 as $value) {
							$print .= "<option value=".$value['category'].">".$value['category']."</option>\n";
						}
						echo $print;
						?>
					</select>
					<input type="text" name="new_category" class="form" placeholder="new category"/><br>
					<button type="submit" name="action" value="add" class="btn">登録</button>
				</form>
			</div>
		</div>

		<div id="sidebar">
			<div id="content">
				<a href="#add"><h4>Add new Activity</h4></a>
			</div>
			<div id="content">
				<h4>Past Activity</h4>
				<?php
				if (!empty($results)) {
					$month = array();
					foreach($results as $value) {
						$month[substr($value['mydate'], 0, -3)] += 1;
					}
					foreach($month as $key=>$value) {
						echo "<li><a href="."'http://サーバURL/activity.php?month=".$key."&num=".$value."'>";
						echo $key.' ('.$value.')'."</a></li>\n";
					}
				}
				?>
			</div>
			<div id="content">
				<h4>Category</h4>
				<?php
				if (!empty($results2)) {
					foreach($results2 as $value) {
						echo "<li><a href="."'http://サーバURL/activity.php?cg=".$value['category']."'>";
						echo $value['category']."</a></li>\n";
					}
				}
				?>
			</div>
		</div>
	<br>
	<br>
	<br>
</body>
</html>
