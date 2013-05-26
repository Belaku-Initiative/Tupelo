<?php

class MyprofileController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // action body
    	$form = new Application_Form_Myprofile();
    	$this->view->form = $form;
    }
    
    public function adduserprofileAction()
    {
    	echo "adduserprofile action";
    }


}

