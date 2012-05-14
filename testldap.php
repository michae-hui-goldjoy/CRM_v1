<?php 
//define('sugarEntry', TRUE);
//if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
if(!defined('sugarEntry')) define('sugarEntry', true);
var_dump($_SERVER);
exit();

class SugarTalker
{
	private $url;
	private $username;
	private $password;
	private $soapclient;
	private $sessionId;

	function __construct()
	{
		$this->url = "http://srvgj05:83/soap.php";  // Replace with your location of SugarCRM
		$this->username = "GOLDJOY\michael.hui";
		$this->password = "mic74mic";

		$this->soapclient = new SoapClient(null,
				array('location' => $this->url,
						'uri'            => 'http://www.sugarcrm.com/sugarcrm',
						'soap_version'   => SOAP_1_1,
						'trace'          => 1,
						'exceptions'     => 0
				));
	}
	public function createSession()
	{
		$user_auth = array(
				'user_name' => $this->username,
				'password' => md5($this->password),
				'version' => $this->soapclient->get_server_version()
		);
	
		$application = "Some description of your program here";
		$result = $this->soapclient->login($user_auth, $application);
		var_dump($result);
		foreach($result as $k=>$v)
		{
			var_dump($k.'='.$v);
			echo '<hr>';
		}
		$session_id = $result->id;
		$result = $this->soapclient->seamless_login($session_id);
	
		if ($result)
		{
			$this->sessionId = $session_id;
			return $session_id;
		}
		else
		{
			error_log("There is a problem with creating a SugarCRM session: {$result}");
			return FALSE;
		}
	}
}
$sugar = new SugarTalker();

if (!$sugar->createSession())
{
	echo 'failed';// Looks like authentication didn't work..do something
}
else{
	
	echo 'bingo';
}


/*
function ldap()
{
	
	$ldap_url = 'srvgj.GoldJoy.local';
	$ldap_domain = 'GoldJoy.local';
	$ldap_dn = "dc=GoldJoy,dc=local";
	
	$ds = ldap_connect( $ldap_url );
	ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
	ldap_set_option($ds, LDAP_OPT_REFERRALS, 0);
	
	$username = "michael.hui";
	//must always check that password length > 0
	$password = "mic74mic";
	
	// now try a real login
	$login = ldap_bind( $ds, "$username@$ldap_domain", $password );
	echo '- Logged In Successfully<br/><br/>';
	try{
		$attributes = array("displayname", "mail",
				"department",
				"title",
				"physicaldeliveryofficename",
				"manager");
		$filter = "(&(objectCategory=person)(sAMAccountName=$username))";
	
		$result = ldap_search($ds, $ldap_dn, $filter, $attributes);
	
		$entries = ldap_get_entries($ds, $result);
	
		if($entries["count"] > 0){
			//echo print_r($entries[$i],1)."<br />";
			echo "<b>User Information:</b><br/>";
			echo "displayName: ".$entries[0]['displayname'][0]."<br/>";
			echo "email: ".$entries[0]['mail'][0]."<br/>";
			echo "department: ".$entries[0]['department'][0]."<br/>";
			echo "title: ".$entries[0]['title'][0]."<br/>";
			echo "office: ".$entries[0]['physicaldeliveryofficename'][0]."<br/>";
			//echo "manager: ".$entries[$i]['manager'][0]."<br/>";
			$manager_result = ldap_search($ds,
					$entries[0]['manager'][0],
					'(objectCategory=person)',
					array("displayname"));
	
			$manager_entries = ldap_get_entries($ds, $manager_result);
			if($manager_entries["count"] > 0){
				echo "manager: ". $manager_entries[0]['displayname'][0];
			}
		}
	}catch(Exception $e){
		ldap_unbind($ds);
		return;
	}
	ldap_unbind($ds);
	echo '<br/><br/>- Logged Out';
}
*/
?>
