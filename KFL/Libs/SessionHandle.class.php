<?php
class SessionHandle
{
	function __construct($setting){
		if($setting['sessionHandle']=='file'){			
			ini_set("session.cookie_domain", isset($_SERVER['HTTP_HOST'])?strstr($_SERVER['HTTP_HOST'],"."):'');
			session_start();
		}elseif($setting['sessionHandle']=='database'){
			SessionHandleMySQL::Init($setting);
		}elseif($setting['sessionHandle']=='memcache'){
			SessionHandleMemcache::Init($setting);
		}
		// user tracks IIS DO NOT PROVIDE REQUEST_URI
		if(!isset($_SERVER['REQUEST_URI'])){
			$_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'];
			// Append the query string if it exists and isn't null
			if (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) {
			  $_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
			}
		}
//		if(!isset($_SESSION['UserTrack'])) $_SESSION['UserTrack'] = array();
//		if($_SERVER['REQUEST_METHOD']=='GET'){
//			array_unshift($_SESSION['UserTrack'],$_SERVER['REQUEST_URI']);
//			if(count($_SESSION['UserTrack'])>5){
//				array_pop($_SESSION['UserTrack']);
//			}
//		}
	}
}

class SessionHandleMySQL extends Model
{
	static $db_static;
	static $set;
    static function Init($setting)
    {
    	self::$set = $setting;
    	self::$db_static = parent::dbConnect (self::$set['database']);

        $domain = strstr($_SERVER['HTTP_HOST'],".");
        //不使用 GET/POST 变量方式
        ini_set('session.use_trans_sid',    0);
        //设置垃圾回收最大生存时间
        ini_set('session.gc_maxlifetime',   self::$set['lifeTime']);

        //使用 COOKIE 保存 SESSION ID 的方式
        ini_set('session.use_cookies',      1);
        ini_set('session.cookie_path',      '/');
        //多主机共享保存 SESSION ID 的 COOKIE
        ini_set('session.cookie_domain',    $domain);

        //将 session.save_handler 设置为 user，而不是默认的 files
        session_module_name('user');
        //定义 SESSION 各项操作所对应的方法名：
        session_set_save_handler(
            array('SessionHandleMySQL', 'open'),   //对应于静态方法 My_Sess::open()，下同。
            array('SessionHandleMySQL', 'close'),
            array('SessionHandleMySQL', 'read'),
            array('SessionHandleMySQL', 'write'),
            array('SessionHandleMySQL', 'destroy'),
            array('SessionHandleMySQL', 'gc')
        );
        session_start();
    }   //end function
    public static function open($save_path, $session_name) {
        return true;
    }   //end function
    public static function close() {


        if (self::$db_static) {    //关闭数据库连接
            //self::$db_static->Close();
        }
        return true;
    }   //end function
    public static function read($sesskey) {
        $sql = "SELECT data FROM session WHERE sesskey='" . $sesskey . "' AND expiry>=" . time();
        $row = self::$db_static->getRow($sql);
        return $row['data'];
    }   //end function
    public static function write($sesskey, $data) {

        $qkey = $sesskey;//self::$db_static->qstr($sesskey);
        $expiry = time() + self::$set['lifeTime'];    //设置过期时间

        //写入 SESSION
        $arr = array(
            'sesskey' => $qkey,
            'expiry'  => $expiry,
            'data'    => addslashes($data));

        self::$db_static->query('Replace into session (`sesskey`,`expiry`,`data`) values ("'.$arr['sesskey'].'","'.$arr['expiry'].'","'.$arr['data'].'")');
        return true;
    }   //end function
    public static function destroy($sesskey) {
        $sql = 'DELETE FROM session WHERE sesskey=' . self::$db_static->qstr($sesskey);
        $rs =& self::$db_static->query($sql);
        return true;
    }   //end function
    public static function gc($maxlifetime = null) {

        $sql = "DELETE FROM session WHERE expiry < ".time();
        self::$db_static->execute($sql);
        //由于经常性的对表 sess 做删除操作，容易产生碎片，
        //所以在垃圾回收中对该表进行优化操作。
        if(self::$set['database']['type']=='mysql') {
       		$sql = 'OPTIMIZE TABLE session';
       		self::$db_static->execute($sql);
        }
        return true;
    }   //end function
}


class SessionHandleMemcache
{
	static $mMemcacheObj;
	static $set;
	public static function Init($setting)
	{
		self::$set = $setting;
		$domain = isset($_SERVER['HTTP_HOST'])?strstr($_SERVER['HTTP_HOST'],"."):'';
		if (!class_exists('Memcache'))
        {
            die('Fatal Error:Can not load Memcache extension!');
        }

        if (!empty(self::$mMemcacheObj) && is_object(self::$mMemcacheObj))
        {
        	return false;
        }
        self::_AddMemcacheServers();

		ini_set("session.use_trans_sid", 0);
		ini_set("session.gc_maxlifetime", self::$set['lifeTime']);
		ini_set("session.use_cookies", 1);
		ini_set("session.cookie_path", "/");
		ini_set("session.cookie_domain", $domain);
		session_module_name("user");
		session_set_save_handler(
			array("SessionHandleMemcache", "Open"),
			array("SessionHandleMemcache", "Close"),
			array("SessionHandleMemcache", "Read"),
			array("SessionHandleMemcache", "Write"),
			array("SessionHandleMemcache", "Destroy"),
			array("SessionHandleMemcache", "Gc")
		);
		session_start();
	}
	public static function Open($save_path, $session_name) {
		return true;
	}
	
	public static function Close() {
		return true;
	}
	
	public static function Read($sesskey) {
		return self::$mMemcacheObj->get($sesskey);
	}
	
	public static function Write($sesskey, $data) {
		self::$mMemcacheObj->set($sesskey, $data,0, self::$set['lifeTime']);

		return true;
	}
	
	public static function Destroy($sesskey) {

		self::$mMemcacheObj->delete($sesskey);
		self::$mMemcacheObj->flush();

		return true;
	}
	
	public static function Gc($maxlifetime = null) {
		return true;
	}
	
	public static function _AddMemcacheServers(){
		
		self::$mMemcacheObj = new Memcache;
		if(is_array(self::$set['memcached'])){
			foreach (self::$set['memcached'] as $server){
				self::$mMemcacheObj->addserver($server['mmhost'],$server['mmport']);
			}
		}
	}

}
?>