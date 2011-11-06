<?php
/**
 * YiiBase class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 * @version $Id: YiiBase.php 1578 2009-12-13 04:37:05Z qiang.xue $
 * @package system
 * @since 1.0
 */

/**
 * Gets the application start timestamp.
 */
defined('YII_BEGIN_TIME') or define('YII_BEGIN_TIME',microtime(true));
/**
 * This constant defines whether the application should be in debug mode or not. Defaults to false.
 */
defined('YII_DEBUG') or define('YII_DEBUG',false);
/**
 * This constant defines how much call stack information (file name and line number) should be logged by Yii::trace().
 * Defaults to 0, meaning no backtrace information. If it is greater than 0,
 * at most that number of call stacks will be logged. Note, only user application call stacks are considered.
 */
defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL',0);
/**
 * This constant defines whether exception handling should be enabled. Defaults to true.
 */
defined('YII_ENABLE_EXCEPTION_HANDLER') or define('YII_ENABLE_EXCEPTION_HANDLER',true);
/**
 * This constant defines whether error handling should be enabled. Defaults to true.
 */
defined('YII_ENABLE_ERROR_HANDLER') or define('YII_ENABLE_ERROR_HANDLER',true);
/**
 * Defines the Yii framework installation path.
 */
defined('YII_PATH') or define('YII_PATH',dirname(__FILE__));

/**
 * YiiBase is a helper class serving common framework functionalities.
 *
 * Do not use YiiBase directly. Instead, use its child class {@link Yii} where
 * you can customize methods of YiiBase.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: YiiBase.php 1578 2009-12-13 04:37:05Z qiang.xue $
 * @package system
 * @since 1.0
 */
class YiiBase
{
	private static $_aliases=array('system'=>YII_PATH); // alias => path
	private static $_imports=array();					// alias => class name or directory
	private static $_classes=array();
	private static $_includePaths;						// list of include paths
	private static $_app;
	private static $_logger;


	/**
	 * @return string the version of Yii framework
	 */
	public static function getVersion()
	{
		return '1.0.11';
	}

	/**
	 * Creates a Web application instance.
	 * @param mixed application configuration.
	 * If a string, it is treated as the path of the file that contains the configuration;
	 * If an array, it is the actual configuration information.
	 * Please make sure you specify the {@link CApplication::basePath basePath} property in the configuration,
	 * which should point to the directory containing all application logic, template and data.
	 * If not, the directory will be defaulted to 'protected'.
	 */
	public static function createWebApplication($config=null)
	{
		return self::createApplication('CWebApplication',$config);
	}

	/**
	 * Creates a console application instance.
	 * @param mixed application configuration.
	 * If a string, it is treated as the path of the file that contains the configuration;
	 * If an array, it is the actual configuration information.
	 * Please make sure you specify the {@link CApplication::basePath basePath} property in the configuration,
	 * which should point to the directory containing all application logic, template and data.
	 * If not, the directory will be defaulted to 'protected'.
	 */
	public static function createConsoleApplication($config=null)
	{
		return self::createApplication('CConsoleApplication',$config);
	}

	/**
	 * Creates an application of the specified class.
	 * @param string the application class name
	 * @param mixed application configuration. This parameter will be passed as the parameter
	 * to the constructor of the application class.
	 * @return mixed the application instance
	 * @since 1.0.10
	 */
	public static function createApplication($class,$config=null)
	{
		return new $class($config);
	}

	/**
	 * @return CApplication the application singleton, null if the singleton has not been created yet.
	 */
	public static function app()
	{
		return self::$_app;
	}

	/**
	 * Stores the application instance in the class static member.
	 * This method helps implement a singleton pattern for CApplication.
	 * Repeated invocation of this method or the CApplication constructor
	 * will cause the throw of an exception.
	 * To retrieve the application instance, use {@link app()}.
	 * @param CApplication the application instance. If this is null, the existing
	 * application singleton will be removed.
	 * @throws CException if multiple application instances are registered.
	 */
	public static function setApplication($app)
	{
		if(self::$_app===null || $app===null)
			self::$_app=$app;
		else
			throw new CException(Yii::t('yii','Yii application can only be created once.'));
	}

	/**
	 * @return string the path of the framework
	 */
	public static function getFrameworkPath()
	{
		return YII_PATH;
	}

