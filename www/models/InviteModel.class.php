<?php

/*  运行例子
 if(isset($_FILES)){
 $UploadSet = array('image'  => array('gif|jpg|jpeg|bmp|GIF|JPG',5000));
 $path=$_SERVER['DOCUMENT_ROOT'];
 $source_path=$path."/upload/max/";
 $photo= DoUpload($source_path,$UploadSet);
 }
 */

/**
 * 用于操作CSV文件的类
 *
 */
class InviteModel extends Model {

	public $db;
    public $account_db;
    
	function __construct( $user_db_key ) 
	{   
		$this->db=parent::dbConnect();
		if(!empty($user_db_key)){
	      if ( isset( $GLOBALS [ 'gDataBase' ][ $user_db_key ] ) ) 
		  {
			$this->account_db = parent::dbConnect( $GLOBALS[ 'gDataBase' ][ $user_db_key ] );
		  }else{
			return "database {$user_db_key} not defined!";
		  }
		}
		
	}
	/**
	 * 从一个上传的文件中将数据导入到一个表中
	 *
	 * @$table   	string      数据库中表名
	 * @$f_names   	array    	字段列表
	 * @$csv_post   array    	文件路径
	 * @return  	int         >1代表执行成功，0代表失败或者没有数据插入
	 * @access  	public
	 */

	function uploadFileOfCsv($table = "", $f_names = array(), $csv_post = array(), $filename = "") {

		$flag = 0;
		$db = $this->db;
		$fields_cnt = count ( $f_names );
		if ($fields_cnt != count ( $csv_post ))
			return 3;
		$test2 = array (array () );
		$rownum = 0;

		$fp = fopen ( $filename, "r" );
		while ( $buffer = fgets ( $fp, 4096 ) ) {
			$i ++;
			$tmp_arr = explode ( ",", $buffer );

			if (trim ( $tmp_arr [0] ) == "") {
				echo "<script language='javascript'>";
				echo "alert('第" . $i . "行空，请检查！');";
				echo "</script>";
				$flag = 0;
				return $flag;
			}

			$query = "INSERT INTO  " . $table;
			$query .= "(";

			for($i = 0; $i < $fields_cnt; $i ++) {
				if ($i == 0) {
					$query .= $f_names [$i] . " ";
				} else {
					$query .= " ," . $f_names [$i] . " ";
				}
			} //end for
			$query .= ")";
			$query .= " values ( ";
			for($q = 0; $q < $fields_cnt; $q ++) {
				if ($q == $fields_cnt - 1) {
					$k = $csv_post [$q];
					$tmp = $tmp_arr [$k];
					$query .= "'$tmp');";
				} else {
					$k = $csv_post [$q];
					$tmp = $tmp_arr [$k];
					$query .= "'$tmp',";

				}
			} //end for($q=0;
			$flag = $db->exec ( $query ); //直接调用PDO中的函数
		}
		fclose ( $fp );
		return $flag;
	}

	/**
	 * 以数组的方式将邮件列表导入到数据库中
	 *
	 * @param string $table  表名
	 * @param string $fileds 字段列表，对应数组的维数
	 * @param array $mailist 邮件列表
	 * @param 邮件类型 $mailtype
	 * @return int 是否成功
	 */
	function importMailByArr($cur_userid, $table = "user_invite_email", $fileds = "email_nick,email ", $mailist = array(), $addosValues = '') {
		$flag = 0;
		if (empty ( $table ) or empty ( $fileds ) or empty ( $mailist ) or !is_numeric($cur_userid)) {
			$flag = 3; //传入的参数有空值
			return $flag;
		}else{
		  $db = $this->db;
		  $sql = "";
		  $sql2 = "insert into $table (" . $fileds . ") values ";
		  $temp = "";
		  $name = "";
		  $email = "";
		  for($k = 0; $k < count ( $mailist [0] ); $k ++) {
			if ($k == (count ( $mailist [0] ) - 1)) {
				$name = htmlspecialchars_decode ( addslashes ( $mailist [0] [$k] ) );
				$email = $mailist [1] [$k];
				if (! empty ( $addosValues )) {
					$temp = ",$addosValues";
				}
				if ($this->is_existFreemail ( $cur_userid, $email )) {
					$sql .= substr ( $sql, 0, - 1 ); //去掉逗号
					continue;
				}
				$sql .= "('$name','$email'" . "$temp);";
				break;
			}
			$name = htmlspecialchars_decode( addslashes( $mailist [0] [$k] ) );
			$email = $mailist [1] [$k];
			if ($this->is_existFreemail($cur_userid, $email ) > 0) {
				continue;
			}
			if (! empty ( $addosValues )) {
				$temp = ",$addosValues";
			}
			$sql .= "('$name','$email'" . $temp . "),";
		  }
		  if (empty ( $sql )) {
			return 3; //
		  }
		  if (! empty ( $_SESSION ['qq'] )) {
			$db->query ( "set names 'GBK'" );
		  }
	   	 $flag = $db->query ( $sql2 . $sql, 1 );
		  if ($flag > 0) {
			return 1;
		  }  
		}	
	}

