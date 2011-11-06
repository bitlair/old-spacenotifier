<?php
function getJSON() {
	global $db;
	
	/**
	 * Gather info from db
	 */
	$open = $db->column("SELECT open FROM space_state LIMIT 1");
	$last_join = $db->column("SELECT MAX(join_date) FROM wifi_event LIMIT 1");
	$last_part = $db->column("SELECT MAX(part_date) FROM wifi_event LIMIT 1");
	if ($last_join > $last_part) $last_change = $last_join;
	else $last_change = $last_part;
	
	// get checkins
	$q = $db->query("select `e`.`id` AS `id`,`e`.`mac_address` AS `mac_address`,`e`.`join_date` AS `join_date`,`e`.`part_date` AS `part_date`,`e`.`radio` AS `radio`,`e`.`ssid` AS `ssid`,`e`.`last_update` AS `last_update`,`u`.`username` AS `username`,`u`.`sex` AS `sex`,`m`.`device` AS `device`,`e`.`signal` AS `signal` from ((`wifi_event` `e` left join `user_mac_address` `m` on((`m`.`mac_address` = `e`.`mac_address`))) left join `user` `u` on((`m`.`user_id` = `u`.`id`))) WHERE username <> '' order by `e`.`join_date` DESC LIMIT 5");
	$tmp_events = array();
	
	foreach ($q as $o) {
		if ($o->part_date > 0) {
			$tmp_events[$o->part_date] = array("name"=>$o->username . "'s " . $o->device,"type"=>"check-out");	
		}
		$tmp_events[$o->join_date] = array("name"=>$o->username . "'s " . $o->device,"type"=>"check-in");
	}
	
	krsort($tmp_events);
	$count = 1;
	$events = array();
	
	foreach ($tmp_events as $t => $event) {
		$events[] = array("t"=>$t,"name"=>$event['name'],"type"=>$event['type']);
		if ($count >= 5) break;
		$count++;
	}
	
	
	/**
	 * Output as JSON
	 */
	$reply = array(
		'space'		=> JSON_SPACE,
		'url'		=> JSON_URL,
		'address' 	=> JSON_ADDRESS,
		'phone'		=> JSON_PHONE,
		'cam'		=> JSON_CAM,
		'logo'		=> JSON_LOGO,
		'open'		=> ($open == 1),
		'lastchange'=> intval($last_change),
		'events'	=> $events
	);
	
	return json_encode($reply);
}