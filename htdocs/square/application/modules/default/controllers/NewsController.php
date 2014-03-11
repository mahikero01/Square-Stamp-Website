<?php
class NewsController extends Zend_Controller_Action 
{
	public function indexAction() {
		$q = 'philately';
		$this->view->q = $q;
		$twitter = new Zend_Service_Twitter_Search();
		$this->view->tweets = $twitter->search(
			$q,
			array(
				'lang' => 'en', 
				'rpp' => 8, 
				'show_user' => true
			)
		);
		
		$this->view->feeds = array();
		$gnewsFeed = "http://news.google.com/news?hl=en&q=$q&output=atom";
		$this->view->feeds[0] = Zend_Feed_Reader::import($gnewsFeed);
		
		$bpmaFeed = "http://www.postalheritage.org.uk/news/RSS";
		$this->view->feeds[1] = Zend_Feed_Reader::import($bpmaFeed);
	}
}