<?php

include_once(CGN_LIB_PATH.'/phing/BuildListener.php');

/**
 * Load up an install file and run step by step
 */
class Cgn_Install_Mgr {

	public $phingFile        = '';
	public $phingCommand     = NULL;
	public $newPackageFile   = NULL;
	public $newPackageDir    = NULL;
	public $existingModInfo  = NULL;
	public $newModInfo       = NULL;
	public $taskList         = array();

	/**
	 * Create a modInfo object from the $newPackage variable.
	 *
	 *  if $newPackage is a directory, look for $newPackage/meta.ini
	 *
	 *  if $newPackage is a file, extract it to a temp location (not done yet)
	 *
	 *  if $existingModInfo is passed, then this package will upgrade
	 *  that module
	 */
	public function __construct($newPackage, $existingModInfo = NULL) {


		//find the new package
		if (is_dir($newPackage)) {
			$this->newPackageDir = $newPackage;
			$this->newModInfo    = Cgn_Module_Info::createFromDir($newPackage);
		} else if (is_file($newPackage)) {
			$this->newPackageFile = $newPackage;
			trigger_error("Cannot deal with compressed modules yet.");
			return;
		}

		//create the existing package
		if ($existingModInfo !== NULL) {
			$this->existingModInfo = $existingModInfo;
		} else {
			$this->existingModInfo = new Cgn_Module_Info($this->newModInfo->codeName, 
				$this->newModInfo->isAdmin);
		}

		$this->phingFile = $this->newPackageDir.'install.xml';
	}

	/**
	 * Return true if this module can be installed
	 */
	public function canInstall() {
		if (!$this->_preCheck()) {
			return FALSE;
		}
		return @file_exists($this->phingFile);
	}

	/**
	 * Prepare the phing file for running
	 */
	public function initInstall() {
		$this->phingCommand = new Cgn_Phing_Command();
		$this->phingCommand->commandFile = $this->phingFile;
		$this->phingCommand->initCommand();

		$pr = $this->phingCommand->project;
		$pr->setUserProperty("module.source.dir", $this->newModInfo->fullModulePath);
		$pr->setUserProperty("module.target.dir", $this->existingModInfo->fullModulePath);
	}

	public function listTargets() {
		return array_keys($this->phingCommand->project->getTargets());	
	}


	/**
	 * Return false if the module is installed and needs an upgrade
	 */
	public function isInstallation() {
		return (bool)(!$this->existingModInfo->isInstalled 
			&& (float)$this->newModInfo->availableVersion > (float)$this->existingModInfo->installedVersion);
	}

	/**
	 * If this is an instllation, return target "install", else
	 * find a target upgrade_{$installedVersion}_{$availableVersion}
	 */
	public function getFirstTarget() {
		if (!$this->isInstallation()) {
			return $this->_findUpgradeTarget();
		}
		$targetList = $this->phingCommand->project->getTargets();
		foreach ($targetList as $_t) {
			if ($_t->getName() == 'install') {
				return $_t;
			}
		}
	}

	public function _findUpgradeTarget() {
		$fromVersion = $this->existingModInfo->installedVersion;
		$toVersion   = $this->newModInfo->availableVersion;
		$targetList = $this->phingCommand->project->getTargets();
		//first search for {$installedVersion}_${availableVersion}
		foreach ($targetList as $_t) {
			if ($_t->getName() == 'upgrade_'.$fromVersion.'_'.$toVersion) {
				return $_t;
			}
		}
		//didn't find specific upgrade, look for general upgrade 
		// upgrade_any_{$availableVersion}
		foreach ($targetList as $_t) {
			if ($_t->getName() == 'upgrade_any_'.$toVersion) {
				return $_t;
			}
		}
		return NULL;
	}


	public function getTargetDescription($tname) {
		$targetList = $this->phingCommand->project->getTargets();
		foreach ($targetList as $_t) {
			if ($_t->getName() == $tname) {
				return $_t->getDescription();
			}
		}
		return '';
	}

	/**
	 * Return all the "phingcall" tasks in the current
	 * target (install, upgrade, etc)
	 *
	 * Run "maybeConfigure" on each task.
	 */
	public function getTaskList() {
		if (count($this->taskList) > 0) {
			return $this->taskList;
		}
		$target = $this->getFirstTarget();
		if ($target == NULL) {
			return FALSE;
		}
		foreach ($target->getTasks() as $_t) {
			if (strtolower($_t->getTaskName()) == 'phingcall') {
				$_t->maybeConfigure();
				$this->taskList[] = $_t;
			}
		}
		return $this->taskList;
	}

