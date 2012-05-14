<?php

/*------------------db--------------*/
mysql_connect('192.168.1.7','root','goldjoy.it') or die('Error while connecting to database');
mysql_select_db('goldjoy_scrm');
mysql_query('SET NAMES UTF8');

/*-----------------db--------------*/


//2do, get 4 list 
// 1) handler to parse $out
// 2) field name in header column

$rid=mysql_real_escape_string($_GET['id']);
$r=mysql_query("SELECT * FROM goldjoy_scrm.a1_report WHERE id='".$_GET['id']."'");
$out="";
$r=mysql_fetch_assoc($r);
$type=$r['reporttype'];
if($type=='company')
{
	$field=$r['company_field'];
	$field=str_replace('^','',$field);
	$sql="SELECT ".$field." FROM a1_company WHERE 1 ORDER BY orderid ASC LIMIT 0,50";
	$r=mysql_query($sql);
	while($rr=mysql_fetch_assoc($r))
	{
		$out.=iconv("utf-8","big5",implode(" \t ",$rr))."\n";
	}
}
elseif($type=='voter')
{
	$field=$r['voter_field'];
	$field=str_replace('^','',$field);
	$sql="SELECT id,".$field." FROM a1_voter WHERE 1 ORDER BY id ASC LIMIT 0,1000";
	$r=mysql_query($sql);
	while($rr=mysql_fetch_assoc($r))
	{
		$r2=mysql_query("SELECT a.name FROM a1_company a, a1_company_a1_voter_c b WHERE a.id=b.a1_company_a1_votera1_company_ida AND b.a1_company_a1_votera1_voter_idb='".$rr['id']."'");
		$r2=mysql_fetch_assoc($r2);
		
		$r3=mysql_query("SELECT first_name,last_name FROM users WHERE id='".$rr['assigned_user_id']."'");
		$r3=mysql_fetch_assoc($r3);
		unset($rr['id']);
		$rr['assigned_user_id']=$r3['first_name']." ".$r3['last_name'];
		$out.=iconv("utf-8","big5",implode(" \t ",$rr)." \t ".$r2['name']." \n");
	}
}
elseif($type=='Companyvoter')
{
	
}
elseif($type=='followup')
{
	
}



if($out=='')
{
	echo "Empty Type, cannot generate report.";
}
else
{
	header("Content-type: text/html; charset=big5");
	header('Content-type: application/ms-excel');
	$filename ="excelreport.xls";
	header('Content-Disposition: attachment; filename='.$filename);
	echo $out;	
}

/*
this is the backup from /wwwroot/crm/modules/A1_Report/metadata/detailviewdefs.php
<script>
document.getElementById('DEFAULT').innerHTML="<center><img src='/themes/Sugar5/images/Dropdown.gif'><a href='exportcsv.php?id=<?php echo $_GET['record']; ?>' target='_blank'>Download EXCEL Report HERE</a>"+document.getElementById('DEFAULT').innerHTML+'</center>';
</script>
 */