	/**
	 * 判断是否已经存在了当前用户几Email
	 *
	 * @param int $cur_userid
	 * @param String $email
	 * @return unknown
	 */
	function is_existFreemail($cur_userid, $email) {
		if (!is_numeric($cur_userid) or empty ( $email )) {
			return 0;
		}else{
		 $sql = "select user_id   from  user_invite_email    where user_id=".$cur_userid." and email='$email'";
	     $count = $this->db->query ( $sql, 1 );
		 return $count;	
		}
	}

	function is_existFriend($cur_userid, $friend_id) {	
		if(is_numeric($cur_userid)&&is_numeric($friend_id)){
		  $sql = "SELECT  count(*) as cc FROM  user_friend   WHERE user_id=$cur_userid AND friend_id=$friend_id OR $cur_userid=$friend_id ";
		  return $this->account_db->getOne ( $sql );
		}else{
			return 0;
		}	
	}

	/**
	 * Favourites Firends 收藏选中的好友
	 * 要点：只需要构造类似如：INSERT INTO user_friend(`user_id`,`friend_id`)SELECT 3434 , user_id FROM user WHERE user_id IN (434,343
	 * 的sqL语句就可以了
	 *@param  int  $cur_userid 当前用户的ID
	 *@param  array $$friend_ids 要收藏的用户ID数组
	 * @return  返回影响的次数
	 */
	function doFavourite($cur_userid, $friend_ids) {
		$mailstr = "";
		if(is_numeric($cur_userid)){
		  for($i = 0; $i < count ( $friend_ids ); $i ++) {
			$id = 0;
			if ($i == count ( $friend_ids ) - 1) {
				if (! empty ( $friend_ids [$i] )) {
					$id = $friend_ids [$i];
				}
				if ($this->is_existFriend ( $cur_userid, $id ) > 0) {
					continue;
				}
				$mailstr .= "" . $id . "";
				break;
			}
			if (! empty ( $friend_ids [$i] )) {
				$id = $friend_ids [$i];
			}
			if ($this->is_existFriend ( $cur_userid, $id )) {
				continue;
			}
			$mailstr .= "" . $id . ",";
		  }
		 if (empty ( $mailstr )) {
			return 0;
		 }else{
		    $sql = "INSERT INTO user_friend ( `user_id` , `friend_id` , `friend_name`,`friend_nickname`,`friend_gender` )
				 SELECT '" . $cur_userid . "' , user_id, user_name,`user_nickname`,`user_gender` FROM user_extinfo
				 WHERE user_id
				 IN (" . $mailstr . " ) ";
		    return $this->account_db->query ( $sql, 1 );
		 }		
		}else{
			return 0;
		}
	}

	function doDisplayall($cur_userid, $invite_arr) {
		$mailstr = "";
		if(is_numeric($cur_userid)){
		 for($i = 0; $i < count ( $invite_arr ); $i ++) {
			$temp = 0;
			if ($i == count ( $invite_arr ) - 1) {
				if (! empty ( $invite_arr [$i] )) {
					$temp = $invite_arr [$i];
				}
				$mailstr .= "'" . $temp . "'";
				break;
			}
			if (! empty ( $invite_arr [$i] )) {
				$temp = $invite_arr [$i];
			}
			$mailstr .= "'" . $temp . "',";
		 }
		 if (empty ( $mailstr )) {
			return 0;
		 }else{
		    $sql = "update  user_invite_email  set  is_invite=1 WHERE email IN (" . $mailstr . " ) and user_id='$cur_userid'  and im_type<>'sendmail'";
		    return $this->db->query ( $sql );
		 }	 
		}else{
			return 0;
		}
	}

