<?php
/**
 * CActiveRecord class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CActiveFinder implements eager loading and lazy loading of related active records.
 *
 * When used in eager loading, this class provides the same set of find methods as
 * {@link CActiveRecord}.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CActiveFinder.php 1563 2009-12-07 20:06:34Z qiang.xue $
 * @package system.db.ar
 * @since 1.0
 */
class CActiveFinder extends CComponent
{
	/**
	 * @var boolean join all tables all at once. Defaults to false.
	 * This property is internally used.
	 * @since 1.0.2
	 */
	public $joinAll=false;
	/**
	 * @var boolean whether the base model has limit or offset.
	 * This property is internally used.
	 * @since 1.0.2
	 */
	public $baseLimited;

	private $_joinCount=0;
	private $_joinTree;
	private $_builder;
	private $_criteria;  // the criteria generated via named scope

	/**
	 * Constructor.
	 * A join tree is built up based on the declared relationships between active record classes.
	 * @param CActiveRecord the model that initiates the active finding process
	 * @param mixed the relation names to be actively looked for
	 * @param CDbCriteria the criteria associated with the named scopes (since version 1.0.5)
	 */
	public function __construct($model,$with,$criteria=null)
	{
		$this->_criteria=$criteria;
		$this->_builder=$model->getCommandBuilder();
		$this->_joinTree=new CJoinElement($this,$model);
		$this->buildJoinTree($this->_joinTree,$with);
	}

	/**
	 * Uses the most aggressive join approach.
	 * By default, several join statements may be generated in order to avoid
	 * fetching duplicated data. By calling this method, all tables will be joined
	 * together all at once.
	 * @param boolean whether we should enforce join even when a limit option is placed on the primary table query.
	 * Defaults to true. If false, we would still use two queries when there is a HAS_MANY/MANY_MANY relation and
	 * the primary table has a LIMIT option. This parameter is available since version 1.0.3.
	 * @return CActiveFinder the finder object
	 * @since 1.0.2
	 */
	public function together($ignoreLimit=true)
	{
		$this->joinAll=true;
		if($ignoreLimit)
			$this->baseLimited=false;
		return $this;
	}

	private function query($criteria,$all=false)
	{
		if($this->_criteria!==null)
		{
			$this->_criteria->mergeWith($criteria);
			$criteria=$this->_criteria;
		}

		$this->_joinTree->beforeFind();
		$this->_joinTree->find($criteria);
		$this->_joinTree->afterFind();

		if($all)
			return array_values($this->_joinTree->records);
		else if(count($this->_joinTree->records))
			return reset($this->_joinTree->records);
		else
			return null;
	}

	/**
	 * This is the relational version of {@link CActiveRecord::find()}.
	 */
	public function find($condition='',$params=array())
	{
		Yii::trace(get_class($this->_joinTree->model).'.find() eagerly','system.db.ar.CActiveRecord');
		$criteria=$this->_builder->createCriteria($condition,$params);
		return $this->query($criteria);
	}

	/**
	 * This is the relational version of {@link CActiveRecord::findAll()}.
	 */
	public function findAll($condition='',$params=array())
	{
		Yii::trace(get_class($this->_joinTree->model).'.findAll() eagerly','system.db.ar.CActiveRecord');
		$criteria=$this->_builder->createCriteria($condition,$params);
		return $this->query($criteria,true);
	}

	/**
	 * This is the relational version of {@link CActiveRecord::findByPk()}.
	 */
	public function findByPk($pk,$condition='',$params=array())
	{
		Yii::trace(get_class($this->_joinTree->model).'.findByPk() eagerly','system.db.ar.CActiveRecord');
		$criteria=$this->_builder->createPkCriteria($this->_joinTree->model->getTableSchema(),$pk,$condition,$params);
		return $this->query($criteria);
	}

	/**
	 * This is the relational version of {@link CActiveRecord::findAllByPk()}.
	 */
	public function findAllByPk($pk,$condition='',$params=array())
	{
		Yii::trace(get_class($this->_joinTree->model).'.findAllByPk() eagerly','system.db.ar.CActiveRecord');
		$criteria=$this->_builder->createPkCriteria($this->_joinTree->model->getTableSchema(),$pk,$condition,$params);
		return $this->query($criteria,true);
	}

	/**
	 * This is  the relational version of {@link CActiveRecord::findByAttributes()}.
	 */
	public function findByAttributes($attributes,$condition='',$params=array())
	{
		Yii::trace(get_class($this->_joinTree->model).'.findByAttributes() eagerly','system.db.ar.CActiveRecord');
		$criteria=$this->_builder->createColumnCriteria($this->_joinTree->model->getTableSchema(),$attributes,$condition,$params);
		return $this->query($criteria);
	}

	/**
	 * This is the relational version of {@link CActiveRecord::findAllByAttributes()}.
	 */
	public function findAllByAttributes($attributes,$condition='',$params=array())
	{
		Yii::trace(get_class($this->_joinTree->model).'.findAllByAttributes() eagerly','system.db.ar.CActiveRecord');
		$criteria=$this->_builder->createColumnCriteria($this->_joinTree->model->getTableSchema(),$attributes,$condition,$params);
		return $this->query($criteria,true);
	}

	/**
	 * This is the relational version of {@link CActiveRecord::findBySql()}.
	 */
	public function findBySql($sql,$params=array())
	{
		Yii::trace(get_class($this->_joinTree->model).'.findBySql() eagerly','system.db.ar.CActiveRecord');
		if(($row=$this->_builder->createSqlCommand($sql,$params)->queryRow())!==false)
		{
			$baseRecord=$this->_joinTree->model->populateRecord($row,false);
			$this->_joinTree->beforeFind();
			$this->_joinTree->findWithBase($baseRecord);
			$this->_joinTree->afterFind();
			return $baseRecord;
		}
	}

	/**
	 * This is the relational version of {@link CActiveRecord::findAllBySql()}.
	 */
	public function findAllBySql($sql,$params=array())
	{
		Yii::trace(get_class($this->_joinTree->model).'.findAllBySql() eagerly','system.db.ar.CActiveRecord');
		if(($rows=$this->_builder->createSqlCommand($sql,$params)->queryAll())!==array())
		{
			$baseRecords=$this->_joinTree->model->populateRecords($rows,false);
			$this->_joinTree->beforeFind();
			$this->_joinTree->findWithBase($baseRecords);
			$this->_joinTree->afterFind();
			return $baseRecords;
		}
		else
			return array();
	}

	/**
	 * This is the relational version of {@link CActiveRecord::count()}.
	 * @since 1.0.3
	 */
	public function count($condition='',$params=array())
	{
		Yii::trace(get_class($this->_joinTree->model).'.count() eagerly','system.db.ar.CActiveRecord');
		$criteria=$this->_builder->createCriteria($condition,$params);
		if($this->_criteria!==null)
		{
			$this->_criteria->mergeWith($criteria);
			$criteria=$this->_criteria;
		}
		return $this->_joinTree->count($criteria);
	}

