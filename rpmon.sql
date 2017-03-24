CREATE TABLE `rpmon` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fecha` datetime DEFAULT '0000-00-00 00:00:00',
  `carga` float(4,2) NOT NULL,
  `usuarios` int(3) NOT NULL,
  `temp` float(5,2) NOT NULL,
  `mem_used` int(5) NOT NULL,
  `mem_total` int(5) NOT NULL,
  `desde` datetime DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

CREATE TABLE `rpmon_day` (
  `fecha` varchar(10) NOT NULL,
  `carga` float(4,2) NOT NULL,
  `usuarios` int(3) NOT NULL,
  `temp` float(5,2) NOT NULL,
  `mem_used` int(5) NOT NULL,
  `mem_total` int(5) NOT NULL,
  PRIMARY KEY (`fecha`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