	function doSendmail($user_id, $email, $email_content, $username) {
		$tmp=null;
		if(preg_match("/;/",$email)){
			$tmp = explode ( ";", $email );
		}
		if(preg_match("/,/",$email)){
			$tmp = explode ( ";", $email );
		}
		$sql = "";
		if(is_numeric($user_id)){
		  if (is_array ( $tmp )) {
		   foreach ( $tmp as $key => $v ) {
		     $sql .= "insert into user_invite_email  (user_id,email,email_content,user_name,im_type,is_invite) values('$user_id','$v','$email_content','$username','other',1);";
			}
		 } else {
			$sql .= "insert into user_invite_email  (user_id,email,email_content,user_name,im_type,is_invite) values('$user_id','$email','$email_content','$username','other',1);";
		 }
		$flag = $this->db->exec ( $sql );
		return $flag;
			
		}else{
		  return false;
		}
	}

	/**
	 * 获得 通讯录中已经再果动网注册的用户列表
	 */
	function getHavedMailUser($mailstr, $cur_userid) {
		$re = array ();
		if (empty ( $mailstr ) or empty ( $cur_userid )||!is_numeric($cur_userid)) {
			return $re;
		}else{			
		 $sql = "SELECT a.user_id as userid,a.user_name as username,a.user_email as useremail,a.user_nickname as nickname,";
		 $sql .= "b.user_gender as gender,b.user_province as province,b.user_pic";
		 $sql .= " FROM user a left join user_extinfo b on a.user_id=b.user_id";
		 $sql .= " WHERE a.user_email IN (" . $mailstr . ") AND  a.user_id<>$cur_userid";
		 return $this->account_db->getAll ( $sql );
		}
	}
	/**
	 * 获取已收藏却已注册的用户列表
	 */
	function getRegHaveFrd($userid_str, $cur_userid) {
		$re = array ();
		if (empty ( $userid_str ) or empty ( $cur_userid )||!is_numeric($cur_userid)) {
			return $re;
		}else{
		 $sql = "select friend_id  from user_friend where  user_id=$cur_userid and friend_id in  (" . $userid_str . ")";
		 return $this->account_db->getAll ( $sql );	
		}
	}

	function getQQMailById($user_id, $count) {
		if (empty ( $count ) or empty ( $user_id )||!is_numeric($user_id)) {
			return null;
		}else{
		   $sql = "SELECT email_nick ,email FROM `user_invite_email`  WHERE user_id =$user_id   ORDER BY  `id` DESC LIMIT $count     ";
		   return $this->db->getAll ( $sql );
		}
	}

	function getQQMailByAddress($user_id, $str) {
		if (empty ( $str ) or empty ( $user_id )||!is_numeric($user_id)) {
			return array ();
		}else{
			$sql = "SELECT email_nick ,email FROM `user_invite_email`  WHERE user_id =$user_id   AND email IN (" . $str . ")  ";
		    return $this->db->getAll ( $sql );
		}
	}

