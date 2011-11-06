<?php
/**
 * CTypeValidator class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CTypeValidator verifies if the attribute is of the type specified by {@link type}.
 *
 * The following data types are supported:
 * <ul>
 * <li><b>integer</b> A 32-bit signed integer data type.</li>
 * <li><b>float</b> A double-precision floating point number data type.</li>
 * <li><b>string</b> A string data type.</li>
 * <li><b>date</b> A date data type.</li>
 * <li><b>time</b> A time data type (available since version 1.0.5).</li>
 * <li><b>datetime</b> A date and time data type (available since version 1.0.5).</li>
 * </ul>
 *
 * For <b>date</b> type, the property {@link dateFormat}
 * will be used to determine how to parse the date string. If the given date
 * value doesn't follow the format, the attribute is considered as invalid.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CTypeValidator.php 1333 2009-08-15 20:20:36Z qiang.xue $
 * @package system.validators
 * @since 1.0
 */
class CTypeValidator extends CValidator
{
	/**
	 * @var string the data type that the attribute should be. Defaults to 'string'.
	 * Valid values include 'string', 'integer', 'float', 'date', 'time' and 'datetime'.
	 * Note that 'time' and 'datetime' have been available since version 1.0.5.
	 */
	public $type='string';
	/**
	 * @var string the format pattern that the date value should follow. Defaults to 'MM/dd/yyyy'.
	 * Please see {@link CDateTimeParser} for details about how to specify a date format.
	 * This property is effective only when {@link type} is 'date'.
	 */
	public $dateFormat='MM/dd/yyyy';
	/**
	 * @var string the format pattern that the time value should follow. Defaults to 'hh:mm'.
	 * Please see {@link CDateTimeParser} for details about how to specify a time format.
	 * This property is effective only when {@link type} is 'time'.
	 * @since 1.0.5
	 */
	public $timeFormat='hh:mm';
	/**
	 * @var string the format pattern that the datetime value should follow. Defaults to 'MM/dd/yyyy hh:mm'.
	 * Please see {@link CDateTimeParser} for details about how to specify a datetime format.
	 * This property is effective only when {@link type} is 'datetime'.
	 * @since 1.0.5
	 */
	public $datetimeFormat='MM/dd/yyyy hh:mm';
	/**
	 * @var boolean whether the attribute value can be null or empty. Defaults to true,
	 * meaning that if the attribute is empty, it is considered valid.
	 */
	public $allowEmpty=true;

	/**
	 * Validates the attribute of the object.
	 * If there is any error, the error message is added to the object.
	 * @param CModel the object being validated
	 * @param string the attribute being validated
	 */
	protected function validateAttribute($object,$attribute)
	{
		$value=$object->$attribute;
		if($this->allowEmpty && $this->isEmpty($value))
			return;

		if($this->type==='integer')
			$valid=preg_match('/^[-+]?[0-9]+$/',trim($value));
		else if($this->type==='float')
			$valid=preg_match('/^[-+]?([0-9]*\.)?[0-9]+([eE][-+]?[0-9]+)?$/',trim($value));
		else if($this->type==='date')
			$valid=CDateTimeParser::parse($value,$this->dateFormat)!==false;
	    else if($this->type==='time')
			$valid=CDateTimeParser::parse($value,$this->timeFormat)!==false;
	    else if($this->type==='datetime')
			$valid=CDateTimeParser::parse($value,$this->datetimeFormat)!==false;
		else
			return;

		if(!$valid)
		{
			$message=$this->message!==null?$this->message : Yii::t('yii','{attribute} must be {type}.');
			$this->addError($object,$attribute,$message,array('{type}'=>$this->type));
		}
	}
}