	/**
	 * Creates an object and initializes it based on the given configuration.
	 *
	 * The specified configuration can be either a string or an array.
	 * If the former, the string is treated as the object type which can
	 * be either the class name or {@link YiiBase::getPathOfAlias class path alias}.
	 * If the latter, the 'class' element is treated as the object type,
	 * and the rest name-value pairs in the array are used to initialize
	 * the corresponding object properties.
	 *
	 * Any additional parameters passed to this method will be
	 * passed to the constructor of the object being created.
	 *
	 * NOTE: the array-typed configuration has been supported since version 1.0.1.
	 *
	 * @param mixed the configuration. It can be either a string or an array.
	 * @return mixed the created object
	 * @throws CException if the configuration does not have a 'class' element.
	 */
	public static function createComponent($config)
	{
		if(is_string($config))
		{
			$type=$config;
			$config=array();
		}
		else if(isset($config['class']))
		{
			$type=$config['class'];
			unset($config['class']);
		}
		else
			throw new CException(Yii::t('yii','Object configuration must be an array containing a "class" element.'));

		if(!class_exists($type,false))
			$type=Yii::import($type,true);

		if(($n=func_num_args())>1)
		{
			$args=func_get_args();
			if($n===2)
				$object=new $type($args[1]);
			else if($n===3)
				$object=new $type($args[1],$args[2]);
			else if($n===4)
				$object=new $type($args[1],$args[2],$args[3]);
			else
			{
				unset($args[0]);
				$class=new ReflectionClass($type);
				// Note: ReflectionClass::newInstanceArgs() is available for PHP 5.1.3+
				// $object=$class->newInstanceArgs($args);
				$object=call_user_func_array(array($class,'newInstance'),$args);
			}
		}
		else
			$object=new $type;

		foreach($config as $key=>$value)
			$object->$key=$value;

		return $object;
	}

	/**
	 * Imports the definition of a class or a directory of class files.
	 *
	 * Path aliases are used to refer to the class file or directory being imported.
	 * If importing a path alias ending with '.*', the alias is considered as a directory
	 * which will be added to the PHP include paths; Otherwise, the alias is translated
	 * to the path of a class file which is included when needed.
	 *
	 * For example, importing 'system.web.*' will add the 'web' directory of the framework
	 * to the PHP include paths; while importing 'system.web.CController' will include
	 * the class file 'web/CController.php' when needed.
	 *
	 * The same alias can be imported multiple times, but only the first time is effective.
	 *
	 * @param string path alias to be imported
	 * @param boolean whether to include the class file immediately. If false, the class file
	 * will be included only when the class is being used.
	 * @return string the class name or the directory that this alias refers to
	 * @throws CException if the alias is invalid
	 */
	public static function import($alias,$forceInclude=false)
	{
		if(isset(self::$_imports[$alias]))  // previously imported
			return self::$_imports[$alias];

		if(class_exists($alias,false) || interface_exists($alias,false))
			return self::$_imports[$alias]=$alias;

		if(isset(self::$_coreClasses[$alias]) || ($pos=strrpos($alias,'.'))===false)  // a simple class name
		{
			self::$_imports[$alias]=$alias;
			if($forceInclude)
			{
				if(isset(self::$_coreClasses[$alias])) // a core class
					require(YII_PATH.self::$_coreClasses[$alias]);
				else
					require($alias.'.php');
			}
			return $alias;
		}

		if(($className=(string)substr($alias,$pos+1))!=='*' && (class_exists($className,false) || interface_exists($className,false)))
			return self::$_imports[$alias]=$className;

		if(($path=self::getPathOfAlias($alias))!==false)
		{
			if($className!=='*')
			{
				self::$_imports[$alias]=$className;
				if($forceInclude)
					require($path.'.php');
				else
					self::$_classes[$className]=$path.'.php';
				return $className;
			}
			else  // a directory
			{
				if(self::$_includePaths===null)
				{
					self::$_includePaths=array_unique(explode(PATH_SEPARATOR,get_include_path()));
					if(($pos=array_search('.',self::$_includePaths,true))!==false)
						unset(self::$_includePaths[$pos]);
				}
				array_unshift(self::$_includePaths,$path);
				if(set_include_path('.'.PATH_SEPARATOR.implode(PATH_SEPARATOR,self::$_includePaths))===false)
					throw new CException(Yii::t('yii','Unable to import "{alias}". Please check your server configuration to make sure you are allowed to change PHP include_path.',array('{alias}'=>$alias)));
				return self::$_imports[$alias]=$path;
			}
		}
		else
			throw new CException(Yii::t('yii','Alias "{alias}" is invalid. Make sure it points to an existing directory or file.',
				array('{alias}'=>$alias)));
	}