	/**
	 * 发送多人邮件的函数，暂时不能发送附件
	 *
	 * @param array $to
	 * @return int
	 */
	function send_multymail($to = array(), $subject2 = "", $content = "", $from = "") {

		$loc_host = $GLOBALS ['account'] ['pop3_host']; //外部邮件pop3地址
		$smtp_host = $GLOBALS ['account'] ['smtp_host']; //外部邮件smtp地址
		$smtp_acc = $GLOBALS ['account'] ['smtp_account']; //发送邮件的帐号
		$smtp_pass = $GLOBALS ['account'] ['smtp_pass']; //发送帐号的密码
		$subject2 = $GLOBALS ['account'] ['subject'];

		if (! is_array ( $to )) {
			return 0;
		}

		//$subject= iconv("utf-8","gb2312",$subject2);
		//if(preg_match("/gmail.com/",$to)){
		$subject = $subject2;
		//}


		if (empty ( $loc_host ) or empty ( $smtp_host ) or empty ( $smtp_acc ) or empty ( $smtp_pass )) {
			return "5";
		}

		$bdy = array ($content );

		$headers = "Content-type: text/html; charset=gb2312";
		$lb = "\r\n"; //linebreak
		$hdr = explode ( $lb, $headers );

		$smtp = array (array ("EHLO " . $loc_host . $lb, "995,2620", "HELO error: " ), array ("AUTH LOGIN" . $lb, "334", "AUTH error:" ), array (base64_encode ( $smtp_acc ) . $lb, "334", "AUTHENTIFICATION error : " ), array (base64_encode ( $smtp_pass ) . $lb, "235", "AUTHENTIFICATION error : " ) );

		$smtp [] = array ("MAIL FROM: <" . $from . ">" . $lb, "250", "MAIL FROM error: " );
		foreach ( $to as $v ) {
			$smtp [] = array ("RCPT TO: <" . $v . ">" . $lb, "250", "RCPT TO error: " );
		}
		foreach ( $to as $v ) {
			$smtp [] = array ("To: " . $v . $lb, "", "" );
		}
		$smtp [] = array ("DATA" . $lb, "354", "DATA error: " );
		$smtp [] = array ("From: " . $from . $lb, "", "" );
		$smtp [] = array ("Subject: " . $subject . $lb, "", "" );
		//	$smtp[] = array ("To: " . $to . $lb,"","");


		foreach ( $hdr as $h ) {
			$smtp [] = array ($h . $lb, "", "" );
		}
		$smtp [] = array ($lb, "", "" );
		if (! empty ( $bdy )) {
			foreach ( $bdy as $b ) {
				$smtp [] = array ($b . $lb . $lb, "", "" );
			}
		}

		$smtp [] = array ("." . $lb, "250", "DATA(end)error: " );
		$smtp [] = array ("QUIT" . $lb, "221", "QUIT error: " );

		$fp = @fsockopen ( $smtp_host, 25 );
		if (! $fp)
			return "6"; //"<b>发生错误如下：</b>未能连接到主机： " . $smtp_host . "<br>";
		while ( $result = @ fgets ( $fp, 1024 ) ) {
			if (@substr ( $result, 3, 1 ) == " ") {
				break;
			}
		}

		foreach ( $smtp as $req ) {
			if (empty ( $req [0] ))
				$req [0] = "EHLO mail.tsong.cn";
			@fputs ( $fp, $req [0] );

			if ($req [1]) {
				while ( $result = @fgets ( $fp, 1024 ) ) {
					if (substr ( $result, 3, 1 ) == " ") {
						break;
					}
				}
				;
				if (! strstr ( $req [1], @substr ( $result, 0, 3 ) )) {
					if (empty ( $req [2] ))
						$req [2] = "";
					// $result_str .= $req[2] . $result . "<br>";
				}
			}
		}

		@fclose ( $fp );
		return "1";
	}



/**
 * 发送邮件，可以防止被列入垃圾邮件
 *
 * @param string $from  	发件人
 * @param mix    $to  	 	收件人可以是数组，也可以是以个字符串，数组的话可以给多个人同时发送邮件，如果是字符串的话，只能给一个人发
 * @param string $subject   主题
 * @param string $message   邮件内容
 * @return mix   			如果返回数组，将返回发送的相关信息,如果是一个字符串，返回错误信息
 */

