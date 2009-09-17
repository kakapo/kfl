<?php

include_once("../config/config.ini.php");
/**
 * 美术资源工具类,主要处理3D资源中配置文件
 * @todo test for 3d preview
 * @author cdwei
 *
 */
class Tool3D {

	public $uploaddir="";

	function Tool3D() {
		$this->uploaddir="../www/tmp/";
	}


	/**
	 * 上传模型文件到数据库中
	 * @return array
	 */
	public function uploadDAE() {

	 $ext=substr($_FILES['file']['name'],-4);
	 if($ext!=".DAE") return array(false,"上传的模型文件必须是.DAE文件!");
	 $uploadfile = $this->uploaddir . basename($_FILES['file']['name']);

	 if (!move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
	 	return array(false,"上传没有成功，move_upload_file函数执行失败!".$_FILES['error']);
	 }

	 $pic_arr=array();
	 foreach ($_FILES["pic"]["error"] as $key => $error) {
	 	if ($error == UPLOAD_ERR_OK) {
	 		if(move_uploaded_file( $_FILES["pic"]["tmp_name"][$key],$this->uploaddir .basename( $_FILES["pic"]["name"][$key]))){
	 			$pic_arr[]=$GLOBALS['gSiteInfo'] ['www_site_url'].'/tmp/'.$_FILES["pic"]["name"][$key];
	 		}
	 	}
	 }
	 $file_uri=$this->uploaddir . basename($_FILES['file']['name']);

	 $re=array(false,"除了上传,没有执行任何操作!");
	 if($_POST['item_model_type']=='entirety'){
	 	$re=$this->ConvertDAEToEntirety($file_uri);
	 }else{
	 	$re=$this->ConvertDAEToSingle($file_uri);
	 }
	 if($_POST['item_model_type']=='7'){
	 	$re=$this->ConvertActionDAE($file_uri);
	 }
	 list($flag,$msgs)=$re;
	 //print_r($re);
	 if($flag==false){
	 	return array(false,'{"s":400,"m":"no model","d":null}');
	 }
	 //
	 $file_url=$GLOBALS ['gSiteInfo'] ['www_site_url'].'/tmp/'.basename($_FILES['file']['name']);
	 $file_url=str_replace(".DAE",'.zip',$file_url);
	 //echo $pic_str;die;
	 $file_name="";
	 if(!empty($_POST['name'])){
	 	$file_name=$_POST['name'];
	 }else{
	 	$path_parts = pathinfo($_FILES['file']['name']);
	 	$file_name=str_replace(".".$path_parts['extension'],"",$path_parts["basename"]);
	 }
	 $arr=array();
	 $arr['s']=200;
	 $arr['m']="success";
	 $arr['d']=array("name"=>$file_name,"url"=>$file_url,"pics"=>$pic_arr,"type"=>$_POST['item_model_type'],"id"=>"521","icon"=>"");
	 return array(true, json_encode($arr),"file_url"=>$file_url);

	}

