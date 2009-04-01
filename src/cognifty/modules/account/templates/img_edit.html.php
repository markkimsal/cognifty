
<h2>Upload Your Profile Picture</h2>

<form id="form_upload_profile_pic" class="acct-pic-upload" enctype="multipart/form-data" 
	name="form_upload_profile_pic" 
	action="<?=cgn_appurl('account', 'img', 'save');?>" 
	method="post">

Select an image file on your computer (4MB max):
<div>
<!--
	<input type="hidden" value="" name="post_form_id" id="post_form_id"/>
-->
	<input type="hidden" value="<?=$u->userId;?>" name="id" id="id"/>
	<input type="file" name="pic" id="account_picture_post_file" class="form-file form-input"/>
	<br/>
	<input type="submit" name="sbmt" id="account_picture_sbmt" class="form-button" value="Submit"/>
</div>
</form>
