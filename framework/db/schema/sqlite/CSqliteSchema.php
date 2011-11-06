<?php
/**
 * CSqliteSchema class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CSqliteSchema is the class for retrieving metadata information from a SQLite (2/3) database.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CSqliteSchema.php 487 2009-01-08 03:44:52Z qiang.xue $
 * @package system.db.schema.sqlite
 * @since 1.0
 */
class CSqliteSchema extends CDbSchema
{
	/**
	 * Returns all table names in the database.
	 * @param string the schema of the tables. This is not used for sqlite database.
	 * @return array all table names in the database.
	 * @since 1.0.2
	 */
	protected function findTableNames($schema='')
	{
		$sql="SELECT DISTINCT tbl_name FROM sqlite_master WHERE tbl_name<>'sqlite_sequence'";
		return $this->getDbConnection()->createCommand($sql)->queryColumn();
	}

	/**
	 * Creates a command builder for the database.
	 * @return CSqliteCommandBuilder command builder instance
	 */
	protected function createCommandBuilder()
	{
		return new CSqliteCommandBuilder($this);
	}

	/**
	 * Creates a table instance representing the metadata for the named table.
	 * @return CDbTableSchema driver dependent table metadata. Null if the table does not exist.
	 */
	protected function createTable($name)
	{
		$db=$this->getDbConnection();

		$table=new CDbTableSchema;
		$table->name=$name;
		$table->rawName=$this->quoteTableName($name);

		if($this->findColumns($table))
		{
			$this->findConstraints($table);
			return $table;
		}
		else
			return null;
	}

	/**
	 * Collects the table column metadata.
	 * @param CDbTableSchema the table metadata
	 * @return boolean whether the table exists in the database
	 */
	protected function findColumns($table)
	{
		$sql="PRAGMA table_info({$table->rawName})";
		$columns=$this->getDbConnection()->createCommand($sql)->queryAll();
		if(empty($columns))
			return false;

		foreach($columns as $column)
		{
			$c=$this->createColumn($column);
			$table->columns[$c->name]=$c;
			if($c->isPrimaryKey)
			{
				if($table->primaryKey===null)
					$table->primaryKey=$c->name;
				else if(is_string($table->primaryKey))
					$table->primaryKey=array($table->primaryKey,$c->name);
				else
					$table->primaryKey[]=$c->name;
			}
		}
		if(is_string($table->primaryKey) && !strncasecmp($table->columns[$table->primaryKey]->dbType,'int',3))
			$table->sequenceName='';

		return true;
	}

	/**
	 * Collects the foreign key column details for the given table.
	 * @param CDbTableSchema the table metadata
	 */
	protected function findConstraints($table)
	{
		$foreignKeys=array();
		$sql="PRAGMA foreign_key_list({$table->rawName})";
		$keys=$this->getDbConnection()->createCommand($sql)->queryAll();
		foreach($keys as $key)
		{
			$column=$table->columns[$key['from']];
			$column->isForeignKey=true;
			$foreignKeys[$key['from']]=array($key['table'],$key['to']);
		}
		$table->foreignKeys=$foreignKeys;
	}

	/**
	 * Creates a table column.
	 * @param array column metadata
	 * @return CDbColumnSchema normalized column metadata
	 */
	protected function createColumn($column)
	{
		$c=new CSqliteColumnSchema;
		$c->name=$column['name'];
		$c->rawName=$this->quoteColumnName($c->name);
		$c->allowNull=!$column['notnull'];
		$c->isPrimaryKey=$column['pk']!=0;
		$c->isForeignKey=false;
		$c->init(strtolower($column['type']),$column['dflt_value']);
		return $c;
	}
}
