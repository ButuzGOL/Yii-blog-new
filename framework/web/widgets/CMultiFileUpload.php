<?php
/**
 * CMultiFileUpload class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CMultiFileUpload generates a file input that can allow uploading multiple files at a time.
 *
 * This is based on the {@link http://www.fyneworks.com/jquery/multiple-file-upload/ jQuery Multi File Upload plugin}.
 * The uploaded file information can be accessed via $_FILES[widget-name], which gives an array of the uploaded
 * files. Note, you have to set the enclosing form's 'enctype' attribute to be 'multipart/form-data'.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CMultiFileUpload.php 970 2009-04-30 02:59:02Z qiang.xue $
 * @package system.web.widgets
 * @since 1.0
 */
class CMultiFileUpload extends CWidget
{
	/**
	 * @var string the input name.
	 */
	public $name;
	/**
	 * @var string the file types that are allowed (e.g. "gif|jpg"). Note, the server side still
	 * needs to check if the uploaded files have allowed types.
	 */
	public $accept;
	/**
	 * @var integer the maximum number of files that can be uploaded. If -1, it means no limits. Defaults to -1.
	 */
	public $max=-1;
	/**
	 * @var string the label for the remove button. Defaults to "Remove".
	 */
	public $remove;
	/**
	 * @var string message that is displayed when a file type is not allowed.
	 */
	public $denied;
	/**
	 * @var string message that is displayed when a file is selected.
	 */
	public $selected;
	/**
	 * @var string message that is displayed when a file appears twice.
	 */
	public $duplicate;
	/**
	 * @var array additional HTML attributes that will be rendered in the file upload tag.
	 */
	public $htmlOptions=array();


	/**
	 * Runs the widget.
	 * This method registers all needed client scripts and renders
	 * the multiple file uploader.
	 */
	public function run()
	{
		if($this->name!==null)
			$name=$this->name;
		else if(isset($this->htmlOptions['name']))
			$name=$this->htmlOptions['name'];
		else
			throw new CException(Yii::t('yii','CMultiFileUpload.name is required.'));
		if(substr($name,-2)!=='[]')
			$name.='[]';
		if(($id=$this->getId(false))===null)
		{
			if(isset($this->htmlOptions['id']))
				$id=$this->htmlOptions['id'];
			else
				$id=CHtml::getIdByName($name);
		}
		$this->htmlOptions['id']=$id;

		$this->registerClientScript();

		echo CHtml::fileField($name,'',$this->htmlOptions);
	}

	/**
	 * Registers the needed CSS and JavaScript.
	 * @since 1.0.1
	 */
	public function registerClientScript()
	{
		$id=$this->htmlOptions['id'];
		$mfOptions=array();
		if($this->accept!==null)
			$mfOptions['accept']=$this->accept;
		if($this->max>0)
			$mfOptions['max']=$this->max;
		$messages=array();
		foreach(array('remove','denied','selected','duplicate') as $messageName)
		{
			if($this->$messageName!==null)
				$messages[$messageName]=$this->$messageName;
		}
		if($messages!==array())
			$mfOptions['STRING']=$messages;
		$options=$mfOptions===array()?'':CJavaScript::encode($mfOptions);

		$cs=Yii::app()->getClientScript();
		$cs->registerCoreScript('multifile');
		$cs->registerScript('Yii.CMultiFileUpload#'.$id,"jQuery(\"#{$id}\").MultiFile({$options});");
	}
}
