<?php
require ("../includes/globals.php");
$page_title = "Page Manager";
/*require ($ROOT."/admin/includes/pages.php");*/
require($ROOT."/functions/benfund_connect.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $page_title ?> - BenFund</title>
<?php include ($ROOT."/includes/head.php"); ?>
</head>
<body>
<div class="container">
<table cellspacing="0" cellpadding="0" align="center">
<!--HEADER START-->
  <tr>
    <td colspan="2" valign="top">
<?php include ($ROOT."/admin/includes/header.php"); ?>
	</td>
  </tr>
  <!--HEADER END-->
  <!--LEFT COLUMN START-->
  <tr>
    <td class="leftcolumn" width="150px" valign="top">
<?php include ($ROOT."/includes/left.php"); ?>
    </td>
	<!--LEFT COLUMN END-->
    <td valign="top">
	<!--PATHWAY START-->
<?php include ($ROOT."/includes/pathway.php"); ?>
	<!--PATHWAY END-->
	<!--MAINBODY START-->
	<div class="content_outer">
	<div class="content_inner">
<span class="pagetitle">Page Manager</span><p>
<div class="hr"></div>
<table class="clear">
<tr><td>
<table class="toolbar" align="right">
<tr><td align="center"><a class="toolbar" href="https://www.benfund.com/admin/new_page.php"><img src="https://www.benfund.com/images/elements/icons/new_page.gif" border="0"/><br />New Page</a></td>
<td align="center"><a class="toolbar" href="https://www.benfund.com/admin/delete_page.php"><img src="https://www.benfund.com/images/elements/icons/delete_page.gif" border="0"/><br />Delete</a></td>
</tr></table>
</td></tr></table>

This example shows you how TinyMCE can be configurated to function with Word content in the best possible way. TinyMCE is configured to auto convert/cleanup pasted Word content in this example. It's has also a custom CSS that makes paragraphs marginless as in Word.<br /><p><p><p>
<?php

if($_GET["cmd"]=="del")
{
$pid = $_GET['pid'];
benfund_connect();
    $sql = "DELETE FROM pages WHERE pid = $pid";
    $result = mysql_query($sql);
    echo '<div class="action"><span class="actionmsg2">Page Deleted!</span></div><p>';
}

if($_GET["cmd"]=="edit")
{
$newtitle = $_POST['title'];
$newcontent = $_POST['content'];
$newauthor = $_POST['author'];
$newdate = $_POST['date'];
$newaccess = $_POST['access'];
$pid = $_POST['pid'];
benfund_connect();
$query0 = "UPDATE pages SET title='$newtitle', content='$newcontent', author='$newauthor', date='$newdate', access='$newaccess' WHERE pid='$pid'";
mysql_query($query0) or die(mysql_error());
echo '<div class="action"><span class="actionmsg">Page Updated!</span></div><p>';
}

if($_GET["cmd"]=="new")
{
$newtitle = $_POST['title'];
$newcontent = $_POST['content'];
$newauthor = $_POST['author'];
$newdate = $_POST['date'];
$newaccess = $_POST['access'];
benfund_connect();
$query0 = "INSERT INTO pages (title, content, author, date, access) VALUES('$newtitle', '$newcontent', '$newauthor', '$newdate', '$newaccess')";
mysql_query($query0) or die(mysql_error());
$lastid = mysql_insert_id();
echo '<div class="action"><span class="actionmsg">Page Added!</span></div><p align="center"><span class="actionmsg">Page URL:<br /> https://www.benfund.com/page.php?pid='.$lastid.'</span></p>';
}
?>
<table width="95%" align="center" border="0" cellspacing="0" cellpadding="4" class="adminlist">

<tr>
<th valign="top" width="5" class="tablehead"><input name="toggle" value="" onclick="checkAll(50);" type="checkbox"></th><th valign="top" width="20" class="tablehead"><b>ID</b></th><th valign="top" class="tablehead"><b>Title</b></th><th valign="top" class="tablehead"><b>Access</b></th><th valign="top" class="tablehead"><b>Author</th><th valign="top" class="tablehead"><b>Date</b></th><th valign="top" class="tablehead">&nbsp;</th>
</tr>
<?php

benfund_connect();
$query = "SELECT pid, title, content, author, date, access FROM pages WHERE pid like '%' ";
$result = mysql_query($query) or die(mysql_error());
$color1 = "row0";
$color2 = "row1";
$row_count = 0;
/*$row = mysql_fetch_row($result) or die(mysql_error());*/
while ($row = mysql_fetch_row($result)) {
$pid = $row[0];
$ptitle = $row[1];
$content = $row[2];
$pauthor = $row[3];
$pdate = $row[4];
$paccess = $row[5];
$row_color = ($row_count % 2) ? $color1 : $color2; 
?>
<tr class="<?php echo $row_color; ?>"><td valign="top" width="20"><input id="cb0" name="cid[]" value="<?php echo $pid; ?>" onclick="isChecked(this.checked);" type="checkbox"></td><td valign="top" width="20"><?php echo $pid; ?></td><td valign="top" width="250"><a href="edit_page.php?pid=<?php echo $pid; ?>"><?php echo $ptitle; ?></a></td><td valign="top"><?php echo $paccess; ?></td><td valign="top"><?php echo $pauthor; ?></td><td valign="top"><?php echo $pdate; ?></td><td valign="top"><a href="page_manager.php?cmd=del&pid=<?php echo $pid; ?>"><img src="https://www.benfund.com/images/elements/icons/sm/delete.gif" border="0" /></a></td></tr>
<?php
$row_count++;
 } ?>
</table>
	</div>
	</div>
	</td>
  </tr>
  <!--MAINBODY END-->
  <!--FOOTER START-->
  <tr>
    <td colspan="2">
      <font color="#FEFFC1">
      <?php include ($ROOT."/includes/footer.php"); ?>
	  </font></td>
  </tr>
  <!--FOOTER START-->
</table>
<?php include ($ROOT."/includes/foot.php"); ?>
</body>
</html>

</body>
</html>