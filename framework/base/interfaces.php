<?php
/**
 * This file contains core interfaces for Yii framework.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * IApplicationComponent is the interface that all application components must implement.
 *
 * After the application completes configuration, it will invoke the {@link init()}
 * method of every loaded application component.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: interfaces.php 1290 2009-08-06 16:13:11Z qiang.xue $
 * @package system.base
 * @since 1.0
 */
interface IApplicationComponent
{
	/**
	 * Initializes the application component.
	 * This method is invoked after the application completes configuration.
	 */
	public function init();
	/**
	 * @return boolean whether the {@link init()} method has been invoked.
	 */
	public function getIsInitialized();
}

/**
 * ICache is the interface that must be implemented by cache components.
 *
 * This interface must be implemented by classes supporting caching feature.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: interfaces.php 1290 2009-08-06 16:13:11Z qiang.xue $
 * @package system.caching
 * @since 1.0
 */
interface ICache
{
	/**
	 * Retrieves a value from cache with a specified key.
	 * @param string a key identifying the cached value
	 * @return mixed the value stored in cache, false if the value is not in the cache or expired.
	 */
	public function get($id);
	/**
	 * Retrieves multiple values from cache with the specified keys.
	 * Some caches (such as memcache, apc) allow retrieving multiple cached values at one time,
	 * which may improve the performance since it reduces the communication cost.
	 * In case a cache doesn't support this feature natively, it will be simulated by this method.
	 * @param array list of keys identifying the cached values
	 * @return array list of cached values corresponding to the specified keys. The array
	 * is returned in terms of (key,value) pairs.
	 * If a value is not cached or expired, the corresponding array value will be false.
	 * @since 1.0.8
	 */
	public function mget($ids);
	/**
	 * Stores a value identified by a key into cache.
	 * If the cache already contains such a key, the existing value and
	 * expiration time will be replaced with the new ones.
	 *
	 * @param string the key identifying the value to be cached
	 * @param mixed the value to be cached
	 * @param integer the number of seconds in which the cached value will expire. 0 means never expire.
	 * @param ICacheDependency dependency of the cached item. If the dependency changes, the item is labelled invalid.
	 * @return boolean true if the value is successfully stored into cache, false otherwise
	 */
	public function set($id,$value,$expire=0,$dependency=null);
	/**
	 * Stores a value identified by a key into cache if the cache does not contain this key.
	 * Nothing will be done if the cache already contains the key.
	 * @param string the key identifying the value to be cached
	 * @param mixed the value to be cached
	 * @param integer the number of seconds in which the cached value will expire. 0 means never expire.
	 * @param ICacheDependency dependency of the cached item. If the dependency changes, the item is labelled invalid.
	 * @return boolean true if the value is successfully stored into cache, false otherwise
	 */
	public function add($id,$value,$expire=0,$dependency=null);
	/**
	 * Deletes a value with the specified key from cache
	 * @param string the key of the value to be deleted
	 * @return boolean whether the deletion is successful
	 */
	public function delete($id);
	/**
	 * Deletes all values from cache.
	 * Be careful of performing this operation if the cache is shared by multiple applications.
	 */
	public function flush();
}

/**
 * ICacheDependency is the interface that must be implemented by cache dependency classes.
 *
 * This interface must be implemented by classes meant to be used as
 * cache dependencies.
 *
 * Objects implementing this interface must be able to be serialized and unserialized.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: interfaces.php 1290 2009-08-06 16:13:11Z qiang.xue $
 * @package system.caching
 * @since 1.0
 */
interface ICacheDependency
{
	/**
	 * Evaluates the dependency by generating and saving the data related with dependency.
	 * This method is invoked by cache before writing data into it.
	 */
	public function evaluateDependency();
	/**
	 * @return boolean whether the dependency has changed.
	 */
	public function getHasChanged();
}


/**
 * IStatePersister is the interface that must be implemented by state persister calsses.
 *
 * This interface must be implemented by all state persister classes (such as
 * {@link CStatePersister}.
 *
 * @version $Id: interfaces.php 1290 2009-08-06 16:13:11Z qiang.xue $
 * @package system.base
 * @since 1.0
 */
