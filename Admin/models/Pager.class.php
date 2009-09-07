<?php
/*----------------------------- 分页类 ----------------------------------
//文件名: Pager.class.php
//描述: 数据分页类

/////////////////// 例子 ////////////////////
$total = 1000;
$pageitem = 20;
//快速使用
$pageObject = new Pager($total, $pageitem);
$offset    = "offset=".$pageObject->offset();
$pagebar2  = $pageObject->wholeNumBar(10);
echo $offset."<br>".$pagebar2."<br><br>";

//自定义
echo $pageObject->firstPage();
echo $pageObject->prePage();
echo $pageObject->numBar('10',' ',' ');
echo $pageObject->nextPage();
echo $pageObject->lastPage();

//可选
$pageObject->setLinkScript("ajaxLink('@PAGE@');");
//翻页条的样式
$pagerStyle = array ('firstPage' => '', 'prePage' => 'gray4_12b none', 'nextPage' => 'gray4_12b none', 'totalPage' => '', 'numBar' => 'yellowf3_12b none', 'numBarMain' => 'gray4_12 none' );
$pageObject->setLinkStyle ( $pagerStyle );
-------------------------------------------------------------------------*/
class Pager {
	//总条数
	private $total;
	//每页显示条数
	private $pageitem;
	//显示页码数
	private $num;
    //当前页数
	private $page;
    //总分页数
	private $totalPage;
    //分页起点
	private $offset;
	//链接头
	private $linkhead;
	//链接样色
	private $linkStyle;
	//连接脚本代码
	private $linkScript;

	public function __construct($total, $pageitem) {
		
		$this->page = !isset ( $_GET ['page'] ) ? 1 : intval($_GET ['page']);
		$this->total = $total;
		$this->pageitem = $pageitem;
		$this->totalPage = ceil ( $total / $pageitem );

		if ($this->page > $this->totalPage) {	
			$this->offset = 0;
		} else {
			$this->offset = ($this->page - 1) * $pageitem;
		}

		$linkarr = explode ( "page=", $_SERVER ['QUERY_STRING'] );
		$linkft = $linkarr [0];

		if (empty ( $linkft )) {
			$this->linkhead = $_SERVER ['PHP_SELF'] . "?" ;
		} else {
			$linkft = (substr ( $linkft, - 1 ) == "&") ? $linkft : $linkft . "&";
			$this->linkhead = $_SERVER ['PHP_SELF'] . "?" . $linkft ;
		}
		
	}

	public function setLinkStyle($linkStyle) {
		$this->linkStyle = $linkStyle;
	}

	public function setLinkScript($func) {
		$this->linkScript = $func;
	}

	private function _getLinkScript($num) {
		return str_replace ( "@PAGE@", $num, $this->linkScript );
	}

	public function offset() {
		return $this->offset;
	}

	public function firstPage() {
		return $this->_returnLinkCode('首页',1,$this->linkStyle['firstPage'],'第一页');
	}

	public function lastPage() {		
		return $this->_returnLinkCode('尾页',$this->totalPage,$this->linkStyle['totalPage'],'最后一页');
	}

	public function prePage() {
		if ($this->page > 1) {
			$prePage = $this->page - 1;
			return $this->_returnLinkCode('[<]',$prePage,$this->linkStyle['prePage'],'上一页');
		} else {
			return '';
		}
	}

	public function nextPage() {

		if ($this->page < $this->totalPage) {
			$nextPage = $this->page + 1;
			return $this->_returnLinkCode('[>]',$nextPage,$this->linkStyle['nextPage'],'下一页');

		} else {
			return '';
		}
	}
	public function numBar($num = '', $left = ' [', $right = '] ') {

		$num = (empty ( $num )) ? 10 : $num;
		$this->num = $num;
		$mid = floor ( $num / 2 );
		$last = $num - 1;

		$minpage = (($this->page - $mid) < 1) ? 1 : ($this->page - $mid);
		$maxpage = $minpage + $last;
		if ($maxpage > $this->totalPage) {
			$maxpage = $this->totalPage;
			$minpage = $maxpage - $last;
			$minpage = ($minpage < 1) ? 1 : $minpage;
		}
		$linkbar = '';
		for($i = $minpage; $i <= $maxpage; $i ++) {
			$char = $left . $i . $right;
			if ($i == $this->page) {
				$linkbar .= $this->_returnLinkCode($char,$i,$this->linkStyle['numBar'],"第".$i."页");
			}else{
				$linkbar .= $this->_returnLinkCode($char,$i,$this->linkStyle['numBarMain'],"第".$i."页");
			}
		}
		return $linkbar;
	}

	public function preGroup() {

		$mid = floor ( $this->num / 2 );
		$minpage = (($this->page - $mid) < 1) ? 1 : ($this->page - $mid);
		$pgpagecount = ($minpage > $this->num) ? $minpage - $mid : 1;
		return $this->_returnLinkCode("[..]",$pgpagecount,$this->linkStyle['preGroup'],'上一组');
	}

	public function nextGroup() {
	
		$mid = floor ( $this->num / 2 );
		$last = $this->num;
		$minpage = (($this->page - $mid) < 1) ? 1 : ($this->page - $mid);
		$maxpage = $minpage + $last;
		if ($maxpage > $this->totalPage) {
			$maxpage = $this->totalPage;
		}
		$ngpagecount = ($this->totalPage > $maxpage + $last) ? $maxpage + $mid : $this->totalPage;
		return $this->_returnLinkCode("[..]" ,$ngpagecount,$this->linkStyle['nextGroup'],'下一组');
	}

	public function wholeNumBar($num = '') {
		$numBar = $this->numBar ( $num );
		return $this->firstPage () . $this->preGroup () . $this->prePage ( ) . $numBar . $this->nextPage ( ) . $this->nextGroup (  ) . $this->lastPage ( );
	}


	private function _returnLinkCode($char,$page,$class,$title){
		if ($this->linkScript) {
			return "<a href='#' onclick='{$this->_getLinkScript($page)}' title='{$title}' class='{$class}'>{$char}</a>\n";
		} else {
			return "<a href='{$this->linkhead}page={$page}' title='{$title}'  class='{$class}'>{$char}</a>\n";
		}
	
	}

}

?>