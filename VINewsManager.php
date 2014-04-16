<?php

class VINewsManager {
    
    static protected $items = array();
    
    static public function addItem($date_str, $title) {
    	$item = new VINewsItem($date_str, $title);
        self::$items[$item->getID()] = $item;
        return $item;
    }

    static public function getItem($id) {
	    return self::$items[$id];
    }
    
    static public function allItems() {
        return self::$items;
    }
    
    static public function getNewsBox($count) {
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
    
	public function __construct($date_str, $title) {
	    $this->id = preg_replace("/[-]+/", "-", preg_replace("/[^a-z0-9-]/", "", strtolower( str_replace(" ", "-", $title) ) ) );;
	    $this->title = $title;
		date_default_timezone_set('Europe/Berlin'); // TODO: generalize
	    $this->date = strtotime($date_str);
	}

    public function getID() {
	    return $this->id;
    }
    
	public function getDateAsString($fmt="d.m.y") {
	    date_default_timezone_set('UTC');
	    return date($fmt, $this->date);
	}

}

?>