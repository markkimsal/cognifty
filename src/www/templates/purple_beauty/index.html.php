<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=iso-8859-1" />
<link rel="stylesheet" type="text/css" href="<?php cgn_templateurl();?>style.css" />
<title>Your custom title</title>
</head>



<body>

<div id="container">

<div id="header"></div>

<div id="navcontainer">
<ul id="navlist">
<li id="active"><a href="<?= cgn_appurl('main');?>" id="current">Home</a></li>
<li><a href="<?= cgn_appurl('login.main');?>">Sign Up</a></li>
<li><a href="<?= cgn_appurl('showoff');?>">Show-off</a></li>
<!--
<li><a href="#">link three</a></li>
<li><a href="#">link four</a></li>
<li><a href="#">link five</a></li>
-->
</ul>
</div>

<div id="wrapper">

<div id="left">
  <ul class="list">
  <li><a href="#">vertical links</a></li>
  <li><a href="#">vertical links</a></li>
  <li><a href="#">vertical links</a></li>
  <li><a href="#">vertical links</a></li> 
</ul>
</div>
  
  
<div id="right">
  <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Nam aliquet. Nunc quis tellus. Praesent eros turpis, laoreet nec, iaculis vitae, tincidunt ac, tellus. Nam metus nisl, sollicitudin eget, malesuada at, rutrum at, libero. Cras hendrerit magna non nunc. Cras elit nibh, rutrum elementum, lacinia in, molestie sed, enim. Fusce ut elit. Cras nec arcu at purus commodo feugiat. Maecenas interdum nunc in sapien. Nulla facilisi. Phasellus ut neque. </p>
</div>
  
<div id="content">

	<?php Cgn_Template::parseTemplateSection('content.main'); ?>

<!--
  <h1>This is heading 1 and a floated image </h1>
  <p><img src="<?php cgn_templateurl();?>images/girl.jpg" alt="Girl" width="151" height="99" class="imageleft" />This template is a free version of Purple Beauty created by Dieter Schneider 2006 for <a href="http://www.webart.no">www.webart.no.</a> If you need help to customize this template or have other questions about webdesign visit our support/community at <a href="http://www.webart.no/forum">www.webart.no/forum</a> You have to keep the link to webart.no in your footer, however if you want to remove it and get the PSD file you can buy this template for 5 dollar at <a href="http://www.webart.no/webshop/index.php?main_page=index&amp;cPath=10">www.webart.no/webshop.</a> The image of the girl are from www.sxc.hu. This template is valid xhtml 1.1 and is tested in the latest versions of FF, IE and Opera.</p>
  
  <h2>Heading 2 and a blockquote</h2>
  <p>Morbi sem pede, malesuada ac, malesuada eget, imperdiet ac, tortor. Maecenas sed augue ac dui euismod vehicula. Phasellus ligula nisi, volutpat nec, aliquam nec, varius id, quam. Donec sagittis. Nulla a tellus at lacus ultricies interdum. Aenean dignissim ultricies quam. Mauris hendrerit cursus sapien. Sed gravida, mauris a gravida pharetra, augue felis pellentesque nunc, eu iaculis justo lacus et tellus. Maecenas eget justo.</p>
  
    <blockquote><p>This is a blockquote . Phasellus gravida augue vitae magna. Donec vehicula, neque sed malesuada gravida, nunc augue pellentesque libero, nec laoreet purus dui a mi. Sed magna. Ut euismod sem vel nisi. In in quam quis sem suscipit semper. Nam ac felis. Phasellus quis enim. Duis blandit lacus at dolor. Proin tincidunt lacus id ante. Curabitur iaculis. </p></blockquote>
  
  <p>Nullam elementum sem nec urna. Etiam tincidunt elit id tellus. Etiam vehicula dolor non risus. Proin porta. Phasellus eleifend velit sit amet dui. Proin facilisis ipsum in libero aliquam varius. Quisque ut lacus. Morbi lectus. Nam nonummy viverra orci. Duis vulputate, ipsum eget feugiat bibendum, massa justo mollis tellus, in luctus nibh leo posuere mauris. </p>
  
-->
</div>


</div>


<div id="footer">
  <p>Design by Dieter Schneider 2006</p>
  <p> Visit <a href="http://www.webart.no/webshop">www.webart.no</a> for more</p>
</div>


</div>
</body>
</html>
