<?php
include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_toolbar.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc_table.php');

class Cgn_Service_Users_Edit extends Cgn_Service_Admin {

	function Cgn_Service_Users_Edit () {

	}

	function authorize($e, $u) {
		return $u->belongsToGroup('admin');
	}


	function mainEvent(&$req, &$t) {
		$id = $req->cleanInt('id');
		$t['users'] = new Cgn_DataItem('cgn_user','cgn_user_id');
		$t['users']->load($id);
		$values = array();

		if ($id < 1) {
			//no such user
			return FALSE;
		}

		$finder = new Cgn_DataItem('cgn_user');
		$finder->load($id);
		$values = $finder->valuesAsArray();

		$values['menuTitle'] = 'Edit a User';
		$values['menuWidth'] = '600px';
		$values['textline_01'] = 'Use this tool to add a new user to the system.<br />
			All fields require an entry when adding a new user.';
		$values['textline_02'] = 'Enter a Unique User ID';
		$values['textline_03'] = '<span style="color:#FF0000;">
			<br />Password must be at least 6 Characters</span>';
		$values['textline_04'] = '( * ) Indicates a required entry.';
			$t['form01'] = $this->_loadEditUserForm($values);

		$groupFinder = new Cgn_DataItem('cgn_group');
		$groupList = $groupFinder->find();

		$user = Cgn_User::load($id);
		$user->loadGroups();
		$groupLinks = $user->getGroupIds();
		$t['form02'] = $this->_loadEditGroupForm($groupList, $groupLinks, $values);


		$orgFinder = new Cgn_DataItem('cgn_account');
		$orgFinder->andWhere('cgn_user_id', 0);
		$orgList = $orgFinder->find();

		$memberList = new Cgn_DataItem('cgn_user_org_link');
		$memberList->andWhere('cgn_user_id', $id);
		$orgLinks = $memberList->findAsArray();
		$orgIds = array();
		foreach ($orgLinks as $_o) {
			$orgIds[] = $_o['cgn_org_id'];
		}


		$t['form03'] = $this->_loadEditOrgForm($orgList, $orgIds, $values);

	}


	function saveUserEditEvent(&$req, &$t) {
		$id = $req->cleanInt('id');
		$u = $req->getUser();
		if ($id == '') {
			$u->addSessionMessage('Missing ID.', 'msg_warn');
			$this->presenter = 'redirect';
			$t['url'] = cgn_adminurl('users','main');
		}  else if ($req->cleanString('username') == '')  {
			$u->addSessionMessage('Missing username.', 'msg_warn');
			$this->presenter = 'redirect';
			$t['url'] = cgn_adminurl('users','edit','',array('id'=>$id));
		}  else if ($req->cleanString('email') == '')  {
			$u->addSessionMessage('Missing email.', 'msg_warn');
			$this->presenter = 'redirect';
			$t['url'] = cgn_adminurl('users','edit','',array('id'=>$id));
		}  else if ($req->cleanString('password1') != $req->cleanString('password2'))  {
			$u->addSessionMessage('Passwords do not match.', 'msg_warn');
			$this->presenter = 'redirect';
			$t['url'] = cgn_adminurl('users','edit','',array('id'=>$id));
		}  else {
			$user = new Cgn_DataItem('cgn_user');
			$user->load($id);
			$user->username = $req->cleanString('username');

			if ($req->cleanString('password1') != '') {
				$user->password = Cgn_User::_hashPassword($req->cleanString('password1'));
			}
			$user->email = $req->cleanString('email');
			if ($user->save()) {
				$u->addSessionMessage('User updated.');
			} else {
				$u->addSessionMessage('Save failed.', 'msg_warn');
			}
			$this->presenter = 'redirect';
			$t['url'] = cgn_adminurl('users','main');
		}
	}

	function saveGroupEditEvent(&$req, &$t) {
		$id = $req->cleanInt('id');

		if ($id < 1) {
			//no such user
			return FALSE;
		}

		$user = Cgn_User::load($id);

		$groupFinder = new Cgn_DataItem('cgn_group');
		$groupList = $groupFinder->find();

		$user->groups = array();
		if (is_array($req->postvars['group_ids'])) {
			foreach ($req->postvars['group_ids'] as $_gid) {
				$user->addToGroup($_gid, $groupList[$_gid]->code);
			}
		} else {
			$_gid =  $req->cleanInt('group_ids');
			$user->addToGroup($_gid, $groupList[$_gid]->code);
		}
		$user->saveGroups();
		$this->presenter = 'redirect';
		$t['url'] = cgn_adminurl('users','main');
	}


