<?php

/*----------------------------�ļ��ϴ���---------------------------------

//�ļ���: upload.class.php
//����ʱ��: 2007-05-15
//������ʱ��: 2006-06-25
//����ά����: GTZHAO
//�汾: 2.0
//����: �ļ��ϴ���

/////////////// ʹ��)չ�����б� ////////////////

{core}/function/file.php	//�����ļ�����)չ����
- size_unit_convert
- parse_path
- create_dir

{core}/function/string.php	//�����ַ����)չ����
- find_in_set

//////////////// ʹ�û����б� /////////////////

/////////////////// ����˵�� ////////////////////

//����:HTTP_Uploader
//��������:֧�ֵ��ļ����ļ��ϴ�
//ʹ��˵������

//�����ϴ�����
$upload = new HTTP_Uploader;

//�����Զ����ļ���(����)չ��)
$upload->setCustomName('filename');	

//�����ϴ����ļ�)չ�� ��Ҫ�ӵ�
//��������������ļ��ϴ��� ��д 'all'
$upload->setAllowExtension('jpg,gif,jpeg,png,doc');

//�����д��ֵ����λ��Ĭ��Ϊ�ֽڣ���������ֵ������뵥λ �� KB,MB,GB,TB
$upload->setMaxSize('1000KB');

//0.ʹ��ԭ�ļ��� 1.�Զ�����ļ��� 2.Ϊָ���ļ��� 3.ʹ��ԭ�ļ�����Զ������
$upload->setNamingRule(1);			

//�ϴ�·��(�ϴ�·�����Զ�����)
$upload->setSaveDir('./'.date('Y/m/d'));

//ִ���ϴ� �ϴ��ɹ����� true ���� false
$upload->upload($_FILES['upload']);

//�����ϴ��ɹ����ļ���Ϣ���� ( ���ϴ�ģʽΪ multiple ��ʱ�򷵻صĶ��ļ���Ϣ������� foreach ֱ��ѭ�� )
$upload->result();

//���ش���ԭ��
$upload->getError();

//�����ϴ��ļ���
$upload->getCount();

/////////////////// �������� ////////////////////

//HTML input ��ǩ�� ���ļ�Ϊ name='xxx' ����ʽ ���ļ�Ϊ name='xxx[]' ��ο�PHP�ֲ���ļ��ϴ�
//}����ʽ�ϴ� upload �����Ϊ $_FILES['xxx'] ���Զ��ж��Ƕ��ļ����ǵ��ļ�

$upload = new HTTP_Uploader;
$upload->setAllowExtension('jpg,gif,jpeg,png,doc,rar,xls');//��������������ļ��ϴ��� ��д 'all'
$upload->setMaxSize('1MB');
$upload->setNamingRule(1);
$upload->setSaveDir(date('Y/m/d'));
if(!$upload->upload($_FILES['upload']))
{
	print_r($upload->getError());
	exit();
}

print_r($upload->result()).'<br>';
print_r($upload->getCount());

//HTML����
<form method=post action="?operation=upload" enctype="multipart/form-data">
<input type="file" name="upload">
<input type="submit">
</form>

---------------------------------------------------------------------------*/

//include_once('file.php');//�����ļ�����)չ����
include_once('string.php');//�����ַ����)չ����

class HTTP_Uploader
{
	/*-------------���б�--------------*/

	var $uploadConfig = array();
	var $uploadList = array();
	var $uploadMode = 'single';//single || multiple
	var $uploadCount = array();
	var $saveFile = array();
	var $error;

	/*-------------���캯��--------------*/

	function HTTP_Uploader()
	{
		$this->setAllowExtension('all');
		$this->setMaxSize('2MB');
		$this->setNamingRule(1);
	}

	/*-----------�������Է���------------*/
	
	//�����Զ����ļ��� [ ����)չ�� ] ����)չ�����Զ�ȥ��
	function setCustomName($customName='')
	{
		$this->uploadConfig['customName'] = $customName ;
	}

	//���������ϴ��ļ����ļ�)չ�� �� ',' �ָ�
	function setAllowExtension($allowExtension)
	{
		$this->uploadConfig['allowExtension'] = $allowExtension ;
	}

	//�����ϴ��ļ�������� (��λΪ B,KB,MB,GB)
	function setMaxSize($maxsize)
	{
		$this->uploadConfig['maxsize'] = str_replace('b','',strtolower(size_unit_convert($maxsize,'b')));//ʹ����)չ���� size_unit_convert
	}

	//�����ϴ�
	function setNamingRule($namingRule)
	{
		$this->uploadConfig['namingRule'] = $namingRule ;
	}

	//�����ļ����Ŀ¼
	function setSaveDir($path)
	{
		$this->uploadConfig['saveDir'] = path_clean($path);
	}

