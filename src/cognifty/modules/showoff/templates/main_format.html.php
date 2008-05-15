Original data is looks like this:
<br/>
<pre>
<?= var_export($t); ?>
</pre>

<br/>
<hr/>
Telephone:
<?php
$f = new Cgn_ActiveFormatter($t['tel']);
echo $f->printAs('phone');

?>

<p>
Code for formatting the telephone $t['tel']:
</p>

<pre>
$f = new Cgn_ActiveFormatter($t['tel']);
echo $f->printAs('phone');
</pre>

<br/>
<hr/>
Telephone #2:
<?php
$f = new Cgn_ActiveFormatter($t['tel2']);
echo $f->printAs('phone');

?>

<p>
Code for formatting the telephone $t['tel2']:
</p>

<pre>
$f = new Cgn_ActiveFormatter($t['tel2']);
echo $f->printAs('phone');
</pre>


<br/>
<hr/>
Email:
<?php
$f = new Cgn_ActiveFormatter($t['email']);
echo $f->printAs('email');

?>

<p>
Code for formatting the email $t['email']:
</p>

<pre>
$f = new Cgn_ActiveFormatter($t['email']);
echo $f->printAs('email');
</pre>
