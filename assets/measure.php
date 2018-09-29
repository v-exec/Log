<?php
function measures($l, $measureType, $pageType, $h) {

	if (!$pageType) $q = 'select '.$measureType.'.name as title, sum(log.time) as hours, count(*) as logs from log left join project on project.id = log.project_id join task on task.id = log.task_id join division on division.id = log.division_id group by title order by hours desc;';
	else $q = 'select '.$pageType.'.name as main, '.$measureType.'.name as title, sum(log.time) as hours, count(*) as logs from log left join project on project.id = log.project_id join task on task.id = log.task_id join division on division.id = log.division_id where '.$pageType.'.name = '."'".$l."'".' group by title order by hours desc;';
	
	global $abstractColor;
	global $codeColor;
	global $audioColor;
	global $visualColor;
	global $personalColor;

	$conn = connect();
	$result = $conn->query($q);
	if ($result->num_rows > 0) {
		$rows = array();

		while ($row = $result->fetch_assoc()) {
			array_push($rows, [$row['title'], $row['hours'], $row['logs']]);
		}

		$maxSize = 1;

		//find biggest size
		for ($i = 0; $i < sizeof($rows); $i++) {
			if ($rows[$i][1] > $maxSize) $maxSize = $rows[$i][1];
		}

		//container
		echo '
		<div class="divider"></div>
		<div class="measures-container">
		';
		
		//title
		if ($measureType === 'task') echo '<a href="tasks" class="measures-title">Tasks</a>';
		else if ($measureType === 'project') echo '<a href="projects" class="measures-title">Projects</a>';
		else if ($measureType === 'division') echo '<a href="divisions" class="measures-title">Divisions</a>';

		echo '<div style="width: 100%";></div>';

		//create all measures
		for ($i = 0; $i < sizeof($rows); $i++) {

			//don't include 'personal' division unless it's particularly relevant
			if ($h > 10 && $rows[$i][0] == 'Personal' && ($rows[$i][1] / $h * 100) < 5 || $rows[$i][0] == 'None') continue;

			//get measure division ratio
			if (!$pageType) $ratio = getDivisionRatio(null, null, $rows[$i][0], $measureType);
			else $ratio = getDivisionRatio($l, $pageType, $rows[$i][0], $measureType);

			//get proper phrasing for hour and log numbers
			$hourphrase = pluralize('hour', number_format($rows[$i][1], 0, '.', ''));
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
					<g transform="translate(47 47) rotate(-90) scale(1 -1)">
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
					<circle cx="50%" cy="50%" r="'.round(($rows[$i][1]/$maxSize * 34) + 2).'" fill="#f7f7f7"/>
				</svg>
				<div class="measure-info">
					<a href="'.$rows[$i][0].'" class="measure-title">'.$rows[$i][0].'</a>
					<div class="measure-text">'.number_format($rows[$i][1], 0, '.', '').' '.$hourphrase.'</div>
					<div class="measure-text">'.$rows[$i][2].' '.$logphrase.'</div>
					<div class="measure-text">'.number_format((($rows[$i][1] / $h) * 100), 2, '.', '').'%</div>
				</div>
			</div>
			';
		}

		//close container
		echo '</div>';
	}
	$conn->close();
}
?>