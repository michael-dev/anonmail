<?php
# use PEAR Mail package
require_once 'Mail.php';
require_once "PEAR.php";
require_once "Mail/RFC822.php";

function build_address_string($addrs) {
  $a = Array();
  foreach ($addrs as $addr) {
    if ($addr->personal != "") {
      $a[] = "=?UTF-8?B?".base64_encode($addr->personal)."?= <".$addr->mailbox."@".$addr->host.">";
    } else {
      $a[] = $addr->mailbox."@".$addr->host;
    }
  }
  return implode(", ", array_unique($a));
}

function multi_attach_mail_arg_parse(&$args) {
  foreach (["to", "cc", "bcc", "from"] as $i) {
    $args[$i] = empty($args[$i]) ? [] : Mail_RFC822::parseAddressList($args[$i]);
    if (PEAR::isError($args[$i])) return $args[$i];
  }
  return true;
}

function multi_attach_mail_build_hdr($args, $mime_boundary) {
  $header = Array();
  $header["Errors-To"]  = build_address_string($args["from"]);
  $header["From"]       = build_address_string($args["from"]);
  $header["Reply-To"]   = build_address_string($args["from"]);
  $header["To"]         = build_address_string($args["to"]);
  $header["Cc"]         = build_address_string($args["cc"]);
  $header["Message-Id"] = "<" . md5(uniqid(microtime())) . "@helfer.stura.tu-ilmenau.de>";
  $header["Date" ]      = date("r");
  $header["Subject"]    = "=?UTF-8?B?".base64_encode($args["subject"])."?=";

  // headers for attachment
  $header["MIME-Version"] = "1.0";
  $header["Content-Type"] = "multipart/mixed;\n" . " boundary=\"{$mime_boundary}\"";

  return $header;
}

function multi_attach_mail_build_msg($args, $mime_boundary) {
  $message = "--{$mime_boundary}\n";
  $message .= "Content-Type: text/plain; charset=\"utf-8\"\n";
  $message .= "Content-Transfer-Encoding: base64\n\n";
  $message .= chunk_split(base64_encode($args["message"])) . "\n\n";
  $message = "--{$mime_boundary}";

  return $message;
}

function multi_attach_mail_append($mime_boundary, $fsrc, $fname, $fsize) {
  $data = file_get_contents($fsrc);
  $data = chunk_split(base64_encode($data));
  $message = "\n" .
              "Content-Type: " . mime_content_type($fsrc) . "; name=\"" . $fname . "\"\n" .
              "Content-Description: " . $fname . "\n" .
              "Content-Disposition: attachment;\n" . " filename=\"".$fname."\"; size=".$fsize.";\n" .
              "Content-Transfer-Encoding: base64\n\n" . $data . "\n\n" .
              "--{$mime_boundary}";
  return $message;
}

function multi_attach_mail_target($args) {
  $target = [];
  foreach (["to","cc","bcc","from"] as $idx) {
    foreach ($args[$idx] as $a) {
      $target[] = $a->mailbox."@".$a->host;
    }
  }
  return array_unique($target);
}

// multi_attach_mail(["from" => $from, "to" => $to, "cc" => $cc, "bcc" => $bcc, "subject" => $subject, "message" => $message, "files" => $files]);
function multi_attach_mail($args) {
    // parse fields
    $r = multi_attach_mail_arg_parse($args);
    if ($r !== true) return $r;

    // boundary
    $semi_rand = md5(time());
    $mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";

    // email fields: to, from, subject, and so on
    $header = multi_attach_mail_build_hdr($args, $mime_boundary);

    // multipart boundary
    $message = multi_attach_mail_build_msg($args, $mime_boundary);

    // preparing attachments
    for ($i=0; $i<count($args["files"]["name"]); $i++) {
      if ( !is_uploaded_file($args["files"]["tmp_name"][$i]))
        continue;
      $message .= multi_attach_mail_append($mime_boundary, $args["files"]["tmp_name"][$i], $args["files"]["name"][$i], $args["files"]["size"][$i]);
    }

    $target = multi_attach_mail_target($args);

    $mail_object =& Mail::factory('smtp', array("debug" => false, "timeout" => 10));
    return $mail_object->send(array_unique($target), $header, $message);
}

