<?php

class Application_Model_DbTable_TblMyProfile extends Zend_Db_Table_Abstract
{

    protected $_name = 'tblMyProfile';
    
    public function addNewProfile($array)
    {
    	$userProfile = new Application_Model_DbTable_TblMyProfile();
    	$userProfile->insert($array);
    }


}

