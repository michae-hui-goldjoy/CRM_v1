<?
define('sugarEntry', TRUE);
session_start();
global $sugar,$uid,$user;
require_once("include/nusoap/nusoap.php");

mysql_connect('localhost','root','goldjoy.it') or die('Error while connecting to database');
mysql_select_db('goldjoy_scrm');
mysql_query('SET NAMES UTF8');

$field=array("id","orderid","comtype","licence_no","lang","name","name_eng","name_bus","phone_office","phone_fax","email1","company_address","place","district","regstatus","info_mailreturn","info_ticpoxy","info_2012cake","info_friendship","info_2011hktaoa","info_ymcalunch","info_2011cytalk","rating");
$votetype=array('^votetype_tic^','^votetype_legco^','^votetype_eac^');

function inemail($id,$email)
{
	$email=trim($email);
	$id=trim($id);
	$sql="INSERT INTO email_addresses (id,email_address,email_address_caps,date_created) VALUE ('".$id."','".$email."','".strtoupper($email)."','".date('Y-m-d H:i:s',(time()+60*60*8))."')";
	if(mysql_query($sql))
		return true;
	else
		return mysql_error();
}


function newuser($type,$comid,$name,$mobile='',$email='',$add='',$assuser=1)
{
	$usrfield=array('id','date_entered','created_by','assigned_user_id','first_name','last_name','phone_mobile','primary_address_street','votetype');
	$name=trim(mysql_real_escape_string($name));
	$mobile=trim(mysql_real_escape_string($mobile));
	$email=trim(mysql_real_escape_string($email));
	
	echo "<h3>".$email."</h3>";
	$add=trim(mysql_real_escape_string($add));
	$comid=intval($comid);
	
	$id=md5(time().$name);
	$sql1="INSERT INTO a1_voter (".implode(',',$usrfield).") VALUE ('".$id."','".date('Y-m-d H:i:s',(time()+60*60*8))."','1','".$assuser."','','".$name."','".$mobile."','".$add."','".$type."')";
	echo $sql1."<br>";
	if($email!="") $sql2="INSERT INTO email_addresses (id,email_address,email_address_caps,date_created) VALUE ('".$id."','".$email."','".strtoupper($email)."','".date('Y-m-d H:i:s',(time()+60*60*8))."')";
	$sql3="INSERT INTO a1_company_a1_voter_c (id,date_modified,a1_company_a1_votera1_company_ida,a1_company_a1_votera1_voter_idb) VALUE ('".md5(time().$name)."','".date('Y-m-d H:i:s',(time()+60*60*8))."','".$comid."','".$id."')";

	if(mysql_query($sql1))
	{
		if($email!="")
		{
			if(mysql_query($sql2))
			{
				echo '1'.$sql1.mysql_error();
				$flag=true;
			}
			else
			{
				echo '2'.$sql1.mysql_error();
				mysql_query("DELETE FROM a1_voter WHERE id='".$id."'");
				return mysql_error();
			}
		}

		if(mysql_query($sql3))
		{
			echo '3'.$sql1.mysql_error();
			return true;
		}
		else
		{
			echo '4'.$sql1.mysql_error();
			mysql_query("DELETE FROM a1_voter WHERE id='".$id."'");
			mysql_query("DELETE FROM email_addresses WHERE id='".$id."'");
			return mysql_error();
		}
	}
	else
	{
		echo '5'.$sql1.mysql_error();
		return mysql_error();
	}
}

