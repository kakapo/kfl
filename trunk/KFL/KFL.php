<?php
// KFL config File
list ( $usec, $sec ) = explode ( " ", microtime () );
$GLOBALS['gAppStartTime'] =  (( float ) $usec + ( float ) $sec);

if(!defined("KFL_DIR")) define("KFL_DIR", dirname(__FILE__));

// define applications models dicrectory
define("APP_DIR_M",APP_DIR. "/models");

// define applications templates dicrectory
define("APP_DIR_V",APP_DIR. "/views");

// define applications controllers dicrectory
define("APP_DIR_C",APP_DIR. "/controllers");

// define applications temporary dicrectory
if(!defined("APP_TEMP_DIR")) define("APP_TEMP_DIR", APP_DIR."/tmp");

define("APP_LANG_DIR", APP_DIR. "/langs");

// define error log file
define("LOG_FILE_DIR", APP_TEMP_DIR . "/logs");

// define upload directory
define("UPLOAD_DIR", APP_TEMP_DIR . "/uploads");

// if using the KFL/Components/libs instead of your systemwide pear libraries.
if(PHP_OS=='Linux'){
	ini_set('include_path', KFL_DIR. "/:". APP_DIR_M ."/:" . ini_get('include_path').':'); // FOR UNIX
}elseif(PHP_OS=='WINNT'){
	ini_set('include_path', KFL_DIR. "/;". APP_DIR_M."/;". ini_get('include_path')); // FOR WINDOWS
}
require_once("Common/common.php");

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
	
	private $mCacheRule;
	/**
     * initialize.
     * @param string $appPath Application directory;
     * @param int $cache, if 0, no cache, else cache time second
	 * @access public
     * @return void
     */
	public function KFL()
	{
		$this->useCache();
		include_once("Common/utils.php");
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
	 * _getPageCacheRule
	 * @access private
	 * @return mix
	 */
	private function _getPageCacheRule(){
		$cur_action = !empty($GLOBALS['gDispatcher'])?$GLOBALS['gDispatcher']:$this->mDefaultController;
		if(isset($GLOBALS['gPageCache'])){
			$_GET['view'] = !empty($_GET['view'])?$_GET['view']:'defaults';
			foreach($GLOBALS['gPageCache'] as $rule){
				if(isset($rule['action']) && $rule['action']==$cur_action)	{
					if(isset($rule['view']) && ($rule['view']=='*')){ 
						return $rule;
					}else if(isset($rule['view']) && (false!==strpos($rule['view'],$_GET['view']))){
						return $rule;
					}
				}
			}
		}
		return false;
	}
	
	/**
	 * useCache
	 * @access public
	 * @return void
	 */
	public function useCache(){
		// only cache get request
		if($_SERVER["REQUEST_METHOD"]!='GET'){
			return ;
		}
		$this->mCacheRule = $this->_getPageCacheRule();
		
		if(!$this->mCacheRule) return ;
		
		include_once("Libs/Cache.class.php");
    	$this->mCache = new Cache($this->mCacheRule['cachetime'],$this->mCacheRule['compressed']);
    	
	 	$this->mCache->setCacheStore($this->mCacheRule['cachestore']);
	 	
		$this->mCache->setCacheServer($this->mCacheRule['cacheserver']);
		
		$this->mCache->setCacheDir($this->mCacheRule['cachedir']);
		
		$cache_file = 'KFL_'.md5($_SERVER['REQUEST_URI']).'.cache';
		$this->mCache->setCacheFile($cache_file);
		if($this->mCache->isCached()){
		 	$this->mCache->output();
		 	die;
		 	
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
			new SessionHandle($GLOBALS ["gSession"]);
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

		View::assignLanguage();
		
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
		$exec_time = (getmicrotime ()-$GLOBALS['gAppStartTime']);
		$memused = memory_get_usage();
		if($exec_time>$GLOBALS ['gLog'] ['maxExecTime'] || $memused>$GLOBALS ['gLog'] ['maxMemUsed']){
			$db = Model::dbConnect($GLOBALS ['gDataBase'] ['db_setting.db3']);
			$datetime = date("Y-m-d H:i:s");
			$db->execute("replace into eventlog (url,visit,exec_time,memuse) values ('".addslashes(WEB_URL)."','$datetime','$exec_time','$memused')");
		}
		//exit("<!-- execute time :".$exec_time."-->");
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
			if('mysql'== $options['type']) {
				$dsn = $options['type'].":host=".$options['host'].";port=".$options['port'].";dbname=".$options['dbname'];
				$user = $options['user'];
				$passwd = $options['passwd'];
			}
			if('sqlite'== $options['type']||'sqlite2'== $options['type']) {
				$dsn = $options['type'].":".$options['path']."/".$options['dbname'];
				$user = '';
				$passwd = '';
			}
			try{
				$GLOBALS[$db_resource] = new Database($dsn,$user,$passwd,array(PDO::ATTR_PERSISTENT => false));
				$cache_setting = isset($GLOBALS ['gPacket'])?$GLOBALS ['gPacket']:'';
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
		// view
		if(strpos($gCurUseMeth,'view')!== false) {
			self::$view->display(APP_TPLSTYLE.'/'.$gTplFile);
		}

	}
	/**
	 * assignLanguage
	 * @access public
	 * @return void
	 */
	public static function assignLanguage(){
		global $tpl;
		$selected_lang = !empty($_COOKIE['_Selected_Language'])?$_COOKIE['_Selected_Language']:APP_LANG; 
		$langfile = APP_LANG_DIR."/".$selected_lang."/globals.php";
		
		if(file_exists($langfile)){
			include_once($langfile);
			
			//$tpl->assign($GLOBALS['gLang']);
			$tpl->assign('selected_lang',$selected_lang);
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