	/**
	 * COLLADA模型处理,整体裸模处理，不删除节点
	 * @param $DAE_fileurl
	 * @return array(boolean,"error memo")
	 */
	public function ConvertDAEToEntirety($DAE_fileurl) {


		$xml=file_get_contents($DAE_fileurl);

		//第一步：替换 Name_array
		preg_match_all('/<node\s*id="(.*?)".*?sid="(.*?)"/m',$xml,$match_arr,PREG_SET_ORDER);
		$bone_arr=array();
		foreach ($match_arr as $bone_key => $bone_value) {
			$key=$bone_value[2];
			$bone_arr[$key]="Bone_".$bone_value[1];
		}

		preg_match_all('/<Name_array.*?>(.*?)<\/Name_array>/m',$xml,$name_match_arr,PREG_SET_ORDER);

		$search=array();
		$repalce=array();
		foreach ($name_match_arr as $name_key => $name_value) {
			$name=$name_value[1];
			$name_arr=explode(" ",$name);
			foreach ($name_arr as $t_key => $number_variable  ) {
				$name_arr[$t_key]=$bone_arr[$number_variable];
			}
			$repalce[]=">".implode(" ",$name_arr)."</";
			$search[]=">".$name."</";
		}

		preg_match_all('/<library_controllers>(.*?)<\/library_controllers>/ism',$xml,$no_match_arr,PREG_SET_ORDER);
		$replace_str=$no_match_arr[0][1];
		$xml2=str_replace($search,$repalce,$replace_str);
		$finally_xml= preg_replace('/<library_controllers>(.*?)<\/library_controllers>/ism',"<library_controllers>".$xml2."</library_controllers>",$xml);


		//第二步，替换Character01-node里的sid
		$test=simplexml_load_string($finally_xml);
		if($test==false){
			return  array(false,"解析$DAE_fileurl文件失败，可能是正则替换Name_array错误!");
		}

		$tmp=$test->library_visual_scenes->visual_scene->node;
		$i=0;
		foreach ($tmp as $key => $rows) {
			$tmpp=$rows->attributes();
			if($tmpp['id']=="Character01-node"){
				$Character01_node_xml = $test->library_visual_scenes->visual_scene->node[$i]->asXML();
				$Character01_node_xml = preg_replace('/(.*)(<node\s*id=")(.*?)(")(.*?)sid="(.*?)"/m','\\1\\2\\3\\4\\5sid="Bone_\\3"',$Character01_node_xml);
				unset($test->library_visual_scenes->visual_scene->node[$i]);
				$test->library_visual_scenes->visual_scene->addChild("will_be_replace",'');
				break;
			}
			$i++;
		}
		$tmp_xml = $test->asXML();
		$finally_xml= preg_replace('/<will_be_replace>(.*?)<\/will_be_replace>/ism',$Character01_node_xml,$tmp_xml);



		//第三步压缩成zip文件
		$path_parts = pathinfo($DAE_fileurl);
		$xml_path=str_replace($path_parts["extension"],"xml",$DAE_fileurl);
		$xml_path=str_replace($path_parts["basename"],"temp_".$path_parts["basename"],$xml_path);
		file_put_contents($xml_path,$finally_xml);
		$zip = new ZipArchive();
		$filename = str_replace($path_parts["extension"],"zip",$DAE_fileurl);;
		if ($zip->open($filename, ZIPARCHIVE::CREATE)!==TRUE) {
			return  array(false,"cannot open <$filename>");
		}
		$zip_name=str_replace($path_parts["extension"],"DAE",$path_parts["basename"]);
		$zip->addFile($xml_path,$zip_name);
		$zip->close();
		unlink($xml_path);//删除临时xml文件
		return array(true,"finsh");

	}
	public function ConvertDAEToSingle($DAE_fileurl) {

		$xml=file_get_contents($DAE_fileurl);

		preg_match_all('/<node\s*id="(.*?)".*?sid="(.*?)"/m',$xml,$match_arr,PREG_SET_ORDER);
		$bone_arr=array();
		foreach ($match_arr as $bone_key => $bone_value) {
			$key=$bone_value[2];
			$bone_arr[$key]="Bone_".$bone_value[1];
		}

		//print_r($name_match_arr);
		preg_match_all('/<Name_array.*?>(.*?)<\/Name_array>/m',$xml,$name_match_arr,PREG_SET_ORDER);

		$search=array();
		$repalce=array();
		foreach ($name_match_arr as $name_key => $name_value) {
			$name=$name_value[1];
			$name_arr=explode(" ",$name);
			foreach ($name_arr as $t_key => $number_variable  ) {
				$name_arr[$t_key]=$bone_arr[$number_variable];
			}
			$repalce[]=">".implode(" ",$name_arr)."</";
			$search[]=">".$name."</";
		}

		//print_r($search);
		//print_r($repalce);

		preg_match_all('/<library_controllers>(.*?)<\/library_controllers>/ism',$xml,$no_match_arr,PREG_SET_ORDER);
		$replace_str=$no_match_arr[0][1];
		$xml2=str_replace($search,$repalce,$replace_str);

		//echo $xml2;die;
		$finally_xml= preg_replace('/<library_controllers>(.*?)<\/library_controllers>/ism',"<library_controllers>".$xml2."</library_controllers>",$xml);


		//file_put_contents("test2.xml",$finally_xml);

		//$start_time = getmicrotime ();
		$test=simplexml_load_string($finally_xml);
		if($test==false){
			return  array(false,"解析$DAE_fileurl文件失败，可能是正则替换Name_array错误!");
		}

		//
		//		//删除节点
		unset($test->asset);
		unset($test->library_lights);
		unset($test->library_images);
		//unset($test->library_materials);
		//unset($test->library_effects);
		unset($test->library_animations);
		unset($test->library_visual_scenes->visual_scene->extra);
		unset($test->scene);


		//$test->library_images->image->init_from="";
		$tmp=$test->library_visual_scenes->visual_scene->node;

		//删除library_visual_scenes里的 id="Character01-node" 的node节点
		$i=0;
		foreach ($tmp as $key => $rows) {
			$tmpp=$rows->attributes();
			if($tmpp['id']=="Character01-node"){
				//echo $Character01_node_xml;die;
				unset($test->library_visual_scenes->visual_scene->node[$i]);
				break;
			}
			$i++;
		}
		$i=0;
		//		删除library_visual_scenes里的 id="VisualSceneNode" 的node节点
		foreach ($tmp as $key => $rows) {
			$tmpp=$rows->attributes();
			if($tmpp['id']=="VisualSceneNode"){
				unset($test->library_visual_scenes->visual_scene->node[$i]);
				break;
			}
			$i++;
		}
		$path_parts = pathinfo($DAE_fileurl);
		$xml_path=str_replace($path_parts["extension"],"xml",$DAE_fileurl);
		$xml_path=str_replace($path_parts["basename"],"temp_".$path_parts["basename"],$xml_path);


		$test->asXML($xml_path);

		$zip = new ZipArchive();
		$filename = str_replace($path_parts["extension"],"zip",$DAE_fileurl);;
		if ($zip->open($filename, ZIPARCHIVE::CREATE)!==TRUE) {
			return  array(false,"cannot open <$filename>");
		}
		$zip_name=str_replace($path_parts["extension"],"DAE",$path_parts["basename"]);
		$zip->addFile($xml_path,$zip_name);
		$zip->close();

		unlink($xml_path);//删除临时xml文件
		return array(true,"finsh");
	}

