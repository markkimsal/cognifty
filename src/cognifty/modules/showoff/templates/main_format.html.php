<p>
The active formatter class tries to clean up poorly formatted input and standardize the output.
Given an array of various phone numbers and emails, we can print them all in a standard format, or override the format if desired.
</p>

<p>
Original data is looks like this:
</p>
<pre>
<?= var_export($t); ?>
</pre>

<br/>
<hr/>
Telephone:
<?php
$f = new Cgn_ActiveFormatter($t['tel'], 'phone');
echo $f;

?>

<p>
Code for formatting the telephone $t['tel']:
</p>

<pre>
$f = new Cgn_ActiveFormatter($t['tel']);
echo $f;
</pre>

<br/>
<hr/>
Telephone #2:
<?php
$f = new Cgn_ActiveFormatter($t['tel2'], 'phone', '%d.%d.%d');
echo $f;

?>

<p>
Code for formatting the telephone $t['tel2']:
</p>

<pre>
$f = new Cgn_ActiveFormatter($t['tel2'], '%d.%d.%d');
echo $f;
</pre>


<br/>
<hr/>
Email:
<?php
$f = new Cgn_ActiveFormatter($t['email'], 'email');
echo $f;

?>

<p>
Code for formatting the email $t['email']:
</p>

<pre>
$f = new Cgn_ActiveFormatter($t['email']);
echo $f;
</pre>
