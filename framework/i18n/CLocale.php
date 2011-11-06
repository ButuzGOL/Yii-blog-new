<?php
/**
 * CLocale class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CLocale represents the data relevant to a locale.
 *
 * The data includes the number formatting information and date formatting information.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CLocale.php 1326 2009-08-14 15:06:15Z qiang.xue $
 * @package system.i18n
 * @since 1.0
 */
class CLocale extends CComponent
{
	private $_id;
	private $_data;
	private $_dateFormatter;
	private $_numberFormatter;

	/**
	 * Returns the instance of the specified locale.
	 * Since the constructor of CLocale is protected, you can only use
	 * this method to obtain an instance of the specified locale.
	 * @param string the locale ID (e.g. en_US)
	 * @return CLocale the locale instance
	 */
	public static function getInstance($id)
	{
		static $locales=array();
		if(isset($locales[$id]))
			return $locales[$id];
		else
			return $locales[$id]=new CLocale($id);
	}

	/**
	 * @return array IDs of the locales which the framework can recognize
	 */
	public static function getLocaleIDs()
	{
		static $locales;
		if($locales===null)
		{
			$locales=array();
			$dataPath=dirname(__FILE__).DIRECTORY_SEPARATOR.'data';
			$folder=@opendir($dataPath);
			while($file=@readdir($folder))
			{
				$fullPath=$dataPath.DIRECTORY_SEPARATOR.$file;
				if(substr($file,-4)==='.php' && is_file($fullPath))
					$locales[]=substr($file,0,-4);
			}
			closedir($folder);
			sort($locales);
		}
		return $locales;
	}

	/**
	 * Constructor.
	 * Since the constructor is protected, please use {@link getInstance}
	 * to obtain an instance of the specified locale.
	 * @param string the locale ID (e.g. en_US)
	 */
	protected function __construct($id)
	{
		$this->_id=self::getCanonicalID($id);
		$dataFile=dirname(__FILE__).DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.$this->_id.'.php';
		if(is_file($dataFile))
			$this->_data=require($dataFile);
		else
			throw new CException(Yii::t('yii','Unrecognized locale "{locale}".',array('{locale}'=>$id)));
	}

	/**
	 * Converts a locale ID to its canonical form.
	 * In canonical form, a locale ID consists of only underscores and lower-case letters.
	 * @param string the locale ID to be converted
	 * @return string the locale ID in canonical form
	 */
	public static function getCanonicalID($id)
	{
		return strtolower(str_replace('-','_',$id));
	}

	/**
	 * @return string the locale ID (in canonical form)
	 */
	public function getId()
	{
		return $this->_id;
	}

	/**
	 * @return CNumberFormatter the number formatter for this locale
	 */
	public function getNumberFormatter()
	{
		if($this->_numberFormatter===null)
			$this->_numberFormatter=new CNumberFormatter($this);
		return $this->_numberFormatter;
	}

	/**
	 * @return CDateFormatter the date formatter for this locale
	 */
	public function getDateFormatter()
	{
		if($this->_dateFormatter===null)
			$this->_dateFormatter=new CDateFormatter($this);
		return $this->_dateFormatter;
	}

	/**
	 * @param string 3-letter ISO 4217 code. For example, the code "USD" represents the US Dollar and "EUR" represents the Euro currency.
	 * @return string the localized currency symbol. Null if the symbol does not exist.
	 */
	public function getCurrencySymbol($currency)
	{
		return isset($this->_data['currencySymbols'][$currency]) ? $this->_data['currencySymbols'][$currency] : null;
	}

	/**
	 * @param string symbol name
	 * @return string symbol
	 */
	public function getNumberSymbol($name)
	{
		return isset($this->_data['numberSymbols'][$name]) ? $this->_data['numberSymbols'][$name] : null;
	}

	/**
	 * @return string the decimal format
	 */
	public function getDecimalFormat()
	{
		return $this->_data['decimalFormat'];
	}

	/**
	 * @return string the currency format
	 */
	public function getCurrencyFormat()
	{
		return $this->_data['currencyFormat'];
	}

	/**
	 * @return string the percent format
	 */
	public function getPercentFormat()
	{
		return $this->_data['percentFormat'];
	}

	/**
	 * @return string the scientific format
	 */
	public function getScientificFormat()
	{
		return $this->_data['scientificFormat'];
	}

	/**
	 * @param integer month (1-12)
	 * @param string month name width. It can be 'wide', 'abbreviated' or 'narrow'.
	 * @return string the month name
	 */
	public function getMonthName($month,$width='wide')
	{
		return $this->_data['monthNames'][$width][$month];
	}

	/**
	 * Returns the month names in the specified width.
	 * @param string month name width. It can be 'wide', 'abbreviated' or 'narrow'.
	 * @return array month names indexed by month values (1-12)
	 * @since 1.0.9
	 */
	public function getMonthNames($width='wide')
	{
		return $this->_data['monthNames'][$width];
	}

	/**
	 * @param integer weekday (0-6, 0 means Sunday)
	 * @param string weekday name width.  It can be 'wide', 'abbreviated' or 'narrow'.
	 * @return string the weekday name
	 */
	public function getWeekDayName($day,$width='wide')
	{
		return $this->_data['weekDayNames'][$width][$day];
	}

	/**
	 * Returns the week day names in the specified width.
	 * @param string weekday name width.  It can be 'wide', 'abbreviated' or 'narrow'.
	 * @return array the weekday names indexed by weekday values (0-6, 0 means Sunday, 1 Monday, etc.)
	 * @since 1.0.9
	 */
	public function getWeekDayNames($width='wide')
	{
		return $this->_data['weekDayNames'][$width];
	}

	/**
	 * @param integer era (0,1)
	 * @param string era name width.  It can be 'wide', 'abbreviated' or 'narrow'.
	 * @return string the era name
	 */
	public function getEraName($era,$width='wide')
	{
		return $this->_data['eraNames'][$width][$era];
	}

	/**
	 * @return string the AM name
	 */
	public function getAMName()
	{
		return $this->_data['amName'];
	}

	/**
	 * @return string the PM name
	 */
	public function getPMName()
	{
		return $this->_data['pmName'];
	}

	/**
	 * @param string date format width. It can be 'full', 'long', 'medium' or 'short'.
	 * @return string date format
	 */
	public function getDateFormat($width='medium')
	{
		return $this->_data['dateFormats'][$width];
	}

	/**
	 * @param string time format width. It can be 'full', 'long', 'medium' or 'short'.
	 * @return string date format
	 */
	public function getTimeFormat($width='medium')
	{
		return $this->_data['timeFormats'][$width];
	}

	/**
	 * @return string datetime format, i.e., the order of date and time.
	 */
	public function getDateTimeFormat()
	{
		return $this->_data['dateTimeFormat'];
	}
}