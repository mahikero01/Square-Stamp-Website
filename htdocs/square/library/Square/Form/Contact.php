<?php
class Square_Form_Contact extends Zend_Form
{
	public function init()
	{
		$this->setAction( '/contact' )
			 ->setMethod( 'post' );
			 
		$name = new Zend_Form_Element_Text( 'name' );
		$name->setLabel( 'contact-name' )
			 ->setOptions( array('size' => '35') )
			 ->setRequired( true )
			 ->addValidator( 'NotEmpty', true )
			 ->addValidator( 'Alpha', true )
			 ->addFilter( 'HtmlEntities' )
			 ->addFilter( 'StringTrim' );
			 
		$email = new Zend_Form_Element_Text( 'email' );
		$email->setLabel( 'contact-email-address' )
			  ->setOptions( array('size' => '50') )
			  ->setRequired( true )
			  ->addValidator( 'NotEmpty', true )
			  ->addValidator( 'EmailAddress', true )
			  ->addFilter( 'HtmlEntities' )
			  ->addFilter( 'StringToLower' )
			  ->addFilter( 'StringTrim' );
			  
		$country = new Zend_Dojo_Form_Element_ComboBox('country');
    	$country->setLabel('contact-country');
    	$country->setOptions(array(
        	'autocomplete' => false,
          	'storeId'   => 'countryStore',
          	'storeType' => 'dojo.data.ItemFileReadStore',
          	'storeParams' => array('url' => "/default/contact/autocomplete"),
          	'dijitParams' => array('searchAttr' => 'name')
    	))
            	->setRequired(true)
            	->addValidator('NotEmpty', true)
            	->addFilter('HTMLEntities')            
            	->addFilter('StringToLower')        
            	->addFilter('StringTrim');       
			  
		$message = new Zend_Form_Element_Textarea('message');
		$message->setLabel( 'contact-message' )
				->setOptions( array('rows' => '8', 'cols' => '40') )
				->setRequired( true )
				->addValidator( 'NotEmpty', true )
				->addFilter( 'HtmlEntities' )
				->addFilter( 'StringTrim' );
		/*		
		$captcha = new Zend_Form_Element_Captcha( 'captcha', array(
			'captcha' => array(
				'captcha' => 'image',
				'wordlen' => 6,
				'timeout' => 300,
				'width' => 300,
				'height' => 100,
				'imgUrl' => '/captcha',
				'imgDir' => APPLICATION_PATH . '/../public/captcha',
				'font' => APPLICATION_PATH . '/../public/fonts/LiberationSansRegular.ttf'
			)
		) );
		$captcha->setLabel( 'Verification code' );
		*/
		
		$submit = new Zend_Form_Element_Submit( 'submit' );
		$submit->setLabel( 'contact-send-message' )
			   ->setOptions( array('class' => 'submit') );
			   
		$this->addElement($name)
			 ->addElement($email)
			 ->addElement($country)
			 ->addElement($message)
			 //->addElement($captcha)
			 ->addElement($submit);
	}
}