if($_GET['act']=='clear')
{
	$table=array('a1_voter','email_addresses','a1_company','a1_company_a1_voter_c');
	foreach($table as $v)
		mysql_query("DELETE FROM ".$v);
}
elseif($_GET['act']=='newuser')
{

	$options = array(
			"location" => 'http://192.168.1.7:83/soap.php',
			"uri" => 'http://www.sugarcrm.com/sugarcrm',
			"trace" => 1
	);
	// connect to soap server
	$client = new SoapClient('http://192.168.1.7:83/soap.php', $options);
	
	// look what modules sugar exposes
	$response = $client->get_available_modules($session_id);
	var_dump($response);
}
$cnt=1;
if($_GET['act']=='submit')
{
	$a=file_get_contents($_FILES['file']['tmp_name']);
	$a=explode("\n",$a);
	unset($a[0]);
	foreach($a as $v)
	{
		
		/*-------cleaning data--------*/
		$v=explode("\t",$v);
		if($v[4]=="")
		{
			
		}
		else
		{

			for($i=0;$i<count($v);$i++)
			{
				$v[$i]=str_replace('"','',$v[$i]); //$v[$i]=str_replace('|',',',$v[$i]);
				$v[$i]=mysql_real_escape_string($v[$i]);
			}
			
			$v[3]=str_replace("E","Eng",$v[3]);
			$v[3]=str_replace("C","Chi",$v[3]);
			$v[19]=str_replace("(沒有委任 NOT APPOINTED)","",$v[19]);
			$v[15]=str_replace("(沒有委任 NOT APPOINTED)","",$v[15]);
			$v[14]=str_replace("(沒有委任 NOT APPOINTED)","",$v[14]);
			$v[13]=str_replace("（待更新委任）","",$v[13]);
			$v[14]=str_replace("（待更新委任）","",$v[14]);
			/*-------cleaning data--------*/
		
			//debug panel
			//echo "<div style='border:1px solid'>";var_dump($v);echo "</div>";

			//if there is a TIC reg number
			if(!is_numeric($v[2])) $v[2]=$v[0];
			
			//assigned user
			$assuser="";
			if(trim($v[30])!="")
			{
				$assuser=mysql_query("SELECT id FROM users WHERE LOWER(user_name) LIKE '%".strtolower(trim($v[30]))."%'");
				$assuser=mysql_fetch_assoc($assuser);
				$assuser=$assuser['id'];
			}

			//main SQL
			$sql="INSERT INTO a1_company (".implode(",",$field).") VALUE (";
			$sql.=$v[2].",";
			$arr=array();
			for($i=0;$i<=13;$i++)
				array_push($arr,"'".$v[$i]."'");
			//array_push($arr,"'".$assuser."'");
			for($i=33;$i<=39;$i++)
				array_push($arr,"'".mysql_real_escape_string($v[$i])."'");
			array_push($arr,"'".intval($v[41])."'");
			$sql.=implode(',',$arr);
			$sql.=") ";
			
			echo "<blockquote>";

			if(mysql_query($sql))
			{
				echo 'Company inserted successfully:'.$v[4].'<br>';
				$usertype=array();
				/*--------Handle TIC Voter--------*/
				$tic_er=explode(",",$v[14]);				
				foreach($tic_er as $er)
				{
					if(trim($er)!="")
					{
						if($er!=trim($v[19]))
						{ 
							$a=newuser($votetype[0],$v[2],$er,'','','',$assuser);
							if($a!=true) echo $a."<br>";
							else echo "inserted TIC Voter:".$er.'<br>';
						}
					}
				}
				/*--------Handle TIC Voter--------*/
				 	
				/*--------Handle Legco Voter--------*/
				if(($v[15]!=$v[19])&&(trim($v[15])!=""))
				{
					$type=$votetype[1];
					if(in_array(trim($v[15]),$tic_er)) $type.=','.$votetype[0];
					
					//add 15 P
					$a=newuser($type,$v[2],$v[15],'','','',$assuser);
					if($a!=true) echo $a."<br>";
					else echo 'inserted Legco Voter:'.$v[15].'<br>';
				}
				
				//add19 TUVW
				if(trim($v[19])!="")
				{
					$type=$votetype[1];
					if(in_array(trim($v[19]),$tic_er)) $type.=','.$votetype[0];
					
					$a=newuser($type,$v[2],$v[19],$v[20],$v[21],$v[22],$assuser);
					if($a!=true)
						echo $a."<br>";
					else
						echo "inserted Legco Voter with Info:".$v[19].'<br>';
				}
				/*--------Handle Legco Voter--------*/	
			}
			else
			{
				echo "Error while inserting ".mysql_error().'['.$sql.']<br>';
			}
			echo "</blockquote><hr>";
			unset($sql);unset($v);unset($arr);
			$cnt++;
			//if($cnt==100) break;
		}
		ob_flush();
		flush();
	}
}
else
{
	?>
	<form  enctype="multipart/form-data" action="?act=submit" method="POST">
	<input type="file" name="file">
	<input type="submit" value="Upload Company Data">
	</form>
	<?
}