   function sendMail_080928($from = "no-reply@guodong.com", $to, $subject, $message) {
	//var_export($to)."<br>";
	/*  your configuration here  */
	$subject = iconv ( "utf-8", "gb2312", $subject );
	$message = iconv ( "utf-8", "gb2312", $message );

	$smtpServer = $GLOBALS ['account'] ['smtp_host']; //ip accepted as well
	$port = "25"; // should be 25 by default
	$timeout = "30"; //typical timeout. try 45 for slow servers
	$username = $GLOBALS ['account'] ['smtp_account']; //"no-reply@guodong.com"; //the login for your smtp
	$password = $GLOBALS ['account'] ['smtp_pass']; //"tsong-0810"; //the pass for your smtp
	$localhost = "127.0.0.1"; //this seems to work always
	$newLine = "\r\n"; //var just for nelines in MS
	$secure = 0; //change to 1 if you need a secure connect


	/*  you shouldn't need to mod anything else */

	//connect to the host and port
	$smtpConnect = fsockopen ( $smtpServer, $port, $errno, $errstr, $timeout );
	$smtpResponse = fgets ( $smtpConnect, 4096 );
	if (empty ( $smtpConnect )) {
		$output = "Failed to connect: $smtpResponse";
		return 2;
	} else {
		$logArray ['connection'] = "Connected to: $smtpResponse";
	}

	//say HELO to our little friend
	fputs ( $smtpConnect, "HELO $smtpServer" . $newLine );
	$smtpResponse = fgets ( $smtpConnect, 4096 );
	$logArray ['heloresponse'] = "$smtpResponse";

	//start a tls session if needed
	if ($secure) {
		fputs ( $smtpConnect, "STARTTLS" . $newLine );
		$smtpResponse = fgets ( $smtpConnect, 4096 );
		$logArray ['tlsresponse'] = "$smtpResponse";

		//you have to say HELO again after TLS is started
		fputs ( $smtpConnect, "HELO $smtpServer" . $newLine );
		$smtpResponse = fgets ( $smtpConnect, 4096 );
		$logArray ['heloresponse2'] = "$smtpResponse";
	}

	//request for auth login
	fputs ( $smtpConnect, "AUTH LOGIN" . $newLine );
	$smtpResponse = fgets ( $smtpConnect, 4096 );
	$logArray ['authrequest'] = "$smtpResponse";

	//send the username
	fputs ( $smtpConnect, base64_encode ( $username ) . $newLine );
	$smtpResponse = fgets ( $smtpConnect, 4096 );
	$logArray ['authusername'] = "$smtpResponse";

	//send the password
	fputs ( $smtpConnect, base64_encode ( $password ) . $newLine );
	$smtpResponse = fgets ( $smtpConnect, 4096 );
	$logArray ['authpassword'] = "$smtpResponse";

	//email from
	fputs ( $smtpConnect, "MAIL FROM: $from" . $newLine );
	$smtpResponse = fgets ( $smtpConnect, 4096 );
	$logArray ['mailfromresponse'] = "$smtpResponse";

	//email to
	if (is_array ( $to )) {
		foreach ( $to as $key => $v ) {
			fputs ( $smtpConnect, "RCPT TO: $v" . $newLine );
			$smtpResponse = fgets ( $smtpConnect, 4096 );
			$logArray ['mailtoresponse'] [] = "$smtpResponse";
		}
	} else {
		fputs ( $smtpConnect, "RCPT TO: $to" . $newLine );
		$smtpResponse = fgets ( $smtpConnect, 4096 );
		$logArray ['mailtoresponse'] = "$smtpResponse";
	}

	//the email
	fputs ( $smtpConnect, "DATA" . $newLine );
	$smtpResponse = fgets ( $smtpConnect, 4096 );
	$logArray ['data1response'] = "$smtpResponse";

	//construct headers
	$headers = "MIME-Version: 1.0" . $newLine;
	$headers .= "Content-type: text/html; charset=gb2312" . $newLine;
	if (is_array ( $to )) {
		for($i = 1; $i < count ( $to ); $i ++) {
			$headers .= "To:  <" . $to [$i] . ">" . $newLine;
		}

	} else {
		// $headers .= "To:s <".$to.">" . $newLine;
	}
	// $headers .= "From:  <$from>" . $newLine;
	if (is_array ( $to )) {
		//observe the . after the newline, it signals the end of message
		fputs ( $smtpConnect, "To: " . $to [0] . "\r\nFrom: $from\r\nSubject: $subject\r\n$headers\r\n\r\n$message\r\n.\r\n" );
		$smtpResponse = fgets ( $smtpConnect, 4096 );
		$logArray ['data2response'] [] = "$smtpResponse";
	} else {
		fputs ( $smtpConnect, "To: " . $to . "\r\nFrom: $from\r\nSubject: $subject\r\n$headers\r\n\r\n$message\r\n.\r\n" );
		$smtpResponse = fgets ( $smtpConnect, 4096 );
		$logArray ['data2response'] [] = "$smtpResponse";
	}

	// say goodbye
	fputs ( $smtpConnect, "QUIT" . $newLine );
	$smtpResponse = fgets ( $smtpConnect, 4096 );
	$logArray ['quitresponse'] = "$smtpResponse";
	$logArray ['quitcode'] = substr ( $smtpResponse, 0, 3 );
	fclose ( $smtpConnect );
	//a return value of 221 in $retVal["quitcode"] is a success
	return 1;
}



