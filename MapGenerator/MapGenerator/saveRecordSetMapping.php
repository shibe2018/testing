<?php
/*************************************************************************************************
 * Copyright 2016 JPL TSolucio, S.L. -- This file is a part of TSOLUCIO coreBOS Customizations.
 * Licensed under the vtiger CRM Public License Version 1.1 (the "License"); you may not use this
 * file except in compliance with the License. You can redistribute it and/or modify it
 * under the terms of the License. JPL TSolucio, S.L. reserves all rights not expressly
 * granted by the License. coreBOS distributed by JPL TSolucio S.L. is distributed in
 * the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. Unless required by
 * applicable law or agreed to in writing, software distributed under the License is
 * distributed on an "AS IS" BASIS, WITHOUT ANY WARRANTIES OR CONDITIONS OF ANY KIND,
 * either express or implied. See the License for the specific language governing
 * permissions and limitations under the License. You may obtain a copy of the License
 * at <http://corebos.org/documentation/doku.php?id=en:devel:vpl11>
 *************************************************************************************************/
include_once 'All_functions.php';
include_once "modules/cbMap/cbMap.php";
require_once 'data/CRMEntity.php';
require_once 'include/utils/utils.php';
include_once 'Staticc.php';

global $root_directory, $log;
$Data = array();

$MapName = $_POST['MapName']; // stringa con tutti i campi scelti in selField1
$MapType = "Record Set Mapping"; // stringa con tutti i campi scelti in selField1
$SaveasMapText = $_POST['SaveasMapText'];
$Data = $_POST['ListData'];
$MapID = explode(',', $_REQUEST['savehistory']);
$mapname = (!empty($SaveasMapText) ? $SaveasMapText : $MapName);
$idquery2 = !empty($MapID[0]) ? $MapID[0] : md5(date("Y-m-d H:i:s") . uniqid(rand(), true));

if (empty($SaveasMapText)) {
	if (empty($MapName)) {
		echo "Missing the name of map Can't save";
		return;
	}
}
if (empty($MapType)) {
	$MapType = "Record Set Mapping";
}

if (!empty($Data)) {
	$jsondecodedata = json_decode($Data);

	if (strlen($MapID[1]) == 0) {
		$focust = new cbMap();
		$focust->column_fields['assigned_user_id'] = 1;
		// $focust->column_fields['mapname'] = $jsondecodedata[0]->temparray->FirstModule."_ListColumns";
		$focust->column_fields['mapname'] = $mapname;
		$focust->column_fields['content'] = add_content($jsondecodedata);
		$focust->column_fields['maptype'] = $MapType;
		// $focust->column_fields['targetname'] =$jsondecodedata[0]->temparray->FirstModule;
		$focust->column_fields['description'] = add_content($jsondecodedata);
		$focust->column_fields['mvqueryid'] = $idquery2;
		$log->debug(" we inicialize value for insert in database ");
		if (!$focust->saveentity("cbMap")) { //
			if (Check_table_if_exist(TypeOFErrors::TABELE_NAME) > 0) {
				echo save_history(add_aray_for_history($jsondecodedata), $idquery2, add_content($jsondecodedata)) . "," . $focust->id;
			} else {
				echo "0,0";
				$log->debug("Error!! MIssing the history Table");
			}
		} else {
			echo "Error!! something went wrong";
			$log->debug("Error!! something went wrong");
		}
	} else {
		include_once "modules/cbMap/cbMap.php";
		$focust = new cbMap();
		$focust->id = $MapID[1];
		$focust->retrieve_entity_info($MapID[1], "cbMap");
		$focust->column_fields['assigned_user_id'] = 1;
		// $focust->column_fields['mapname'] = $MapName;
		$focust->column_fields['content'] = add_content($jsondecodedata);
		$focust->column_fields['maptype'] = $MapType;
		$focust->column_fields['mvqueryid'] = $idquery2;
		// $focust->column_fields['targetname'] =$jsondecodedata[0]->temparray->FirstModule;
		$focust->column_fields['description'] = add_content($jsondecodedata);
		$focust->mode = "edit";
		$focust->save("cbMap");

		if (Check_table_if_exist(TypeOFErrors::TABELE_NAME) > 0) {
			echo save_history(add_aray_for_history($jsondecodedata), $idquery2, add_content($jsondecodedata)) . "," . $MapID[1];
		} else {
			echo "0,0";
			$log->debug("Error!! MIssing the history Table");
		}
	}
}

