<?php
/**
 * CUniqueValidator class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CUniqueValidator validates that the attribute value is unique in the corresponding database table.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CUniqueValidator.php 1333 2009-08-15 20:20:36Z qiang.xue $
 * @package system.validators
 * @since 1.0
 */
class CUniqueValidator extends CValidator
{
	/**
	 * @var boolean whether the comparison is case sensitive. Defaults to true.
	 * Note, by setting it to false, you are assuming the attribute type is string.
	 */
	public $caseSensitive=true;
	/**
	 * @var boolean whether the attribute value can be null or empty. Defaults to true,
	 * meaning that if the attribute is empty, it is considered valid.
	 */
	public $allowEmpty=true;
	/**
	 * @var string the ActiveRecord class name that should be used to
	 * look for the attribute value being validated. Defaults to null, meaning using
	 * the class of the object currently being validated.
	 * You may use path alias to reference a class name here.
	 * @see attributeName
	 * @since 1.0.8
	 */
	public $className;
	/**
	 * @var string the ActiveRecord class attribute name that should be
	 * used to look for the attribute value being validated. Defaults to null,
	 * meaning using the name of the attribute being validated.
	 * @see className
	 * @since 1.0.8
	 */
	public $attributeName;
	/**
	 * @var array additional query criteria. This will be combined with the condition
	 * that checks if the attribute value exists in the corresponding table column.
	 * This array will be used to instantiate a {@link CDbCriteria} object.
	 * @since 1.0.8
	 */
	public $criteria=array();


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

		$className=$this->className===null?get_class($object):Yii::import($this->className);
		$attributeName=$this->attributeName===null?$attribute:$this->attributeName;
		$finder=CActiveRecord::model($className);
		$table=$finder->getTableSchema();
		if(($column=$table->getColumn($attributeName))===null)
			throw new CException(Yii::t('yii','Column "{column} does not exist in table "{table}".',
				array('{column}'=>$attributeName,'{table}'=>$table->name)));

		$columnName=$column->rawName;
		$criteria=new CDbCriteria(array(
			'condition'=>$this->caseSensitive ? "$columnName=:value" : "LOWER($columnName)=LOWER(:value)",
			'params'=>array(':value'=>$value),
		));
		if($this->criteria!==array())
			$criteria->mergeWith($this->criteria);

		if($column->isPrimaryKey || $this->className!==null)
			$exists=$finder->exists($criteria);
		else
		{
			// need to exclude the current record based on PK
			$criteria->limit=2;
			$objects=$finder->findAll($criteria);
			$n=count($objects);
			if($n===1)
				$exists=$objects[0]->getPrimaryKey()!=$object->getPrimaryKey();
			else
				$exists=$n>1;
		}

		if($exists)
		{
			$message=$this->message!==null?$this->message:Yii::t('yii','{attribute} "{value}" has already been taken.');
			$this->addError($object,$attribute,$message,array('{value}'=>$value));
		}
	}
}

