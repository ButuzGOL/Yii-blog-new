<?php
/**
 * CDateTimeParser class file
 *
 * @author Wei Zhuo <weizhuo[at]gamil[dot]com>
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Tomasz Suchanek <tomasz[dot]suchanek[at]gmail[dot]com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CDateTimeParser converts a date/time string to a UNIX timestamp according to the specified pattern.
 *
 * The following pattern characters are recognized:
 * <pre>
 * Pattern |      Description
 * ----------------------------------------------------
 * d       | Day of month 1 to 31, no padding
 * dd      | Day of month 01 to 31, zero leading
 * M       | Month digit 1 to 12, no padding
 * MM      | Month digit 01 to 12, zero leading
 * yy      | 2 year digit, e.g., 96, 05
 * yyyy    | 4 year digit, e.g., 2005
 * h       | Hour in 0 to 23, no padding (since version 1.0.5)
 * hh      | Hour in 00 to 23, zero leading (since version 1.0.5)
 * H       | Hour in 0 to 23, no padding (since version 1.0.9)
 * HH      | Hour in 00 to 23, zero leading (since version 1.0.9)
 * m       | Minutes in 0 to 59, no padding (since version 1.0.5)
 * mm      | Minutes in 00 to 59, zero leading (since version 1.0.5)
 * s	   | Seconds in 0 to 59, no padding (since version 1.0.5)
 * ss      | Seconds in 00 to 59, zero leading (since version 1.0.5)
 * ----------------------------------------------------
 * </pre>
 * All other characters must appear in the date string at the corresponding positions.
 *
 * For example, to parse a date string '21/10/2008', use the following:
 * <pre>
 * $timestamp=CDateTimeParser::parse('21/10/2008','dd/MM/yyyy');
 * </pre>
 *
 * To format a timestamp to a date string, please use {@link CDateFormatter}.
 *
 * @author Wei Zhuo <weizhuo[at]gmail[dot]com>
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CDateTimeParser.php 1404 2009-09-09 07:51:20Z istvan.beregszaszi $
 * @package system.utils
 * @since 1.0
 */
class CDateTimeParser
{
	/**
	 * Converts a date string to a timestamp.
	 * @param string the date string to be parsed
	 * @param string the pattern that the date string is following
	 * @return integer timestamp for the date string. False if parsing fails.
	 */
	public static function parse($value,$pattern='MM/dd/yyyy')
	{
		$tokens=self::tokenize($pattern);
		$i=0;
		$n=strlen($value);
		foreach($tokens as $token)
		{
			switch($token)
			{
				case 'yyyy':
				{
					if(($year=self::parseInteger($value,$i,4,4))===false)
						return false;
					$i+=4;
					break;
				}
				case 'yy':
				{
					if(($year=self::parseInteger($value,$i,1,2))===false)
						return false;
					$i+=strlen($year);
					break;
				}
				case 'MM':
				{
					if(($month=self::parseInteger($value,$i,2,2))===false)
						return false;
					$i+=2;
					break;
				}
				case 'M':
				{
					if(($month=self::parseInteger($value,$i,1,2))===false)
						return false;
					$i+=strlen($month);
					break;
				}
				case 'dd':
				{
					if(($day=self::parseInteger($value,$i,2,2))===false)
						return false;
					$i+=2;
					break;
				}
				case 'd':
				{
					if(($day=self::parseInteger($value,$i,1,2))===false)
						return false;
					$i+=strlen($day);
					break;
				}
				case 'h':
				case 'H':
				{
					if(($hour=self::parseInteger($value,$i,1,2))===false)
						return false;
					$i+=strlen($hour);
					break;
				}
				case 'hh':
				case 'HH':
				{
					if(($hour=self::parseInteger($value,$i,2,2))===false)
						return false;
					$i+=2;
					break;
				}
				case 'm':
				{
					if(($minute=self::parseInteger($value,$i,1,2))===false)
						return false;
					$i+=strlen($minute);
					break;
				}
				case 'mm':
				{
					if(($minute=self::parseInteger($value,$i,2,2))===false)
						return false;
					$i+=2;
					break;
				}
				case 's':
				{
					if(($second=self::parseInteger($value,$i,1,2))===false)
						return false;
					$i+=strlen($second);
					break;
				}
				case 'ss':
				{
					if(($second=self::parseInteger($value,$i,2,2))===false)
						return false;
					$i+=2;
					break;
				}
				default:
				{
					$tn=strlen($token);
					if($i>=$n || substr($value,$i,$tn)!==$token)
						return false;
					$i+=$tn;
					break;
				}
			}
		}
		if($i<$n)
			return false;

		if(!isset($year))
			$year=date('Y');
		if(!isset($month))
			$month=date('n');
		if(!isset($day))
			$day=date('j');

		if(strlen($year)===2)
		{
			if($year>70)
				$year+=1900;
			else
				$year+=2000;
		}
		$year=(int)$year;
		$month=(int)$month;
		$day=(int)$day;

		if(!isset($hour) && !isset($minute) && !isset($second))
			$hour=$minute=$second=0;
		else
		{
			if(!isset($hour))
				$hour=date('H');
			if(!isset($minute))
				$minute=date('i');
			if(!isset($second))
				$second=date('s');
			$hour=(int)$hour;
			$minute=(int)$minute;
			$second=(int)$second;
		}

		if(CTimestamp::isValidDate($year,$month,$day) && CTimestamp::isValidTime($hour,$minute,$second))
			return CTimestamp::getTimestamp($hour,$minute,$second,$month,$day,$year);
		else
			return false;
	}

	private static function tokenize($pattern)
	{
		if(!($n=strlen($pattern)))
			return array();
		$tokens=array();
		for($c0=$pattern[0],$start=0,$i=1;$i<$n;++$i)
		{
			if(($c=$pattern[$i])!==$c0)
			{
				$tokens[]=substr($pattern,$start,$i-$start);
				$c0=$c;
				$start=$i;
			}
		}
		$tokens[]=substr($pattern,$start,$n-$start);
		return $tokens;
	}

	protected static function parseInteger($value,$offset,$minLength,$maxLength)
	{
		for($len=$maxLength;$len>=$minLength;--$len)
		{
			$v=substr($value,$offset,$len);
			if(ctype_digit($v))
				return $v;
		}
		return false;
	}
}
