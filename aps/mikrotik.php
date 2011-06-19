<?php
/**
 * Module for MikroTik RouterOS
 * 
 * Config options:
 * 
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

 * 	
 */
class ap_mikrotik {
	
	private $config;
	
	public function __construct($config) {
		$this->config = $config;
	}	
	
	public function get_macs() {
		$ip = $this->config["ip"];
		$snmp = $this->config["snmp"];
		
		
		// wireless registration table oid
		$oid_w_tbl = ".1.3.6.1.4.1.14988.1.1.1.2.1.";
		$oid_w_['ifInSignal']   = "3";
		$oid_w_['ifOutOctets']  = "4";
		$oid_w_['ifInOctets']   = "5";
		$oid_w_['ifOutPackets'] = "6";
		$oid_w_['ifInPackets']  = "7";
		$oid_w_['ifOutRate']    = "8";
		$oid_w_['ifInRate']     = "9";
		
		// interface array
		$intdb     = array();
		
		// get interface names and their oid
		$int_info  = snmprealwalk($ip, $snmp, ".1.3.6.1.2.1.2.2.1.2");
		$int_mib   = array_keys($int_info);
		$int_val   = array_values($int_info);
		$int_type  = snmpwalk($ip, $snmp, ".1.3.6.1.2.1.2.2.1.3");
		
		// and stick the wireless interfaces them in an array
		for ($x = 0; $x < count($int_mib); $x++) {
				$interfaces[substr($int_mib[$x], strrpos($int_mib[$x], ".") + 1)] = substr($int_val[$x], strpos($int_val[$x], ":") + 2);
		}
		
		// get wireless client mac addresses
		$cmac_info = snmprealwalk($ip, $snmp, $oid_w_tbl . $oid_w_['ifInSignal']);
		$macs = array();
		
		foreach ($cmac_info as $key => $value) {
			$tmp = explode(".", $key);
			$cnt = count($tmp);
			
			$interface = $tmp[$cnt-1];
			$mac = array();
			$signal = explode(":", $value);
			$signal = trim($signal[1]);
			
			for ($i = ($cnt-7); $i < ($cnt-1); $i++) {
				$octet = dechex($tmp[$i]);
				if (strlen($octet) == 1) {
					$octet = "0" . $octet;
				}
				$mac[] = $octet;
			}
			$mac = implode(":", $mac);
			
			$assoc = array (
				"mac_address" => $mac,
				"ssid" => $this->config["ssid"][$interfaces[$interface]],
				"radio" => $this->config["radio"][$interfaces[$interface]],
				"signal" => $signal . " dBm"
			);
			
			$macs[] = $assoc;
		}
		
		return $macs;
	}
}