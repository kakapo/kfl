<?php

include_once ('Cache.class.php');

class html51 {
	function read_html_file($url) {
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_REFERER, "http://home.51.com" );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, 1 );
		curl_setopt ( $ch, CURLOPT_ENCODING, "" );
		$content = curl_exec ( $ch );
		curl_close ( $ch );
		
		$is51user = "/diary\.php\?user=/i";
		if (! preg_match ( $is51user, $content )) {
			return false;
		} else {
			return $content;
		}
	}
	
	//Rss模块抓取
	function gethtml($userName, $htmlurl = '', $htmldir = '') {
		$bloghtml = $this->read_html_file ( $htmlurl );
		if (false != $bloghtml) {
			$cache = "{$userName}.html";
			file_put_contents ( $htmldir . '/' . $cache, $bloghtml );
			@chmod ( $htmldir . "/" . $cache, 0777 );
			return true;
		} else {
			return false;
		}
	}
	
	//html模块显示
	function outhtml($userName , $htmlurl = '', $htmldir = '', $user, $edit = '') {
		if (empty ( $htmlurl )) {
			return array ();
		}
		if ((empty ( $htmldir )) || (! is_dir ( $htmldir ))) {
			return array ();
		}
		
		$cache = new Cache ( RSS_CACHE_TIME );
		$cache->setCacheDir ( $htmldir . "/" );
		$cache->setCacheFile ( $userName . ".html" );
		
		$de = false;
		if ($edit == 'y') { //编辑,则要重新抓取			
			$rs = $this->gethtml ( $htmlurl, $htmldir );
			if ($rs) {
				$de = true;
			} else {
				return array ();
			}
		} elseif ($cache->isCached ()) { //有html
			$de = true;
		} else { //重新抓取				
			$rs = $this->gethtml ( $userName, $htmlurl, $htmldir );
			if ($rs) {
				$de = true;
			} else {
				return array ();
			}
		}
		if ($de) { //读取html
			return $this->readhtml ( $userName, $htmldir, $user );
		}
	}
	
	//读51html文件
	function readhtml($userName,$htmldir, $user) {
		$file = $htmldir . "/{$userName}.html";
		$htmlstr = file_get_contents ( $file );
		if ($htmlstr) {
			$item51 = array ();
			$pattern51 = "/<a.+href=[\'|\"](.+)\.php\?user=.+&id=([A-Za-z0-9]+?)[\'|\"].*>(.*)<\/a>.*([1|2]\d{3}-[0|1]\d-[0-3]\d)/i"; //51日志链接			
			//链接字符串匹配
			$link51 = "/^http:\/\/home\.51\.com$/i";
			$htmlstr = strip_tags ( $htmlstr, "<html><body><div><a>" );
			if (preg_match_all ( $pattern51, $htmlstr, $arr )) {
				$length = count ( $arr [0] );
				if ($length > 10)
					$length = 10;
				for($i = 0; $i < $length; $i ++) {
					if (preg_match ( $link51, $arr [1] [$i] )) { //链接有http://home.51.com
						$linkstr = $arr [1] [$i] . ".php?user=" . $user . "&id=" . $arr [2] [$i];
					} elseif (strpos ( $arr [1] [$i], "/" ) == 0) {
						$linkstr = "http://home.51.com" . $arr [1] [$i] . ".php?user=" . $user . "&id=" . $arr [2] [$i];
					} else {
						$linkstr = "http://home.51.com/" . $arr [1] [$i] . ".php?user=" . $user . "&id=" . $arr [2] [$i];
					}
					$item51 [$i] ['link'] = $linkstr;
					$item51 [$i] ['title'] = iconv ( "GBK", "UTF-8", $arr [3] [$i] );
					$item51 [$i] ['pubDate'] = str_replace ( "-", ".", $arr [4] [$i] );
				}
			}
			return $item51;
		} else {
			return array ();
		}
	}
}

?>
