<?php

// STATIC
if (!defined('INIT_ERR') ) {
	$e = Cgn_ErrorStack::_singleton();
	set_error_handler( array( &$e, '_errorHandler') );
	define('INIT_ERR',true);
}


/**
 * Store and mete errors of the system.
 *
 * use pullError($context) to find errors.
 * use Cgn_ErrorStack::throwError() to throw a new error.
 */
class Cgn_ErrorStack {

	var $stack    = array(); 	//pile of errors
	var $notices  = array();
	var $count    = 0;
	var $n_count    = 0;

	function stack($e) {
		$x =& Cgn_ErrorStack::_singleton();
		if ($e->priority == E_NOTICE) {
			$x->notices[] = $e;
			$x->n_count++;
		} else {
			$x->stack[] = $e;
			$x->count++;
		}
	}

	function count() {
		$x =& Cgn_ErrorStack::_singleton();
		return $x->count;
	}


	function& _singleton() {
		static $single;
		if (! isset($single) ) {
			$single = new Cgn_ErrorStack();
		}
		return $single;
	}

	/**
	 * return null or an error of the specified context
	 */
	function pullError($t='user') {
		$ret = false;
		$newstack = array();
		$found = false;
		$s =& Cgn_ErrorStack::_singleton();

//		echo "count stack = ". count($s->stack)."\n\n";
//		cgn::debug($s->stack);

		for ($x= ($s->count-1); $x >= 0; --$x)  {
			if ( ($s->stack[$x]->type == $t) and (!$found)) {
				$ret = $s->stack[$x];
				$found = true;
				$s->count--;
			}
			else {
				$newstack[] = $s->stack[$x];
			}
		}
		$s->stack = array_reverse($newstack);
	return $ret;
	}


	/**
	 * Unimplemented, should allow for callbacks on errors
	 * might be unneeded in PHP4 (i.e. useless)
	 */
	function watchErrorTicks() {
		$e = Cgn_ErrorStack::pullError();
		if ($e) {
		$callback = Cgn_ErrorStack::registerCatch('',true);
		call_user_func_array($callback, array($e));
		}
	}



	function registerCatch($callback='',$private=false) {
		static $c;
		if ($private) {return $c;}
		$c = $callback;
	}


	function _errorHandler ($level, $message, $file, $line, $context='', $type='user') {
		static $count;
		//drop unintialized variables
//		echo $level;
//		echo E_NOTICE; exit();
//		if ($level == 8 ) return;  //E_NOTICE
		if ($level == 2 ) return;
		if ($level == 2048 ) return;

		$e = new Cgn_RuntimeError($message,$level,$type,$context);
		$bt = debug_backtrace();
		array_shift($bt);
		$e->addBackTrace($bt);
		Cgn_ErrorStack::stack($e);
	}


	function dumpStack() {
		$html = '';
		$s =& Cgn_ErrorStack::_singleton();
		for ($z=0; $z < $s->count; ++$z) {
			//start at 1, skip the backtrace to this function, not necassary
			// sometimes it's not necassary, sometimes it is (MAK)
			$bt = $s->stack[$z]->backtrace;
			$html .= "<h3>".$s->stack[$z]->message ."</h3>\n";
			//*
			for ($x=0; $x < count($bt); ++$x ) {
				if ($bt[$x]['class'] != '' ) {
					$html .= "<b>".$bt[$x]['class']."&nbsp;::&nbsp;".$bt[$x]['function']."</b>";
				} else {
					$html .= "<b>".$bt[$x]['function']."</b>";
				}
				$html .= "<br/>\n";
				//try to hide the dir structure
				$html .= basename($bt[$x]['file'])." ";
				$html .= "(".$bt[$x]['line'].")<br />\n";
				$html .= "<br />\n";
			}
			//*/
		}
		return $html;
	}


	function logStack() {
		$s =& Cgn_ErrorStack::_singleton();
		for ($z=0; $z <= $s->count; ++$z) {
			//start at 1, skip the backtrace to this function, not necassary
			// sometimes it's not necassary, sometimes it is (MAK)
			$bt = $s->stack[$z]->backtrace;
			$ret .= $s->stack[$z]->message . "\n";
			for ($x=0; $x < count($bt); ++$x ) {
				$indent = str_repeat("  ",$x);
				if ($bt[$x]['class'] != '' ) {
					$ret .= $indent."method : ".$bt[$x]['class']."::".$bt[$x]['function'];
				} else {
					$ret .= $indent."function : ".$bt[$x]['function'];
				}
				$ret .= "\n";
				$ret .= $bt[$x]['file']." ";
				$ret .= "(".$bt[$x]['line'].")\n";
			}
		}
		return $ret;
	}


