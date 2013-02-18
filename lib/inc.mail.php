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

function multi_attach_mail($from, $to, $cc, $bcc, $subject, $message, $files){
    // parse fields
    $to = empty($to) ? Array() : Mail_RFC822::parseAddressList($to);
    if (PEAR::isError($to)) return $to;
    $cc = empty($cc) ? Array() : Mail_RFC822::parseAddressList($cc);
    if (PEAR::isError($cc)) return $cc;
    $bcc = empty($bcc) ? Array() : Mail_RFC822::parseAddressList($bcc);
    if (PEAR::isError($bcc)) return $to;
    $from = empty($from) ? Array() : Mail_RFC822::parseAddressList($from);
    if (PEAR::isError($from)) return $from;

    $cc = array_merge($cc, $from);

    // email fields: to, from, subject, and so on
    $header = Array();
    $header["Errors-To"]  = build_address_string($from);
    $header["From"]       = build_address_string($from);
    $header["Reply-To"]   = build_address_string($from);
    $header["To"]         = build_address_string($to);
    $header["Cc"]         = build_address_string($cc);
    $header["Message-Id"] = "<" . md5(uniqid(microtime())) . "@helfer.stura.tu-ilmenau.de>";
    $header["Date" ]      = date("r");
    $header["Subject"]    = "=?UTF-8?B?".base64_encode($subject)."?=";

    // boundary
    $semi_rand = md5(time());
    $mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";
 
    // headers for attachment
    $header["MIME-Version"] = "1.0";
    $header["Content-Type"] = "multipart/mixed;\n" . " boundary=\"{$mime_boundary}\"";
 
    // multipart boundary
    $message = "--{$mime_boundary}\n" . "Content-Type: text/plain; charset=\"utf-8\"\n" .
    "Content-Transfer-Encoding: base64\n\n" . chunk_split(base64_encode($message)) . "\n\n";
 
    // preparing attachments
    for($i=0;$i<count($files["name"]);$i++){
      if(is_uploaded_file($files["tmp_name"][$i])) {
        $message .= "--{$mime_boundary}\n";
        $data = file_get_contents($files["tmp_name"][$i]);
        $data = chunk_split(base64_encode($data));
        $message .= "Content-Type: ".mime_content_type($files["tmp_name"][$i])."; name=\"".$files["name"][$i]."\"\n" .
                    "Content-Description: ".$files["name"][$i]."\n" .
                    "Content-Disposition: attachment;\n" . " filename=\"".$files["name"][$i]."\"; size=".$files["size"][$i].";\n" .
                    "Content-Transfer-Encoding: base64\n\n" . $data . "\n\n";
      }
    }
    $message .= "--{$mime_boundary}--";

    $target = Array();
    foreach ($to as $a) { $target[] = $a->mailbox."@".$a->host; }
    foreach ($cc as $a) { $target[] = $a->mailbox."@".$a->host; }
    foreach ($bcc as $a) { $target[] = $a->mailbox."@".$a->host; }
    foreach ($from as $a) { $target[] = $a->mailbox."@".$a->host; }

    $mail_object =& Mail::factory('smtp', array("debug" => false, "timeout" => 10));
    return $mail_object->send(array_unique($target), $header, $message);
}

