<?php
/**
 * CErrorEvent class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CErrorEvent represents the parameter for the {@link CApplication::onError onError} event.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CErrorEvent.php 870 2009-03-22 15:06:23Z qiang.xue $
 * @package system.base
 * @since 1.0
 */
class CErrorEvent extends CEvent
{
	/**
	 * @var string error code
	 */
	public $code;
	/**
	 * @var string error message
	 */
	public $message;
	/**
	 * @var string error message
	 */
	public $file;
	/**
	 * @var string error file
	 */
	public $line;

	/**
	 * Constructor.
	 * @param mixed sender of the event
	 * @param string error code
	 * @param string error message
	 * @param string error file
	 * @param integer error line
	 */
	public function __construct($sender,$code,$message,$file,$line)
	{
		$this->code=$code;
		$this->message=$message;
		$this->file=$file;
		$this->line=$line;
		parent::__construct($sender);
	}
}
