#!/usr/bin/php
<?php
require_once('../lib/tools.php');
require_once('../lib/nzdrm.php');
$argv[1] = '';
$nzdrm = new Nzdrm\Nzdrm();
$nzdrm::init();
$nzdrm::launch();
?>