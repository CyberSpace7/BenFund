<?php
session_start();
if (!isset($_SESSION[valid_client])){
     header('Location:../login.php');
}else{
$page_title = "Advanced Editor";
require ("../includes/globals.php");
require($ROOT."/functions/common.php");
require ($ROOT."/functions/editor.php");
$error = '<font color="#0000FF"><strong>You must be logged in to view this page</strong></font>';
benfund_connect();
$query = "SELECT id, m_type, name, contact_name, address, address2, city, state, zip, phone, alt_phone, email, ssn2, tax2, password, pin, goal, activated, settings FROM merchant WHERE id = '$mid' ";
$results = mysql_query($query) or die(mysql_error());
$row = mysql_fetch_array($results) or die(mysql_error());
$uid = $row['id'];
$m_type = $row['m_type'];
$name = $row['name'];
$c_name = $row['contact_name'];
//Here we define out main variables
$welcome_string="Welcome!";
$numeric_date=date("G");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>BenFund - Advanced Flyer Editor</title>
<?php include ($ROOT."/includes/head.php") ?>
<?php simple_editor(); ?>
<script language="javascript" type="text/javascript">
function addtext() {
	var newtext = "test";
	document.myform.mce_editor_0.value += newtext;
}
</script>
<script type="text/javascript" src="https://www.benfund.com/includes/js/preload.js"></script>
<script type="text/javascript">womOn();</script>

<body onLoad="document.getElementById('loading').style.display = 'none';">
<div class="container">
<table cellspacing="0" cellpadding="0" align="center">
<!--HEADER START-->
  <tr>
    <td colspan="2" valign="top">
<?php include ($ROOT."/includes/header.php") ?>
	</td>
  </tr>
  <!--HEADER END-->
  <!--LEFT COLUMN START-->
  <tr>
    <td class="leftcolumn" width="150px" valign="top">
<?php include ($ROOT."/includes/left.php") ?>
    </td>
	<!--LEFT COLUMN END-->
    <td valign="top" valign="top">
	<!--PATHWAY START-->
<?php include ($ROOT."/includes/pathway.php") ?>
	<!--PATHWAY END-->
	<!--MAINBODY START-->
	<?php m_menu3(); ?>
	<div class="content_outer">
	<div class="content_inner">
<span class="pagetitle">Advanced Flyer Editor</span>
<div class="hr"></div>
<form action="<?=$PHP_SELF?>" method="post" enctype="multipart/form-data" name="myform" id="myform">
<a href="javascript:tinyMCE.execCommand('mceInsertContent', false, '<img src=https://www.benfund.com/images/illuminati.jpg border=0>');"><div class="buttongreen">Insert Image</div></a></p>
<span class="subtitle" >Footer:</span><br />You can put your contact information here of feelfree to do something original like a slogan, phrase or quote.<br />
	<textarea style="width: 550px; height: 100px" name="outputtext" id="outputtext">
		<?php echo $footer ?>
	</textarea>

        <?php
if (isset($HTTP_POST_VARS['submit'])) {
  if (!is_uploaded_file($HTTP_POST_FILES['file']['tmp_name'])) {
    $error2 = "You did not upload a file!";
    unlink($HTTP_POST_FILES['file']['tmp_name']);
    // assign error message, remove uploaded file, redisplay form.
  } else {
    //a file was uploaded
    $maxfilesize=1625292;

    if ($HTTP_POST_FILES['file']['size'] > $maxfilesize) {
      $error2 = "file is too large";
      unlink($HTTP_POST_FILES['file']['tmp_name']);
      // assign error message, remove uploaded file, redisplay form.
    } else {
      if ($HTTP_POST_FILES['file']['type'] != "image/pjpeg" && $HTTP_POST_FILES['file']['type'] != "image/jpeg") {
        $error2 = "This file type is not allowed";
        unlink($HTTP_POST_FILES['file']['tmp_name']);
        // assign error message, remove uploaded file, redisplay form.
      } else {
       //File has passed all validation, copy it to the final destination and remove the temporary file:
       $filename = $HTTP_POST_FILES['file']['name'];
	   copy($HTTP_POST_FILES['file']['tmp_name'],"images/$id.jpg");
       unlink($HTTP_POST_FILES['file']['tmp_name']);
       require ("functions/image_resize.php");
	   $img = "images/$id.jpg";
	   scale($img,'480','360');
	   echo "The file $filename has been successfully uploaded!";

		include("approval_email.php");

	   }
    }
  }
}
?>
        <p>
          <?php
		 $im = "images/approved/$id.jpg";
		 $im2 = getimagesize($im);
		 $im = "https://www.benfund.com/images/approved/$id.jpg";
		 if(isset($im2[0])){ ?>
          <img SRC="<?php echo $im; ?>"> 
          <?php } ?>
          <br>
          <?php if (isset($error2)){ echo $error2; }
		  
     ?>
          <br>
          <p>This Image will be available for use on your flyer.</p>
          Please Choose an image to upload:<br>
          <input type="file" name="file" class="big" size="30">
          <br>
          <b>Allowable maximum image size is 1.5MB and must have a .jpg extension</b><p>
          Images are subject to approval.<p>
          Images may not contain nudity, sexually explicit content, violent, offensive 
          material, or be copyrighted. Do not upload images of other people without 
          their permission.<p> 
          Image approval can take as long as two business days.<br>
          When the image has been approved, you will recieve an email informing 
          you of approval and the image will appear above.</p>
       
        <p>
          <input type="submit" name="submit" value="Upload Image" class="big">
        </p>
      </form>
	</div>
	</div>
	</td>
  </tr>
  <!--MAINBODY END-->
  <!--FOOTER START-->
  <tr>
    <td colspan="2">
<?php include ($ROOT."/includes/footer.php") ?>
	</td>
  </tr>
  <!--FOOTER START-->
</table>
<?php include ($ROOT."/includes/foot.php"); ?>
</body>
</html>
<?php
}
?>
