<?php

class Cgn_Phing_String_Logger extends DefaultLogger  {
	/**
	 * @var String to use for standard output.
	 */
	protected $out = '';

	/**
	 * @var String to use for standard error.
	 */
	protected $err = '';

	protected function printMessage($message, &$stream, $priority) {
		$stream .= $message . PHP_EOL;
	}

	public function getOut() {
		return $this->out;
	}
	
	public function getErr() {
		return $this->out;
	}


	/**
	 * Say "step" instead of BUILD
     * Get the message to return when a build failed.
     * @return string The classic "BUILD FAILED"
     */
    protected function getBuildFailedMessage() {
        return "STEP FAILED";
    }

    /**
	 * Say "step" instead of BUILD
     * Get the message to return when a build succeeded.
     * @return string The classic "BUILD FINISHED"
     */
    protected function getBuildSuccessfulMessage() {
        return "STEP FINISHED";
    }
}

