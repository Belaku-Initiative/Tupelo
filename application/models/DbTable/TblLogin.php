<?php

class Application_Model_DbTable_TblLogin extends Zend_Db_Table_Abstract
{

    protected $_name = 'tblLogin';
    
    public function loginTblInsert($array)
    {
    	$newUser = new Application_Model_DbTable_TblLogin();
    	$newUser->insert($array);
    }


}

