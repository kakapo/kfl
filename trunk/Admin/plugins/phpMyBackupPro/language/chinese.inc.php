<?php
/*
 +--------------------------------------------------------------------------+
 | phpMyBackupPro                                                           |
 +--------------------------------------------------------------------------+
 | Copyright (c) 2004-2007 by Dirk Randhahn                                 |                               
 | http://www.phpMyBackupPro.net                                            |
 | version information can be found in definitions.php.                     |
 |                                                                          |
 | This program is free software; you can redistribute it and/or            |
 | modify it under the terms of the GNU General Public License              |
 | as published by the Free Software Foundation; either version 2           |
 | of the License, or (at your option) any later version.                   |
 |                                                                          |
 | This program is distributed in the hope that it will be useful,          |
 | but WITHOUT ANY WARRANTY; without even the implied warranty of           |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            |
 | GNU General Public License for more details.                             |
 |                                                                          |
 | You should have received a copy of the GNU General Public License        |
 | along with this program; if not, write to the Free Software              |
 | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307,USA.|
 +--------------------------------------------------------------------------+
*/

/*basic data*/
define('BD_LANG_SHORTCUT',"ch"); // used for the php function setlocale() (http://www.php.net/setlocale)
define('BD_DATE_FORMAT',"%x %X"); // used for the php function strftime() (http://www.php.net/strftime)
define('BD_CHARSET_HTML',"UTF-8"); // the charset used in you language for html
define('BD_CHARSET_EMAIL',"GBK"); // the charset used in your langauge for MIME-emails

/*functions.inc.php*/
define('F_START',"开始");
define('F_CONFIG',"配置");
define('F_IMPORT',"导入");
define('F_BACKUP',"备份");
define('F_SCHEDULE',"计划备份");
define('F_DB_INFO',"数据库信息");
define('F_SQL_QUERY',"sql 查询");
define('F_HELP',"帮助");
define('F_LOGOUT',"登出");
define('F_FOOTER',"访问%sphpMyBackupPro project site%s 获取最新版本和新闻.");
define('F_NOW_AVAILABLE',"phpMyBackupPro有新版本更新了，访问 %s".PMBP_WEBSITE."%s");
define('F_SELECT_DB',"选择备份数据库");
define('F_SELECT_ALL',"全选");
define('F_COMMENTS',"备注");
define('F_EX_TABLES',"导出数据表");
define('F_EX_DATA',"导出数据");
define('F_EX_DROP',"添加 'drop table'");
define('F_EX_COMP',"压缩");
define('F_EX_OFF',"无");
define('F_EX_GZIP',"gzip");
define('F_EX_ZIP',"zip");
define('F_DEL_FAILED',"备份删除失败 %s");
define('F_FTP_1',"FTP 无法连接到服务器");
define('F_FTP_2',"用户无效");
define('F_FTP_3',"FTP 上传失败");
define('F_FTP_4',"文件上传成功");
define('F_FTP_5',"FTP 删除文件 '%s' 失败");
define('F_FTP_6',"文件 '%s' 在FTP服务器上删除成功");
define('F_FTP_7',"文件 '%s' 在FTP服务器上不可用");
define('F_MAIL_1',"接收者邮件错误");
define('F_MAIL_2',"邮件发送成功 by phpMyBackupPro ".PMBP_VERSION." ".PMBP_WEBSITE."");
define('F_MAIL_3',"无法读取");
define('F_MAIL_4',"MySQL 从备份");
define('F_MAIL_5',"邮件发送失败");
define('F_MAIL_6',"文件已经成功被发送到");
define('F_YES',"是");
define('F_NO',"否");
define('F_DURATION',"耗时");
define('F_SECONDS',"秒");

