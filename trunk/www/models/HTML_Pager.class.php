<?php
/*----------------------------- 分页类 ----------------------------------
//文件名: pagination.class.php
//创建时间: 2007-05-22
//最后更新时间: 2007-07-08
//代码维护人: gtzhao
//版本: 1.1
//描述: 数据分页类
/////////////////// 方法说明 ////////////////////

$total = 1000;
$onepage = 20;
//创建对象
$pb = new HTML_Pager($total, $onepage);

//返回移动到的指针值
$pageObject->offset();

//设置连接为Javascript函数
$pageObject->setLinkScript("ajaxLink('@PAGE@')");

//返回分页HTML连接代码
$pageObject->wholeNumBar($num, $color ,$maincolor);
$pageObject->wholeBar('跳转后缀', $num , $color ,$maincolor);
$pageObject->jumpForm('跳转后缀');

//自定义拼凑连接代码
$pageObject->firstPage($char, $color);//首页
$pageObject->lastPage($char, $color);//尾页

$pageObject->prePage($char);//上一页
$pageObject->numBar($num, $color, $maincolor, $left, $right);//数字连接
$pageObject->nextPage($char);//上一页

$pageObject->preGroup($char);//上一组
$pageObject->nextGroup($char);//下一组

/////////////////// 完整例子 ////////////////////

$total = 1000;
$onepage = 20;

$pageObject = new HTML_Pager($total, $onepage);
$offset    = "offset=".$pageObject->offset();
$pagebar1  = $pageObject->wholeBar();
$pagebar2  = $pageObject->wholeNumBar(10, '#000000', '#cccccc');
$pagebar3  = $pageObject->wholeBar('aaa', 5, '#000000', '#cccccc');
echo $offset."<br>".$pagebar1."<br>";
echo $offset."<br>".$pagebar2."<br><br>";
echo $offset."<br>".$pagebar3."<br>";

echo $offset."<br>";
echo $pageObject->firstPage('首页').' ';
echo $pageObject->prePage('上页').' |';
echo $pageObject->numBar('10', '#FF4415','#666666', ' ',' ').'| ';
echo $pageObject->nextPage('下页').' ';
echo $pageObject->lastPage('末页')."<br><br>";

$pageObject->setLinkScript("ajaxLink('@PAGE@');");
echo $offset."<br>".$pageObject->wholeNumBar(10, '#000000', '#cccccc')."<br>";

-------------------------------------------------------------------------*/
class HTML_Pager {
	/**+-----------------------------------------------
	|	总记录数
	|  +-----------------------------------------------
	 */
	var $total;
	/**+-----------------------------------------------
	|	每页记录数
	|  +-----------------------------------------------
	 */
	var $onepage;
	/**+-----------------------------------------------
	|	数字条显示个数
	|  +-----------------------------------------------
	 */
	var $num;
	/**+-----------------------------------------------
	|	当前页数
	|  +-----------------------------------------------
	 */
	var $pagecount;
	/**+-----------------------------------------------
	|	总页数
	|  +-----------------------------------------------
	 */
	var $totalPage;
	/**+-----------------------------------------------
	|	MYSQL查询指针
	|  +-----------------------------------------------
	 */
	var $offset;
	/**+-----------------------------------------------
	|	链接的前部分
	|  +-----------------------------------------------
	 */
	var $linkhead;
	/**+-----------------------------------------------
	|	所有当前事例连接
	|  +-----------------------------------------------
	 */
	var $links;
	/**+-----------------------------------------------
	|	链接的样式
	|  +-----------------------------------------------
	 */
	var $linkStyle;
	/**+-----------------------------------------------
	|	js翻页
	|  +-----------------------------------------------
	 */
	var $linkScriptUrl;
	//是否是ajax分页
	var $aj;
	/**+-----------------------------------------------
	|	$form_vars为当前页的表单变量，用"|"隔开。
	|	i.e. $pb = new HTML_Pager(50, 10, "action|username")
	|  +-----------------------------------------------
	 */
	function HTML_Pager($total, $onepage, $form_vars = '') {
		if (empty ( $_GET ['pagecount'] ))
			$_GET ['pagecount'] = 0;
		if (empty ( $this->linkScript ))
			$this->linkScript = 0;
		$pagecount = $_GET ['pagecount'] < 0 || ! is_numeric ( $_GET ['pagecount'] ) ? '' : $_GET ['pagecount'];
		$this->total = $total;
		$this->onepage = $onepage;
		$this->totalPage = ceil ( $total / $onepage );

		if (empty ( $pagecount ) || $pagecount > $this->totalPage) {
			$pagecount = 1;
			$this->pagecount = 1;
			$this->offset = 0;
		} else {
			$this->pagecount = $pagecount;
			$this->offset = ($pagecount - 1) * $onepage;
		}

		if (! empty ( $form_vars )) {
			$vars = explode ( "|", $form_vars );
			$chk = $vars [0];
			$chk2 = $vars [1];
			$chk_value = $_POST ["$chk"];
			$chk_value2 = $_POST ["$chk2"];
			if (empty ( $chk_value ) && empty ( $chk_value2 )) {
				$formlink = "";
			} else {
				for($i = 0; $i < sizeof ( $vars ); $i ++) {
					$var = $vars [$i];
					$value = $_POST ["$var"];
					$addchar = $var . "=" . $value;
					$formlink = $formlink . $addchar . "&";
				}
			}
		} else {
			$formlink = "";
		}

		$linkarr = explode ( "pagecount=", $_SERVER ['QUERY_STRING'] );
		$linkft = $linkarr [0];

		if (empty ( $linkft )) {
			$this->linkhead = $_SERVER ['PHP_SELF'] . "?" . $formlink;
		} else {
			$linkft = (substr ( $linkft, - 1 ) == "&") ? $linkft : $linkft . "&";
			$this->linkhead = $_SERVER ['PHP_SELF'] . "?" . $linkft . $formlink;
		}
	}
	#End of function HTML_Pager();
	//array($firstPage,$prePage,$nextPage,$totalPage,$numBar);
	function setLinkStyle($linkStyle) {
		$this->linkStyle = $linkStyle;
	}
	/**+-----------------------------------------------
	|	用于取得select的指针.
	|	i.e. $pb     = new HTML_Pager(50, 10);
	|		 $offset = $pageObject->offset();
	|  +-----------------------------------------------
	 */
	function setLinkScriptUrl($func) {
		$this->linkScriptUrl = $func;
	}
	function _getLinkScriptUrl($url) {
		return str_replace ( "@URL@", $url, $this->linkScriptUrl );
	}
	function setLinkScript($func) {
		$this->linkScript = $func;
	}
	function setajax($str) {
		$this->aj = $str;
	}
	function _getLinkScript($num) {
		return str_replace ( "@PAGE@", $num, $this->linkScript );
	}
	/**+-----------------------------------------------
	|	用于取得select的指针.
	|	i.e. $pb     = new HTML_Pager(50, 10);
	|		 $offset = $pageObject->offset();
	|  +-----------------------------------------------
	 */
	function offset() {
		return $this->offset;
	}
	/**+-----------------------------------------------
	|	取得第一页
	|	i.e. $pb         = new HTML_Pager(50, 10);
	|		 $firstPage = $pageObject->firstPage();
	|  +-----------------------------------------------
	 */
	function firstPage($char = '', $color = '') {
		if (strpos ( $color, '#' ) === true) {
			$this->linkStyle ['firstPage'] = $color;
		}
		$linkchar = (empty ( $char )) ? "<font color='$color'>[1]</font>" : $char;
		return $this->returnLinkCode($linkchar,1,$this->linkStyle['firstPage'],'第一页');
	}
	/**+-----------------------------------------------
	|	取得最末页
	|	i.e. $pb         = new HTML_Pager(50, 10);
	|		 $totalPage = $pageObject->lastPage(1);
	|  +-----------------------------------------------
	 */
	function lastPage($char = '', $color = '') {
		if (strpos ( $color, '#' ) === true) {
			$this->linkStyle ['totalPage'] = $color;
		}
		$linkchar = (empty ( $char )) ? "<font color='$color'>[" . $this->totalPage . "]</font>" : $char;
		return $this->returnLinkCode($linkchar,$this->totalPage,$this->linkStyle['totalPage'],'最后一页');
	}
	/**+-----------------------------------------------
	|	取得上一页.$char为链接的字符,默认为"[<]"
	|	i.e. $pb       = new HTML_Pager(50, 10);
	|		 $prePage = $pageObject->prePage("上一页");
	|  +-----------------------------------------------
	 */
	function prePage($char = '', $color = '', $show = false) {
		if (strpos ( $color, '#' ) === true) {
			$this->linkStyle ['prePage'] = $color;
		}
		if (empty ( $char )) {
			$char = "[<]";
		}

		if ($this->pagecount > 1 || $show) {
			$prePage = $this->pagecount - 1;
			return $this->returnLinkCode($char,$prePage,$this->linkStyle['prePage'],'上一页');
		} else {
			return '';
		}
	}
	/**+-----------------------------------------------
	|	取得上一页.$char为链接的字符,默认为"[>]"
	|	i.e. $pb        = new HTML_Pager(50, 10);
	|		 $nextPage = $pageObject->nextPage("上一页");
	|  +-----------------------------------------------
	 */
	function nextPage($char = '', $color = '', $show = false) {
		if (strpos ( $color, '#' ) === true) {
			$this->linkStyle ['nextPage'] = $color;
		}
		if (empty ( $char )) {
			$char = "[>]";
		}
		if ($this->pagecount < $this->totalPage || $show) {
			$nextPage = $this->pagecount + 1;
			return $this->returnLinkCode($char,$nextPage,$this->linkStyle['nextPage'],'下一页');

		} else {
			return '';
		}
	}
	/**+-----------------------------------------------
	|	取得页码数字条.	 $num 为个数,默认为10
	|                    $color 为当前链接的突显颜色
	|					 $left 数字左边 默认为"["
	|                    $right 数字左右 默认为"]"
	|	i.e. $pb      = new HTML_Pager(50, 10);
	|		 $numBar = $pageObject->numBar(9, "$cccccc");
	|  +-----------------------------------------------
	 */
	function numBar($num = '', $color = '', $maincolor = '', $left = ' [', $right = '] ') {

		if (strpos ( $color, '#' ) === true) {
			$this->linkStyle ['numBar'] = $color;
		}
		if (strpos ( $maincolor, '#' ) === true) {
			$this->linkStyle ['numBarMain'] = $maincolor;
		}
		$num = (empty ( $num )) ? 10 : $num;
		$this->num = $num;
		$mid = floor ( $num / 2 );
		$last = $num - 1;
		$pagecount = $this->pagecount;
		$totalpage = $this->totalPage;
		$minpage = (($pagecount - $mid) < 1) ? 1 : ($pagecount - $mid);
		$maxpage = $minpage + $last;
		if ($maxpage > $totalpage) {
			$maxpage = $totalpage;
			$minpage = $maxpage - $last;
			$minpage = ($minpage < 1) ? 1 : $minpage;
		}
		$linkbar = '';
		for($i = $minpage; $i <= $maxpage; $i ++) {
			$chars = $left . $i . $right;
			$char = !$this->linkStyle ['numBarMain'] && $maincolor ? "<font color='$maincolor'>" . $chars . "</font>" : $chars;
			if ($i == $pagecount) {
				$char = !$this->linkStyle ['numBar'] && $color ? "<font color='$color'>$chars</font>" : $chars;
				$linkbar .= $this->returnLinkCode($char,$i,$this->linkStyle['numBar'],"第".$i."页");
			}else{
				$linkbar .= $this->returnLinkCode($char,$i,$this->linkStyle['numBarMain'],"第".$i."页");
			}
		}
		return $linkbar;
	}
	/**+-----------------------------------------------
	|	取得上一组数字条.$char为链接的字符,默认为"[<<]"
	|	i.e. $pb        = new HTML_Pager(50, 10);
	|        $numBar   = $pageObject->numBar();
	|		 $preGroup = $pageObject->preGroup();
	|  +-----------------------------------------------
	 */
	function preGroup($char = '', $color = '') {
		if (strpos ( $color, '#' ) === false) {
			$this->linkStyle ['preGroup'] = $color;
		}
		$char = (empty ( $char )) ? "[<<]" : $char;
		$mid = floor ( $this->num / 2 );
		$minpage = (($this->pagecount - $mid) < 1) ? 1 : ($this->pagecount - $mid);
		$pgpagecount = ($minpage > $this->num) ? $minpage - $mid : 1;
		return $this->returnLinkCode($char,$pgpagecount,$this->linkStyle['preGroup'],'上一组');
	}
	#End of function preGroup();
	/**+-----------------------------------------------
	|	取得下一组数字条.$char为链接的字符,默认为"[>>]"
	|	i.e. $pb         = new HTML_Pager(50, 10);
	|        $numBar    = $pageObject->numBar();
	|		 $nextGroup = $pageObject->nextGroup();
	|  +-----------------------------------------------
	 */
	function nextGroup($char = '', $color = '') {
		if (strpos ( $color, '#' ) === false) {
			$this->linkStyle ['nextGroup'] = $color;
		}
		$char = (empty ( $char )) ? "[>>]" : $char;
		$mid = floor ( $this->num / 2 );
		$last = $this->num;
		$minpage = (($this->pagecount - $mid) < 1) ? 1 : ($this->pagecount - $mid);
		$maxpage = $minpage + $last;
		if ($maxpage > $this->totalPage) {
			$maxpage = $this->totalPage;
		}
		$ngpagecount = ($this->totalPage > $maxpage + $last) ? $maxpage + $mid : $this->totalPage;
		return $this->returnLinkCode($char,$ngpagecount,$this->linkStyle['nextGroup'],'下一组');
	}
	/**+-----------------------------------------------
	|	取得整个数字条，上一页，上一页，上一组
	|   下一组的等.$num数字个数,$color 当前链接的突显色
	|	i.e. $pb               = new HTML_Pager(50, 10);
	|        $wholeNumBar    = $pageObject->wholeNumBar(9);
	|  +-----------------------------------------------
	 */
	function wholeNumBar($num = '', $color = '', $maincolor = '') {
		$numBar = $this->numBar ( $num, $color, $maincolor );
		return $this->firstPage ( '', $maincolor ) . $this->preGroup ( "<font color='$maincolor'>[<<]</font>" ) . $this->prePage ( "<font color='$maincolor'>[<]</font>" ) . $numBar . $this->nextPage ( "<font color='$maincolor'>[>]</font>" ) . $this->nextGroup ( "<font color='$maincolor'>[>>]</font>" ) . $this->lastPage ( '', $maincolor );
	}
	#End of function wholeBar();
	/**+-----------------------------------------------
	|	取得整链接，等于wholeNumBar加上表单跳转.
	|   $num数字个数,$color 当前链接的突显色
	|	i.e. $pb           = new HTML_Pager(50, 10);
	|        $wholeBar    = $pageObject->wholeBar(9);
	|  +-----------------------------------------------
	 */
	function wholeBar($jump = '', $num = '', $color = '', $maincolor = '') {
		$wholeNumBar = $this->wholeNumBar ( $num, $color, $maincolor ) . "&nbsp;";
		$jumpForm = $this->jumpForm ( $jump );
		return <<<EOT
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td align="right">$wholeNumBar</td>
    <td width="50" align="right">$jumpForm</td>
  </tr>
</table>
EOT;
	}

