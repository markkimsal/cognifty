<?php


class Cgn_ObjectStore extends Cgn_Singleton {


	var $objStore = array();


	/**
	 * Initialize the singleton
	 */
	function init() {
		$x = new Cgn_ObjectStore();
		Cgn_ObjectStore::getSingleton($x);
	}


	function getMethodName($uri) {
		$scheme = Cgn_ObjectStore::getScheme($uri);
		$host = Cgn_ObjectStore::getHost($uri);
		$path = Cgn_ObjectStore::getPath($uri);

		$x =& Cgn_ObjectStore::getSingleton();
		if (! isset( $x->objStore[$scheme][$host.$path.'_method']) ) {
			trigger_error("No resource found for: ".$uri);
		}
		return $x->objStore[$scheme][$host.$path.'_method'];
	}


	function &getObject($uri) {
		$scheme = Cgn_ObjectStore::getScheme($uri);
		$host = Cgn_ObjectStore::getHost($uri);
		$path = Cgn_ObjectStore::getPath($uri);

		$x =& Cgn_ObjectStore::getSingleton();
		if (! isset( $x->objStore[$scheme][$host.$path]) ) {
			trigger_error("No resource found for: ".$uri);
		}
		return $x->objStore[$scheme][$host.$path];
	}


	function &getObjectByConfig($uri) {
		$scheme = Cgn_ObjectStore::getScheme($uri);
		$host = Cgn_ObjectStore::getHost($uri);
		$path = Cgn_ObjectStore::getPath($uri);

		$x =& Cgn_ObjectStore::getSingleton();
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

		$x =& Cgn_ObjectStore::getSingleton();
		$val = $x->getConfig($uri);
		$classLoaderPackage = explode(':',$val);
		if (! isset( $x->objStore['object'][$classLoaderPackage[2]]) ) {
			trigger_error("No resource found for: ".$uri);
		}

		$method =& $x->objStore['object'][$classLoaderPackage[2].'_method'];
		return $method;
	}


	function storeObject($uri,&$ref) {
		//exmpale object://key/key2/name
		$scheme = Cgn_ObjectStore::getScheme($uri);
		$host = Cgn_ObjectStore::getHost($uri);
		$path = Cgn_ObjectStore::getPath($uri);

		$x =& Cgn_ObjectStore::getSingleton();
		$x->objStore[$scheme][$host.$path] = $ref;
	}


	function storeValue($uri,&$ref) {
		//exmpale object://key/key2/name
		$scheme = Cgn_ObjectStore::getScheme($uri);
		$host = Cgn_ObjectStore::getHost($uri);
		$path = Cgn_ObjectStore::getPath($uri);
		$x =& Cgn_ObjectStore::getSingleton();
		if ($path!='') { 
			$x->objStore[$scheme][$host][$path] = $ref;
		} else { 
			$x->objStore[$scheme][$host] = $ref;
		}
	}


	function &getArray($uri) {
		$scheme = Cgn_ObjectStore::getScheme($uri);
		$host = Cgn_ObjectStore::getHost($uri);
		$path = Cgn_ObjectStore::getPath($uri);

		$x =& Cgn_ObjectStore::getSingleton();
		return $x->objStore[$scheme][$host];
	}



	function &getString($uri) {
		$string = Cgn_ObjectStore::getConfig($uri);
		return $string;
	}

	function storeConfig($uri,&$ref) {
		//exmpale object://key/key2/name

		$scheme = Cgn_ObjectStore::getScheme($uri);
		$host = Cgn_ObjectStore::getHost($uri);
		$path = Cgn_ObjectStore::getPath($uri);

		$x =& Cgn_ObjectStore::getSingleton();
		$x->objStore['config'][$host][$path] = $ref;
	}

