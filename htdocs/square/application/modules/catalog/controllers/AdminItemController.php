<?php
class Catalog_AdminItemController extends Zend_Controller_Action
{
  public function init() 
  {
    $this->view->doctype('XHTML1_STRICT');
  }
    
  // action to handle admin URLs
  public function preDispatch() 
  {
    // set admin layout
    // check if user is authenticated
    // if not, redirect to login page
    $url = $this->getRequest()->getRequestUri();
    $this->_helper->layout->setLayout('admin');          
    if (!Zend_Auth::getInstance()->hasIdentity()) {
      $session = new Zend_Session_Namespace('square.auth');
      $session->requestURL = $url;
      $this->_redirect('/admin/login');
    }
  }
  
  // action to display list of catalog items
  public function indexAction()
  {
    // set filters and validators for GET input
    $filters = array(
      'sort' => array('HtmlEntities', 'StripTags', 'StringTrim'),
      'dir'  => array('HtmlEntities', 'StripTags', 'StringTrim'),
      'page' => array('HtmlEntities', 'StripTags', 'StringTrim')
    );        
    $validators = array(
      'sort' => array(
        'Alpha', 
        array('InArray', 'haystack' => 
          array('RecordID', 'Title', 'Denomination', 'CountryID', 'GradeID', 'Year'))
      ),
      'dir'  => array(
        'Alpha', array('InArray', 'haystack' => 
          array('asc', 'desc'))
      ),
      'page' => array('Int')
    );     
    $input = new Zend_Filter_Input($filters, $validators);
    $input->setData($this->getRequest()->getParams());
    
    // test if input is valid
    // create query and set pager parameters
    if ($input->isValid()) {
      $q = Doctrine_Query::create()
            ->from('Square_Model_Item i')
            ->leftJoin('i.Square_Model_Grade g')
            ->leftJoin('i.Square_Model_Country c')
            ->leftJoin('i.Square_Model_Type t')
            ->orderBy(sprintf('%s %s', $input->sort, $input->dir));
            
      // configure pager
      $configs = $this->getInvokeArg('bootstrap')->getOption('configs');
      $localConfig = new Zend_Config_Ini($configs['localConfigPath']);        
      $perPage = $localConfig->admin->itemsPerPage;
      $numPageLinks = 5;      
      
      // initialize pager
      $pager = new Doctrine_Pager($q, $input->page, $perPage);
      
      // execute paged query
      $result = $pager->execute(array(), Doctrine::HYDRATE_ARRAY);            
       
      // initialize pager layout
      $pagerRange = new Doctrine_Pager_Range_Sliding(array('chunk' => $numPageLinks), $pager);
      $pagerUrlBase = $this->view->url(array(), 'admin-catalog-index', 1) . "/{%page}/{$input->sort}/{$input->dir}";
      $pagerLayout = new Doctrine_Pager_Layout($pager, $pagerRange, $pagerUrlBase);
      
      // set page link display template
      $pagerLayout->setTemplate('<a href="{%url}">{%page}</a>');
      $pagerLayout->setSelectedTemplate('<span class="current">{%page}</span>');      
      $pagerLayout->setSeparatorTemplate('&nbsp;');

      // set view variables
      $this->view->records = $result;
      $this->view->pages = $pagerLayout->display(null, true);                  
    } else {
      throw new Zend_Controller_Action_Exception('Invalid input');                    
    }
  }


  // action to delete catalog items
  public function deleteAction()
  {
    // set filters and validators for POST input
    $filters = array(
      'ids' => array('HtmlEntities', 'StripTags', 'StringTrim')
    );    
    $validators = array(
      'ids' => array('NotEmpty', 'Int')
    );
    $input = new Zend_Filter_Input($filters, $validators);
    $input->setData($this->getRequest()->getParams());
    
    // test if input is valid
    // read array of record identifiers
    // delete records from database
    if ($input->isValid()) {
      $q = Doctrine_Query::create()
            ->delete('Square_Model_Item i')
            ->whereIn('i.RecordID', $input->ids);
      $result = $q->execute();          
      $config = $this->getInvokeArg('bootstrap')->getOption('uploads');                  
      foreach ($input->ids as $id) {
        foreach (glob("{$config['uploadPath']}/{$id}_*") as $file) {
          unlink($file);
        }           
      }
      $this->_helper->getHelper('FlashMessenger')->addMessage('The records were successfully deleted.');
      $this->_redirect('/admin/catalog/item/success');
    } else {
      throw new Zend_Controller_Action_Exception('Invalid input');              
    }
  }
  
