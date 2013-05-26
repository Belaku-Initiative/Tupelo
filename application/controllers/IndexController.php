<?php
require 'lcfunctions.php';

class IndexController extends Zend_Controller_Action
{
    public function init()
    {
        /* Initialize action controller here */
	
    }

    public function indexAction()
    {
		//This is stubees.com homepage
		$session = new Zend_Session_Namespace('login_session');
		print_scr(D, session_id());	
		
		//Check if the session is already valid
		if(isset($session->name)) {
			$this->view->userLoggedIn="true";
			$this->view->userName=$session->userName;
		} else {
			//session expired or not started
			Zend_Session::destroy( true );
			print_scr(E,"Session Expired OR Not Started!!");
		}

    }

		
    }




