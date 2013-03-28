<?php

global $nonce;

session_start();

if (!isset($_SESSION["nonce"]))
  $_SESSION["nonce"] = md5(mt_rand());
$nonce = $_SESSION["nonce"];

