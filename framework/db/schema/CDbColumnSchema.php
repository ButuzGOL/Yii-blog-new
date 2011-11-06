<?php
/**
 * CDbColumnSchema class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CDbColumnSchema class describes the column meta data of a database table.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CDbColumnSchema.php 538 2009-01-15 19:34:03Z qiang.xue $
 * @package system.db.schema
 * @since 1.0
 */
class CDbColumnSchema extends CComponent
{
	/**
	 * @var string name of this column (without quotes).
	 */
	public $name;
	/**
	 * @var string raw name of this column. This is the quoted name that can be used in SQL queries.
	 */
	public $rawName;
	/**
	 * @var boolean whether this column can be null.
	 */
	public $allowNull;
	/**
	 * @var string the DB type of this column.
	 */
	public $dbType;
	/**
	 * @var string the PHP type of this column.
	 */
	public $type;
	/**
	 * @var mixed default value of this column
	 */
	public $defaultValue;
	/**
	 * @var integer size of the column.
	 */
	public $size;
	/**
	 * @var integer precision of the column data, if it is numeric.
	 */
	public $precision;
	/**
	 * @var integer scale of the column data, if it is numeric.
	 */
	public $scale;
	/**
	 * @var boolean whether this column is a primary key
	 */
	public $isPrimaryKey;
	/**
	 * @var boolean whether this column is a foreign key
	 */
	public $isForeignKey;


	/**
	 * Initializes the column with its DB type and default value.
	 * This sets up the column's PHP type, size, precision, scale as well as default value.
	 * @param string the column's DB type
	 * @param mixed the default value
	 */
	public function init($dbType, $defaultValue)
	{
		$this->dbType=$dbType;
		$this->extractType($dbType);
		$this->extractLimit($dbType);
		if($defaultValue!==null)
			$this->extractDefault($defaultValue);
	}

	/**
	 * Extracts the PHP type from DB type.
	 * @param string DB type
	 */
	protected function extractType($dbType)
	{
		if(stripos($dbType,'int')!==false)
			$this->type='integer';
		else if(stripos($dbType,'bool')!==false)
			$this->type='boolean';
		else if(preg_match('/(real|floa|doub)/i',$dbType))
			$this->type='double';
		else
			$this->type='string';
	}

	/**
	 * Extracts size, precision and scale information from column's DB type.
	 * @param string the column's DB type
	 */
	protected function extractLimit($dbType)
	{
		if(strpos($dbType,'(') && preg_match('/\((.*)\)/',$dbType,$matches))
		{
			$values=explode(',',$matches[1]);
			$this->size=$this->precision=(int)$values[0];
			if(isset($values[1]))
				$this->scale=(int)$values[1];
		}
	}

	/**
	 * Extracts the default value for the column.
	 * The value is typecasted to correct PHP type.
	 * @param mixed the default value obtained from metadata
	 */
	protected function extractDefault($defaultValue)
	{
		$this->defaultValue=$this->typecast($defaultValue);
	}

	/**
	 * Converts the input value to the type that this column is of.
	 * @param mixed input value
	 * @return mixed converted value
	 */
	public function typecast($value)
	{
		if(gettype($value)===$this->type || $value===null || $value instanceof CDbExpression)
			return $value;
		if($value==='')
			return $this->type==='string' ? '' : null;
		switch($this->type)
		{
			case 'integer': return (integer)$value;
			case 'boolean': return (boolean)$value;
			case 'double': return (double)$value;
			case 'string': return (string)$value;
			default: return $value;
		}
	}
}
