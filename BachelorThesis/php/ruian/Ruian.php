<?php

require_once('XmlRuian.php');
require_once('RuianDatabase.php');
$dbRuian = new RuianDatabase();
$XmlRuian = new XmlRuian();
$data_to_insert = array();
if (count($argv) > 1) { //osetrenie parametrov
	echo ("Skript podporuje iba následujúce konfigurácie spustenia:");
	echo ("php Ruian.php        pre naplnenie databázy posledným mesačným stavovým súborom");
	echo ("php Ruian.php --update pre aktualizáciu databázy pomocou zmenových súborov od poslednej aktualizácie pod dnešný dátum");
} elseif (isset($argv[1]) && argv[1] == 'update') {
	if ($dbRuian->getLastLog() == false) {
		echo ("Najprv musíte skript spustiť bez parametrov pre úvodné naplnenie daatabázy pomocou posledného mesačného stavového súboru");
	}
	$ruianStructure = $XmlRuian->parseUpdateXml($dbRuian->getLastLog());  //naplnenie struktúry pre databázu zmenovými súbormi od dátumu poslednej aktualizácie po súčasnostť

	foreach ($ruianStructure as $ruianStr) {
		foreach ($ruianStr as $key => $value) {
			if ($value != array()) {
				$data_to_insert = $dbRuian->prepareData($value, $key, true);   //príprava dát pre uloženie do databázy
				$key_table = key($data_to_insert);
				foreach ($data_to_insert[$key_table] as $value) {
					if (($dbRuian->checkData($value['id'], $key_table)) == false) {
						$dbRuian->insert($value, $key_table);  //uloženie alebo aktualizácia dát podľa existencie záznamu v databáze
					} else {
						$dbRuian->update($value, $key_table);
					}
				}
			}
		}
	}
} elseif (isset($argv[1])) {
	echo ("Skript podporuje iba následujúce konfigurácie spustenia:");
	echo ("php Ruian.php        pre naplnenie databázy posledným mesačným stavovým súborom");
	echo ("php Ruian.php --update pre aktualizáciu databázy pomocou zmenových súborov od poslednej aktualizácie pod dnešný dátum");
} else {
	if ($dbRuian->getLastLog() != false) {
		$dbRuian->truncateAll(); //premazanie celej databázy
	}
	$ruianStructure = $XmlRuian->parseXml();   //naplnenie štruktúry pre databázu posledným mesačným stavovým súborom
	foreach ($ruianStructure as $key => $value) {
		if ($value != array()) {
			$data_to_insert = $dbRuian->prepareData($value, $key);
			$key_table = key($data_to_insert);
			foreach ($data_to_insert[$key_table] as $value) {
				$dbRuian->insert($value, $key_table);
			}
		}
	}

	$dbRuian->insert(array('date' => $XmlRuian->getLogDate()), 'logs');
}

return 0;

/*
	 * To change this license header, choose License Headers in Project Properties.
	 * To change this template file, choose Tools | Templates
	 * and open the template in the editor.
	 */