interface IStatePersister
{
	/**
	 * Loads state data from a persistent storage.
	 * @return mixed the state
	 */
	public function load();
	/**
	 * Saves state data into a persistent storage.
	 * @param mixed the state to be saved
	 */
	public function save($state);
}


/**
 * IFilter is the interface that must be implemented by action filters.
 *
 * @version $Id: interfaces.php 1290 2009-08-06 16:13:11Z qiang.xue $
 * @package system.base
 * @since 1.0
 */
interface IFilter
{
	/**
	 * Performs the filtering.
	 * This method should be implemented to perform actual filtering.
	 * If the filter wants to continue the action execution, it should call
	 * <code>$filterChain->run()</code>.
	 * @param CFilterChain the filter chain that the filter is on.
	 */
	public function filter($filterChain);
}


/**
 * IAction is the interface that must be implemented by controller actions.
 *
 * @version $Id: interfaces.php 1290 2009-08-06 16:13:11Z qiang.xue $
 * @package system.base
 * @since 1.0
 */
interface IAction
{
	/**
	 * Runs the action.
	 * This method is invoked by the controller owning this action.
	 */
	public function run();
	/**
	 * @return string id of the action
	 */
	public function getId();
	/**
	 * @return CController the controller instance
	 */
	public function getController();
}


/**
 * IWebServiceProvider interface may be implemented by Web service provider classes.
 *
 * If this interface is implemented, the provider instance will be able
 * to intercept the remote method invocation (e.g. for logging or authentication purpose).
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: interfaces.php 1290 2009-08-06 16:13:11Z qiang.xue $
 * @package system.base
 * @since 1.0
 */
interface IWebServiceProvider
{
	/**
	 * This method is invoked before the requested remote method is invoked.
	 * @param CWebService the currently requested Web service.
	 * @return boolean whether the remote method should be executed.
	 */
	public function beforeWebMethod($service);
	/**
	 * This method is invoked after the requested remote method is invoked.
	 * @param CWebService the currently requested Web service.
	 */
	public function afterWebMethod($service);
}


/**
 * IViewRenderer interface is implemented by a view renderer class.
 *
 * A view renderer is {@link CWebApplication::viewRenderer viewRenderer}
 * application component whose wants to replace the default view rendering logic
 * implemented in {@link CBaseController}.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: interfaces.php 1290 2009-08-06 16:13:11Z qiang.xue $
 * @package system.base
 * @since 1.0
 */
interface IViewRenderer
{
	/**
	 * Renders a view file.
	 * @param CBaseController the controller or widget who is rendering the view file.
	 * @param string the view file path
	 * @param mixed the data to be passed to the view
	 * @param boolean whether the rendering result should be returned
	 * @return mixed the rendering result, or null if the rendering result is not needed.
	 */
	public function renderFile($context,$file,$data,$return);
}


/**
 * IUserIdentity interface is implemented by a user identity class.
 *
 * An identity represents a way to authenticate a user and retrieve
 * information needed to uniquely identity the user. It is normally
 * used with the {@link CWebApplication::user user application component}.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: interfaces.php 1290 2009-08-06 16:13:11Z qiang.xue $
 * @package system.base
 * @since 1.0
 */
interface IUserIdentity
{
	/**
	 * Authenticates the user.
	 * The information needed to authenticate the user
	 * are usually provided in the constructor.
	 * @return boolean whether authentication succeeds.
	 */
	public function authenticate();
	/**
	 * Returns a value indicating whether the identity is authenticated.
	 * @return boolean whether the identity is valid.
	 */
	public function getIsAuthenticated();
	/**
	 * Returns a value that uniquely represents the identity.
	 * @return mixed a value that uniquely represents the identity (e.g. primary key value).
	 */
	public function getId();
	/**
	 * Returns the display name for the identity (e.g. username).
	 * @return string the display name for the identity.
	 */
	public function getName();
	/**
	 * Returns the additional identity information that needs to be persistent during the user session.
	 * @return array additional identity information that needs to be persistent during the user session (excluding {@link id}).
	 */
	public function getPersistentStates();
}


