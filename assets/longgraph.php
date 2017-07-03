<?php
//creates graph of last $n days of logs and each division's daily involvement
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
		$now;

		//fill $days array with set number of days ($n)
		for ($i = 0; $i < $n; $i++) {
			array_push($days, null);
		}

		//echo sizeof($days);

		$dayCount = 0;

		//pass through all logs, separate them into each $day, and then put every $day into $days
		for ($i = 0; $i < sizeof($rows); $i++) {
			if ($i == 0) $now = $rows[$i][0];

			if ($now === $rows[$i][0]) {
				array_push($day, [$rows[$i][1], $rows[$i][2]]);
				if ($i == sizeof($rows) - 1) {
					array_push($days, $day);
				}

			} else {
				$now = $rows[$i][0];
				$days[$dayCount] = $day;
				$dayCount++;

				$day = array();
				array_push($day, [$rows[$i][1], $rows[$i][2]]);

				if ($i == sizeof($rows) - 1) array_push($days, $day);
			}
		}

		//$days[day][log][0: division 1: time]

		//total hours in each division (excluding personal)
		$totalAudio = 0;
		$totalAbstract = 0;
		$totalVisual = 0;
		$totalCode = 0;

		echo '<div class="long-graph-container">';

		//go through each day
		for ($i = sizeof($days)-1; $i > -1; $i--) {
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
						$totalAudio += $audioTime;
						break;
					
					case 'Abstract':
						$abstractTime += $days[$i][$j][1];
						$totalAbstract += $abstractTime;
						break;

					case 'Visual':
						$visualTime += $days[$i][$j][1];
						$totalVisual += $visualTime;
						break;

					case 'Code':
						$codeTime += $days[$i][$j][1];
						$totalCode += $codeTime;
						break;

					case 'Personal':
						$personalTime += $days[$i][$j][1];
						break;
				}
			}

			echo '<div class="long-graph-day" style="width:'.(100 / sizeof($days)).'%">';

			//sort values to determine render order
			$values = array($codeTime, $abstractTime, $visualTime, $audioTime, $personalTime);
			rsort($values);

			//render flags
			$codeDone = false;
			$abstractDone = false;
			$visualDone = false;
			$audioDone = false;
			$personalDone = false;

			//max number of hours per day per division
			$max = 11;

			//render all bars for the day
			for ($j = 0; $j < sizeof($values); $j++) {
				if ($values[$j] == $personalTime && !$personalDone) {
					echo
					'
					<svg class="long-graph-bar" style="margin-top: '.(200 - (($personalTime / $max) * 200)).'px; width:'.(500 / sizeof($days)).'">
						<rect width="'.(500 / sizeof($days)).'" height="'.(($personalTime / $max) * 200).'" fill="'.$personalColor.'"/>
					</svg>
					';
					$personalDone = true;
				}else if ($values[$j] == $codeTime && !$codeDone) {
					echo
					'
					<svg class="long-graph-bar" style="margin-top: '.(200 - (($codeTime / $max) * 200)).'px; width:'.(500 / sizeof($days)).'">
						<rect width="'.(500 / sizeof($days)).'" height="'.(($codeTime / $max) * 200).'" fill="'.$codeColor.'"/>
					</svg>
					';
					$codeDone = true;
				}else if ($values[$j] == $abstractTime && !$abstractDone) {
					echo
					'
					<svg class="long-graph-bar" style="margin-top: '.(200 - (($abstractTime / $max) * 200)).'px; width:'.(500 / sizeof($days)).'">
						<rect width="'.(500 / sizeof($days)).'" height="'.(($abstractTime / $max) * 200).'" fill="'.$abstractColor.'"/>
					</svg>
					';
					$abstractDone = true;
				}else if ($values[$j] == $visualTime && !$visualDone) {
					echo
					'
					<svg class="long-graph-bar" style="margin-top: '.(200 - (($visualTime / $max) * 200)).'px; width:'.(500 / sizeof($days)).'">
						<rect width="'.(500 / sizeof($days)).'" height="'.(($visualTime / $max) * 200).'" fill="'.$visualColor.'"/>
					</svg>
					';
					$visualDone = true;
				}else if ($values[$j] == $audioTime && !$audioDone) {
					echo
					'
					<svg class="long-graph-bar" style="margin-top: '.(200 - (($audioTime / $max) * 200)).'px; width:'.(500 / sizeof($days)).'">
						<rect width="'.(500 / sizeof($days)).'" height="'.(($audioTime / $max) * 200).'" fill="'.$audioColor.'"/>
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

				<span class="graph-stats-text">'.sizeof($days).' days'.'</span>
				<span class="graph-stats-text">'.number_format($h, 0).' hours'.'</span>
			</div>
			';
		}
	}
}
?>