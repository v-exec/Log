<?php
//creates graph of last number of days of logs ($n) and each division's daily involvement, using total log hours ($h), through given query ($q)
function longgraph($q, $h, $n) {
	//get colors
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
			array_push($rows, [$row['date'], $row['title'], $row['hours']]);
		}

		//days array holds all days and their logs
		$days = array();
		//day array holds each day
		$day = array();
		//holds date
		$now = $rows[0][0];

		//get date offset
		$today = new DateTime();
		$recent = new DateTime($now);
		$difference = $today->diff($recent)->format("%a");

		//fill $days array with set number of days ($n)
		for ($i = 0; $i < $n; $i++) {
			array_push($days, null);
		}

		$dayCount = $n + - 1 - $difference;

		//pass through all logs, separate them into each $day, and then put every $day into $days
		for ($i = 0; $i < sizeof($rows); $i++) {
			if ($now != $rows[$i][0]) {
				$now = $rows[$i][0];

				$days[$dayCount] = $day;
				$dayCount--;

				$day = array();
			}
			array_push($day, [$rows[$i][1], $rows[$i][2]]);
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

		echo '<div class="long-graph-container">';

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

			echo '<div class="long-graph-day" style="width:'.(100 / sizeof($days)).'%">';

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
					<svg class="long-graph-bar" style="margin-top: '.($graphHeight - $height).'px; margin-bottom: '.($height).'px; width:'.($barWidthModifier / sizeof($days)).'">
						<rect width="'.($barWidthModifier / sizeof($days)).'" height="'.((($personalTime / $max) * $graphHeight) + 1).'" fill="'.$personalColor.'"/>
					</svg>
					';
					$personalDone = true;
				}else if ($values[$j] == $codeTime && !$codeDone) {
					$height += ($codeTime / $max) * $graphHeight;
					echo
					'
					<svg class="long-graph-bar" style="margin-top: '.($graphHeight - $height).'px; margin-bottom: '.($height).'px; width:'.($barWidthModifier / sizeof($days)).'">
						<rect width="'.($barWidthModifier / sizeof($days)).'" height="'.((($codeTime / $max) * $graphHeight) + 1).'" fill="'.$codeColor.'"/>
					</svg>
					';
					$codeDone = true;
				}else if ($values[$j] == $abstractTime && !$abstractDone) {
					$height += ($abstractTime / $max) * $graphHeight;
					echo
					'
					<svg class="long-graph-bar" style="margin-top: '.($graphHeight - $height).'px; margin-bottom: '.($height).'px; width:'.($barWidthModifier / sizeof($days)).'">
						<rect width="'.($barWidthModifier / sizeof($days)).'" height="'.((($abstractTime / $max) * $graphHeight) + 1).'" fill="'.$abstractColor.'"/>
					</svg>
					';
					$abstractDone = true;
				}else if ($values[$j] == $visualTime && !$visualDone) {
					$height += ($visualTime / $max) * $graphHeight;
					echo
					'
					<svg class="long-graph-bar" style="margin-top: '.($graphHeight - $height).'px; margin-bottom: '.($height).'px; width:'.($barWidthModifier / sizeof($days)).'">
						<rect width="'.($barWidthModifier / sizeof($days)).'" height="'.((($visualTime / $max) * $graphHeight) + 1).'" fill="'.$visualColor.'"/>
					</svg>
					';
					$visualDone = true;
				}else if ($values[$j] == $audioTime && !$audioDone) {
					$height += ($audioTime / $max) * $graphHeight;
					echo
					'
					<svg class="long-graph-bar" style="margin-top: '.($graphHeight - $height).'px; margin-bottom: '.($height).'px; width:'.($barWidthModifier / sizeof($days)).'">
						<rect width="'.($barWidthModifier / sizeof($days)).'" height="'.((($audioTime / $max) * $graphHeight) + 1).'" fill="'.$audioColor.'"/>
					</svg>
					';
					$audioDone = true;
				}
			}
			echo '</div>';
		}
		echo '</div>';

		//create legend
		if ($h > 0) {
			echo
			'
			<div class="graph-legend">
				<span class="graph-legend-text">Code</span>
				<span class="graph-legend-num" style="color:'.$codeColor.'">'.number_format((($totalCode / $h) * 100), 2). '%'.'</span>

				<span class="graph-legend-text">Abstract</span>
				<span class="graph-legend-num" style="color:'.$abstractColor.'">'.number_format((($totalAbstract / $h) * 100), 2). '%'.'</span>

				<span class="graph-legend-text">Visual</span>
				<span class="graph-legend-num" style="color:'.$visualColor.'">'.number_format((($totalVisual / $h) * 100), 2). '%'.'</span>

				<span class="graph-legend-text">Audio</span>
				<span class="graph-legend-num" style="color:'.$audioColor.'">'.number_format((($totalAudio / $h) * 100), 2). '%'.'</span>

				<span class="graph-stats-text">'.sizeof($days).' days</span>
				<span class="graph-stats-text">'.number_format($h, 0).' hours</span>
				<span class="graph-stats-text">'.number_format(($totalCode + $totalAbstract + $totalVisual + $totalAudio + $totalPersonal) / sizeof($days), 1).' h/d</span>
			</div>
			';
		}
	}
}
?>