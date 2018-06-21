<?php
function graph($l, $desiredDays, $type, $h) {
	$graphData = setupGraph($l, $desiredDays, $type);

	$h = $graphData[2];
	$desiredDays = $graphData[3];

	if ($type === 'project' || $type === 'task') $q = 'select division.name as title, log.date, log.time as hours, '.$type.'.name as type from log left join division on division.id = log.division_id join '.$type.' on '.$type.'.id = log.'.$type.'_id where date between '."'".$graphData[0]."'".' and '."'".$graphData[1]."'".' order by log.id asc;';
	else $q = 'select division.name as title, log.date, log.time as hours from log left join division on division.id = log.division_id where date between '."'".$graphData[0]."'".' and '."'".$graphData[1]."'".'  order by log.id asc;';

	global $abstractColor;
	global $codeColor;
	global $audioColor;
	global $visualColor;
	global $personalColor;

	$conn = connect();
	$result = $conn->query($q);

	if ($result->num_rows > 0) {
		$rows = array();

		//get query results
		while ($row = $result->fetch_assoc()) {
			if ($type === 'project' || $type === 'task') array_push($rows, [$row['date'], $row['title'], $row['hours'], $row['type']]);
			else array_push($rows, [$row['date'], $row['title'], $row['hours']]);
		}

		//days array holds all days and their logs
		$days = array();
		//day array holds each day
		$day = array();
		//holds date
		$now = $rows[0][0];

		//get date offset
		if (!$type) {
			$today = new DateTime();
			$recent = new DateTime($now);
			$difference = $today->diff($recent)->format("%a");
		} else {
			$difference = 0;
		}

		//fill $days array with set number of days ($n)
		for ($i = 0; $i < $desiredDays; $i++) {
			array_push($days, null);
		}

		//number of days before last log (-1 to count current day)
		$dayCount = $desiredDays - 1 - $difference;

		//pass through all logs, separate them into each $day, fill each $day with its respective logs, and then put every $day into $days
		for ($i = 0; $i < sizeof($rows); $i++) {
			//if not the next dated element
			if ($now !== $rows[$i][0]) {
				//then update marker for date
				$now = $rows[$i][0];

				//add day to $days
				$days[$dayCount] = $day;
				$dayCount--;

				$day = array();
			}
			if ($type == 'project' || $type == 'task') {
				if (strtolower($rows[$i][3]) === stripslashes($l)) array_push($day, [$rows[$i][1], $rows[$i][2]]);
				else array_push($day, $rows[$i][1], 0);
			} else if ($type == null) {
				array_push($day, [$rows[$i][1], $rows[$i][2]]);
			} else if ($type == 'division') {
				if (strtolower($rows[$i][1]) === stripslashes($l)) array_push($day, [$rows[$i][1], $rows[$i][2]]);
				else array_push($day, $rows[$i][1], 0);
			}
		}

		//add last element ([0])
		$days[0] = $day;

		//$days[day][log][0: division 1: time]

		//total hours in each division
		$totalAudio = 0;
		$totalAbstract = 0;
		$totalVisual = 0;
		$totalCode = 0;
		$totalPersonal = 0;

		//get max number of hours achieved in single day
		$max = 0;
		for ($i = 0; $i < sizeof($days); $i++) {
			$hours = 0;

			for ($j = 0; $j < sizeof($days[$i]); $j++) {
				$hours += $days[$i][$j][1];
			}

			if ($hours > $max) $max = $hours;
		}
		if ($max == 0) $max = 1;

		echo
		'
		<div class="graph-container">
			<div class="graph-bar-container">
		';

		//go through each day
		for ($i = 0; $i < sizeof($days); $i++) {
			$audioTime = 0;
			$abstractTime = 0;
			$visualTime = 0;
			$codeTime = 0;
			$personalTime = 0;

			//in each day's logs, get number of hours for each divisions

			for ($j = 0; $j < sizeof($days[$i]); $j++) {
				switch ($days[$i][$j][0]) {
					case 'Audio':
						$audioTime += $days[$i][$j][1];
						$totalAudio += $days[$i][$j][1];
						break;
					
					case 'Abstract':
						$abstractTime += $days[$i][$j][1];
						$totalAbstract += $days[$i][$j][1];
						break;

					case 'Visual':
						$visualTime += $days[$i][$j][1];
						$totalVisual += $days[$i][$j][1];
						break;

					case 'Code':
						$codeTime += $days[$i][$j][1];
						$totalCode += $days[$i][$j][1];
						break;

					case 'Personal':
						$personalTime += $days[$i][$j][1];
						$totalPersonal += $days[$i][$j][1];
						break;
				}
			}

			echo '<div class="graph-day" style="width:'.(100 / sizeof($days)).'%">';

			//sort values to determine render order
			$values = array($codeTime, $abstractTime, $visualTime, $audioTime, $personalTime);
			sort($values);

			//render flags
			$codeDone = false;
			$abstractDone = false;
			$visualDone = false;
			$audioDone = false;
			$personalDone = false;

			//saves height for each bar - determining total height, allowing bar stacks
			$height = 0;
			$oldHeight = 0;

			//graph data for rendering measurements
			$graphHeight = 200;
			$barWidthModifier = 600;

			//render all bars for the day
			for ($j = 0; $j < sizeof($values); $j++) {
				$spacing = 1;

				if ($values[$j] == $personalTime && !$personalDone) {
					$oldHeight = $height;
					if ($oldHeight == 0) $spacing = 0;
					$height += ($personalTime / $max) * $graphHeight;
					echo
					'
					<svg class="graph-bar" style="height: '.($height - $oldHeight - $spacing).'px; margin-top: '.($graphHeight - $height).'px; margin-bottom: '.($oldHeight).'px;">
						<rect width="100%" height="'.(($personalTime / $max) * $graphHeight).'" fill="'.$personalColor.'"/>
					</svg>
					';
					$personalDone = true;
				} else if ($values[$j] == $codeTime && !$codeDone) {
					$oldHeight = $height;
					if ($oldHeight == 0) $spacing = 0;
					$height += ($codeTime / $max) * $graphHeight;
					echo
					'
					<svg class="graph-bar" style="height: '.($height - $oldHeight - $spacing).'px; margin-top: '.($graphHeight - $height).'px; margin-bottom: '.($oldHeight).'px;">
						<rect width="100%" height="'.(($codeTime / $max) * $graphHeight).'" fill="'.$codeColor.'"/>
					</svg>
					';
					$codeDone = true;
				} else if ($values[$j] == $abstractTime && !$abstractDone) {
					$oldHeight = $height;
					if ($oldHeight == 0) $spacing = 0;
					$height += ($abstractTime / $max) * $graphHeight;
					echo
					'
					<svg class="graph-bar" style="height: '.($height - $oldHeight - $spacing).'px; margin-top: '.($graphHeight - $height).'px; margin-bottom: '.($oldHeight).'px;">
						<rect width="100%" height="'.(($abstractTime / $max) * $graphHeight).'" fill="'.$abstractColor.'"/>
					</svg>
					';
					$abstractDone = true;
				} else if ($values[$j] == $visualTime && !$visualDone) {
					$oldHeight = $height;
					if ($oldHeight == 0) $spacing = 0;
					$height += ($visualTime / $max) * $graphHeight;
					echo
					'
					<svg class="graph-bar" style="height: '.($height - $oldHeight - $spacing).'px; margin-top: '.($graphHeight - $height).'px; margin-bottom: '.($oldHeight).'px;">
						<rect width="100%" height="'.(($visualTime / $max) * $graphHeight).'" fill="'.$visualColor.'"/>
					</svg>
					';
					$visualDone = true;
				} else if ($values[$j] == $audioTime && !$audioDone) {
					$oldHeight = $height;
					if ($oldHeight == 0) $spacing = 0;
					$height += ($audioTime / $max) * $graphHeight;
					echo
					'
					<svg class="graph-bar" style="height: '.($height - $oldHeight - $spacing).'px; margin-top: '.($graphHeight - $height).'px; margin-bottom: '.($oldHeight).'px;">
						<rect width="100%" height="'.(($audioTime / $max) * $graphHeight).'" fill="'.$audioColor.'"/>
					</svg>
					';
					$audioDone = true;
				}
			}
			echo '</div>';
		}
		echo '</div>';

		//create legend
		if ($h <= 0) $h = 0.1;
		echo
			'
				<div class="graph-legend">
					<span class="graph-legend-text">Code</span>
					<span class="graph-legend-num" style="color:'.$codeColor.'">'.number_format((($totalCode / $h) * 100), 2, '.', ''). '%'.'</span>

					<span class="graph-legend-text">Abstract</span>
					<span class="graph-legend-num" style="color:'.$abstractColor.'">'.number_format((($totalAbstract / $h) * 100), 2, '.', ''). '%'.'</span>

					<span class="graph-legend-text">Visual</span>
					<span class="graph-legend-num" style="color:'.$visualColor.'">'.number_format((($totalVisual / $h) * 100), 2, '.', ''). '%'.'</span>

					<span class="graph-legend-text">Audio</span>
					<span class="graph-legend-num" style="color:'.$audioColor.'">'.number_format((($totalAudio / $h) * 100), 2, '.', ''). '%'.'</span>

					<span class="graph-stats-text">'.sizeof($days).' days</span>
					<span class="graph-stats-text">'.number_format($h, 0, '.', '').' hours</span>
					<span class="graph-stats-text">'.number_format(($totalCode + $totalAbstract + $totalVisual + $totalAudio + $totalPersonal) / sizeof($days), 1, '.', '').' h/d</span>
				</div>
			</div>
			';
	}
	$conn->close();
}

