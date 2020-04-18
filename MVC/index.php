<?php
session_start();

require ("Registry/registry.class.php");

$reg = MGPDINAMIC::singleton();

//$reg->getUrlData();
$log = $reg->getLoginStatus();

$reg->getObject("page")->do_page();
echo $reg->getObject("page")->page_body;

exit();



