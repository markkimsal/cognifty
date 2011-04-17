<?php

class Sysmgr_Utest_Rundir extends TestSuite {

	var $dirstack = array();

	function Sysmgr_Utest_Rundir() {
		if ($_SERVER['argc']>=2) {
			$starthere = rtrim(array_pop($_SERVER['argv']), DIRECTORY_SEPARATOR);
			$startname = 'Top Level Test Dir';
		} else {
			$starthere = '.';
			$startname = $starthere;
		}

		$testdir = dir($starthere);
		if (!$testdir) {
			return;
		}

		$this->TestSuite($startname);
		while ($entry = $testdir->read()) {
			if (substr($entry, 0, 1) == '.') continue;
			if (substr($entry, -1) == '~') continue;

			$entry = $starthere.'/'.$entry;
			if (is_dir($entry) ) {
				#echo "Stacking $entry ...\n";
				$this->dirstack[] = $entry;
			} else if (is_readable($entry)) {
				if (substr($entry, -4) != '.php') continue;
				#echo "Stacking $entry ...\n";
				$this->addFile($entry);
			}
		}
		$testdir->close();
		while (count($this->dirstack)) {
			$subdir = array_shift($this->dirstack);
			$this->recurseDir($subdir);
		}
	}

	function recurseDir($subdir) {
		$testdir = dir($subdir);
		$this->TestSuite('Tests in '.$subdir);
		while ($entry = $testdir->read()) {
			if (substr($entry, 0, 1) == '.') continue;
			if (substr($entry, -1) == '~') continue;

			$entry = $subdir.'/'.$entry;
			if (is_dir($entry) && is_readable($entry)) {
				//echo "Stacking $entry ...\n";
				$this->dirstack[] = $entry;
			} else if (is_readable($entry)) {
				if (substr($entry, -4) != '.php') continue;
				//echo "Stacking $entry ...\n";
				$this->addFile($entry);
			}
		}
		$testdir->close();
	}
}
