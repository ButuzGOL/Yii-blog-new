<?php
/**
 * CCaptchaAction class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CCaptchaAction renders a CAPTCHA image.
 *
 * CCaptchaAction is used together with {@link CCaptcha} and {@link CCaptchaValidator}
 * to provide the {@link http://en.wikipedia.org/wiki/Captcha CAPTCHA} feature.
 *
 * You must configure properties of CCaptchaAction to customize the appearance of
 * the generated image.
 *
 * Note, CCaptchaAction requires PHP GD2 extension.
 *
 * Using CAPTCHA involves the following steps:
 * <ol>
 * <li>Override {@link CController::actions()} and register an action of class CCaptchaAction with ID 'captcha'.</li>
 * <li>In the form model, declare an attribute to store user-entered verification code, and declare the attribute
 * to be validated by the 'captcha' validator.</li>
 * <li>In the controller view, insert a {@link CCaptcha} widget in the form.</li>
 * </ol>
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CCaptchaAction.php 1441 2009-10-08 00:18:37Z qiang.xue $
 * @package system.web.widgets.captcha
 * @since 1.0
 */
class CCaptchaAction extends CAction
{
	/**
	 * The name of the GET parameter indicating whether the CAPTCHA image should be regenerated.
	 */
	const REFRESH_GET_VAR='refresh';
	/**
	 * Prefix to the session variable name used by the action.
	 */
	const SESSION_VAR_PREFIX='Yii.CCaptchaAction.';
	/**
	 * @var integer how many times should the same CAPTCHA be displayed. Defaults to 3.
	 */
	public $testLimit=3;
	/**
	 * @var integer the width of the generated CAPTCHA image. Defaults to 120.
	 */
	public $width=120;
	/**
	 * @var integer the height of the generated CAPTCHA image. Defaults to 50.
	 */
	public $height=50;
	/**
	 * @var integer padding around the text. Defaults to 2.
	 */
	public $padding=2;
	/**
	 * @var integer the background color. For example, 0x55FF00.
	 * Defaults to 0xFFFFFF, meaning white color.
	 */
	public $backColor=0xFFFFFF;
	/**
	 * @var integer the font color. For example, 0x55FF00. Defaults to 0x2040A0 (blue color).
	 */
	public $foreColor=0x2040A0;
	/**
	 * @var boolean whether to use transparent background. Defaults to false.
	 * @since 1.0.10
	 */
	public $transparent=false;
	/**
	 * @var integer the minimum length for randomly generated word. Defaults to 6.
	 */
	public $minLength=6;
	/**
	 * @var integer the maximum length for randomly generated word. Defaults to 7.
	 */
	public $maxLength=7;
	/**
	 * @var string the TrueType font file. Defaults to Duality.ttf which is provided
	 * with the Yii release.
	 */
	public $fontFile;

	/**
	 * Runs the action.
	 * If the GET parameter {@link wsdlVar} exists, the action will serve WSDL content;
	 * If not, the action will handle the remote method invocation.
	 */
	public function run()
	{
		if(isset($_GET[self::REFRESH_GET_VAR]))  // AJAX request for regenerating code
		{
			$code=$this->getVerifyCode(true);
			// we add a random 'v' parameter so that FireFox can refresh the image
			// when src attribute of image tag is changed
			echo $this->getController()->createUrl($this->getId(),array('v'=>rand(0,10000)));
		}
		else
		{
			$session=Yii::app()->session;
			$session->open();
			$name=$this->getSessionKey().'count';
			if($session[$name]===null || $session[$name]>=$this->testLimit)
				$regenerate=true;
			else
			{
				$session[$name]=$session[$name]+1;
				$regenerate=false;
			}

			$this->renderImage($this->getVerifyCode($regenerate));
			Yii::app()->end();
		}
	}

	/**
	 * Gets the verification code.
	 * @param string whether the verification code should be regenerated.
	 * @return string the verification code.
	 */
	public function getVerifyCode($regenerate=false)
	{
		$session=Yii::app()->session;
		$session->open();
		$name=$this->getSessionKey();
		if($session[$name]===null || $regenerate)
		{
			$session[$name]=$this->generateVerifyCode();
			$session[$name.'count']=1;
		}
		return $session[$name];
	}

	/**
	 * Validates the input to see if it matches the generated code.
	 * @param string user input
	 * @param boolean whether the comparison should be case-sensitive
	 * @return whether the input is valid
	 */
	public function validate($input,$caseSensitive)
	{
		$code=$this->getVerifyCode();
		return $caseSensitive?($input===$code):!strcasecmp($input,$code);
	}

	/**
	 * Generates a new verification code.
	 * @return string the generated verification code
	 */
	protected function generateVerifyCode()
	{
		if($this->minLength<3)
			$this->minLength=3;
		if($this->maxLength>20)
			$this->maxLength=20;
		if($this->minLength>$this->maxLength)
			$this->maxLength=$this->minLength;
		$length=rand($this->minLength,$this->maxLength);

		$letters='bcdfghjklmnpqrstvwxyz';
		$vowels='aeiou';
		$code='';
		for($i=0;$i<$length;++$i)
		{
			if($i%2 && rand(0,10)>2 || !($i%2) && rand(0,10)>9)
				$code.=$vowels[rand(0,4)];
			else
				$code.=$letters[rand(0,20)];
		}

		return $code;
	}

	/**
	 * Returns the session variable name used to store verification code.
	 * @return string the session variable name
	 */
	protected function getSessionKey()
	{
		return self::SESSION_VAR_PREFIX.Yii::app()->getId().'.'.$this->getController()->getUniqueId().'.'.$this->getId();
	}

	/**
	 * Renders the CAPTCHA image based on the code.
	 * @param string the verification code
	 * @return string image content
	 */
	protected function renderImage($code)
	{
		$image=imagecreatetruecolor($this->width,$this->height);

		$backColor=imagecolorallocate($image,
			(int)($this->backColor%0x1000000/0x10000),
			(int)($this->backColor%0x10000/0x100),
			$this->backColor%0x100);
        imagefilledrectangle($image,0,0,$this->width,$this->height,$backColor);
        imagecolordeallocate($image,$backColor);

        if($this->transparent)
			imagecolortransparent($image,$backColor);

		$foreColor=imagecolorallocate($image,
			(int)($this->foreColor%0x1000000/0x10000),
			(int)($this->foreColor%0x10000/0x100),
			$this->foreColor%0x100);

        if($this->fontFile===null)
        	$this->fontFile=dirname(__FILE__).'/Duality.ttf';

		$offset=2;
		$length=strlen($code);
		$box=imagettfbbox(30,0,$this->fontFile,$code);
		$w=$box[4]-$box[0]-$offset*($length-1);
		$h=$box[1]-$box[5];
		$scale=min(($this->width-$this->padding*2)/$w,($this->height-$this->padding*2)/$h);
		$x=10;
		$y=round($this->height*27/40);
		for($i=0;$i<$length;++$i)
		{
			$fontSize=(int)(rand(26,32)*$scale*0.8);
			$angle=rand(-10,10);
			$letter=$code[$i];
            $box=imagettftext($image,$fontSize,$angle,$x,$y,$foreColor,$this->fontFile,$letter);
            $x=$box[2]-$offset;
		}

		imagecolordeallocate($image,$foreColor);

		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Transfer-Encoding: binary');
		header("Content-type: image/png");
		imagepng($image);
		imagedestroy($image);
	}
}