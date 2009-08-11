<?php

require("FastDFS.class.php");

//download
//echo FastDFS::factory('group1')->downToFile('M00/00/00/fWaowEoaUtwAADwWZmBRJw73.png', 'nginx.png'), "<br />";
//echo FastDFS::factory('group1')->downToFileEx('M00/00/00/fWaowEoaUtwAADwWZmBRJw73.png'), "<br />";
//echo FastDFS::factory('group1')->downToBuff('M00/00/00/fWaowEoaUtwAADwWZmBRJw73.png'), "<br />";
//
//delete
//FastDFS::factory('group1')->delFile('M00/00/00/fWaowEoaUtwAADwWZmBRJw73.png');

//upload
echo FastDFS::factory('')->upByFileName('PA210023.JPG'), "<br />";
//echo FastDFS::factory('group1')->upByBuff( file_get_contents('nginx.png'), 'png' ), "<br />";

?>