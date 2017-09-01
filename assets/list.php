<?php
//creates loglist of given query ($q), limit to 10 logs
function loglist($q) {
	$conn = connect();
	$result = $conn->query($q);
	if ($result->num_rows > 0) {
		$rows = array();

		//get query results
		while ($row = $result->fetch_assoc()) {
			array_push($rows, [$row['date'], $row['time'], $row['project'], $row['task'], $row['details']]);
		}

		//display logs
		$displayLogCount = 10;

		if (sizeof($rows) > $displayLogCount) $size = $displayLogCount;
		else $size = sizeof($rows);

		//container
		echo '<div class="loglist-container">';

		for ($i = 0; $i < $size; $i++) {
			$date = new DateTime($rows[$i][0]);
			echo
			'
			<div class="loglist-item-container">
				<div class="loglist-date">
					<span class="loglist-text">'.$date->format('Y.m.d').'</span>
				</div>
				<div class="loglist-time">
					<span class="loglist-text">'.number_format($rows[$i][1], 1).'</span>
				</div>
				<div class="loglist-info">
					<a href="'.$rows[$i][2].'" class="loglist-text">'.$rows[$i][2].'</a>
				</div>
				<div class="loglist-info">
					<a href="'.$rows[$i][3].'" class="loglist-text">'.$rows[$i][3].'</a>
				</div>
				<div class="loglist-details">
					<span class="loglist-text">'.$rows[$i][4].'</span>
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