<?
function selectPinyin($type) {
	switch ($type) {
		case 1 :
			include_once (getcwd () . "/../tool/pinyin/jianti_pinyin.php");
			return $py;
			break;
		case 2 :
			include_once (getcwd () . "/../tool/pinyin/fanti_pinyin.php");
			return $py;
			break;
		case 3 :
			include_once (getcwd () . "/../tool/pinyin/naocanti_pinyin.php");
			return $py;
			break;
		case 4 :
			include_once (getcwd () . "/../tool/pinyin/jianti_pinyin.php");
			include_once (getcwd () . "/../tool/pinyin/fanti_pinyin.php");
			return $py;
			break;
		case 5 :
			include_once (getcwd () . "/../tool/pinyin/jianti_pinyin.php");
			include_once (getcwd () . "/../tool/pinyin/naocanti_pinyin.php");
			return $py;
			break;
		case 6 :
			include_once (getcwd () . "/../tool/pinyin/fanti_pinyin.php");
			include_once (getcwd () . "/../tool/pinyin/naocanti_pinyin.php");
			return $py;
			break;
		case 7 :
			include_once (getcwd () . "/../tool/pinyin/jianti_pinyin.php");
			include_once (getcwd () . "/../tool/pinyin/fanti_pinyin.php");
			include_once (getcwd () . "/../tool/pinyin/naocanti_pinyin.php");
			return $py;
			break;
	}
}
?>