	/**
	 * Finds the related objects for the specified active record.
	 * This method is internally invoked by {@link CActiveRecord} to support lazy loading.
	 * @param CActiveRecord the base record whose related objects are to be loaded
	 */
	public function lazyFind($baseRecord)
	{
		$this->_joinTree->lazyFind($baseRecord);
		if(!empty($this->_joinTree->children))
		{
			$child=reset($this->_joinTree->children);
			$child->afterFind();
		}
	}

	/**
	 * Builds up the join tree representing the relationships involved in this query.
	 * @param CJoinElement the parent tree node
	 * @param mixed the names of the related objects relative to the parent tree node
	 * @param array additional query options to be merged with the relation
	 */
	private function buildJoinTree($parent,$with,$options=null)
	{
		if($parent instanceof CStatElement)
			throw new CDbException(Yii::t('yii','The STAT relation "{name}" cannot have child relations.',
				array('{name}'=>$parent->relation->name)));

		if(is_string($with))
		{
			if(($pos=strrpos($with,'.'))!==false)
			{
				$parent=$this->buildJoinTree($parent,substr($with,0,$pos));
				$with=substr($with,$pos+1);
			}

			// named scope
			if(($pos=strpos($with,':'))!==false)
			{
				$scopes=explode(':',substr($with,$pos+1));
				$with=substr($with,0,$pos);
			}

			if(isset($parent->children[$with]))
				return $parent->children[$with];

			if(($relation=$parent->model->getActiveRelation($with))===null)
				throw new CDbException(Yii::t('yii','Relation "{name}" is not defined in active record class "{class}".',
					array('{class}'=>get_class($parent->model), '{name}'=>$with)));

			$relation=clone $relation;
			$model=CActiveRecord::model($relation->className);
			if(($scope=$model->defaultScope())!==array())
				$relation->mergeWith($scope);
			if(isset($scopes) && !empty($scopes))
			{
				$scs=$model->scopes();
				foreach($scopes as $scope)
				{
					if(isset($scs[$scope]))
						$relation->mergeWith($scs[$scope]);
					else
						throw new CDbException(Yii::t('yii','Active record class "{class}" does not have a scope named "{scope}".',
							array('{class}'=>get_class($model), '{scope}'=>$scope)));
				}
			}

			// dynamic options
			if($options!==null)
				$relation->mergeWith($options);

			if($relation instanceof CStatRelation)
				return new CStatElement($this,$relation,$parent);
			else
			{
				$element=$parent->children[$with]=new CJoinElement($this,$relation,$parent,++$this->_joinCount);
				if(!empty($relation->with))
					$this->buildJoinTree($element,$relation->with);
				return $element;
			}
		}

		// $with is an array, keys are relation name, values are relation spec
		foreach($with as $key=>$value)
		{
			if(is_string($value))  // the value is a relation name
				$this->buildJoinTree($parent,$value);
			else if(is_string($key) && is_array($value))
				$element=$this->buildJoinTree($parent,$key,$value);
		}
	}
}


/**
 * CJoinElement represents a tree node in the join tree created by {@link CActiveFinder}.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CActiveFinder.php 1563 2009-12-07 20:06:34Z qiang.xue $
 * @package system.db.ar
 * @since 1.0
 */
class CJoinElement
{
	/**
	 * @var integer the unique ID of this tree node
	 */
	public $id;
	/**
	 * @var CActiveRelation the relation represented by this tree node
	 */
	public $relation;
	/**
	 * @var CActiveRecord the model associated with this tree node
	 */
	public $model;
	/**
	 * @var array list of active records found by the queries. They are indexed by primary key values.
	 */
	public $records=array();
	/**
	 * @var array list of child join elements
	 */
	public $children=array();
	/**
	 * @var array list of stat elements
	 * @since 1.0.4
	 */
	public $stats=array();
	/**
	 * @var string table alias for this join element
	 */
	public $tableAlias;

	private $_finder;
	private $_builder;
	private $_parent;
	private $_pkAlias;  				// string or name=>alias
	private $_columnAliases=array();	// name=>alias
	private $_joined=false;
	private $_table;
	private $_related=array();			// PK, relation name, related PK => true

	/**
	 * Constructor.
	 * @param CActiveFinder the finder
	 * @param mixed the relation (if the third parameter is not null)
	 * or the model (if the third parameter is null) associated with this tree node.
	 * @param CJoinElement the parent tree node
	 * @param integer the ID of this tree node that is unique among all the tree nodes
	 */
	public function __construct($finder,$relation,$parent=null,$id=0)
	{
		$this->_finder=$finder;
		$this->id=$id;
		if($parent!==null)
		{
			$this->relation=$relation;
			$this->_parent=$parent;
			$this->_builder=$parent->_builder;
			$this->tableAlias=$relation->alias===null?'t'.$id:$relation->alias;
			$this->model=CActiveRecord::model($relation->className);
			$this->_table=$this->model->getTableSchema();
		}
		else  // root element, the first parameter is the model.
		{
			$this->model=$relation;
			$this->_builder=$relation->getCommandBuilder();
			$this->_table=$relation->getTableSchema();
		}

		// set up column aliases, such as t1_c2
		$table=$this->_table;
		if($this->model->getDbConnection()->getDriverName()==='oci')  // Issue 482
			$prefix='T'.$id.'_C';
		else
			$prefix='t'.$id.'_c';
		foreach($table->getColumnNames() as $key=>$name)
		{
			$alias=$prefix.$key;
			$this->_columnAliases[$name]=$alias;
			if($table->primaryKey===$name)
				$this->_pkAlias=$alias;
			else if(is_array($table->primaryKey) && in_array($name,$table->primaryKey))
				$this->_pkAlias[$name]=$alias;
		}
	}

	/**
	 * Performs the recursive finding with the criteria.
	 * @param CDbCriteria the query criteria
	 */
	public function find($criteria=null)
	{
		if($this->_parent===null) // root element
		{
			$query=new CJoinQuery($this,$criteria);
			if($this->_finder->baseLimited===null)
				$this->_finder->baseLimited=($criteria->offset>=0 || $criteria->limit>=0);
			$this->buildQuery($query);
			$this->runQuery($query);
		}
		else if(!$this->_joined && !empty($this->_parent->records)) // not joined before
		{
			$query=new CJoinQuery($this->_parent);
			$this->_joined=true;
			$query->join($this);
			$this->buildQuery($query);
			$this->_parent->runQuery($query);
		}

		foreach($this->children as $child) // find recursively
			$child->find();

		foreach($this->stats as $stat)
			$stat->query();
	}

