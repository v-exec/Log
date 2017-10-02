<?php
function timeline($l, $type) {

	if ($l == null || $l == 'home') $q = 'select log.date, sum(log.time) as hours from log group by date order by log.id asc;';
	else $q = 'select log.date, sum(log.time) as hours from log left join project on project.id = log.project_id join task on task.id = log.task_id join division on division.id = log.division_id where '.$type.'.name = '."'".$l."'".' group by date order by log.id asc;';

	$fillColor = '#000';

	$conn = connect();
	$result = $conn->query($q);

	if ($result->num_rows > 0) {
		$rows = array();

		while ($row = $result->fetch_assoc()) {
			array_push($rows, [$row['date'], $row['hours']]);
		}

		//get timespan of timeline
		$first = new DateTime($rows[sizeof($rows)-1][0]);
		$last = new DateTime($rows[0][0]);
		$difference = $last->diff($first)->format("%a");

		//get number of separate days
		$days = 0;
		$savedDate = '';
		for ($i = 0; $i < sizeof($rows); $i++) {
			if ($rows[$i] != $savedDate) {
				$savedDate = $rows[0];
				$days++;
			}
		}

		//get resolution for how many days/dots to render (50 days + ~15% of anything above 50 days)
		$t = 1;
		$dotDelimiter = 50;

		if ($days < $dotDelimiter) $t = 1;
		else {
			$left = $days - $dotDelimiter;
			$t = 1 + ($left / $dotDelimiter / 2);
		}

		//setup timeline layout
		echo
		'
		<div class="timeline-container">
			<span class="timeline-date-begin">'.$first->format('Y.m.d').'</span>
			<span class="timeline-date-end">'.$last->format('Y.m.d').'</span>
			<div class="timeline-bar-container">
				<div class="timeline"></div>
				<div class="timeline-circle-container">
		';

		//fill threshold (timeline node is filled if hours per day are higher than $threshold)
		$threshold = 3;

		//display dots
		if (sizeof($rows) > 1) {
			for ($i = sizeof($rows) - 1; $i > -1; $i-=$t) {
				$now = new DateTime($rows[$i][0]);
				$position = ($now->diff($first)->format("%a")) / $difference;

				$old = new DateTime($rows[$i - 1][0]);
				$oldPosition = ($old->diff($first)->format("%a")) / $difference;

				if ($now != $old && ($oldPosition - $position) > 0.0001) {

					if ($rows[$i][1] >= $threshold) $fill = $fillColor;
					else $fill = '#fff';
					
					echo
					'
					<svg class="timeline-circle" style="left: '. $position * 100 .'%;">
						<circle cx="10" cy="10" r="5" stroke='.$fillColor.' stroke-width="1" fill="'.$fill.'"/>
					</svg>
					';
				}
			}
		} else {
			echo
			'
			<svg class="timeline-circle" style="left: 0%;">
				<circle cx="10" cy="10" r="5" stroke="'.$fillColor.'"" stroke-width="1" fill="'.$fillColor.'"/>
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