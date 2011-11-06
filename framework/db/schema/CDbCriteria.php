<?php
/**
 * CDbCriteria class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CDbCriteria represents a query criteria, such as conditions, ordering by, limit/offset.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CDbCriteria.php 1573 2009-12-11 15:30:33Z qiang.xue $
 * @package system.db.schema
 * @since 1.0
 */
class CDbCriteria
{
	const PARAM_PREFIX=':ycp';
	private $_paramCount=0;

	/**
	 * @var mixed the columns being selected. This refers to the SELECT clause in an SQL
	 * statement. The property can be either a string (column names separated by commas)
	 * or an array of column names. Defaults to '*', meaning all columns.
	 */
	public $select='*';
	/**
	 * @var boolean whether to select distinct rows of data only. If this is set true,
	 * the SELECT clause would be changed to SELECT DISTINCT.
	 * @since 1.0.9
	 */
	public $distinct=false;
	/**
	 * @var string query condition. This refers to the WHERE clause in an SQL statement.
	 * For example, <code>age>31 AND team=1</code>.
	 */
	public $condition='';
	/**
	 * @var array list of query parameter values indexed by parameter placeholders.
	 * For example, <code>array(':name'=>'Dan', ':age'=>31)</code>.
	 */
	public $params=array();
	/**
	 * @var integer maximum number of records to be returned. If less than 0, it means no limit.
	 */
	public $limit=-1;
	/**
	 * @var integer zero-based offset from where the records are to be returned. If less than 0, it means starting from the beginning.
	 */
	public $offset=-1;
	/**
	 * @var string how to sort the query results. This refers to the ORDER BY clause in an SQL statement.
	 */
	public $order='';
	/**
	 * @var string how to group the query results. This refers to the GROUP BY clause in an SQL statement.
	 * For example, <code>'projectID, teamID'</code>.
	 */
	public $group='';
	/**
	 * @var string how to join with other tables. This refers to the JOIN clause in an SQL statement.
	 * For example, <code>'LEFT JOIN users ON users.id=authorID'</code>.
	 */
	public $join='';
	/**
	 * @var string the condition to be applied with GROUP-BY clause.
	 * For example, <code>'SUM(revenue)<50000'</code>.
	 * @since 1.0.1
	 */
	public $having='';

	/**
	 * Constructor.
	 * @param array criteria initial property values (indexed by property name)
	 */
	public function __construct($data=array())
	{
		foreach($data as $name=>$value)
			$this->$name=$value;
	}

	/**
	 * Appends a condition to the existing {@link condition}.
	 * The new condition and the existing condition will be concatenated via the specified operator
	 * which defaults to 'AND'.
	 * The new condition can also be an array. In this case, all elements in the array
	 * will be concatenated together via the operator.
	 * This method handles the case when the existing condition is empty.
	 * After calling this method, the {@link condition} property will be modified.
	 * @param mixed the new condition. It can be either a string or an array of strings.
	 * @param string the operator to join different conditions. Defaults to 'AND'.
	 * @return CDbCriteria the criteria object itself
	 * @since 1.0.9
	 */
	public function addCondition($condition,$operator='AND')
	{
		if(is_array($condition))
		{
			if($condition===array())
				return $this;
			$condition='('.implode(') '.$operator.' (',$condition).')';
		}
		if($this->condition==='')
			$this->condition=$condition;
		else
			$this->condition='('.$this->condition.') '.$operator.' ('.$condition.')';
		return $this;
	}

	/**
	 * Appends a search condition to the existing {@link condition}.
	 * The search condition and the existing condition will be concatenated via the specified operator
	 * which defaults to 'AND'.
	 * The search condition is generated using the SQL LIKE operator with the given column name and
	 * search keyword.
	 * @param string the column name (or a valid SQL expression)
	 * @param string the search keyword. This interpretation of the keyword is affected by the next parameter.
	 * @param boolean whether the keyword should be escaped if it contains characters % or _.
	 * When this parameter is true (default), the special characters % (matches 0 or more characters)
	 * and _ (matches a single character) will be escaped, and the keyword will be surrounded with a %
	 * character on both ends. When this parameter is false, the keyword will be directly used for
	 * matching without any change.
	 * @param string the operator used to concatenate the new condition with the existing one.
	 * Defaults to 'AND'.
	 * @return CDbCriteria the criteria object itself
	 * @since 1.0.10
	 */
	public function addSearchCondition($column,$keyword,$escape=true,$operator='AND')
	{
		if($escape)
			$keyword='%'.strtr($keyword,array('%'=>'\%', '_'=>'\_')).'%';
		$condition=$column.' LIKE '.self::PARAM_PREFIX.$this->_paramCount;
		$this->params[self::PARAM_PREFIX.$this->_paramCount++]=$keyword;
		return $this->addCondition($condition, $operator);
	}