	/**
	 * Sets the active step which will run 
	 * when performStep is called
	 *
	 * @param Int $step  index into step array starting at 0
	 */
	public function setCurrentStep($step) {
		$this->currStep = (int)$step;
	}

	/**
	 * Prep the target folder and run the current step
	 */
	public function runCurrentStep() {
		//create the target dir if it doesn't exist
		//TODO: this is a waste for all steps, a special 
		//task should be make because CopyTask doesn't support
		//creating the target dir

		if (!is_dir($this->existingModInfo->fullModulePath)
			&& !mkdir($this->existingModInfo->fullModulePath))
		throw new BuildException('Target directory does not exist and cannot be created.');

		$taskList = $this->getTaskList();
		$currStep = $taskList[$this->currStep];

		//add a buildListener to decorate certain tasks
		$this->phingCommand->project->addBuildListener(new Cgn_Phing_Target_Prep());

		$this->phingCommand->runTarget($currStep->subTarget);
	}

	/**
	 * Create or update the install.ini file
	 */
	public function finishInstall() {
		if (!file_exists($this->existingModInfo->fullModulePath.'install.ini')) {
			$this->_createInstallIni();
			if (!$this->_activateModule()) {
				throw new Exception("Cannot activate module.");
			}
		} else {
			$this->_updateInstallIni();
		}
	}

	public function _createInstallIni() {
		$version = $this->newModInfo->availableVersion;
		$fini = fopen($this->existingModInfo->fullModulePath.'install.ini', 'w');
		fputs($fini, "version.number=".$version."\n");
		fputs($fini, "install.date=".date('Y-m-d')."\n");
		fputs($fini, "install.time=".date('h:i:s a')."\n");
		fclose($fini);
	}

	public function _updateInstallIni() {
		$version = $this->newModInfo->availableVersion;
		$iniName = $this->existingModInfo->fullModulePath.'install.ini';
		$lines = explode("\n", file_get_contents($iniName));
		$fini = fopen($iniName, 'w');
		if (!$fini) {
			throw new Exception('Installation cannot complete: install.ini is not writable.');
		}
		//put the lines back 1 by 1 unless line == version.number=?
		foreach ($lines as $_l) {
			$_l = trim($_l);
			if (strpos($_l, 'version.number=') === 0) {
				fputs($fini, "version.number=".$version."\n");
			} else {
				fputs($fini, $_l."\n");
			}
		}
		fclose($fini);
	}


	/**
	 * Utility method for interacting with INI Config file class.
	 * This is usually called after an initial install
	 */
	public function _activateModule() {
		Cgn::loadModLibrary('Mods::Cgn_Config_File', 'admin');
		$mname = $this->existingModInfo->codeName;
		$mpath = $this->existingModInfo->fullModulePath;
		$defaultIni = new Cgn_Config_File('boot/local/default.ini');
		//override.module.mengdict=@sys.path@/local-modules/mengdict/
		return $defaultIni->addOrUpdate('path', 'override.module.'.$mname, $mpath);
	}

	/**
	 * Check target destination as writable
	 * Check target install.ini as writable
	 *
	 * This does not check writing to strange file locations, 
	 * such as media/
	 */
	protected function _preCheck() {
		$install = TRUE;
		$canMakeTarget = file_exists($this->existingModInfo->fullModulePath) || 
			is_writable(dirname($this->existingModInfo->fullModulePath));
		$install = $install && $canMakeTarget;
		if (file_exists($this->existingModInfo->fullModulePath.'install.ini'))
			$install = $install && is_writable($this->existingModInfo->fullModulePath.'install.ini');
		return $install;
	}
}

class Cgn_Phing_Target_Prep implements BuildListener {
	function taskStarted(BuildEvent $event) {
		$taskName = strtolower(get_class($event->getTask()));
		$taskObj  = $event->getTask();
		if (strstr($taskName, 'pdosqlexec')) {
			//set the URL for the pdo task
			$dsn = parse_url(Cgn_ObjectStore::getConfig('dsn://default.uri'));
			$taskObj->setUrl($dsn['scheme'].':host='.$dsn['host'].';dbname='.ltrim($dsn['path'], '/'));
			$taskObj->setUserid($dsn['user']);
			$taskObj->setPassword($dsn['pass']);
		}
	}

    /**
     * Fired before any targets are started.
     *
     * @param BuildEvent The BuildEvent
     */
    function buildStarted(BuildEvent $event) {} 

    /**
     * Fired after the last target has finished.
     *
     * @param BuildEvent The BuildEvent
     * @see BuildEvent::getException()
     */
    function buildFinished(BuildEvent $event) {} 

