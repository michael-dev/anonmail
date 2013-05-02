<?php

global $attributes, $logoutUrl, $ADMINGROUP, $nonce;

require "../template/header.tpl";

?>
<h2>Anonymer Mailversand</h2>

<form action="sendmail.php" method="POST" enctype="multipart/form-data">
<input type="hidden" name="nonce" id="nonce" value="<?php echo $nonce; ?>">
<div class="table">
 <div class="tr">
  <div class="th">Absender:</div>
  <div class="td"><select id="from" name="from"><?php foreach ($mailinglists as $ml):?><option><?php echo htmlentities($ml);?></option><?php endforeach; ?></select></div>
 </div>
 <div class="tr">
  <div class="th">An:</div>
  <div class="td"><input id="to" type="text" name="to"></div>
 </div>
 <div class="tr">
  <div class="th">Kopie:</div>
  <div class="td"><input id="cc" type="text" name="cc"> + Absender</div>
 </div>
 <div class="tr">
  <div class="th">Blindkopie:</div>
  <div class="td"><input id="bcc" type="text" name="bcc"></div>
 </div>
 <div class="tr">
  <div class="th">Betreff:</div>
  <div class="td"><input id="subject" type="text" name="subject"></div>
 </div>
 <div class="tr">
  <div class="th">Nachricht</div>
  <div class="td"><textarea id="message" name="message"></textarea></div>
 </div>
 <div class="tr">
  <div class="th">Anlagen</div>
  <div class="td" id="attachments">
   <input type="file" id="files" name="attachment[]" multiple />
   <ul id="list"></ul>
  </div>
  <script type="text/javascript">
  var attachments = [];
  function handleFileSelect(evt) {
    if (evt != null) {
      var files = evt.target.files; // FileList object
      for (var i = 0, f; f = files[i]; i++) {
        attachments[attachments.length] = f;
      }
    }

    // files is a FileList of File objects. List some properties.
    var ul = $('#list');
    ul.empty();
    for (var i = 0, f; f = attachments[i]; i++) {
      var li = $('<li>').html('<strong>'+ escape(f.name)+ '</strong> ('+ (f.type || 'n/a') + ') - '+
                  f.size+ ' bytes, last modified: '+
                  f.lastModifiedDate.toLocaleDateString()).appendTo(ul);
      li.click(i, function(evt) { attachments.splice(evt.data, 1); handleFileSelect(null); });
    }
  }
  $('#files').val('');
  $('#files').change(handleFileSelect);
  </script>
 </div>
 <div class="tr">
  <div class="td"><input type="submit" name="submit" value="Absenden" onClick="doSubmit(); return false;"></div>
  <div class="td"><input type="reset" name="reset" value="Abbrechen"></div>
 </div>
</div>
</form>

<script type="text/javascript">
function doSubmit() {
  var data = new FormData();
  data.append( 'from' , $('#from').val() );
  data.append( 'to' , $('#to').val() );
  data.append( 'cc' , $('#cc').val() );
  data.append( 'bcc' , $('#bcc').val() );
  data.append( 'subject' , $('#subject').val() );
  data.append( 'message' , $('#message').val() );
  data.append( 'nonce' , $('#nonce').val() );
  data.append( 'ajax' , 1 );
  for (var i = 0, f; f = attachments[i]; i++) {
    data.append( 'attachment[]' , f );
  }
  $.ajax({
    url: 'sendmail.php',
    data: data,
    cache: false,
    contentType: false,
    processData: false,
    type: 'POST',
    error: function (jqXHR, textStatus, errorThrown) {
      t = window.open('','fehler');
      t.document.open('text/plain');
      t.document.writeln(textStatus);
      t.document.writeln(errorThrown);
      t.document.writeln(jqXHR.responseText);
      t.document.close();
    },
    success: function(data){
      if (data.popupUrl) {
        if (confirm(data.msg)) {
          window.open(data.popupUrl, '_blank');
        }
      } else {
        alert(data.msg);
      }
    }
  });
}
</script>

<hr/>
<a href="<?php echo $logoutUrl; ?>">Logout</a>

<?php
require "../template/footer.tpl";
