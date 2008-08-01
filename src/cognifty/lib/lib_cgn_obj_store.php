<?php


class Cgn_ObjectStore {


	var $objStore = array();
	var $objRefByName = array();
	public static $singleton;


	/**
	 * Initialize the singleton
	 */
	function init() {
		if (!isset(Cgn_ObjectStore::$singleton)) {
			$x = new Cgn_ObjectStore();
			Cgn_ObjectStore::$singleton = $x;
		}
	}


	function getMethodName($uri) {
		$scheme = Cgn_ObjectStore::getScheme($uri);
		$host = Cgn_ObjectStore::getHost($uri);
		$path = Cgn_ObjectStore::getPath($uri);

		$x =& Cgn_ObjectStore::$singleton;
		if (! isset( $x->objStore[$scheme][$host.$path.'_method']) ) {
			trigger_error("No resource found for: ".$uri);
		}
		return $x->objStore[$scheme][$host.$path.'_method'];
	}


	static function &getObject($uri) {
		$uriParts = @parse_url($uri);
		$scheme = $uriParts['scheme'];
		$host   = $uriParts['host'];
		if (isset($uriParts['path'])) {
			$host .= substr(@$uriParts['path'],1);
		}

		$x =& Cgn_ObjectStore::$singleton;

		if ($scheme !== 'object') {
			return $x->objStore[$scheme][$host];
		}
		if (! isset( $x->objStore[$scheme][$host]) ) {
			trigger_error("No resource found for: ".$uri);
		}
		if ($x->objStore[$scheme][$host] === 'ref' &&
				isset($x->objRefByName[$host])) {
			return $x->objRefByName[$host];
		} elseif (is_array($x->objStore[$scheme][$host]) &&
				isset($x->objRefByName[$host])) {
			return $x->objRefByName[$host];
		} else {
			if (!isset($x->objStore[$scheme][$host]['name'])) {
				return NULL;
			}
			$filename = Cgn_ObjectStore::getRealFilename($x->objStore[$scheme][$host]['file']);
			//setup the object
			include_once($filename);
			$class = $x->objStore[$scheme][$host]['class'];
			$name = $x->objStore[$scheme][$host]['name'];
			$obj = new $class();
			Cgn_ObjectStore::storeObject('object://'.$name, $obj);
			//Cgn_ObjectStore::storeObject($obj, $name);

			return $obj;
		}
	}


	function &getObjectByConfig($uri) {
		$scheme = Cgn_ObjectStore::getScheme($uri);
		$host = Cgn_ObjectStore::getHost($uri);
		$path = Cgn_ObjectStore::getPath($uri);

		$x =& Cgn_ObjectStore::$singleton;
		$val = $x->getConfig($uri);
		$classLoaderPackage = explode(':',$val);
		if (! isset( $x->objStore['object'][$classLoaderPackage[2]]) ) {
			trigger_error("No resource found for: ".$uri);
		}

		$obj =& $x->objStore['object'][$classLoaderPackage[2]];
		return $obj;
	}


	function &getMethodByConfig($uri) {
		$scheme = Cgn_ObjectStore::getScheme($uri);
		$host = Cgn_ObjectStore::getHost($uri);
		$path = Cgn_ObjectStore::getPath($uri);

		$x =& Cgn_ObjectStore::$singleton;
		$val = $x->getConfig($uri);
		$classLoaderPackage = explode(':',$val);
		if (! isset( $x->objStore['object'][$classLoaderPackage[2]]) ) {
			trigger_error("No resource found for: ".$uri);
		}

		$method =& $x->objStore['object'][$classLoaderPackage[2].'_method'];
		return $method;
	}


	static function storeObject($uri,&$ref) {
		//exmpale objectref://name
		$uriParts = @parse_url($uri);
		$scheme = $uriParts['scheme'];
		$host   = $uriParts['host'];
		$path   = '';
		if (isset($uriParts['path'])) {
			$path   = substr(@$uriParts['path'],1);
		}

		$x =& Cgn_ObjectStore::$singleton;
		if ($path != '') {
			if ($scheme === 'object') {
				$x->objStore[$scheme][$host][$path] = 'ref';
				$x->objRefByName[$host][$path] =& $ref;
			} else {
				$x->objStore[$scheme][$host][$path] =& $ref;
			}
		} else {
			if ($scheme === 'object') {
				$x->objStore[$scheme][$host] = 'ref';
				$x->objRefByName[$host] =& $ref;
			} else {
				$x->objStore[$scheme][$host] =& $ref;
			}
		}
	}


	static function storeValue($uri,&$ref) {
		//exmpale object://key/key2/name
		$scheme = Cgn_ObjectStore::getScheme($uri);
		$host = Cgn_ObjectStore::getHost($uri);
		$path = Cgn_ObjectStore::getPath($uri);
		$x =& Cgn_ObjectStore::$singleton;
		if ($path!='') { 
			$x->objStore[$scheme][$host][$path] = $ref;
		} else { 
			$x->objStore[$scheme][$host] = $ref;
		}
	}


