-- phpMyAdmin SQL Dump
-- version 3.3.3
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: May 29, 2011 at 04:21 PM
-- Server version: 5.0.51
-- PHP Version: 5.3.2

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `bitlair`
--

-- --------------------------------------------------------

--
-- Table structure for table `space_state`
--

CREATE TABLE IF NOT EXISTS `space_state` (
  `open` tinyint(1) NOT NULL,
  `trigger_message` varchar(255) NOT NULL,
  PRIMARY KEY  (`open`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL auto_increment,
  `username` varchar(64) NOT NULL,
  `real_name` varchar(64) NOT NULL,
  `sex` enum('male','female','yes please') NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `user_mac_address`
--

CREATE TABLE IF NOT EXISTS `user_mac_address` (
  `user_id` int(11) NOT NULL,
  `mac_address` varchar(32) NOT NULL,
  `device` varchar(32) NOT NULL default 'Laptop',
  PRIMARY KEY  (`user_id`,`mac_address`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `wifi_event`
--

CREATE TABLE IF NOT EXISTS `wifi_event` (
  `id` int(11) NOT NULL auto_increment,
  `mac_address` varchar(32) NOT NULL,
  `join_date` int(20) NOT NULL,
  `part_date` int(20) NOT NULL,
  `radio` tinyint(1) NOT NULL,
  `ssid` varchar(64) NOT NULL,
  `last_update` int(20) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `user_mac_address`
--
ALTER TABLE `user_mac_address`
  ADD CONSTRAINT `user_mac_address_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
