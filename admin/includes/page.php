<?php
$pid = $_GET['pid'];
require($ROOT."/functions/benfund_connect.php");

benfund_connect();
$query = "SELECT pid, title, content, author, date, access FROM pages WHERE pid = '$pid' ";
$results = mysql_query($query) or die(mysql_error());
$row = mysql_fetch_row($results) or die(mysql_error());

$pid = $row[0];
$ptitle = $row[1];
$content = $row[2];
$pauthor = $row[3];
$pdate = $row[4];
$paccess = $row[5];
?>