	/**
	 * Cgn_ErrorStack::throwError
	 * wrapper function for directly accessing the error handler
	 * for some reason, directly calling Cgn_ErrorStack::stack 
	 * from userspace doesn't cut it
	 */
	function throwError ($msg,$level,$type='user') {
		Cgn_ErrorStack::_errorHandler($level,$msg,'',0,'',$type);
	}


	function showErrorBox() {
		$e =& Cgn_ErrorStack::_singleton();
		if ($e->count) { 

		$html ='<form id="errorbox">
			<div style="position:absolute;top:80px;left:70px;padding:3px;width:500px;background-color:#C0C0C0;border-style:outset">
			<table width="500" cellpadding="5" cellspacing="0" border="0">
				<tr>
					<td valign="top">
						<font color="red" style="font-size:110%;font-weight:bold;font-family:Serif;line-height:60%;">
<pre>
 **
**** 
**** 
 **
 **

 **
 **
</pre>
						</font>
					</td>
					<td width="80%" valign="top">
						<h3>'.$e->stack[0]->message .'</h3>
						There was a problem executing this program,
						click \'Details\' to find out more information.
						The detailed information will be usefull when debugging the program.
					</td>
					<td valign="top">
						<input type="button" value="Close"
						onclick="
document.getElementById(\'errdetails\').style.visibility=\'hidden\';
document.getElementById(\'errscroll\').style.visibility=\'hidden\';
document.getElementById(\'errscroll\').style.height=\'0px\';
document.getElementById(\'errdetailsbutton\').disabled=false;
document.getElementById(\'errorbox\').style.visibility=\'hidden\';"/>
						<p>&nbsp;
						<input type="button" id="errdetailsbutton" value="Details -&gt;"
						onclick="
document.getElementById(\'errdetails\').style.visibility=\'visible\';
document.getElementById(\'errscroll\').style.visibility=\'visible\';
document.getElementById(\'errscroll\').style.height=\'175px\';
this.disabled = true;
						"/>
					</td>
				</tr>
				<tr>
					<td colspan="3" width="480">
						<div id="errdetails" style="visibility:hidden" align="left">
							<div style="border-style:inset;overflow:scroll;height:0px;width:480px;visibility:hidden" id="errscroll" align="left">


				'.Cgn_ErrorStack::dumpStack().'

							</div>
							<br/>
							<input type="button" value="&lt;- No Details"
							onclick="
document.getElementById(\'errdetails\').style.visibility=\'hidden\';
document.getElementById(\'errscroll\').style.visibility=\'hidden\';
document.getElementById(\'errscroll\').style.height=\'0px\';
document.getElementById(\'errdetailsbutton\').disabled=false;
						"/>
						</div>
					</td>
				</tr>
			</table>
		</div>
	</form>
';
		}
	return $html;
	}
}


/**
 * Represent one error of the system or a module
 *
 * An error has a message, priority level, line & file, and a
 * context, or type.  The context can help with organizing errors.
 * For example, you can query the error stack for any error from
 * the type of the database or the forums; types are not enforced
 */
class Cgn_RuntimeError {
	var $message;
	var $priority;
	var $context;
	var $type;

	function Cgn_RuntimeError($m='', $p=0, $t='user', $c='') {
		if ($m == '' && isset($php_errormsg) )
			$m = $php_errormsg;

		$this->message = $m;
		$this->priority = $p;
		$this->type = $t;
		$this->context = $c;
	}

	function setType ($t) {
		$this->type = $t;
	}

	function getType () {
		return $this->type;
	}


	function toString() {
		return "[Message]: ".$this->message." [Type]: ".$this->type."";
	}


	/**
	 * This function expects the backtrace to have one level
	 *  stripped off so that the error function itself doesn't 
	 *  get recorded.  array_unshift() works fine
	 */
	function addBackTrace ($bt) {
		$this->backtrace = $bt;
	}

	/**
         * This is used at the top of getForm() if it was passed an object
	 * instead of an array as Keith had originally intended.
	 */
	function object2array($object) {
		if (!is_object($object)) return $object;
	
		$ret = array();
		$v = get_object_vars($object);
	
		while (list($prop, $value)= @each($v))
		{	$ret[$prop] = $this->object2array($value);
		}
	
		return $ret;
	}
}


