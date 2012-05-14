<?
define('sugarEntry', TRUE);
session_start();
global $sugar,$uid,$user;
require_once("include/nusoap/nusoap.php");

class general
{
	function general()
	{}

	public static function txtf($name,$check,$class="",$add="")
	{
		return "<input type='text' name='".$name."' class='".$class."' ".$add." value='".$check."'>";
	}

	public static function selef($name,$data,$POST,$class='',$add='',$first="")
	{
		$html="<select name='".$name."' class='".$class."' >";
		foreach($data as $k=>$v)
		{
			$html.="<option value='".$v."' ";
			if($POST[$name]==$v) $html.=" selected ";
			if(is_numeric($k)) $k=$v;
			$html.=">".$k."</option>";
		}
		$html.="</select>";
		return $html;
	}
	
	public static function chkf($name,$data,$POST,$class='',$add='')
	{
		$html=array();
		foreach($data as $k=>$v)
		{
			if($v!='')
			{
				$tmp="<input type='checkbox' name='".$name."[]' class='".$class."' value='".$v."' ".$add." ";
				if(in_array($v,$POST[$name]))
					$tmp.=" checked ";
				$tmp.=">";
				if($k=="") $k=$v;
				$tmp.=$v;
				array_push($html,$tmp);
				unset($tmp);
			}
		}
		return $html;
	}

	public static function dump($obj)
	{
		if(is_array($obj))
		{
			foreach($obj as $k=>$v)
			{
				echo $k."<br>";
				var_dump($v);
				echo "<hr>";
			}
		}
		else
		{
			var_dump($obj);
		}
	}
}

class SugarSoap{
		var $proxy,$client;
		var $uid;
		var $sess;
		var $type;	//	campaign/sales/all
		function SugarSoap($soap_url,$login=true,$sessid=''){
				$soapclient= new nusoapclient($soap_url,true);
				$this->proxy = $soapclient->getProxy();
				$this->client=$soapclient;
				if($sessid!='')
					$this->set_sess($sessid);

				elseif($login) $this->login();
		}

		function login($uname,$pwd){
			$params = array(
				'user_name' => $uname,
				'password'  => md5($pwd),
				'version'   => '.01'
			);
			$result = $this->proxy->login($params,'MyApp');
			$this->sess= $result['error']['number']==0 ? $result['id'] : null;
			$_SESSION['GJCRM']['sess']=$this->sess;
			$this->uid=$this->proxy->get_user_id($this->sess);
			return $this->sess;
		}

		function logout()
		{
			$this->proxy->logout($this->sess);
			unset($_SESSION['GJCRM']['sess']);
		}

		function set_sess($sid)
		{
			$this->sess=$sid;
			$this->uid=$this->proxy->get_user_id($this->sess);
		}

		public function get_data($mod,$id,$fieldarr)
		{
			$dt=$this->proxy->get_entry($this->sess,$mod, $id, $fieldarr);
			if($dt['error']['description']=="No Error")
			{
				$arr=array();
				foreach($dt['entry_list'][0]['name_value_list'] as $v)
					$arr[$v['name']]=$v['value'];
				return $arr;
			}
			else
			{
				return $dt['error']['description'];
			}
		}

		function get_user_list($field,$val)
		{
			$arr=array('entry_list'=>array());
			$mod=array('Contacts','Calls','Leads','Users');
			foreach($mod as $v)
			{

				if($this->type=='campaign')
				{
					if(in_array($v,array('Contacts','Leads')))
						$tmp=$this->proxy->get_entry_list($this->sess, $v, $v.'.'.$field.' LIKE "%'.$val.'%" AND '.strtolower($v).'_cstm.acctype_c LIKE "%Campaign%"',$field,0,10);
				}
				else
				{
					$tmp=$this->proxy->get_entry_list($this->sess, $v, $v.'.'.$field.' LIKE "%'.$val.'%"',$field,0,10);
				}
				if(count($tmp['entry_list'])>0)
					$arr['entry_list']=array_merge($arr['entry_list'],$tmp['entry_list']);
				unset($tmp['entry_list']);
				
			}
			$arr['result_count']=count($arr['entry_list']);
			return $arr;
		}


		function get_obj($id)
		{
			//get contact/lead
			$tmp=$this->get_user_list('id',$id);
			foreach($tmp['entry_list'] as $k=>$v)
			{
				$d=new sugarData($v);
				return $d;
			}
		}
}


class sugarData
{
	var $id,$mod,$data;

	function sugarData($entrylist_arr)
	{
		$this->id=$entrylist_arr['id'];
		$this->mod=$entrylist_arr['module_name'];
		$this->data=$entrylist_arr['name_value_list'];
	}

	function get($field)
	{
		
		foreach($this->data as $v)
			if($v['name']==$field)
				return $v['value'];
		return false;
	}

	function add_entry($sug,$mod,$data)
	{
		$insertid=$sug->proxy->set_entry($sug->sess,$mod, $data);
		if($insertid['id']!='')
		{
			if(in_array($this->mod,array('Leads','Contacts')))
			{
				$sug->client->call('set_relationship', array($sug->sess,array('module1' => 'Calls','module1_id' => $insertid['id'],'module2' => $this->mod,'module2_id' => $this->id)));
				//2DO: add error checking
			}
			return $insertid['id'];
		}
		else
		{
			return false;
		}
	}
}


$sugar=new SugarSoap('http://srvgj05:83/soap.php?wsdl',false);
//$sugar=new SugarSoap('http://127.0.0.1/scrm/service/v4/soap.php?wsdl',false);

if(($_GET['a']=='lgin')&&($sugar->sess==""))
{

	$sugar->login($_POST['username'],$_POST['pwd']);

}
elseif($_GET['a']=='lgot')
{
	$sugar->logout();
}
else
{
	if($_SESSION['GJCRM']['sess']!="")
	{
		$sugar->set_sess($_SESSION['GJCRM']['sess']);
		$uid=$sugar->proxy->get_user_id($sugar->sess);
	}
}

//user type
$uid=$sugar->proxy->get_user_id($sugar->sess);
//user type
$type=$sugar->get_data('Users',$uid,array('department','is_admin'));
if($type['is_admin']>0) $sugar->type="all";
else $sugar->type=$type['department'];