<?php
class Square_Form_ItemCreate extends Zend_Form
{

	public function init() 
	{

		$this->setAction('/catalog/item/create')
			 ->setMethod('post');
			 
		$name = new Zend_Form_Element_Text('SellerName');
		$name->setLabel('Name:')
			 ->setOptions(array('size' => '35'))
			 ->setRequired(true)
			 ->addValidator('Regex', false, array('pattern' => '/^[a-zA-Z]+[A-Za-z\'\-\. ]{1,50}$/'))
			 ->addFilter('HtmlEntities')
			 ->addFilter('StringTrim');
			 
		$email = new Zend_Form_Element_Text('SellerEmail');
		$email->setLabel('Email address:')
			 ->setOptions(array('size' => '50'))
			 ->setRequired(true)
			 ->addValidator('EmailAddress', false)
			 ->addFilter('HtmlEntities')
			 ->addFilter('StringTrim')
			 ->addFilter('StringToLower'); 
			 
		$tel = new Zend_Form_Element_Text('SellerTel');
		$tel->setLabel('Telephone number:')
			->addValidator('StringLength', false, array('min' => 8))
			->addValidator('Regex', false, array(
				'pattern' => '/^\+[1-9][0-9]{6,30}$/',
				'messages' => array(
					Zend_Validate_Regex::INVALID => 
						'\'%value\' does not match international number
						format +XXYYZZZZ',
					Zend_Validate_Regex::NOT_MATCH => 
						'\'%value\' does not match international number
						format +XXYYZZZZ',
				)
			))
			->addFilter('HtmlEntities')
			->addFilter('StringTrim');
			
		$address = new Zend_Form_Element_Textarea('SellerAddress');
		$address->setLabel('Postal address:')
				->setOptions(array('rows' => '6', 'cols' => '36'))
				->addFilter('HtmlEntities')
				->addFilter('StringTrim');
				

		$title = new Zend_Form_Element_Text('Title');
		$title->setLabel('Title')
			  ->setOptions(array('size' => '60'))
			  ->setRequired(true)
			  ->addFilter('HtmlEntities')
			  ->addFilter('StringTrim');
			  
		$year = new Zend_Form_Element_Text('Year');
		$year->setLabel('Year')
			 ->setOptions(array('size' => '8', 'length' => '4'))
			 ->setRequired(true)
			 ->addValidator('Between', false,  array(
			 	'min' => 1700, 'max' => 2015
			 ))
			 ->addFilter('HtmlEntities')
			 ->addFilter('StringTrim');  
			 
		$country = new Zend_Form_Element_Select('CountryID');
		$country->setLabel('Country:')
			 ->setRequired(true)
			 ->addValidator('Int')
			 ->addFilter('HtmlEntities')
			 ->addFilter('StringTrim')
			 ->addFilter('StringToUpper'); 
		foreach ($this->getCountries() as $c) {
			$country->addMultiOption($c['CountryID'], $c['CountryName']);
		}
		
		$denomination = new Zend_Form_Element_Text('Denomination');
		$denomination->setLabel('Denomination:')
					 ->setOptions(array('size' => '8'))
					 ->setRequired(true)
					 ->addValidator('Float')
					 ->addFilter('HtmlEntities')
					 ->addFilter('StringTrim');
					 
		$type = new Zend_Form_Element_Radio('TypeID');
		$type->setLabel('Type:')
			 ->setRequired(true)
			 ->addValidator('Int')
			 ->addFilter('HtmlEntities')
			 ->addFilter('StringTrim');
		foreach ($this->getTypes() as $t) {
			$type->addMultiOption($t['TypeID'], $t['TypeName']);
		}
		$type->setValue(1);
		
		$grade = new Zend_Form_Element_Select('GradeID');
		$grade->setLabel('Grade:')
			  ->setRequired(true)
			  ->addValidator('Int')
			  ->addFilter('HtmlEntities')
			  ->addFilter('StringTrim');
		foreach ($this->getGrades() as $g) {
			$grade->addMultiOption($g['GradeID'], $g['GradeName']);
		};
		
		$priceMin = new Zend_Form_Element_Text('SalePriceMin');
		$priceMin->setLabel('Sale price (min):')
				 ->setOptions(array('size' => '8'))
				 ->setRequired(true)
				 ->addValidator('Float')
				 ->addFilter('HtmlEntities')
				 ->addFilter('StringTrim');
				 
		$priceMax = new Zend_Form_Element_Text('SalePriceMax');
		$priceMax->setLabel('Sale price (max):')
				 ->setOptions(array('size' => '8'))
				 ->setRequired(true)
				 ->addValidator('Float')
				 ->addFilter('HtmlEntities')
				 ->addFilter('StringTrim');
				 
		$notes = new Zend_Form_Element_TextArea('Description');
		$notes->setLabel('Description:')
			  ->setOptions(array('rows' => '15','cols' => '60'))
			  ->setRequired(true)
			  ->addFilter('HTMLEntities')
			  ->addFilter('StripTags')
			  ->addFilter('StringTrim');

		$submit = new Zend_Form_Element_Submit('submit');
		$submit->setLabel('Submit Entry')
			   ->setOrder(100)
			   ->setOptions(array('class' => 'submit'));
			   
		$this->addElement($name)
			 ->addElement($email)
			 ->addElement($tel)
			 ->addElement($address);
			 
		$this->addDisplayGroup(
			array('SellerName', 'SellerEmail', 'SellerTel',
				'SellerAddress'), 'contact');
		$this->getDisplayGroup('contact')
			 ->setOrder(10)
			 ->setLegend('Seller Information');
			 
		$this->addElement($title)
			 ->addElement($year)
			 ->addElement($country)
			 ->addElement($denomination)
			 ->addElement($type)
			 ->addElement($grade)
			 ->addElement($priceMin)
			 ->addElement($priceMax)
			 ->addElement($notes);
			 
		$this->addDisplayGroup(
			array('Title', 'Year', 'CountryID', 'Denomination',
				'TypeID', 'GradeID', 'SalePriceMin', 'SalePriceMax',
				'Description'), 'item');
		$this->getDisplayGroup('item')
			 ->setOrder(20)
			 ->setLegend('Item Information');
			 
	 	$images = new Zend_Form_Element_File('images');
		$images->setMultiFile(3)
			   ->addValidator('MimeType', false, array('image/jpeg'))
			   ->addValidator('Size', false, '204800')
			   ->addValidator('Extension', false, 'jpg,png,gif')
			   ->addValidator(
				   	'ImageSize', 
				   	false, 
				   	array(
				   		'minwidth'  => 150,
				   		'minheight' => 150,
				   		'maxwidth'  => 150,
				   		'maxheight' => 150
				   	)
			   )
			   ->setValueDisabled(true);
	 	
		$this->addElement($images);
		
		$this->addDisplayGroup(array('images'), 'files');
		$this->getDisplayGroup('files')
			 ->setOrder(40)
			 ->setLegend('Images');
			   
		$this->addElement($submit);

	}
	
	public function getCountries() 
	{
		$q = Doctrine_Query::create()
			->from('Square_Model_Country c');
		return $q->fetchArray();
	}

	public function getGrades() 
	{
		$q = Doctrine_Query::create()
			->from('Square_Model_Grade g');
		return $q->fetchArray();
	}

	public function getTypes() 
	{
		$q = Doctrine_Query::create()
			->from('Square_Model_Type t');
		return $q->fetchArray();
	}
}