	/**
	 * Appends an IN condition to the existing {@link condition}.
	 * The IN condition and the existing condition will be concatenated via the specified operator
	 * which defaults to 'AND'.
	 * The IN condition is generated by using the SQL IN operator which requires the specified
	 * column value to be among the given list of values.
	 * @param string the column name (or a valid SQL expression)
	 * @param array list of values that the column value should be in
	 * @param string the operator used to concatenate the new condition with the existing one.
	 * Defaults to 'AND'.
	 * @return CDbCriteria the criteria object itself
	 * @since 1.0.10
	 */
	public function addInCondition($column,$values,$operator='AND')
	{
		if(($n=count($values))<1)
			return $this->addCondition('0=1',$operator);
		if($n===1)
		{
			if($values[0]===null)
				return $this->addCondition($column.' IS NULL');
			$condition=$column.'='.self::PARAM_PREFIX.$this->_paramCount;
			$this->params[self::PARAM_PREFIX.$this->_paramCount++]=$values[0];
		}
		else
		{
			$params=array();
			foreach($values as $value)
			{
				$params[]=self::PARAM_PREFIX.$this->_paramCount;
				$this->params[self::PARAM_PREFIX.$this->_paramCount++]=$value;
			}
			$condition=$column.' IN ('.implode(', ',$params).')';
		}
		return $this->addCondition($condition,$operator);
	}

	/**
	 * Appends a condition for matching the given list of column values.
	 * The generated condition will be concatenated to the existing {@link condition}
	 * via the specified operator which defaults to 'AND'.
	 * The condition is generated by matching each column and the corresponding value.
	 * @param array list of column names and values to be matched (name=>value)
	 * @param string the operator to concatenate multiple column matching condition. Defaults to 'AND'.
	 * @param string the operator used to concatenate the new condition with the existing one.
	 * Defaults to 'AND'.
	 * @return CDbCriteria the criteria object itself
	 * @since 1.0.10
	 */
	public function addColumnCondition($columns,$columnOperator='AND',$operator='AND')
	{
		$params=array();
		foreach($columns as $name=>$value)
		{
			$params[]=$name.'='.self::PARAM_PREFIX.$this->_paramCount;
			$this->params[self::PARAM_PREFIX.$this->_paramCount++]=$value;
		}
		return $this->addCondition(implode(" $columnOperator ",$params), $operator);
	}

	/**
	 * Merges with another criteria.
	 * In general, the merging makes the resulting criteria more restrictive.
	 * For example, if both criterias have conditions, they will be 'AND' together.
	 * Also, the criteria passed as the parameter takes precedence in case
	 * two options cannot be merged (e.g. LIMIT, OFFSET).
	 * @param CDbCriteria the criteria to be merged with.
	 * @param boolean whether to use 'AND' to merge condition and having options.
	 * If false, 'OR' will be used instead. Defaults to 'AND'. This parameter has been
	 * available since version 1.0.6.
	 * @since 1.0.5
	 */
	public function mergeWith($criteria,$useAnd=true)
	{
		$and=$useAnd ? 'AND' : 'OR';
		if(is_array($criteria))
			$criteria=new self($criteria);
		if($this->select!==$criteria->select)
		{
			if($this->select==='*')
				$this->select=$criteria->select;
			else if($criteria->select!=='*')
			{
				$select1=is_string($this->select)?preg_split('/\s*,\s*/',trim($this->select),-1,PREG_SPLIT_NO_EMPTY):$this->select;
				$select2=is_string($criteria->select)?preg_split('/\s*,\s*/',trim($criteria->select),-1,PREG_SPLIT_NO_EMPTY):$criteria->select;
				$this->select=array_merge($select1,array_diff($select2,$select1));
			}
		}

		if($this->condition!==$criteria->condition)
		{
			if($this->condition==='')
				$this->condition=$criteria->condition;
			else if($criteria->condition!=='')
				$this->condition="({$this->condition}) $and ({$criteria->condition})";
		}

		if($this->params!==$criteria->params)
			$this->params=array_merge($this->params,$criteria->params);

		if($criteria->limit>0)
			$this->limit=$criteria->limit;

		if($criteria->offset>=0)
			$this->offset=$criteria->offset;

		if($this->order!==$criteria->order)
		{
			if($this->order==='')
				$this->order=$criteria->order;
			else if($criteria->order!=='')
				$this->order=$criteria->order.', '.$this->order;
		}

		if($this->group!==$criteria->group)
		{
			if($this->group==='')
				$this->group=$criteria->group;
			else if($criteria->group!=='')
				$this->group.=', '.$criteria->group;
		}

		if($this->join!==$criteria->join)
		{
			if($this->join==='')
				$this->join=$criteria->join;
			else if($criteria->join!=='')
				$this->join.=' '.$criteria->join;
		}

		if($this->having!==$criteria->having)
		{
			if($this->having==='')
				$this->having=$criteria->having;
			else if($criteria->having!=='')
				$this->having="({$this->having}) $and ({$criteria->having})";
		}

		if($criteria->distinct>0)
			$this->distinct=$criteria->distinct;
	}

	/**
	 * @return array the array representation of the criteria
	 * @since 1.0.6
	 */
	public function toArray()
	{
		$result=array();
		foreach(array('select', 'condition', 'params', 'limit', 'offset', 'order', 'group', 'join', 'having', 'distinct') as $name)
			$result[$name]=$this->$name;
		return $result;
	}
}
