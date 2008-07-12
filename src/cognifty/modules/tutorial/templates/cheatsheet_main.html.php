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
 white-space:pre;
 overflow:scroll;
}

#code-sample-area {
 color:white;
 background-color:#333;
 white-space:pre;
 overflow:scroll;
 border:0px solid transparent;
 padding:0px;
}

input {
 color:#000;
}
#code-sample .custom {
color:#E99;
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
					//selectCode("code-sample");
					$("#code-sample-area").css('display', 'block');
//					$("#code-sample-area").html($("#code-sample").html());
					$("#code-sample-area").val($("#code-sample").text());

					$("#code-sample").css('display', 'none');
					$("#code-sample-area").focus();
					$("#code-sample-area").select();

//					$("#code-sample").select();
					var t = $("#code-sample").html();
					if (window.clipdboardData) {
						window.clipboardData.setData('Text',t);
					}
			});

				$("#code-sample-area").bind('blur', function() {
					$(this).css('display', 'none');
					$("#code-sample").css('display', 'block');
			});

		 });


</script>
<h3>Live Cheat Sheet</h3>
<p>Click a link below, then copy the code sample from this text box.</p>

<div width="50%" style="width:50%;float:right;">
<input type="button" name="code-copy" id="code-copy" value="select all..."/>
<br/>
<div id="code-sample" name="code-sample" cols="50" rows="20" nowrap="nowrap" style="width:100%;height:300px"></div>
<textarea id="code-sample-area" name="code-sample-area" cols="50" rows="20" nowrap="nowrap" style="display:none;width:100%;height:300px;"></textarea>
<br/>
<div id="code-desc" name="code-desc" cols="50" rows="20" nowrap="nowrap" style="width:100%;height:300px"></div>

</div>


<p>
<h4>Database</h4>
<dl>
<dt>Get a database handle</dt>
<dd>$db = Cgn_Db_Connector::getHandle();</dd>
<dt>Get a database handle (different DSN)</dt>
<dd>$db = Cgn_Db_Connector::getHandle(<span class="custom">'custom'</span>);</dd>

<dt>New Data Item (active record)</dt>
<dd>$item = new Cgn_DataItem(<span class="custom">'table'</span>, <span class="custom">'table_id'</span>);
$item-&gt;load(<span class="custom">1</span>);</dd>

<dt>New Data Item Finder</dt>
<dd>$finder = new Cgn_DataItem(<span class="custom">'table'</span>, <span class="custom">'table_id'</span>);
$finder-&gt;andWhere(<span class="custom">'column'</span>, <span class="custom">'1'</span>);
$results = $finder-&gt;find();</dd>

<dt>New Data Item Finder with Joins</dt>
<dd>$finder = new Cgn_DataItem(<span class="custom">'table'</span>, <span class="custom">'table_id'</span>);
$finder-&gt;andWhere(<span class="custom">'column_one'</span>, <span class="custom">'100'</span>, '&gt;');
$finder-&gt;hasOne(<span class="custom">'table_B'</span>, <span class="custom">'B_col_fkey'</span>, <span class="custom">'table_B_alias'</span>);
$finder-&gt;hasOne(<span class="custom">'table_C'</span>, <span class="custom">'C_col_fkey'</span>, <span class="custom">'table_C_alias'</span>, <span class="custom">'local_col'</span>);
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
	function __construct() { }

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

	function __construct() {
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

<dt>Check the spam rating of a request to a service.</dt>
<dd>$spamScore = $this->getSpamScore();</dd>

<dt>Redirect a service</dt>
<dd>$this->presenter = 'redirect';
$t['url'] =  cgn_appurl('module', 'service', 'event', array('get1'=&gt;'value'));
return false;</dd>

<dt>Redirect a service to its own home.</dt>
<dd>$this->redirectHome();
return false;</dd>

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

<dt>Change the current page's title</dt>
<dd>Cgn_Template::setPageTitle("title");</dd>

<dt>Change the site's tag line</dt>
<dd>Cgn_Template::setSiteTagLine("put all your fancy quotes here.");</dd>


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
