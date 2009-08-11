<?php
include_once ("Cache.class.php");
include_once ("simple_html_dom.php");
/**
 * 支持gzip的rss阅读器，支持缓存
 * 
 */
class rss {
	var $xmlstr = ''; //xml数据串
	function read_rss_file($url) {
		$ch = curl_init ( $url );
		curl_setopt ( $ch, CURLOPT_HEADER, 0 );
		curl_setopt ( $ch, CURLOPT_TIMEOUT, 15 );
		curl_setopt ( $ch, CURLOPT_ENCODING, "" );
		
		curl_setopt ( $ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN; rv:1.8.1) Gecko/20061010 Firefox/2.0" );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		$content = curl_exec ( $ch ); //远程抓取内容		
		curl_close ( $ch );
		return $content;
	}
	
	//Rss模块抓取
	function getrss($userName,$rssurl = '', $rssdir = '') {
		$xml = $this->read_rss_file ( $rssurl );
		$cache = $userName . ".xml";
		file_put_contents ( $rssdir . '/' . $cache, $xml );
		@chmod ( $rssdir . "/" . $cache, 0777 );
	
	}
	
	//Rss模块显示
	function outrss($userName, $rssurl = '', $rssdir = '', $edit = '') {
		if (empty ( $rssurl )) {
			return array ();
		}
		if ((empty ( $rssdir )) || (! is_dir ( $rssdir ))) {
			return array ();
		}
		
		$cache = new Cache ( RSS_CACHE_TIME );
		$cache->setCacheDir ( $rssdir . '/' );
		$cache->setCacheFile ( $userName . ".xml" );
		
		$de = false;
		if ($edit == 'y') { //编辑,则要重新抓取			
			$this->getrss ( $userName,$rssurl, $rssdir );
			$de = true;
		
		} elseif ($cache->isCached ()) { //有rss	
			$de = true;
		} else { //重新抓取				
			$this->getrss ( $userName,$rssurl, $rssdir );
			$de = true;
		}
		if ($de) { //读取rss
			return $this->readxml ( $userName,$rssdir );
		}
	}
	//读xml文件
	function readxml($userName,$rssdir) {
		$file = $rssdir . "/{$userName}.xml";
		$this->xmlstr = file_get_contents ( $file );
		if ($this->xmlstr) {
			$this->JudgeEncode ();
			$rssdom = new simple_html_dom ( );
			$rssdom = str_get_html ( $this->xmlstr );
			
			$finally = array ();
			$re = $rssdom->find ( 'item' );
			$i = 0;
			foreach ( $re as $key => $item ) {
				if ($i >= 10)
					break;
					//取标题		 
				$title_arr = $item->find ( 'title', 0 );
				$title = trim ( $title_arr->innertext );
				$title = str_ireplace ( "<![CDATA[", "", $title );
				$title = str_ireplace ( "]]>", "", $title );
				$finally [$i] ['title'] = $title;
				
				//取链接地址
				$all = $item->innertext;
				$link = explode ( "<link>", $all );
				$link = explode ( "</link>", $link [1] );
				$link [0] = str_ireplace ( "<![CDATA[", "", $link [0] );
				$link [0] = str_ireplace ( "]]>", "", $link [0] );
				$finally [$i] ['link'] = $link [0];
				//取时间	
				$date_arr = $item->find ( 'pubDate', 0 );
				$pubDate = trim ( $date_arr->innertext );
				$pubDate = str_ireplace ( "<![CDATA[", "", $pubDate );
				$pubDate = str_ireplace ( "]]>", "", $pubDate );
				$finally [$i] ['pubDate'] = $pubDate;
				
				$i ++;
			}
			unset ( $rssdom );
			return $finally;
		} else {
			return array ();
		}
	}
	//转换xml编码
	function JudgeEncode() {
		$rx = '/<\?xml.*encoding=[\'"](.*?)[\'"].*\?>/im';
		if (preg_match ( $rx, $this->xmlstr, $m )) {
			$encoding = strtoupper ( $m [1] );
		} else {
			$encoding = "UTF-8";
		}
		if (($encoding == 'GB2312') || ($encoding == 'GBK')) {
			$encoding = 'GBK';
		}
		if (function_exists ( 'mb_convert_encoding' )) {
			$encoded_source = mb_convert_encoding ( $this->xmlstr, "UTF-8", $encoding );
		} else {
			$encoded_source = iconv ( $encoding, "UTF-8", $this->xmlstr );
		}
		if (($encoded_source != NULL) && (! empty ( $m [0] ))) {
			$this->xmlstr = str_replace ( $m [0], '<?xml   version="1.0"   encoding="utf-8"?>', $encoded_source );
		}
	}
}
?>
