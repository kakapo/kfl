<?php

/*----------------------------- 分页类 ----------------------------------

//文件名: pagination.class.php
//创建时间: 2007-05-22
//最后更新时间: 2007-07-08
//代码维护人: gtzhao
//版本: 1.1
//描述: 数据分页类

/////////////// 使用扩展函数列表 ////////////////

//////////////// 使用基本类列表 /////////////////

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
	/**+-----------------------------------------------
	|	$form_vars为当前页的表单变量，用"|"隔开。
	|	i.e. $pb = new HTML_Pager(50, 10, "action|username")
	|  +-----------------------------------------------
	 */
	var $linkScript;
	
	function HTML_Pager($total, $onepage, $form_vars = '') {
		$get_pagecount = isset ( $_GET ['pagecount'] ) ? $_GET ['pagecount'] : '';
		
		$pagecount = $get_pagecount < 0 || ! is_numeric ( $get_pagecount ) ? '' : $get_pagecount;
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
	
	function _getLinkScript($num) {
		return str_replace ( "@PAGE@", $num, $this->linkScript );
	}
	
	#End of function offset();
	

	/**+-----------------------------------------------
	|	用于取得select的指针.
	|	i.e. $pb     = new HTML_Pager(50, 10);
	|		 $offset = $pageObject->offset();
	|  +-----------------------------------------------
	 */
	function offset() {
		return $this->offset;
	}
	#End of function offset();
	

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
		
		$linkhead = $this->linkhead;
		$linkchar = (empty ( $char )) ? "<font color='$color'>[1]</font>" : $char;
		
		if ($this->linkScriptUrl) {
			return "<a href='javascript:{$this->_getLinkScriptUrl($linkhead."pagecount=1")}' title='第一页' class='{$this->linkStyle['firstPage']}'>{$linkchar}</a>";
		} else {
			if ($this->linkScript) {
				return "<a href='javascript:{$this->_getLinkScript(1)}' title='第一页' class='{$this->linkStyle['firstPage']}'>$linkchar</a>";
			} else {
				return "<a href='{$linkhead} pagecount=1 ' title='第一页' class='{$this->linkStyle['firstPage']}'>{$linkchar}</a>";
			}
		}
	}
	#End of function firstPage();
	

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
		
		$linkhead = $this->linkhead;
		$totalPage = $this->totalPage;
		$linkchar = (empty ( $char )) ? "<font color='$color'>[" . $totalPage . "]</font>" : $char;
		
		if ($this->linkScriptUrl) {
			return "<a href='javascript:{$this->_getLinkScriptUrl($linkhead."pagecount=".$totalPage)}' title='最后一页' class='{$this->linkStyle['totalPage']}'>{$linkchar}</a>";
		} else {
			
			if ($this->linkScript) {
				return "<a href='javascript:{$this->_getLinkScript($totalPage)}' title='最后一页' class='{$this->linkStyle['totalPage']}'>{$linkchar}</a>";
			} else {
				return "<a href='{$linkhead}pagecount={$totalPage}' title='最后一页' class='{$this->linkStyle['totalPage']}'>{$linkchar}</a>";
			}
		}
	}
	#End of function lastPage();
	

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
		
		$linkhead = $this->linkhead;
		$pagecount = $this->pagecount;
		if (empty ( $char )) {
			$char = "[<]";
		}
		
		if ($pagecount > 1 || $show) {
			$prePage = $pagecount - 1;
			
			if ($this->linkScriptUrl) {
				return "<a href='javascript:{$this->_getLinkScriptUrl($linkhead."pagecount=".$prePage)}' title='上一页' class='{$this->linkStyle['prePage']}'>{$char}</a>";
			} else {
				
				if ($this->linkScript) {
					return "<a href='javascript:{$this->_getLinkScript($prePage)}' title='上一页' class='{$this->linkStyle['prePage']}'>{$char}</a>";
				} else {
					return "<a href='{$linkhead}pagecount={$prePage}' title='上一页' class='{$this->linkStyle['prePage']}'>{$char}</a>";
				}
			}
		} else {
			return '';
		}
	}
	#End of function prePage();
	

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
		
		$linkhead = $this->linkhead;
		$totalPage = $this->totalPage;
		$pagecount = $this->pagecount;
		if (empty ( $char )) {
			$char = "[>]";
		}
		if ($pagecount < $totalPage || $show) {
			$nextPage = $pagecount + 1;
			
			if ($this->linkScriptUrl) {
				return "<a href='javascript:{$this->_getLinkScriptUrl($linkhead."pagecount=".$nextPage)}' title='上一页' class='{$this->linkStyle['nextPage']}'>{$char}</a>";
			} else {
				
				if ($this->linkScript) {
					return "<a href='javascript:{$this->_getLinkScript($nextPage)}' title='上一页' class='{$this->linkStyle['nextPage']}'>{$char}</a>";
				} else {
					return "<a href='{$linkhead}pagecount={$nextPage}' title='下一页' class='{$this->linkStyle['nextPage']}'>{$char}</a>";
				}
			}
		} else {
			return '';
		}
	}
	#End of function nextPage();
	

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
		$linkbar = '';
		
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
		$linkhead = $this->linkhead;
		/*
		$left      = (empty($left))?"[":$left;
		$right     = (empty($right))?"]":$right;
		$color     = (empty($color))?"#ff0000":$color;
*/
		$minpage = (($pagecount - $mid) < 1) ? 1 : ($pagecount - $mid);
		$maxpage = $minpage + $last;
		if ($maxpage > $totalpage) {
			$maxpage = $totalpage;
			$minpage = $maxpage - $last;
			$minpage = ($minpage < 1) ? 1 : $minpage;
		}
		
		for($i = $minpage; $i <= $maxpage; $i ++) {
			$chars = $left . $i . $right;
			$char = ! $this->linkStyle ['numBarMain'] && $maincolor ? "<font color='$maincolor'>" . $chars . "</font>" : $chars;
			
			if ($i == $pagecount) {
				$char = ! $this->linkStyle ['numBar'] && $color ? "<font color='$color'>$chars</font>" : $chars;
			}
			
			if ($this->linkScriptUrl) {
				$linkchar = "<a href='javascript:{$this->_getLinkScriptUrl($linkhead."pagecount=".$i)}'" . ($i == $pagecount ? ($this->linkStyle ['numBar'] ? "class='{$this->linkStyle['numBar']}'" : null) : ($this->linkStyle ['numBarMain'] ? "class='{$this->linkStyle['numBarMain']}'" : null)) . ">" . $char . "</a>";
			} 

			else {
				if ($this->linkScript) {
					$linkchar = "<a href='javascript:{$this->_getLinkScript($i)}' " . ($i == $pagecount ? ($this->linkStyle ['numBar'] ? "class='{$this->linkStyle['numBar']}'" : null) : ($this->linkStyle ['numBarMain'] ? "class='{$this->linkStyle['numBarMain']}'" : null)) . ">" . $char . "</a>";
				} else {
					$linkchar = "<a href=\"{$linkhead}pagecount={$i}\" " . ($i == $pagecount ? ($this->linkStyle ['numBar'] ? "class='{$this->linkStyle['numBar']}'" : null) : ($this->linkStyle ['numBarMain'] ? "class='{$this->linkStyle['numBarMain']}'" : null)) . ">" . $char . "</a>";
				}
			}
			$linkbar .= $linkchar;
		}
		
		return $linkbar;
	}
	#End of function numBar();
	

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
		
		$pagecount = $this->pagecount;
		$linkhead = $this->linkhead;
		$num = $this->num;
		$mid = floor ( $num / 2 );
		$minpage = (($pagecount - $mid) < 1) ? 1 : ($pagecount - $mid);
		$char = (empty ( $char )) ? "[<<]" : $char;
		$pgpagecount = ($minpage > $num) ? $minpage - $mid : 1;
		
		if ($this->linkScriptUrl) {
			return "<a href='javascript:{$this->_getLinkScriptUrl($linkhead."pagecount=".$pgpagecount)}' title='上一组' class='{$this->linkStyle['preGroup']}'>{$char}</a>";
		} else {
			
			if ($this->linkScript) {
				return "<a href='javascript:{$this->_getLinkScript($pgpagecount)}' title='上一组' class='{$this->linkStyle['preGroup']}'>{$char}</a>";
			} else {
				return "<a href='{$linkhead}pagecount={$pgpagecount}' title='上一组' class='{$this->linkStyle['preGroup']}'>{$char}</a>";
			}
		}
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
		
		$pagecount = $this->pagecount;
		$linkhead = $this->linkhead;
		$totalpage = $this->totalPage;
		$num = $this->num;
		$mid = floor ( $num / 2 );
		$last = $num;
		$minpage = (($pagecount - $mid) < 1) ? 1 : ($pagecount - $mid);
		$maxpage = $minpage + $last;
		if ($maxpage > $totalpage) {
			$maxpage = $totalpage;
			$minpage = $maxpage - $last;
			$minpage = ($minpage < 1) ? 1 : $minpage;
		}
		
		$char = (empty ( $char )) ? "[>>]" : $char;
		$ngpagecount = ($totalpage > $maxpage + $last) ? $maxpage + $mid : $totalpage;
		
		if ($this->linkScriptUrl) {
			return "<a href='javascript:{$this->_getLinkScriptUrl($linkhead."pagecount=".$ngpagecount)}' title='下一组' class='{$this->linkStyle['nextGroup']}'>{$char}</a>";
		} else {
			
			if ($this->linkScript) {
				return "<a href='javascript:{$this->_getLinkScript($ngpagecount)}' title='下一组' class='{$this->linkStyle['nextGroup']}'>{$char}</a>";
			} else {
				return "<a href='{$linkhead}pagecount={$ngpagecount}' title='下一组' class='{$this->linkStyle['nextGroup']}'>{$char}</a>";
			}
		}
	}
	#End of function nextGroup();
	

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
	#End of function jumpForm();
	

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

}
#End of class HTML_Pager;
?>