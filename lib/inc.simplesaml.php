<?php

global $SIMPLESAML, $SIMPLESAMLAUTHSOURCE, $attributes, $logoutUrl;

function getUserMailinglists() {
  global $attributes;
  requireAuth();
  return $attributes["mailinglists"];
}

function requireAuth() {
  global $SIMPLESAML, $SIMPLESAMLAUTHSOURCE;
  global $attributes, $logoutUrl, $loginUrl;

  require_once($SIMPLESAML.'/lib/_autoload.php');
  $as = new SimpleSAML_Auth_Simple($SIMPLESAMLAUTHSOURCE);
  $as->requireAuth();

  $attributes = $as->getAttributes();
  $logoutUrl = $as->getLogoutURL();
  $logoinUrl = $as->getLoginURL();
}

function requireGroup($group, $dodie=true) {
  global $attributes;

  requireAuth();

  if (count(array_intersect(explode(",",$group), $attributes["groups"])) == 0) {
    if (!$dodie) return false;
    header('HTTP/1.0 401 Unauthorized');
    include SGISBASE."/template/permission-denied.tpl";
    die();
  }
  return true;
}

function isLoggedIn() {
  global $SIMPLESAML, $SIMPLESAMLAUTHSOURCE;
  global $attributes, $logoutUrl, $loginUrl;
  require_once($SIMPLESAML.'/lib/_autoload.php');
  $as = new SimpleSAML_Auth_Simple($SIMPLESAMLAUTHSOURCE);
  $logoutUrl = $as->getLogoutURL();
  $logoinUrl = $as->getLoginURL();
  return $as->isAuthenticated();
}
