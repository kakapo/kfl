<?php
// KFL config File

if(!defined("KFL_DIR")) define("KFL_DIR", dirname(__FILE__));

// define applications models dicrectory
define("APP_DIR_M",APP_DIR. "/models");

// define applications templates dicrectory
define("APP_DIR_V",APP_DIR. "/views");

// define applications controllers dicrectory
define("APP_DIR_C",APP_DIR. "/controllers");

// define applications temporary dicrectory
define("APP_TEMP_DIR", APP_DIR. '/tmp');

define("APP_LANG_DIR", APP_DIR. '/languages');

// define error log file
define("LOG_FILE", APP_DIR . "/tmp/logs");

// define upload directory
define("UPLOAD_DIR", APP_DIR . "/tmp/uploads");

// if using the KFL/Components/libs instead of your systemwide pear libraries.
if(PHP_OS=='Linux'){
	ini_set('include_path', KFL_DIR. "/:". APP_DIR_M ."/:" . ini_get('include_path').':'); // FOR UNIX
}elseif(PHP_OS=='WINNT'){
	ini_set('include_path', KFL_DIR. "/;". APP_DIR_M."/;". ini_get('include_path')); // FOR WINDOWS
}

/**
* @abstract KFL: Kindly Fast Light, a light fast MVC framework, kindly to be used.
* @author kakapo <kakapowu@gmail.com>
* @version 0.9.2 2008-05-05
* @copyright  Copyright (c) 2006-2007 kakapo.cn.  All rights reserved.
*/
class KFL
{
	/**
	 * core component
	 * @param array
	 */
	private $mCore;
	/**
	 * start time
	 * @param string
	 */
	private $mStartTime ;
	/**
	 * default controller
	 * @param string
	 */
	private $mDefaultController = "defaults";

	/**
	 * core settings
	 * @param array
	 */
	private $mCoreSettings = array('is_session'=>1,'is_phptpl'=>1,'is_authen'=>0,'is_database'=>1);
	
	/**
	 * cache object
	 * @param object
	 */
	private $mCache;
	
	/**
     * initialize.
     * @param string $appPath Application directory;
     * @param int $cache, if 0, no cache, else cache time second
	 * @access public
     * @return void
     */
	public function KFL()
	{
		require_once("Common/common.php");
		include_once("Common/file.php");
		$this->mStartTime = getmicrotime ();
	}

	/**
	 * setDefController
	 * @param string $defComtroller
	 * @access public
	 * @return void
	 */
	public function setDefController($defComtroller){
		$this->mDefaultController = $defComtroller;
	}

	/**
	 * setDefView
	 * @param string $defView
	 * @access public
	 * @return void
	 */
	public function setDefView($defView){
		if(!defined("APP_TPLSTYLE")){
			define("APP_TPLSTYLE",$defView);
		}
	}

	/**
	 * useCache
	 * @param int $lifetime
	 * @access public
	 * @return void
	 */
	public function useCache($lifetime=300){
		// only cache get request
		if($_SERVER["REQUEST_METHOD"]!='GET'){
			return ;
		}
		include_once("Libs/Cache.class.php");
    	$this->mCache = new Cache($lifetime);
    	$cacheDir = APP_TEMP_DIR.'/_cache/';
    	$cache_file = 'KFL_'.md5($_SERVER['REQUEST_URI']);
	 
		$this->mCache->setCacheDir($cacheDir);
		$this->mCache->setCacheFile($cache_file);
		if($this->mCache->isCached()){
		 	$this->mCache->output();
		 	$this->execTime();
		}
	}

	/**
     * setup
	 * @access private
	 * @return void
     */
	private function setup(){
		global $tpl;
		if($this->mCoreSettings['is_database']){
			require_once("Libs/Database.class.php");
			$this->mCore[] = 'database';
		}

		//add core components
		if($this->mCoreSettings['is_session']){
			require_once("Libs/SessionHandle.class.php");
			new SessionHandle();
			$this->mCore[] = 'session';
		}

		if($this->mCoreSettings['is_authen']){
			require_once("Libs/Authenticate.class.php");
			new Authenticate('authen');
			$this->mCore[] = 'authen';
		}
		// init view engine
		if($this->mCoreSettings['is_phptpl']){
			require_once("Libs/PhpTemplate.class.php");
	    	$tpl = new PhpTemplate();
	    	$tpl->template_dir = APP_DIR_V;
	    	$this->mCore[] = 'phptpl';
		}
	}

	/**
	 * run
	 * @access public
	 * @return void
	 */
	public function run(){
		// set up core components;
		$this->setup();

		// use controller to dispatch
		Controller::dispatch($this->mDefaultController,$this->mCore);

		// use view
		View::display();	

		if($this->mCache){
			$this->mCache->save();
		}
		
		$this->execTime();
		
	}
	/**
	 * execTime
	 * @access public
	 * @return void
	 */	
	public function execTime(){
		exit(getmicrotime ()-$this->mStartTime);
	}

}