function add_content($DataDecode) {
	$xml = new DOMDocument("1.0");
	$root = $xml->createElement("map");
	$xml->appendChild($root);
	$records = $xml->createElement("records");

	foreach ($DataDecode as $value) {
		$record = $xml->createElement("record");
		if ($value->temparray->JsonType == "Entity") {
			// echo "ckemi ";
			$id = $xml->createElement("id");
			$idText = $xml->createTextNode("");
			$id->appendChild($idText);

			$module = $xml->createElement("module");
			$moduleText = $xml->createTextNode($value->temparray->FirstModule);
			$module->appendChild($moduleText);

			$value1 = $xml->createElement("value");
			$valueText = $xml->createTextNode($value->temparray->EntityValueId);
			$value1->appendChild($valueText);

			$action = $xml->createElement("action");
			$actionText = $xml->createTextNode($value->temparray->ActionId);
			$action->appendChild($actionText);

			$record->appendChild($id);
			$record->appendChild($module);
			$record->appendChild($value1);
			$record->appendChild($action);
		} else {
			// echo "Hello";
			$id2 = $xml->createElement("id");
			$idText2 = $xml->createTextNode($value->temparray->inputforId);
			$id2->appendChild($idText2);

			$module2 = $xml->createElement("module");
			$moduleText2 = $xml->createTextNode("");
			$module2->appendChild($moduleText2);

			$value2 = $xml->createElement("value");
			$valueText2 = $xml->createTextNode("");
			$value2->appendChild($valueText2);

			$action2 = $xml->createElement("action");
			$actionText2 = $xml->createTextNode($value->temparray->ActionId);
			$action2->appendChild($actionText2);

			$record->appendChild($id2);
			$record->appendChild($module2);
			$record->appendChild($value2);
			$record->appendChild($action2);
		}

		$records->appendChild($record);
	}
	$root->appendChild($records);
	$xml->formatOutput = true;
	return $xml->saveXML();
}

function add_aray_for_history($decodedata) {
	//$countarray=(count($decodedata)-1);
	// $labels="";
	foreach ($decodedata as $value) {
		$labels .= $value->temparray->FirstModule . ",";
	}
	// return $labels;
	return array
	(
	'Labels' => $labels,
	'FirstModuleval' => " ",
	'FirstModuletxt' => " ",
	'SecondModuleval' => " ",
	'SecondModuletxt' => " ",
	'firstmodulelabel' => " ",
	'secondmodulelabel' => " ",
	);
}

function save_history($datas, $queryid, $xmldata) {
	global $adb;
	$idquery = $queryid;

	$q = $adb->query("select sequence from " . TypeOFErrors::TABELE_NAME . " where id='$idquery' order by sequence DESC");
	$seq = $adb->query_result($q, 0, 0);
	if (!empty($seq)) {
		$seq = $seq + 1;
		$adb->query("update " . TypeOFErrors::TABELE_NAME . " set active=0 where id='$idquery'");
		//$seqmap=count($data);
		$adb->pquery("insert into " . TypeOFErrors::TABELE_NAME . " values (?,?,?,?,?,?,?,?,?,?,?)", array($idquery, $datas["FirstModuleval"], $datas["FirstModuletxt"],
		 $datas["SecondModuletxt"], $datas["SecondModuleval"], $xmldata, $seq, 1, $datas["firstmodulelabel"], $datas["secondmodulelabel"], $datas["Labels"]));
		//return $idquery;
	} else {
		//$idquery=md5(date("Y-m-d H:i:s").uniqid(rand(), true));
		$adb->pquery("insert into " . TypeOFErrors::TABELE_NAME . " values (?,?,?,?,?,?,?,?,?,?,?)", array($idquery, $datas["FirstModuleval"], $datas["FirstModuletxt"],
		 $datas["SecondModuletxt"], $datas["SecondModuleval"], $xmldata, 1, 1, $datas["firstmodulelabel"], $datas["secondmodulelabel"], $datas["Labels"]));
	}
	echo $idquery;
}
