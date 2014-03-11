<?php
class Api_CatalogController extends Zend_Rest_Controller 
{
	public function init() 
	{
		$this->apiBaseUrl = 'http://square.localhost/api/catalog';
		$this->_helper->layout->disableLayout();
		$this->getHelper('viewRenderer')->setNoRender(true);
	}
	
	public function indexAction() 
	{
		$q = Doctrine_Query::create()
			->from('Square_Model_Item i')
			->leftJoin('i.Square_Model_Country c')
			->leftJoin('i.Square_Model_Grade g')
			->leftJoin('i.Square_Model_Type t')
			->addWhere('i.DisplayStatus = 1');
		$result = $q->fetchArray();
		
		$output = array(
			'title' => 'Catalog records',
			'link' 	=> $this->apiBaseUrl,
			'author'=>	'Square API/1.0',
			'charset'=> 'UTF-8',
			'entries'=> array()
		);
		
		foreach ($result as $r) {
			$output['entries'][] = array(
				'title' 		=> 	$r['Title'] . ' - ' . $r['Year'],
				'link'			=>	$this->apiBaseUrl . '/' . $r['RecordID'],
				'description'	=>	$r['Description'],
				'lastUpdate'	=>	strtotime($r['RecordDate']),
				'square:title'	=>	$r['Title']
			); 
		}
		$feed = Zend_Feed::importArray($output, 'atom');
		$feed->send();
		exit;
	}
	
	public function listAction() 
	{
		return $this->_forward('index');
	}
	
	public function getAction() 
	{
		$id = $this->_getParam('id');
		$q = Doctrine_Query::create()
			->from('Square_Model_Item i')
			->leftJoin('i.Square_Model_Country c')
			->leftJoin('i.Square_Model_Grade g')
			->leftJoin('i.Square_Model_Type t')
			->where('i.RecordID = ?', $id)
			->addWhere('i.DisplayStatus = 1');
		$result = $q->fetchArray();		
		
		if ( count($result) == 1 ) { 
			$output = array(
				'title' => 'Catalog record for item ID: ' . $id,
				'link' 	=> $this->apiBaseUrl . '/' . $id,
				'author'=>	'Square API/1.0',
				'charset'=> 'UTF-8',
				'entries'=> array()
			);
			$output['entries'][0] = array(
        'title' => $result[0]['Title'] . ' - ' . $result[0]['Year'],
        'link'  => $this->apiBaseUrl . '/' . $id,
        'description' => $result[0]['Description'],
        'lastUpdate' => strtotime($result[0]['RecordDate'])
      );
      
      // import array into atom feed
      $feed = Zend_Feed::importArray($output, 'atom');
      Zend_Feed::registerNamespace('square', 'http://square.localhost');
  
      // set custom namespaced elements
      $feed->rewind();
      $entry = $feed->current();
      if ($entry) {
        $entry->{'square:id'} = $result[0]['RecordID'];  
        $entry->{'square:title'} = $result[0]['Title'];  
        $entry->{'square:year'} = $result[0]['Year'];  
        $entry->{'square:grade'} = $result[0]['Square_Model_Grade']['GradeName'];  
        $entry->{'square:description'} = $result[0]['Description'];  
        $entry->{'square:country'} = $result[0]['Square_Model_Country']['CountryName'];  
        $entry->{'square:price'} = null;
        $entry->{'square:price'}->{'square:min'}= $result[0]['SalePriceMin'];  
        $entry->{'square:price'}->{'square:max'} = $result[0]['SalePriceMax'];  
      }
        
      // output to client
      $feed->send(); 
      exit;
    } else {
      $this->getResponse()->setHttpResponseCode(404);
      echo 'Invalid record identifier';
      exit;
    }
  }

  public function postAction()
  {
    // read POST parameters and save to database
    $item = new Square_Model_Item;
    $item->fromArray($this->getRequest()->getPost());      
    $item->RecordDate = date('Y-m-d', mktime());
    $item->DisplayStatus = 0;
    $item->DisplayUntil = null;
    $item->save();
    $id = $item->RecordID;         
    
    // set response code to 201
    // send ID of newly-created record
    $this->getResponse()->setHttpResponseCode(201);
    $this->getResponse()->setHeader('Location', $this->apiBaseUrl.'/'.$id);
    echo $this->apiBaseUrl.'/'.$id;    
    exit;
		
	}
	
	public function putAction() 
	{
		
	}
	
	public function deleteAction() 
	{
		
	}
}