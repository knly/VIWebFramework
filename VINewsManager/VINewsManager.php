<?php

class VINewsManager {
    
    protected $items = array();
    
    function addItem(VINewsItem $item) {
        $this->items[] = $item;
    }
    
    function addNewItem() {
        $item = new VINewsItem();
        $this->addItem($item);
        return $item;
    }
    
    function allItems() {
        return $this->items;
    }
    
}

class VINewsItem {
    
    public $title;
    public $date;
    public $content;
    
    function setDateFromString($date_str) {
        $this->date = strtotime($date_str);
    }
    function getDateAsString() {
        date_default_timezone_set('UTC');
        return date("d.m.y", $this->date);
    }
    
}

?>