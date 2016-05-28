<?php

/* iCDN is the interface for interacting with a Content Delivery Network (or a simple file store) 
 * implement this interface to access a particular CDN
 * Included in this file is the simplest local filesystem based CDN, which can be used for development and testing
 */
 
 include_once 'log.php';
 include_once 'config.php';
 
 interface iCDN {
 	
	public function getUrl($key);
	
	// copies the specified sourcefile to the CDN, identified by "key" and storing specific metadata if the CDN supports metadata tagging
	// function should return url to content if successfully deposited in CDN
	public function putContent($sourcefile, $key, $metadata);
	
 }
 
 class localCDN implements iCDN {
 	
	private $userid;
	private $tenantid;
	
	public function __construct($userid,$tenantid) {
		$this->userid=$userid;
		$this->tenantid=$tenantid;
	}
	
	public function getUrl($key) {
		// the Url is how the media can be retrieved by a web client (i.e. not local filesystem path)
		$path = Config::$cdn_root . '/' . $this->tenantid;
		$path .= '/' . $this->userid;
		$url = $path . '/' . $key;
		return $url;

	}
	
	public function putContent($sourcefile, $key, $metadata) {
		// no meta-data retained for file system, so we ignore that parameter
		Log::debug('Putting content to local CDN: source:' . $sourcefile . ', key: ' . $key , 5);
		
		// to do: figure out how to handle duplicates. Right now, will just overwrite
		$this->getPathForKey($key);
		if (copy($sourcefile, $this->getPathForKey($key))) {
			return $this->getUrl($key);
		}
		else {
			return false;
		}				
	}
	
	private function getPathForKey($key) {
		// This function returns the local filesystem pathf for where media will be stored
		// will need to work on the logic for how to store locally
		// for now, separate folders per tenant and user		
		$path = Config::$cdn_path . '/' . $this->tenantid;
		$this->makePath($path);
		$path .= '/' . $this->userid;
		$this->makePath($path);
		$path .= '/' . $key;
		return $path;
	}
	
	
	private function makePath($path) {
		if (!file_exists($path)) {
		    Log::debug('creating new folder in local CDN:' . $path . '...', 5);
		    mkdir($path,0777);
		}

	}
	
 }