	/**
	 * Translates an alias into a file path.
	 * Note, this method does not ensure the existence of the resulting file path.
	 * It only checks if the root alias is valid or not.
	 * @param string alias (e.g. system.web.CController)
	 * @return mixed file path corresponding to the alias, false if the alias is invalid.
	 */
	public static function getPathOfAlias($alias)
	{
		if(isset(self::$_aliases[$alias]))
			return self::$_aliases[$alias];
		else if(($pos=strpos($alias,'.'))!==false)
		{
			$rootAlias=substr($alias,0,$pos);
			if(isset(self::$_aliases[$rootAlias]))
				return self::$_aliases[$alias]=rtrim(self::$_aliases[$rootAlias].DIRECTORY_SEPARATOR.str_replace('.',DIRECTORY_SEPARATOR,substr($alias,$pos+1)),'*'.DIRECTORY_SEPARATOR);
			else if(self::$_app instanceof CWebApplication)
			{
				if(self::$_app->findModule($rootAlias)!==null)
					return self::getPathOfAlias($alias);
			}
		}
		return false;
	}

	/**
	 * Create a path alias.
	 * Note, this method neither checks the existence of the path nor normalizes the path.
	 * @param string alias to the path
	 * @param string the path corresponding to the alias. If this is null, the corresponding
	 * path alias will be removed.
	 */
	public static function setPathOfAlias($alias,$path)
	{
		if(empty($path))
			unset(self::$_aliases[$alias]);
		else
			self::$_aliases[$alias]=rtrim($path,'\\/');
	}

	/**
	 * Class autoload loader.
	 * This method is provided to be invoked within an __autoload() magic method.
	 * @param string class name
	 * @return boolean whether the class has been loaded successfully
	 */
	public static function autoload($className)
	{
		// use include so that the error PHP file may appear
		if(isset(self::$_coreClasses[$className]))
			include(YII_PATH.self::$_coreClasses[$className]);
		else if(isset(self::$_classes[$className]))
			include(self::$_classes[$className]);
		else
		{
			include($className.'.php');
			return class_exists($className,false) || interface_exists($className,false);
		}
		return true;
	}

	/**
	 * Writes a trace message.
	 * This method will only log a message when the application is in debug mode.
	 * @param string message to be logged
	 * @param string category of the message
	 * @see log
	 */
	public static function trace($msg,$category='application')
	{
		if(YII_DEBUG)
		{
			if(YII_TRACE_LEVEL>0)
			{
				$traces=debug_backtrace();
				$count=0;
				foreach($traces as $trace)
				{
					if(isset($trace['file'],$trace['line']))
					{
						$className=substr(basename($trace['file']),0,-4);
						if(!isset(self::$_coreClasses[$className]) && $className!=='YiiBase')
						{
							$msg.="\nin ".$trace['file'].' ('.$trace['line'].')';
							if(++$count>=YII_TRACE_LEVEL)
								break;
						}
					}
				}
			}
			self::log($msg,CLogger::LEVEL_TRACE,$category);
		}
	}

	/**
	 * Logs a message.
	 * Messages logged by this method may be retrieved via {@link CLogger::getLogs}
	 * and may be recorded in different media, such as file, email, database, using
	 * {@link CLogRouter}.
	 * @param string message to be logged
	 * @param string level of the message (e.g. 'trace', 'warning', 'error'). It is case-insensitive.
	 * @param string category of the message (e.g. 'system.web'). It is case-insensitive.
	 */
	public static function log($msg,$level=CLogger::LEVEL_INFO,$category='application')
	{
		if(self::$_logger===null)
			self::$_logger=new CLogger;
		self::$_logger->log($msg,$level,$category);
	}

