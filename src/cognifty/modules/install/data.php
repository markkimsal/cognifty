<?php

Cgn::loadLibrary('Form::lib_cgn_form');

class Cgn_Service_Install_Data extends Cgn_Service {

	public function Cgn_Service_Install_Data () {
		$this->templateStyle = 'install';
	}

	/**
	 * Install menu and web pages
	 */
	public function mainEvent($req, &$t) {
		$t['form'] =  $this->_loadFormSampledata();
	}

	public function writeSampleEvent($req, &$t) {
		//$t['form'] =  $this->_loadFormSampledata();
//		var_dump($_POST);
		if ($req->cleanInt('sample_menu_top')) {
			$this->_installJson('sample_menu_top');
		}
		if ($req->cleanInt('sample_menu_btm')) {
			$this->_installJson('sample_menu_btm');
		}

		$this->presenter = 'redirect';
		$t['url'] = cgn_appurl('install', 'main', 'askAdmin');
		return true;
	}

	public function _installJson($file) {
		$data = json_decode( file_get_contents(dirname(__FILE__). DIRECTORY_SEPARATOR.'sampledata'.DIRECTORY_SEPARATOR.$file.'.json'), TRUE);
		foreach ($data as $_set) {
			$table = $_set['table'];
			foreach ($_set['items'] as $_d) {
				$item = new Cgn_Data_Item($table);
				foreach ($_d as $_k => $_v) {
					$item->set($_k, $_v);
				}
				$item->save();
			}
		}
	}

	public function _loadFormSampleData() {
		Cgn::loadLibrary('Form::lib_cgn_form');
		Cgn::loadLibrary('Html_Widgets::lib_cgn_widget');

		$f = new Cgn_Form('install_askEmail');
		$f->layout = new Cgn_Form_Layout_Dl();
		$f->width  = '100%';
		$f->label  = 'Install Sample Data';
		$f->showCancel  = false;
		$f->action = cgn_appurl('install', 'data', 'writeSample');


		$values   = array();
		$defaults = array(
			'sample_menu_top' => '1',
			'sample_menu_btm' => '1',
			'sample_pages' => array(
				'pp' => '1',
				'tos' => '1',
				'au' => '1'
				)
			);
		$values = array_merge($defaults, $values);

		$input1 = new Cgn_Form_ElementRadio('sample_menu_top', 'Top Menu');
		$input1->addChoice('Yes', '1');
		$input1->addChoice('No', '0');
		$f->appendElement($input1, $values['sample_menu_top'], '',  'Inludes About Us, Blog, and Account Settings links');

		$input2 = new Cgn_Form_ElementCheck('sample_pages', 'Sample Pages');
		$input2->addChoice('Privacy Policy', 'pp',       $values['sample_pages']['pp']);
		$input2->addChoice('Terms of Service', 'tos',    $values['sample_pages']['tos']);
		$input2->addChoice('About Us', 'au',             $values['sample_pages']['au']);
		$f->appendElement($input2, $values['sample_pages']);

		$input4 = new Cgn_Form_ElementRadio('sample_menu_btm', 'Bottom Menu');
		$input4->addChoice('Yes', '1');
		$input4->addChoice('No', '0');
		$f->appendElement($input4, $values['sample_menu_btm'], '',  'Inludes About Us, Privacy Policy, Terms of Service');

		return $f;
	}
}
