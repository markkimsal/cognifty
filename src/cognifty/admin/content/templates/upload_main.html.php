<?php
echo $t['form']->toHtml();
?>

<script type="text/javascript">
	$(document).ready(function(){
		var lastfname = ''
		//$("input[@type='file']").change(function(){
		$("#filename").change(function(){
			if ($("#title").attr("value") == lastfname ||
				$("#title").attr("value") == '') {
				var fname = $("#filename").attr("value");
				if (fname.lastIndexOf('/') != -1) {
					fname = rightFromSubString( fname, '/');
				}
				if (fname.lastIndexOf('\\') != -1) {
					fname = rightFromSubString( fname, '\\');
				}
				lastfname = fname;
				$("#title").attr("value", fname);
			}
			$("#description").focus();
			});
	});

function rightFromSubString(fullString, subString) { 
	if (fullString.lastIndexOf(subString) == -1) { 
		return ''; 
	} else { 
		return fullString.substring(fullString.lastIndexOf(subString)+1, fullString.length); 
	} 
}
</script>