	//����ļ�����·��
	function getSavePath($oldFileName)
	{
		$pathinfo = parse_path($oldFileName);//ʹ����)չ���� parse_path
		$fileTimeName = date('YmdHis').substr(microtime(),2,6);//������ظ�

		switch($this->uploadConfig['namingRule'])
		{
			//(0.ʹ��ԭ�ļ���1.�Զ�����ļ���2.Ϊָ���ļ���3.ʹ��ԭ�ļ�����Զ������)
			
			case 0:
				$newFileName = $oldFileName;
			break;

			case 1:
				$newFileName = $fileTimeName.'.'.$pathinfo['extension'];
			break;

			case 2:
				$newFileName = $this->uploadConfig['customName'].'.'.$pathinfo['extension'];
			break;

			case 3:
				$newFileName = $pathinfo['basename'].'_'.$fileTimeName.'.'.$pathinfo['extension'];
			break;
		}

		$this->saveFile['filename'] = strrpos($oldFileName,'.') === false ? str_replace('.','',$newFileName) : $newFileName ;
		$tmpPathInfo = parse_path($newFileName);
		$this->saveFile['basename'] = strrpos($oldFileName,'.') === false ? str_replace('.','',$tmpPathInfo['basename']) : $tmpPathInfo['basename'] ;
		$this->saveFile['path'] = path_clean($this->uploadConfig['saveDir'].'/'.$this->saveFile['filename']);

		return $this->saveFile['path'];
	}

	//����:ִ���ϴ�
	//����:$name ��ӦHTML�?�е��ļ��ϴ� ��ǩ
	//����:true|false
	function upload($upfile)
	{

		//�ж����Ϊ���ļ��ϴ��������ļ�����
		if(is_array($upfile['name']))
		{
			//��ȡ���ļ�����Ϣ ��ÿ���ļ�ת���ɵ��ļ�ģʽ ���ϴ�
			foreach($upfile['name'] as $key => $filename)
			{
				if($filename!=='')
				{
					$tmpUpfile = array
					(
						'name' => $upfile['name'][$key],
						'type' => $upfile['type'][$key],
						'size' => $upfile['size'][$key],
						'tmp_name' => $upfile['tmp_name'][$key]
					);
					
					//�����Լ����е��ļ��ϴ�
					//�ϴ��ļ���������һ���ļ�����Ϲ涨 ��ع�ɾ���Ѿ��ϴ����ļ��� ���ش�����Ϣ
					if(!$this->upload($tmpUpfile))
					{
						foreach($this->uploadList as $tmpListRs)
						{
							@unlink($tmpListRs['path']);
						}

						$this->uploadList = array();
						$this->uploadCount = 0 ;
						return false;
					}
				}
			}
	
			//�����ϴ�ģʽ multiple
			$this->uploadMode = 'multiple';
		}
		else
		{
			if(!$upfile['name'])
			{
				$this->setError(LANG_HTTP_UPLOADER_FILE_ERROR);
				return false;
			}
			
			//���Ϊ��ԭ�ļ������ļ���ȫ��Сд
			$upfile['name'] = $this->uploadConfig['namingRule'] > 0 ? strtolower($upfile['name']) : $upfile['name'];

			$upfileInfo = @parse_path($upfile['name']);//ʹ����)չ���� parse_path

			//����ϴ��ļ���)չ���Ƿ������������б���
			if(!find_in_set($upfileInfo['extension'],$this->uploadConfig['allowExtension']) && ($this->uploadConfig['allowExtension']!='all'))//ʹ����)չ���� find_in_set
			{
				$this->setError( LANG_HTTP_UPLOADER_EXTENSION_ERROR.$this->uploadConfig['allowExtension'] );
				return false;
			}
			
			//����ļ���С�Ƿ����ϴ�����֮��
			if($upfile['size'] > $this->uploadConfig['maxsize'])
			{
				$this->setError(LANG_HTTP_UPLOADER_FILE_SIZE_ERROR.size_unit_convert($this->uploadConfig['maxsize']));//ʹ����)չ���� size_unit_convert
				return false;
			}

			//����Ŀ¼
			if(!create_dir($this->uploadConfig['saveDir']))//ʹ����)չ���� create_dir
			{
				$this->setError(LANG_HTTP_UPLOADER_CREATE_DIR_ERROR);
				return false;
			}

			//���ϴ���ɵ���ʱ�ļ��ƶ���ָ��λ��
			if(!move_uploaded_file($upfile['tmp_name'],$this->getSavePath($upfile['name'])))
			{
				$this->setError(LANG_HTTP_UPLOADER_ERROR);
				return false;
			}
			
			//�ϴ��ɹ���ʼ�����ϴ��ļ���Ϣ�б�
			$this->uploadList[] = array
			(
				'filename' => $upfileInfo['filename'],
				'basename' => $upfileInfo['basename'],
				'save_filename' => $this->saveFile['filename'],
				'save_basename' => $this->saveFile['basename'],
				'extension' => $upfileInfo['extension'],
				'size' => $upfile['size'],
				'type' => $upfile['type'],
				'dir' => $this->uploadConfig['saveDir'],
				'path' => $this->saveFile['path']
			);
			
			//�����ϴ�ģʽ single
			$this->uploadMode = 'single';
		}

		return true;
	}

	//����:��ȡ�������
	//����:��Ϊ���ļ��ϴ�ʱ �����±�Ϊ filename,basename,extension,size,type,path �ļ���Ϣ������
	//����:��Ϊ���ļ��ϴ�ʱ���� ������ foreach ֱ�ӱ�������� ÿ������͵��ļ��ϴ�������Ϣ��ͬ
	function result()
	{
		$resultValue = $this->uploadMode=='single' ? $this->uploadList[0] : $this->uploadList ;
		return $resultValue;
	}

	//����:��ȡ�ɹ��ϴ����ļ���
	function getCount()
	{
		return count($this->uploadList);
	}

	//����:���ô�����Ϣ
	function setError($error)
	{
		$this->error = $error;
	}

	//����:���ش�����Ϣ
	//����:������Ϣ
	function getError()
	{
		return $this->error;
	}

}//end class

?>