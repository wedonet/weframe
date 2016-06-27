<?php
function cachestart(){
	switch (CacheType) {
		case 'dummy':
			require('cache_dummy.php');
			//$this->cache = new Cache_dummy();
		case 'apc':
			require_once('Cache_apc.php');
			break;
		case 'wincache':
			require_once('Cache_wincache.php');	    
		    break;
		case 'text':
			require('Cache_file.php');
	}
} // end funccachestart();