	/**
	 * Marks the begin of a code block for profiling.
	 * This has to be matched with a call to {@link endProfile()} with the same token.
	 * The begin- and end- calls must also be properly nested, e.g.,
	 * <pre>
	 * Yii::beginProfile('block1');
	 * Yii::beginProfile('block2');
	 * Yii::endProfile('block2');
	 * Yii::endProfile('block1');
	 * </pre>
	 * The following sequence is not valid:
	 * <pre>
	 * Yii::beginProfile('block1');
	 * Yii::beginProfile('block2');
	 * Yii::endProfile('block1');
	 * Yii::endProfile('block2');
	 * </pre>
	 * @param string token for the code block
	 * @param string the category of this log message
	 * @see endProfile
	 */
	public static function beginProfile($token,$category='application')
	{
		self::log('begin:'.$token,CLogger::LEVEL_PROFILE,$category);
	}

	/**
	 * Marks the end of a code block for profiling.
	 * This has to be matched with a previous call to {@link beginProfile()} with the same token.
	 * @param string token for the code block
	 * @param string the category of this log message
	 * @see beginProfile
	 */
	public static function endProfile($token,$category='application')
	{
		self::log('end:'.$token,CLogger::LEVEL_PROFILE,$category);
	}

	/**
	 * @return CLogger message logger
	 */
	public static function getLogger()
	{
		if(self::$_logger!==null)
			return self::$_logger;
		else
			return self::$_logger=new CLogger;
	}

	/**
	 * @return string a string that can be displayed on your Web page showing Powered-by-Yii information
	 */
	public static function powered()
	{
		return 'Powered by <a href="http://www.yiiframework.com/" target="_blank">Yii Framework</a>.';
	}

	/**
	 * Translates a message to the specified language.
	 * Starting from version 1.0.2, this method supports choice format (see {@link CChoiceFormat}),
	 * i.e., the message returned will be chosen from a few candidates according to the given
	 * number value. This feature is mainly used to solve plural format issue in case
	 * a message has different plural forms in some languages.
	 * @param string message category. Please use only word letters. Note, category 'yii' is
	 * reserved for Yii framework core code use. See {@link CPhpMessageSource} for
	 * more interpretation about message category.
	 * @param string the original message
	 * @param array parameters to be applied to the message using <code>strtr</code>.
	 * Starting from version 1.0.2, the first parameter can be a number without key.
	 * And in this case, the method will call {@link CChoiceFormat::format} to choose
	 * an appropriate message translation.
	 * @param string which message source application component to use.
	 * Defaults to null, meaning using 'coreMessages' for messages belonging to
	 * the 'yii' category and using 'messages' for the rest messages.
	 * @param string the target language. If null (default), the {@link CApplication::getLanguage application language} will be used.
	 * This parameter has been available since version 1.0.3.
	 * @return string the translated message
	 * @see CMessageSource
	 */
	public static function t($category,$message,$params=array(),$source=null,$language=null)
	{
		if(self::$_app!==null)
		{
			if($source===null)
				$source=$category==='yii'?'coreMessages':'messages';
			if(($source=self::$_app->getComponent($source))!==null)
				$message=$source->translate($category,$message,$language);
		}
		if($params===array())
			return $message;
		if(isset($params[0])) // number choice
		{
			$message=CChoiceFormat::format($message,$params[0]);
			unset($params[0]);
		}
		return $params!==array() ? strtr($message,$params) : $message;
	}

	/**
	 * Registers a new class autoloader.
	 * The new autoloader will be placed before {@link autoload} and after
	 * any other existing autoloaders.
	 * @param callback a valid PHP callback (function name or array($className,$methodName)).
	 * @since 1.0.10
	 */
	public static function registerAutoloader($callback)
	{
		spl_autoload_unregister(array('YiiBase','autoload'));
		spl_autoload_register($callback);
		spl_autoload_register(array('YiiBase','autoload'));
	}

