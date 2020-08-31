<?php
function timeline($l, $type) {

	if ($l == null || $l == 'home') $q = 'select division.name as title, log.date, log.time as hours from log left join division on division.id = log.division_id order by log.id asc;';
	else $q = 'select division.name as title, log.date, log.time as hours from log left join project on project.id = log.project_id join task on task.id = log.task_id join division on division.id = log.division_id where '.$type.'.name = '."'".$l."'".' order by log.id asc;';

	global $backgroundColor;
	global $abstractColor;
	global $codeColor;
	global $audioColor;
	global $visualColor;

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

		//get timespan of timeline
		$first = new DateTime($rows[sizeof($rows)-1][0]);
		$last = new DateTime($rows[0][0]);
		$difference = $last->diff($first)->format("%a");

		//pass through all logs, separate them into each $day, fill each $day with its respective logs, and then put every $day into $days
		$dayCount = 0;
		$savedDate = $rows[0][0];

		for ($i = 0; $i < sizeof($rows); $i++) {
			//if starting new day
			if ($rows[$i][0] != $savedDate) {
				$savedDate = $rows[$i][0];
				$dayCount++;

				array_push($days, $day);
				$day = array();
			}

			array_push($day, [$rows[$i][1], $rows[$i][2], $savedDate]);
		}

		//add last element
		$dayCount++;
		array_push($days, $day);

		//$days[day][log][0: division 1: time 2: date]

		//get resolution for how many days/dots to render (50 days + ~15% of anything above 50 days)
		$t = 1;
		$dotDelimiter = 50;

		if ($dayCount < $dotDelimiter) $t = 1;
		else {
			$left = $dayCount - $dotDelimiter;
			$t = 1 + ($left / $dotDelimiter / 2);
		}

		//setup timeline layout
		echo
		'
		<div class="divider"></div>
		<div class="timeline-container">
			<span class="timeline-date-begin">'.$first->format('Y.m.d').'</span>
			<span class="timeline-date-end">'.$last->format('Y.m.d').'</span>
			<div class="timeline-bar-container">
				<div class="timeline"></div>
				<div class="timeline-circle-container">
		';

		//fill threshold (timeline node is filled if hours per day are higher than $threshold)
		$threshold = 4;

		//render dots
		if (sizeof($rows) > 1) {

			for ($i = sizeof($days) - 1; $i > -1; $i-=$t) {

				$now = new DateTime($days[$i][0][2]);
				$position = ($now->diff($first)->format("%a")) / $difference;

				$old = new DateTime($days[$i - 1][0][2]);
				$oldPosition = ($old->diff($first)->format("%a")) / $difference;

				if ($now != $old && ($oldPosition - $position) > 0.0001) {

					//check most popular topic of the day and color accordingly
					$fill;
					
					//go through each day
					$audioTime = 0;
					$abstractTime = 0;
					$visualTime = 0;
					$codeTime = 0;

					//in each day's logs, get number of hours for each divisions
					for ($j = 0; $j < sizeof($days[$i]); $j++) {
						switch ($days[$i][$j][0]) {
							case 'Audio':
								$audioTime += $days[$i][$j][1];
								break;
							
							case 'Abstract':
								$abstractTime += $days[$i][$j][1];
								break;

							case 'Visual':
								$visualTime += $days[$i][$j][1];
								break;

							case 'Code':
								$codeTime += $days[$i][$j][1];
								break;
						}
					}

					//sort values to determine render order
					$values = array($codeTime, $abstractTime, $visualTime, $audioTime);
					sort($values);

					if ($values[3] === $codeTime) $fill = $codeColor;
					else if ($values[3] === $abstractTime) $fill = $abstractColor;
					else if ($values[3] === $visualTime) $fill = $visualColor;
					else if ($values[3] === $audioTime) $fill = $audioColor;

					echo
					'
					<svg class="timeline-circle" style="left: '. $position * 100 .'%;">
						<circle cx="10" cy="10" r="5" stroke="'.$fill.'" stroke-width="1" fill="'.$fill.'"/>
					</svg>
					';
				}
			}
		} else {
			echo
			'
			<svg class="timeline-circle" style="left: 0%;">
				<circle cx="10" cy="10" r="5" stroke="#ccc" stroke-width="1" fill="'.$backgroundColor.'"/>
			</svg>
			';
		}

		//end timeline layout
		echo
		'
		</div>
		</div>
		</div>
		';
	}
	$conn->close();
}
?>