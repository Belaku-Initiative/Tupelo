<?php

class Application_Form_Myprofile extends Zend_Form
{

    public function init()
    {
        /* Form Elements & Other Definitions Here ... */
    	$userCountry = 	new Zend_Form_Element_Text('country');
    	$userCountry	->setLabel('Country:')
			    	->setRequired(true)
			    	->addValidator('NotEmpty', true);
    	
    	$userState = 	new Zend_Form_Element_Text('state');
    	$userState	->setLabel('State:')
			    	->setRequired(true)
			    	->addValidator('NotEmpty', true);
    	
    	$userCity = 	new Zend_Form_Element_Text('city');
    	$userCity	->setLabel('City:')
			    	->setRequired(true)
			    	->addValidator('NotEmpty', true);
    	
    	$userContact = 	new Zend_Form_Element_Text('contact');
    	$userContact 	->setLabel('Contact:')
						->setRequired(true)
				    	->addValidator('NotEmpty', true);
    	
    	$userImage = 	new Zend_Form_Element_Text('userImage');
    	$userImage	->setLabel('Image:')
			    	->setRequired(true)
			    	->addValidator('NotEmpty', true);
    	 
    	$userUniversity = 	new Zend_Form_Element_Text('university');
    	$userUniversity	->setLabel('University:')
					    ->setRequired(true)
					    ->addValidator('NotEmpty', true);
    	 
    	$submit = 	new Zend_Form_Element_Submit('submit');
    	$submit->setLabel('Click here to submit');
    	 
    	$this->addElements(array($userCountry,$userState,$userCity,$userContact,$userImage,$userUniversity,$submit));
    	$this->setAttrib('action','Myprofile/adduserprofile');
    	$this->setMethod('post');
    	
    }


}
