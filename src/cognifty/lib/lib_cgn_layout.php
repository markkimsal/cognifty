<?php


class Cgn_LayoutManager {


	function showMainContent($sectionName) {
		if ($sectionName == 'content.main') {
			include('');
		}

		echo "Layout engine parsing content for [$sectionName]";
	}
}


?>
