<?php

define("VI_LOG_LEVEL_UNSPECIFIED", 0);
define("VI_LOG_LEVEL_VERBOSE", 1);
define("VI_LOG_LEVEL_DEBUG", 2);
define("VI_LOG_LEVEL_INFO", 3);
define("VI_LOG_LEVEL_WARNING", 4);
define("VI_LOG_LEVEL_ERROR", 5);
define("VI_LOG_LEVEL_NONE", 6);

class VILogger {

	protected $key;
	public $log_level = VI_LOG_LEVEL_UNSPECIFIED;
	public $email_logging_to_address = null;
	public $print_logging_enabled = true;

	static protected $default_logger;
	protected $parent_logger;
	protected $child_loggers = array();

	public function __construct($key) {
		$this->key = $key;
	}

	public function getKey() {
		return $this->key;
	}

	public function getKeyPath() {
		if ($this->parent_logger==null) {
			return $this->key;
		} else {
			return $this->parent_logger->getKeyPath().'.'.$this->key;
		}
	}

	public function setLogLevel($log_level) {
		$this->log_level = $log_level;
	}

	public function getLogLevel() {
		if ($this->log_level!=VI_LOG_LEVEL_UNSPECIFIED||$this->parent_logger==null) {
			return $this->log_level;
		} else {
			return $this->parent_logger->getLogLevel();
		}
	}

	public function getEmailLoggingToAddress() {
		if ($this->email_logging_to_address!=null||$this->parent_logger==null) {
			return $this->email_logging_to_address;
		} else {
			return $this->parent_logger->getEmailLoggingToAddress();
		}
	}

	public function isPrintLoggingEnabled() {
		if ($this->print_logging_enabled!=true||$this->parent_logger==null) {
			return $this->print_logging_enabled;
		} else {
			return $this->parent_logger->isPrintLoggingEnabled();
		}
	}

	static public function get($key_path) {

		if ($key_path=='default') {
			if (self::$default_logger==null) {
				self::$default_logger = new VILogger('default');
			}
			return self::$default_logger;
		}

		$path_components = explode('.', $key_path);

		$parent_logger = self::get('default');

		foreach($path_components as $key) {

			$logger = $parent_logger->find($key);

			if ($logger==null) {
				$logger = new VILogger($key);
				$logger->setParent($parent_logger);
			}

			$parent_logger = $logger;

		}

		return $parent_logger;

	}

	protected function setParent($parent_logger) {
		$this->parent_logger = $parent_logger;
		$parent_logger->addChild($this);
	}

	protected function addChild($child_logger) {
		if (!isset($this->child_loggers[$child_logger->getKey()])) {
			$this->child_loggers[$child_logger->getKey()] = $child_logger;
			$child_logger->setParent($this);
		}
	}

	protected function find($key) {
		if ($this->key==$key) {
			return $this;
		}
		foreach ($this->child_loggers as $child) {
			$found = $child->find($key);
			if ($found!=null) {
				return $found;
			}
		}
		return null;
	}

	public function log($log_str, $log_level=VI_LOG_LEVEL_UNSPECIFIED) {
		if ($log_level < $this->getLogLevel()) return;

	    switch ($log_level) {
	        case VI_LOG_LEVEL_UNSPECIFIED:
	            $level_string = @"UNSPECIFIED";
	            break;
	        case VI_LOG_LEVEL_VERBOSE:
	            $level_string = @"VERBOSE";
	            break;
	        case VI_LOG_LEVEL_DEBUG:
	            $level_string = @"DEBUG";
	            break;
	        case VI_LOG_LEVEL_INFO:
	            $level_string = @"INFO";
	            break;
	        case VI_LOG_LEVEL_WARNING:
	            $level_string = @"WARNING";
	            break;
	        case VI_LOG_LEVEL_ERROR:
	            $level_string = @"ERROR";
	            break;
	        default:
	        	$level_string = 'UNKNOWN';
	            break;
	    }

	    $key_path = $this->getKeyPath();

	    $log_str = $key_path.':'.$level_string.': '.$log_str;

	    $print_logging_enabled = $this->isPrintLoggingEnabled();
	    if ($print_logging_enabled) {
		    echo '<pre>'.$log_str.'</pre>';
	    }

	    $email_logging_to_address = $this->getEmailLoggingToAddress();
	    if ($email_logging_to_address!=null) {
			$receiver = $email_logging_to_address;
			$subject = 'VILogger:'.$key_path;
			$mailheader = "Content-type: text/html; charset=utf-8\r\n";
			$mailheader .= 'From: debug@viwid.com';
			$message_body = '<pre>'.$log_str.'</pre>';
			mail($receiver, $subject, $message_body, $mailheader);
	    }

	}

	public function configureErrorReporting($log_level=VI_LOG_LEVEL_DEBUG) {
		if ($log_level < $this->getLogLevel()) {
		    error_reporting(0);
		} else {
	        ini_set('display_startup_errors',1);
		    ini_set('display_errors',1);
		    error_reporting(-1);
		}
	}

}

?>
