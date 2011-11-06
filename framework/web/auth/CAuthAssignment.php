<?php
/**
 * CAuthAssignment class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CAuthAssignment represents an assignment of a role to a user.
 * It includes additional assignment information such as {@link bizRule} and {@link data}.
 * Do not create a CAuthAssignment instance using the 'new' operator.
 * Instead, call {@link IAuthManager::assign}.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CAuthAssignment.php 433 2008-12-30 22:59:17Z qiang.xue $
 * @package system.web.auth
 * @since 1.0
 */
class CAuthAssignment extends CComponent
{
	private $_auth;
	private $_itemName;
	private $_userId;
	private $_bizRule;
	private $_data;

	/**
	 * Constructor.
	 * @param IAuthManager the authorization manager
	 * @param string authorization item name
	 * @param mixed user ID (see {@link IWebUser::getId})
	 * @param string the business rule associated with this assignment
	 * @param mixed additional data for this assignment
	 */
	public function __construct($auth,$itemName,$userId,$bizRule=null,$data=null)
	{
		$this->_auth=$auth;
		$this->_itemName=$itemName;
		$this->_userId=$userId;
		$this->_bizRule=$bizRule;
		$this->_data=$data;
	}

	/**
	 * @return mixed user ID (see {@link IWebUser::getId})
	 */
	public function getUserId()
	{
		return $this->_userId;
	}

	/**
	 * @return string the authorization item name
	 */
	public function getItemName()
	{
		return $this->_itemName;
	}

	/**
	 * @return string the business rule associated with this assignment
	 */
	public function getBizRule()
	{
		return $this->_bizRule;
	}

	/**
	 * @param string the business rule associated with this assignment
	 */
	public function setBizRule($value)
	{
		if($this->_bizRule!==$value)
		{
			$this->_bizRule=$value;
			$this->_auth->saveAuthAssignment($this);
		}
	}

	/**
	 * @return mixed additional data for this assignment
	 */
	public function getData()
	{
		return $this->_data;
	}

	/**
	 * @param mixed additional data for this assignment
	 */
	public function setData($value)
	{
		if($this->_data!==$value)
		{
			$this->_data=$value;
			$this->_auth->saveAuthAssignment($this);
		}
	}
}