<html>
<head>
	<title>HOME -Resume</title>
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
			<li class="current"><a href="resume.php">Resume</a></li>
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
	$stmt -> execute(array(':tblname' => resume_main));
	if ($stmt->rowCount() > 0) {
		$query = "drop table if exists ".resume_main;
		$pdo -> exec($query);
	}
	$stmt = $pdo->prepare('show tables from データベース名 like :tblname');
	$stmt -> execute(array(':tblname' => resume_education));
	if ($stmt->rowCount() > 0) {
		$query = "drop table if exists ".resume_education;
		$pdo -> exec($query);
	}
	/**/

	// DB 作成
	$sql = "CREATE TABLE if not exists resume_main"
					. "("
					. "usermail TEXT,"
					. "name TEXT,"							// 名前
					. "n_kana TEXT,"						// ふりがな（名前）
					. "sex char(5),"						// 性別
					. "birth TEXT,"							// 生年月日（年齢）
					. "now_address TEXT,"				// 現住所
					. "na_kana TEXT,"						// ふりがな（現住所）
					. "na_tel TEXT,"						// 電話番号（現住所）
					. "parents_home TEXT,"			// 帰省先
					. "ph_kana TEXT,"						// ふりがな（帰省先）
					. "ph_tel TEXT,"						// 電話番号（帰省先）
					. "mail TEXT,"							// メールアドレス
					. "hobby TEXT,"							// 趣味
					. "qualification TEXT,"			// 資格・免許
					. "good_subject TEXT,"			// 得意な科目
					. "theme TEXT,"							// 卒研テーマ
					. "detail TEXT,"						// 卒研内容
					. "myexperience TEXT,"			// 学生時代に力を入れたこと
					. "mystrength TEXT,"				// 長所
					. "myweekness TEXT,"				// 短所
					. "other TEXT"							// その他メモ
					. ");";
	$stmt = $pdo -> query($sql);
	$sql = "CREATE TABLE if not exists resume_education"
					. "("
					. "usermail TEXT,"
					. "id INT,"									// 日付順に取り出すためのID
					. "era TEXT,"								// 年号
					. "year char(5),"								// 年
					. "month char(5),"							// 月
					. "mytext TEXT"								// 学歴・職歴
					. ");";
	$stmt = $pdo -> query($sql);

	$first = array(0, 0);
	$sql = $pdo -> prepare("SELECT * FROM resume_main WHERE usermail=:usermail");
	$params = array(':usermail'=>$usermail);
	$sql -> execute($params);
	$results = $sql->fetch();
	if (empty($results)) { $first[0] = 1; }

	$sql = $pdo -> prepare("SELECT * FROM resume_education WHERE usermail=:usermail");
	$params = array(':usermail'=>$usermail);
	$sql -> execute($params);
	$results = $sql->fetchAll();
	if (empty($results)) { $first[1] = 1; }
	?>
	<center>
	<h1 class="resume">履歴書メモ</h1><br>
	<?php
	$checked = 0;
	$filename = "photo.png";
	$path = '写真をアップロードするサーバ上のファイルのパス';
	switch($_POST['action']) {
		case 'edit':
				// 各変数確保
				$name = $_POST['name'];
				$n_kana = $_POST['n_kana'];
				$sex = $_POST['sex'];
				$birth = $_POST['birth'];
				$now_address = $_POST['now_address'];
				$na_kana = $_POST['na_kana'];
				$na_tel = $_POST['na_tel'];
				$parents_home = $_POST['parents_home'];
				$ph_kana = $_POST['ph_kana'];
				$ph_tel = $_POST['ph_tel'];
				$mail = $_POST['mail'];
				$hobby = $_POST['hobby'];
				$qualification = $_POST['qualification'];
				$good_subject = $_POST['good_subject'];
				$theme = $_POST['theme'];
				$detail = $_POST['detail'];
				$myexperience = $_POST['myexperience'];
				$mystrength = $_POST['mystrength'];
				$myweekness = $_POST['myweekness'];
				$other = $_POST['other'];
				// パラメータ指定
				$params = array(':usermail'=>$usermail,
												':name'=>$name, ':n_kana'=>$n_kana,
												':sex'=>$sex, ':birth'=>$birth,
												':now_address'=>$now_address,
												':na_kana'=>$na_kana, ':na_tel'=>$na_tel,
												':parents_home'=>$parents_home,
												':ph_kana'=>$ph_kana, ':ph_tel'=>$ph_tel,
												':mail'=>$mail, ':hobby'=>$hobby,
												':qualification'=>$qualification,
												':good_subject'=>$good_subject,
												':theme'=>$theme, ':detail'=>$detail,
												':myexperience'=>$myexperience,
												':mystrength'=>$mystrength,
												':myweekness'=>$myweekness,
												':other'=>$other);

				/* resume_main */
				if ($first[0] == 1) {
					try {
						$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
						$pdo->beginTransaction();

						$sql = $pdo -> prepare("INSERT INTO resume_main  (usermail,name,n_kana,sex,birth,now_address,na_kana,na_tel,parents_home,ph_kana,ph_tel,mail,hobby,qualification,good_subject,theme,detail,myexperience,mystrength,myweekness,other) VALUES  (:usermail,:name,:n_kana,:sex,:birth,:now_address,:na_kana,:na_tel,:parents_home,:ph_kana,:ph_tel,:mail,:hobby,:qualification,:good_subject,:theme,:detail,:myexperience,:mystrength,:myweekness,:other)");
						$sql -> execute($params);

						$pdo->commit();
					} catch ( Exception $e ) {
						$pdo->rollback();
						print("error<br>". $e->getMessage());
					}
				} else {
					try {
						$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
						$pdo->beginTransaction();

						$sql = $pdo -> prepare("UPDATE resume_main SET name=:name, n_kana=:n_kana, sex=:sex, birth=:birth, now_address=:now_address, na_kana=:na_kana, na_tel=:na_tel, parents_home=:parents_home, ph_kana=:ph_kana, ph_tel=:ph_tel, mail=:mail, hobby=:hobby, qualification=:qualification, good_subject=:good_subject, theme=:theme, detail=:detail, myexperience=:myexperience, mystrength=:mystrength, myweekness=:myweekness, other=:other WHERE usermail=:usermail");
						$sql -> execute($params);

						$pdo->commit();
					} catch ( Exception $e ) {
						$pdo->rollback();
						print("error<br>". $e->getMessage());
					}
				}

				/* resume_education */
				for ($id = 0; $id < 13; $id++) {
					$era[$id] = $_POST['era'.$id];
					$year[$id] = $_POST['year'.$id];
					$month[$id] = $_POST['month'.$id];
					$mytext[$id] = $_POST['mytext'.$id];
					// パラメータ指定
					$params = array(':usermail'=>$usermail, ':id'=>$id,
													':era'=>$era[$id], ':year'=>$year[$id],
													':month'=>$month[$id], ':mytext'=>$mytext[$id]
													);

					if ($first[1] == 1) {
						try {
							$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
							$pdo->beginTransaction();

							$sql = $pdo -> prepare("INSERT INTO resume_education (usermail,id,era,year,month,mytext) VALUES  (:usermail,:id,:era,:year,:month,:mytext)");
							$sql -> execute($params);

							$pdo->commit();
						} catch ( Exception $e ) {
							$pdo->rollback();
							print("error<br>". $e->getMessage());
						}
					} else {
						try {
							$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
							$pdo->beginTransaction();

							// DB にデータを入れるSQL文
							$sql = $pdo -> prepare("UPDATE resume_education SET era=:era, year=:year, month=:month, mytext=:mytext WHERE usermail=:usermail and id=:id");
							// SQL 実行
							$sql -> execute($params);

							$pdo->commit();
						} catch ( Exception $e ) {
							$pdo->rollback();
							print("error<br>". $e->getMessage());
						}
					}
				}

				/* 画像のアップロード */
				$error = "";
				if (is_uploaded_file($_FILES["upfile"]["tmp_name"])) {
					$check = move_uploaded_file ($_FILES["upfile"]["tmp_name"], $path . $filename);
					if ($check) { chmod($path . $filename, 0644); }
					else {
						$error = "ファイルをアップロードできません。";
						$checked = 1;
					}
				}

				break;
	}

	$sql = $pdo -> prepare("SELECT * FROM resume_main WHERE usermail=:usermail");
	$params = array(':usermail'=>$usermail);
	$sql -> execute($params);
	$results1 = $sql->fetch();

	$sql = $pdo -> prepare("SELECT * FROM resume_education WHERE usermail=:usermail ORDER BY id");
	$params = array(':usermail'=>$usermail);
	$sql -> execute($params);
	$results2 = $sql->fetchAll();
	?>
	<table class="resume">
		<tbody>
			<tr width="100%">
				<th width="10%" class="h_mini">ふりがな</th>
				<td width="50%" class="d_mini"><?php echo $results1['n_kana']; ?></td>
				<th width="10%" class="h_mini">性別</th>
				<td width="30%" class="photo" rowspan="3" colspan="2">
					<img src="<?php echo $filename; ?>" alt="photo" width="120" height="150">
				</td>
			</tr>
			<tr width="100%">
				<th width="10%" class="h_big">氏名</th>
				<td width="50%" class="d_big"><?php echo $results1['name']; ?></td>
				<td width="10%" class="d_medium"><?php echo $results1['sex']; ?></td>
			</tr>
			<tr width="100%">
				<th width="10%" class="h_medium">生年月日</th>
				<td width="60%" colspan="2" class="d_medium"><?php echo $results1['birth']; ?></td>
			</tr>
			<tr width="100%">
				<th width="10%" class="h_mini">ふりがな</th>
				<td width="70%" colspan="3" class="d_mini"><?php echo $results1['na_kana']; ?></td>
				<th width="20%" class="h_mini">電話</th>
			</tr>
			<tr width="100%">
				<th width="10%" class="h_medium">現住所</th>
				<td width="70%" colspan="3" class="d_medium"><?php echo str_replace("\r\n", "<br />", $results1['now_address']); ?></td>
				<td width="20%" class="d_medium"><?php echo $results1['na_tel']; ?></td>
			</tr>
			<tr width="100%">
				<th width="10%" class="h_mini">ふりがな</th>
				<td width="70%" colspan="3" class="d_mini"><?php echo $results1['ph_kana']; ?></td>
				<th width="20%" class="h_mini">電話</th>
			</tr>
			<tr width="100%">
				<th width="10%" class="h_medium">帰省先</th>
				<td width="70%" colspan="3" class="d_medium"><?php echo str_replace("\r\n", "<br />", $results1['parents_home']); ?></td>
				<td width="20%" class="d_medium"><?php echo $results1['ph_tel']; ?></td>
			</tr>
			<tr width="100%">
				<th width="10%" class="h_medium">メール</th>
				<td width="90%" colspan="4" class="d_medium"><?php echo $results1['mail']; ?></td>
			</tr>
		</tbody>
	</table>
	<br>
	<table class="resume">
		<tbody>
			<tr width="100%">
				<th width="5%" class="h_medium">年号</th>
				<th width="5%" class="h_medium">年</th>
				<th width="5%" class="h_medium">月</th>
				<th width="85%" class="h_medium">学歴・職歴</th>
			</tr>
			<?php
			$print = "";
			$tmpid = 0;
			foreach ($results2 as $value) {
				$print .= <<<EOM
<tr width="100%">
<td width="5%" class="d_medium">{$value['era']}&nbsp;</td>
<td width="5%" class="d_medium">{$value['year']}&nbsp;</td>
<td width="5%" class="d_medium">{$value['month']}&nbsp;</td>
<td width="85%" class="d_medium">{$value['mytext']}&nbsp;</td>
</tr>
EOM;
				$tmpid++;
			}
			for(; $tmpid < 13; $tmpid++) {
				$tmpera = "era".$tmpid;
				$tmpyear = "year".$tmpid;
				$tmpmonth = "month".$tmpid;
				$tmptext = "mytext".$tmpid;
				$print .= <<<EOM
<tr width="100%">
<td width="5%" class="d_medium">&nbsp;</td>
<td width="5%" class="d_medium">&nbsp;</td>
<td width="5%" class="d_medium">&nbsp;</td>
<td width="85%" class="d_medium">&nbsp;</td>
</tr>
EOM;
			}
			echo $print;
			?>
		</tbody>
	</table>
	<br>
	<table class="resume">
		<tbody>
			<tr width="100%">
				<th width="20%" class="h_medium">趣味</th>
				<td width="80%" class="d_medium"><?php echo str_replace("\r\n", "<br />", $results1['hobby']); ?></td>
			</tr>
			<tr width="100%">
				<th width="20%" class="h_medium">資格・免許 等</th>
				<td width="80%" class="d_medium"><?php echo str_replace("\r\n", "<br />", $results1['qualification']); ?></td>
			</tr>
			<tr width="100%">
				<th width="20%" class="h_medium">得意な科目</th>
				<td width="80%" class="d_medium"><?php echo $results1['good_subject']; ?></td>
			</tr>
			<tr width="100%">
				<th width="20%" class="h_medium">[卒業研究]テーマ</th>
				<td width="80%" class="d_medium"><?php echo $results1['theme']; ?></td>
			</tr>
			<tr width="100%">
				<th width="20%" class="h_medium">[卒業研究]内容</th>
				<td width="80%" class="d_medium"><?php echo str_replace("\r\n", "<br />", $results1['detail']); ?></td>
			</tr>
			<tr width="100%">
				<th width="20%" class="h_medium">学生時代力を入れたこと</th>
				<td width="80%" class="d_medium"><?php echo str_replace("\r\n", "<br />", $results1['myexperience']); ?></td>
			</tr>
			<tr width="100%">
				<th width="20%" class="h_medium">長所</th>
				<td width="80%" class="d_medium"><?php echo $results1['mystrength']; ?></td>
			</tr>
			<tr width="100%">
				<th width="20%" class="h_medium">短所</th>
				<td width="80%" class="d_medium"><?php echo $results1['myweekness']; ?></td>
			</tr>
		</tbody>
	</table>
	<br>
	<table class="resume">
		<tbody>
			<tr width="100%">
				<th width="20%" class="h_medium">その他メモ</th>
				<td width="80%" class="d_medium"><?php echo str_replace("\r\n", "<br />", $results1['other']); ?></td>
			</tr>
		</tbody>
	</table>

	<br>

	<div class="hidden_box">
		<label for="hidden_label"><h1 class="bracket">編集</h1></label>
		<input type="checkbox" id="hidden_label" class="none" <?php if($checked==1) echo 'checked'; ?>></input>
		<div class="hidden_show">
			<div style="color:red;"><?php echo $error; ?></div>
			<br>
			<form method="post" enctype="multipart/form-data">
			<table class="resume">
				<tbody>
					<tr width="100%">
						<th width="10%" class="h_mini">ふりがな</th>
						<td width="50%"><input type="text" name="n_kana" class="mini" value="<?php echo $results1['n_kana']; ?>"></td>
						<th width="10%" class="h_mini">性別</th>
						<td width="30%" class="photo" rowspan="3" colspan="2">
							<label for="upload">画像アップロード</label>
							<input type="file" name="upfile" size="30" id="upload">
						</td>
					</tr>
					<tr width="100%">
						<th width="10%" class="h_big">氏名</th>
						<td width="50%"><input type="text" name="name" class="big" value="<?php echo $results1['name']; ?>"></td>
						<td width="10%"><input type="text" name="sex" class="medium" value="<?php echo $results1['sex']; ?>"></td>
					</tr>
					<tr width="100%">
						<th width="10%" class="h_medium">生年月日</th>
						<td width="60%" colspan="2"><input type="text" name="birth" class="medium" value="<?php echo $results1['birth']; ?>"></td>
					</tr>
					<tr width="100%">
						<th width="10%" class="h_mini">ふりがな</th>
						<td width="70%" colspan="3"><input type="text" name="na_kana" class="mini" value="<?php echo $results1['na_kana']; ?>"></td>
						<th width="20%" class="h_mini">電話</th>
					</tr>
					<tr width="100%">
						<th width="10%" class="h_medium">現住所</th>
						<td width="70%" colspan="3"><textarea rows="2" name="now_address" class="medium"><?php echo $results1['now_address']; ?></textarea></td>
						<td width="20%"><input type="text" name="na_tel" class="medium" value="<?php echo $results1['na_tel']; ?>"></td>
					</tr>
					<tr width="100%">
						<th width="10%" class="h_mini">ふりがな</th>
						<td width="70%" colspan="3"><input type="text" name="ph_kana" class="mini" value="<?php echo $results1['ph_kana']; ?>"></td>
						<th width="20%" class="h_mini">電話</th>
					</tr>
					<tr width="100%">
						<th width="10%" class="h_medium">帰省先</th>
						<td width="70%" colspan="3"><textarea rows="2" name="parents_home" class="medium"><?php echo $results1['parents_home']; ?></textarea></td>
						<td width="20%"><input type="text" name="ph_tel" class="medium" value="<?php echo $results1['ph_tel']; ?>"></td>
					</tr>
					<tr width="100%">
						<th width="10%" class="h_medium">メール</th>
						<td width="90%" colspan="4"><input type="text" name="mail" class="medium" value="<?php echo $results1['mail']; ?>"></td>
					</tr>
				</tbody>
			</table>
			<br>
			<table class="resume">
				<tbody>
					<tr width="100%">
						<th width="5%" class="h_medium">年号</th>
						<th width="5%" class="h_medium">年</th>
						<th width="5%" class="h_medium">月</th>
						<th width="85%" class="h_medium">学歴・職歴</th>
					</tr>
					<?php
					$print = "";
					$tmpid = 0;
					foreach ($results2 as $value) {
						$tmpera = "era".$tmpid;
						$tmpyear = "year".$tmpid;
						$tmpmonth = "month".$tmpid;
						$tmptext = "mytext".$tmpid;
						$print .= <<<EOM
<tr width="100%">
	<td width="5%"><input type="text" name={$tmpera} class="medium" value={$value['era']}></td>
	<td width="5%"><input type="text" name={$tmpyear} class="medium" value={$value['year']}></td>
	<td width="5%"><input type="text" name={$tmpmonth} class="medium" value={$value['month']}></td>
	<td width="85%"><input type="text" name={$tmptext} class="medium" value={$value['mytext']}></td>
</tr>
EOM;
						$tmpid++;
					}
					for(; $tmpid < 13; $tmpid++) {
						$tmpera = "era".$tmpid;
						$tmpyear = "year".$tmpid;
						$tmpmonth = "month".$tmpid;
						$tmptext = "mytext".$tmpid;
						$print .= <<<EOM
<tr width="100%">
	<td width="5%"><input type="text" name={$tmpera} class="medium"></td>
	<td width="5%"><input type="text" name={$tmpyear} class="medium"></td>
	<td width="5%"><input type="text" name={$tmpmonth} class="medium"></td>
	<td width="85%"><input type="text" name={$tmptext} class="medium"></td>
</tr>
EOM;
					}
					echo $print;
					?>
				</tbody>
			</table>
			<br>
			<table class="resume">
				<tbody>
					<tr width="100%">
						<th width="20%" class="h_medium">趣味</th>
						<td width="80%"><textarea rows="2" name="hobby" class="medium"><?php echo $results1['hobby']; ?></textarea></td>
					</tr>
					<tr width="100%">
						<th width="20%" class="h_medium">資格・免許 等</th>
						<td width="80%"><textarea rows="2" name="qualification" class="medium"><?php echo $results1['qualification']; ?></textarea></td>
					</tr>
					<tr width="100%">
						<th width="20%" class="h_medium">得意な科目</th>
						<td width="80%"><input type="text" name="good_subject" class="medium" value="<?php echo $results1['good_subject']; ?>"></td>
					</tr>
					<tr width="100%">
						<th width="20%" class="h_medium">[卒業研究]テーマ</th>
						<td width="80%"><input type="text" name="theme" class="medium" value="<?php echo $results1['theme']; ?>"></td>
					</tr>
					<tr width="100%">
						<th width="20%" class="h_medium">[卒業研究]内容</th>
						<td width="80%"><textarea rows="2" name="detail" class="medium"><?php echo $results1['detail']; ?></textarea></td>
					</tr>
					<tr width="100%">
						<th width="20%" class="h_medium">学生時代力を入れたこと</th>
						<td width="80%"><textarea rows="4" name="myexperience" class="medium"><?php echo $results1['myexperience']; ?></textarea></td>
					</tr>
					<tr width="100%">
						<th width="20%" class="h_medium">長所</th>
						<td width="80%"><input type="text" name="mystrength" class="medium" value="<?php echo $results1['mystrength']; ?>"></td>
					</tr>
					<tr width="100%">
						<th width="20%" class="h_medium">短所</th>
						<td width="80%"><input type="text" name="myweekness" class="medium" value="<?php echo $results1['myweekness']; ?>"></td>
					</tr>
				</tbody>
			</table>
			<br>
			<table class="resume">
				<tbody>
					<tr width="100%">
						<th width="20%" class="h_medium">その他メモ</th>
						<td width="80%"><textarea rows="3" name="other" class="medium"><?php echo $results1['other']; ?></textarea></td>
					</tr>
				</tbody>
			</table>
			<button type="submit" name="action" value="edit" class="btn">登録</button>
			</form>
		</div>
	</div>
	<br>
	<br>
	</center>
</body>
</html>
