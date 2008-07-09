Back to  <a href="<?=cgn_appurl('tutorial');?>">Tutorial Home</a>

 <script language="JavaScript" src="<?=cgn_url();?>media/js/jquery-1.2.5.min.js" type="text/javascript"></script>
<style type="text/css">
DL DD {
display: none;
}
DL DT {
cursor:pointer;
color:#33F;
width:50%;
}
DL DT:hover {
cursor:pointer;
color:#33F;
width:50%;
background-color:#EEE;
}

DL DT.selected {
background-color:#EE9;
}

#code-sample {
 color:white;
 background-color:#333;
}
input {
 color:#000;
}

</style>
<script language="javascript">
		 $(document).ready(function(){
				$("dl>dt").bind('click', function() {
					$("#code-sample").html($(this).next().html());
					$("dt.selected").attr('class', '' );
					$(this).attr('class', 'selected');
			});
				$("#code-copy").bind("click", function() {
					//select text area
				//	selectCode("code-sample");

					$("#code-sample").select();
					var t = $("#code-sample").html();
					if (window.clipdboardData) {
						window.clipboardData.setData('Text',t);
					}
			});
		 });


function selectCode(blockId)
{
var e = document.getElementById(blockId);

// required
e.focus();

	// fetch the elements' selection range
	if (document.selection) {
		var r = document.selection.createRange();
	}

	// retrieve text range object from the whole text of our html element
	var re = e.createTextRange();

	// duplicate text range for later use
	var rc = re.duplicate();

// set the text range boundaries to the current selection
// we have to do this because ?setEndPoint? won?t allow us
// to use a bookmarked selection directly
re.moveToBookmark(r.getBookmark());

// move the end of the range to the start of the selection
rc.setEndPoint('EndToStart', rc);

// now the length of rc is equal to the start of our selection
// and the end position of our selection is equal to (length r plus length rc)

// it goes like this:
// start: rc.text.length, end:
	return { start: rc.text.length, end: rc.text.length + r.text.length, length: r.text.length, text: r.text };
}
</script>
<h3>Live Cheat Sheet</h3>
<p>Click a link below, then copy the code sample from this text box.</p>
<input type="button" name="code-copy" id="code-copy" value="select all..."/>
<br/>
<textarea id="code-sample" name="code-sample" width="60" rows="10"></textarea>


<p>
<h4>Database</h4>
<dl>
<dt>Get a database handle</dt>
<dd>$db = Cgn_Db_Connector::getHandle();</dd>
<dt>Get a database handle (different DSN)</dt>
<dd>$db = Cgn_Db_Connector::getHandle('custom');</dd>

<dt>New Data Item (active record)</dt>
<dd>$item = new Cgn_DataItem('table', 'table_id');
$item-&gt;load(1);</dd>

<dt>New Data Item Finder</dt>
<dd>$finder = new Cgn_DataItem('table', 'table_id');
$finder-&gt;andWhere('column', '1');
$results = $finder-&gt;find();</dd>

<dt>New Data Item Finder with Joins</dt>
<dd>$finder = new Cgn_DataItem('table', 'table_id');
$finder-&gt;andWhere('column_one', '100', '&gt;');
$finder-&gt;hasOne('table_B', 'B_col_fkey', 'table_B_alias');
$finder-&gt;hasOne('table_C', 'C_col_fkey', 'table_C_alias', 'local_col');
$finder-&gt;andWhere('table_B_alias.col_two', 'column_one');
$finder-&gt;andWhere('table_C_alias.col_three', $someValue);
$finder-&gt;orderBy('local_col DESC');
$finder-&gt;_cols = array('table.*', 'table_B_alias.column_two');
//use primary key as array index
$finder-&gt;_rsltByPkey = TRUE;
$records = $finder-&gt;find();
</dd>
</dl>
</p>

<p>
<h4>Services</h4>
<dl>
<dt>Skeleton Service</dt>
<dd>&lt;php
/**
 * New service
 * @package CHANGE
 */
class Cgn_Service_CHANGE_ME extends Cgn_Service {
	function _constructor() { }

	function mainEvent(&amp;$req, &amp;$t) {
		$t['message'] = "This is the main event.";
	}
}</dd>

<dt>Skeletal Trusted Service (Spam filter)</dt>
<dd>&lt;php
/**
 * New service
 * @package CHANGE
 */
class Cgn_Service_CHANGE_ME extends Cgn_Service_Trusted {

	var $untrustLimit  = 3;
	var $entry         = NULL;
	var $usesConfig    = TRUE;
	var $dieOnFailure  = TRUE;

	function _constructor() {
		$this->screenPosts();
		$this->trustPlugin('throttle',10);
		$this->trustPlugin('html',10);
//		$this->trustPlugin('requireCookie');
//		$this->trustPlugin('secureForm');

	}

	function mainEvent(&amp;$req, &amp;$t) {
		$t['message'] = "This is the main event.";
	}
}</dd>

</dl>
</p>

<p>
<h4>Users and sessions</h4>
<dl>
<dt>Get the current user (inside service)</dt>
<dd>$u = $req->getUser();</dd>

<dt>Get the current user (from anywhere)</dt>
<dd>$u = Cgn_SystemRequest::getUser();</dd>

<dt>Get the current session object</dt>
<dd>$session = Cgn_Session::getSessionObj();</dd>
</dl>
</p>


<p>
<h4>URLs</h4>
<dl>
<dt>Create a URL to an application</dt>
<dd>$href = cgn_appurl('module', 'service', 'event', array('get1'=&gt;'value'));</dd>

</dl>
</p>


<p>
<h4>Template</h4>
<dl>
<dt>Show a template <i>section</i> by name</dt>
<dd>&lt;?= Cgn_Template::parseTemplateSection('content.main');?&gt;</dd>

<dt>Check if a template section has content</dt>
<dd>&lt;? if( Cgn_Template::sectionHasContent('content.main') ) { } ?&gt;</dd>

</dl>
</p>

<p>
<h4>Ini Settings</h4>
<dl>

<dt>Create a new section callback (layout.ini)</dt>
<dd>;use boot/local/layout.ini for local changes
[object]
column.leftside=@lib.path@/lib_cgn_layout.php:Cgn_LayoutManager:defaultLayoutManager:showMainContent
</dd>


<dt>Create a new database connection (core.ini)</dt>
<dd>;use boot/local/core.ini for local changes
[dsn]
custom.uri=mysql://user:password@localhost/cognifty
</dd>


</dl>
</p>
