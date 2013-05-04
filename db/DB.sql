CREATE TABLE `UA_Users` (
  `UserID` varchar(255) DEFAULT NULL,
  `UserName` varchar(255) DEFAULT NULL,
  `UserPass` varchar(255) DEFAULT NULL,
  `UserMapID` int(10) NOT NULL,
  `Remarks` varchar(255) DEFAULT NULL,
  `LoginCount` int(10) DEFAULT '0',
  `LastLoginTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Registered` tinyint(1) NOT NULL,
  `Activated` tinyint(1) NOT NULL,
  PRIMARY KEY (`UserMapID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
INSERT INTO `UA_Users` VALUES ('admin','Administrator','ceb6c970658f31504a901b89dcd3e461',0,NULL,14,'2013-05-03 22:47:45',1,1);
CREATE TABLE `UA_Visitors` (
  `ip` tinytext CHARACTER SET latin1,
  `vtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `vpage` tinytext CHARACTER SET latin1,
  `referrer` tinytext CHARACTER SET latin1,
  `uagent` text CHARACTER SET latin1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE `UA_Visits` (
  `PageID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `PageURL` text NOT NULL,
  `VisitCount` bigint(20) NOT NULL DEFAULT '1',
  `LastVisit` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `PageTitle` text,
  `VisitorIP` text NOT NULL,
  PRIMARY KEY (`PageID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE `UA_logs` (
  `LogID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `SessionID` varchar(20) DEFAULT NULL,
  `IP` varchar(15) DEFAULT NULL,
  `Referrer` longtext,
  `UserAgent` longtext,
  `UserMapID` int(10) DEFAULT NULL,
  `URL` longtext,
  `Action` longtext,
  `Method` varchar(10) DEFAULT NULL,
  `URI` longtext,
  `AccessTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`LogID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;