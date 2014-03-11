<?php
class Square_Form_Configure extends Zend_Form 
{
	public function init() 
	{
		$this->setAction('/admin/config')
			 ->setMethod('post');
			 
		$default = new Zend_Form_Element_Text('defaultEmailAddress');
		$default->setLabel('Fallback email address for all operations:')
				->setOptions(array('size' => '40'))
				->setRequired(true)
				->addValidator('EmailAddress')
				->addFilter('HtmlEntities')
				->addFilter('StringTrim');
				
		$sales = new Zend_Form_Element_Text('salesEmailAddress');
		$sales->setLabel('Default email address for all sales enquiries:')
			  ->setOptions(array('size' => '40'))
			  ->addValidator('EmailAddress')
			  ->addFilter('HtmlEntities')
			  ->addFilter('StringTrim');
			  
		$items = new Zend_Form_Element_Text('itemsPerPage');
		$items->setLabel('Number of items per page in administrative views:')
			  ->setOptions(array('size' => '4'))
			  ->setRequired(true)
			  ->addValidator('Int')
			  ->addFilter('HtmlEntities')
			  ->addFilter('StringTrim');
			  
		$seller = new Zend_Form_Element_Radio('displaySellerInfo');
		$seller->setLabel('Seller name and address visible in public catalo:')
			   ->setRequired(true)
			   ->setMultiOptions(
			   		array(
			   			'1' => 'Yes',
			   			'0'	=> 'No'
			   		)
			   );
			   
		$log = new Zend_Form_Element_Radio('logExceptionsToFile');
		$log->setLabel('Exceptions logged to file:')
			->setRequired(true)
			->setMultiOptions(
			   		array(
			   			'1' => 'Yes',
			   			'0'	=> 'No'
			   		)
			   );
	
		$submit = new Zend_Form_Element_Submit('submit');
		$submit->setLabel('Save configuration')
			   ->setOptions(array('class' => 'submit'));
			   
		$this->addElement($sales)
			 ->addElement($default)
			 ->addElement($items)
			 ->addElement($seller)
			 ->addElement($log)
			 ->addElement($submit);
	}
}