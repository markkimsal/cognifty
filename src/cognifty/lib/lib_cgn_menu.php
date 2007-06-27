<?php

class Cgn_Menu {

	var $dataItem = null;

	function Cgn_Menu($id=0) {
		$this->dataItem = new Cgn_DataItem('cgn_menu');
		if ($id>0) {
			$this->dataItem->load($id);
		}
	}

	function load($name) {
		$this->dataItem->andWhere('code_name',$name);
		$menus = $this->dataItem->find();
//		cgn::debug($menus);
		foreach ($menus as $m) {
			if (is_object($m)) {
				$this->dataItem = $m;
			}
		}
	}
}


class Cgn_MenuItem {

	var $dataItem = null;

	function Cgn_MenuItem($id=0) {
		$this->dataItem = new Cgn_DataItem('cgn_menu_item');
		if ($id>0) {
			$this->dataItem->load($id);
		}
	}
}
