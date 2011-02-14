<?php 
/*
WNMap
Copyright (C) 2006 Eric Butler <eric@extremeboredom.net>

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

require ("config.php");


header ("Content-Type: text/xml");
$connection = mysql_connect (MYSQL_HOST, MYSQL_USER, MYSQL_PASS) or die ('Could not connect: ' . mysql_error());
mysql_select_db (MYSQL_DB) or die ('Could not select database.');

echo "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n
<map>\n";


/* Calc lat, log delta */
$lat = $_GET["lat"];
$lng = $_GET["lng"];

$lat_max =$lat + 0.1;
$lat_min =$lat - 0.1;

$lng_max = $lng + 0.1;
$lng_min = $lng - 0.1;

/* Push the nodes */
$query = "SELECT * FROM " . MYSQL_NODES_TABLE . " WHERE status IN (1, 2, 3) and lng > $lng_min and lng < $lng_max and lat > $lat_min and lat < $lat_max";
$result = mysql_query ($query, $connection) or die (mysql_error());

//echo "<info>$lat_max:$lat_min</info>";

echo "<nodes>\n";

while ($row = mysql_fetch_assoc($result)) {
	$name = htmlspecialchars($row['nodeName']);
	$owner = htmlspecialchars($row['userRealName']);
	$desc = htmlspecialchars ($row['nodeDescription']);
	$lat = htmlspecialchars($row['lat']);
	$lng = htmlspecialchars($row['lng']);
	$ele = htmlspecialchars($row['elevation']);
	$status = htmlspecialchars($row['status']);
	$ip = htmlspecialchars($row['nodeIP']);

	if ($status == 1)
		$state = "potential";
	else if ($status == 2)
		$state = "active";
	else if ($status == 3)
		$state = "hotspot";

echo "<node name=\"$name\" base64Name=\"" . base64_encode($name) . "\" owner=\"$owner\" lat=\"$lat\" lng=\"$lng\" elevation = \"$ele\" state=\"$state\" description=\"$desc\" ip=\"$ip\" />\n";
}

/* Now push the links */
echo "</nodes>
	<links>";
		
$query = "SELECT * FROM " . MYSQL_LINKS_TABLE . " ORDER BY type DESC";
$result = mysql_query ($query, $connection) or die (mysql_error());

while ($row = mysql_fetch_assoc($result)) {
	$query = "SELECT nodeName FROM " . MYSQL_NODES_TABLE . " WHERE id = '" . $row['node1'] . "'";
	$node_result = mysql_query ($query, $connection) or die (mysql_error());
	$node1row = mysql_fetch_row($node_result);
	$node1name = htmlspecialchars($node1row[0]);

	$query = "SELECT nodeName FROM " . MYSQL_NODES_TABLE . " WHERE id = '" . $row['node2'] . "'";
	$node_result = mysql_query ($query, $connection) or die (mysql_error());
	$node2row = mysql_fetch_row($node_result);
	$node2name = htmlspecialchars($node2row[0]);
	$type = htmlspecialchars($row['type']);
	$quality = htmlspecialchars($row['quality']);

	echo "<link node1=\"" . $node1name . "\" node2=\"" . $node2name . "\" type=\"" . $type . "\" quality=\"" . $quality . "\" />\n";
}

echo "</links>";

mysql_close ($connection);
echo "</map>";

?>

