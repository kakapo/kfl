<?php
include_once ("ApiUser.class.php");
include_once ("Cache.class.php");
class HelpModel extends Model {
	
	var $db;
	
	function HelpModel() {	
	  $this->db=parent::dbConnect ();	
	}
 
	function getGuestBook($pageCount, $pagination) {
	    $select = $this->db->select ();
		$select->from ( "guestbook" ,'*');
		$select->where ( "isdisplay = '1'" );
		$select->order('id DESC');
		if ($pagination !== false) {
			include_once('HTML_Pager.class.php');
			$total = $select->count (); //获得查询到的记录数
			$list ['page'] = new HTML_Pager ( $total, $pageCount ); //创建分页对象
			$offset = $list ['page']->offset (); //获得记录偏移量
			$pagerStyle = array ('firstPage' => 'gray5_12 none', 'prePage' => 'gray5_12 none', 'nextPage' => 'gray5_12 none', 'totalPage' => 'gray5_12 none', 'numBar' => 'gray5_12_orange none hover', 'numBarMain' => 'gray5_12_orange none' ); //翻页条的样式
			$list ['page']->setLinkStyle ( $pagerStyle );
			$list ['page_array'] ['firstPage'] = $list ['page']->firstPage ( '&lt;&lt;' );
			$list ['page_array'] ['prePage'] = $list ['page']->prePage ( '&lt;' );
			$list ['page_array'] ['numBar'] = $list ['page']->numBar ( '7', '', '', '', '' );
			$list ['page_array'] ['nextPage'] = $list ['page']->nextPage ( '&gt;' );
			$list ['page_array'] ['lastPage'] = $list ['page']->lastPage ( '&gt;&gt;' );
			$list ['page_array'] ['preGroup'] = $list ['page']->preGroup ( '...' );
			$list ['page_array'] ['nextGroup'] = $list ['page']->nextGroup ( '...' );
		}
		$select->limit ( $offset, $pageCount );
		$rs = $select->query ();
		$list [ 'record' ] = array();
		if ( $rs )
		{
			foreach ( $rs as $key => $record )
			{
				$uerinfo=ApiUser::getUserByName($record['username']);
				$record['user_nickname']=$uerinfo?$uerinfo['user_name']:'';
				$list[ 'record' ][] = $record;
			}
		}
		return ( array ) $list;
    }
    
    function getAllLink($linktype) {
		$rows = $this->db->getAll ( "select webname,url,logo from weblink where isdisplay = '1' and linktype=$linktype order by sort" );
		return $rows;
	}
	
	function doFeedback($fd_c,$username) {
	  	$sql = "INSERT INTO `guestbook` (`id`, `username`, `content`, `isdisplay`, `restore`, `pubtime`) VALUES
('', '".$username."', '$fd_c', 0, '', '".date("Y-m-d H:i:s")."')";
		$this->db->query ( $sql );
	}

	//获取活动公告
	function getbulletin($num, $pagination)
	{
		$select = $this->db->select ();
		$select->from ( "wiget_bulletin" );
		$select->where ( "isdisplay = '1'" );
		$select->order('istop DESC , sort DESC');
		return $this->getList ( $select, $num, $pagination );
	}
	//获取活动 3张图片
	function getImage($num=3)
	{
		$select = $this->db->select ();
		$select->from ( "wiget_bulletin","id,img_url" );
		$select->where ( "isdisplay = '1' and img_url <>''" );
		$select->order('time DESC , sort DESC');
		$select->limit(0,3);
		return $select->query();
	}
		//获取活动公告
	function getbulletinContent($id)
	{
		$rs['content'] = $this->db->getRow ( "select * from wiget_bulletin where id='{$id}'" );
		$rs['up'] = $this->db->getRow ( "select id,img_url from wiget_bulletin where id<'{$id}'  order by id desc limit 0,1" );
		$rs['down'] = $this->db->getRow ( "select id,img_url from wiget_bulletin where id>'{$id}'  order by id asc limit 0,1" );
		return $rs;
	}
	
	function getList($select, $pageCount = -1, $pagination = false) {
	   if ($pagination !== false) {
			include_once('HTML_Pager.class.php');
			$total = $select->count (); //获得查询到的记录数
			$list ['page'] = new HTML_Pager ( $total, $pageCount ); //创建分页对象
			$offset = $list ['page']->offset (); //获得记录偏移量
			$pagerStyle = array ('firstPage' => 'gray5_12 none', 'prePage' => 'gray5_12 none', 'nextPage' => 'gray5_12 none', 'totalPage' => 'gray5_12 none', 'numBar' => 'gray5_12_orange none hover', 'numBarMain' => 'gray5_12_orange none' ); //翻页条的样式
			$list ['page']->setLinkStyle ( $pagerStyle );
			$list ['page_array'] ['firstPage'] = $list ['page']->firstPage ( '&lt;&lt;' );
			$list ['page_array'] ['prePage'] = $list ['page']->prePage ( '&lt;' );
			$list ['page_array'] ['numBar'] = $list ['page']->numBar ( '7', '', '', '', '' );
			$list ['page_array'] ['nextPage'] = $list ['page']->nextPage ( '&gt;' );
			$list ['page_array'] ['lastPage'] = $list ['page']->lastPage ( '&gt;&gt;' );
			$list ['page_array'] ['preGroup'] = $list ['page']->preGroup ( '...' );
			$list ['page_array'] ['nextGroup'] = $list ['page']->nextGroup ( '...' );
		}
		$select->limit ( $offset, $pageCount );
		$rs = $select->query ();
		$list [ 'record' ] = array();
		if ( $rs )
		{
			foreach ( $rs as $key => $record )
			{
				$list[ 'record' ][] = $record;
			}
		}
		return ( array ) $list;
	}
}

?>