	/**
	 * Performs lazy find with the specified base record.
	 * @param CActiveRecord the active record whose related object is to be fetched.
	 */
	public function lazyFind($baseRecord)
	{
		if(is_string($this->_table->primaryKey))
			$this->records[$baseRecord->{$this->_table->primaryKey}]=$baseRecord;
		else
		{
			$pk=array();
			foreach($this->_table->primaryKey as $name)
				$pk[$name]=$baseRecord->$name;
			$this->records[serialize($pk)]=$baseRecord;
		}

		foreach($this->stats as $stat)
			$stat->query();

		if(empty($this->children))
			return;

		$child=reset($this->children);
		$query=new CJoinQuery($child);
		$query->selects=array();
		$query->selects[]=$child->getColumnSelect($child->relation->select);
		$query->conditions=array();
		$query->conditions[]=$child->getCondition();
		if(!empty($child->relation->on))
			$query->conditions[]=str_replace($child->relation->aliasToken.'.', $child->tableAlias.'.', $child->relation->on);
		$query->groups[]=$child->getGroupBy();
		$query->havings[]=$child->getHaving();
		$query->orders[]=$child->getOrder();
		if(is_array($child->relation->params))
			$query->params=$child->relation->params;
		$query->elements[$child->id]=true;
		if($child->relation instanceof CHasManyRelation)
		{
			$query->limit=$child->relation->limit;
			$query->offset=$child->relation->offset;
		}

		$child->applyLazyCondition($query,$baseRecord);

		$this->_joined=true;
		$child->_joined=true;

		$child->buildQuery($query);
		$child->runQuery($query);
		foreach($child->children as $c)
			$c->find();

		if(empty($child->records))
			return;
		if($child->relation instanceof CHasOneRelation || $child->relation instanceof CBelongsToRelation)
			$baseRecord->addRelatedRecord($child->relation->name,reset($child->records),false);
		else // has_many and many_many
		{
			foreach($child->records as $record)
			{
				if($child->relation->index!==null)
					$index=$record->{$child->relation->index};
				else
					$index=true;
				$baseRecord->addRelatedRecord($child->relation->name,$record,$index);
			}
		}
	}

	private function applyLazyCondition($query,$record)
	{
		$schema=$this->_builder->getSchema();
		$parent=$this->_parent;
		if($this->relation instanceof CManyManyRelation)
		{
			if(!preg_match('/^\s*(.*?)\((.*)\)\s*$/',$this->relation->foreignKey,$matches))
				throw new CDbException(Yii::t('yii','The relation "{relation}" in active record class "{class}" is specified with an invalid foreign key. The format of the foreign key must be "joinTable(fk1,fk2,...)".',
					array('{class}'=>get_class($parent->model),'{relation}'=>$this->relation->name)));

			if(($joinTable=$schema->getTable($matches[1]))===null)
				throw new CDbException(Yii::t('yii','The relation "{relation}" in active record class "{class}" is not specified correctly: the join table "{joinTable}" given in the foreign key cannot be found in the database.',
					array('{class}'=>get_class($parent->model), '{relation}'=>$this->relation->name, '{joinTable}'=>$matches[1])));
			$fks=preg_split('/[\s,]+/',$matches[2],-1,PREG_SPLIT_NO_EMPTY);


			$joinAlias=$this->relation->name.'_'.$this->tableAlias;
			$parentCondition=array();
			$childCondition=array();
			$count=0;
			$params=array();
			foreach($fks as $i=>$fk)
			{
				if(isset($joinTable->foreignKeys[$fk]))  // FK defined
				{
					list($tableName,$pk)=$joinTable->foreignKeys[$fk];
					if(!isset($parentCondition[$pk]) && $schema->compareTableNames($parent->_table->rawName,$tableName))
					{
						$parentCondition[$pk]=$joinAlias.'.'.$schema->quoteColumnName($fk).'=:ypl'.$count;
						$params[':ypl'.$count]=$record->$pk;
						$count++;
					}
					else if(!isset($childCondition[$pk]) && $schema->compareTableNames($this->_table->rawName,$tableName))
						$childCondition[$pk]=$this->getColumnPrefix().$schema->quoteColumnName($pk).'='.$joinAlias.'.'.$schema->quoteColumnName($fk);
					else
						throw new CDbException(Yii::t('yii','The relation "{relation}" in active record class "{class}" is specified with an invalid foreign key "{key}". The foreign key does not point to either joining table.',
							array('{class}'=>get_class($parent->model), '{relation}'=>$this->relation->name, '{key}'=>$fk)));
				}
				else // FK constraints not defined
				{
					if($i<count($parent->_table->primaryKey))
					{
						$pk=is_array($parent->_table->primaryKey) ? $parent->_table->primaryKey[$i] : $parent->_table->primaryKey;
						$parentCondition[$pk]=$joinAlias.'.'.$schema->quoteColumnName($fk).'=:ypl'.$count;
						$params[':ypl'.$count]=$record->$pk;
						$count++;
					}
					else
					{
						$j=$i-count($parent->_table->primaryKey);
						$pk=is_array($this->_table->primaryKey) ? $this->_table->primaryKey[$j] : $this->_table->primaryKey;
						$childCondition[$pk]=$this->getColumnPrefix().$schema->quoteColumnName($pk).'='.$joinAlias.'.'.$schema->quoteColumnName($fk);
					}
				}
			}
			if($parentCondition!==array() && $childCondition!==array())
			{
				$join='INNER JOIN '.$joinTable->rawName.' '.$joinAlias.' ON ';
				$join.='('.implode(') AND (',$parentCondition).') AND ('.implode(') AND (',$childCondition).')';
				if(!empty($this->relation->on))
					$join.=' AND ('.str_replace($this->relation->aliasToken.'.', $this->tableAlias.'.', $this->relation->on).')';
				$query->joins[]=$join;
				foreach($params as $name=>$value)
					$query->params[$name]=$value;
			}
			else
				throw new CDbException(Yii::t('yii','The relation "{relation}" in active record class "{class}" is specified with an incomplete foreign key. The foreign key must consist of columns referencing both joining tables.',
					array('{class}'=>get_class($parent->model), '{relation}'=>$this->relation->name)));
		}
		else
		{
			$fks=preg_split('/[\s,]+/',$this->relation->foreignKey,-1,PREG_SPLIT_NO_EMPTY);
			$params=array();
			foreach($fks as $i=>$fk)
			{
				if($this->relation instanceof CBelongsToRelation)
				{
					if(isset($parent->_table->foreignKeys[$fk]))  // FK defined
						$pk=$parent->_table->foreignKeys[$fk][1];
					else if(is_array($this->_table->primaryKey)) // composite PK
						$pk=$this->_table->primaryKey[$i];
					else
						$pk=$this->_table->primaryKey;
					$params[$pk]=$record->$fk;
				}
				else
				{
					if(isset($this->_table->foreignKeys[$fk]))  // FK defined
						$pk=$this->_table->foreignKeys[$fk][1];
					else if(is_array($parent->_table->primaryKey)) // composite PK
						$pk=$parent->_table->primaryKey[$i];
					else
						$pk=$parent->_table->primaryKey;
					$params[$fk]=$record->$pk;
				}
			}
			$prefix=$this->getColumnPrefix();
			$count=0;
			foreach($params as $name=>$value)
			{
				$query->conditions[]=$prefix.$schema->quoteColumnName($name).'=:ypl'.$count;
				$query->params[':ypl'.$count]=$value;
				$count++;
			}
		}
	}