	static function &getArray($uri) {
		$scheme = Cgn_ObjectStore::getScheme($uri);
		$host = Cgn_ObjectStore::getHost($uri);
		$path = Cgn_ObjectStore::getPath($uri);

		$x =& Cgn_ObjectStore::$singleton;
		if ($path != '') {
			$retArray = array();
			$pathLen = strlen($path);
			if (substr($path,-1) !== '/') {
				$pathLen += 1;
			}
			foreach ($x->objStore[$scheme][$host] as $k =>$v ) {
				//echo "\n".$path. " " . $k;
				if (strpos($k, $path) === 0) {
					$retArray[substr($k,$pathLen)] = $v;
				}
			}
			return $retArray;
		} else {
			return $x->objStore[$scheme][$host];
		}
	}

	static function setArray($uri, &$ar) {
		$scheme = Cgn_ObjectStore::getScheme($uri);
		$host = Cgn_ObjectStore::getHost($uri);
		$path = Cgn_ObjectStore::getPath($uri);

		if (substr($path,-1) === '/') {
			$path = substr($path,0,-1);
		}

		$x =& Cgn_ObjectStore::$singleton;
		if ($path != '') {
			foreach ($ar as $k=>$v) {
				$x->objStore[$scheme][$host][$path.'/'.$k] = $v;
			}
		} else {
			$x->objStore[$scheme][$host] = $ar;
		}
	}

	static function unsetArray($uri) {
		$scheme = Cgn_ObjectStore::getScheme($uri);
		$host = Cgn_ObjectStore::getHost($uri);
		$path = Cgn_ObjectStore::getPath($uri);

		$x =& Cgn_ObjectStore::$singleton;
		$q = $x->objStore[$scheme][$host];
		if (! is_array($q)) {
			return true;
		}
		foreach ($q as $key => $val) {
			unset($val);
			unset($x->objStore[$scheme][$host][$key]);
		}
		return true;
	}

	static function clearConfig($uri) {
		$uriParts = @parse_url($uri);
		$scheme = $uriParts['scheme'];
		$host   = $uriParts['host'];
		$path   = '';
		$x =& Cgn_ObjectStore::$singleton;
		if ($path!='') { 
			$x->objStore[$scheme][$host][$path] = NULL;
			unset($x->objStore[$scheme][$host][$path]);
		} else { 
			$x->objStore[$scheme][$host] = NULL;
			unset($x->objStore[$scheme][$host]);
		}
	}

	static function &getString($uri) {
		$string = Cgn_ObjectStore::getConfig($uri);
		return $string;
	}

	static function storeConfig($uri,&$ref) {
		//exmpale object://key/key2/name
		$uriParts = @parse_url($uri);
		$scheme = $uriParts['scheme'];
		$host   = $uriParts['host'];
		if (isset($uriParts['path'])) {
			$path   = @$uriParts['path'];
			$path   = substr(@$uriParts['path'],1);
		} else {
			$path = '';
		}
		$x =& Cgn_ObjectStore::$singleton;
//		$x->objStore[$scheme][$host][$path] = $ref;

		if ($path!='') { 
			$x->objStore[$scheme][$host][$path] = $ref;
		} else { 
			$x->objStore[$scheme][$host] = $ref;
		}

	}

	static function &getValue($uri) {
		$scheme = Cgn_ObjectStore::getScheme($uri);
		$host = Cgn_ObjectStore::getHost($uri);
		$path = Cgn_ObjectStore::getPath($uri);

		$x =& Cgn_ObjectStore::$singleton;
		if ($path!='') { 
			if (! isset( $x->objStore[$scheme][$host][$path]) ) {
				trigger_error("No config found for: ".$scheme.'://'.$host.'/'.$path);
			}
			return $x->objStore['config'][$host][$path];
		} else {
			if (! isset( $x->objStore[$scheme][$host]) ) {
				trigger_error("No config found for: ".$scheme.'://'.$host.'/');
			}
			return $x->objStore[$scheme][$host];
		}
	}



	static function &getConfig($uri) {
		$uriParts = @parse_url($uri);
		$scheme = $uriParts['scheme'];
		$host   = $uriParts['host'];
		if (isset($uriParts['path'])) {
			$path   = substr(@$uriParts['path'],1);
		} else {
			$path = '';
		}

		$x =& Cgn_ObjectStore::$singleton;
		if ($path != '') {
			if (! isset( $x->objStore[$scheme][$host][$path]) ) {
				trigger_error("No config found for: ".$scheme.'://'.$host.'/'.$path);
			}
			return $x->objStore[$scheme][$host][$path];
		} else {
			if (! isset( $x->objStore[$scheme][$host]) ) {
				trigger_error("No config found for: ".$scheme.'://'.$host);
			}
			return $x->objStore[$scheme][$host];
		}
	}


	static function hasConfig($uri) {
		$uriParts = @parse_url($uri);
		$scheme = $uriParts['scheme'];
		$host   = $uriParts['host'];
		if (isset($uriParts['path'])) {
			$path   = substr(@$uriParts['path'],1);
		} else {
			$path = '';
		}
		$x =& Cgn_ObjectStore::$singleton;
		if ($path != '' ) {
			return isset( $x->objStore[$scheme][$host][$path]);
		} else {
			return isset( $x->objStore[$scheme][$host]);
		}
	}



