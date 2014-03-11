<?php
class LoginController extends Zend_Controller_Action 
{
	public function init() 
	{
		$this->_helper->layout->setLayout('admin');
	}
	
	public function loginAction() 
	{
		$form = new Square_Form_Login();
		$this->view->form = $form;
		
		if ( $this->getRequest()->isPost() ) {
			if ( $form->isValid($this->getRequest()->getPost()) ) {
				$values = $form->getValues();
				$adapter = new Square_Auth_Adapter_Doctrine(
					$values['username'], $values['password']
				);
				$auth = Zend_Auth::getInstance();
				$result = $auth->authenticate($adapter);
				if ( $result->isValid() ) {
					$session = new Zend_Session_Namespace('square.auth');
					$session->user = $adapter->getResultArray('Password');
					if ( isset($session->requestURL) ) {
						$url = $session->requestURL;
						unset($session->requestURL);
						$this->_redirect($url);
					} else {
						$this->_helper->getHelper('FlashMessenger')
							          ->addMessage('You were successfully logged in');
						$this->_redirect('/admin/login/success');
					}
				} else {
						$this->view->message = 
							'You could not be logged in. Please try again';	
				}
			}
		}
	}
	
	public function successAction() 
	{
		if ( $this->_helper->getHelper('FlashMessenger')->getMessages() ) {
			$this->view->messages = $this->_helper
										->getHelper('FlashMessenger')
										->getMessages();
		} else {
			$this->_redirect('/');
		}
	}
	
	public function logoutAction() 
	{
		Zend_Auth::getInstance()->clearIdentity();
		Zend_Session::destroy();
		$this->_redirect('/admin/login');
	}
}