<?php

class Catalog_ItemController extends Zend_Controller_Action
{
	public function init()
	{
		$this->view->doctype('XHTML1_STRICT');
    // initialize context switch helper
    $contextSwitch = $this->_helper->getHelper('contextSwitch');
    $contextSwitch->addActionContext('search', 'xml')
                  ->initContext();
	}

	public function displayAction()
	{		
		$filters = array('id' => array(
			'HtmlEntities', 'StripTags', 'StringTrim')
		);
		
		$validators = array('id' => array(
			'NotEmpty', 'Int')
		);

		$input = new Zend_Filter_Input($filters, $validators);
		$input->setData($this->getRequest()->getParams());		
			if ( $input->isValid() ) {
				$q = Doctrine_Query::create()
					->from('Square_Model_Item i')
					->leftJoin('i.Square_Model_Country c')
					->leftJoin('i.Square_Model_Grade g')	
					->leftJoin('i.Square_Model_Type t')
					->where('i.RecordID = ?', $input->id)
					->addWhere('i.DisplayStatus = 1')
					->addWhere('i.DisplayUntil >= CURDATE()');
				$result = $q->fetchArray();
				if ( count($result) == 1 ) {
					$this->view->item = $result[0];
					$this->view->images = array();
					$config = $this->getInvokeArg('bootstrap')->getOption('uploads');
					foreach (glob("{$config['uploadPath']}/{$this->view->item['RecordID']}_*") as $file) {
						$this->view->images[] = basename($file);
					}
					$configs = $this->getInvokeArg('bootstrap')->getOption('configs');
					$localConfig = new Zend_Config_Ini($configs['localConfigPath']);
					$this->view->seller = $localConfig->user->displaySellerInfo;
					$registry = Zend_Registry::getInstance();
					$this->view->locale = $registry->get('Zend_Locale');
					$this->view->recordDate = new Zend_Date($result[0]['RecordDate']);
				} else {
				throw new Zend_Controller_Action_Exception
					('Page not found', 404);
				}
			} else {
				throw new Zend_Controller_Action_Exception
				('Invalid input');
			}
	}

	public function createAction()	
	{
		$form = new Square_Form_ItemCreate;
		$this->view->form = $form;
		if ( $this->getRequest()->isPost() ) {
			if ( $form->isValid($this->getRequest()->getPost()) ) {
				$item = new Square_Model_Item;
				$item->fromArray($form->getValues());
				$item->RecordDate = date('Y-m-d', mktime());
				$item->DisplayStatus = 0;
				$item->DisplayUntil = null;
				$item->save();
				$id = $item->RecordID;
				$config = $this->getInvokeArg('bootstrap')->getOption('uploads');
				$form->images->setDestination($config['uploadPath']);
				$adapter = $form->images->getTransferAdapter();
				for ($x=0; $x<$form->images->getMultiFile(); $x++) {
					$xt = @pathinfo($adapter->getFileName('images_'.$x.'_'), PATHINFO_EXTENSION);
					$adapter->clearFilters();
					$adapter->addFIlter(
						'Rename',
						array(
							'target'    => sprintf('%d_%d.%s', $id, ($x+1), $xt),
							'overwrite' => true
						)
					);
					$adapter->receive('images_'.$x.'_');
				};
				$this->_helper->getHelper('FlashMessenger')
				->addMessage(
				'Your submission has been accepted as item #' 
				. $id . 
				'. A moderator will review it and, if approved,
				it will appear on the site within 48 hours.');
				$this->_redirect('/catalog/item/success');
			}
		}
	}

	public function successAction()
	{
		if ( $this->_helper->getHelper('FlashMessenger')
			->getMessages() ) {
			$this->view->messages = $this->_helper
				->getHelper('FlashMessenger')
				->getMessages();
		} else {			
			$this->_redirect('/');
		}
	}
	
	public function searchAction() 
	{
		$form = new Square_Form_Search();
		$this->view->form = $form;
		
		if ( $form->isValid($this->getRequest()->getParams()) ) {
			$input = $form->getValues();
			
			if ( !empty($input['q']) ) {
				$config = $this->getInvokeArg('bootstrap')->getOption('indexes');
				$index = Zend_Search_Lucene::open($config['indexPath']);
				$results = $index->find(Zend_Search_Lucene_Search_QueryParser::parse($input['q']));
				$this->view->results = $results;
			}
		}
	}
}
