<?php

include_once ("plugins/Uploader.php");
class Original extends Model
{  
	private $main;
	
	function Original()
	{
		$this->main=parent::dbConnect();
		$this->works_dir = getcwd().'/../image/images/upload/user_works/';
	}

//获取列表
	private function _getList( $select , $pageCount = -1 , $pagination = false )
	{
		//是否获得分页对象
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
		//echo $select->getSql();//显示运行的sql语句，调试使用
		return ( array ) $list;
	}
	
	function getdatalist($type=0,$num = '', $pagination)
	{
		$select = $this->main->select ();
		$select->from ( "works_upload" );
		$select->where ( "ws_type =".$type );
		$select->where ( "ws_check = 1" );
		$select->order ( "ws_uploadtime desc" );
		return $this->_getList ( $select, $num, $pagination );
	}
	
	function origUpload($myfile)
	{
	  $upload = new HTTP_Uploader;
	  $upload->setAllowExtension('all');
	  $upload->setMaxSize('3MB');
	  $upload->setNamingRule(1);
	  if(!is_dir($this->works_dir)){
	  	create_dir($this->works_dir);
	  }
	  $upload->setSaveDir($this->works_dir);//设置存储路径  
	  if(!$upload->upload($myfile))
	  {			
		return false;
	  }else{	 
		return $upload->result();			    
	  }  
	}
	
	function abbrUpload($myfile)
	{
	  $upload = new HTTP_Uploader;
	  $upload->setAllowExtension('all');
	  $upload->setMaxSize('1MB');
	  $upload->setNamingRule(1);
	  if(!is_dir($this->works_dir)){
	  	create_dir($this->works_dir);
	  }
	  $upload->setSaveDir($this->works_dir);//设置存储路径
	  if(!$upload->upload($myfile))
	  {			
		return false;
	  }else{	 
		return $upload->result();			    
	  }  
	}
   
	function createWorks($myarray)
	{
		return $this->main->insert($myarray,'works_upload');
	}
	
		
}