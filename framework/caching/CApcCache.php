<?php
/**
 * CApcCache class file
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CApcCache provides APC caching in terms of an application component.
 *
 * The caching is based on {@link http://www.php.net/apc APC}.
 * To use this application component, the APC PHP extension must be loaded.
 *
 * See {@link CCache} manual for common cache operations that are supported by CApcCache.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CApcCache.php 1290 2009-08-06 16:13:11Z qiang.xue $
 * @package system.caching
 * @since 1.0
 */
class CApcCache extends CCache
{
	/**
	 * Initializes this application component.
	 * This method is required by the {@link IApplicationComponent} interface.
	 * It checks the availability of memcache.
	 * @throws CException if APC cache extension is not loaded or is disabled.
	 */
	public function init()
	{
		parent::init();
		if(!extension_loaded('apc'))
			throw new CException(Yii::t('yii','CApcCache requires PHP apc extension to be loaded.'));
	}

	/**
	 * Retrieves a value from cache with a specified key.
	 * This is the implementation of the method declared in the parent class.
	 * @param string a unique key identifying the cached value
	 * @return string the value stored in cache, false if the value is not in the cache or expired.
	 */
	protected function getValue($key)
	{
		return apc_fetch($key);
	}

	/**
	 * Retrieves multiple values from cache with the specified keys.
	 * @param array a list of keys identifying the cached values
	 * @return array a list of cached values indexed by the keys
	 * @since 1.0.8
	 */
	protected function getValues($keys)
	{
		return array_combine($keys,apc_fetch($keys));
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
		return apc_store($key,$value,$expire);
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
		return apc_add($key,$value,$expire);
	}

	/**
	 * Deletes a value with the specified key from cache
	 * This is the implementation of the method declared in the parent class.
	 * @param string the key of the value to be deleted
	 * @return boolean if no error happens during deletion
	 */
	protected function deleteValue($key)
	{
		return apc_delete($key);
	}

	/**
	 * Deletes all values from cache.
	 * Be careful of performing this operation if the cache is shared by multiple applications.
	 */
	public function flush()
	{
		return apc_clear_cache('user');
	}
}
