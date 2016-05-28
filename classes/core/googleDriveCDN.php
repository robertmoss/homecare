<?php
 
  include_once 'log.php';
 include_once 'config.php';
 include_once 'cdn.php';
 
 class googleDriveCDN implements iCDN {
    
    private $userid;
    private $tenantid;
    
    public function __construct($userid,$tenantid) {
        $this->userid=$userid;
        $this->tenantid=$tenantid;
    }
    
    public function getUrl($key) {

        throw new Exception('getUrl not yet implemented for GoogleDrive.');

        
    }
    
    public function putContent($sourcefile, $key, $metadata) {
        
        Log::debug('Putting content to Google Drive CDN: source:' . $sourcefile . ', key: ' . $key , 5);
        
        throw new Exception('putContent not yet implemented for GoogleDrive.');
        
    }
 
 }
 