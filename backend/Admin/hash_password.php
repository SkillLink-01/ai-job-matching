<?php
$password = "SL00814010GeMeSaTiTs5";  // change this to your real password
$hashed = password_hash($password, PASSWORD_BCRYPT);
echo "Hashed Password: " . $hashed;
?>
