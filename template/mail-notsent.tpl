<?php

include SGISBASE."/template/header.tpl";

?>

<h1>Mail nicht versandt.</h1>

Die eMail wurde nicht versandt:
<pre>
<?php echo htmlspecialchars($errMsg); ?>
</pre>

<a href="<?php echo htmlentities($logoutUrl)?>">Abmelden</a>

<?php

include SGISBASE."/template/footer.tpl";