	/**
	 * Performs the eager loading with the base records ready.
	 * @param mixed the available base record(s).
	 */
	public function findWithBase($baseRecords)
	{
		if(!is_array($baseRecords))
			$baseRecords=array($baseRecords);
		if(is_string($this->_table->primaryKey))
		{
			foreach($baseRecords as $baseRecord)
				$this->records[$baseRecord->{$this->_table->primaryKey}]=$baseRecord;
		}
		else
		{
			foreach($baseRecords as $baseRecord)
			{
				$pk=array();
				foreach($this->_table->primaryKey as $name)
					$pk[$name]=$baseRecord->$name;
				$this->records[serialize($pk)]=$baseRecord;
			}
		}

		$query=new CJoinQuery($this);
		$this->buildQuery($query);
		if(count($query->joins)>1)
			$this->runQuery($query);
		foreach($this->children as $child)
			$child->find();

		foreach($this->stats as $stat)
			$stat->query();
	}

	/**
	 * Count the number of primary records returned by the join statement.
	 * @param CDbCriteria the query criteria
	 * @return integer number of primary records.
	 * @since 1.0.3
	 */
	public function count($criteria=null)
	{
		$query=new CJoinQuery($this,$criteria);
		// ensure only one big join statement is used
		$this->_finder->baseLimited=false;
		$this->_finder->joinAll=true;
		$this->buildQuery($query);

		$select=is_array($criteria->select) ? implode(',',$criteria->select) : $criteria->select;
		if($select!=='*' && !strncasecmp($select,'count',5))
			$query->selects=array($select);
		else if(is_string($this->_table->primaryKey))
		{
			$prefix=$this->getColumnPrefix();
			$schema=$this->_builder->getSchema();
			$column=$prefix.$schema->quoteColumnName($this->_table->primaryKey);
			$query->selects=array("COUNT(DISTINCT $column)");
		}
		else
			$query->selects=array("COUNT(*)");

		$query->orders=$query->groups=$query->havings=array();
		$query->limit=$query->offset=-1;
		$command=$query->createCommand($this->_builder);
		return $command->queryScalar();
	}

	/**
	 * Calls {@link CActiveRecord::beforeFind}.
	 * @since 1.0.11
	 */
	public function beforeFind()
	{
		$this->model->beforeFindInternal();
	}

	/**
	 * Calls {@link CActiveRecord::afterFind} of all the records.
	 * @since 1.0.3
	 */
	public function afterFind()
	{
		foreach($this->records as $record)
			$record->afterFindInternal();
		foreach($this->children as $child)
			$child->afterFind();
	}

	/**
	 * Builds the join query with all descendant HAS_ONE and BELONGS_TO nodes.
	 * @param CJoinQuery the query being built up
	 */
	public function buildQuery($query)
	{
		foreach($this->children as $child)
		{
			if($child->relation instanceof CHasOneRelation || $child->relation instanceof CBelongsToRelation
				|| $child->relation->together || ($this->_finder->joinAll && !$this->_finder->baseLimited))
			{
				$child->_joined=true;
				$query->join($child);
				$child->buildQuery($query);
			}
		}
	}

	/**
	 * Executes the join query and populates the query results.
	 * @param CJoinQuery the query to be executed.
	 */
	public function runQuery($query)
	{
		$command=$query->createCommand($this->_builder);
		foreach($command->queryAll() as $row)
			$this->populateRecord($query,$row);
	}

	/**
	 * Populates the active records with the query data.
	 * @param CJoinQuery the query executed
	 * @param array a row of data
	 * @return CActiveRecord the populated record
	 */
	private function populateRecord($query,$row)
	{
		// determine the primary key value
		if(is_string($this->_pkAlias))  // single key
		{
			if(isset($row[$this->_pkAlias]))
				$pk=$row[$this->_pkAlias];
			else	// no matching related objects
				return null;
		}
		else // is_array, composite key
		{
			$pk=array();
			foreach($this->_pkAlias as $name=>$alias)
			{
				if(isset($row[$alias]))
					$pk[$name]=$row[$alias];
				else	// no matching related objects
					return null;
			}
			$pk=serialize($pk);
		}

		// retrieve or populate the record according to the primary key value
		if(isset($this->records[$pk]))
			$record=$this->records[$pk];
		else
		{
			$attributes=array();
			$aliases=array_flip($this->_columnAliases);
			foreach($row as $alias=>$value)
			{
				if(isset($aliases[$alias]))
					$attributes[$aliases[$alias]]=$value;
			}
			$record=$this->model->populateRecord($attributes,false);
			foreach($this->children as $child)
				$record->addRelatedRecord($child->relation->name,null,$child->relation instanceof CHasManyRelation);
			$this->records[$pk]=$record;
		}

		// populate child records recursively
		foreach($this->children as $child)
		{
			if(!isset($query->elements[$child->id]))
				continue;
			$childRecord=$child->populateRecord($query,$row);
			if($child->relation instanceof CHasOneRelation || $child->relation instanceof CBelongsToRelation)
				$record->addRelatedRecord($child->relation->name,$childRecord,false);
			else // has_many and many_many
			{
				// need to double check to avoid adding duplicated related objects
				if($childRecord instanceof CActiveRecord)
					$fpk=serialize($childRecord->getPrimaryKey());
				else
					$fpk=0;
				if(!isset($this->_related[$pk][$child->relation->name][$fpk]))
				{
					if($childRecord instanceof CActiveRecord && $child->relation->index!==null)
						$index=$childRecord->{$child->relation->index};
					else
						$index=true;
					$record->addRelatedRecord($child->relation->name,$childRecord,$index);
					$this->_related[$pk][$child->relation->name][$fpk]=true;
				}
			}
		}

		return $record;
	}

	/**
	 * @return string the table name and the table alias (if any). This can be used directly in SQL query without escaping.
	 */
	public function getTableNameWithAlias()
	{
		if($this->tableAlias!==null)
			return $this->_table->rawName . ' ' . $this->tableAlias;
		else
			return $this->_table->rawName;
	}

