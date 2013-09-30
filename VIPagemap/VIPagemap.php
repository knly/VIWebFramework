<?php

class VIPagemap {

    protected $pages = array();
    
    public $default_page;
    public $error_page;
    
    public $main_title;

    function addPage(VIPage $page) {
        $this->pages[$page->id] = $page;
    }
    function addPageWithID($id) {
        $page = new VIPage($id);
        $this->addPage($page);
        return $page;
    }
    function pageWithID($id) {
        if (!isset($this->pages[$id])) return null;
        return $this->pages[$id];
    }
    function allPages() {
        return $this->pages;
    }
    
    function currentPage() {
        if (isset($_GET['p'])) {
            $current_page = $this->pageWithID($_GET['p']);
    		if (!isset($current_page)) $current_page = $this->error_page;
    		if (!isset($current_page)) $current_page = $this->default_page;
    		return $current_page;
		} else {
    		return $this->default_page;
		}
    }
    function isCurrentPage($page) {
        return $this->currentPage()==$page;
    }
    
    function checkURL() {
        // redirect when directly accessing index.php
        $page = $this->currentPage();
        $correct_url = $page->id;
        if ($page->id==$this->default_page->id) $correct_url = '';
        $actual_url = preg_replace ('/\?.*$/', '', $_SERVER['REQUEST_URI']);
        if ($actual_url != "/$correct_url") {Header ("Location: /$correct_url", true, 301); exit;}
    }
    
    function makeSitemap() {
    
        header('Content-Type: application/xml');

        echo '<?xml version="1.0" encoding="utf-8"?>'."\n";

       	echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

       	foreach($this->allPages() as $page) {

            echo "<url>\n";
            echo "<loc>/".$page->id."</loc>\n";
            echo "<lastmod>".date('c', filemtime($page->file))."</lastmod>\n";
            echo "<changefreq>weekly</changefreq>\n";
            echo "<priority>1</priority>\n";
            echo "</url>\n";

        }

        echo "</urlset>";
    }

}

class VIPage {
    
    public $id;
    public $file;
    
    private $parent;
    private $children;
    
    function __construct($id) {
        $this->id = $id;
        $this->file = 'content_'.$id.'.php';
    }
    
    function setParent(VIPage $parent) {
        $this->parent = $parent;
        $parent->addChild($this);
    }
    function addChild(VIPage $child) {
        $children[] = $child;
    }
    
}

class VINavigation {
    
    private $elements;
    
    function addElement(VIPage $page) {
        $this->elements[] = $page;
    }
    
    function allElements() {
        return $this->elements;
    }
    
}

?>