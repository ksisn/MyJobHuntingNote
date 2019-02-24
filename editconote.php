<html>
<head>
	<title>Company Note -edit</title>
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
		$name = $_POST['name'];
		if (empty($name)) { $error = "<br>error!<br>Please enter any company name.<br>"; }
		else {
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
			$editid = $_POST['editid'];

			try {
				$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$pdo->beginTransaction();

				// conotes のデータ更新
				$sql = $pdo -> prepare("UPDATE conotes SET usermail=:usermail, name=:name, hp=:hp, tel=:tel, recruiter=:recruiter, address=:address, representative=:representative, established=:established, n_employee=:n_employee, capital=:capital, profit=:profit, business_contents=:business_contents, philosophy=:philosophy, position=:position, strength=:strength, vision=:vision, ideal_candidate=:ideal_candidate, reason=:reason, mystrength=:mystrength, memo=:memo WHERE id=:editid");
				$params = array(':usermail'=>$usermail,
												':name'=>$name, ':hp'=>$hp, ':tel'=>$tel,
												':recruiter'=>$recruiter, ':address'=>$address,
												':representative'=>$representative,
												':established'=>$established, 'n_employee'=>$n_employee,
												':capital'=>$capital, ':profit'=>$profit,
												':business_contents'=>$business_contents,
												':philosophy'=>$philosophy, ':position'=>$position,
												':strength'=>$strength, ':vision'=>$vision,
												':ideal_candidate'=>$ideal_candidate,':reason'=>$reason,
												':mystrength'=>$mystrength, ':memo'=>$memo,
												':editid'=>$editid);
				$sql -> execute($params);

				$pdo->commit();
			} catch ( Exception $e ) {
				$pdo->rollback();
				print("error<br>". $e->getMessage());
			}
		}
	}

	/* 現在の情報取得（変更後も適用） */
	$editid = $_POST['editid'];

	$sql = $pdo -> prepare("SELECT * FROM conotes WHERE id = :editid");
	$params = array(':editid'=>$editid);
	$sql -> execute($params);
	$result = $sql->fetch(PDO::FETCH_ASSOC);
	?>

	<center>
	<h1 class="com_circle">企業情報 編集</h1>
	<br>
	<div style="color:red;"><?php echo $error; ?></div>
	<br>
	<h3 class="husen"><?php echo $result['name']; ?></h3><br>
	<form method="post" action="editconote.php">
	<table class="company">
		<tbody>
			<tr width="100%">
				<th width="10%" class="header">企業名</th>
				<td width="50%"><input type="text" name="name" class="nonborder" value="<?php echo $result['name']; ?>"></td>
				<th width="10%" class="header">HP</th>
				<td width="30%"><input type="text" name="hp" class="nonborder" value="<?php echo $result['hp']; ?>"></td>
			</tr>
			<tr width="100%">
				<th width="10%" class="header">電話番号</th>
				<td width="50%"><input type="text" name="tel" class="nonborder" value="<?php echo $result['tel']; ?>"></td>
				<th width="10%" class="header">採用担当</th>
				<td width="30%"><input type="text" name="recruiter" class="nonborder" value="<?php echo $result['recruiter']; ?>"></td>
			</tr>
			<tr width="100%">
				<th width="10%" class="header">住所</th>
				<td width="90%" colspan="3"><input type="text" name="address" class="nonborder" value="<?php echo $result['address']; ?>"></td>
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
				<td width="16.4%"><input type="text" name="representative" class="nonborder" value="<?php echo $result['representative']; ?>"></td>
				<td width="16.4%"><input type="text" name="established" class="nonborder" value="<?php echo $result['established']; ?>"></td>
				<td width="16.4%"><input type="text" name="n_employee" class="nonborder" value="<?php echo $result['n_employee']; ?>"></td>
				<td width="16.4%"><input type="text" name="capital" class="nonborder" value="<?php echo $result['capital']; ?>"></td>
				<td width="16.4%"><input type="text" name="profit" class="nonborder" value="<?php echo $result['profit']; ?>"></td>
			</tr>
			<tr width="100%">
				<th width="15%">事業内容</th>
				<td colspan="5"><textarea rows="3" name="business_contents" class="nonborder"><?php echo  $result['business_contents']; ?></textarea></td>
			</tr>
			<tr width="100%">
				<th width="15%">経営理念</th>
				<td colspan="5"><textarea rows="3" name="philosophy" class="nonborder"><?php echo $result['philosophy']; ?></textarea></td>
			</tr>
			<tr width="100%">
				<th width="15%">業界ポジション</th>
				<td colspan="5"><textarea rows="3" name="position" class="nonborder"><?php echo $result['position']; ?></textarea></td>
			</tr>
			<tr width="100%">
				<th width="15%">強み</th>
				<td colspan="5"><textarea rows="3" name="strength" class="nonborder"><?php echo $result['strength']; ?></textarea></td>
			</tr>
			<tr width="100%">
				<th width="15%">ビジョン<br>（今後の課題）</th>
				<td colspan="5"><textarea rows="3" name="vision" class="nonborder"><?php echo $result['vision']; ?></textarea></td>
			</tr>
			<tr width="100%">
				<th width="15%">求める人材</th>
				<td colspan="5"><textarea rows="3" name="ideal_candidate" class="nonborder"><?php echo  $result['ideal_candidate']; ?></textarea></td>
			</tr>
		</tbody>
	</table>
	<br>
	<table class="company">
		<tbody>
			<tr width="100%">
				<th width="3%" rowspan="2" class="header"><p class="vertical">自己PR</p></th>
				<th width="15%">志望理由</th>
				<td colspan="5"><textarea rows="4" name="reason" class="nonborder"><?php echo $result['reason']; ?></textarea></td>
			</tr>
			<tr width="100%">
				<th width="15%">この企業への<br>自分の強み</th>
				<td colspan="5"><textarea rows="4" name="mystrength" class="nonborder"><?php echo $result['mystrength']; ?></textarea></td>
			</tr>
		</tbody>
	</table>
	<br>
	<table class="company">
		<tbody>
			<tr width="100%">
				<th width="10%" class="header">フリーメモ</th>
				<td colspan="5"><textarea rows="3" name="memo" class="nonborder"><?php echo $result['memo']; ?></textarea></td>
			</tr>
		</tbody>
	</table>
	<input type="hidden" name="editid" value=<?php echo $editid; ?>>
	<button type="submit" name="action" value="edit" class="btn">登録</button>
	</form>
	<br>
	<hr>
	<form class="black">
		<a href="conote.php" class="line_btn">BACK</a>
	</form>
	</center>
	<br><br>
</body>
</html>
