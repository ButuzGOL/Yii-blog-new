<?php
/**
 * CExpressionDependency class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CExpressionDependency represents a dependency based on the result of a PHP expression.
 *
 * CExpressionDependency performs dependency checking based on the
 * result of a PHP {@link expression}.
 * The dependency is reported as unchanged if and only if the result is
 * the same as the one evaluated when storing the data to cache.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CExpressionDependency.php 1507 2009-11-02 13:49:59Z qiang.xue $
 * @package system.caching.dependencies
 * @since 1.0
 */
class CExpressionDependency extends CCacheDependency
{
	/**
	 * @var string the PHP expression whose result is used to determine the dependency.
	 * Starting from version 1.0.11, the expression can also be a valid PHP callback,
	 * including class method name (array(ClassName/Object, MethodName)),
	 * or anonymous function (PHP 5.3.0+). The function/method will be passed a single
	 * parameter which is the dependency object itself.
	 */
	public $expression;

	/**
	 * Constructor.
	 * @param string the PHP expression whose result is used to determine the dependency.
	 */
	public function __construct($expression='true')
	{
		$this->expression=$expression;
	}

	/**
	 * Generates the data needed to determine if dependency has been changed.
	 * This method returns the result of the PHP expression.
	 * @return mixed the data needed to determine if dependency has been changed.
	 */
	protected function generateDependentData()
	{
		if(!is_string($this->expression) && is_callable($this->expression))
			return call_user_func($this->expression, $this);
		else
			return @eval('return '.$this->expression.';');
	}
}
