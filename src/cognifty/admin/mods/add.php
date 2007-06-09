<?php

class Cgn_Service_Mods_Add extends Cgn_Service {

	function mainEvent(&$cc, &$t) {
		//do nothing right now
		// installed modules loaded in systemPretemplate
	}



	function uploadEvent(&$cc, &$t) {
		$zip = zip_open($cc->uploads['new_mod']['tmp_name']);
		$t['newModule'] = $cc->uploads['new_mod'];

		 if ($zip) {
		 
		    while ($zip_entry = zip_read($zip)) {

			if ( strstr(zip_entry_name($zip_entry), "setup.ini") ) {
				if (zip_entry_open($zip, $zip_entry, "r")) {
				    $t['iniFile'] = parse_ini_str(zip_entry_read($zip_entry, zip_entry_filesize($zip_entry)));
				    zip_entry_close($zip_entry);
				}
			}

		    }
		 
		    zip_close($zip);
		    move_uploaded_file(
				$cc->uploads['new_mod']['tmp_name'],
				LC_ROOT."deploy/".$cc->uploads['new_mod']['name']);
		}

		//do the deployment
		exec("unzip -d ".MODULE_PATH." ".
			LC_ROOT."deploy/".$cc->uploads['new_mod']['name']);


		//install into lc_registry
		$reg = new LcRegistry();

		//split up ini file entries into a usable array
		for ($x=0; $x < count($t['iniFile']['Registry']); ++$x) { 
			if ($t['iniFile']['Registry'][$x]['NAME'] == 'mid') {
				$reg->set('mid',$t['iniFile']['Registry'][$x]['VALUE']);
				$reg->set('moduleName',$t['iniFile']['Registry'][$x]['VALUE']);
			}
			if ($t['iniFile']['Registry'][$x]['NAME'] == 'author') {
				$reg->set('author',$t['iniFile']['Registry'][$x]['author']);
			}

			if ($t['iniFile']['Registry'][$x]['NAME'] == 'displayName') {
				$reg->set('displayName',$t['iniFile']['Registry'][$x]['displayName']);
			}

		}
		$reg->set('installedOn',time());
		$reg->save();

	}



	function getMenu() {

		$m = array();
		$menuItem->module = 'mods';
		$menuItem->service = 'add';
		$menuItem->linkText = 'Add Module';
		$m[] = $menuItem;

		return $m;
	}
}



function parse_ini_str($Str,$ProcessSections = TRUE) {
	$Section = NULL;
	$Data = array();
	if ($Temp = strtok($Str,"\r\n")) {
		do {
			switch ($Temp{0}) {
				case ';':
				case '#':
				break;
				case '[':
					if (!$ProcessSections) {
						break;
					}
					$Pos = strpos($Temp,'[');
					$Section = substr($Temp,$Pos+1,strpos($Temp,']',$Pos)-1);
					$Data[$Section] = array();
					break;
				default:
					$Pos = strpos($Temp,'=');
					if ($Pos === FALSE) {
						break;
					}
					$Value = array();
					$Value["NAME"] = trim(substr($Temp,0,$Pos));
					$Value["VALUE"] = trim(substr($Temp,$Pos+1),' "');

					if ($ProcessSections) {
						$Data[$Section][] = $Value;
					}
					else {
						$Data[] = $Value;
					}
					break;
			}
		} while ($Temp = strtok("\r\n"));
	}
	return $Data;
}

?>
