<?php
/**
 * COciSchema class file.
 *
 * @author Ricardo Grana <rickgrana@yahoo.com.br>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * COciSchema is the class for retrieving metadata information from an Oracle database.
 *
 * @author Ricardo Grana <rickgrana@yahoo.com.br>
 * @version $Id: COciSchema.php 1518 2009-11-10 22:36:11Z qiang.xue $
 * @package system.db.schema.oci
 * @since 1.0.5
 */
class COciSchema extends CDbSchema
{
	private $_defaultSchema = '';
	private $_sequences=array();

	/**
	 * Quotes a table name for use in a query.
	 * @param string table name
	 * @return string the properly quoted table name
	 */
	public function quoteTableName($name)
	{
		return $name;
	}

	/**
	 * Quotes a column name for use in a query.
	 * @param string column name
	 * @return string the properly quoted column name
	 */
	public function quoteColumnName($name)
	{
		return $name;
	}

	/**
	 * Creates a command builder for the database.
	 * This method may be overridden by child classes to create a DBMS-specific command builder.
	 * @return CDbCommandBuilder command builder instance
	 */
	protected function createCommandBuilder()
	{
		return new COciCommandBuilder($this);
	}

	/**
     * @param string default schema.
     */
    public function setDefaultSchema($schema)
    {
		$this->_defaultSchema=$schema;
    }

    /**
     * @return string default schema.
     */
    public function getDefaultSchema()
    {
		if (!strlen($this->_defaultSchema))
		{
			$this->setDefaultSchema(strtoupper($this->getDbConnection()->username));
		}

		return $this->_defaultSchema;
    }

    /**
     * @param string table name with optional schema name prefix, uses default schema name prefix is not provided.
     * @return array tuple as ($schemaName,$tableName)
     */
    protected function getSchemaTableName($table)
    {
		$table = strtoupper($table);
		if(count($parts= explode('.', str_replace('"','',$table))) > 1)
			return array($parts[0], $parts[1]);
		else
			return array($this->getDefaultSchema(),$parts[0]);
    }

	/**
	 * Creates a table instance representing the metadata for the named table.
	 * @return CDbTableSchema driver dependent table metadata.
	 */
	protected function createTable($name)
	{
		$table=new COciTableSchema;
		$this->resolveTableNames($table,$name);

		if(!$this->findColumns($table))
			return null;
		$this->findConstraints($table);

		return $table;
	}

	/**
	 * Generates various kinds of table names.
	 * @param COciTableSchema the table instance
	 * @param string the unquoted table name
	 */
	protected function resolveTableNames($table,$name)
	{
		$parts=explode('.',str_replace('"','',$name));
		if(isset($parts[1]))
		{
			$schemaName=$parts[0];
			$tableName=$parts[1];
		}
		else
		{
			$schemaName=$this->getDefaultSchema();
			$tableName=$parts[0];
		}

		$table->name=$tableName;
		$table->schemaName=$schemaName;
		if($schemaName===$this->getDefaultSchema())
			$table->rawName=$this->quoteTableName($tableName);
		else
			$table->rawName=$this->quoteTableName($schemaName).'.'.$this->quoteTableName($tableName);
	}

