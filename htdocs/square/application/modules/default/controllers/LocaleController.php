<?php
class LocaleController extends Zend_Controller_Action 
{
	public function indexAction() {
		if 
			( 
				Zend_Validate::is(
					$this->getRequest()->getParam('locale'),
					'InArray',
					array(
						'haystack' => array(
							'en_US', 'fil_PH'
						)
					)
				)	
			) {
			$session = new Zend_Session_Namespace('square.l10n');
			$session->locale = $this->getRequest()->getParam('locale');
		}
		$url = $this->getRequest()->getServer('HTTP_REFERER');
		$this->_redirect($url);
	}
}