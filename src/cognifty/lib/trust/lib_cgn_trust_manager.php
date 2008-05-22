<?

/**
 * Chain of repsonisbility to run trust plugins and tally a score
 */
class Cgn_Trust_Manager {

	var $score       = 0;
	var $plugins     = array();
	var $screenPosts = false;
	var $screenGets  = false;
	var $screenHeads = false;
	var $hitRules    = array();

	function Cgn_Trust_Manager() {

	}

	function screenPosts() {
		$this->screenPosts = true;
	}

	function screenGets() {
		$this->screenGets = true;
	}

	function initPlugin($name,$args) {
		$_name = 'Cgn_Trust_'.ucfirst($name);
		$_plugin = new $_name();
		$_plugin->init($args);
		$this->plugins[] = $_plugin;
	}

	/**
	 * TODO: fix this so it defaults to false, and screenPosts==true triggers plugins
	 */
	function runPlugins(&$req) {
		if ($this->screenPosts) {
			$postSize = count($req->postvars);
			if ($postSize == 0) { return 0; }
		}
		$_size = count($this->plugins);
		$score = 0;
		for ($x=0;$x<$_size;$x++) {
			if (!$this->plugins[$x]->run($req)) {
				$score += $this->plugins[$x]->points;
				//echo "Failed: ".get_class($this->plugins[$x])."\n<br/>\n";
				$this->hitRules[] = $this->plugins[$x]->reason;
			}
		}
		return $score;
	}

	function scoreRequest(&$req) {
		$this->score = $this->runPlugins($req);
		if(! $req->getUser()->isAnonymous()) {
			$this->score -= 1;
		}
		return $this->score;
	}
}

class Cgn_Trust_RequireCookie {

	var $points = 2;
	var $reason = 'REQUIRES_COOKIE';

	function run($req) {
		return isset($_COOKIE['CGNSESSION']);
	}

	function init($args) {
	}
}


class Cgn_Trust_Throttle {

	var $points = 2;
	var $time   = 5;
	var $reason = 'REQUEST_THROTTLE';

	/**
	 * Get the last request time from the session
	 */
	function run($req) {
		if (! isset($_SESSION['_lastTouch'])) {
			return false;
		}
		if ( ($_SESSION['_touch'] - $_SESSION['_lastTouch']) < $this->time) {
			return false;
		}
		return true;
	}

	function init($args) {
		if ( isset($args['time']) ) {
			$this->time = $args['time'];
		}
		if ( isset($args[0]) ) {
			$this->time = $args[0];
		}
	}
}

class Cgn_Trust_Html {

	var $points       = 3;
	var $percentHtml  = 10;
	var $countHttp    = 10;
	var $reason       = 'HTML_PERCENTAGE';

	function run($req) {
		foreach ($req->postvars as $val) {
			$fullCount = strlen($val);
			$stripCount = strlen(strip_tags($val));
			$diff = $fullCount - $stripCount;
//			var_dump($fullCount);//exit();
//			var_dump($diff);exit();
			if ($diff && (($diff / $fullCount * 100) > $this->percentHtml)) {
				return false;
			}

			if (substr_count($val, 'http') > $this->countHttp ) {
				return false;
			}
		}
		return true;
	}

	function init($args) {
		if ( isset($args[0]) ) {
			$this->percentHtml = $args[0];
		}
	}
}

class Cgn_Trust_SecureForm {

	var $reason = 'SECURE_FORM';

	function run($req) {
		return false;
	}

	function init($args) {
	}
}
?>