	/**
	 * Collects the table column metadata.
	 * @param COciTableSchema the table metadata
	 * @return boolean whether the table exists in the database
	 */
	protected function findColumns($table)
	{
		list($schemaName,$tableName) = $this->getSchemaTableName($table->name);

		$sql=<<<EOD
SELECT a.column_name, a.data_type ||
    case
        when data_precision is not null
            then '(' || a.data_precision ||
                    case when a.data_scale > 0 then ',' || a.data_scale else '' end
                || ')'
        when data_type = 'DATE' then ''
        else '(' || to_char(a.data_length) || ')'
    end as data_type,
    a.nullable, a.data_default,
    (   SELECT D.constraint_type
        FROM ALL_CONS_COLUMNS C
        inner join ALL_constraints D on D.OWNER = C.OWNER and D.constraint_name = C.constraint_name
        WHERE C.OWNER = B.OWNER
           and C.table_name = B.object_name
           and C.column_name = A.column_name
           and D.constraint_type = 'P') as Key
FROM ALL_TAB_COLUMNS A
inner join ALL_OBJECTS B ON b.owner = a.owner and ltrim(B.OBJECT_NAME) = ltrim(A.TABLE_NAME)
WHERE
    a.owner = '{$schemaName}'
	and (b.object_type = 'TABLE' or b.object_type = 'VIEW')
	and b.object_name = '{$tableName}'
ORDER by a.column_id
EOD;

		$command=$this->getDbConnection()->createCommand($sql);

		if(($columns=$command->queryAll())===array()){
			return false;
		}

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
				$table->sequenceName='';
			}
		}
		return true;
	}

	/**
	 * Creates a table column.
	 * @param array column metadata
	 * @return CDbColumnSchema normalized column metadata
	 */
	protected function createColumn($column)
	{
		$c=new COciColumnSchema;
		$c->name=$column['COLUMN_NAME'];
		$c->rawName=$this->quoteColumnName($c->name);
		$c->allowNull=$column['NULLABLE']==='Y';
		$c->isPrimaryKey=strpos($column['KEY'],'P')!==false;
		$c->isForeignKey=false;
		$c->init($column['DATA_TYPE'],$column['DATA_DEFAULT']);

		return $c;
	}

	/**
	 * Collects the primary and foreign key column details for the given table.
	 * @param COciTableSchema the table metadata
	 */
	protected function findConstraints($table)
	{
		$sql=<<<EOD
		SELECT D.constraint_type as CONSTRAINT_TYPE, C.COLUMN_NAME, C.position, D.r_constraint_name,
                E.table_name as table_ref, f.column_name as column_ref
        FROM ALL_CONS_COLUMNS C
        inner join ALL_constraints D on D.OWNER = C.OWNER and D.constraint_name = C.constraint_name
        left join ALL_constraints E on E.OWNER = D.r_OWNER and E.constraint_name = D.r_constraint_name
        left join ALL_cons_columns F on F.OWNER = E.OWNER and F.constraint_name = E.constraint_name and F.position = c.position
        WHERE C.OWNER = '{$table->schemaName}'
           and C.table_name = '{$table->name}'
           and D.constraint_type <> 'P'
        order by d.constraint_name, c.position
EOD;
		$command=$this->getDbConnection()->createCommand($sql);
		foreach($command->queryAll() as $row)
		{
			if($row['CONSTRAINT_TYPE']==='R')   // foreign key
			{
				$name = $row["COLUMN_NAME"];
				$table->foreignKeys[$name]=array($row["TABLE_REF"], $row["COLUMN_REF"]);
				if(isset($table->columns[$name]))
					$table->columns[$name]->isForeignKey=true;
			}

		}
	}


	/**
	 * Returns all table names in the database.
	 * @return array all table names in the database.
	 */
	protected function findTableNames($schema='')
	{
		if($schema==='')
		{
			$sql=<<<EOD
SELECT table_name, '{$schema}' as table_schema FROM user_tables
EOD;
			$command=$this->getDbConnection()->createCommand($sql);
		}
		else
		{
			$sql=<<<EOD
SELECT object_name as table_name, owner as table_schema FROM all_objects
WHERE object_type = 'TABLE' AND owner=:schema
EOD;
			$command=$this->getDbConnection()->createCommand($sql);
			$command->bindParam(':schema',$schema);
		}

		$rows=$command->queryAll();
		$names=array();
		foreach($rows as $row)
		{
			if($schema===$this->getDefaultSchema())
				$names[]=$row['table_name'];
			else
				$names[]=$row['schema_name'].'.'.$row['table_name'];
		}
		return $names;
	}
}