	/**
	 *转换动作模型文件
	 */
	public function ConvertActionDAE($DAE_fileurl) {

		$xml=file_get_contents($DAE_fileurl);

		$test=simplexml_load_string($xml);
			
		if($test==false){
			return  array(false,"解析$DAE_fileurl文件失败!");
		}
			
		$obj_attributes=get_object_vars($test);
		foreach ($obj_attributes as $atr_key => $attr) {
			if($atr_key!="library_animations"){
				unset($test->$atr_key);
			}
		}

		$path_parts = pathinfo($DAE_fileurl);
		$xml_path=str_replace($path_parts["extension"],"xml",$DAE_fileurl);
		$xml_path=str_replace($path_parts["basename"],"temp_".$path_parts["basename"],$xml_path);

		$test->asXML($xml_path);

		$zip = new ZipArchive();
		$filename = str_replace($path_parts["extension"],"zip",$DAE_fileurl);
		if ($zip->open($filename, ZIPARCHIVE::CREATE)!==TRUE) {
			return  array(false,"cannot open <$filename>");
		}
		$zip_name=str_replace($path_parts["extension"],"DAE",$path_parts["basename"]);
		$zip->addFile($xml_path,$zip_name);
		$zip->close();
		unlink($xml_path);
		return array(true,"finsh");
	}


	/**
	 * 合并多个DAE动作文件
	 * @param $file_input_name	上传动作文件的input name
	 * @return array
	 */
	public	function mergeActionDAE($file_input_name) {
		include("../widget/DaeManage.class.php");
		$file_arr=array();
		$file_url="";
		$names=array();
		$dae = new DaeManage();
		$test=realpath('../../www/tmp');
		$dae->setCacheDir('../../www/tmp');
		foreach ($_FILES[$file_input_name]["error"] as $key => $error) {
			if ($error == UPLOAD_ERR_OK) {
				$ext=substr($_FILES[$file_input_name]['name'][$key],-4);
				if($ext!=".DAE") return array(false,"上传的模型文件必须是.DAE文件!");
				if(move_uploaded_file( $_FILES[$file_input_name]["tmp_name"][$key],'../../www/tmp/'. $_FILES[$file_input_name]["name"][$key])){
					$dae->setActionDae(array("name"=>str_ireplace(".DAE","",$_FILES[$file_input_name]["name"][$key]),"url"=> '../../www/tmp/'.$_FILES[$file_input_name]["name"][$key]));
				}
			}
		}//end foreach

		$dae->mergeAtionDae();
		$file_=$dae->zip();
		foreach ($_FILES[$file_input_name]["error"] as $key => $error) {
			if ($error == UPLOAD_ERR_OK) {
				if(is_file('../../www/tmp/'.$_FILES[$file_input_name]["name"][$key])){
					unlink('../../www/tmp/'.$_FILES[$file_input_name]["name"][$key]);
				}
			}
		}
			
		$path_parts=pathinfo ($file_);
		$url=$GLOBALS['gSiteInfo'] ['www_site_url'].'/tmp/'.str_replace($path_parts["extension"],"",$path_parts["basename"]).'zip';

		$arr=array();
		$arr['s']=200;
		$arr['m']="success";
		$arr['d']=array("name"=>str_replace(".DAE","",$path_parts["basename"]),"url"=>$url,"pics"=>array(),"type"=>"7");
			
		return array(true, json_encode($arr),"file_url"=>$url);
	 //	return array(true,"finsh");

	}




}





?>