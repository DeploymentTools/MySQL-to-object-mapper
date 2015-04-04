CREATE TABLE `cursuri` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nume` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `judet` varchar(100) DEFAULT NULL,
  `localitate` varchar(100) DEFAULT NULL,
  `adresa` varchar(255) DEFAULT NULL,
  `tip_curs` varchar(50) DEFAULT NULL,
  `data_start` date DEFAULT NULL,
  `data_stop` date DEFAULT NULL,
  `partenerID` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;
