<?php
include 'connection.php';
$db = getConnection();
$q = "
SELECT
s.id,
AVG(t.level) as average,
s.name,
la.name
FROM
entries e,
traffic t,
spaces s,
traffic_labels la,
(
	SELECT
	space,
	ROUND(AVG(level)) as rounded
	FROM
	traffic
	GROUP BY
	space
	) a
WHERE
e.entryId = t.entryId";
if (isset($_GET['days'])){
	if (isset($_GET['days']['include'])){
		for ($i = 0; $i < count($_GET['days']['include']['begin']); $i++){
			if (!($_GET['days']['include']['begin'][$i] == "" || $_GET['days']['include']['begin'][$i] == "")){
				$q .= ("
					AND e.time BETWEEN TIMESTAMP('" . $_GET['days']['include']['begin'][$i] . "') AND TIMESTAMP('" . $_GET['days']['include']['end'][$i] . "')");
			}
		}
	}
	if (isset($_GET['days']['exclude'])){
		for ($i = 0; $i < count($_GET['days']['exclude']['begin']); $i++){
			if (!($_GET['days']['exclude']['begin'][$i] == "" || $_GET['days']['exclude']['begin'][$i] == "")){
			$q .= ("
				AND e.time NOT BETWEEN TIMESTAMP('" . $_GET['days']['exclude']['begin'][$i] . "') AND TIMESTAMP('" . $_GET['days']['exclude']['end'][$i] . "')");
			}
		}
	}
}
if (isset($_GET['hours'])){
	if (isset($_GET['hours']['include'])){
		for ($i = 0; $i < count($_GET['hours']['include']['begin']); $i++){
			if (!($_GET['hours']['include']['begin'][$i] == "" || $_GET['hours']['include']['begin'][$i] == "")){
				$q .= ("
					AND HOUR(e.time) BETWEEN " . $_GET['hours']['include']['begin'][$i] . " AND " . $_GET['hours']['include']['end'][$i]);
			}
		}
	}
	if (isset($_GET['hours']['exclude'])){
		for ($i = 0; $i < count($_GET['hours']['exclude']['begin']); $i++){
			if (!($_GET['hours']['exclude']['begin'][$i] == "" || $_GET['hours']['exclude']['begin'][$i] == "")){
			$q .= ("
				AND HOUR(e.time) NOT BETWEEN TIMESTAMP('" . $_GET['hours']['exclude']['begin'][$i] . " AND " . $_GET['hours']['exclude']['end'][$i]);
			}
		}
	}
}

$q .= "
AND t.space = s.id
AND t.space = a.space
AND la.id = a.rounded
GROUP BY
t.space";
$data;
$db_result = $db->query($q);
while ($area = $db_result->fetch_row()) {
	$data[] = array($area[0], (float)$area[1], '<div class="chart-tooltip"><span class="area-title">' . $area[2] . '</span><br><span class="area-avg-label">' . $area[3] . '</span><br><span class="area-avg-value">' . $area[1] . "</span></div>");
}
$data = json_encode($data);
header('Content-Type: application/json');
echo $data;

?>