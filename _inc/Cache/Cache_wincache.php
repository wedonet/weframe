<?php


class ClsCache {

	/**
	 * Get 
	 *
	 * Look for a value in the cache.  If it exists, return the data 
	 * if not, return FALSE
	 *
	 * @param 	string	
	 * @return 	mixed		value that is stored/FALSE on failure
	 */
   
   
	public function get($id)
	{
		$data = wincache_ucache_get($id);

		return (is_array($data)) ? $data[0] : FALSE;
	}

	// ------------------------------------------------------------------------	
	
	/**
	 * Cache Save
	 *
	 * @param 	string		Unique Key
	 * @param 	mixed		Data to store
	 * @param 	int			Length of time (in seconds) to cache the data
	 *
	 * @return 	boolean		true on success/false on failure
	 */
	public function save($id, $data, $ttl = 0)
	{
		return wincache_ucache_add($id, array($data, time(), $ttl), $ttl);
	}
	
	// ------------------------------------------------------------------------

	/**
	 * Delete from Cache
	 *
	 * @param 	mixed		unique identifier of the item in the cache
	 * @param 	boolean		true on success/false on failure
	 */
	public function delete($id)
	{
		return wincache_ucache_delete($id);
	}

	// ------------------------------------------------------------------------

	/**
	 * Clean the cache
	 *
	 * @return 	boolean		false on failure/true on success
	 */
	public function clean()
	{
		return apc_clear_cache('user');
	}

	// ------------------------------------------------------------------------

	/**
	 * Cache Info
	 *
	 * @param 	string		user/filehits
	 * @return 	mixed		array on success, false on failure	
	 */
	 public function cache_info($type = NULL)
	 {
		 return apc_cache_info($type);
	 }

	// ------------------------------------------------------------------------

	/**
	 * Get Cache Metadata
	 *
	 * @param 	mixed		key to get cache metadata on
	 * @return 	mixed		array on success/false on failure
	 */
	public function get_metadata($id)
	{
		$stored = apc_fetch($id);

		if (count($stored) !== 3)
		{
			return FALSE;
		}

		list($data, $time, $ttl) = $stored;

		return array(
			'expire'	=> $time + $ttl,
			'mtime'		=> $time,
			'data'		=> $data
		);
	}

	// ------------------------------------------------------------------------

	/**
	 * is_supported()
	 *
	 * Check to see if APC is available on this system, bail if it isn't.
	 */
	public function is_supported()
	{
		if ( ! extension_loaded('apc') OR ini_get('apc.enabled') != "1")
		{
			log_message('error', 'The APC PHP extension must be loaded to use APC Cache.');
			return FALSE;
		}
		
		return TRUE;
	}

	// ------------------------------------------------------------------------
	// 返回缓存数组
	public function getrs()	{
		$a =  wincache_ucache_info();
        $rs = $a['ucache_entries']  ;
		return $rs;
	} // end func
	

}
// End Class

/* End of file Cache_apc.php */
/* Location: ./system/libraries/Cache/drivers/Cache_apc.php */
