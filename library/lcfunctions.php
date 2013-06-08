<?php
$salt = "@#$&^%!(*)"; // Need to move it globaldefs
$oneday = 60*60*24;
define("tblLogin",1);
define("tbl_myProfile",2);
define("pwdresettbl",3);

define("ScrLogsEnable",0);//Enables or Disables the echo statements on screen
//Logging Utils
define("E",1);//ERROR
define("D",2);//DEBUG
/*******************************************************************************************
Name	   : getModule()
Description: Sends the user module currently in
Return     : Name of the user/module
********************************************************************************************/
function getModule()
{
   return "scooby";
}
/*******************************************************************************************
Name	   : getForm($formName)
Description: Creates a new form and returns the same based on the type of  form requested
Return     : Requested Form
********************************************************************************************/
function getForm($formName)
{
	// create form 
	switch($formName)
	{
		case "login" :
			$form = new Application_Form_Login();
			break;
	}
	return $form;
}
/********************************************************************************
Name	   : generateUuid()
Description: Generates a UUID
Return     : UUID
********************************************************************************/
function generateUuid()
{
	$hash   = sha1(microtime(true) . mt_rand());
	$uuid   = substr($hash, 0, 8) . '-'
			. substr($hash, 8, 4) . '-'
			. substr($hash, 12, 4) . '-'
			. substr($hash, 16, 4) . '-'
			. substr($hash, 20, 12);

	return $uuid;
}
/********************************************************************************
Name	   : getauthadapter()
Description: Generates Zend Auth Adapter For authentication
Params     : $username, since it is used when we generate the password, we use it
             to validate the user entered password
Return     : AuthAdapter
***************************************************************************/
function getauthadapter($username)
{
	$salt = "@#$&^%!(*)"; // Including the global variable
	$authAdapter = new Zend_Auth_Adapter_DbTable(Zend_Db_Table::getDefaultAdapter()); //Getting an instance
	$authAdapter->setTablename('tblLogin')
	->setidentitycolumn('email')
	->setcredentialcolumn('password')
	->setCredentialTreatment("SHA1(CONCAT('$salt','$username',?))"); //Using username as salt to avoid storing salt in DB

	return $authAdapter;
}
/********************************************************************************
Name	   : validateEmail()
Description: Check if it is a valid email using Zend_Validate_EmailAddress 
Params     : $email- the email which is to be validated
Return     : Bool{true if success}
*********************************************************************************/
function validateEmail($email)
{
	$emailvalidator = new Zend_Validate_EmailAddress();
	if ($emailvalidator->isValid($email))
	{
       return true;
	}
    return false;
}
/********************************************************************************
Name	   : doesDbRecordExist()
Description: Check if a record exists in a specified table, column 
Params     : $tableName,$columnName,$value 
Return     : Bool{true if success}
*********************************************************************************/
function doesDbRecordExist($tableName,$columnName,$value)
{
	$validator = new Zend_Validate_Db_RecordExists(
			array(
					'table' => $tableName,
					'field' => $columnName
			));
	
	if ($validator->isValid($value))
	{
		//The record exists in specified table
		return true;
	}
	return false;
}
/********************************************************************************
Name	   : dbTblFind()
Description: Find based on primary key 
Params     : $tableName, primaryKeyValue
Return     : Corresponding row if found
*********************************************************************************/
function dbTblFind($tableName,$primaryKeyValue)
{
	//db->find() is used to find an entry by using primary key
	switch($tableName)
	{
		case "tblLogin" :
			$table = new Application_Model_DbTable_TblLogin();
			break;
		case "tbl_myProfile" :
			$table = new Application_Model_DbTable_TblMyProfile();
			break;
		case "pwdresettbl" :
			$table = new Application_Model_DbTable_Pwdresettbl();
			break;

	}
	$rows = $table->find($primaryKeyValue);
	return $rows;	
}
 /********************************************************************************
Name	   : fetchRow()
Description: Fetch a row from a given table,colomn having given value
Params     : $tableName, $columnName , $value
Return     : Corresponding row if found
*********************************************************************************/
function fetchRow($tableName,$columnName,$value)
{
	switch($tableName)
	{
		case "tblLogin" :
			$table = new Application_Model_DbTable_TblLogin();
			break;
		case "tblMyProfile" :
			$table = new Application_Model_DbTable_TblMyProfile();
			break;
		case "pwdresettbl" :
			$table = new Application_Model_DbTable_Pwdresettbl();
			break;
	}
	$row = $table->fetchRow($table->select()->where($columnName.'= ?', $value));
	return $row; //Can be an array of rows if mulptiple rows present
}
function deleteRow()
{
	
}
/********************************************************************************
Name	   : sendEmail()
Description: Sends an email, to the recepient in $toEmail 
Params     : $fromEmail, $fromName , $toEmail,$toName,$subject,$body,$reserved
Return     : No return, end result is sending an email
*********************************************************************************/
function sendEmail($fromEmail, $fromName,$toEmail,$toName,$subject,$body,$reserved)
{
				
	$mail = new Zend_Mail();
	$mail->setBodyHtml($body);
	$mail->setFrom($fromEmail,$fromName);
	$mail->setSubject($subject);
	$mail->addTo($toEmail,$toName);
	$mail->send();
}
 /********************************************************************************
Name	   : getFB()
Description: Creates a FB object based on our APP Id
Params     : None
Return     : FB object, user information if exists
*********************************************************************************/
function getFB() {
	require 'facebook.php';
	$facebook = new Facebook(array(
			'appId'  => '190385524450312',
			'secret' => '032d258cf79b7d1be0ee1fa470e6b023',
	));
	return $facebook;
}
 /********************************************************************************
Name	   : print_scr()
Description: echo's the debug logs onto screen
Params     : $debug_msg, $debug_level
Return     : No Return
*********************************************************************************/
function print_scr($debug_level,$debug_msg)
{
	if(ScrLogsEnable)
	{
		$debug_level_info="n/a";
		$backtrace = debug_backtrace();
		
		switch($debug_level)
		{
			case E: $debug_level_info="ERROR:";
					break;
			case D: $debug_level_info="DEBUG:";
					break;
		}
		
		$fnTag="<font color='yellow'><b>FNAME:</b></font>";
		$fnName="<font color='red'>".$backtrace[1]['function']."</font>";
		$msgTag="<font color='yellow'><b>MSG:</b></font>";
		$msg="<font color='red'>".$debug_level_info.$debug_msg."</font><br>";
		$final_debug=$fnTag.$fnName.$msgTag.$msg;
		
		echo $final_debug;
	}
}

 /********************************************************************************
Name	   : isFbUser()
Description: Check if the user account associated with an email is Fb user
Params     : $email
Return     : True or False
*********************************************************************************/
function isFbUser($email)
{
	$table = new Application_Model_DbTable_TblLogin();
	$columnName = 'email';
	$row = $table->fetchRow($table->select()->where($columnName.'= ?', $email));
	$ifFbUser = 0; //Initializing
	if(count($row)!= 0)
	{
		$ifFbUser = $row->is_FB_User;
	}
	if($ifFbUser)
	{
		return true;
	}
	return false;
}
?>