	/**
	 * Generates the list of columns to be selected.
	 * Columns will be properly aliased and primary keys will be added to selection if they are not specified.
	 * @param mixed columns to be selected. Defaults to '*', indicating all columns.
	 * @return string the column selection
	 */
	public function getColumnSelect($select='*')
	{
		$schema=$this->_builder->getSchema();
		$prefix=$this->getColumnPrefix();
		$columns=array();
		if($select==='*')
		{
			foreach($this->_table->getColumnNames() as $name)
				$columns[]=$prefix.$schema->quoteColumnName($name).' AS '.$schema->quoteColumnName($this->_columnAliases[$name]);
		}
		else
		{
			if(is_string($select))
				$select=explode(',',$select);
			$selected=array();
			foreach($select as $name)
			{
				$name=trim($name);
				$matches=array();
				if(($pos=strrpos($name,'.'))!==false)
					$key=substr($name,$pos+1);
				else
					$key=$name;
				if(isset($this->_columnAliases[$key]))  // simple column names
				{
					$columns[]=$prefix.$schema->quoteColumnName($key).' AS '.$schema->quoteColumnName($this->_columnAliases[$key]);
					$selected[$this->_columnAliases[$key]]=1;
				}
				else if(preg_match('/^(.*?)\s+AS\s+(\w+)$/i',$name,$matches)) // if the column is already aliased
				{
					$alias=$matches[2];
					if(!isset($this->_columnAliases[$alias]) || $this->_columnAliases[$alias]!==$alias)
					{
						$this->_columnAliases[$alias]=$alias;
						$columns[]=$name;
						$selected[$alias]=1;
					}
				}
				else
					throw new CDbException(Yii::t('yii','Active record "{class}" is trying to select an invalid column "{column}". Note, the column must exist in the table or be an expression with alias.',
						array('{class}'=>get_class($this->model), '{column}'=>$name)));
			}
			// add primary key selection if they are not selected
			if(is_string($this->_pkAlias) && !isset($selected[$this->_pkAlias]))
				$columns[]=$prefix.$schema->quoteColumnName($this->_table->primaryKey).' AS '.$schema->quoteColumnName($this->_pkAlias);
			else if(is_array($this->_pkAlias))
			{
				foreach($this->_table->primaryKey as $name)
					if(!isset($selected[$name]))
						$columns[]=$prefix.$schema->quoteColumnName($name).' AS '.$schema->quoteColumnName($this->_pkAlias[$name]);
			}
		}

		$select=implode(', ',$columns);
		if($this->relation!==null)
			return str_replace($this->relation->aliasToken.'.', $prefix, $select);
		else
			return $select;
	}

	/**
	 * @return string the primary key selection
	 */
	public function getPrimaryKeySelect()
	{
		$schema=$this->_builder->getSchema();
		$prefix=$this->getColumnPrefix();
		$columns=array();
		if(is_string($this->_pkAlias))
			$columns[]=$prefix.$schema->quoteColumnName($this->_table->primaryKey).' AS '.$schema->quoteColumnName($this->_pkAlias);
		else if(is_array($this->_pkAlias))
		{
			foreach($this->_pkAlias as $name=>$alias)
				$columns[]=$prefix.$schema->quoteColumnName($name).' AS '.$schema->quoteColumnName($alias);
		}
		return implode(', ',$columns);
	}

	/**
	 * @return string the condition that specifies only the rows with the selected primary key values.
	 */
	public function getPrimaryKeyRange()
	{
		if(empty($this->records))
			return '';
		$values=array_keys($this->records);
		if(is_array($this->_table->primaryKey))
		{
			foreach($values as &$value)
				$value=unserialize($value);
		}
		return $this->_builder->createInCondition($this->_table,$this->_table->primaryKey,$values,$this->getColumnPrefix());
	}

	/**
	 * @return string the WHERE clause. Column references are properly disambiguated.
	 */
	public function getCondition()
	{
		if($this->relation->condition!=='' && $this->tableAlias!==null)
			return str_replace($this->relation->aliasToken.'.', $this->tableAlias.'.', $this->relation->condition);
		else
			return $this->relation->condition;
	}

	/**
	 * @return string the ORDER BY clause. Column references are properly disambiguated.
	 */
	public function getOrder()
	{
		if($this->relation->order!=='' && $this->tableAlias!==null)
			return str_replace($this->relation->aliasToken.'.',$this->tableAlias.'.',$this->relation->order);
		else
			return $this->relation->order;
	}

	/**
	 * @return string the GROUP BY clause. Column references are properly disambiguated.
	 * @since 1.0.4
	 */
	public function getGroupBy()
	{
		if($this->relation->group!=='' && $this->tableAlias!==null)
			return str_replace($this->relation->aliasToken.'.', $this->tableAlias.'.', $this->relation->group);
		else
			return $this->relation->group;
	}

	/**
	 * @return string the HAVING clause. Column references are properly disambiguated.
	 * @since 1.0.4
	 */
	public function getHaving()
	{
		if($this->relation->having!=='' && $this->tableAlias!==null)
			return str_replace($this->relation->aliasToken.'.', $this->tableAlias.'.', $this->relation->having);
		else
			return $this->relation->having;
	}

	/**
	 * @return string the column prefix for column reference disambiguation
	 */
	public function getColumnPrefix()
	{
		if($this->tableAlias!==null)
			return $this->tableAlias.'.';
		else
			return $this->_table->rawName.'.';
	}

	/**
	 * @return string the join statement (this node joins with its parent)
	 */
	public function getJoinCondition()
	{
		$parent=$this->_parent;
		$relation=$this->relation;
		if($this->relation instanceof CManyManyRelation)
		{
			if(!preg_match('/^\s*(.*?)\((.*)\)\s*$/',$this->relation->foreignKey,$matches))
				throw new CDbException(Yii::t('yii','The relation "{relation}" in active record class "{class}" is specified with an invalid foreign key. The format of the foreign key must be "joinTable(fk1,fk2,...)".',
					array('{class}'=>get_class($parent->model),'{relation}'=>$this->relation->name)));

			$schema=$this->_builder->getSchema();
			if(($joinTable=$schema->getTable($matches[1]))===null)
				throw new CDbException(Yii::t('yii','The relation "{relation}" in active record class "{class}" is not specified correctly: the join table "{joinTable}" given in the foreign key cannot be found in the database.',
					array('{class}'=>get_class($parent->model), '{relation}'=>$this->relation->name, '{joinTable}'=>$matches[1])));
			$fks=preg_split('/[\s,]+/',$matches[2],-1,PREG_SPLIT_NO_EMPTY);

			return $this->joinManyMany($joinTable,$fks,$parent);
		}
		else
		{
			$fks=preg_split('/[\s,]+/',$relation->foreignKey,-1,PREG_SPLIT_NO_EMPTY);
			if($this->relation instanceof CBelongsToRelation)
			{
				$pke=$this;
				$fke=$parent;
			}
			else
			{
				$pke=$parent;
				$fke=$this;
			}
			return $this->joinOneMany($fke,$fks,$pke,$parent);
		}
	}

