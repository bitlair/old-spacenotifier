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
 * Misc config
 */
$gender_convert = array(
	"male" => "his",
	"female" => "her"
);

/**
 * Custom notify
 */
function customNotify($state) {
	return true;
}

function customNotifyEvent() {
	return true;
}

/**
 * JSON variables
 */
define ('JSON_API','0.9');		// version
define ('JSON_SPACE','spacename');
define ('JSON_URL','https://space.url/');
define ('JSON_ADDRESS','Address, Zip City, Country');
define ('JSON_PHONE','+CCNNNNNNNNN');
define ('JSON_CAM','http://cam.url/');
define ('JSON_LOGO','https://logo.url/');
define ('JSON_IRC','irc://irc.server.com/channel');
define ('JSON_TWITTER', '@twitteraccount');
define ('JSON_EMAIL', 'address@domain.com');
define ('JSON_ML', 'ml@domain.com');		// mailinglist
define ('JSON_LAT', 0.0);		// float latitude
define ('JSON_LON', 0.0);		// float longitude
define ('JSON_STREAM', 'http://stream.url');