/**
 * IWebUser interface is implemented by a {@link CWebApplication::user user application component}.
 *
 * A user application component represents the identity information
 * for the current user.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: interfaces.php 1290 2009-08-06 16:13:11Z qiang.xue $
 * @package system.base
 * @since 1.0
 */
interface IWebUser
{
	/**
	 * Returns a value that uniquely represents the identity.
	 * @return mixed a value that uniquely represents the identity (e.g. primary key value).
	 */
	public function getId();
	/**
	 * Returns the display name for the identity (e.g. username).
	 * @return string the display name for the identity.
	 */
	public function getName();
	/**
	 * Returns a value indicating whether the user is a guest (not authenticated).
	 * @return boolean whether the user is a guest (not authenticated)
	 */
	public function getIsGuest();
	/**
	 * Performs access check for this user.
	 * @param string the name of the operation that need access check.
	 * @param array name-value pairs that would be passed to business rules associated
	 * with the tasks and roles assigned to the user.
	 * @return boolean whether the operations can be performed by this user.
	 */
	public function checkAccess($operation,$params=array());
}


/**
 * IAuthManager interface is implemented by an auth manager application component.
 *
 * An auth manager is mainly responsible for providing role-based access control (RBAC) service.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: interfaces.php 1290 2009-08-06 16:13:11Z qiang.xue $
 * @package system.base
 * @since 1.0
 */
interface IAuthManager
{
	/**
	 * Performs access check for the specified user.
	 * @param string the name of the operation that need access check
	 * @param mixed the user ID. This should can be either an integer and a string representing
	 * the unique identifier of a user. See {@link IWebUser::getId}.
	 * @param array name-value pairs that would be passed to biz rules associated
	 * with the tasks and roles assigned to the user.
	 * @return boolean whether the operations can be performed by the user.
	 */
	public function checkAccess($itemName,$userId,$params=array());

	/**
	 * Creates an authorization item.
	 * An authorization item represents an action permission (e.g. creating a post).
	 * It has three types: operation, task and role.
	 * Authorization items form a hierarchy. Higher level items inheirt permissions representing
	 * by lower level items.
	 * @param string the item name. This must be a unique identifier.
	 * @param integer the item type (0: operation, 1: task, 2: role).
	 * @param string description of the item
	 * @param string business rule associated with the item. This is a piece of
	 * PHP code that will be executed when {@link checkAccess} is called for the item.
	 * @param mixed additional data associated with the item.
	 * @return CAuthItem the authorization item
	 * @throws CException if an item with the same name already exists
	 */
	public function createAuthItem($name,$type,$description='',$bizRule=null,$data=null);
	/**
	 * Removes the specified authorization item.
	 * @param string the name of the item to be removed
	 * @return boolean whether the item exists in the storage and has been removed
	 */
	public function removeAuthItem($name);
	/**
	 * Returns the authorization items of the specific type and user.
	 * @param integer the item type (0: operation, 1: task, 2: role). Defaults to null,
	 * meaning returning all items regardless of their type.
	 * @param mixed the user ID. Defaults to null, meaning returning all items even if
	 * they are not assigned to a user.
	 * @return array the authorization items of the specific type.
	 */
	public function getAuthItems($type=null,$userId=null);
	/**
	 * Returns the authorization item with the specified name.
	 * @param string the name of the item
	 * @return CAuthItem the authorization item. Null if the item cannot be found.
	 */
	public function getAuthItem($name);
	/**
	 * Saves an authorization item to persistent storage.
	 * @param CAuthItem the item to be saved.
	 * @param string the old item name. If null, it means the item name is not changed.
	 */
	public function saveAuthItem($item,$oldName=null);

