<?php
//creates graph of delimited days of logs ($n number of logs) within the contextual location ($spec 0 = all logs, $spec 1 = project/task, $spec 2 = division) and each division's daily involvement, using total log hours ($h), through given query ($q)
function graph($q, $h, $n, $spec) {
	//get colors
	global $abstractColor;
	global $codeColor;
	global $audioColor;
	global $visualColor;
	global $personalColor;

	global $l;

	$conn = connect();
	$result = $conn->query($q);

	if ($result->num_rows > 0) {
		$rows = array();

		//get query results
		while ($row = $result->fetch_assoc()) {
			if ($spec == 1) array_push($rows, [$row['date'], $row['title'], $row['hours'], $row['type']]);
			else if ($spec == 0 || $spec == 2) array_push($rows, [$row['date'], $row['title'], $row['hours']]);
		}

		//days array holds all days and their logs
		$days = array();
		//day array holds each day
		$day = array();
		//holds date
		$now = $rows[0][0];

		//get date offset
		if (!$spec) {
			$today = new DateTime();
			$recent = new DateTime($now);
			$difference = $today->diff($recent)->format("%a");
		} else {
			$difference = 0;
		}

		//fill $days array with set number of days ($n)
		for ($i = 0; $i < $n; $i++) {
			array_push($days, null);
		}

		$dayCount = $n + - 1 - $difference;

		//pass through all logs, separate them into each $day, fill each $day with its respective logs, and then put every $day into $days
		for ($i = 0; $i < sizeof($rows); $i++) {
			if ($now != $rows[$i][0]) {
				$now = $rows[$i][0];

				$days[$dayCount] = $day;
				$dayCount--;

				$day = array();
			}
			if ($spec == 1) {
				if (strtolower($rows[$i][3]) === $l) array_push($day, [$rows[$i][1], $rows[$i][2]]);
				else array_push($day, $rows[$i][1], 0);
			} else if ($spec == 0) {
				array_push($day, [$rows[$i][1], $rows[$i][2]]);
			} else if ($spec == 2) {
				if (strtolower($rows[$i][1]) === $l) array_push($day, [$rows[$i][1], $rows[$i][2]]);
				else array_push($day, $rows[$i][1], 0);
			}
		}

		//add last element
		$days[$dayCount] = $day;

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

			//graph data for rendering measurements
			$graphHeight = 200;
			$barWidthModifier = 600;

			//render all bars for the day
			for ($j = 0; $j < sizeof($values); $j++) {
				if ($values[$j] == $personalTime && !$personalDone) {
					$height += ($personalTime / $max) * $graphHeight;
					echo
					'
					<svg class="graph-bar" style="margin-top: '.($graphHeight - $height).'px; margin-bottom: '.($height).'px;">
						<rect width="100%" height="'.((($personalTime / $max) * $graphHeight) + 1).'" fill="'.$personalColor.'"/>
					</svg>
					';
					$personalDone = true;
				} else if ($values[$j] == $codeTime && !$codeDone) {
					$height += ($codeTime / $max) * $graphHeight;
					echo
					'
					<svg class="graph-bar" style="margin-top: '.($graphHeight - $height).'px; margin-bottom: '.($height).'px;">
						<rect width="100%" height="'.((($codeTime / $max) * $graphHeight) + 1).'" fill="'.$codeColor.'"/>
					</svg>
					';
					$codeDone = true;
				} else if ($values[$j] == $abstractTime && !$abstractDone) {
					$height += ($abstractTime / $max) * $graphHeight;
					echo
					'
					<svg class="graph-bar" style="margin-top: '.($graphHeight - $height).'px; margin-bottom: '.($height).'px;">
						<rect width="100%" height="'.((($abstractTime / $max) * $graphHeight) + 1).'" fill="'.$abstractColor.'"/>
					</svg>
					';
					$abstractDone = true;
				} else if ($values[$j] == $visualTime && !$visualDone) {
					$height += ($visualTime / $max) * $graphHeight;
					echo
					'
					<svg class="graph-bar" style="margin-top: '.($graphHeight - $height).'px; margin-bottom: '.($height).'px;">
						<rect width="100%" height="'.((($visualTime / $max) * $graphHeight) + 1).'" fill="'.$visualColor.'"/>
					</svg>
					';
					$visualDone = true;
				} else if ($values[$j] == $audioTime && !$audioDone) {
					$height += ($audioTime / $max) * $graphHeight;
					echo
					'
					<svg class="graph-bar" style="margin-top: '.($graphHeight - $height).'px; margin-bottom: '.($height).'px;">
						<rect width="100%" height="'.((($audioTime / $max) * $graphHeight) + 1).'" fill="'.$audioColor.'"/>
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

//sets up graph delimitations (to specific number of days $d if set) (according to contextual topic if $s, by using type $t)
function setupGraph($s, $d, $t) {
	$graphData = array();

	//if spec page, find delimitations of contextual topic's logs
	if ($s) {
		global $l;

		$query = 'select log.date, sum(log.time) as hours from log left join project on project.id = log.project_id join task on task.id = log.task_id join division on division.id = log.division_id where '.$t.'.name = '."'".$l."'".' group by date order by log.id asc;';

		$conn = connect();
		$result = $conn->query($query);

		if ($result->num_rows > 0) {
			$rows = array();

			//get query results
			while ($row = $result->fetch_assoc()) {
				array_push($rows, [$row['date'], $row['hours']]);
			}

			//if no specified day number is set, get timespan dynamically
			if ($d == 0) {
				$first = new DateTime($rows[sizeof($rows)-1][0]);
				$first = $first->sub(new DateInterval('P1D'));
				$last = new DateTime($rows[0][0]);
				$difference = $last->diff($first)->format("%a");
			} else {
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

			$hours = getNum('select sum(time) as num_hours, '.$t.'.name as type from log left join '.$t.' on '.$t.'.id = log.'.$t.'_id where date between '."'".$old."'".' and '."'".$now."'".' and '.$t.'.name = '."'".$l."'".';', 'num_hours');

			$conn->close();

			return array($old, $now, $hours, $days);
		}
	} else {
		$now = new DateTime();
		$now = $now->format('Y-m-d');

		$old = new DateTime($now);
		$old = $old->sub(new DateInterval('P'.($d - 1).'D'));
		$old = $old->format('Y-m-d');

		$hours = getNum('select sum(time) as num_hours from log where date between '."'".$old."'".' and '."'".$now."'".';', 'num_hours');

		return array($old, $now, $hours);
	}
}
?>