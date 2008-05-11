<?php
/**
 * Renderer output base class
 *
 * @author Harry Fuecks <hfuecks@gmail.com>
 * @author Andreas Gohr <andi@splitbrain.org>
 */
if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');

//require_once DOKU_INC . 'inc/parser/renderer.php';
//require_once DOKU_INC . 'inc/pluginutils.php';

class Doku_Renderer {
    var $info = array(
        'cache' => TRUE, // may the rendered result cached?
        'toc'   => TRUE, // render the TOC?
    );


    function nocache() {
        $this->info['cache'] = FALSE;
    }

    function notoc() {
        $this->info['toc'] = FALSE;
    }

    //handle plugin rendering
    function plugin($name,$data){
        $plugin =& plugin_load('syntax',$name);
        if($plugin != null){
            // determine mode from renderer class name - format = "Doku_Renderer_<mode>"
            $mode = substr(get_class($this), 14);
            $plugin->render($mode,$this,$data);
        }
    }

    /**
     * handle nested render instructions
     * this method (and nest_close method) should not be overloaded in actual renderer output classes
     */
    function nest($instructions) {

      foreach ( $instructions as $instruction ) {
        // execute the callback against ourself
        call_user_func_array(array(&$this, $instruction[0]),$instruction[1]);
      }
    }

    // dummy closing instruction issued by Doku_Handler_Nest, normally the syntax mode should
    // override this instruction when instantiating Doku_Handler_Nest - however plugins will not
    // be able to - as their instructions require data.
    function nest_close() {}

    function document_start() {}

    function document_end() {}

    function render_TOC() { return ''; }

    function header($text, $level, $pos) {}

    function section_edit($start, $end, $level, $name) {}

    function section_open($level) {}

    function section_close() {}

    function cdata($text) {}

    function p_open() {}

    function p_close() {}

    function linebreak() {}

    function hr() {}

    function strong_open() {}

    function strong_close() {}

    function emphasis_open() {}

    function emphasis_close() {}

    function underline_open() {}

    function underline_close() {}

    function monospace_open() {}

    function monospace_close() {}

    function subscript_open() {}

    function subscript_close() {}

    function superscript_open() {}

    function superscript_close() {}

    function deleted_open() {}

    function deleted_close() {}

    function footnote_open() {}

    function footnote_close() {}

    function listu_open() {}

    function listu_close() {}

    function listo_open() {}

    function listo_close() {}

    function listitem_open($level) {}

    function listitem_close() {}

    function listcontent_open() {}

    function listcontent_close() {}

    function unformatted($text) {}

    function php($text) {}

    function html($text) {}

    function preformatted($text) {}

    function file($text) {}

    function quote_open() {}

    function quote_close() {}

    function code($text, $lang = NULL) {}

    function acronym($acronym) {}

    function smiley($smiley) {}

    function wordblock($word) {}

    function entity($entity) {}

    // 640x480 ($x=640, $y=480)
    function multiplyentity($x, $y) {}

    function singlequoteopening() {}

    function singlequoteclosing() {}

    function doublequoteopening() {}

    function doublequoteclosing() {}

    // $link like 'SomePage'
    function camelcaselink($link) {}

    // $link like 'wiki:syntax', $title could be an array (media)
    function internallink($link, $title = NULL) {}

    // $link is full URL with scheme, $title could be an array (media)
    function externallink($link, $title = NULL) {}

    // $link is the original link - probably not much use
    // $wikiName is an indentifier for the wiki
    // $wikiUri is the URL fragment to append to some known URL
    function interwikilink($link, $title = NULL, $wikiName, $wikiUri) {}

    // Link to file on users OS, $title could be an array (media)
    function filelink($link, $title = NULL) {}

    // Link to a Windows share, , $title could be an array (media)
    function windowssharelink($link, $title = NULL) {}

//  function email($address, $title = NULL) {}
    function emaillink($address, $name = NULL) {}

    function internalmedialink (
        $src,$title=NULL,$align=NULL,$width=NULL,$height=NULL,$cache=NULL
        ) {}

    function externalmedialink(
        $src,$title=NULL,$align=NULL,$width=NULL,$height=NULL,$cache=NULL
        ) {}

    function table_open($maxcols = NULL, $numrows = NULL){}

    function table_close(){}

    function tablerow_open(){}

    function tablerow_close(){}

    function tableheader_open($colspan = 1, $align = NULL){}

    function tableheader_close(){}

    function tablecell_open($colspan = 1, $align = NULL){}

    function tablecell_close(){}

}


//Setup VIM: ex: et ts=4 enc=utf-8 :


