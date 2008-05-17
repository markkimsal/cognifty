<?php
echo $t['form']->toHtml();
?>

<script type="text/javascript">
	 $(document).ready(function(){
		  $("input[@type=file]").change(function(){
			  if ($("#title").attr("value") == '') {
			  	$("#title").attr("value", rightFromSubString( $("#filename").attr("value"), "/"));
			  }
				$("#description").focus();
				  });
	 });

function rightFromSubString(fullString, subString) { 
	if (fullString.lastIndexOf(subString) == -1) { 
		return ""; 
	} else { 
		return fullString.substring(fullString.lastIndexOf(subString)+1, fullString.length); 
	} 
}
</script>
