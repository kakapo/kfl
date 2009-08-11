<?php
/*
进阶道具类
*/

class ShareModel extends Model {
	var $show_db;

	function ShareModel() {
		$this->show_db = parent::dbConnect ( $GLOBALS ['gDataBase'] ['show'] );
		$this->show_db->cacheDir = APP_TEMP_DIR . "/database";
	}

	//进阶道具列表
	function getItemList($num, $pagination, $search) {
		$select = $this->show_db->select ();
		$select->from ( "share_3ditems" );
		$select->where ( "promotion_type = '{$search['promotion_type']}'" );
		$select->where ( "item_type = '{$search['item_type']}'" );
		$select->order ( "id desc" );
		return $this->getList ( $select, $num, $pagination, $search );
	}

	function getList($select, $pageCount = -1, $pagination = false, $search) {

		//是否获得分页对象
		$offset = '';
		if ($pagination !== false) {
			include_once ("Pager.php");
			$total = $select->count (); //获得查询到的记录数
			$list ['page'] = new HTML_Pager ( $total, $pageCount ); //创建分页对象
			$offset = $list ['page']->offset (); //获得记录偏移量


			$pagerStyle = array ('firstPage' => '', 'prePage' => '', 'nextPage' => '', 'totalPage' => '', 'numBar' => 'noneline gray9_10 ed', 'numBarMain' => 'noneline gray9_10 ' ); //翻页条的样式
			$list ['page']->setLinkStyle ( $pagerStyle );

			$list ['page']->setLinkScript ( "Post_getitem(\"{$search['promotion_type']}\",\"{$search['item_type']}\",@PAGE@);" );

			$list ['page_array'] ['firstPage'] = $list ['page']->firstPage ( '<img src="' . $GLOBALS ['gSiteInfo'] ['image_site_url'] . '/images/component/min_component/page_preend.gif" />' );
			$list ['page_array'] ['prePage'] = $list ['page']->prePage ( '<img src="' . $GLOBALS ['gSiteInfo'] ['image_site_url'] . '/images/component/diylist/pagenum_p.jpg" border="0" />' );
			$list ['page_array'] ['numBar'] = $list ['page']->numBar ( '7', ' ', ' ', ' ', ' ' );
			$list ['page_array'] ['nextPage'] = $list ['page']->nextPage ( '<img src="' . $GLOBALS ['gSiteInfo'] ['image_site_url'] . '/images/component/diylist/pagenum_n.jpg" border="0" />' );
			$list ['page_array'] ['lastPage'] = $list ['page']->lastPage ( '<img src="' . $GLOBALS ['gSiteInfo'] ['image_site_url'] . '/images/component/min_component/page_nextend.gif" />' );
			$list ['page_array'] ['preGroup'] = $list ['page']->preGroup ( '...' );
			$list ['page_array'] ['nextGroup'] = $list ['page']->nextGroup ( '...' );
		}
		$select->limit ( $offset, $pageCount );

		$rs = $select->cacheQuery ( - 1, $select->getSql () . $offset );

		if ($rs) {
			foreach ( $rs as $key => $record ) {
				$list ['record'] [] = $record;
			}
		}
		//echo $select->getSql();
		//$this->show_db->showDebug();  //显示运行的sql语句，调试使用
		return ( array ) $list;
	}

	function getShowUserModifyTime($user_id) {
		return $this->show_db->getRow ( 'select modify_times,user_dress,operation from show_user where user_id=' . $user_id );
	}
}
?>