    /**
     * Fired when a target is started.
     *
     * @param BuildEvent The BuildEvent
     * @see BuildEvent::getTarget()
     */

    /**
     * Fired when a target has finished.
     *
     * @param BuildEvent The BuildEvent
     * @see BuildEvent#getException()
     */
    function targetFinished(BuildEvent $event) {} 

    /**
     * Fired when a task is started.
     *
     * @param BuildEvent The BuildEvent
     * @see BuildEvent::getTask()
     */
    function targetStarted(BuildEvent $event) {} 

    /**
     *  Fired when a task has finished.
     *
     *  @param BuildEvent The BuildEvent
     *  @see BuildEvent::getException()
     */
    function taskFinished(BuildEvent $event) {} 

    /**
     *  Fired whenever a message is logged.
     *
     *  @param BuildEvent The BuildEvent
     *  @see BuildEvent::getMessage()
     */
    function messageLogged(BuildEvent $event) {} 

}

class Cgn_Phing_Command {

	public $commandFile = '';
	public $commandTarget = '';
	public $project = NULL;
	public $properties = array();
	public $capturePhingOutput = FALSE;

	public $isReady  = FALSE;

	public $chainCommand  = NULL;

	/**
	 * @throws BuildException
	 */
	public function runTarget($t='') {
		if ($t == '') {
			$t = $this->commandTarget;
		}
		$cwd = getcwd();
		$this->initCommand();

		foreach ($this->properties as $k => $p) {
			$this->project->setProperty($k, $p);
		}

		if ($this->capturePhingOutput) {
			require_once 'phing/listener/DefaultLogger.php';
			$logger = new DefaultLogger();
			Phing::startup();
			$logger->setMessageOutputLevel(Project::MSG_INFO);
			$logger->setOutputStream(Phing::getOutputStream());
			$logger->setErrorStream(Phing::getErrorStream());
			$this->project->addBuildListener($logger);
		}

		$this->project->executeTargets(array($t));
		$out = $this->project->getProperty("command.out");
		$this->project->fireBuildFinished(null);
		restore_error_handler();
		chdir($cwd);
		return $out;
	}


	public function initCommand() {
		if ($this->isReady) { return; }
		$this->isReady = TRUE;

		/* set classpath */
		if (!defined('PHP_CLASSPATH')) { define('PHP_CLASSPATH',  get_include_path().":./cognifty/lib"); }
		ini_set('include_path', PHP_CLASSPATH);

		require_once 'phing/Phing.php';

		Phing::setProperty('host.fstype', 'UNIX');
		$buildFile = new PhingFile($this->commandFile);

		$this->project = new Project();
		$this->project->setBaseDir(getcwd());

		$this->project->setUserProperty("phing.file", $this->commandFile);
		$this->project->init();

		Phing::setCurrentProject($this->project);
		set_error_handler(array('Phing', 'handlePhpError'));

		ProjectConfigurator::configureProject($this->project, $buildFile);
		$this->applyPropertiesFile();
	}

	public function setPropertiesFile($file) {
		$this->propertiesFile = $file;
	}

	/**
	 * Apply a properties file.
	 *
	 * If file does not exist, returns without error.
	 */
	public function applyPropertiesFile() {
		$file = $this->propertiesFile;
		if ($file === NULL ) {
			return;
		}
		if (!file_exists($this->propertiesFile)) {
			return;
		}
//		if (!defined('PHP_CLASSPATH')) { define('PHP_CLASSPATH',  get_include_path().":./cognifty/phing/classes"); }
//		ini_set('include_path', PHP_CLASSPATH);
//		require_once 'phing/Phing.php';

		Phing::setProperty('host.fstype', 'UNIX');
		// load default tasks
		$taskdefs = new PhingFile($file);

        try { // try to load taskdefs
            $props = new Properties();
            $in = new PhingFile((string)$taskdefs);

            if ($in === null) {
                throw new BuildException("Can't load default task list");
            }
            $props->load($in);

            $enum = $props->propertyNames();
            foreach($enum as $key) {
                $value = $props->getProperty($key);
                $this->project->setProperty($key, $value);
            }
        } catch (IOException $ioe) {
            throw new BuildException("IO Can't load default task list ".$ioe->getMessage());
        }
	}

	public function setProperty($key, $value) {
		if (!is_object($this->project)) {
			return false;
		}
		return $this->project->setProperty($key, $value);
	}

	public function __destruct() {
		if (class_exists('Phing'))
		Phing::unsetCurrentProject();
	}

	function mybt() {
		$bt = debug_backtrace();
		foreach ($bt as $b) {
			echo $b['file'] .' ('.$b['line'].')'."\n";
	}
	}

}

