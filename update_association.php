<?php
/**
 * Include config
 */
include("config.php");

echo "Updating associations on AP...\n";

/**
 * Setup MySQL connection
 * 
 * TODO: use PDO or something.
 */
if (!@mysql_connect(DB_HOST, DB_USERNAME, DB_PASSWORD)) {
	die("mysql connect failed");
}
if (!@mysql_selectdb(DB_DATABASE)) {
	die("mysql selectdb failed");
}



$ret = file_get_contents(URL);
if (trim($ret) == "") {
	die ("could not get data from url");
}

preg_match_all("'<tr class=\"eventablerow\">(.*?)</tr>'si",$ret,$out);

foreach ($out[1] as $row) {
	preg_match_all("'<td class=\"section-cell\" align=\"center\">(.*?)</td>'si",$row,$cells);
	
	$radio = trim($cells[1][0]);
	$ssid = trim($cells[1][1]);
	$mac = trim($cells[1][2]);

	$event_id = @mysql_result(mysql_query("SELECT id FROM wifi_event WHERE mac_address = '{$mac}' AND part_date = 0"),0,0);
	
	// update
	if ($event_id > 0) {
		mysql_query("UPDATE wifi_event SET last_update = " . time() . " WHERE id = {$event_id}");		
	}
	// insert
	else {
		mysql_query("INSERT INTO wifi_event (mac_address, radio, ssid, join_date, last_update) VALUES ('{$mac}', '{$radio}', '{$ssid}', " . time() . ", " . time() . ")");
		
		echo "JOIN {$mac}\n";
	}
}


// clean up old
$q = mysql_query("SELECT * FROM wifi_event WHERE join_date > 0 AND part_date = 0 AND last_update < " . (time() - (TIMEOUT * 60)));

while ($o = mysql_fetch_object($q)) {
	// set current 0
	mysql_query("UPDATE wifi_event SET part_date = '" . time() . "' WHERE id = {$o->id}");
	
	echo "PART {$o->mac_address}\n";
}

// determine if we're open or not
$count = @mysql_result(mysql_query("SELECT COUNT(id) FROM wifi_event WHERE join_date > 0 AND part_date = 0 AND ssid = '" . SSID . "'"),0,0);
$open = @mysql_result(mysql_query("SELECT open FROM space_state"),0,0);

// open it
if ($count > 0 && !$open) {
	// get first event (only registered users)
	$event = @mysql_fetch_object(mysql_query("SELECT e.*, u.username, u.sex, m.device FROM wifi_event e LEFT JOIN user_mac_address m ON m.mac_address = e.mac_address LEFT JOIN user u ON m.user_id = u.id WHERE e.join_date > 0 AND e.part_date = 0 AND u.username <> '' ORDER BY e.join_date ASC LIMIT 1"));

	if ($event->sex == "male") $own = "his";
	else $own = "her";	
	
	$trigger_message = "User {$event->username} joined SSID {$event->ssid} on Radio {$event->radio} with {$own} {$event->device} at " . date("Y-m-d H:i:s", $event->join_date);
		
	// update it
	mysql_query("UPDATE space_state SET open = 1, trigger_message = '{$trigger_message}'");
	
}
// close it
else if ($count == 0 && $open) {
	// get last event
	$event = @mysql_fetch_object(mysql_query("SELECT e.*, u.username, m.device, u.sex FROM wifi_event e LEFT JOIN user_mac_address m ON m.mac_address = e.mac_address LEFT JOIN user u ON m.user_id = u.id WHERE e.join_date > 0 AND e.part_date = 0 AND u.username <> '' ORDER BY e.part_date DESC LIMIT 1"));
	
	if ($event->sex == "male") $own = "his";
	else $own = "her";
	
	$trigger_message = "User {$event->username} left SSID {$event->ssid} on Radio {$event->radio} with {$own} {$event->device} at " . date("Y-m-d H:i:s", $event->part_date);
	
	// update it
	mysql_query("UPDATE space_state SET open = 0, trigger_message = '{$trigger_message}'");	
}