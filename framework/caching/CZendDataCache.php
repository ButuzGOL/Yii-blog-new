<?php
/**
 * CZendDataCache class file
 *
 * @author Steffen Dietz <steffo.dietz[at]googlemail[dot]com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CZendDataCache implements a cache application module based on the Zend Data Cache
 * delivered with {@link http://www.zend.com/en/products/server/ ZendServer}.
 *
 * To use this application component, the Zend Data Cache PHP extension must be loaded.
 *
 * See {@link CCache} manual for common cache operations that are supported by CZendDataCache.
 *
 * @author Steffen Dietz <steffo.dietz[at]googlemail[dot]com>
 * @version $Id: CZendDataCache.php 852 2009-03-20 14:14:44Z qiang.xue $
 * @package system.caching
 * @since 1.0.4
 */
class CZendDataCache extends CCache
{
	/**
	 * Initializes this application component.
	 * This method is required by the {@link IApplicationComponent} interface.
	 * It checks the availability of Zend Data Cache.
	 * @throws CException if Zend Data Cache extension is not loaded.
	 */
	public function init()
	{
		parent::init();
		if(!function_exists('zend_shm_cache_store'))
			throw new CException(Yii::t('yii','CZendDataCache requires PHP Zend Data Cache extension to be loaded.'));
	}

	/**
	 * Retrieves a value from cache with a specified key.
	 * This is the implementation of the method declared in the parent class.
	 * @param string a unique key identifying the cached value
	 * @return string the value stored in cache, false if the value is not in the cache or expired.
	 */
	protected function getValue($key)
	{
		$result = zend_shm_cache_fetch($key);
		return $result !== NULL ? $result : false;
	}

	/**
	 * Stores a value identified by a key in cache.
	 * This is the implementation of the method declared in the parent class.
	 *
	 * @param string the key identifying the value to be cached
	 * @param string the value to be cached
	 * @param integer the number of seconds in which the cached value will expire. 0 means never expire.
	 * @return boolean true if the value is successfully stored into cache, false otherwise
	 */
	protected function setValue($key,$value,$expire)
	{
		return zend_shm_cache_store($key,$value,$expire);
	}

	/**
	 * Stores a value identified by a key into cache if the cache does not contain this key.
	 * This is the implementation of the method declared in the parent class.
	 *
	 * @param string the key identifying the value to be cached
	 * @param string the value to be cached
	 * @param integer the number of seconds in which the cached value will expire. 0 means never expire.
	 * @return boolean true if the value is successfully stored into cache, false otherwise
	 */
	protected function addValue($key,$value,$expire)
	{
		return (NULL === zend_shm_cache_fetch($key)) ? $this->setValue($key,$value,$expire) : false;
	}

	/**
	 * Deletes a value with the specified key from cache
	 * This is the implementation of the method declared in the parent class.
	 * @param string the key of the value to be deleted
	 * @return boolean if no error happens during deletion
	 */
	protected function deleteValue($key)
	{
		return zend_shm_cache_delete($key);
	}

	/**
	 * Deletes all values from cache.
	 * Be careful of performing this operation if the cache is shared by multiple applications.
	 */
	public function flush()
	{
		zend_shm_cache_clear();
	}
}
