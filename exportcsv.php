<?php

/*------------------db--------------*/
mysql_connect('192.168.1.7','root','goldjoy.it') or die('Error while connecting to database');
mysql_select_db('goldjoy_scrm');
mysql_query('SET NAMES UTF8');
/*-----------------db--------------*/

function votelabel($str)
{
	if(!stripos($str,'legco')===false)
		return '立法會';
	if(!stripos($str,'tic')===false)
		return 'TIC';
	
	return 'N/A';
}
function voterfieldlabel($str)
{
	$arr=array('votetype'=>'TYPE','first_name'=>'','last_name'=>'NAME','phone_mobile'=>'MOBILE','email1'=>'EMAIL','description'=>'DESCRIPTION','assigned_user_id'=>'ASSIGNED TO');
	return $arr[$str];
}
function votercompanylabel($str)
{
	$arr=array('lang'=>'LANG','name'=>'NAME','name_eng'=>'ENG NAME','name_bus'=>'BUSINESS NAME','phone_office'=>'PHONE','company_address'=>'ADDRESS','place'=>'PLACE','district'=>'DISTRICT','regstatus'=>'REG STATUS 立法會','orderid'=>'EXCEL ORDER ID','licence_no'=>'LICENCE NO.','email1'=>'EMAIL','phone_fax'=>'FAX','rating'=>'RATING','info_mailreturn'=>'郵件退回','info_ticpoxy'=>'TIC-POXY','info_2012cake'=>'2012送年糕','info_friendship'=>'友好程度','info_2011hktaoa'=>'2011東主會','info_ymcalunch'=>'YMCA LUNCH','info_2011cytalk'=>'2011CY講座');
	return $arr[$str];
}
if($_GET['html']=='t') $limit=" LIMIT 0, 50";
//2do, get 4 list 
// 1) handler to parse $out
// 2) field name in header column

$rid=mysql_real_escape_string($_GET['id']);
$r=mysql_query("SELECT * FROM goldjoy_scrm.a1_report WHERE id='".$_GET['id']."'");
$out="";
$r=mysql_fetch_assoc($r);
$type=$r['reporttype'];

$assigneduser=$r['user_id_c'];
/*if($assigneduser!="")
{
	$assigneduser=mysql_query("SELECT user_name FROM users WHERE id='".$assigneduser."'");
	$assigneduser=mysql_fetch_assoc($assigneduser);
	$assigneduser=$assigneduser['user_name'];
}*/

