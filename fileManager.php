<?php
include_once (MODX_BASE_PATH . 'assets/snippets/DocLister/lib/DLTemplate.class.php');
include_once (MODX_BASE_PATH . 'assets/lib/APIHelpers.class.php');

$TemplatePath = 'assets/snippets/fileManager/tpl/';
$TemplateExtension = 'tpl';
$DLTemplate = DLTemplate::getInstance($modx);
$DLTemplate->setTemplatePath($TemplatePath);
$DLTemplate->setTemplateExtension($TemplateExtension);


$uid = $modx->getLoginUserID('web');

if(!$uid){return false;}



//Секция загрузки
$data['newfile_form'] = $modx->runSnippet('FormLister', array(
	'formid' => 'frm',
	'prepareAfterProcess' => array(function($modx, $data, $FormLister, $name){
		$upload_path = "assets/files/userfiles/";
		$files_table = $modx->getFullTableName('userfiles');
		$dir = $upload_path .'uid_' . $data['userid'] . '/';
		$files = $FormLister->getFormData('files');
		$uid = $modx->getLoginUserID('web');
		if (isset($files['first']) && $files['first']['error'] === 0) {
			$fields = array('owner_id'  => $uid);
			//имя
			$filename = $FormLister->fs->takeFileName($files['first']['name']);
			//расширение 
			$ext = $FormLister->fs->takeFileExt($files['first']['name']);
			$fields['user_filename'] = $filename . '.' .$ext;
			//делаем транслитерацию и добавляем к нему расширение
			$filename = $modx->stripAlias($filename).'.'.$ext;
			//предполагаемый путь к файлу, при необходимости переименовываем, чтобы не затереть файл
			$filename = $FormLister->fs->getInexistantFilename($dir.$filename,true);
			if ($FormLister->fs->makeDir($dir) && move_uploaded_file($files['first']['tmp_name'],$filename)) {
				//если получилось, то сохраняем в поле относительный путь к файлу. Можно заюзать в плейсхолдере
				$FormLister->setField('uploaded_filename',$FormLister->fs->relativePath($filename));
				$fields['real_filename'] = $FormLister->fs->takeFileName($filename) . '.' . $FormLister->fs->takeFileExt($filename);
				$fields['viewer_id'] = (int)$data['userid'];
				$modx->db->insert( $fields, $files_table);  				
			}
		}
		return $data;
	}),      
	'removeEmptyPlaceholders' => 1,
	'attachments' => 'first',
	'rules' => '{
		"userid":{
			"required":"Обязательно введите имя",
			"numeric":{
				"message":"Какая-то херня, тут должна быть цифра"
			}
		}
	}',	
	'fileRules' => '{
		"first":{
			"required":"Приложите документ",
			"allowed":{
				"params": [ ["doc","docx"] ],
				"message": "Разрешены только документы Word"
			},
			"maxSize" : {
				"params": 10000,
				"message": "Размер файла не должен превышать 10000 кб"
			}
		}
	}',
	'formTpl' => '@FILE: fileform',
	'successTpl' => '@CODE: <div class="alert alert-info" role="alert">Файл добавлен. <a href="[~65~]">Ещё?</a></div>',
	'noemail' => 1,
	'errorClass' => ' has-error',
	'requiredClass' => ' has-warning',
	'messagesOuterTpl' => '@CODE:<div class="alert alert-danger" role="alert">[+messages+]</div>',
	'errorTpl' => '@CODE:<span class="help-block">[+message+]</span>',
	'onlyUsers'=> 1,
	'protectSubmit' => 0,
	'submitLimit' => 0,
));


//Секция обзора файлов
$data['allfiles_list'] = $modx->runSnippet('DocLister', array(
	'controller' => 'onetable',
	'idType' => 'documents',
	'table' => 'userfiles',
	'display' => 'all',
	'ignoreEmpty' => 1,
	'sortBy' => 'id',
	'sortDir' => 'DESC',
	'tpl' => '@FILE: fileform_row',
	'showParent' => 0,
	'idField' => 'id',
	'addWhereList' => 'c.owner_id =' . $uid,
	'ownerTPL' => '@FILE: files_outer',
));


$tpl = '@FILE: main';
$out = $DLTemplate->parseChunk( $tpl, $data, true );

return $out;
 