	/**
	 * Generates the join statement for one-many relationship.
	 * This works for HAS_ONE, HAS_MANY and BELONGS_TO.
	 * @param CJoinElement the join element containing foreign keys
	 * @param array the foreign keys
	 * @param CJoinElement the join element containg primary keys
	 * @param CJoinElement the parent join element
	 * @return string the join statement
	 * @throws CDbException if a foreign key is invalid
	 */
	private function joinOneMany($fke,$fks,$pke,$parent)
	{
		$schema=$this->_builder->getSchema();
		$joins=array();
		foreach($fks as $i=>$fk)
		{
			if(!isset($fke->_table->columns[$fk]))
				throw new CDbException(Yii::t('yii','The relation "{relation}" in active record class "{class}" is specified with an invalid foreign key "{key}". There is no such column in the table "{table}".',
					array('{class}'=>get_class($parent->model), '{relation}'=>$this->relation->name, '{key}'=>$fk, '{table}'=>$fke->_table->name)));

			if(isset($fke->_table->foreignKeys[$fk]))
				$pk=$fke->_table->foreignKeys[$fk][1];
			else  // FK constraints undefined
			{
				if(is_array($pke->_table->primaryKey)) // composite PK
					$pk=$pke->_table->primaryKey[$i];
				else
					$pk=$pke->_table->primaryKey;
			}
			$joins[]=$fke->getColumnPrefix().$schema->quoteColumnName($fk) . '=' . $pke->getColumnPrefix().$schema->quoteColumnName($pk);
		}
		if(!empty($this->relation->on))
			$joins[]=str_replace($this->relation->aliasToken.'.', $this->tableAlias.'.', $this->relation->on);
		return $this->relation->joinType . ' ' . $this->getTableNameWithAlias() . ' ON (' . implode(') AND (',$joins).')';
	}

	/**
	 * Generates the join statement for many-many relationship.
	 * @param CDbTableSchema the join table
	 * @param array the foreign keys
	 * @param CJoinElement the parent join element
	 * @return string the join statement
	 * @throws CDbException if a foreign key is invalid
	 */
	private function joinManyMany($joinTable,$fks,$parent)
	{
		$schema=$this->_builder->getSchema();
		$joinAlias=$this->relation->name.'_'.$this->tableAlias;
		$parentCondition=array();
		$childCondition=array();
		foreach($fks as $i=>$fk)
		{
			if(!isset($joinTable->columns[$fk]))
				throw new CDbException(Yii::t('yii','The relation "{relation}" in active record class "{class}" is specified with an invalid foreign key "{key}". There is no such column in the table "{table}".',
					array('{class}'=>get_class($parent->model), '{relation}'=>$this->relation->name, '{key}'=>$fk, '{table}'=>$joinTable->name)));

			if(isset($joinTable->foreignKeys[$fk]))
			{
				list($tableName,$pk)=$joinTable->foreignKeys[$fk];
				if(!isset($parentCondition[$pk]) && $schema->compareTableNames($parent->_table->rawName,$tableName))
					$parentCondition[$pk]=$parent->getColumnPrefix().$schema->quoteColumnName($pk).'='.$joinAlias.'.'.$schema->quoteColumnName($fk);
				else if(!isset($childCondition[$pk]) && $schema->compareTableNames($this->_table->rawName,$tableName))
					$childCondition[$pk]=$this->getColumnPrefix().$schema->quoteColumnName($pk).'='.$joinAlias.'.'.$schema->quoteColumnName($fk);
				else
					throw new CDbException(Yii::t('yii','The relation "{relation}" in active record class "{class}" is specified with an invalid foreign key "{key}". The foreign key does not point to either joining table.',
						array('{class}'=>get_class($parent->model), '{relation}'=>$this->relation->name, '{key}'=>$fk)));
			}
			else // FK constraints not defined
			{
				if($i<count($parent->_table->primaryKey))
				{
					$pk=is_array($parent->_table->primaryKey) ? $parent->_table->primaryKey[$i] : $parent->_table->primaryKey;
					$parentCondition[$pk]=$parent->getColumnPrefix().$schema->quoteColumnName($pk).'='.$joinAlias.'.'.$schema->quoteColumnName($fk);
				}
				else
				{
					$j=$i-count($parent->_table->primaryKey);
					$pk=is_array($this->_table->primaryKey) ? $this->_table->primaryKey[$j] : $this->_table->primaryKey;
					$childCondition[$pk]=$this->getColumnPrefix().$schema->quoteColumnName($pk).'='.$joinAlias.'.'.$schema->quoteColumnName($fk);
				}
			}
		}
		if($parentCondition!==array() && $childCondition!==array())
		{
			$join=$this->relation->joinType.' '.$joinTable->rawName.' '.$joinAlias;
			$join.=' ON ('.implode(') AND (',$parentCondition).')';
			$join.=' '.$this->relation->joinType.' '.$this->getTableNameWithAlias();
			$join.=' ON ('.implode(') AND (',$childCondition).')';
			if(!empty($this->relation->on))
				$join.=' AND ('.str_replace($this->relation->aliasToken.'.', $this->tableAlias.'.', $this->relation->on).')';
			return $join;
		}
		else
			throw new CDbException(Yii::t('yii','The relation "{relation}" in active record class "{class}" is specified with an incomplete foreign key. The foreign key must consist of columns referencing both joining tables.',
				array('{class}'=>get_class($parent->model), '{relation}'=>$this->relation->name)));
	}
}


/**
 * CJoinQuery represents a JOIN SQL statement.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CActiveFinder.php 1563 2009-12-07 20:06:34Z qiang.xue $
 * @package system.db.ar
 * @since 1.0
 */
class CJoinQuery
{
	/**
	 * @var array list of column selections
	 */
	public $selects=array();
	/**
	 * @var boolean whether to select distinct result set
	 * @since 1.0.9
	 */
	public $distinct=false;
	/**
	 * @var array list of join statement
	 */
	public $joins=array();
	/**
	 * @var array list of WHERE clauses
	 */
	public $conditions=array();
	/**
	 * @var array list of ORDER BY clauses
	 */
	public $orders=array();
	/**
	 * @var array list of GROUP BY clauses
	 */
	public $groups=array();
	/**
	 * @var array list of HAVING clauses
	 */
	public $havings=array();
	/**
	 * @var integer row limit
	 */
	public $limit=-1;
	/**
	 * @var integer row offset
	 */
	public $offset=-1;
	/**
	 * @var array list of query parameters
	 */
	public $params=array();
	/**
	 * @var array list of join element IDs (id=>true)
	 */
	public $elements=array();

	/**
	 * Constructor.
	 * @param CJoinElement The root join tree.
	 * @param CDbCriteria the query criteria
	 */
	public function __construct($joinElement,$criteria=null)
	{
		if($criteria!==null)
		{
			$this->selects[]=$joinElement->getColumnSelect($criteria->select);
			$this->joins[]=$joinElement->getTableNameWithAlias();
			$this->joins[]=$criteria->join;
			$this->conditions[]=$criteria->condition;
			$this->orders[]=$criteria->order;
			$this->groups[]=$criteria->group;
			$this->havings[]=$criteria->having;
			$this->limit=$criteria->limit;
			$this->offset=$criteria->offset;
			$this->params=$criteria->params;
			if(!$this->distinct && $criteria->distinct)
				$this->distinct=true;
		}
		else
		{
			$this->selects[]=$joinElement->getPrimaryKeySelect();
			$this->joins[]=$joinElement->getTableNameWithAlias();
			$this->conditions[]=$joinElement->getPrimaryKeyRange();
		}
		$this->elements[$joinElement->id]=true;
	}

