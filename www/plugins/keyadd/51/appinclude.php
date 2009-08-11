<?php
define ( "FIVEONE_OP_API_DOMAIN", "sandbox.api" );
//define("FIVEONE_OP_API_DOMAIN", "api");


require_once 'openapp_51.php';

$appapikey = '����51����ƽ̨�������Ŀʱϵͳ��������key';
$appsecret = '����51����ƽ̨�������Ŀʱϵͳ��������secret';
$OpenApp_51 = new OpenApp_51 ( $appapikey, $appsecret );

//�ú������û��Ƿ��¼�����û�е�¼��ȥ51��վ��¼������¼�򷵻ص�ǰ��¼���û�����ֻ��õ���ǰ��¼�û�������Ե���get_user()����
$user = $OpenApp_51->require_login ();
?>
