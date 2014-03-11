<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	protected function _initDoctrine()
	{
		require_once 'Doctrine/Doctrine.php';
		$this->getApplication()
			 ->getAutoloader()
			 ->pushAutoloader(array('Doctrine', 'autoload'), 'Doctrine');
		$manager = Doctrine_Manager::getInstance();
		$manager->setAttribute(
			Doctrine::ATTR_MODEL_LOADING,
			Doctrine::MODEL_LOADING_CONSERVATIVE
		);
		$config = $this->getOption('doctrine');
		$conn = Doctrine_Manager::connection($config['dsn'], 'doctrine');
		return $conn;
	}
	
	protected function _initLocale($locale = null) 
	{
		$session = new Zend_Session_Namespace('square.l10n');
		if ( $session->locale ) {
			$locale = new Zend_Locale($session->locale);
		}
		
		if ( $locale === null ) {
			try {
				$locale = new Zend_Locale('browser');
			} catch (Zend_Locale_Exception $e) {
				$locale = new Zend_Locale('en_PH');
			}
		}
		$registry = Zend_Registry::getInstance();
		$registry->set('Zend_Locale', $locale);
	}
	
	protected function _initTranslate()
	{
		$translate = new Zend_Translate(
			'array',
			APPLICATION_PATH . '/../languages',
			null,
			array(
				'scan' => Zend_Translate::LOCALE_FILENAME,
				'disableNotice' => 1
			)
		);
		$registry = Zend_Registry::getInstance();
		$registry->set('Zend_Translate', $translate);
	}
	
	protected function _initRoutes()
  	{
    	$front = Zend_Controller_Front::getInstance();
    	$router = $front->getRouter();
    	$restRoute = new Zend_Rest_Route($front, array(), array('api'));
    	$router->addRoute('api', $restRoute);  
  	}
  
  protected function _initNavigation() 
  {
  	$config = new Zend_Config_Xml(APPLICATION_PATH . '/configs/navigation.xml');
  	$container = new Zend_Navigation($config);
  	
  	$registry = Zend_Registry::getInstance();
  	$registry->set('Zend_Navigation', $container);
  	
  	Zend_Controller_Action_HelperBroker::
  		addHelper(new Square_Controller_Action_Helper_Navigation());
  }
  protected function _initDojo()
  {
    // get view resource
    $this->bootstrap('view'); 
    $view = $this->getResource('view');
    
    // add helper path to view
    Zend_Dojo::enableView($view);
    
    // configure Dojo view helper, disable
    $view->dojo()->setCdnBase(Zend_Dojo::CDN_BASE_AOL)
                 ->addStyleSheetModule('dijit.themes.tundra')
                 ->disable();
  }    
}