//copied from other files.
function getCacheName($data,$ext=''){
  $conf = array();
  $conf['cachedir'] = BASE_DIR.'var/cache/';
  $md5  = md5($data);
  $file = $conf['cachedir'].'/'.$md5{0}.'/'.$md5.$ext;
  io_makeFileDir($file);
  return $file;
}

/**
 * Create the directory needed for the given file
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 */
function io_makeFileDir($file){
  $conf = array();
  $conf['cachedir'] = BASE_DIR.'var/cache/';

  $dir = dirname($file);
  if(!@is_dir($dir)){
    io_mkdir_p($dir);
  }
}

/**
 * Creates a directory hierachy.
 *
 * @link    http://www.php.net/manual/en/function.mkdir.php
 * @author  <saint@corenova.com>
 * @author  Andreas Gohr <andi@splitbrain.org>
 */
function io_mkdir_p($target){
  $conf = array();
  $conf['cachedir'] = BASE_DIR.'var/cache/';
  $conf['safemodehack'] = FALSE;
  $conf['dmode'] = 755;
  $conf['dperm'] = 755;
  if (@is_dir($target)||empty($target)) return 1; // best case check first
  if (@file_exists($target) && !is_dir($target)) return 0;
  //recursion
  if (io_mkdir_p(substr($target,0,strrpos($target,'/')))){
    if($conf['safemodehack']){
      $dir = preg_replace('/^'.preg_quote(fullpath($conf['ftp']['root']),'/').'/','', $target);
      return io_mkdir_ftp($dir);
    }else{
      $ret = @mkdir($target,$conf['dmode']); // crawl back up & create dir tree
      if($ret && $conf['dperm']) chmod($target, $conf['dperm']);
      return $ret;
    }
  }
  return 0;
}


function io_saveFile($file,$content,$append=false){
  $conf = array();
  $conf['cachedir'] = BASE_DIR.'var/cache/';
  $conf['safemodehack'] = FALSE;
  $conf['dmode'] = 755;
  $conf['dperm'] = 755;

  $mode = ($append) ? 'ab' : 'wb';

  $fileexists = @file_exists($file);
  io_makeFileDir($file);
  io_lock($file);
  if(substr($file,-3) == '.gz'){
    $fh = @gzopen($file,$mode.'9');
    if(!$fh){
      io_unlock($file);
      return false;
    }
    gzwrite($fh, $content);
    gzclose($fh);
  }else if(substr($file,-4) == '.bz2'){
    $fh = @bzopen($file,$mode{0});
    if(!$fh){
      io_unlock($file);
      return false;
    }
    bzwrite($fh, $content);
    bzclose($fh);
  }else{
    $fh = fopen($file,$mode);
    if(!$fh){
      io_unlock($file);
      return false;
    }
    fwrite($fh, $content);
    fclose($fh);
  }

  if(!$fileexists and !empty($conf['fperm'])) chmod($file, $conf['fperm']);
  io_unlock($file);
  return true;
}

function io_lock($file){
  $conf = array();
  $conf['cachedir'] = BASE_DIR.'var/cache/';
  $conf['safemodehack'] = TRUE;
  $conf['dmode'] = 755;
  $conf['dperm'] = 755;

  // no locking if safemode hack
  if($conf['safemodehack']) return;

  $lockDir = $conf['lockdir'].'/'.md5($file);
  @ignore_user_abort(1);

  $timeStart = time();
  do {
    //waited longer than 3 seconds? -> stale lock
    if ((time() - $timeStart) > 3) break;
    $locked = @mkdir($lockDir, $conf['dmode']);
    if($locked){
      if(!empty($conf['dperm'])) chmod($lockDir, $conf['dperm']);
      break;
    }
    usleep(50);
  } while ($locked === false);
}

/**
 * Unlocks a file
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function io_unlock($file){
  $conf = array();
  $conf['cachedir'] = BASE_DIR.'var/cache/';
  $conf['safemodehack'] = TRUE;
  $conf['dmode'] = 755;
  $conf['dperm'] = 755;

  // no locking if safemode hack
  if($conf['safemodehack']) return;

  $lockDir = $conf['lockdir'].'/'.md5($file);
  @rmdir($lockDir);
  @ignore_user_abort(0);
}

/**
 * Returns content of $file as cleaned string.
 *
 * Uses gzip if extension is .gz
 *
 * If you want to use the returned value in unserialize
 * be sure to set $clean to false!
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 */
function io_readFile($file,$clean=true){
  $ret = '';
  if(@file_exists($file)){
    if(substr($file,-3) == '.gz'){
      $ret = join('',gzfile($file));
    }else if(substr($file,-4) == '.bz2'){
      $ret = bzfile($file);
    }else{
      $ret = file_get_contents($file);
    }
  }
  if($clean){
    return cleanText($ret);
  }else{
    return $ret;
  }
}