  // action to modify an individual catalog item
  public function updateAction()
  {
    // load JavaScript and CSS files
    $this->view->headLink()->appendStylesheet('http://yui.yahooapis.com/combo?2.8.0r4/build/calendar/assets/skins/sam/calendar.css');
    $this->view->headScript()->appendFile('/js/form.js');
    $this->view->headScript()->appendFile('http://yui.yahooapis.com/combo?2.8.0r4/build/yahoo-dom-event/yahoo-dom-event.js&2.8.0r4/build/calendar/calendar-min.js');
    
    // generate input form
    $form = new Square_Form_ItemUpdate;
    $this->view->form = $form;    
    
    if ($this->getRequest()->isPost()) {
      // if POST request
      // test if input is valid
      // retrieve current record
      // update values and replace in database
      $postData = $this->getRequest()->getPost();   
      if ($form->isValid($postData)) {
        $input = $form->getValues();
        $item = Doctrine::getTable('Square_Model_Item')->find($input['RecordID']);        
        $item->fromArray($input);
        $item->DisplayUntil = ($item->DisplayStatus == 0) ? null : $item->DisplayUntil;
        $item->save();
        $this->_helper->getHelper('FlashMessenger')->addMessage('The record was successfully updated.');
        $this->_redirect('/admin/catalog/item/success');        
      }      
    } else {    
      // if GET request
      // set filters and validators for GET input
      // test if input is valid
      // retrieve requested record
      // pre-populate form
      $filters = array(
        'id' => array('HtmlEntities', 'StripTags', 'StringTrim')
      );          
      $validators = array(
        'id' => array('NotEmpty', 'Int')
      );  
      $input = new Zend_Filter_Input($filters, $validators);
      $input->setData($this->getRequest()->getParams());      
      if ($input->isValid()) {
        $q = Doctrine_Query::create()
              ->from('Square_Model_Item i')
              ->leftJoin('i.Square_Model_Country c')
              ->leftJoin('i.Square_Model_Grade g')
              ->leftJoin('i.Square_Model_Type t')
              ->where('i.RecordID = ?', $input->id);
        $result = $q->fetchArray();        
        if (count($result) == 1) {
          $this->view->form->populate($result[0]);                
        } else {
          throw new Zend_Controller_Action_Exception('Page not found', 404);        
        }        
      } else {
        throw new Zend_Controller_Action_Exception('Invalid input');                
      }              
    }
  }  
  
  // action to display an individual catalog item
  public function displayAction()
  {
    // set filters and validators for GET input
    $filters = array(
      'id' => array('HtmlEntities', 'StripTags', 'StringTrim')
    );    
    $validators = array(
      'id' => array('NotEmpty', 'Int')
    );
    $input = new Zend_Filter_Input($filters, $validators);
    $input->setData($this->getRequest()->getParams());

    // test if input is valid
    // retrieve requested record
    // attach to view
    if ($input->isValid()) {
      $q = Doctrine_Query::create()
            ->from('Square_Model_Item i')
            ->leftJoin('i.Square_Model_Country c')
            ->leftJoin('i.Square_Model_Grade g')
            ->leftJoin('i.Square_Model_Type t')
            ->where('i.RecordID = ?', $input->id);
      $result = $q->fetchArray();
      if (count($result) == 1) {
        $this->view->item = $result[0];               
        $this->view->images = array(); 
        $config = $this->getInvokeArg('bootstrap')->getOption('uploads');                  
        foreach (glob("{$config['uploadPath']}/{$this->view->item['RecordID']}_*") as $file) {
          $this->view->images[] = basename($file);
        }          
      } else {
        throw new Zend_Controller_Action_Exception('Page not found', 404);        
      }
    } else {
      throw new Zend_Controller_Action_Exception('Invalid input');              
    }
  }      

  // action to create full-text indices
  public function createFulltextIndexAction()
  {
    // create and execute query
    $q = Doctrine_Query::create()
          ->from('Square_Model_Item i')
          ->leftJoin('i.Square_Model_Country c')
          ->leftJoin('i.Square_Model_Grade g')
          ->leftJoin('i.Square_Model_Type t')
          ->where('i.DisplayStatus = 1')
          ->addWhere('i.DisplayUntil >= CURDATE()');
    $result = $q->fetchArray();
    
    // get index directory
    $config = $this->getInvokeArg('bootstrap')->getOption('indexes');
    $index = Zend_Search_Lucene::create($config['indexPath']);
    
    foreach ($result as $r) {
      // create new document in index
      $doc = new Zend_Search_Lucene_Document();

      // index and store fields
      $doc->addField(Zend_Search_Lucene_Field::Text('Title', $r['Title']));
      $doc->addField(Zend_Search_Lucene_Field::Text('Country', $r['Square_Model_Country']['CountryName']));
      $doc->addField(Zend_Search_Lucene_Field::Text('Grade', $r['Square_Model_Grade']['GradeName']));
      $doc->addField(Zend_Search_Lucene_Field::Text('Year', $r['Year']));      
      $doc->addField(Zend_Search_Lucene_Field::UnStored('Description', $r['Description']));
      $doc->addField(Zend_Search_Lucene_Field::UnStored('Denomination', $r['Denomination']));
      $doc->addField(Zend_Search_Lucene_Field::UnStored('Type', $r['Square_Model_Type']['TypeName']));
      $doc->addField(Zend_Search_Lucene_Field::UnIndexed('SalePriceMin', $r['Denomination']));
      $doc->addField(Zend_Search_Lucene_Field::UnIndexed('SalePriceMax', $r['Denomination']));
      $doc->addField(Zend_Search_Lucene_Field::UnIndexed('RecordID', $r['RecordID']));

      // save result to index
      $index->addDocument($doc);      
    }

    // set number of documents in index
    $count = $index->count();
    $this->_helper->getHelper('FlashMessenger')->addMessage("The index was successfully created with $count documents.");
    $this->_redirect('/admin/catalog/item/success');    
  }
  
  // success action
  public function successAction()
  {
    if ($this->_helper->getHelper('FlashMessenger')->getMessages()) {
      $this->view->messages = $this->_helper->getHelper('FlashMessenger')->getMessages();    
    } else {
      $this->_redirect('/admin/catalog/item/index');    
    } 
  }
  
    
}
