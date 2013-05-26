<?php

class Application_Model_TableFunctions
{
	
	public function getUserData($id)
	{
		$tbl = new Application_Model_DbTable_TblMyProfile();
		$rows = $tbl->find($id)->toArray();
		
		return $rows;
	}
}
