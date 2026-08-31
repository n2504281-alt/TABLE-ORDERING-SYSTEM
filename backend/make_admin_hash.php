<?php
echo "Run from CLI: php make_admin_hash.php 'YourStrongPassword'\n";
if(isset($argv[1])) echo password_hash($argv[1],PASSWORD_DEFAULT).PHP_EOL;
