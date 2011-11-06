<?php
error_reporting(0);

/**
 * Includes
 */
include("config.php");
include("common.php");
include("db.php");

echo "Updating associations on AP(s)...\n";

/**
 * Setup db connection
 */
$db = DB::get();

/**
 * Update assocations on the AP's in the config.
 */
$joins = array();
$parts = array();

foreach ($_APS as $_AP) {
	$module_file = "aps/" . $_AP["type"] . ".php";
	if (file_exists($module_file)) {
		require_once($module_file);
		
		$class = "ap_" . $_AP["type"];
		if (class_exists($class)) {
			$module = new $class ($_AP["config"]);
			
			echo "Pulling macs from '{$_AP["name"]}'...\n";
			$macs = $module->get_macs();
			
			foreach ($macs as $mac) {
				$event_id = $db->column("SELECT id FROM wifi_event WHERE mac_address = ? AND part_date = 0", $mac["mac_address"]);
				
				// update
				if ($event_id > 0) {
					$update = array (
						"radio" => $mac["radio"],
						"ssid" => $mac["ssid"],
						"signal" => $mac["signal"],
						"last_update" => time()
					);					
					
					$db->update("wifi_event",$update, "id = ?", $event_id);
				}
				// insert
				else {
					
					$insert = array (
						"mac_address" => $mac["mac_address"],
						"radio" => $mac["radio"],
						"ssid" => $mac["ssid"],
						"signal" => $mac["signal"],
						"join_date" => time(),
						"last_update" => time()
					);
					
					$db->insert("wifi_event", $insert);
					
					echo "JOIN {$mac["mac_address"]}\n";
					$join[] = $insert;
				}
				
			}
		}
		else {
			echo "Error: class {$class} does not exist!\n";
		}
		
	}
	else {
		echo "Error: {$module_file} does not exist!\n";
	}
	
}


// clean up old
$q = $db->query("SELECT * FROM wifi_event WHERE join_date > 0 AND part_date = 0 AND last_update < " . (time() - (TIMEOUT * 60)));

foreach ($q as $o) {
	// set current 0
	$db->update("wifi_event", array ("part_date" => time()), "id = ?", $o->id);
	
	echo "PART {$o->mac_address}\n";
	$part[] = array (
		"mac_address" => $o->mac_address,
		"radio" => $o->radio,
		"ssid" => $o->ssid,
		"signal" => $o->signal
	);
}

// determine if we're open or not
$count = $db->column("SELECT COUNT(id) FROM wifi_event e, user_mac_address m WHERE e.join_date > 0 AND e.part_date = 0 AND e.mac_address = m.mac_address");
$open = $db->column("SELECT open FROM space_state");

// open it
if ($count > 0 && !$open) {
	// get first event (only registered users)
	$event = $db->row("SELECT e.*, u.username, u.sex, m.device FROM wifi_event e LEFT JOIN user_mac_address m ON m.mac_address = e.mac_address LEFT JOIN user u ON m.user_id = u.id WHERE e.join_date > 0 AND e.part_date = 0 AND u.username <> '' ORDER BY e.join_date ASC LIMIT 1");
	
	if ($event->signal != "") $signal = " (signal $event->signal) ";
	$trigger_message = "User {$event->username} joined SSID {$event->ssid} @ {$event->radio}{$signal} with {$gender_convert[$event->sex]} {$event->device} at " . date("Y-m-d H:i:s", $event->join_date);
		
	// update it
	$db->update("space_state", array("open"=>1, "trigger_message"=>$trigger_message), "open = ?", 0);
	
	// tweet it
	tweet("We are OPEN! " . $trigger_message, TWITTER_USERNAME, TWITTER_PASSWORD, TWITTER_URL);
	
	// custom notify
	customNotify(1);	
}
// close it
else if ($count == 0 && $open) {
	// get last event
	$event = $db->row("SELECT e.*, u.username, m.device, u.sex FROM wifi_event e LEFT JOIN user_mac_address m ON m.mac_address = e.mac_address LEFT JOIN user u ON m.user_id = u.id WHERE e.join_date > 0 AND e.part_date > 0 AND u.username <> '' ORDER BY e.part_date DESC LIMIT 1");
	
	if ($event->signal != "") $signal = " (signal $event->signal) ";
	$trigger_message = "User {$event->username} left SSID {$event->ssid} @ {$event->radio}{$signal} with {$gender_convert[$event->sex]} {$event->device} at " . date("Y-m-d H:i:s", $event->part_date);
	
	// update it
	$db->update("space_state", array("open"=>0, "trigger_message"=>$trigger_message), "open = ?", 1);
	
	// tweet it
	tweet("We are closed. " . $trigger_message, TWITTER_USERNAME, TWITTER_PASSWORD, TWITTER_URL);	
	
	// custom notify
	customNotify(0);
}

// do IRC join/parts
$what = array("join","part");
$event_count = 0;
foreach ($what as $w) {
	foreach ($$w as $row) {
		$user = $db->row("SELECT u.id, u.username, u.sex, m.device FROM user_mac_address m, user u WHERE m.mac_address = ? AND m.user_id = u.id", $row["mac_address"]);
		if ($user->id > 0) {
			$signal = $row["signal"];
			if ($signal != "") $signal = ", signal " . $signal;
			ircNotify($user->username . " " . $w . "s with " . $gender_convert[$user->sex]  . " " . $user->device . " @ {$row["ssid"]} ({$row["radio"]})" . $signal);
			$event_count++;
		}
	}
}

if ($event_count > 0) {
	customNotifyEvent();
}

echo "done\n";