<?php
define('DB_DRIVER','mysql');
define('DB_USERNAME', '');
define('DB_PASSWORD','');
define('DB_HOST','127.0.0.1');
define('DB_DATABASE','');

define('TIMEOUT', 15);		// in minutes
define('SSID','');

define('TWITTER_URL','http://identi.ca/api/statuses/update.xml');
define('TWITTER_USERNAME','');
define('TWITTER_PASSWORD','');
define('TWEETS_DISABLED', false);

define('IRC_CHANNEL','#channel');


/**
 * ap definitions
 */

$_APS[0] = array (
		"name" => "hp ap",
  	"type" => "hp_ap530",
	"config" => array (
		"url" => "http://USERNAME:PASSWORD@IP/admin.cgi?action=station_statistics",
		"radio" => array(
			"1"	=> "802.11g",
			"2" => "802.11a"			
		)
	)
);

$_APS[1] = array (
	"name" => "mikrotik",
	"type" => "mikrotik",
	"config" => array (
		"ip" => "IP",
		"snmp" => "COMMUNITY",
		"ssid" => array (
			"wlan1" => "SSID-A",
			"wlan2" => "SSID-B"
		),
		"radio" => array (
			"wlan1" => "802.11g",
			"wlan2" => "802.11a"	
		)
	)
);

/**
 * Custom notify
 */
function customNotify($state) {
	return true;
}