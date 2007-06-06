<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php cgn_sitename();?> | <?php cgn_pagename();?></title>

<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta name="keywords" content="some, keywords, will, help, in, tagging, or, making, links, to, your, site" />
<meta name="description" content="this will appear on search engine results" />

<!-- Robots Meta tag will allow search engines to index your site -->
<meta name="robots" content="index, follow" />

<link rel="stylesheet" type="text/css" href="<?php cgn_templateurl();?>style.css" />

<!-- 
      Design By: Shahrukh Hasan
			Web Address: www.abnplus.com
			Date: April, 2006
			Comments: To customize this template, please contact me by visiting [website www.abnplus.com]
                This template is divided in Three Sections. 
      Open Source: This template is free to use for personal or commercial projects. 
			             Please do give credit to the author of this template by creating a link to authors website.
						
-->

</head>
<body>

<div class="container">
<!-- Edit below to alter Top Section of Webpage -->  
  <div class="top_cont">
	    <div class="header" id="header_text">
			    <div>
					<?php cgn_sitename();?>
					</div>	
			</div>
	</div>
<!-- Top Section Ends Here  -->	

<!--  Edit Below to alter Middle Section of Webpage  -->	
	<div class="middle_cont">
	    <div class="content">
			    

		<div class="main_content">

		<div class="menu_box">
		<div class="menu">
		<!-- Edit Navigation Bar Below -->
		<ul>

			<li id="active"><a href="<?= cgn_appurl('main');?>" id="current">Home</a></li>
			<li><a href="<?= cgn_appurl('login.main');?>">Sign Up</a></li>
			<li><a href="<?= cgn_appurl('showoff');?>">Show-off</a></li>
			<li><a href="#nogo" >Portfolio</a></li>
			<li><a href="#nogo" >Contact</a></li> 
		</ul>
		   <!-- Navigation Bar Ends Here -->
								
								 </div>
								</div>
								
								<!-- Edit Content of your webpage below -->
								<br/>
								<p> <img src="<?php cgn_templateurl();?>img/100.jpg" alt="" align="right" />
								</p>

								<?php Cgn_Template::parseTemplateSection('content.main'); ?>
								
								
								<!-- Content Ends Here-->
								</div>
																
								
								<div class="sidebar">
								
								<div class="menuBAR">
                <form method="post" action="#">
                 Username:<br/>
                <input type="text" name="nameLOGIN" />
                <br/>Password:<br/> 
                <input type="password" name="passWORD"  />
               <div style="margin-top:3px; padding:0 ;" >
                <button type="submit">Login</button>&nbsp;
                 <button type="reset">Reset</button>
               </div>
							    </form>
									<br/><hr/>
									</div>
									
									<div class="extra_links">
									<br/>
									<h2>Company's Progress </h2>
									<br/>
									30 Million more profit genrated in last Quarter
									compared to previous quarters
									
									<hr/>
									<br/>
									<h2>Links</h2>
									<br/>
									<a href="#nogo">Google</a><br/>
								  <a href="#nogo">Yahoo</a><br/>
									<a href="#nogo">Edit-Me</a><br/>
									<a href="#nogo">Edit-Me</a><br/>
									
									</div>
								
								
								</div>
								
					
			</div>
	</div>
<!-- Middle Section Ends Here -->

<!-- Edit below to alter Bottom Sectio of Webpage -->	
	<div class="bottom_cont">
      <div class="footer">
			    <div id="footer_text">
					<br/>
					Copyright &copy; 2006, <a href="#nogo">YourSite</a> Design By
					<a href="http://abnplus.com">ABNplus</a>
					</div>
			</div>
	</div>  
<!-- Bottom Section Ends Here -->
  
	<div class="bottom_box">
	&nbsp;
	</div>

</div>

</body>
</html>
