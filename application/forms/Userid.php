<?php
class Application_Form_Userid extends Zend_Form
{
	public function init()
	{
		$email = 	new Zend_Form_Element_Text('email');
		$email	->setLabel('Enter Email:')
		->setOptions(array('class'=>'txtBox'))
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
	
	
	$this->addElements(array($email,$emailDetails,$submit));
	$this->setMethod('post');
	//$this->setAttrib('action','resetpwd');
	$this->setDecorators(array(
			'FormElements',
			array(array('data'=>'HtmlTag'),array('tag'=>'table')),
			'Form'
			));
    }
}
?>