function setupGraph($l, $desiredDays, $type) {

	//if spec page, find delimitations of contextual topic's logs
	if ($type != null) {
		$query = 'select log.date, sum(log.time) as hours from log left join project on project.id = log.project_id join task on task.id = log.task_id join division on division.id = log.division_id where '.$type.'.name = '."'".$l."'".' group by date order by log.id asc;';

		$conn = connect();
		$result = $conn->query($query);

		if ($result->num_rows > 0) {
			$rows = array();

			//get query results
			while ($row = $result->fetch_assoc()) {
				array_push($rows, [$row['date'], $row['hours']]);
			}

			//if no specified day number is set, get timespan dynamically
			if ($desiredDays == 0) {
				$first = new DateTime($rows[sizeof($rows)-1][0]);
				$first = $first->sub(new DateInterval('P1D'));
				$last = new DateTime($rows[0][0]);
				$difference = $last->diff($first)->format("%a");
			} else {
				echo 'hi';
				$last = new DateTime($rows[0][0]);
				$first = $last->sub(new DateInterval('P'.$d.'D'));
				$difference = $last->diff($first)->format("%a");
			}

			//limit to 120 days
			if ($difference > 120) $difference = 120;

			$days = $difference;

			if ($days <= 0) $days = 1;

			$now = $last;
			$now = $now->format('Y-m-d');

			$old = new DateTime($now);
			$old = $old->sub(new DateInterval('P'.($days - 1).'D'));
			$old = $old->format('Y-m-d');

			$hours = getNum('select sum(time) as num_hours, '.$type.'.name as type from log left join '.$type.' on '.$type.'.id = log.'.$type.'_id where date between '."'".$old."'".' and '."'".$now."'".' and '.$type.'.name = '."'".$l."'".';', 'num_hours');

			$conn->close();

			return array($old, $now, $hours, $days);
		}
	} else {
		$now = new DateTime();
		$now = $now->format('Y-m-d');

		$old = new DateTime($now);
		$old = $old->sub(new DateInterval('P'.($desiredDays - 1).'D'));
		$old = $old->format('Y-m-d');

		$hours = getNum('select sum(time) as num_hours from log where date between '."'".$old."'".' and '."'".$now."'".';', 'num_hours');

		$days = $desiredDays;

		return array($old, $now, $hours, $days);
	}
}
?>