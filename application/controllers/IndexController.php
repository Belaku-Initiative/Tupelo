<?php
require 'lcfunctions.php';

class IndexController extends Zend_Controller_Action
{
    protected $session;
	
	public function init()
    {
	    
        $this->session=new Zend_Session_Namespace("login_session");
    }

    public function indexAction()
    {
		//This is stubees.com homepage
		print_scr(D, session_id());	
		
		//Check if the session is already valid
		if(isset($this->session->name)) {
			if (isset($this->session->isFBUser)) {
			        //Fb User has logged in
					$user_profile=$this->session->userProfile;  
					$this->view->userFirstName = $user_profile['first_name'];
					$this->view->userLastName= $user_profile['last_name'];
					$this->view->fbUser = 1;
			} else {
					// regular user who has logged in
					$this->view->regularUser=1;
					$this->view->userFirstName=$this->session->userFirstName;
					$this->view->userLastName=$this->session->userLastName;
			}
		} else {
			//session expired or not started
			Zend_Session::destroy( true );
			print_scr(E,"Session Expired OR Not Started!!");
		}

    }	
}
