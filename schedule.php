<html>
<head>
	<title>HOME -Schedule</title>
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
			<li class="current"><a href="schedule.php">Schedule</a></li>
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
	?>
	<?php
	if ($_POST['action'] == 'delete') {
		// 削除対象番号 確保
		$delid = $_POST['delid'];

		// エラー処理分岐のための変数 確保
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
	}
	?>
	<?php
	$year = date('Y');
	$month = date('m');
	switch($_POST['action']){
		case 'pre':
			$year = $_POST['p_year'];
			$month = $_POST['p_month'];
			print(calendar($usermail, $year, $month));
			break;
		case 'next':
			$year = $_POST['n_year'];
			$month = $_POST['n_month'];
			print(calendar($usermail, $year, $month));
			break;
		default:
			print(calendar($usermail, $year, $month));
			break;
	}
	?>
	<br>
	<hr>
	<center>
	<h1 class="circle"><?php echo $month; ?>月の予定<br><a style="font-size:medium">（本日以降）</a></h1><br>
	<?php
	$mydate = $year.'/'.$month;
	$today = $year.'/'.$month.'/'.date('d');
	$sql = $pdo -> prepare("SELECT * FROM events WHERE usermail=:usermail AND mydate like concat(:mydate, '%') ORDER BY mydate");
	$params = array(':usermail'=>$usermail, ':mydate'=>$mydate);
	$sql -> execute($params);
	$results = $sql->fetchAll();

	if (empty($results)) {
		$e_print .= "予定なし<br>";
	} else {
		$check = 0;
		foreach ($results as $row) {
			if ($row['mydate'] < $today) { continue; }
			$text = str_replace("\r\n", "<br />", $row['detail']);
			if ($date != substr($row['mydate'], '5')) {
				if ($check == 1) { $e_print .= "</div></div>"; }
				$check = 1;
				$date = substr($row['mydate'], '5');
				$e_print .= <<<EOM
<div class="hidden_box">
	<label for={$date}><h3 class="sideline">{$date}</h3><br></label>
	<input type="checkbox" id={$date} class="none" checked></input>
	<div class="hidden_show">
EOM;
			}
			$e_print .= "<a class={$row['color']} style='font-size:x-large'>{$row['title']}</a>";
			if(!empty($text)) { $e_print .= "<div class='txtbox' align='left'>{$text}</div>"; }
			else { $e_print .= '<br>'; }
			$e_print .= <<<EOM
<form method="post" action="schedule.php" class="inline">
	<input type="hidden" name="date" value={$mydate}>
	<input type="hidden" name="delid" value={$row['id']}>
	<button type="submit" class="btn" name="action" value="delete">削除</button>
</form>
<form method="post" action="editevent.php" class="inline">
	<input type="hidden" name="editid" value={$row['id']}>
	<button type="submit" class="btn">編集</button>
</form>
<br><br><br>
EOM;
		}
		if ($check == 1) { $e_print .= "</div></div>"; }
	}
	echo $e_print;
	?>
	</center>
	<br><br>
</body>
</html>





