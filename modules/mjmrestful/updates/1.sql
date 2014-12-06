CREATE TABLE `mjmrestful_apiaccess` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL,
  `device_id` varchar(255) DEFAULT NULL,
  `token_expire_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token_index` (`token`),
  KEY `device_id_index` (`device_id`),
  KEY `customer_id_index` (`customer_id`),
  KEY `token_expire_index` (`token_expire_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;