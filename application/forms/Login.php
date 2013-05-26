<?php

class Application_Form_Login extends Zend_Form
{

    public function init()
    {
        /* Form Elements & Other Definitions Here ... */
    	$email = 	new Zend_Form_Element_Text('email');
	    $email	->setLabel('Enter Email:')
	    	  	->setRequired(true)
	    	 	->setAllowEmpty(false)
	   			->setDecorators(array(
	    	 			'ViewHelper','Description','Errors',
	    	 			array(array('data'=>'HtmlTag'), array('tag' => 'td')),
	    	 			array('Label', array('tag' => 'td')),
	    	 			array(array('row'=>'HtmlTag'),array('tag'=>'tr','openOnly'=>true))
	   			));
	    	 	
	   	$emailDetails = new Zend_Form_Element_Note("emailDetails");
	    $emailDetails	-> setValue("<span id=\"emailDetails\">So we can validate you</span>")
					    -> setDecorators(array(
					    		'ViewHelper','Description','Errors',
					    		array(array('data'=>'HtmlTag'), array('tag' => 'td'),array('tag' => 'td')),
					    		array('Label', array('tag' => 'td','openOnly'=>'true')),
					    		array(array('row'=>'HtmlTag'),array('tag'=>'tr','closeOnly'=>'true'))
					    ));
	    				
	    $password = new Zend_Form_Element_Password('password');
	    $password->setLabel('Enter Password:')
	      		 ->setRequired(true)
			     ->setDecorators(array(
			     		'ViewHelper','Description','Errors',
			     		array(array('data'=>'HtmlTag'), array('tag' => 'td')),
			     		array('Label', array('tag' => 'td','openOnly'=>'true')),
			     		array(array('row'=>'HtmlTag'),array('tag'=>'tr','openOnly'=>'true'))
			     ));
	    
	    $passwordDetails = new Zend_Form_Element_Note("passwordDetails");
	    $passwordDetails -> setValue("<span id=\"passwordDetails\">Password must be a minimum of 8 characters</span>")
					     ->setDecorators(array(
					    		'ViewHelper','Description','Errors',
					    		array(array('data'=>'HtmlTag'), array('tag' => 'td')),
					    		array('Label', array('tag' => 'td','openOnly'=>'true')),
					    		array(array('row'=>'HtmlTag'),array('tag'=>'tr','closeOnly'=>'true'))
					    ));
	    
	    $submit = 	new Zend_Form_Element_Submit('submit');
	    $submit ->setLabel('Click here to submit')
	    		->setDecorators(array(
	    		'ViewHelper',
	    		'Description',
	    		'Errors', 
	    		array(array('data'=>'HtmlTag'), array('tag' => 'td',
	    				'colspan'=>'2','align'=>'center')),
	    		array(array('row'=>'HtmlTag'),array('tag'=>'tr'))
	    		));

	    $this->addElements(array($email,$emailDetails,$password,$passwordDetails,$submit));	    
	    $this->setMethod('post');
	    $this->setDecorators(array(
	    		'FormElements',
	    		array(array('data'=>'HtmlTag'),array('tag'=>'table')),
	    		'Form'
	    ));
    }

}
