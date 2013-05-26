<?php

class Application_Form_Signup extends Zend_Form
{

    public function init()
    {
        /* Form Elements & Other Definitions Here ... */
    	
    	$firstName = 	new Zend_Form_Element_Text('firstName');

    	$firstName  ->setLabel('First Name:')
					->setOptions(array('class'=>'txtBox'))
    				->setRequired(true)
    				->setDecorators(array(
			    			'ViewHelper','Description','Errors',
			    			array(array('data'=>'HtmlTag'), array('tag' => 'td')),
			    			array('Label', array('tag' => 'td')),
			    			array(array('row'=>'HtmlTag'),array('tag'=>'tr','openOnly'=>true))
			    	));
    	
    	$firstNameDetails = new Zend_Form_Element_Note("firstNameDetails");
	    $firstNameDetails	-> setValue("<span id=\"firstNameDetails\">Put in your first name</span>")
					    	->setDecorators(array(
					    		'ViewHelper','Description','Errors',
					    		array(array('data'=>'HtmlTag'), array('tag' => 'td'),array('tag' => 'td')),
					    		array('Label', array('tag' => 'td','openOnly'=>'true')),
					    		array(array('row'=>'HtmlTag'),array('tag'=>'tr','closeOnly'=>'true'))
					    	));
					    
	    				
	    $lastName = 	new Zend_Form_Element_Text('lastName');
    	$lastName	->setLabel('Last Name:')
					->setOptions(array('class'=>'txtBox'))
			    	->setRequired(true)
			    	->setDecorators(array(
			    			'ViewHelper','Description','Errors',
			    			array(array('data'=>'HtmlTag'), array('tag' => 'td')),
			    			array('Label', array('tag' => 'td')),
			    			array(array('row'=>'HtmlTag'),array('tag'=>'tr','openOnly'=>true))
			    	));
    	
    	$lastNameDetails = new Zend_Form_Element_Note("lastNameDetails");
	    $lastNameDetails	-> setValue("<span id=\"lastNameDetails\">Put in your last name</span>")
					    	->setDecorators(array(
					    		'ViewHelper','Description','Errors',
					    		array(array('data'=>'HtmlTag'), array('tag' => 'td'),array('tag' => 'td')),
					    		array('Label', array('tag' => 'td','openOnly'=>'true')),
					    		array(array('row'=>'HtmlTag'),array('tag'=>'tr','closeOnly'=>'true'))
					    	));
	    				
	    $gender = 	new Zend_Form_Element_Text('gender');
    	$gender ->setLabel('Gender:')
				->setOptions(array('class'=>'txtBox'))
		    	->setRequired(true)
		    	->setDecorators(array(
		    			'ViewHelper','Description','Errors',
		    			array(array('data'=>'HtmlTag'), array('tag' => 'td')),
		    			array('Label', array('tag' => 'td')),
		    			array(array('row'=>'HtmlTag'),array('tag'=>'tr','openOnly'=>true))
		    	));
    	
    	$genderDetails = new Zend_Form_Element_Note("genderDetails");
	    $genderDetails	->setValue("<span id=\"genderDetails\">Male(M)/Female(F)/Other(O)</span>")
					    ->setDecorators(array(
					    		'ViewHelper','Description','Errors',
					    		array(array('data'=>'HtmlTag'), array('tag' => 'td'),array('tag' => 'td')),
					    		array('Label', array('tag' => 'td','openOnly'=>'true')),
					    		array(array('row'=>'HtmlTag'),array('tag'=>'tr','closeOnly'=>'true'))
					    ));
	    				
	    $contact = 	new Zend_Form_Element_Text('contact');
    	$contact->setLabel('Contact:')
				->setOptions(array('class'=>'txtBox'))
		    	->setRequired(true)
		    	->setDecorators(array(
		    			'ViewHelper','Description','Errors',
		    			array(array('data'=>'HtmlTag'), array('tag' => 'td')),
		    			array('Label', array('tag' => 'td')),
		    			array(array('row'=>'HtmlTag'),array('tag'=>'tr','openOnly'=>true))
		    	));
		    	
    	$contactDetails = new Zend_Form_Element_Note("contactDetails");
	    $contactDetails	->setValue("<span id=\"contactDetails\">Enter your phone number in format 'xxx-xxx-xxxx'</span>")
					    ->setDecorators(array(
					    		'ViewHelper','Description','Errors',
					    		array(array('data'=>'HtmlTag'), array('tag' => 'td'),array('tag' => 'td')),
					    		array('Label', array('tag' => 'td','openOnly'=>'true')),
					    		array(array('row'=>'HtmlTag'),array('tag'=>'tr','closeOnly'=>'true'))
					    ));
	    				
	    $email = 	new Zend_Form_Element_Text('email');
		
    	$email->setLabel('Enter Email:')
				->setOptions(array('class'=>'txtBox'))
		    	->setRequired(true)
    			->addErrorMessage('Value is empty, but a non-empty value is required.')
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
		
    	$password   ->setLabel('Choose Password:')
					->setOptions(array('class'=>'txtBox'))
			    	->setRequired(true)
			    	->setDecorators(array(
			    			'ViewHelper','Description','Errors',
			    			array(array('data'=>'HtmlTag'), array('tag' => 'td')),
			    			array('Label', array('tag' => 'td')),
			    			array(array('row'=>'HtmlTag'),array('tag'=>'tr','openOnly'=>true))
			    	));
    	
    	$passwordDetails = new Zend_Form_Element_Note("passwordDetails");
	    $passwordDetails	-> setValue("<span id=\"passwordDetails\">Must be 8 or more characters</span>")
					    	->setDecorators(array(
					    		'ViewHelper','Description','Errors',
					    		array(array('data'=>'HtmlTag'), array('tag' => 'td'),array('tag' => 'td')),
					    		array('Label', array('tag' => 'td','openOnly'=>'true')),
					    		array(array('row'=>'HtmlTag'),array('tag'=>'tr','closeOnly'=>'true'))
					    	));
	    				
	    $retypePassword = new Zend_Form_Element_Password('retypePassword');
		
    	$retypePassword ->setLabel('Retype Password:')
						->setOptions(array('class'=>'txtBox'))
				    	->setRequired(true)
				    	->setDecorators(array(
				    			'ViewHelper','Description','Errors',
				    			array(array('data'=>'HtmlTag'), array('tag' => 'td')),
				    			array('Label', array('tag' => 'td')),
				    			array(array('row'=>'HtmlTag'),array('tag'=>'tr','openOnly'=>true))
				    	));
				    	
    	$retypePasswordDetails = new Zend_Form_Element_Note("retypePasswordDetails");
	    $retypePasswordDetails	-> setValue("<span id=\"retypePasswordDetails\">Re-enter the password</span>")
						    	-> setDecorators(array(
						    		'ViewHelper','Description','Errors',
						    		array(array('data'=>'HtmlTag'), array('tag' => 'td'),array('tag' => 'td')),
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

	    
    	$this->addElements(array($firstName,$firstNameDetails,
    							$lastName,$lastNameDetails,
    							$gender,$genderDetails,
    							$contact,$contactDetails,
    							$email,$emailDetails,
    							$password,$passwordDetails,
    							$retypePassword,$retypePasswordDetails,
    							$submit));
    	$this->setAttrib('action','adduser');
    	$this->setMethod('post');
    	$this->setDecorators(array(
    			'FormElements',
    			array(array('data'=>'HtmlTag'),array('tag'=>'table')),
    			'Form'
    	));
    	 
    }


}

