#!/usr/bin/php
<?php
require_once('../lib/tools.php');
require_once('../lib/nzdrm.php');

$nzdrm = new Nzdrm\Nzdrm();
$nzdrm::init();
if (empty($argv[1]))
	$nsdrm::get_from_db();
$nzdrm::launch($argv[1]);
?>