/*index.php*/
define('I_SQL_ERROR',"出错: 请在 '配置' 输入正确的 MySQL 资料!");
define('I_NAME',"phpMyBackupPro 最新版本 ");
define('I_WELCOME',"phpMyBackupPro是免费软件基于GNU GPL协议.<br>
需要帮助请访问 %s.<br><br>
下一步操作可以从顶部菜单开始! 第一次使用 phpMyBackupPro, 你应该从配置开始!
'export'目录和'global_conf.php'文件的权限请设置为 0777.");
define('I_CONF_ERROR',"此文件 ".PMBP_GLOBAL_CONF." 不可写!");
define('I_DIR_ERROR',"此目录".PMBP_EXPORT_DIR." 不可写!");
define('PMBP_I_INFO',"系统信息");
define('PMBP_I_SERVER',"web服务器");
define('PMBP_I_TIME',"时间");
define('PMBP_I_PHP_VERS',"PHP 版本");
define('PMBP_I_MEM_LIMIT',"PHP 内存限制");
define('PMBP_I_SAFE_MODE',"安全模式打开");
define('PMBP_I_FTP',"FTP 传输打开");
define('PMBP_I_MAIL',"邮件发送");
define('PMBP_I_GZIP',"gzip 压缩支持");
define('PMBP_I_SQL_SERVER',"MySQL 服务器");
define('PMBP_I_SQL_CLIENT',"MySQL 客户端");
define('PMBP_I_NO_RES',"*无法恢复*");
define('PMBP_I_LAST_SCHEDULED',"上次计划备份");
define('PMBP_I_LAST_LOGIN',"上次登录");
define('PMBP_I_LAST_LOGIN_ERROR',"上次失败登录");

/*config.php*/
define('C_SITENAME',"站名");
define('C_LANG',"语言");
define('C_SQL_HOST',"MySQL 主机");
define('C_SQL_USER',"MySQL 用户名");
define('C_SQL_PASSWD',"MySQL 密码");
define('C_SQL_DB',"指定数据库");
define('C_FTP_USE',"通过FTP备份?");
define('C_FTP_BACKUP',"使用本地备份?");
define('C_FTP_REC',"递归备份?");
define('C_FTP_SERVER',"FTP 服务器 (url or IP)");
define('C_FTP_USER',"FTP 用户名");
define('C_FTP_PASSWD',"FTP 密码");
define('C_FTP_PATH',"FTP 路径");
define('C_FTP_PASV',"使用被动模式?");
define('C_FTP_PORT',"FTP 端口");
define('C_FTP_DEL',"在FTP服务器上删除文件");
define('C_EMAIL_USE',"邮件通知?");
define('C_EMAIL',"邮箱地址");
define('C_STYLESHEET',"风格");
define('C_DATE',"日期格式");
define('C_DEL_TIME',"多少天后删除本地备份");
define('C_DEL_NUMBER',"每个数据库只能存储最大文件数");
define('C_TIMELIMIT',"php 时间限制");
define('C_IMPORT_ERROR',"显示导入错误?");
define('C_NO_LOGIN',"登录关闭?");
define('C_LOGIN',"HTTP 认证?");
define('C_DIR_BACKUP',"开启本地备份?");
define('C_DIR_REC',"支持多级目录备份?");
define('C_CONFIRM',"确认");
define('C_CONFIRM_1',"清空, 删除, 导入");
define('C_CONFIRM_2',"... 所有");
define('C_CONFIRM_3',"... 所有");
define('C_CONFIRM_4',"不需要确认");

define('C_BASIC_VAL',"基本配置");
define('C_EXT_VAL',"扩展配置");
define('PMBP_C_SYSTEM_VAL',"系统变量");
define('PMBP_C_SYS_WARNING',"这些系统变量由phpMyBackupPro使用. 别改动除非你知道它是干什么的!");
define('C_TITLE_SQL',"SQL 数据");
define('C_TITLE_FTP',"FTP 设置");
define('C_TITLE_EMAIL',"备份通知邮件");
define('C_TITLE_STYLE',"phpMyBackupPro风格");
define('C_TITLE_DELETE',"自动删除备份文件");
define('C_TITLE_CONFIG',"更多配置项");
define('C_WRONG_TYPE',"不正确!");
define('C_WRONG_SQL',"MySQL 资料不正确!");
define('C_WRONG_DB',"MySQL 数据库名不正确!");
define('C_WRONG_FTP',"FTP 资料不正确!");
define('C_OPEN',"无法打开");
define('C_WRITE',"不能写到");
define('C_SAVED',"数据成功保存");
define('C_WRITEABLE',"不可写");
define('C_SAVE',"保存数据");

/*import.php*/
define('IM_ERROR',"%d 错误出现. 你可以使用 '清空数据库' 来确保没有任何表存在.");
define('IM_SUCCESS',"成功导入");
define('IM_TABLES',"表 和");
define('IM_ROWS',"行数");

define('B_EMPTIED_ALL',"所有数据库都被清空");
define('B_EMPTIED',"此数据库已经被清空");
define('B_DELETED',"此文件已经被删除");
define('B_DELETED_ALL',"所有文件都被删除");
define('B_NO_FILES',"当前没有备份文件");
define('B_DELETE_ALL_2',"删除所有备份");
define('B_IMPORT_ALL',"导入所有备份");
define('B_EMPTY_ALL',"清空所有数据库");
define('B_EMPTY_DB',"清空数据库");
define('B_DELETE_ALL',"清空所有备份");
define('B_INFO',"信息");
define('B_VIEW',"显示");
define('B_DOWNLOAD',"下载");
define('B_IMPORT',"导入");
define('B_IMPORT_FRAG',"分块执行");
define('B_DELETE',"删除");
define('B_CONF_EMPTY_DB',"你真的想清空数据库吗?");
define('B_CONF_DEL_ALL',"你真的想删除此数据库的所有备份文件吗?");
define('B_CONF_IMP',"你真的想导入此备份文件吗?");
define('B_CONF_DEL',"你真的想删除此备份文件吗?");
define('B_CONF_EMPT_ALL',"你真的想清空所有数据库吗?");
define('B_CONF_IMP_ALL',"你真的想导入所有最后的备份文件吗?");
define('B_CONF_DEL_ALL_2',"你真的想删除所有备份?");
define('B_LAST_BACKUP',"最后备份在");
define('B_SIZE_SUM',"所有备份文件大小");

/*backup.php*/
define('EX_SAVED',"文件已经被成功保存");
define('EX_NO_DB',"无选择数据库");
define('EX_EXPORT',"备份");
define('EX_NOT_SAVED',"无法保存备份数据库 %s 在 '%s'");
define('EX_DIRS',"请选择目录备份到FTP服务器");
define('EX_DIRS_MAN',"输入更多的相对 phpMyBackupPro根目录的路径.<br>用'|'分隔");
define('EX_PACKED',"打包成ZIP文件");
define('PMBP_EX_NO_AVAILABLE',"数据库 %s 不可用");
define('PMBP_EXS_UPDATE_DIRS',"更新目录列表");
define('PMBP_EX_NO_ARGV',"举例:\n$ php backup.php db1,db2,db3
需要更多例子请在 'documentation' 目录阅读 'SHELL_MODE.txt' ");

/*scheduled.php*/
define('EXS_PERIOD',"选择之前的备份");
define('EXS_PATH',"选择PHP文件存放目录");
define('EXS_BACK',"返回");
define('PMBP_EXS_ALWAYS',"在每次访问");
define('EXS_HOUR',"小时");
define('EXS_HOURS',"小时");
define('EXS_DAY',"天");
define('EXS_DAYS',"天");
define('EXS_WEEK',"周");
define('EXS_WEEKS',"周");
define('EXS_MONTH',"月");
define('EXS_SHOW',"显示脚本");
define('PMBP_EXS_INCL',"在你准备备份时引入这个脚本PHP file (%s)");
define('PMBP_EXS_SAVE',"还是保存当前脚本成新的文件(将会覆盖已经存在的文件)!)");

/*file_info.php*/
define('INF_INFO',"信息");
define('INF_DATE',"日期");
define('INF_DB',"数据库");
define('INF_SIZE',"备份大小");
define('INF_COMP',"是否压缩");
define('INF_DROP',"包含 'drop table'");
define('INF_TABLES',"包含数据表");
define('INF_DATA',"包含数据");
define('INF_COMMENT',"备注");
define('INF_NO_FILE',"没选择文件");

/*db_status.php*/
define('DB_NAME',"数据库名");
define('DB_NUM_TABLES',"数据表数");
define('DB_NUM_ROWS',"行数");
define('DB_SIZE',"大小");
define('DB_DIFF',"备份文件大小不一!");
define('DB_NO_DB',"无数据库可用");
define('DB_TABLES',"数据表信息");
define('DB_TAB_TITLE',"表信息 ");
define('DB_TAB_NAME',"表名");
define('DB_TAB_COLS',"字段数");

/*sql_query.php*/
define('SQ_ERROR',"出现错误在行");
define('SQ_SUCCESS',"成功执行");
define('SQ_RESULT',"查询结果");
define('SQ_AFFECTED',"影响行数");
define('SQ_WARNING',"注意: 这个页面只是提供简单的SQL语句查询.不小心就会删除掉数据库!");
define('SQ_SELECT_DB',"选择数据库");
define('SQ_INSERT',"输入sql查询语句：");
define('SQ_FILE',"上传SQL文件");
define('SQ_SEND',"运行");

/*login.php*/
define('LI_MSG',"请输入 (MySQL 用户名和密码)");
define('LI_USER',"用户名");
define('LI_PASSWD',"密码");
define('LI_LOGIN',"登录");
define('LI_LOGED_OUT',"安全退出!");
define('LI_NOT_LOGED_OUT',"非安全退出!<br>要安全退出请输入错误的密码");

/*big_import.php*/
define('BI_IMPORTING_FILE',"正在导入文件");
define('BI_INTO_DB',"到数据库");
define('BI_SESSION_NO',"Session 编号");
define('BI_STARTING_LINE',"开始于行");
define('BI_STOPPING_LINE',"停止于行");
define('BI_QUERY_NO',"执行查询数");
define('BI_BYTE_NO',"已处理字节数");
define('BI_DURATION',"最后会话耗时");
define('BI_THIS_LAST',"当前会话");
define('BI_END',"到达文件结尾，导入OK");
define('BI_RESTART',"重新导入文件");
define('BI_SCRIPT_RUNNING',"当前脚本还在执行!<br>请耐心等待");
define('BI_CONTINUE',"继续于行");
define('BI_ENABLE_JS',"开启JavaScript来自动继续执行");
define('BI_BROKEN_ZIP',"此ZIP文件好像被损坏");
define('BI_WRONG_FILE',"在行 %s停止.<br>当前查询执行了超过%s行. 此情况发生的原因可能是
你采用其它的工具备份的文件在每一个查询语句的结尾没有用';'分行, 或者是你的备份文件有其他的插入数据。");
?>
