<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	protected function _initDoctype()
	{
		$this->bootstrap('view');
		$view = $this->getResource('view');
		$view->doctype('XHTML1_STRICT');
	}
	protected function _initViewHelpers()
	{
		$this->bootstrap('view');
		$view = $this->getResource('view'); //get the view object
		//jquery lib includes here (default loads from google CDN)
		$view->addHelperPath("ZendX/JQuery/View/Helper", "ZendX_JQuery_View_Helper");
		$view->jQuery()->enable()//enable jquery ; ->setCdnSsl(true) if need to load from ssl location
			 ->setVersion('1.5')//jQuery version, automatically 1.5 = 1.5.latest
			 ->setUiVersion('1.8')//jQuery UI version, automatically 1.8 = 1.8.latest
			 ->addStylesheet('https://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/ui-lightness/jquery-ui.css')//add the css
			 ->uiEnable();//enable ui
	}
}

