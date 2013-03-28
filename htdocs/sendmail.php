<?php

global $attributes, $logoutUrl, $AUTHGROUP, $ADMINGROUP, $nonce, $loginUrl;

require_once "../lib/inc.all.php";
$ret = Array();

if ($_REQUEST["nonce"] !== $nonce) {
  if (isset($_REQUEST["ajax"])) {
    $ret["msg"] = "CSRF Schutz - Seite neu laden.";
    header("Content-Type: text/json; charset=UTF-8");
    echo json_encode($ret);
  } else {
    echo "CSRF Schutz";
  }
  die();
}

if (!isLoggedIn()) {
  if (isset($_REQUEST["ajax"])) {
    $ret["msg"] = "Login nötig.";
    $ret["popupUrl"] = $loginUrl;
    header("Content-Type: text/json; charset=UTF-8");
    echo json_encode($ret);
  } else {
    requireAuth();
  }
  die();
}

if (!requireGroup($AUTHGROUP, !isset($_REQUEST["ajax"]))) {
  $ret["msg"] = "Berechtigung verweigert.";
  header("Content-Type: text/json; charset=UTF-8");
  echo json_encode($ret);
  die();
}

$mailinglists = getUserMailinglists();
if (!in_array($_REQUEST["from"], $mailinglists)) {
  if (isset($_REQUEST["ajax"])) {
    $ret["msg"] = "Unzulässiger Absender";
    header("Content-Type: text/json; charset=UTF-8");
    echo json_encode($ret);
  } else {
    header('HTTP/1.0 401 Unauthorized');
    include SGISBASE."/template/permission-denied.tpl";
  }
  die();
}

$from = $_REQUEST["from"];
$to = $_REQUEST["to"];
$cc = $_REQUEST["cc"];
$bcc = $_REQUEST["bcc"];
$subject = $_REQUEST["subject"];
$message = $_REQUEST["message"];
if (isset($_FILES["attachment"])) {
  $files = $_FILES["attachment"];
} else {
  $files = Array();
}

$r = multi_attach_mail($from, $to, $cc, $bcc, $subject, $message, $files);

if ($r === true) {
  if (isset($_REQUEST["ajax"])) {
    $ret["msg"] = "Die eMail wurde erfolgreich versendet.";
    header("Content-Type: text/json; charset=UTF-8");
    echo json_encode($ret);
  } else {
    include SGISBASE."/template/mail-sent.tpl";
  }
} else {
  if (isset($_REQUEST["ajax"])) {
    $ret["msg"] = $r->toString();
    header("Content-Type: text/json; charset=UTF-8");
    echo json_encode($ret);
  } else {
    $errMsg = $r->toString();
    include SGISBASE."/template/mail-notsent.tpl";
  }
}
die();
