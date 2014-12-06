CREATE TABLE `mjmrestful_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token_header_name` varchar(255) DEFAULT NULL,
  `token_expire` int(11) DEFAULT NULL,
  `token_device_lock` tinyint(4) DEFAULT NULL,
  `force_https` tinyint(4) DEFAULT NULL,
  `force_correct_request` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


insert  into `mjmrestful_settings`(`id`,`token_header_name`,`token_expire`,`token_device_lock`,`force_https`,`force_correct_request`) values (1,'X-Lemonstand-Api-Token',365,1,1,1);


