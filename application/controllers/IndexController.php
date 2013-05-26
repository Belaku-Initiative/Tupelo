<?php
require 'lcfunctions.php';

class IndexController extends Zend_Controller_Action
{
    public function init()
    {
        /* Initialize action controller here */
		print_scr(D,"IndexController(init)Creating Zend Session");
    }

    public function indexAction()
    {
		/* //This is stubees.com homepage
		$session = new Zend_Session_Namespace();
		print_scr(D, session_id());	
		
		//Check if the session is already valid
		if(isset($session->name)) {
			$this->view->userLoggedIn="true";
		} else {
			//session expired or not started
			print_scr(E,"Session Expired OR Not Started!!");
		} */
		echo Zend_Version::VERSION; 
		$storage = new Zend_Auth_Storage_Session();
		$data = $storage->read();
		if(!$data){
			echo "Session Expired OR Not Started!!";
		} else{
			echo "Success!! ".$data->email; 
			// print_scr(D,$data->email); 
		}	
    }


}
