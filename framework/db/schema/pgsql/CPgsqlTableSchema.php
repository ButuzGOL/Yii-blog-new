<?php
/**
 * CPgsqlTable class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CPgsqlTable represents the metadata for a PostgreSQL table.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CPgsqlTableSchema.php 433 2008-12-30 22:59:17Z qiang.xue $
 * @package system.db.schema.pgsql
 * @since 1.0
 */
class CPgsqlTableSchema extends CDbTableSchema
{
	/**
	 * @var string name of the schema that this table belongs to.
	 */
	public $schemaName;
}