if($type=='company')
{
	$field=$r['company_field'];
	$field=str_replace('^','',$field);
	$sql="SELECT ".$field." FROM a1_company WHERE 1 ORDER BY orderid ASC ".$limit;
	$r=mysql_query($sql);
	$out.="<table  cellpadding=0 cellspacing=0><tr>";
	
	foreach(explode(",",$field) as $v)
		$out.="<th style='border:1px solid'>".votercompanylabel($v)."</th>";
	$out.="</tr>";
	while($rr=mysql_fetch_assoc($r))
	{
		$out.="<tr>";
		foreach(explode(",",$field) as $v)
			$out.="<td style='border:1px solid'>".$rr[$v]."</td>";
		$out.="</tr>";
	}
	$out.="</table>";
}
elseif($type=='voter')
{
	$field=$r['voter_field'];
	$field=str_replace('^','',$field);
	if($assigneduser!="") $add=" AND a1_voter.assigned_user_id='".$assigneduser."'";
	$sql="SELECT id,".$field." FROM a1_voter WHERE 1  ".$add." ORDER BY id ASC ".$limit;
	$r=mysql_query($sql);
	if(mysql_num_rows($r)>0)
	{
		$out.="<table style='boder:1px' cellpadding=0 cellspacing=0><tr>";
		foreach(explode(",",$field) as $v)
			if($field!='first_name')
				$out.="<th style='border:1px solid'>".voterfieldlabel($v)."</th>";
		$out.="<th style='border:1px solid'>COMPANY</th></tr>";
		while($rr=mysql_fetch_assoc($r))
		{
			$r2=mysql_query("SELECT a.name FROM a1_company a, a1_company_a1_voter_c b WHERE a.id=b.a1_company_a1_votera1_company_ida AND b.a1_company_a1_votera1_voter_idb='".$rr['id']."'");
			$r2=mysql_fetch_assoc($r2);
			
			$r3=mysql_query("SELECT first_name,last_name FROM users WHERE id='".$rr['assigned_user_id']."'");
			$r3=mysql_fetch_assoc($r3);
			unset($rr['id']);
			$rr['assigned_user_id']=$r3['first_name']." ".$r3['last_name'];
			$rr['votetype']=votelabel($rr['votetype']);
			$out.="<tr><td style='border:1px solid'>".implode("</td><td style='border:1px solid'>",$rr)."</td><td style='border:1px solid'>".$r2['name']."</td></tr>"."\n";
		}
		$out.="</table>";
	}
	else
	{
		$out.="No record";
	}
	
}
elseif($type=='Companyvoter')
{
	
}
elseif($type=='followup')
{
	$field=$r['voter_field'];
	$voter=$r['a1_voter_id_c'];
	if($voter!="") 
	{
		$log=mysql_query("SELECT a.* FROM a1_log a, a1_voter_a1_log_c b WHERE b.a1_voter_a1_loga1_log_idb=a.id AND b.a1_voter_a1_loga1_voter_ida='".$voter."'".$limit);
		if(mysql_num_rows($log)==0)
			$log=mysql_query("SELECT a.* FROM a1_log a, a1_log_a1_voter_c b WHERE b.a1_log_a1_votera1_log_idb=a.id AND b.a1_log_a1_votera1_voter_ida='".$voter."'".$limit);
	}
	else
	{
		$log=mysql_query("SELECT * FROM a1_log WHERE 1 ORDER BY id DESC".$limit);
	}
	if(mysql_num_rows($log)>0)
	{
		$out.="<table style='boder:1px' cellpadding=0 cellspacing=0>";
		$first=true;
		while($llog=mysql_fetch_assoc($log))
		{
			//get directly from sql
			if($first)
			{
				
				$usr=mysql_query("SELECT a.*,c.name as comname FROM a1_voter a,a1_company_a1_voter_c b,a1_company c WHERE a.id='".$voter."' AND a.id=b.a1_company_a1_votera1_voter_idb AND b.a1_company_a1_votera1_company_ida=c.id");
				
				$usr=mysql_fetch_assoc($usr);
				$out.="<tr><td colspan=4>Voter Name:".$usr['first_name'].$usr['last_name']."<br>Email:".$usr['email1']."<br>Mobile Phone:".$usr['phone_mobile']."<br>Vote Type:".votelabel($usr['votetype'])."<br>Company:".$usr['comname']."</td></tr>"."\n";
				$first=false;
				$aun=mysql_query("SELECT first_name,last_name FROM users WHERE id='".$llog['created_by']."'");
				$aun=mysql_fetch_assoc($aun);
				$aun=$aun['first_name']." ".$aun['last_name'];
				$out.="<tr><th style='border:1px solid'>SUBJECT</th><th style='border:1px solid'>DESCRIPTION</th><th style='border:1px solid'>TIME CREATED</th><th style='border:1px solid'>CREATED BY</th></tr>";
			}
			
			//get from foreach
			$out.="<tr><td style='border:1px solid'>".$llog['name']."</td><td style='border:1px solid'>".$llog['description']."</td><td style='border:1px solid'>".$llog['logtime']."</td><td style='border:1px solid'>".$aun."</tr>"."\n";
		}
		$out.="</table>";
	}
	else
	{
		$out.="No record.";
	}
}



if($out=='')
{
	echo "Empty Type, cannot generate report.";
}
elseif($_GET['html']=='t')
{
	//echo iconv("utf-8","big5",$out);
	echo $out."<br>Please download excel for complete record.";
}
else
{
	header("Content-type: text/html; charset=big5");
	header('Content-type: application/ms-excel');
	$filename ="excelreport.xls";
	header('Content-Disposition: attachment; filename='.$filename);
	//echo iconv("utf-8","big5",$out);
	echo $out;	
}

/*
this is the backup from /wwwroot/crm/modules/A1_Report/metadata/detailviewdefs.php
<script>
document.getElementById('DEFAULT').innerHTML="<center><img src='/themes/Sugar5/images/Dropdown.gif'><a href='exportcsv.php?id=<?php echo $_GET['record']; ?>' target='_blank'>Download EXCEL Report HERE</a>"+document.getElementById('DEFAULT').innerHTML+'</center>';
</script>
$michael="linus";
 */

/*
asdasd
 */