	/**
	 * Joins with another join element
	 * @param CJoinElement the element to be joined
	 */
	public function join($element)
	{
		$this->selects[]=$element->getColumnSelect($element->relation->select);
		$this->conditions[]=$element->getCondition();
		$this->orders[]=$element->getOrder();
		$this->joins[]=$element->getJoinCondition();
		$this->groups[]=$element->getGroupBy();
		$this->havings[]=$element->getHaving();

		if(is_array($element->relation->params))
		{
			if(is_array($this->params))
				$this->params=array_merge($this->params,$element->relation->params);
			else
				$this->params=$element->relation->params;
		}
		$this->elements[$element->id]=true;
	}

	/**
	 * Creates the SQL statement.
	 * @param CDbCommandBuilder the command builder
	 * @return string the SQL statement
	 */
	public function createCommand($builder)
	{
		$sql=($this->distinct ? 'SELECT DISTINCT ':'SELECT ') . implode(', ',$this->selects);
		$sql.=' FROM ' . implode(' ',$this->joins);

		$conditions=array();
		foreach($this->conditions as $condition)
			if($condition!=='')
				$conditions[]=$condition;
		if($conditions!==array())
			$sql.=' WHERE (' . implode(') AND (',$conditions).')';

		$groups=array();
		foreach($this->groups as $group)
			if($group!=='')
				$groups[]=$group;
		if($groups!==array())
			$sql.=' GROUP BY ' . implode(', ',$groups);

		$havings=array();
		foreach($this->havings as $having)
			if($having!=='')
				$havings[]=$having;
		if($havings!==array())
			$sql.=' HAVING (' . implode(') AND (',$havings).')';

		$orders=array();
		foreach($this->orders as $order)
			if($order!=='')
				$orders[]=$order;
		if($orders!==array())
			$sql.=' ORDER BY ' . implode(', ',$orders);

		$sql=$builder->applyLimit($sql,$this->limit,$this->offset);
		$command=$builder->getDbConnection()->createCommand($sql);
		$builder->bindValues($command,$this->params);
		return $command;
	}
}


/**
 * CStatElement represents STAT join element for {@link CActiveFinder}.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CActiveFinder.php 1563 2009-12-07 20:06:34Z qiang.xue $
 * @package system.db.ar
 * @since 1.0.4
 */
class CStatElement
{
	/**
	 * @var CActiveRelation the relation represented by this tree node
	 */
	public $relation;

	private $_finder;
	private $_parent;

	/**
	 * Constructor.
	 * @param CActiveFinder the finder
	 * @param CStatRelation the STAT relation
	 * @param CJoinElement the join element owning this STAT element
	 */
	public function __construct($finder,$relation,$parent)
	{
		$this->_finder=$finder;
		$this->_parent=$parent;
		$this->relation=$relation;
		$parent->stats[]=$this;
	}

	/**
	 * Performs the STAT query.
	 */
	public function query()
	{
		if(preg_match('/^\s*(.*?)\((.*)\)\s*$/',$this->relation->foreignKey,$matches))
			$this->queryManyMany($matches[1],$matches[2]);
		else
			$this->queryOneMany();
	}

	private function queryOneMany()
	{
		$relation=$this->relation;
		$model=CActiveRecord::model($relation->className);
		$builder=$model->getCommandBuilder();
		$schema=$builder->getSchema();
		$table=$model->getTableSchema();
		$parent=$this->_parent;
		$pkTable=$parent->model->getTableSchema();

		$fks=preg_split('/[\s,]+/',$relation->foreignKey,-1,PREG_SPLIT_NO_EMPTY);
		if(count($fks)!==count($pkTable->primaryKey))
			throw new CDbException(Yii::t('yii','The relation "{relation}" in active record class "{class}" is specified with an invalid foreign key. The columns in the key must match the primary keys of the table "{table}".',
						array('{class}'=>get_class($parent->model), '{relation}'=>$relation->name, '{table}'=>$pkTable->name)));

		// set up mapping between fk and pk columns
		$map=array();  // pk=>fk
		foreach($fks as $i=>$fk)
		{
			if(!isset($table->columns[$fk]))
				throw new CDbException(Yii::t('yii','The relation "{relation}" in active record class "{class}" is specified with an invalid foreign key "{key}". There is no such column in the table "{table}".',
					array('{class}'=>get_class($parent->model), '{relation}'=>$relation->name, '{key}'=>$fk, '{table}'=>$table->name)));

			if(isset($table->foreignKeys[$fk]))
			{
				list($tableName,$pk)=$table->foreignKeys[$fk];
				if($schema->compareTableNames($pkTable->rawName,$tableName))
					$map[$pk]=$fk;
				else
					throw new CDbException(Yii::t('yii','The relation "{relation}" in active record class "{class}" is specified with a foreign key "{key}" that does not point to the parent table "{table}".',
						array('{class}'=>get_class($parent->model), '{relation}'=>$relation->name, '{key}'=>$fk, '{table}'=>$pkTable->name)));
			}
			else  // FK constraints undefined
			{
				if(is_array($pkTable->primaryKey)) // composite PK
					$map[$pkTable->primaryKey[$i]]=$fk;
				else
					$map[$pkTable->primaryKey]=$fk;
			}
		}

		$records=$this->_parent->records;

		$where=empty($relation->condition)?'' : ' WHERE ('.$relation->condition.')';
		$group=empty($relation->group)?'' : ', '.$relation->group;
		$having=empty($relation->having)?'' : ' AND ('.$relation->having.')';
		$order=empty($relation->order)?'' : ' ORDER BY '.$relation->order;

		$c=$schema->quoteColumnName('c');
		$s=$schema->quoteColumnName('s');

		// generate and perform query
		if(count($fks)===1)  // single column FK
		{
			$col=$table->columns[$fks[0]]->rawName;
			$sql="SELECT $col AS $c, {$relation->select} AS $s FROM {$table->rawName}"
				.$where
				." GROUP BY $col".$group
				." HAVING ".$builder->createInCondition($table,$fks[0],array_keys($records))
				.$having.$order;
			$command=$builder->getDbConnection()->createCommand($sql);
			if(is_array($relation->params))
				$builder->bindValues($command,$relation->params);
			$stats=array();
			foreach($command->queryAll() as $row)
				$stats[$row['c']]=$row['s'];
		}
		else  // composite FK
		{
			$keys=array_keys($records);
			foreach($keys as &$key)
			{
				$key2=unserialize($key);
				$key=array();
				foreach($pkTable->primaryKey as $pk)
					$key[$map[$pk]]=$key2[$pk];
			}
			$cols=array();
			foreach($pkTable->primaryKey as $n=>$pk)
			{
				$name=$table->columns[$map[$pk]]->rawName;
				$cols[$name]=$name.' AS '.$schema->quoteColumnName('c'.$n);
			}
			$sql='SELECT '.implode(', ',$cols).", {$relation->select} AS $s FROM {$table->rawName}"
				.$where
				.' GROUP BY '.implode(', ',array_keys($cols)).$group
				.' HAVING '.$builder->createInCondition($table,$fks,$keys)
				.$having.$order;
			$command=$builder->getDbConnection()->createCommand($sql);
			if(is_array($relation->params))
				$builder->bindValues($command,$relation->params);
			$stats=array();
			foreach($command->queryAll() as $row)
			{
				$key=array();
				foreach($pkTable->primaryKey as $n=>$pk)
					$key[$pk]=$row['c'.$n];
				$stats[serialize($key)]=$row['s'];
			}
		}

		// populate the results into existing records
		foreach($records as $pk=>$record)
			$record->addRelatedRecord($relation->name,isset($stats[$pk])?$stats[$pk]:$relation->defaultValue,false);
	}