	function saveOrgEditEvent(&$req, &$t) {
		$id = $req->cleanInt('id');

		$user = Cgn_User::load($id);

		$orgFinder = new Cgn_DataItem('cgn_account');
		$orgFinder->andWhere('cgn_user_id', 0);
		$orgList = $orgFinder->find();

		$gidList = array();
		if (is_array($req->postvars['org_ids'])) {
			$gidList = $req->cleanInt('org_ids');
		} else {
			$gidList[] =  $req->cleanInt('org_ids');
		}

		$eraser = new Cgn_DataItem('cgn_user_org_link');
		$eraser->andWhere('cgn_user_id', $id);
		$eraser->delete();

		foreach ($gidList as $_gid) {
			if ($_gid == 0) continue;
			$link = new Cgn_DataItem('cgn_user_org_link');
			$link->_nuls[] ='inviter_id';
			$link->set('cgn_org_id', intval($_gid));
			$link->set('cgn_user_id', intval($id));
			$link->set('joined_on', time());
			$link->set('is_active', 1);
			$link->set('role_code', 'member');
			$link->set('inviter_id', NULL);
			$link->save();
		}

		$this->presenter = 'redirect';
		$t['url'] = cgn_adminurl('users','main');
	}


	function _loadEditUserForm($values=array()) {
		include_once(CGN_LIB_PATH.'/form/lib_cgn_form.php');
		include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
		$id = $values['cgn_user_id'];
		$f = new Cgn_FormAdmin('useredit');
		$f->width = $values['menuWidth'];
		$f->action = cgn_adminurl('users','edit','saveUserEdit');
		$f->label = $values['menuTitle'];
		$f->formHeader = 'Use this tool to edit a User\'s Site Registration.<br />
			If you leave the passwords blank, they WILL NOT be overwritten.<br /><br />
			Record  : '.$id.'<br />
			Userid&nbsp; : '.$values['username'].'<br />';
		$f->appendElement(new Cgn_Form_ElementHidden('id'),$values['cgn_user_id']);
		$f->appendElement(new Cgn_Form_ElementInput('username', '* User ID'),$values['username']);
		$f->appendElement(new Cgn_Form_ElementInput('email', '* Email'),$values['email']);
		$instruction = new Cgn_Form_ElementLabel('instruction','', 
			'If you leave the passwords blank, they WILL NOT be updated.'
		);
		$f->appendElement($instruction);
		$f->appendElement(new Cgn_Form_ElementPassword('password1', 'Password'));
		$f->appendElement(new Cgn_Form_ElementPassword('password2','Confirm'));
		$f->formFooter = $values['textline_04'];
		return $f;
	}


	function _loadEditGroupForm($groups, $groupIds, $values=array()) {
		include_once(CGN_LIB_PATH.'/form/lib_cgn_form.php');
		include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
		$id = $values['cgn_user_id'];
		$f = new Cgn_FormAdmin('groupedit');

		$f->width = $values['menuWidth'];
		$f->action = cgn_adminurl('users','edit','saveGroupEdit');
		$f->label = 'Change Groups';

		$radio = new Cgn_Form_ElementCheck('group_ids','Groups');
		foreach ($groups as $g) {
			if (in_array($g->cgn_group_id, $groupIds)) 
			$radio->addChoice($g->display_name, $g->cgn_group_id, TRUE);
			else
			$radio->addChoice($g->display_name, $g->cgn_group_id);
		}

		$f->appendElement(new Cgn_Form_ElementHidden('id'),$values['cgn_user_id']);
		$f->appendElement($radio);
		$f->formFooter = $values['textline_04'];
		return $f;
	}

	function _loadEditOrgForm($orgs, $orgIds, $values=array()) {
		Cgn::loadLibrary('Form::lib_cgn_form');
		Cgn::loadLibrary('Html_Widgets::lib_cgn_widget');
		$id = $values['cgn_user_id'];
		$f = new Cgn_FormAdmin('orgedit');

		$f->width = $values['menuWidth'];
		$f->action = cgn_adminurl('users','edit','saveOrgEdit');
		$f->label = 'Change Membership';

		$radio = new Cgn_Form_ElementCheck('org_ids', 'Org');
		foreach ($orgs as $g) {
			if (in_array($g->cgn_account_id, $orgIds)) 
			$radio->addChoice($g->org_name, $g->cgn_account_id, TRUE);
			else
			$radio->addChoice($g->org_name, $g->cgn_account_id);
		}

		$f->appendElement(new Cgn_Form_ElementHidden('id'),$values['cgn_user_id']);
		$f->appendElement($radio);
		$f->formFooter = $values['textline_04'];
		return $f;
	}

}

?>
