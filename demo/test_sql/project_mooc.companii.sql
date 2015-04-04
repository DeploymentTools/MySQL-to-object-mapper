CREATE TABLE `companii` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nume` varchar(255) DEFAULT NULL,
  `url` varchar(100) DEFAULT NULL,
  `domeniu` varchar(100) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `telefon` varchar(100) DEFAULT NULL,
  `judet` varchar(100) DEFAULT NULL,
  `localitate` varchar(100) DEFAULT NULL,
  `strada` varchar(255) DEFAULT NULL,
  `nr` varchar(20) DEFAULT NULL,
  `reprezentant` varchar(255) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `parola` varchar(60) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;
