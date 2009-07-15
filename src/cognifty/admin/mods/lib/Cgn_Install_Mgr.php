<?php

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
	 * Try to find an install.xml file from $modInfo directory
	 * If $newPackage is NULL, assume that the module has already 
     *  been updated, but needs some more installation steps.
     * If $newPackage is a directory, then assume that the new 
     *  module version resides in that directory.
     * If $newPackage is a file, assume that the file is a tar.gz 
     *  of the updated module.
	 */
	public function __construct($modInfo, $newPackage = NULL) {

		$this->existingModInfo = $modInfo;

		$pathToModule = $this->existingModInfo->fullModulePath;

		//find the updated package
		if ($newPackage !== NULL) {
			if (is_dir($newPackage)) {
				$this->newPackageDir = $newPackage;
				$this->newModInfo    = new Cgn_Module_Info(
					$this->existingModInfo->codeName,
					$this->existingModInfo->isAdmin,
					$newPackage);
			} else if (is_file($newPackage)) {
				$this->newPackageFile = $newPackage;
				trigger_error("Cannot deal with compressed modules yet.");
				return;
			}

		}

		//treat the existing module as the new package
		if ($newPackage === NULL) {
			$this->newModInfo    = $this->existingModInfo;
			$this->newPackageDir = $pathToModule;
		}

		$this->phingFile = $this->newPackageDir.'install.xml';
	}

	/**
	 * Return true if this module can be installed
	 */
	public function canInstall() {
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
	 * Create or update the install.ini file
	 */
	public function finishInstall() {
		if (!@file_exists($this->existingModInfo->fullModulePath.'install.ini')) {
			$this->_createInstallIni();
		} else {
			$this->_updateInstallIni();
		}
	}

	public function _createInstallIni() {
		$version = $this->newModInfo->availableVersion;
		$fini = fopen($this->existingModInfo->fullModulePath.'install.ini', 'w');
		fputs($fini, "version.number=".$version."\n");
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

}

class Cgn_Phing_Command {

	public $commandFile = '';
	public $commandTarget = '';
	public $project = NULL;
	public $properties = array();
	public $capturePhingOutput = FALSE;

	public $isReady  = FALSE;

	public $chainCommand  = NULL;

	public function runCommand() {
		$cwd = getcwd();
		$this->initCommand();

		$this->project->executeTargets(array($this->commandTarget));
		$out = $this->project->getProperty("command.out");
		$this->project->fireBuildFinished(null);
		restore_error_handler();
		chdir($cwd);
		return $out;
	}


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