class Controller{
    /**
     * mComponents
     * @param array
     */
    private static $mComponents = array();
	private	static $mDispatcher;
	/**
	 * includeDispatcher
	 * @access private
	 * @return void
	 */
	private static function includeDispatcher(){
		// include controller file and instance controller object
		$entrance = basename($_SERVER["SCRIPT_NAME"]);
		$entrance = substr($entrance,0,strpos($entrance,"."));
		if(!defined("ENTRANCE")) define("ENTRANCE",$entrance);
		$file =APP_DIR_C."/".$entrance."/".self::$mDispatcher.".class.php";

		if(is_file($file)){
			include_once($file);
			return true;
		}else{
			trigger_error("KFL Error: File ".$file." is not exists.",E_USER_ERROR);
			return false;
		}
	}
	
	/**
	 * authenticate
	 * @access private
	 * @return void
	 */
	private static function authenticate(){
		 global $authen,$gCurPriv;
		 if(in_array("authen",self::$mComponents)){
		 	$sessionLoginUser = isset($_SESSION['LoginUser'])?$_SESSION['LoginUser']:array();
		 	$sessionLoginUserId = isset($SessionLoginUser['user_id'])?$sessionLoginUser['user_id']:0;
		 	$authen->setLoginUser($sessionLoginUser,$sessionLoginUserId);
		 	$res = $authen->isAllowed($gCurPriv);
		 	if(!$res){
		 		show_message("你没有权限访问.");
		 		die;
		 	}
		 }

	}
	
	public static function dispatch($defaultdispatcher,$components){

		self::$mDispatcher = !empty($GLOBALS['gDispatcher'])?$GLOBALS['gDispatcher']:$defaultdispatcher;
		self::$mComponents = $components;
	    self::includeDispatcher();

		$class = self::$mDispatcher;

		if(!class_exists($class))
		{
			trigger_error("Class ".$class." not defined.");
			die();
		}

		// instance controller object.
		$obj = new $class;

		//deal with method
		if(!empty($_POST['op'])&&ereg("^[A-Za-z0-9]+$", $_POST['op'])) {
            $method = "op_".$_POST['op'];
            $u = $class.".op.".($_POST['op']);
            $tplfile = $class.'_'.$_POST['op'].'.html';
		}elseif(!empty($_GET['view'])&&ereg("^[A-Za-z0-9]+$",$_GET['view'])) {
            $method = "view_".$_GET['view'];
            $u = $class.".view.".$_GET['view'];
            $tplfile = $class.'_'.$_GET['view'].'.html';
		}else {
            $method = "view_defaults";
			$u = $class.".view.defaults";
			$tplfile = $class.'_defaults.html';
		}

	    if(!method_exists($obj,$method)){
	    	trigger_error("Function ".$method."() not defined.",E_USER_ERROR);
	    	die();
	    }
	    // authenticate
		self::authenticate();

		// real function
		$obj-> $method();

	    $GLOBALS['gCurUseMeth'] = $method;
	    $GLOBALS['gTplFile'] = $tplfile;
	    $GLOBALS['gCurPriv'] = $u;
	}

}

class Model
{

	/**
	 * 数据库连接
	 *@access public
	 *@param array $options
	 *@return mixed
	 */
	static function dbConnect($options=''){
		$default =0;
		if(empty($options)){
			$options = $GLOBALS['gDataBase']['defaults'];
			$default =1;
		}
		$db_resource = $options['dbname'];
	    if(isset($GLOBALS[$db_resource]) && is_object($GLOBALS[$db_resource])){
			//echo 2;
	    }else{
			if('mysql'== $options['type']) $dsn = $options['type'].":host=".$options['host'].";port=".$options['port'].";dbname=".$options['dbname'];
			if('sqlite'== $options['type']||'sqlite2'== $options['type']) $dsn = $options['type'].":".$options['path']."/".$options['dbname'];
			try{
				$GLOBALS[$db_resource] = new Database($dsn,$options['user'],$options['passwd'],array(PDO::ATTR_PERSISTENT => false));
				$cache_setting = isset($GLOBALS ['packet'])?$GLOBALS ['packet']:'';
				if($cache_setting) {
					$GLOBALS[$db_resource]->setCache($cache_setting);
				}
			}catch (PDOException $e){
				trigger_error("db connect failed!".$e->getMessage(),E_USER_ERROR);
				die();
			}
			if('mysql'== $options['type']) $GLOBALS[$db_resource] -> query("SET NAMES ".$options['charset']);
			//echo 1;
	    }

    	return $GLOBALS[$db_resource];

	}

}

class View{
	private static $view;
	public static function display(){
		global $tpl,$gCurUseMeth,$gTplFile;
		self::$view = $tpl;
		self::_assignGlobalSetting();
	    self::_assignLanguage();
		// view
		if(strpos($gCurUseMeth,'view')!== false) {
			self::$view->display(APP_TPLSTYLE.'/'.$gTplFile);
		}

	}
	/**
	 * _assignLanguage
	 * @access private
	 * @return void
	 */
	private static function _assignLanguage(){
		$langfile = APP_LANG_DIR."/".APP_LANG."/globals.php";
		if(file_exists($langfile)){
			include_once($langfile);
			self::$view->assign($GLOBALS['gLang']);
		}

	}
	/**
	 * _assignGlobalSetting
	 * @access private
	 * @return void
	 */
	private static function _assignGlobalSetting(){
		self::$view->assign($GLOBALS['gSiteInfo']);
	}
}
?>