	static function getPath($uri) {
		$split = parse_url($uri);

		if (isset($split['path']) ) {
			return substr($split['path'],1);
		} else {
			return '';
		}
	}


	static function getHost($uri) {
		$split = parse_url($uri);
		return $split['host'];
	}


	static function getScheme($uri) {
		$split = parse_url($uri);
		return $split['scheme'];
	}


	static function parseConfig($inifile) {
		$majorSection = basename($inifile,".ini");
		$configs = parse_ini_file(CGN_BOOT_DIR.$inifile,true);

		$libPath = Cgn_ObjectStore::getConfig('config://cgn/path/lib');
		$sysPath = Cgn_ObjectStore::getConfig('config://cgn/path/sys');
		$pluginPath = Cgn_ObjectStore::getConfig('config://cgn/path/plugin');
		$filterPath = Cgn_ObjectStore::getConfig('config://cgn/path/filter');

		foreach ($configs as $section => $struct) {
//			echo "sec=".$section ."<br/>\n";

		foreach ($struct as $key => $val) {
			$key = str_replace('_','/',$key);
			$key = str_replace('.','/',$key);
			$key = $majorSection.'/'.$key;
//			$key = $section.'/'.$key;
			$val = str_replace('@lib.path@',$libPath,$val);
			$val = str_replace('@sys.path@',$sysPath,$val);
			$val = str_replace('@plugin.path@',$pluginPath,$val);
//			echo "key=".$key ."<br/>\n";

			//if the value is a reference to a class, then load the object and
			// save a reference to it
			$classLoaderPackage = explode(':',$val);
			if ($section == 'object' || $section == 'plugins'  || $section == 'filters') {
//			if (count($classLoaderPackage) > 1) {
				//we have a class definition
				Cgn_ObjectStore::includeObject($val);// Cgn_SystemRunner
				//if we have a method name (4th position)
				if ( @strlen($classLoaderPackage[3]) ) {
					Cgn_ObjectStore::storeConfig($section.'://'.$key.'/file',$classLoaderPackage[0]);
					Cgn_ObjectStore::storeConfig($section.'://'.$key.'/class',$classLoaderPackage[1]);
					Cgn_ObjectStore::storeConfig($section.'://'.$key.'/name',$classLoaderPackage[2]);
					Cgn_ObjectStore::storeConfig($section.'://'.$key.'/method',$classLoaderPackage[3]);
					//Cgn_ObjectStore::debug();
				} else {
					//we don't have a method name
					Cgn_ObjectStore::storeConfig($section.'://'.$key,$val);
				}
			} else {
				Cgn_ObjectStore::storeConfig($section.'://'.$key,$val);
			}
		}
		}
	}

	static function getRealFilename($filename='') {
		$libPath = Cgn_ObjectStore::getConfig('config://cgn/path/lib');
		$pluginPath = Cgn_ObjectStore::getConfig('config://cgn/path/plugin');
		$filterPath = Cgn_ObjectStore::getConfig('config://cgn/path/filter');

		$filename = str_replace('@lib.path@', $libPath, $filename);
		$filename = str_replace('@plugin.path@', $pluginPath, $filename);
		$filename = str_replace('@filter.path@', $filterPath, $filename);
		return $filename;
	}

	static function includeObject($objectToken, $scheme='object') {
		$classLoaderPackage = explode(':', $objectToken);

		$fileName = Cgn_ObjectStore::getRealFilename($classLoaderPackage[0]);
		if ($fileName == '') { print_r(debug_backtrace());}
//		$included_files[] = $fileName;
		$s = include_once($fileName);
		if (! $s ) {
			trigger_error("No resource found for: ".$classLoaderPackage[2]);
		}
		$className = $classLoaderPackage[1];
		$tempObj = new $className();
		Cgn_ObjectStore::$singleton->objRefByName[$classLoaderPackage[2]] = $tempObj;
		Cgn_ObjectStore::storeConfig($scheme.'://'.$classLoaderPackage[2].'/file', $classLoaderPackage[0]);
		Cgn_ObjectStore::storeConfig($scheme.'://'.$classLoaderPackage[2].'/class', $classLoaderPackage[1]);
		Cgn_ObjectStore::storeConfig($scheme.'://'.$classLoaderPackage[2].'/name', $classLoaderPackage[2]);
		Cgn_ObjectStore::storeConfig($scheme.'://'.$classLoaderPackage[2].'/method', $classLoaderPackage[3]);
		return $s;
	}


	function debug($section = '') {
		$x = Cgn_ObjectStore::$singleton;
		echo "<pre>\n";
		if ($section != '') {
			var_dump($x->objStore[$section]);
		} else {
			var_dump($x);
		}
		echo "</pre>\n";
	}

	/**
	 * Initialize some of the core classes
	 */
	function wakeup() {
		//kick off lazy loading
		Cgn_ObjectStore::getObject('object://defaultSessionLayer');
	}
}

//$objRef = System::getChachecObjectByName("object://EventListeners/myEmailHandler");

?>