	/**
	 * @var array class map for core Yii classes.
	 * NOTE, DO NOT MODIFY THIS ARRAY MANUALLY. IF YOU CHANGE OR ADD SOME CORE CLASSES,
	 * PLEASE RUN 'build autoload' COMMAND TO UPDATE THIS ARRAY.
	 */
	private static $_coreClasses=array(
		'CApplication' => '/base/CApplication.php',
		'CApplicationComponent' => '/base/CApplicationComponent.php',
		'CBehavior' => '/base/CBehavior.php',
		'CComponent' => '/base/CComponent.php',
		'CErrorEvent' => '/base/CErrorEvent.php',
		'CErrorHandler' => '/base/CErrorHandler.php',
		'CException' => '/base/CException.php',
		'CExceptionEvent' => '/base/CExceptionEvent.php',
		'CHttpException' => '/base/CHttpException.php',
		'CModel' => '/base/CModel.php',
		'CModelBehavior' => '/base/CModelBehavior.php',
		'CModelEvent' => '/base/CModelEvent.php',
		'CModule' => '/base/CModule.php',
		'CSecurityManager' => '/base/CSecurityManager.php',
		'CStatePersister' => '/base/CStatePersister.php',
		'CApcCache' => '/caching/CApcCache.php',
		'CCache' => '/caching/CCache.php',
		'CDbCache' => '/caching/CDbCache.php',
		'CDummyCache' => '/caching/CDummyCache.php',
		'CEAcceleratorCache' => '/caching/CEAcceleratorCache.php',
		'CFileCache' => '/caching/CFileCache.php',
		'CMemCache' => '/caching/CMemCache.php',
		'CXCache' => '/caching/CXCache.php',
		'CZendDataCache' => '/caching/CZendDataCache.php',
		'CCacheDependency' => '/caching/dependencies/CCacheDependency.php',
		'CChainedCacheDependency' => '/caching/dependencies/CChainedCacheDependency.php',
		'CDbCacheDependency' => '/caching/dependencies/CDbCacheDependency.php',
		'CDirectoryCacheDependency' => '/caching/dependencies/CDirectoryCacheDependency.php',
		'CExpressionDependency' => '/caching/dependencies/CExpressionDependency.php',
		'CFileCacheDependency' => '/caching/dependencies/CFileCacheDependency.php',
		'CGlobalStateCacheDependency' => '/caching/dependencies/CGlobalStateCacheDependency.php',
		'CAttributeCollection' => '/collections/CAttributeCollection.php',
		'CConfiguration' => '/collections/CConfiguration.php',
		'CList' => '/collections/CList.php',
		'CListIterator' => '/collections/CListIterator.php',
		'CMap' => '/collections/CMap.php',
		'CMapIterator' => '/collections/CMapIterator.php',
		'CQueue' => '/collections/CQueue.php',
		'CQueueIterator' => '/collections/CQueueIterator.php',
		'CStack' => '/collections/CStack.php',
		'CStackIterator' => '/collections/CStackIterator.php',
		'CTypedList' => '/collections/CTypedList.php',
		'CConsoleApplication' => '/console/CConsoleApplication.php',
		'CConsoleCommand' => '/console/CConsoleCommand.php',
		'CConsoleCommandRunner' => '/console/CConsoleCommandRunner.php',
		'CHelpCommand' => '/console/CHelpCommand.php',
		'CDbCommand' => '/db/CDbCommand.php',
		'CDbConnection' => '/db/CDbConnection.php',
		'CDbDataReader' => '/db/CDbDataReader.php',
		'CDbException' => '/db/CDbException.php',
		'CDbTransaction' => '/db/CDbTransaction.php',
		'CActiveFinder' => '/db/ar/CActiveFinder.php',
		'CActiveRecord' => '/db/ar/CActiveRecord.php',
		'CActiveRecordBehavior' => '/db/ar/CActiveRecordBehavior.php',
		'CDbColumnSchema' => '/db/schema/CDbColumnSchema.php',
		'CDbCommandBuilder' => '/db/schema/CDbCommandBuilder.php',
		'CDbCriteria' => '/db/schema/CDbCriteria.php',
		'CDbExpression' => '/db/schema/CDbExpression.php',
		'CDbSchema' => '/db/schema/CDbSchema.php',
		'CDbTableSchema' => '/db/schema/CDbTableSchema.php',
		'CMssqlColumnSchema' => '/db/schema/mssql/CMssqlColumnSchema.php',
		'CMssqlCommandBuilder' => '/db/schema/mssql/CMssqlCommandBuilder.php',
		'CMssqlPdoAdapter' => '/db/schema/mssql/CMssqlPdoAdapter.php',
		'CMssqlSchema' => '/db/schema/mssql/CMssqlSchema.php',
		'CMssqlTableSchema' => '/db/schema/mssql/CMssqlTableSchema.php',
		'CMysqlColumnSchema' => '/db/schema/mysql/CMysqlColumnSchema.php',
		'CMysqlSchema' => '/db/schema/mysql/CMysqlSchema.php',
		'CMysqlTableSchema' => '/db/schema/mysql/CMysqlTableSchema.php',
		'COciColumnSchema' => '/db/schema/oci/COciColumnSchema.php',
		'COciCommandBuilder' => '/db/schema/oci/COciCommandBuilder.php',
		'COciSchema' => '/db/schema/oci/COciSchema.php',
		'COciTableSchema' => '/db/schema/oci/COciTableSchema.php',
		'CPgsqlColumnSchema' => '/db/schema/pgsql/CPgsqlColumnSchema.php',
		'CPgsqlSchema' => '/db/schema/pgsql/CPgsqlSchema.php',
		'CPgsqlTableSchema' => '/db/schema/pgsql/CPgsqlTableSchema.php',
		'CSqliteColumnSchema' => '/db/schema/sqlite/CSqliteColumnSchema.php',
		'CSqliteCommandBuilder' => '/db/schema/sqlite/CSqliteCommandBuilder.php',
		'CSqliteSchema' => '/db/schema/sqlite/CSqliteSchema.php',
		'CChoiceFormat' => '/i18n/CChoiceFormat.php',
		'CDateFormatter' => '/i18n/CDateFormatter.php',
		'CDbMessageSource' => '/i18n/CDbMessageSource.php',
		'CGettextMessageSource' => '/i18n/CGettextMessageSource.php',
		'CLocale' => '/i18n/CLocale.php',
		'CMessageSource' => '/i18n/CMessageSource.php',
		'CNumberFormatter' => '/i18n/CNumberFormatter.php',
		'CPhpMessageSource' => '/i18n/CPhpMessageSource.php',
		'CGettextFile' => '/i18n/gettext/CGettextFile.php',
		'CGettextMoFile' => '/i18n/gettext/CGettextMoFile.php',
		'CGettextPoFile' => '/i18n/gettext/CGettextPoFile.php',
		'CDbLogRoute' => '/logging/CDbLogRoute.php',
		'CEmailLogRoute' => '/logging/CEmailLogRoute.php',
		'CFileLogRoute' => '/logging/CFileLogRoute.php',
		'CLogFilter' => '/logging/CLogFilter.php',
		'CLogRoute' => '/logging/CLogRoute.php',
		'CLogRouter' => '/logging/CLogRouter.php',
		'CLogger' => '/logging/CLogger.php',
		'CProfileLogRoute' => '/logging/CProfileLogRoute.php',
		'CWebLogRoute' => '/logging/CWebLogRoute.php',
		'CDateTimeParser' => '/utils/CDateTimeParser.php',
		'CFileHelper' => '/utils/CFileHelper.php',
		'CMarkdownParser' => '/utils/CMarkdownParser.php',
		'CPropertyValue' => '/utils/CPropertyValue.php',
		'CTimestamp' => '/utils/CTimestamp.php',
		'CVarDumper' => '/utils/CVarDumper.php',
		'CBooleanValidator' => '/validators/CBooleanValidator.php',
		'CCaptchaValidator' => '/validators/CCaptchaValidator.php',
		'CCompareValidator' => '/validators/CCompareValidator.php',
		'CDefaultValueValidator' => '/validators/CDefaultValueValidator.php',
		'CEmailValidator' => '/validators/CEmailValidator.php',
		'CExistValidator' => '/validators/CExistValidator.php',
		'CFileValidator' => '/validators/CFileValidator.php',
		'CFilterValidator' => '/validators/CFilterValidator.php',
		'CInlineValidator' => '/validators/CInlineValidator.php',
		'CNumberValidator' => '/validators/CNumberValidator.php',
		'CRangeValidator' => '/validators/CRangeValidator.php',
		'CRegularExpressionValidator' => '/validators/CRegularExpressionValidator.php',
		'CRequiredValidator' => '/validators/CRequiredValidator.php',
		'CStringValidator' => '/validators/CStringValidator.php',
		'CTypeValidator' => '/validators/CTypeValidator.php',
		'CUniqueValidator' => '/validators/CUniqueValidator.php',
		'CUrlValidator' => '/validators/CUrlValidator.php',
		'CValidator' => '/validators/CValidator.php',
		'CAssetManager' => '/web/CAssetManager.php',
		'CBaseController' => '/web/CBaseController.php',
		'CCacheHttpSession' => '/web/CCacheHttpSession.php',
		'CClientScript' => '/web/CClientScript.php',
		'CController' => '/web/CController.php',
		'CDbHttpSession' => '/web/CDbHttpSession.php',
		'CExtController' => '/web/CExtController.php',
		'CFormModel' => '/web/CFormModel.php',
		'CHttpCookie' => '/web/CHttpCookie.php',
		'CHttpRequest' => '/web/CHttpRequest.php',
		'CHttpSession' => '/web/CHttpSession.php',
		'CHttpSessionIterator' => '/web/CHttpSessionIterator.php',
		'COutputEvent' => '/web/COutputEvent.php',
		'CPagination' => '/web/CPagination.php',
		'CSort' => '/web/CSort.php',
		'CTheme' => '/web/CTheme.php',
		'CThemeManager' => '/web/CThemeManager.php',
		'CUploadedFile' => '/web/CUploadedFile.php',
		'CUrlManager' => '/web/CUrlManager.php',
		'CWebApplication' => '/web/CWebApplication.php',
		'CWebModule' => '/web/CWebModule.php',
		'CAction' => '/web/actions/CAction.php',
		'CInlineAction' => '/web/actions/CInlineAction.php',
		'CViewAction' => '/web/actions/CViewAction.php',
		'CAccessControlFilter' => '/web/auth/CAccessControlFilter.php',
		'CAuthAssignment' => '/web/auth/CAuthAssignment.php',
		'CAuthItem' => '/web/auth/CAuthItem.php',
		'CAuthManager' => '/web/auth/CAuthManager.php',
		'CBaseUserIdentity' => '/web/auth/CBaseUserIdentity.php',
		'CDbAuthManager' => '/web/auth/CDbAuthManager.php',
		'CPhpAuthManager' => '/web/auth/CPhpAuthManager.php',
		'CUserIdentity' => '/web/auth/CUserIdentity.php',
		'CWebUser' => '/web/auth/CWebUser.php',
		'CFilter' => '/web/filters/CFilter.php',
		'CFilterChain' => '/web/filters/CFilterChain.php',
		'CInlineFilter' => '/web/filters/CInlineFilter.php',
		'CGoogleApi' => '/web/helpers/CGoogleApi.php',
		'CHtml' => '/web/helpers/CHtml.php',
		'CJSON' => '/web/helpers/CJSON.php',
		'CJavaScript' => '/web/helpers/CJavaScript.php',
		'CPradoViewRenderer' => '/web/renderers/CPradoViewRenderer.php',
		'CViewRenderer' => '/web/renderers/CViewRenderer.php',
		'CWebService' => '/web/services/CWebService.php',
		'CWebServiceAction' => '/web/services/CWebServiceAction.php',
		'CWsdlGenerator' => '/web/services/CWsdlGenerator.php',
		'CAutoComplete' => '/web/widgets/CAutoComplete.php',
		'CClipWidget' => '/web/widgets/CClipWidget.php',
		'CContentDecorator' => '/web/widgets/CContentDecorator.php',
		'CFilterWidget' => '/web/widgets/CFilterWidget.php',
		'CFlexWidget' => '/web/widgets/CFlexWidget.php',
		'CHtmlPurifier' => '/web/widgets/CHtmlPurifier.php',
		'CInputWidget' => '/web/widgets/CInputWidget.php',
		'CMarkdown' => '/web/widgets/CMarkdown.php',
		'CMaskedTextField' => '/web/widgets/CMaskedTextField.php',
		'CMultiFileUpload' => '/web/widgets/CMultiFileUpload.php',
		'COutputCache' => '/web/widgets/COutputCache.php',
		'COutputProcessor' => '/web/widgets/COutputProcessor.php',
		'CStarRating' => '/web/widgets/CStarRating.php',
		'CTabView' => '/web/widgets/CTabView.php',
		'CTextHighlighter' => '/web/widgets/CTextHighlighter.php',
		'CTreeView' => '/web/widgets/CTreeView.php',
		'CWidget' => '/web/widgets/CWidget.php',
		'CCaptcha' => '/web/widgets/captcha/CCaptcha.php',
		'CCaptchaAction' => '/web/widgets/captcha/CCaptchaAction.php',
		'CBasePager' => '/web/widgets/pagers/CBasePager.php',
		'CLinkPager' => '/web/widgets/pagers/CLinkPager.php',
		'CListPager' => '/web/widgets/pagers/CListPager.php',
	);
}

spl_autoload_register(array('YiiBase','autoload'));
require(YII_PATH.'/base/interfaces.php');
