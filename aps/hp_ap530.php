<?php
/**
 * Module for HP ProCurve AP530
 * 
 * Config options:
 * 
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
	
 */
class ap_hp_ap530 {
	
	private $config;
	
	public function __construct($config) {
		$this->config = $config;
	}
	
	public function get_macs() {
		$ret = @file_get_contents($this->config["url"]);
		if (trim($ret) == "") {
			echo "could not get data from url...\n";
			return false;
		}
		
		$macs = array();
		
		preg_match_all("'<tr class=\"eventablerow\">(.*?)</tr>'si",$ret,$out);
		foreach ($out[1] as $row) {
			preg_match_all("'<td class=\"section-cell\" align=\"center\">(.*?)</td>'si",$row,$cells);
			
			$radio = trim($cells[1][0]);
			$ssid = trim($cells[1][1]);
			$mac = trim($cells[1][2]);
			
			$tmp = array (
				"mac_address" => $mac,
				"ssid" => $ssid,
				"radio" => $this->config["radio"][$radio]
			);
			
			$macs[] = $tmp;
		}
		
		return $macs;	
	}
}