	private function queryManyMany($joinTableName,$keys)
	{
		$relation=$this->relation;
		$model=CActiveRecord::model($relation->className);
		$table=$model->getTableSchema();
		$builder=$model->getCommandBuilder();
		$schema=$builder->getSchema();
		$pkTable=$this->_parent->model->getTableSchema();

		if(($joinTable=$builder->getSchema()->getTable($joinTableName))===null)
			throw new CDbException(Yii::t('yii','The relation "{relation}" in active record class "{class}" is not specified correctly. The join table "{joinTable}" given in the foreign key cannot be found in the database.',
				array('{class}'=>get_class($this->_parent->model), '{relation}'=>$relation->name, '{joinTable}'=>$joinTableName)));

		$fks=preg_split('/[\s,]+/',$keys,-1,PREG_SPLIT_NO_EMPTY);
		if(count($fks)!==count($table->primaryKey)+count($pkTable->primaryKey))
			throw new CDbException(Yii::t('yii','The relation "{relation}" in active record class "{class}" is specified with an incomplete foreign key. The foreign key must consist of columns referencing both joining tables.',
				array('{class}'=>get_class($this->_parent->model), '{relation}'=>$relation->name)));

		$joinCondition=array();
		$map=array();
		foreach($fks as $i=>$fk)
		{
			if(!isset($joinTable->columns[$fk]))
				throw new CDbException(Yii::t('yii','The relation "{relation}" in active record class "{class}" is specified with an invalid foreign key "{key}". There is no such column in the table "{table}".',
					array('{class}'=>get_class($this->_parent->model), '{relation}'=>$relation->name, '{key}'=>$fk, '{table}'=>$joinTable->name)));

			if(isset($joinTable->foreignKeys[$fk]))
			{
				list($tableName,$pk)=$joinTable->foreignKeys[$fk];
				if(!isset($joinCondition[$pk]) && $schema->compareTableNames($table->rawName,$tableName))
					$joinCondition[$pk]=$table->rawName.'.'.$schema->quoteColumnName($pk).'='.$joinTable->rawName.'.'.$schema->quoteColumnName($fk);
				else if(!isset($map[$pk]) && $schema->compareTableNames($pkTable->rawName,$tableName))
					$map[$pk]=$fk;
				else
					throw new CDbException(Yii::t('yii','The relation "{relation}" in active record class "{class}" is specified with an invalid foreign key "{key}". The foreign key does not point to either joining table.',
						array('{class}'=>get_class($this->_parent->model), '{relation}'=>$relation->name, '{key}'=>$fk)));
			}
			else // FK constraints not defined
			{
				if($i<count($pkTable->primaryKey))
				{
					$pk=is_array($pkTable->primaryKey) ? $pkTable->primaryKey[$i] : $pkTable->primaryKey;
					$map[$pk]=$fk;
				}
				else
				{
					$j=$i-count($pkTable->primaryKey);
					$pk=is_array($table->primaryKey) ? $table->primaryKey[$j] : $table->primaryKey;
					$joinCondition[$pk]=$table->rawName.'.'.$schema->quoteColumnName($pk).'='.$joinTable->rawName.'.'.$schema->quoteColumnName($fk);
				}
			}
		}

		if($joinCondition===array() || $map===array())
			throw new CDbException(Yii::t('yii','The relation "{relation}" in active record class "{class}" is specified with an incomplete foreign key. The foreign key must consist of columns referencing both joining tables.',
				array('{class}'=>get_class($this->_parent->model), '{relation}'=>$relation->name)));

		$records=$this->_parent->records;

		$cols=array();
		foreach(is_string($pkTable->primaryKey)?array($pkTable->primaryKey):$pkTable->primaryKey as $n=>$pk)
		{
			$name=$joinTable->rawName.'.'.$schema->quoteColumnName($map[$pk]);
			$cols[$name]=$name.' AS '.$schema->quoteColumnName('c'.$n);
		}

		$keys=array_keys($records);
		if(is_array($pkTable->primaryKey))
		{
			foreach($keys as &$key)
			{
				$key2=unserialize($key);
				$key=array();
				foreach($pkTable->primaryKey as $pk)
					$key[$map[$pk]]=$key2[$pk];
			}
		}

		$where=empty($relation->condition)?'' : ' WHERE ('.$relation->condition.')';
		$group=empty($relation->group)?'' : ', '.$relation->group;
		$having=empty($relation->having)?'' : ' AND ('.$relation->having.')';
		$order=empty($relation->order)?'' : ' ORDER BY '.$relation->order;

		$sql='SELECT '.$this->relation->select.' AS '.$schema->quoteColumnName('s').', '.implode(', ',$cols)
			.' FROM '.$table->rawName.' INNER JOIN '.$joinTable->rawName
			.' ON ('.implode(') AND (',$joinCondition).')'
			.$where
			.' GROUP BY '.implode(', ',array_keys($cols)).$group
			.' HAVING ('.$builder->createInCondition($joinTable,$map,$keys).')'
			.$having.$order;

		$command=$builder->getDbConnection()->createCommand($sql);
		if(is_array($relation->params))
			$builder->bindValues($command,$relation->params);

		$stats=array();
		foreach($command->queryAll() as $row)
		{
			if(is_array($pkTable->primaryKey))
			{
				$key=array();
				foreach($pkTable->primaryKey as $n=>$k)
					$key[$k]=$row['c'.$n];
				$stats[serialize($key)]=$row['s'];
			}
			else
				$stats[$row['c0']]=$row['s'];
		}

		foreach($records as $pk=>$record)
			$record->addRelatedRecord($relation->name,isset($stats[$pk])?$stats[$pk]:$this->relation->defaultValue,false);
	}
}
