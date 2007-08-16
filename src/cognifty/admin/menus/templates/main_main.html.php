<style type="text/css">

#toolbar { 
  border: 1px solid black; 
  margin: 1em 0; 
  padding: .25em .1em; 
  background: url(../../templates/admin01/images/sprite.png) repeat-x scroll 0 0; 
} 
.adm_toolbtn button { 
  cursor:pointer;
  border:none;
  background-color:transparent;
  border-color:transparent;
  border-width:1.5px;
  border-style:solid;
  display:-moz-inline-box;

}
.adm_toolbtn button:hover { 
  background: url(../../templates/admin01/images/sprite.png) repeat-x scroll 0 -1300px; 
  border-color:#808080;
  border-width:1.5px;
  border-style:solid;
  display:-moz-inline-box;
} 
</style>

<h3>Menus</h3>

<?php

echo $t['toolbar']->toHtml();

?>

<?php

echo $t['dataGrid']->toHtml();

?>
