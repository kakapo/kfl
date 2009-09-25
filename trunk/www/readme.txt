前台页面
1、注册页面
2、登录页面
3、密码找回页面

API开发
1、注册接口
2、登录接口
3、信息查询接口

工具开发
1、创建用户散列表 substr(md5(user_email),0,2) 256 tables
2、用户数据导入
3、用户数据备份

数据结构
user_id int 11 primary
user_email varchar 64 unique
user_password char 32
user_name varchar 16 index
user_nickname varchar 12 option
user_state tiny 1
user_reg_time int 11
user_reg_ip varchar 16
user_lastlogin_time int 11
user_lastlogin_ip varchar 16