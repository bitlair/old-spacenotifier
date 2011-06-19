-- phpMyAdmin SQL Dump
-- version 3.2.2.1deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jun 19, 2011 at 09:10 PM
-- Server version: 5.1.37
-- PHP Version: 5.2.10-2ubuntu6.10

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `bitwifi`
--

-- --------------------------------------------------------

--
-- Table structure for table `space_state`
--

CREATE TABLE IF NOT EXISTS `space_state` (
  `open` tinyint(1) NOT NULL,
  `trigger_message` varchar(255) NOT NULL,
  PRIMARY KEY (`open`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL,
  `real_name` varchar(64) NOT NULL,
  `sex` enum('male','female','yes please') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Stand-in structure for view `users_online`
--
CREATE TABLE IF NOT EXISTS `users_online` (
`id` int(11)
,`mac_address` varchar(32)
,`join_date` int(20)
,`part_date` int(20)
,`radio` varchar(32)
,`ssid` varchar(64)
,`last_update` int(20)
,`username` varchar(64)
,`sex` enum('male','female','yes please')
,`device` varchar(32)
,`signal` varchar(16)
);
-- --------------------------------------------------------

--
-- Table structure for table `user_mac_address`
--

CREATE TABLE IF NOT EXISTS `user_mac_address` (
  `user_id` int(11) NOT NULL,
  `mac_address` varchar(32) NOT NULL,
  `device` varchar(32) NOT NULL DEFAULT 'Laptop',
  PRIMARY KEY (`user_id`,`mac_address`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `wifi_event`
--

CREATE TABLE IF NOT EXISTS `wifi_event` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mac_address` varchar(32) NOT NULL,
  `join_date` int(20) NOT NULL,
  `part_date` int(20) NOT NULL,
  `radio` varchar(32) NOT NULL,
  `ssid` varchar(64) NOT NULL,
  `last_update` int(20) NOT NULL,
  `signal` varchar(16) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure for view `users_online`
--
DROP TABLE IF EXISTS `users_online`;

CREATE ALGORITHM=UNDEFINED DEFINER=`bitwifi`@`localhost` SQL SECURITY DEFINER VIEW `users_online` AS select `e`.`id` AS `id`,`e`.`mac_address` AS `mac_address`,`e`.`join_date` AS `join_date`,`e`.`part_date` AS `part_date`,`e`.`radio` AS `radio`,`e`.`ssid` AS `ssid`,`e`.`last_update` AS `last_update`,`u`.`username` AS `username`,`u`.`sex` AS `sex`,`m`.`device` AS `device`,`e`.`signal` AS `signal` from ((`wifi_event` `e` left join `user_mac_address` `m` on((`m`.`mac_address` = `e`.`mac_address`))) left join `user` `u` on((`m`.`user_id` = `u`.`id`))) where ((`e`.`join_date` > 0) and (`e`.`part_date` = 0)) order by `e`.`join_date`;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `user_mac_address`
--
ALTER TABLE `user_mac_address`
  ADD CONSTRAINT `user_mac_address_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