	function &getValue($uri) {
		$scheme = Cgn_ObjectStore::getScheme($uri);
		$host = Cgn_ObjectStore::getHost($uri);
		$path = Cgn_ObjectStore::getPath($uri);

		$x =& Cgn_ObjectStore::getSingleton();
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



	function &getConfig($uri) {
		$scheme = Cgn_ObjectStore::getScheme($uri);
		$host = Cgn_ObjectStore::getHost($uri);
		$path = Cgn_ObjectStore::getPath($uri);

		$x =& Cgn_ObjectStore::getSingleton();
		if (! isset( $x->objStore[$scheme][$host][$path]) ) {
			trigger_error("No config found for: ".$scheme.'://'.$host.'/'.$path);
		}
//		$x->debug();
		return $x->objStore['config'][$host][$path];
	}


	function hasConfig($uri) {
		$scheme = Cgn_ObjectStore::getScheme($uri);
		$host = Cgn_ObjectStore::getHost($uri);
		$path = Cgn_ObjectStore::getPath($uri);

		$x =& Cgn_ObjectStore::getSingleton();
		return isset( $x->objStore[$scheme][$host][$path]);
	}



	function getPath($uri) {
		$split = parse_url($uri);
		if (isset($split['path']) ) {
			return substr($split['path'],1);
		} else {
			return '';
		}
	}


	function getHost($uri) {
		$split = parse_url($uri);
		return $split['host'];
	}


	function getScheme($uri) {
		$split = parse_url($uri);
		return $split['scheme'];
	}


	function parseConfig($inifile) {
		$configs = parse_ini_file('../'.$inifile,true);

		$libPath = Cgn_ObjectStore::getConfig('config://cgn/path/lib');
		$sysPath = Cgn_ObjectStore::getConfig('config://cgn/path/sys');
		$pluginPath = Cgn_ObjectStore::getConfig('config://cgn/path/plugin');
		$filterPath = Cgn_ObjectStore::getConfig('config://cgn/path/filter');

		foreach ($configs as $section => $struct) {
//			echo "sec=".$section ."<br/>\n";
		foreach ($struct as $key => $val) {
			$key = str_replace('_','/',$key);
			$key = str_replace('.','/',$key);
			$key = $section.'/'.$key;
			$val = str_replace('@lib.path@',$libPath,$val);
			$val = str_replace('@sys.path@',$sysPath,$val);
			$val = str_replace('@plugin.path@',$pluginPath,$val);

			//if the value is a reference to a class, then load the object and
			// save a reference to it
			$classLoaderPackage = explode(':',$val);
			if (count($classLoaderPackage) > 1) {
				//we have a class definition
				includeObject($val);// Cgn_SystemRunner
				//if we have a method name (4th position)
				if ( strlen($classLoaderPackage[3]) ) {
					Cgn_ObjectStore::storeConfig('config://'.$key,$val);
					Cgn_ObjectStore::storeObject("object://".$classLoaderPackage[2].'_method',$classLoaderPackage[3]);
				} else {
					//we don't have a method name
					Cgn_ObjectStore::storeConfig('config://'.$key,$val);
				}
			} else {
				Cgn_ObjectStore::storeConfig('config://'.$key,$val);
			}
		}
		}
	}


	function debug() {
		$x =& Cgn_ObjectStore::getSingleton();
		echo "<pre>\n";
		print_r($x);
		echo "</pre>\n";
	}

}

//$objRef = System::getChachecObjectByName("object://EventListeners/myEmailHandler");


/**
 * Holds one instance of the object
 * using the singleton pattern.
 */
class Cgn_Singleton {
	//var $single; //can't use static class variables in PHP4

	/*
	function Cgn_Singleton() {
	}
	*/


	/**
	 * Initialize the singleton
	 */
	function init() {
		//why is this getting called???
		$x = new Cgn_Singleton();
		Cgn_Singleton::getSingleton($x);
		print "********* done \n\n";
	}


	/**
	 * Return the singleton.
	 * First time this function is called with an argument, it will
	 * store the singleton value.
	 */
	function &getSingleton($input=0) {
		static $single;

		if (! isset($single)  && !is_int($input)) {
			$single = $input;
		}

		return $single;
	}
}



?>