	/**
	 * 上传操作
	 *
	 * @param string $updir
	 * @param array $UploadSet
	 * @return $upload_url
	 */
	function DoUpload($updir = "", $UploadSet = array(), $file_field) {

		$upload_url = "";
		$sAllowExt = $UploadSet ['image'] [0];
		$sAllowSize = $UploadSet ['image'] [1];

		//任何时候都不允许上传PHP文件
		$sAllowExt = str_replace ( "php", "", $sAllowExt );
		$tmp_name = $_FILES ["tmp_name"];
		$name = basename ( $_FILES [$file_field] ['name'] );

		$uploadfile = $updir . basename ( $_FILES [$file_field] ['name'] );
		$ExtName = substr ( $name, strrpos ( $name, "." ) + 1 );

		if (CheckValidExt ( $sAllowExt, $ExtName )) {
			if (move_uploaded_file ( $_FILES [$file_field] ['tmp_name'], $uploadfile )) {
				$upload_url = $name;
			} else {
				$upload_url = "0";
			}

		} else {
			$upload_url = "type"; //"<script>alert('请选择有效的文件！
		}
		return $upload_url;

	}

	//检测扩展名的有效性
	function CheckValidExt($sAllowExt, $sExt) {
		$aExt = explode ( "|", $sAllowExt );
		if (in_array ( $sExt, $aExt )) {
			Return 1;
		} else {
			Return 0;
		}
	}

	//获得 还有几个邀请码没有使用
	function getNOInviteCount($user_id) {
		if (empty ( $user_id )||!is_numeric($user_id)) {
			return 0;
		}else{
		    $sql = "select  count(*) as cc   from  invite_code_url  where user_id='$user_id'   and state=0 ";
		    return $this->db->getOne ( $sql );	
		}
	}

	//获得链接邀请的数量限制
	function getInviteLimit($user_id) {
		if (empty ( $user_id )||!is_numeric($user_id)) {
			return 0;
		}else{
			$sql = "select  user_invite_limit  from  user_extinfo where user_id='$user_id' limit 0, 1";
		   return $this->account_db->getOne ( $sql );
		}
	}

	function generateCode($length = 5) {
		$chars = "abcdefghijklmnopqrstuvwxyz0123456789";
		$code = "";
		while ( strlen ( $code ) < $length ) {
			$code .= $chars [mt_rand ( 0, strlen ( $chars ) )];
		}
		return $code;
	}

	function generateNumber($length = 2) {
		$chars = "0123456789";
		$code = "";
		while ( strlen ( $code ) < $length ) {
			$code .= $chars [mt_rand ( 0, strlen ( $chars ) )];
		}
		return $code;
	}

	function getRegCode($user_id) {
		$content = "b" . $this->generateCode ( 5 ) . $this->generateNumber ( 2 );
		if(is_numeric($user_id)){	
		 $sql = "insert into invite_code_url(content,user_id,state,time) values ('{$content}','{$user_id}','0','" . time () . "');";
		 $this->db->query ( $sql );
		 $sqll = "select user_invite_limit from  user_extinfo where  user_id='$user_id'  limit 0,1";
		 $count = $this->account_db->getOne ( $sqll );
		 if ($count > 0) {
			$sql2 = "update user_extinfo  set   user_invite_limit =user_invite_limit-1  where user_id='$user_id';";
			$this->account_db->query ( $sql2 );
		 }
		}
		return $content;
	}

	function updateRegCode($code, $user_name = "") {
		$sql = " update invite_code_url  set  state=1 where  content='$code';";
		$this->db->query ( $sql );
	}

	function getRegCodeState($code) {
		if(!is_blank($code)){
		  $sql = " select state from invite_code_url   where  content='$code'";
		  return $this->db->getOne ( $sql );
		}else{
		  return false;
		}
	}

	function getHistoryInvUrl($user_id) {
		if(is_numeric($user_id)){
		  $sql = "select content  from invite_code_url  where user_id='$user_id' and state=0";
		  return $this->db->getAll ( $sql );
		}else{
		  return false;
	    }
	}

	function getInviteUrl($limit, $user_id) {
		$url = array ();
		if(is_numeric($user_id)){
		  for($i = 1; $i <= $limit; $i ++) {
			$url [] = $this->getRegCode ( $user_id );
		  }
		}
		return $url;
	}

	function log2($event = null) {
		$now = date ( "Y-M-d H:i:s" );
		$fd = fopen ( APP_TEMP_DIR . "invite_log.html", 'w' );
		$log = $now . " " . $_SERVER ["REMOTE_ADDR"] . " - $event <br>";
		fwrite ( $fd, $log );
	}

}

?>