<?php
class Square_Form_Search extends Zend_Form 
{
	public function init()
	{
		$this->setAction('/catalog/item/search')
			 ->setMethod('get');
		
		$query = new Zend_Form_Element_Text('q');
    	$query->setLabel('Keywords:')
             ->setOptions(array('size' => '20'))
             ->addFilter('HtmlEntities')            
             ->addFilter('StringTrim');            
    	$query->setDecorators(
    		array(
                array('ViewHelper'),
                array('Errors'),
                array('Label', array('tag' => '<span>')),
             )
		);
             
    	// create submit button
    	$submit = new Zend_Form_Element_Submit('submit');
    	$submit->setLabel('Search')
           	   ->setOptions(array('class' => 'submit'));
    	$submit->setDecorators(
    		array(
            	array('ViewHelper'),
         	)
   		);
         
    	// attach elements to form    
    	$this->addElement($query)
         	 ->addElement($submit);    
	}
			 
//	public $messages = array(
//		Zend_Validate_Int::INVALID =>
//			'\'%value%\' is not an integer',
//		Zend_Validate_Int::NOT_INT =>
//			'\'%value%\' is not an integer'
//	);				 
//		$this->setDecorators(
//			array(
//				array(
//					'FormErrors', 
//					array(
//						'markupListItemStart' => '', 
//						'markupListItemEnd' => ''
//					)
//				),
//				array('FormElements'),
//				array('Form')
//			)
//		);
//		
//		$year = new Zend_Form_Element_Text('y');
//		$year->setLabel('Year:')
//			 ->setOptions(array('size' => '6'))
//			 ->addValidator('Int', false,
//			 	array('messages' => $this->messages))
//			 ->addFilter('HtmlEntities')
//			 ->addFilter('StringTrim');
//		
//		$price = new Zend_Form_Element_Text('p');
//		$price->setLabel('Price:')
//			 ->setOptions(array('size' => '8'))
//			 ->addValidator('Int', false,
//			 	array('messages' => $this->messages))
//			 ->addFilter('HtmlEntities')
//			 ->addFilter('StringTrim');	 
//			 
//		$grade = new Zend_Form_Element_Select('g');
//		$grade->setLabel('Grade:')
//			 ->addValidator('Int', false,
//			 	array('messages' => $this->messages))
//			 ->addFilter('HtmlEntities')
//			 ->addFilter('StringTrim')
//			 ->addMultiOption('','Any');
//		foreach ($this->getGrades() as $g) {
//			$grade->addMultiOption($g['GradeID'], $g['GradeName']);
//		}
//		
//		$submit = new Zend_Form_Element_Submit('submit');
//		$submit->setLabel('Search')
//			   ->setOptions(array('class' => 'submit'));
//			   
//		$this->addElement($year)
//			 ->addElement($price)
//			 ->addElement($grade)
//			 ->addElement($submit);
//			 
//		$this->setElementDecorators(
//			array(
//				array('ViewHelper'),
//				array('Label', array('tag' => '<span>'))
//			)
//		);
//		$submit->setDecorators(
//			array(array('ViewHelper'), )
//		);
//

	
//	public function getGrades() 
//	{
//		$q = Doctrine_Query::create()
//			->from('Square_Model_Grade g');
//		return $q->fetchArray();
//	}
}