	/**
	 * Adds an item as a child of another item.
	 * @param string the parent item name
	 * @param string the child item name
	 * @throws CException if either parent or child doesn't exist or if a loop has been detected.
	 */
	public function addItemChild($itemName,$childName);
	/**
	 * Removes a child from its parent.
	 * Note, the child item is not deleted. Only the parent-child relationship is removed.
	 * @param string the parent item name
	 * @param string the child item name
	 * @return boolean whether the removal is successful
	 */
	public function removeItemChild($itemName,$childName);
	/**
	 * Returns a value indicating whether a child exists within a parent.
	 * @param string the parent item name
	 * @param string the child item name
	 * @return boolean whether the child exists
	 */
	public function hasItemChild($itemName,$childName);
	/**
	 * Returns the children of the specified item.
	 * @param mixed the parent item name. This can be either a string or an array.
	 * The latter represents a list of item names (available since version 1.0.5).
	 * @return array all child items of the parent
	 */
	public function getItemChildren($itemName);

	/**
	 * Assigns an authorization item to a user.
	 * @param string the item name
	 * @param mixed the user ID (see {@link IWebUser::getId})
	 * @param string the business rule to be executed when {@link checkAccess} is called
	 * for this particular authorization item.
	 * @param mixed additional data associated with this assignment
	 * @return CAuthAssignment the authorization assignment information.
	 * @throws CException if the item does not exist or if the item has already been assigned to the user
	 */
	public function assign($itemName,$userId,$bizRule=null,$data=null);
	/**
	 * Revokes an authorization assignment from a user.
	 * @param string the item name
	 * @param mixed the user ID (see {@link IWebUser::getId})
	 * @return boolean whether removal is successful
	 */
	public function revoke($itemName,$userId);
	/**
	 * Returns a value indicating whether the item has been assigned to the user.
	 * @param string the item name
	 * @param mixed the user ID (see {@link IWebUser::getId})
	 * @return boolean whether the item has been assigned to the user.
	 */
	public function isAssigned($itemName,$userId);
	/**
	 * Returns the item assignment information.
	 * @param string the item name
	 * @param mixed the user ID (see {@link IWebUser::getId})
	 * @return CAuthAssignment the item assignment information. Null is returned if
	 * the item is not assigned to the user.
	 */
	public function getAuthAssignment($itemName,$userId);
	/**
	 * Returns the item assignments for the specified user.
	 * @param mixed the user ID (see {@link IWebUser::getId})
	 * @return array the item assignment information for the user. An empty array will be
	 * returned if there is no item assigned to the user.
	 */
	public function getAuthAssignments($userId);
	/**
	 * Saves the changes to an authorization assignment.
	 * @param CAuthAssignment the assignment that has been changed.
	 */
	public function saveAuthAssignment($assignment);

	/**
	 * Removes all authorization data.
	 */
	public function clearAll();
	/**
	 * Removes all authorization assignments.
	 */
	public function clearAuthAssignments();

	/**
	 * Saves authorization data into persistent storage.
	 * If any change is made to the authorization data, please make
	 * sure you call this method to save the changed data into persistent storage.
	 */
	public function save();

	/**
	 * Executes a business rule.
	 * A business rule is a piece of PHP code that will be executed when {@link checkAccess} is called.
	 * @param string the business rule to be executed.
	 * @param array additional parameters to be passed to the business rule when being executed.
	 * @param mixed additional data that is associated with the corresponding authorization item or assignment
	 * @return whether the execution returns a true value.
	 * If the business rule is empty, it will also return true.
	 */
	public function executeBizRule($bizRule,$params,$data);
}


/**
 * IBehavior interfaces is implemented by all behavior classes.
 *
 * A behavior is a way to enhance a component with additional methods that
 * are defined in the behavior class and not available in the component class.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: interfaces.php 1290 2009-08-06 16:13:11Z qiang.xue $
 * @package system.base
 * @since 1.0.2
 */
interface IBehavior
{
	/**
	 * Attaches the behavior object to the component.
	 * @param CComponent the component that this behavior is to be attached to.
	 */
	public function attach($component);
	/**
	 * Detaches the behavior object from the component.
	 * @param CComponent the component that this behavior is to be detached from.
	 */
	public function detach($component);
	/**
	 * @return boolean whether this behavior is enabled
	 */
	public function getEnabled();
	/**
	 * @param boolean whether this behavior is enabled
	 */
	public function setEnabled($value);
}
