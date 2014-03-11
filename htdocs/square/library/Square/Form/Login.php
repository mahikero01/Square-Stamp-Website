<?php
class Square_Form_Login extends Zend_Form 
{
	public function init() 
	{
		$this->setAction('/admin/login')
			 ->setMethod('post');
			 
		$username =  new Zend_Form_Element_Text('username');
		$username->setLabel('Username:')
				 ->setOptions(array('size' => '30'))
				 ->setRequired(true)
				 ->addValidator('Alnum')
				 ->addFilter('HtmlEntities')
				 ->addFilter('StringTrim');
				 
		$password = new Zend_Form_Element_Password('password');
		$password->setLabel('Password:')
				 ->setOptions(array('size' => '30'))
				 ->setRequired(true)
				 ->addFilter('HtmlEntities')
				 ->addFilter('StringTrim');
				 
		$submit = new Zend_Form_Element_Submit('submit');
		$submit->setLabel('Log In')
			   ->setOptions(array('class' => 'submit'));
			   
		$this->addElement($username)
			 ->addElement($password)
			 ->addElement($submit);
	}
}