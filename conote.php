<html>
<head>
	<title>HOME -Company Note</title>
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

	<nav>
		<ul>
			<li><a href="activity.php">Activity</a></li>
			<li class="current"><a href="conote.php">Company Note</a></li>
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

	// make TABLE（conotes: ユーザ別企業研究情報）
	$sql = "CREATE TABLE if not exists conotes"
					. "("
					. "usermail TEXT,"
					. "id INT,"
					. "name TEXT,"							// 企業名
					. "hp TEXT,"								// HP URL
					. "tel TEXT,"								// 電話番号
					. "recruiter TEXT,"					// 採用担当
					. "address TEXT,"						// 住所
					. "representative TEXT,"		// 代表者
					. "established TEXT,"				// 設立年
					. "n_employee TEXT,"				// 従業員数
					. "capital TEXT,"						// 資本金
					. "profit TEXT,"						// 売上高
					. "business_contents TEXT,"	// 事業内容
					. "philosophy TEXT,"				// 経営理念
					. "position TEXT,"					// 業界ポジション
					. "strength TEXT,"					// 強み
					. "vision TEXT,"						// ビジョン（今後の課題）
					. "ideal_candidate TEXT,"		// 求める人材
					. "reason TEXT,"						// 志望理由
					. "mystrength TEXT,"				// この企業へアピールできる自分の強み
					. "memo TEXT"								// フリーメモ
					. ");";
	$stmt = $pdo -> query($sql);


	?>
	<center>
	<h1 class="miniunder">企業情報一覧</h1><br>
	<?php
	$error = "";
	$checked = 0;
	switch($_POST['action']) {
		case 'add':
				// 各変数確保
				$sql = $pdo -> prepare("SELECT COUNT(*) FROM conotes");
				$sql -> execute();
				$id = $sql->fetchColumn() + 1;

				$name = $_POST['name'];
				$hp = $_POST['hp'];
				$tel = $_POST['tel'];
				$recruiter = $_POST['recruiter'];
				$address = $_POST['address'];
				$representative = $_POST['representative'];
				$established = $_POST['established'];
				$n_employee = $_POST['n_employee'];
				$capital = $_POST['capital'];
				$profit = $_POST['profit'];
				$business_contents = $_POST['business_contents'];
				$philosophy = $_POST['philosophy'];
				$position = $_POST['position'];
				$strength = $_POST['strength'];
				$vision = $_POST['vision'];
				$ideal_candidate = $_POST['ideal_candidate'];
				$reason = $_POST['reason'];
				$mystrength = $_POST['mystrength'];
				$memo = $_POST['memo'];

				/* 追加処理 */
				if (empty($name)) {
					$error = "<br>error!<br>Please enter any company name.<br>";
					$checked = 1;
				} else {
					try {
						$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
						$pdo->beginTransaction();

						// conotes にデータを入れる
						$sql = $pdo -> prepare("INSERT INTO conotes  (usermail,id,name,hp,tel,recruiter,address,representative,established,n_employee,capital,profit,business_contents,philosophy,position,strength,vision,ideal_candidate,reason,mystrength,memo) VALUES (:usermail,:id,:name,:hp,:tel,:recruiter,:address,:representative,:established,:n_employee,:capital,:profit,:business_contents,:philosophy,:position,:strength,:vision,:ideal_candidate,:reason,:mystrength,:memo)");
						$params = array(':usermail'=>$usermail, ':id'=>$id,
														':name'=>$name, ':hp'=>$hp, ':tel'=>$tel,
														':recruiter'=>$recruiter, ':address'=>$address,
														':representative'=>$representative,
														':established'=>$established, ':n_employee'=>$n_employee,
														':capital'=>$capital, ':profit'=>$profit,
														':business_contents'=>$business_contents,
														':philosophy'=>$philosophy, ':position'=>$position,
														':strength'=>$strength, ':vision'=>$vision,
														':ideal_candidate'=>$ideal_candidate,':reason'=>$reason,
														':mystrength'=>$mystrength, ':memo'=>$memo);
						$sql -> execute($params);

						$pdo->commit();

						// 各変数初期化
						$name = ""; $hp = ""; $tel = ""; $recruiter = ""; $address = "";
						$representative = ""; $established = ""; $n_employee = "";
						$capital = ""; $profit = ""; $business_contents = "";
						$philosophy = ""; $position = ""; $strength = ""; $vision = "";
						$ideal_candidate = ""; $reason = ""; $mystrength = "";
						$memo = "";
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
				// conotes の全データ確保
				$sql = 'SELECT * FROM conotes ORDER BY id';
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

							$sql = $pdo -> prepare("DELETE FROM conotes WHERE id=:delid");
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
						$sql = "UPDATE conotes SET id = $new_id WHERE id = $id";
						$result = $pdo -> query($sql);
					}
				}
				break;
	}


	$sql = $pdo -> prepare("SELECT * FROM conotes WHERE usermail=:usermail ORDER BY id");
	$params = array(':usermail'=>$usermail);
	$sql -> execute($params);
	$results = $sql->fetchAll();

	// 全企業研究表示
	if (empty($results)) {
		$e_print .= "企業登録なし<br>";
	} else {
		foreach ($results as $row) {
			// 改行反映
			$business_contents2 = str_replace("\r\n", "<br />", $row['business_contents']);
			$philosophy2 = str_replace("\r\n", "<br />", $row['philosophy']);
			$position2 = str_replace("\r\n", "<br />", $row['position']);
			$strength2 = str_replace("\r\n", "<br />", $row['strength']);
			$vision2 = str_replace("\r\n", "<br />", $row['vision']);
			$ideal_candidate2 = str_replace("\r\n", "<br />", $row['ideal_candidate']);
			$reason2 = str_replace("\r\n", "<br />", $row['reason']);
			$mystrength2 = str_replace("\r\n", "<br />", $row['mystrength']);
			$memo2 = str_replace("\r\n", "<br />", $row['memo']);
			// 表示
			$e_print .= <<<EOM
<div class="hidden_box">
	<label for={$row['id']}><h3 class="husen">{$row['name']}</h3></label>
	<input type="checkbox" id={$row['id']} class="none"></input>
	<div class="hidden_show">
		<table class="company">
			<tbody>
				<tr width="100%">
					<th width="10%" class="header">企業名</th>
					<td width="50%">{$row['name']}</td>
					<th width="10%" class="header">HP</th>
					<td width="30%"><a href={$row['hp']} target="_blank">{$row['hp']}</a></td>
				</tr>
				<tr width="100%">
					<th width="10%" class="header">電話番号</th>
					<td width="50%">{$row['tel']}</td>
					<th width="10%" class="header">採用担当</th>
					<td width="30%">{$row['recruiter']}</td>
				</tr>
				<tr width="100%">
					<th width="10%" class="header">住所</th>
					<td width="90%" colspan="3">{$row['address']}</td>
				</tr>
			</tbody>
		</table>
		<br>
		<table class="company">
			<tbody>
				<tr width="100%">
					<th width="3%" rowspan="8" class="header"><p class="vertical">企業データ</p></th>
					<th width="15%" rowspan="2">基本情報</th>
					<th width="16.4%" class="mini">代表者名</th>
					<th width="16.4%" class="mini">設立</th>
					<th width="16.4%" class="mini">従業員</th>
					<th width="16.4%" class="mini">資本金</th>
					<th width="16.4%" class="mini">売上高</th>
				</tr>
				<tr width="100%">
					<td width="16.4%">{$row['representative']}</td>
					<td width="16.4%">{$row['established']}</td>
					<td width="16.4%">{$row['n_employee']}</td>
					<td width="16.4%">{$row['capital']}</td>
					<td width="16.4%">{$row['profit']}</td>
				</tr>
				<tr width="100%">
					<th width="15%">事業内容</th>
					<td colspan="5">{$business_contents2}</td>
				</tr>
				<tr width="100%">
					<th width="15%">経営理念</th>
					<td colspan="5">{$philosophy2}</td>
				</tr>
				<tr width="100%">
					<th width="15%">業界ポジション</th>
					<td colspan="5">{$position2}</td>
				</tr>
				<tr width="100%">
					<th width="15%">強み</th>
					<td colspan="5">{$strength2}</td>
				</tr>
				<tr width="100%">
					<th width="15%">ビジョン<br>（今後の課題）</th>
					<td colspan="5">{$vision2}</td>
				</tr>
				<tr width="100%">
					<th width="15%">求める人材</th>
					<td colspan="5">{$ideal_candidate2}</td>
				</tr>
			</tbody>
		</table>
		<br>
		<table class="company">
			<tbody>
				<tr width="100%">
					<th width="3%" rowspan="2" class="header"><p class="vertical">自己PR</p></th>
					<th width="15%">志望理由</th>
					<td colspan="5">{$reason2}</td>
				</tr>
				<tr width="100%">
					<th width="15%">この企業への<br>自分の強み</th>
					<td colspan="5">{$mystrength2}</td>
				</tr>
			</tbody>
		</table>
		<br>
		<table class="company">
			<tbody>
				<tr width="100%">
					<th width="10%" class="header">フリーメモ</th>
					<td colspan="5">{$memo2}</td>
				</tr>
			</tbody>
		</table>
		<form method="post" action="conote.php" class="inline">
			<input type="hidden" name="delid" value={$row['id']}>
			<button type="submit" class="btn" name="action" value="delete">削除</button>
		</form>
		<form method="post" action="editconote.php" class="inline">
			<input type="hidden" name="editid" value={$row['id']}>
			<button type="submit" class="btn">編集</button>
		</form>
	</div>
</div>
EOM;
		}
	}
	echo $e_print;
	?>
	<br>
	<div class="hidden_box">
		<label for="hidden_label"><h1 class="miniunder">企業追加</h1></label>
		<input type="checkbox" id="hidden_label" class="none" <?php if($checked==1) echo 'checked'; ?>></input>
		<div class="hidden_show">
			<div style="color:red;"><?php echo $error; ?></div>
			<br>
			<form method="post">
			<table class="company">
				<tbody>
					<tr width="100%">
						<th width="10%" class="header">企業名</th>
						<td width="50%"><input type="text" name="name" class="nonborder" value="<?php echo $name; ?>"></td>
						<th width="10%" class="header">HP</th>
						<td width="30%"><input type="text" name="hp" class="nonborder" value="<?php echo $hp; ?>"></td>
					</tr>
					<tr width="100%">
						<th width="10%" class="header">電話番号</th>
						<td width="50%"><input type="text" name="tel" class="nonborder" value="<?php echo $tel; ?>"></td>
						<th width="10%" class="header">採用担当</th>
						<td width="30%"><input type="text" name="recruiter" class="nonborder" value="<?php echo $recruiter; ?>"></td>
					</tr>
					<tr width="100%">
						<th width="10%" class="header">住所</th>
						<td width="90%" colspan="3"><input type="text" name="address" class="nonborder" value="<?php echo $address; ?>"></td>
					</tr>
				</tbody>
			</table>
			<br>
			<table class="company">
				<tbody>
					<tr width="100%">
						<th width="3%" rowspan="8" class="header"><p class="vertical">企業データ</p></th>
						<th width="15%" rowspan="2">基本情報</th>
						<th width="16.4%" class="mini">代表者名</th>
						<th width="16.4%" class="mini">設立</th>
						<th width="16.4%" class="mini">従業員</th>
						<th width="16.4%" class="mini">資本金</th>
						<th width="16.4%" class="mini">売上高</th>
					</tr>
					<tr width="100%">
						<td width="16.4%"><input type="text" name="representative" class="nonborder" value="<?php echo $representative; ?>"></td>
						<td width="16.4%"><input type="text" name="established" class="nonborder" value="<?php echo $established; ?>"></td>
						<td width="16.4%"><input type="text" name="n_employee" class="nonborder" value="<?php echo $n_employee; ?>"></td>
						<td width="16.4%"><input type="text" name="capital" class="nonborder" value="<?php echo $capital; ?>"></td>
						<td width="16.4%"><input type="text" name="profit" class="nonborder" value="<?php echo $profit; ?>"></td>
					</tr>
					<tr width="100%">
						<th width="15%">事業内容</th>
						<td colspan="5"><textarea rows="3" name="business_contents" class="nonborder"><?php echo  $business_contents; ?></textarea></td>
					</tr>
					<tr width="100%">
						<th width="15%">経営理念</th>
						<td colspan="5"><textarea rows="3" name="philosophy" class="nonborder"><?php echo $philosophy; ?></textarea></td>
					</tr>
					<tr width="100%">
						<th width="15%">業界ポジション</th>
						<td colspan="5"><textarea rows="3" name="position" class="nonborder"><?php echo $position; ?></textarea></td>
					</tr>
					<tr width="100%">
						<th width="15%">強み</th>
						<td colspan="5"><textarea rows="3" name="strength" class="nonborder"><?php echo $strength; ?></textarea></td>
					</tr>
					<tr width="100%">
						<th width="15%">ビジョン<br>（今後の課題）</th>
						<td colspan="5"><textarea rows="3" name="vision" class="nonborder"><?php echo $vision; ?></textarea></td>
					</tr>
					<tr width="100%">
						<th width="15%">求める人材</th>
						<td colspan="5"><textarea rows="3" name="ideal_candidate" class="nonborder"><?php echo  $ideal_candidate; ?></textarea></td>
					</tr>
				</tbody>
			</table>
			<br>
			<table class="company">
				<tbody>
					<tr width="100%">
						<th width="3%" rowspan="2" class="header"><p class="vertical">自己PR</p></th>
						<th width="15%">志望理由</th>
						<td colspan="5"><textarea rows="4" name="reason" class="nonborder"><?php echo $reason; ?></textarea></td>
					</tr>
					<tr width="100%">
						<th width="15%">この企業への<br>自分の強み</th>
						<td colspan="5"><textarea rows="4" name="mystrength" class="nonborder"><?php echo $mystrength; ?></textarea></td>
					</tr>
				</tbody>
			</table>
			<br>
			<table class="company">
				<tbody>
					<tr width="100%">
						<th width="10%" class="header">フリーメモ</th>
						<td colspan="5"><textarea rows="3" name="memo" class="nonborder"><?php echo $memo; ?></textarea></td>
					</tr>
				</tbody>
			</table>
			<button type="submit" name="action" value="add" class="btn">登録</button>
			</form>
		</div>
	</div>
	</center>
</body>
</html>
