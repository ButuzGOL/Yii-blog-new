<?php
/**
 * CHtmlPurifier class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

require_once(Yii::getPathOfAlias('system.vendors.htmlpurifier').DIRECTORY_SEPARATOR.'HTMLPurifier.standalone.php');
HTMLPurifier_Bootstrap::registerAutoload();

/**
 * CHtmlPurifier is wrapper of {@link http://htmlpurifier.org HTML Purifier}.
 *
 * CHtmlPurifier removes all malicious code (better known as XSS) with a thoroughly audited,
 * secure yet permissive whitelist. It will also make sure the resulting code
 * is standard-compliant.
 *
 * CHtmlPurifier can be used as either a widget or a controller filter.
 *
 * Note: since HTML Purifier is a big package, its performance is not very good.
 * You should consider either caching the purification result or purifying the user input
 * before saving to database.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CHtmlPurifier.php 1074 2009-05-28 21:34:21Z qiang.xue $
 * @package system.web.widgets
 * @since 1.0
 */
class CHtmlPurifier extends COutputProcessor
{
	/**
	 * @var mixed the options to be passed to {@link http://htmlpurifier.org HTML Purifier}.
	 * This can be a HTMLPurifier_Config object,  an array of directives (Namespace.Directive => Value)
	 * or the filename of an ini file.
	 * @see http://htmlpurifier.org/live/configdoc/plain.html
	 */
	public $options=null;

	/**
	 * Processes the captured output.
     * This method purifies the output using {@link http://htmlpurifier.org HTML Purifier}.
	 * @param string the captured output to be processed
	 */
	public function processOutput($output)
	{
		$output=$this->purify($output);
		parent::processOutput($output);
	}

	/**
	 * Purifies the HTML content by removing malicious code.
	 * @param string the content to be purified.
	 * @return string the purified content
	 */
	public function purify($content)
	{
		$purifier=new HTMLPurifier($this->options);
		$purifier->config->set('Cache','SerializerPath',Yii::app()->getRuntimePath());
		return $purifier->purify($content);
	}
}
