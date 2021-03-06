<?php
class Square_Auth_Adapter_Doctrine implements Zend_Auth_Adapter_Interface
{
	protected $_resultArray;
	
	public function __construct($username, $password) 
	{
		$this->username = $username;
		$this->password = $password;
	}
	
	public function authenticate() 
	{
		$q = Doctrine_Query::create()
			->from('Square_Model_User u')
			->where('u.Username = ? AND u.Password = ?',
				array($this->username, $this->password)
			);
		$result = $q->fetchArray();
		if ( count($result) == 1 ) {
			return new Zend_Auth_Result(Zend_Auth_Result::SUCCESS, $this->username, array());
		} else {
			return new Zend_Auth_Result(Zend_Auth_Result::FAILURE, null, array('Authentication unsuccessful'));
		}
	}
	
	public function getResultArray($excludeFields  = null)
	{
		if ( !$this->_resultArray ) {
			return false;
		}
		
		if ( $excludeFields != null ) {
			$excludeFields = (array)$excludeFields;
			foreach ($this->_resultArray as $key => $value) {
				if ( !in_array($key, $excludeFields) ) {
					$returnArray[$key] = $value;
				}
			}
			return $returnArray;
		} else {
			return $this->_resultArray;
		}
	}
}