<?php

    include_once 'log.php';

/*
 * Wrapper to the application cache
 * In the future, will implement memcache; as of now this is just a stub
 */
 
 class Cache {

     public static function getCacheArray() {
        
        $cache = Utility::getSessionVariable('cache', 'empty');
        if ($cache=='empty') {
            $cache = array();
        }
        return $cache;
         
     }
     
     public static function getValue($key) {
        Log::debug("cache request for " . $key,1);    
        
        // currently, just using simple in memory cache that will persist just for life of session (to prevent duplicate database hits when rendering single page)
        // in future will implement memcached call
        $cache = Cache::getCacheArray();
        for ($i=0;$i<count($cache);$i++) {
            if ($cache[$i][0] == $key) {
                Log::debug("cache hit. Value=" . Cache::safeString($cache[$i][1]),1);
                return $cache[$i][1];
            } 
        }
        
        Log::debug("cache miss.",1);
        return null;       
     }
     
     public static function putValue($key,$value) {
         // will port to memcached in the future
        Log::debug("cache put: " . $key . ' = ' . Cache::safeString($value),1);    
                  
         // for now, check in memory cache for this page
        $cache = Cache::getCacheArray();
        $inCache = false;
        for ($i=0;$i<count($cache);$i++) {
            if ($cache[$i][0] == $key) {
                // already in cache. Update value
                Log::debug("Updating cache value: " . $key . ' = ' . Cache::safeString($value),1);
                $cache[$i][1] =$key;
                $inCache = true;
            }
        }
        if (!$inCache) {
            $arr = array($key,$value);
            array_push($cache,$arr);
            Log::debug("Value pushed. Cache count = " . count($cache ),1);
        }
        $_SESSION["cache"] = $cache;         
         return true;
     }
     
  
  public static function safeString($value) {
      if (is_array($value)) {
          return '[array]';
      }
      else {
          return $value;
      }
          
    }
  
 
 public static function flushCache() {
      Log::debug('flushing full cache...',7);
      $_SESSION["cache"] = array();            
 }
 
  }
