<?php
/**
 * COciTableSchema class file.
 *
 * @author Ricardo Grana <rickgrana@yahoo.com.br>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * COciTableSchema represents the metadata for a Oracle table.
 *
 * @author Ricardo Grana <rickgrana@yahoo.com.br>
 * @version $Id: COciTableSchema.php 1518 2009-11-10 22:36:11Z qiang.xue $
 * @package system.db.schema.oci
 * @since 1.0.5
 */
class COciTableSchema extends CDbTableSchema
{
	/**
	 * @var string name of the schema (database) that this table belongs to.
	 * Defaults to null, meaning no schema (or the current database).
	 */
	public $schemaName;
}