<?php
	function calendar($usermail, $year, $month) {
		// 前月
		$p_year = $year;
		$p_month = $month - 1;
		if ($p_month < 10) { $p_month = '0'.$p_month; }
		if ($month == 1) {	// 前月
			$p_year = $year - 1;
			$p_month = 12;
		}
		// 次月
		$n_year = $year;
		$n_month = $month + 1;
		if ($n_month < 10) { $n_month = '0'.$n_month; }
		if ($month == 12) {
			$n_year = $year + 1;
			$n_month = '01';
		}
		//月末の取得
		$l_day = date('j', mktime(0, 0, 0, $month + 1, 0, $year));

		//初期出力
		$html = <<<EOM
<table class="calendar" align="center" frame="void">
	<thead>
		<tr class="thead">
			<form method="post">
			<th valign="middle" class="cal_th">
					<input type="hidden" name="p_year" value={$p_year}>
					<input type="hidden" name="p_month" value={$p_month}>
					<button type="submit" name="action" value="pre" class="arrow_btn"><<</button>
			</th>
			<th colspan="5" valign="middle" class="cal_th">{$year} / {$month}</th>
			<th valign="middle" class="cal_th">
					<input type="hidden" name="n_year" value={$n_year}>
					<input type="hidden" name="n_month" value={$n_month}>
					<button type="submit" name="action" value="next" class="arrow_btn">>></button>
			</th>
			</form>
		</tr>
	</thead>
	<tbody>
		<tr class="week">
			<td class="sun" id="week">日</td>
			<td id="week">月</td>
			<td id="week">火</td>
			<td id="week">水</td>
			<td id="week">木</td>
			<td id="week">金</td>
			<td class="sat" id="week">土</td>
		</tr>\n
EOM;

		// 月末まで繰り返す
		for ($i = 1; $i <= $l_day; $i++) {
			$classes = array();
			$class   = '';
			// 曜日の取得
			$week = date('w', mktime(0, 0, 0, $month, $i, $year));

			// 日曜 or 土曜の場合
			if ($week == 0) {
				$html .= "\t\t<tr>\n";
				$classes[] = 'sun';
			} else if ($week == 6) {
				$classes[] = 'sat';
			} else {
				$classes[] = 'normal';
			}

			// 1日の場合
			if ($i == 1) {
				if($week != 0) { $html .= "\t\t<tr>\n"; }
				$html .= repeatEmptyTd($week);
			}
			// 今日の場合
			if ($i == date('j') && $year == date('Y') && $month == date('m')) {
				$classes[] = 'today';
			}

			// クラスを定義
			if (count($classes) > 0) {
				$class = ' class="'.implode(' ', $classes).'"';
			}

			// DB接続
			$dsn = 'mysql:dbname=データベース名;host=localhost';
			$user = 'ユーザ名';
			$password = 'パスワード';
			$pdo = new PDO($dsn, $user, $password);
			// この日予定タイトル取得
			if ($i < 10) { $i0 = '0'.$i; }
			else { $i0 = $i; }
			$sql = "SELECT * FROM events WHERE usermail='$usermail' and mydate='$year/$month/$i0'";
			$results = $pdo -> query($sql);
			$check = 0;
			if (!empty($results)) { $check = 1; }

			$html .= "\t\t\t".'<td'.$class.' valign="middle">'."\n";
			$html .= "\t\t\t\t".'<form method="post" action="event.php">'."\n";
			$html .= "\t\t\t\t\t".'<input type="hidden" name="date" value="'."$year/$month/$i0".'">'."\n";
			$html .= "\t\t\t\t\t".'<button type="submit" formaction="event.php" '.$class.' id="daylink">'.$i.'</button>'."\n";
			$html .= "\t\t\t\t".'</form>'."\n";
			if ($check == 1) {
				$html .= "\t\t\t\t".'<span>'."\n";
				foreach ($results as $row) {
					$html .= "\t\t\t\t".'<form method="post" action="editevent.php" class="inline">'."\n";
					$html .= "\t\t\t\t\t".'<input type="hidden" name="editid" value='.$row['id'].'>'."\n";
					$html .= "\t\t\t\t\t".'<button type="submit" id="event" class='.$row['color'].'>'.$row['title'].'</button>'."\n";
					$html .= "\t\t\t\t".'</form>'."\n";
				}
				$html .= "\t\t\t\t".'</span>'."\n";
			}
			$html .= "\t\t\t".'</td>'."\n";

			// 月末の場合
			if ($i == $l_day) {
				$html .= repeatEmptyTd(6 - $week);
			}
			// 土曜日の場合
			if ($week == 6) {
				$html .= "\t\t</tr>\n";
			}
		}	// for()終わり

		$html .= "\t</tbody>\n";
		$html .= "</table>\n";

		return $html;
	}

	function repeatEmptyTd($n = 0) {
		return str_repeat("\t\t<td class=".'normal'."> </td>\n", $n);
	}
?>
