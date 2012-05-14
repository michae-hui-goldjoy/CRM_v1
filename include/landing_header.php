<?php
$user=explode('\\',$_SERVER['AUTH_USER']);
if(count($user)==0) $user=$_SERVER['AUTH_USER'];
else $user=$user[1];
$tmpusr=array(
	'peggy.gao'=>'sdda456s',
	'catherine.cheng'=>'gdfe8423t5',
	'simon.see'=>'fgd4435g',
	'michael.hui'=>'mic74mic',
	'lester.hui'=>'sdet5354fsd',
	'stephen.chan'=>'fge424t5g',
	'gary.lai'=>'asd37866524sdf',
	'fred.yip'=>'sdffg213tuh'
	);

if($_GET['user']!="") $user=$_GET['user'];
if(array_key_exists($user,$tmpusr))
{
?>
<body onload="javascript:document.DetailView.submit();">
<form action="http://srvgj05:83/index.php" method="post" name="DetailView" id="form">

<input type="hidden" name="module" value="Users">
<input type="hidden" name="action" value="Authenticate">
<input type="hidden" name="return_module" value="Users">
<input type="hidden" name="return_action" value="Login">
<input type="hidden" name="login_module" value="">
<input type="hidden" name="login_action" value="">
<input type="hidden" name="login_record" value="">
<input type="hidden" name="user_name" value="<? echo $user; ?>">
<input type="hidden" name="user_password" value="<? echo $tmpusr[strtolower(trim($user))]; ?>">
<input type="hidden" name="login_theme" value="">
<input type="hidden" name="login_language" value="en">

</form>
</body>
<?php 
break;
}else
{
	header("Location: http://srvgj05:83/");
}?>
