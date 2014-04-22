<?php

class VIPagemap {

    static protected $pages = array();
    
    // URL to document root
    static public $baseurl;
    // Dir on server
    static public $basedir;
    
    // the page to load when none is specified
    static public $default_page;
    
    // error page
    static public $error_page;
    
    // main title
    static public $main_title;


    static public function addPage($id) {
    	if (!isset(self::$pages[$id])) {
	        self::$pages[$id] = new VIPage($id);
    	}
        return self::$pages[$id];
    }

    static public function getPage($id) {
        if (!isset(self::$pages[$id])) return NULL;
        return self::$pages[$id];
    }

    static public function allPages() {
        return self::$pages;
    }
    
    static public function getCurrentPage() {
        $current_page = NULL;
        if (isset($_GET['p'])) {
            $current_page = self::getPage($_GET['p']);
    		if (!isset($current_page)) $current_page = self::$error_page;
		}
		if (!isset($current_page)) $current_page = self::$default_page;
		if (isset($current_page)&&isset($current_page->forward)) $current_page = $current_page->forward;
		return $current_page;
    }
    static public function isCurrentPage($page) {
        return self::getCurrentPage()==$page;
    }
    
    static public function getUrlForPage($page) {
        return self::$baseurl.$page->displayURL();
    }
    
    static public function checkURL() {
        // redirect when directly accessing index.php
        $page = self::getCurrentPage();
        $correct_url = $page->displayURL();
        if ($page->getID()==self::$default_page->getID()) $correct_url = '/';
        $actual_url = preg_replace ('/\/?\?.*$/', '', $_SERVER['REQUEST_URI']);
        if ($actual_url != self::$basedir.$correct_url) {Header ("Location: $correct_url", true, 301); exit;}
    }
    
    // TODO: implement properly
    static public function makeSitemap() {
    
        header('Content-Type: application/xml');

        echo '<?xml version="1.0" encoding="utf-8"?>'."\n";

       	echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

       	foreach(self::allPages() as $page) {

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
    protected $id;
    
    // the corresponding filename
    public $filename;
    
    // when set, checkURL() redirects to the specified url and an appropriate rewrite rule is needed (-> /.htaccess)
    // url items prepended with the $ character are replaced with the value of the $_GET variable of the same name
    public $display_url;
    
    // optional array to specify options
    public $options = array();
    
    // when set to another page, that page gets included instead
    public $forward;
    
    // tree structure for nested pages
    protected $parentPage;
    protected $childPages;
    
    // constructor
    public function __construct($id) {
        $this->id = $id;
    }
    
    public function getID() {
	    return $this->id;
    }

    public function getFile() {
	    if ($this->filename!=null) {
		    return $this->filename;
	    }
	    return $this->id.'.php';
    }

    // Tree structure
    public function setParentPage(VIPage $parent) {
        $this->parentPage = $parentPage;
        $parentPage->addChildPage($this);
    }
    public function getParentPage() {
	    return $this->parentPage;
    }

    public function addChildPage(VIPage $child) {
        $this->childPages[] = $child;
        $child->parentPage = $this;
    }
    public function getChildPages() {
	    return $this->childPages;
    }
    
    public function isChildOf(VIPage $page) {
        $the_current_page = $this->parentPage;
        while(isset($the_current_page)) {
            if ($the_current_page==$page) return true;
            $the_current_page = $the_current_page->parentPage;
        }
        return false;
    }
    
    
    
    public function displayURL() {
	    if (isset($this->display_url)) {
		    $display_url = $this->display_url;
		    $url_items = explode('/', $display_url);
		    $replaced_url_items = array();
		    foreach ($url_items as $url_item) {
			    if ($url_item!=''&&$url_item[0]=='$') {
				    $url_item = $_GET[substr($url_item, 1)];
			    }
			    $replaced_url_items[] = $url_item;
		    }
		    return implode('/', $replaced_url_items);
	    }
	    return '/'.$this->id;
    }
}

class VINavigation {
    
    protected $elements;
    protected $id;
    
    public function __construct($id) {
	    $this->id = $id;
    }
    
    public function addElement(VIPage $page) {
        $this->elements[] = $page;
    }
    
    public function allElements() {
        return $this->elements;
    }
    
    public function getHTML($class='') {
    	$html = '<ul id="'.$this->id.'" class="'.$class.'">';
	    foreach ($this->allElements() as $page) {
			$html .= '<li class="';
			if (VIPagemap::isCurrentPage($page)) {
				$html .= 'active';
			}
			if (count($page->getChildPages())>0) {
				$html .= ' dropdown';
			}
			$html .= '"><a';
			if (count($page->getChildPages())>0) {
				$html .= ' class="dropdown-toggle" data-toggle="dropdown"';
			}
			$html .= ' href="/'.$page->getID().'">'.$page->title;
			if (count($page->getChildPages())>0) {
			  $html .= ' <b class="caret"></b>';
			}
			$html .= '</a>';
			if (count($page->getChildPages())>0) {
				$html .= '<ul class="dropdown-menu">';
				foreach ($page->getChildPages() as $child) {
					$html .= '<li';
					if (VIPagemap::isCurrentPage($child)) {
						$html .= ' class="active"';
					}
					$html .= '><a href="/'.$child->getID().'">'.$child->title.'</a></li>';
				}
				$html .= '</ul>';
			}
			
			$html .= '</li>';
		}
		$html .= '</ul>';
		return $html;

    }
    
}

?>