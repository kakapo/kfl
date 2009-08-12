<?php
// KFL config File
require_once(dirname(__FILE__) . "/Common/config.php");

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
	 * default view style
	 * @param string
	 */
	private $mDefaultView = "defaults";

	/**
	 * core settings
	 * @param array
	 */
	private $mCoreSettings = array('is_session'=>1,'is_phptpl'=>1,'is_authen'=>1,'is_database'=>1);

	/**
	 * is caching
	 * @param int
	 */
	public  $mIsCache = 0;

	/**
	 * cache time
	 * @param int
	 */
	public  $mCacheTime = 300;

	/**
	 * cache dir
	 * @param string
	 */
	public  $mCacheDir = "/tmp/";

	/**
     * initialize.
     * @param string $appPath Application directory;
     * @param int $cache, if 0, no cache, else cache time second
	 * @access public
     * @return void
     */
	public function KFL($cache=0)
	{

		if($cache > 0){
			$this->useCache($cache);
		}
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
		$this->mDefaultView = $defView;
	}

	/**
	 * useCache
	 * @param int $lifetime
	 * @access public
	 * @return void
	 */
	public function useCache($lifetime){

		$this->mCacheTime = $lifetime;
		// $_POST no cache
		if($_SERVER["REQUEST_METHOD"]=='GET'){
			$this->mIsCache = 1;
		}

		$this->mCacheDir = APP_TEMP_DIR.'/_cache/';
		if(!is_dir($this->mCacheDir)){
			mkdir($this->mCacheDir);
			chmod($this->mCacheDir,0777);
		}
	    $cache_file = 'KFL_'.md5($_SERVER['REQUEST_URI']);

	    if($this->mIsCache && is_file($this->mCacheDir.$cache_file)){
	    	$modify_time = @filemtime($this->mCacheDir.$cache_file);
	    	if(time()-$modify_time < $this->mCacheTime){
	    		echo file_get_contents($this->mCacheDir.$cache_file);
	    		$end_time2 = getmicrotime();
	    		$this->costtime();
	    		die();
	    		
	    	}else{
	    		ob_start();
	    	}
	    }else{
	    	ob_start();
	    }

	}

	/**
     * setup
	 * @access public
	 * @return void
     */
	public function setup(){
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

		// generate html page
		Controller::generateHtml();

		if($this->mIsCache){
			if(!is_dir($this->mCacheDir)){
				mkdirr($this->mCacheDir);
			}
			$cache_file = $this->mCacheDir.'KFL_'.md5($_SERVER['REQUEST_URI']);
			write_file(ob_get_contents(),$cache_file);
		}

		
		$this->costtime();
	}

	
	public function costtime(){
		$end_time = getmicrotime ();
		$this->pageExecTime = $end_time-$this->mStartTime;
	}

}

class Controller{
    /**
     * mComponents
     * @param array
     */
    private static $mComponents = array();
	private	static $mDispatcher;
	private static $mHtmlFile;
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
//		 	//记录操作
//		 	$s = substr($gCurPriv,0,strpos($gCurPriv,'.'));
//		 	if($s!=='frame'){
//		 		include("OpLogManage.class.php");
//		 		$oplog =new OpLogManage;
//		 		$data = array('user_id'=>$_SESSION['LoginUser']['user_id'],'operation'=>$gCurPriv,'user_name'=>$_SESSION['LoginUser']['user_name']);
//		 		$oplog->saveLog($data);
//		 	}
		 }else{
		 	//trigger_error("KFL Error: add \$KFL->useAuthen(); in /index.php",E_USER_ERROR);
		 	//die();
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
	public static function generateHtml(){
		if(!empty(self::$mHtmlFile)){
			write_file(ob_get_contents(),self::$mHtmlFile);
		}
	}
	public static function createHtml($html_file){
		self::$mHtmlFile = $html_file;
		ob_start ();
	}
	public static function destoryHtml($html_file){
		unlink($html_file);
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
			$dsn = $options['type'].":host=".$options['host'].";port=".$options['port'].";dbname=".$options['dbname'];
			try{
				$GLOBALS[$db_resource] = new Database($dsn,$options['user'],$options['passwd'],array(PDO::ATTR_PERSISTENT => false));
			}catch (PDOException $e){
				trigger_error("db connect failed!".$e->getMessage(),E_USER_ERROR);
				die();
			}
			$GLOBALS[$db_resource] -> query("SET NAMES ".$options['charset']);
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
		$adfile = APP_TEMP_DIR."/ad_setup.php";
		if(file_exists($adfile)){
			include_once($adfile);
			self::$view->assign($adsetup);
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