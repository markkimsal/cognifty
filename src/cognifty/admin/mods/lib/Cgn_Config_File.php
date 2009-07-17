<?php

class Cgn_Config_File {

	protected $fh       = NULL;
	protected $tmpfh    = NULL;
	protected $path     = NULL;

	public function __construct($path) {
		$this->path = $path;
	}

	public function open() {
		$this->fh = @fopen($this->path, 'rw');
	}

	public function openTmp() {
		@unlink($this->path.'.swp');
		$this->tmpfh = @fopen($this->path.'.swp', 'w');
	}

	public function close() {
		@fclose($this->fh);
	}

	public function closeTmp() {
		//remove extra trailing newline
		@truncate($this->tmpfh, (@filesize($this->path.'.swp')-1));
		@fclose($this->tmpfh);
	}

	/**
	 * Write a line to the tmp file
	 */
	public function rewrite($l) {
		@fwrite($this->tmpfh, trim($l)."\n");
	}

	/**
	 * Write a line to the tmp file
	 */
	public function swapFiles($l) {
		$old = @rename($this->path, $this->path.'.old');
		if(! @rename($this->path.'.swp', $this->path) ) {
			@unlink($this->path.'.swp');
			@rename($this->path.'.old', $this->path);
			return FALSE;
		}
		@unlink($this->path.'.old');
		return TRUE;
	}


	/**
	 * Scan linearly through an ini file, 
	 * add or update key in section
	 */
	public function addOrUpdate($section, $key, $val) {
		$this->open();
		$this->openTmp();
		$once = FALSE;

		$insideSec = FALSE;
		while(!feof($this->fh)) {
			$l = fgets($this->fh, 4096);
			if (!$insideSec) {
				if (trim($l) == '['.$section.']') {
					$insideSec = TRUE;
				}
				$this->rewrite($l);
				continue;
			}
			//inside the section
			//always add key/val, then remove key if found
			if (!$once) $this->rewrite($key.'='.$val);
			$once = TRUE;

			//remove old key/val if found by continue
			if (trim($l) == $key.'='.$val) 
				continue;

			$this->rewrite($l);

		}

		$this->close();
		$this->closeTmp();
		return $this->swapFiles();
	}
}
