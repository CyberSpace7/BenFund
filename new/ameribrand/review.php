<html>
<head>
<title>Untitled Document</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<?
$fname = $_POST['fname'];
$lname = $_POST['lname'];
$address = $_POST['address'];
$city = $_POST['city'];
$state = $_POST['state'];
$zip = $_POST['zip'];
$pnumber = $_POST['pnumber'];
$product = $_POST['product'];
$email = $_POST['email'];
$check = ( empty($fname) + empty($lname) + empty($address) + empty($city) + empty($state) + empty($zip) + empty($pnumber) + empty($product) + empty($email) );
if($check > 0)
{echo '<h1><font color="red"> You did not fill out every field <br> All fields are required</h1></font>';
echo '<input name="Go Back" type="button" onClick="history.go(-1)" value="Go Back">';}
else{

echo '<h2><font color="#blue">Please review the following information and be sure 
  it is correct</font></h2>
</p>';

echo '<form name="form1" method="post" action="submit.php">
  <table width="65%" border="0" cellpadding="2">
    <tr> 
      <td width="55%"><div align="right">
	   First Name:</div></td>';
echo      '<td width="45%"> <strong>';
echo   "$fname";
 

	'</strong>  </td>
    </tr>';
	
echo   '<tr> 
      <td><div align="right">Last Name:</div></td>
      <td><strong>';
echo "$lname";
echo	  '</strong></td>
    </tr>
    <tr> 
      <td><div align="right">Address:</div></td>
      <td><strong>'; 
echo "$address";
echo	'</strong></td>
    </tr>
    <tr> 
      <td><div align="right">City:</div></td>
      <td><strong>'; 
echo "$city";
echo  '</strong></td>
    </tr>
    <tr> 
      <td><div align="right">State:</div></td>
      <td><strong>'; 
echo "$state";
echo  '</strong></td>
    </tr>
    <tr> 
      <td><div align="right">Zip Code:</div></td>
      <td><strong>'; 
echo "$zip"; 
echo  '</strong></td>
    </tr>
    <tr> 
      <td height="26"><div align="right">Phone Number :</div></td>
      <td><strong>';
echo "$pnumber"; 
echo  '</strong></td>
    </tr>
	<tr>
      <td><div align="right">E-Mail Address</div></td>
      <td><strong> ';
echo "$email";	
echo	'</strong></td>
    </tr>
    <tr>
      <td><div align="right">Product:</div></td>
      <td> <strong>';
  	  switch($product)
	  {
	  case 'pbfl' :
	  echo 'Paintball Field (Large)';
	  break;
	  case 'pbfm' :
	  echo 'Paintball Field (Medium)';
	  break;
	  case 'pbfs' :
	  echo 'Paintball Field (Small)';
	  break;
	  case '0' :
	  echo '<h2><font color="red"> You did not make a selection! </h2></font>';
	  break;
	  }

echo 	  '</strong></td>
    </tr>
    <tr> 
      <td><div align="right">
          <p></p>
          <p></p>
        </div></td>
      <td> 
        <input name="Go Back" type="button" id="gb" onClick="history.go(-1)" value="Go Back">
        <input type="submit" name="Submit" value="Submit"></td>
    </tr>
  </table>  <p align="center"> </p>
</form>
<p> </p>';}
?>
</body>
</html>