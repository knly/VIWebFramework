<?php

class VIPagemap {

    protected $pages = array();
    
    public $root_url;
    
    // the page to load when none is specified
    public $default_page;
    
    // error page
    public $error_page;
    
    // main title
    public $main_title;

    // constructor
    function __construct() {
        $this->root_url = '/';
    }

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
        $current_page = NULL;
        if (isset($_GET['p'])) {
            $current_page = $this->pageWithID($_GET['p']);
    		if (!isset($current_page)) $current_page = $this->error_page;
		}
		if (!isset($current_page)) $current_page = $this->default_page;
		if (isset($current_page)&&isset($current_page->forward)) $current_page = $current_page->forward;
		return $current_page;
    }
    function isCurrentPage($page) {
        return $this->currentPage()==$page;
    }
    
    function getUrlForPage($page) {
        return $this->root_url.$page->id;
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
    
    // identifies the page
    public $id;
    
    // the file path to the page content
    public $file;
    
    // optional array to specify options
    public $options;
    
    // when set to another page, that page gets included instead
    public $forward;
    
    // tree structure for nested pages
    public $parentPage;
    public $childPages;
    
    // constructor
    function __construct($id) {
        $this->id = $id;
        $this->file = $id.'.php';
        $this->options = array();
    }

    function setParentPage(VIPage $parent) {
        $this->parentPage = $parentPage;
        $parentPage->addChildPage($this);
    }
    function addChildPage(VIPage $child) {
        $this->childPages[] = $child;
        $child->parentPage = $this;
    }
    function isChildOf(VIPage $page) {
        $the_current_page = $this->parentPage;
        while(isset($the_current_page)) {
            if ($the_current_page==$page) return true;
            $the_current_page = $the_current_page->parentPage;
        }
        return false;
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