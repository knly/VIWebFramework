<?php

class VINewsManager {
    
    protected $items = array();
    
    function addItem(VINewsItem $item) {
    	if ($item->getID()!=NULL) {
	        $this->items[$item->getID()] = $item;
        } else {
	        $this->items[] = $item;
        }
    }

    public function getItem($id) {
	    return $this->items[$id];
    }
    
    function allItems() {
        return $this->items;
    }
    
    public function getNewsBox($count) {
        $html = '<ul class="news-box">';
        $i = 0;
        foreach (self::allItems() as $item) {
            $html .= '<li><a href="/news/'.$item->getID().'"><small>'.$item->getDateAsString().'</small> '.$item->title.'</a></li>';
            $i++;
            if ($i>=$count) {
	            break;
            }
        }
        $html .= '</ul>';
        return $html;
    }
    
}

class VINewsItem {
    
    protected $id;
    
    public $title;
    public $date;
    public $content;
    
	public function __construct($id) {
		$this->id = $id;
	}

    public function getID() {
	    return $this->id;
    }
    
    public function setTitle($title) {
	    if (!isset($this->id)) {
		    $this->id = ereg_replace("[-]+", "-", ereg_replace("[^a-z0-9-]", "", strtolower( str_replace(" ", "-", $title) ) ) );;
	    }
	    $this->title = $title;
    }
    
    function setDateFromString($date_str) {
        $this->date = strtotime($date_str);
    }
    function getDateAsString() {
        date_default_timezone_set('UTC');
        return date("d.m.y", $this->date);
    }
    
}

?>