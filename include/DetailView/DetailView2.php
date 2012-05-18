<?php
/*********************************************************************************
 * SugarCRM Community Edition is a customer relationship management program developed by
 * SugarCRM, Inc. Copyright (C) 2004-2012 SugarCRM Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY SUGARCRM, SUGARCRM DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact SugarCRM, Inc. headquarters at 10050 North Wolfe Road,
 * SW2-130, Cupertino, CA 95014, USA. or at email address contact@sugarcrm.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * SugarCRM" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by SugarCRM".
 ********************************************************************************/

require_once('include/TemplateHandler/TemplateHandler.php');
require_once('include/EditView/EditView2.php');

/**
 * DetailView - display single record
 * New implementation
 * @api
 */
class DetailView2 extends EditView
{
    var $view = 'DetailView';

    /**
     * DetailView constructor
     * This is the DetailView constructor responsible for processing the new
     * Meta-Data framework
     *
     * @param $module String value of module this detail view is for
     * @param $focus An empty sugarbean object of module
     * @param $id The record id to retrieve and populate data for
     * @param $metadataFile String value of file location to use in overriding default metadata file
     * @param tpl String value of file location to use in overriding default Smarty template
     */
    function setup(
        $module,
        $focus,
        $metadataFile = null,
        $tpl = 'include/DetailView/DetailView.tpl'
        )
    {
        $this->th = new TemplateHandler();
        $this->th->ss = $this->ss;
        $this->focus = $focus;
        $this->tpl = $tpl;
        $this->module = $module;
        $this->metadataFile = $metadataFile;
        if(isset($GLOBALS['sugar_config']['disable_vcr'])) {
           $this->showVCRControl = !$GLOBALS['sugar_config']['disable_vcr'];
        }
        if(!empty($this->metadataFile) && file_exists($this->metadataFile)){
        	require_once($this->metadataFile);
        }else {
        	//If file doesn't exist we create a best guess
        	if(!file_exists("modules/$this->module/metadata/detailviewdefs.php") &&
        	    file_exists("modules/$this->module/DetailView.html")) {
                global $dictionary;
        	    $htmlFile = "modules/" . $this->module . "/DetailView.html";
        	    $parser = new DetailViewMetaParser();
        	    if(!file_exists('modules/'.$this->module.'/metadata')) {
        	       sugar_mkdir('modules/'.$this->module.'/metadata');
        	    }
        	   	$fp = sugar_fopen('modules/'.$this->module.'/metadata/detailviewdefs.php', 'w');
        	    fwrite($fp, $parser->parse($htmlFile, $dictionary[$focus->object_name]['fields'], $this->module));
        	    fclose($fp);
        	}

        	//Flag an error... we couldn't create the best guess meta-data file
        	if(!file_exists("modules/$this->module/metadata/detailviewdefs.php")) {
        	   global $app_strings;
        	   $error = str_replace("[file]", "modules/$this->module/metadata/detailviewdefs.php", $app_strings['ERR_CANNOT_CREATE_METADATA_FILE']);
        	   $GLOBALS['log']->fatal($error);
        	   echo $error;
        	   die();
        	}
            require_once("modules/$this->module/metadata/detailviewdefs.php");
            
        }
        /*-------------Customize------------------*/
        if($this->module=='A1_Report')
        {
        	/*//connect db
	        $tdb=mysql_connect('192.168.1.7','root','goldjoy.it') or die('Error while connecting to database');
	        mysql_select_db('goldjoy_scrm',$tdb);
	        mysql_query('SET NAMES UTF8',$tdb);
	        
	        //fire
	        echo '<div id="fromphp" style="display:none">';
	        echo $_REQUEST['record'];
	        $r=mysql_query("SELECT * FROM A1_company",$tdb);
	        echo mysql_error($tdb);
	        var_dump(mysql_num_rows($r));
			echo '</div>';
			mysql_close($tdb);*/
        	echo '<div id="fromphp" style="display:none">';
        	
			//$a=file_get_contents("http://srvgj05:83/public/exportcsv.php?html=t&id=".$_REQUEST['record']);
			//var_dump($a);
        	$curl = curl_init();
        	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        	curl_setopt($curl, CURLOPT_URL, "http://192.168.1.7:83/public/exportcsv.php?html=t&id=".$_REQUEST['record']);
        	$result = curl_exec($curl);
        	echo $result;
        	curl_close($curl);
        	echo '</div>';
        	echo "<script>document.getElementById('DEFAULT').innerHTML=document.getElementById('DEFAULT').innerHTML+'<img src=\"/themes/Sugar5/images/Dropdown.gif\"><a href=\"/public/exportcsv.php?id=".$_REQUEST['record']."\" target=\"_blank\">Download EXCEL Report HERE</a><h4>Preview:</h4>'+document.getElementById('fromphp').innerHTML+'".$more."';</script>";
		}
		/*-------------Customize------------------*/
		
		$this->defs = $viewdefs[$this->module][$this->view];
    }

}
?>