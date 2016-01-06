<?php
$hash = sha1("pwdserietv");
setcookie('token', $hash, (time()+(60*60*24*365)), '/');
?>