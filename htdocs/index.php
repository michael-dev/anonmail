<?php

global $attributes, $logoutUrl, $AUTHGROUP, $ADMINGROUP, $nonce, $loginUrl;

require_once "../lib/inc.all.php";

requireGroup($AUTHGROUP);
$mailinglists = getUserMailinglists();
sort($mailinglists);

require "../template/mailform.tpl";
