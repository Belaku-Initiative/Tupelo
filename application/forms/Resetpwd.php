<?php
class Application_Form_Resetpwd extends Zend_Form
{
	public function init()
	{
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
		->setOptions(array('class'=>'button'))
		->setDecorators(array(
				'ViewHelper',
				'Description',
				'Errors',
				array(array('data'=>'HtmlTag'), array('tag' => 'td',
						'colspan'=>'2','align'=>'center')),
				array(array('row'=>'HtmlTag'),array('tag'=>'tr'))
		));
		
		$this->addElements(array($email,$emailDetails,
				$password,$passwordDetails,
				$retypePassword,$retypePasswordDetails,
				$submit));
		$this->setAttrib('action','resetpwdemail');
		$this->setMethod('post');
		$this->setDecorators(array(
				'FormElements',
				array(array('data'=>'HtmlTag'),array('tag'=>'table')),
				'Form'
		));
	}
}
