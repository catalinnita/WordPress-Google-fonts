<?php
$sql = "CREATE TABLE IF NOT EXISTS `es_fonts` (
				 	`ID` bigint(20) NOT NULL AUTO_INCREMENT,
				  `FontName` varchar(255) NOT NULL,
				  `FontPath` varchar(255) NOT NULL,
				  `Installed` int(2) NOT NULL,
				  PRIMARY KEY (`ID`)
				) ENGINE=MyISAM  DEFAULT CHARSET=latin1;";
$wpdb->query($sql);					
				
$sql = "CREATE TABLE IF NOT EXISTS `es_fvariants` (
				  `ID` bigint(20) NOT NULL AUTO_INCREMENT,
				  `VariantName` varchar(255) NOT NULL,
				  PRIMARY KEY (`ID`)
				) ENGINE=MyISAM  DEFAULT CHARSET=latin1;"; 
$wpdb->query($sql);					
				
$sql = "CREATE TABLE IF NOT EXISTS `es_fonts_variants` (
				  `ID` bigint(20) NOT NULL AUTO_INCREMENT,
				  `FontID` bigint(20) NOT NULL,
				  `VariantID` bigint(20) NOT NULL,
				  PRIMARY KEY (`ID`)
				) ENGINE=MyISAM  DEFAULT CHARSET=latin1;";
$wpdb->query($sql);					
				
$sql = "CREATE TABLE IF NOT EXISTS `es_fsets` (
				  `ID` bigint(20) NOT NULL AUTO_INCREMENT,
				  `SetName` varchar(255) NOT NULL,
				  PRIMARY KEY (`ID`)
				) ENGINE=MyISAM  DEFAULT CHARSET=latin1;";
$wpdb->query($sql);	
				
$sql = "CREATE TABLE IF NOT EXISTS `es_fonts_sets` (
				  `ID` bigint(20) NOT NULL AUTO_INCREMENT,
				  `FontID` bigint(20) NOT NULL,
				  `SetID` bigint(20) NOT NULL,
				  PRIMARY KEY (`ID`)
				) ENGINE=MyISAM  DEFAULT CHARSET=latin1;";
$wpdb->query($sql);	
?>