<?php
//creates the measures for tasks/projects/divisions of given query ($q), calculates percentage of time of total given hours ($h), and creates measure slices according to divisions
function measures($q, $h, $t) {
	//get location
	global $l;

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
			array_push($rows, [$row['title'], $row['hours'], $row['logs']]);
		}

		$maxSize = 1;

		//find biggest size
		for ($i = 0; $i < sizeof($rows); $i++) {
			if ($rows[$i][1] > $maxSize) $maxSize = $rows[$i][1];
		}

		//create all measures
		for ($i = 0; $i < sizeof($rows); $i++) {

			//don't include 'personal' division unless it's particularly relevant
			if ($h > 10 && $rows[$i][0] == 'Personal' && ($rows[$i][1] / $h * 100) < 5) continue;

			//get measure division ratio
			$pageType = checkType($l);
			if (!$pageType) {
				$query = 'select '.$t.'.name as title, division.name as division, sum(log.time) as hours from log left join project on project.id = log.project_id join task on task.id = log.task_id join division on division.id = log.division_id where '.$t.'.name = '."'".$rows[$i][0]."'".' group by division;';
			} else {
				$query = 'select '.$t.'.name as title, division.name as division, sum(log.time) as hours from log left join project on project.id = log.project_id join task on task.id = log.task_id join division on division.id = log.division_id where '.$t.'.name = '."'".$rows[$i][0]."'".' and '.$pageType.'.name = '."'".$l."'".' group by division;';
			}
			
			$ratio = getDivisionRatio($query);

			//get proper phrasing for hour and log numbers
			$hourphrase = pluralize('hour', number_format($rows[$i][1], 0));
			$logphrase = pluralize('log', $rows[$i][2] == 1);

			echo
			'
			<div class="measure-container">
			<svg class="measure-circle">
			';

			//variables for determining radius of division slices
			$degree = 0;
			$oldDegree = 0;

			for ($j = 0; $j < sizeof($ratio); $j++) {

				//get division color
				if ($ratio[$j][1] > 0) {

					switch($ratio[$j][0]) {
						case 'Code':
							$color = $codeColor;
							break;

						case 'Visual':
							$color = $visualColor;
							break;

						case 'Audio':
							$color = $audioColor;
							break;

						case 'Abstract':
							$color = $abstractColor;
							break;

						case 'Personal':
							$color = $personalColor;
							break;
					}

					//calculates size of division slice
					$degree += remap(($ratio[$j][1] / $rows[$i][1]), 0, 1, 0, M_PI*2);

					//outputs division slice
					echo
					'
					<g transform="translate(55 55) rotate(-90) scale(1 -1)">
						<path d="'.createSvgArc(0, 0, round(($rows[$i][1]/$maxSize * 40) + 4), $oldDegree, $degree).'" fill="'.$color.'"/>
					</g>
					';
					$oldDegree = $degree;
				}
			}
			
			//make hour minimum 1, to avoid division by 0
			if ($h == 0) $h = 1;

			//outputs middle circle for hollow point and measure data
			echo
			'
					<circle cx="50%" cy="50%" r="'.round(($rows[$i][1]/$maxSize * 32) + 2).'" fill="#fff"/>
				</svg>
				<div class="measure-info">
					<a href="'.$rows[$i][0].'" class="measure-title">'.$rows[$i][0].'</a>
					<div class="measure-text">'.number_format($rows[$i][1], 0).' '.$hourphrase.'</div>
					<div class="measure-text">'.$rows[$i][2].' '.$logphrase.'</div>
					<div class="measure-text">'.number_format((($rows[$i][1] / $h) * 100), 2).'%</div>
				</div>
			</div>
			';
		}
	}
	$conn->close();
}
?>