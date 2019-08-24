<?php
include_once (MODX_BASE_PATH . 'assets/snippets/DocLister/lib/DLTemplate.class.php');
include_once (MODX_BASE_PATH . 'assets/lib/APIHelpers.class.php');

$TemplatePath = 'assets/snippets/fileManager/tpl/';
$TemplateExtension = 'tpl';
$DLTemplate = DLTemplate::getInstance($modx);
$DLTemplate->setTemplatePath($TemplatePath);
$DLTemplate->setTemplateExtension($TemplateExtension);
$uid = $modx->getLoginUserID('web');
$files_table = $modx->getFullTableName('userfiles');
if(!$uid){return false;}

if(!isset($mode))	$mode='user';
if($mode == 'admin'){
	//Секция загрузки
	$data['newfile_form'] = $modx->runSnippet('FormLister', array(
		'formid' => 'frm',
		'formControls' => 'userid',
		'prepare' => array(function($modx, $data, $FormLister, $name){
				$webuser_attr_table = $modx->getFullTableName('web_user_attributes');
				$web_groups_table = $modx->getFullTableName('web_groups');
				$webusers_table = $modx->getFullTableName('web_users');
				$_res = $modx->db->query("
				SELECT
					user_attr.internalKey,
					user_attr.fullname,
					users.username,
					users.id as uid
				FROM 
					$webuser_attr_table as user_attr
				JOIN  
					$web_groups_table as user_grp ON user_attr.internalKey = user_grp.webuser
				LEFT JOIN 
					$webusers_table as users ON user_attr.internalKey = users.id
				WHERE
					user_grp.webgroup = 1
				ORDER BY user_attr.internalKey ASC			
				");
				if($modx->db->getRecordCount($_res)>= 1){
					while($row = $modx->db->getRow( $_res )){
						if(empty($row['fullname'])){
							$row['fullname'] = $row['username'];
						}
						$_webusers .= '<option value="'.$row['internalKey'].'"  [+s.userid.'.$row['internalKey'].'+]>'. $row['fullname'] . ' ('.$row['uid'].')</option>';
					}
				}
				$data['users_list'] = $_webusers;
				
				
				$files_table = $modx->getFullTableName('userfiles');
				$_res2 = $modx->db->query("SELECT DISTINCT files_group_name FROM $files_table");
				if($modx->db->getRecordCount($_res2)>= 1){
					while($row = $modx->db->getRow( $_res2 )){
						$_optlist .= '<option>'.$row['files_group_name'].'</option>';
					}
				}			
				//<option>Аперитивы</option>
				$data['datalist'] = $_optlist;
			return $data;
		}),
		'prepareAfterProcess' => array(function($modx, $data, $FormLister, $name){
			$upload_path = "assets/files/userfiles/";
			$files_table = $modx->getFullTableName('userfiles');
			$dir = $upload_path .'uid_' . $data['userid'] . '/';
			$files = $FormLister->getFormData('files');
			$uid = $modx->getLoginUserID('web');
			if (isset($files['first']) && $files['first']['error'] === 0) {
				$fields = array('owner_id'  => $uid);
				$fields['file_description'] = $modx->db->escape( $data['file_description'] );
				$fields['files_group_name'] = $modx->db->escape( $data['files_group_name'] );
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
					"message":"Тут должна быть цифра"
				}
			},
			"file_description":{
				"required":"Обязательно заполните описание",
				"minLength":{
					"params":10,
					"message":"Должно быть не менее 10 символов"
				}
			},
			"files_group_name":{
				"required":"Выберите или введите тему"
			}
		}',	
		'fileRules' => '{
			"first":{
				"required":"Приложите документ",
				"allowed":{
					"params": [ ["doc","docx", "jpg", "jpeg", "png", "gif", "pdf", "xls", "xlsx", "txt", "rtf", "zip", "rar"] ],
					"message": "Разрешены документы Word, Excel, картинки и архивы"
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
	// загрузка всё
	
	//Удаление
	if($_GET['act'] == 'del'){
		$fid = $modx->db->escape($_GET['id']);
		$res  = $modx->db->select("id, real_filename,viewer_id,user_filename", $files_table,  "id=$fid  AND (owner_id=$uid)", "id DESC", 1);
		if( $modx->db->getRecordCount( $res ) == 1 ) {
			$res = $modx->db->getRow($res);
			$filepath =  $upload_path . 'uid_' . $res['viewer_id'] . '/'. $res['real_filename'];
			if($modx->db->delete($files_table, "id = " . $res['id'])){
				unlink(MODX_BASE_PATH . '/' . $filepath);
				$data['filemanager_messages'] = '<div class="alert alert-info">Удалили файл</div>';
			}
		}
		else{
			$data['filemanager_messages'] = '<div class="alert alert-info">Невозможно удалить</div>';
		}
	}
	
}

//Секция обзора файлов
$fileparams = array(
	'controller' => 'onetable',
	'idType' => 'documents',
	'table' => 'userfiles',
	'display' => 'all',
	'ignoreEmpty' => 1,
	'showParent' => 0,
	'idField' => 'id',
	'addWhereList' => 'c.owner_id =' . $uid,
	'dateSource' => 'upload_date',
	'dateFormat' => '%d.%m.%Y',
	'prepare' => array(function($data, $modx, $DocLister, $e){
		$data['fileextension_class'] = pathinfo($data['real_filename'], PATHINFO_EXTENSION);
		$webuser_attr_table = $modx->getFullTableName('web_user_attributes');
		$web_groups_table = $modx->getFullTableName('web_groups');
		$webusers_table = $modx->getFullTableName('web_users');
		$data['viewer_id'] = $modx->db->getValue( $modx->db->select('fullname', $webuser_attr_table, "internalKey = " . $data['viewer_id']) );
		return $data;
	})
);
if($mode=='admin'){
	$fileparams['tpl'] = '@FILE: fileform_row_admin';
	$fileparams['ownerTPL'] = '@FILE: files_outer_admin';
	$fileparams['orderBy'] = 'id DESC';
	
}
else{
	$fileparams['tpl'] = '@FILE: fileform_row';	
	$fileparams['ownerTPL'] = '@FILE: files_outer';	
	$fileparams['orderBy'] = 'files_group_name ASC, upload_date DESC';
	$fileparams['addWhereList'] = 'c.viewer_id = ' . $uid;
}
$data['allfiles_list'] = $modx->runSnippet('DocLister', $fileparams);


$tpl = '@FILE: main';
$out = $DLTemplate->parseChunk( $tpl, $data, true );

return $out;
 