	/**+-----------------------------------------------
	|	跳转表单
	|   i.e. $pb           = new HTML_Pager(50, 10);
	|        $jumpForm    = $pageObject->jumpForm();
	|  +-----------------------------------------------
	 */
	function jumpForm($jump = '') {
		$formname = "pagebarjumpform" . $jump;
		$jumpname = "jump" . $jump;
		$linkhead = $this->linkhead;
		$total = $this->totalPage;
		$form = <<<EOT
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<script language="javascript">
		function $jumpname(linkhead, total, page){

			var pagecount = (page.value>total)?total:page.value;
			pagecount = (pagecount<1)?1:pagecount;
			location.href = linkhead + "pagecount=" + pagecount;
			return false;
		}
	</script>
       <form name="$formname" method="post" onSubmit="return $jumpname('$linkhead', $total, $formname.page)"><tr>
          <td>
        <input name="page" type="text" size="1">
		<input type="button" name="Submit" value="GO" onClick="return $jumpname('$linkhead', $total, $formname.page)">
      </td>
        </tr></form></table>
EOT;

		return $form;
	}

	//获得指定类型的链接
	function getLink($type = false) {
		$linkArray = array ();
		$link = array ('firstPage', 'lastPage', 'prePage', 'nextPage', 'preGroup', 'nextGroup', 'numBar' );

		foreach ( $link as $rs ) {
			if ($rs == 'numBar') {
				preg_match_all ( '/href="([^"]+)"/', $this->$rs (), $outLink );
				$linkArray [$rs] = $outLink [1];
			} else {
				preg_match ( '/href="([^"]+)"/', $this->$rs (), $outLink );
				$linkArray [$rs] = $outLink [1];
			}
		}

		$this->links = $linkArray;

		if ($type) {
			return $linkArray [$type];
		} else {
			return $linkArray;
		}

	}
	//生成分页链接
	function returnLinkCode($char,$pagecount,$class,$title){

		if ($this->linkScriptUrl) {
			return "<a href='javascript:{$this->_getLinkScriptUrl($this->linkhead."pagecount=".$pagecount)}' title='{$title}' class='{$class}'>{$char}</a>";
		} else {
			if ($this->linkScript) {
				return "<a href='javascript:{$this->_getLinkScript($pagecount)}' title='{$title}' class='{$class}'>{$linkchar}</a>\n";
			} elseif ($this->aj) {
				return "<a href='javascript:link(\"{$this->linkhead}pagecount={$pagecount}\");' title='{$title}' class='{$class}'>{$char}</a>\n";
			} else {
				return "<a href='{$this->linkhead}pagecount={$pagecount}' title='{$title}'  class='{$class}'>{$char}</a>\n";
			}
		}

	}

}
#End of class HTML_Pager;
?>