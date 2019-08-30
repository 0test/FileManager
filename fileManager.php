<?php
include_once (MODX_BASE_PATH . 'assets/snippets/DocLister/lib/DLTemplate.class.php');
include_once (MODX_BASE_PATH . 'assets/lib/APIHelpers.class.php');
$files_table = $modx->getFullTableName('userfiles');
$webuser_attr_table = $modx->getFullTableName('web_user_attributes');
$web_groups_table = $modx->getFullTableName('web_groups');
$webusers_table = $modx->getFullTableName('web_users');
$web_groups_name_table = $modx->getFullTableName('webgroup_names');

$uid = $modx->getLoginUserID('web');

if(!isset($upload_path))		{$upload_path = "assets/files/userfiles/";}
if(!isset($noneTPL))			{$noneTPL = "@FILE: empty_files";}

if(!isset($TemplatePath))		{$TemplatePath = 'assets/snippets/fileManager/tpl/';}
if(!isset($TemplateExtension))	{$TemplateExtension = 'tpl';}
if(!isset($users_group))		{$where_users_in = ' WHERE user.id !=' . $uid;}
else{
	$users_group = explode(",",$users_group);
	foreach($users_group as $one_users_group){
		 $_nums[]= $modx->db->getValue( $modx->db->select('id', $web_groups_name_table, "name = '$one_users_group'") );
	}
	$where_users_in = ' WHERE user_grp.webgroup IN('.implode(",",$_nums).') AND user.id !=' . $uid;
}
if(!isset($formValidateRules))	{$formValidateRules = '{"userid":{"required":"Обязательно введите имя","numeric":{"message":"Тут должна быть цифра"}},"file_description":{"required":"Обязательно заполните описание","minLength":{"params":5,"message":"Должно быть не менее 5 символов"}}}';
}
if(!isset($formFileRules))	{$formFileRules = '{"first":{"required":"Приложите документ","allowed":{"params": [ ["doc","docx", "jpg", "jpeg", "png", "gif", "pdf", "xls", "xlsx", "txt", "rtf", "zip", "rar"] ],"message": "Разрешены документы Word, Excel, картинки и архивы"},"maxSize" : {"params": 10000,"message": "Размер файла не должен превышать 10000 кб"}}}';
}

		
if(!isset($mode))	$mode='user';

$DLTemplate = DLTemplate::getInstance($modx);
$DLTemplate->setTemplatePath($TemplatePath);
$DLTemplate->setTemplateExtension($TemplateExtension);



				
if(!$uid){
	$tpl = '@FILE: not_login';
	$out = $DLTemplate->parseChunk( $tpl, ['text'=>''], true );
	return false;
}


if($mode == 'admin'){
	//Секция загрузки
	$data['newfile_form'] = $modx->runSnippet('FormLister', array(
		'formid' => 'frm',
		'formControls' => 'userid',
		'prepare' => array(function($modx, $data, $FormLister, $name) use ($webuser_attr_table, $web_groups_table, $webusers_table, $where_users_in){
				$_res = $modx->db->query("
				SELECT
					user.id as uid,
					user_attr.fullname,
					user.username					
				FROM 
					$webuser_attr_table as user_attr
				JOIN  
					$web_groups_table as user_grp ON user_attr.internalKey = user_grp.webuser
				LEFT JOIN 
					$webusers_table as user ON user_attr.internalKey = user.id
				$where_users_in
				ORDER BY user.id ASC
				");
				if($modx->db->getRecordCount($_res)>= 1){
					while($row = $modx->db->getRow( $_res )){
						if(empty($row['fullname'])){
							$row['fullname'] = $row['username'];
						}
						$_webusers .= '<option value="'.$row['uid'].'"  [+s.userid.'.$row['uid'].'+]>'. $row['fullname'] . ' ('.$row['uid'].')</option>';
					}
				}
				$data['users_list'] = $_webusers;
			return $data;
		}),
		'prepareAfterProcess' => array(function($modx, $data, $FormLister, $name) use ($files_table, $upload_path, $webusers_table, $uid){
			$dir = $upload_path .'uid_' . $data['userid'] . '/';
			$files = $FormLister->getFormData('files');
			if (isset($files['first']) && $files['first']['error'] === 0) {
				$fields = array('owner_id'  => $uid);
				$fields['file_description'] = $modx->db->escape( $data['file_description'] );
				$filename = $FormLister->fs->takeFileName($files['first']['name']); 
				$ext = $FormLister->fs->takeFileExt($files['first']['name']);
				$fields['user_filename'] = $filename . '.' .$ext;
				$filename = $modx->stripAlias($filename).'.'.$ext;
				$filename = $FormLister->fs->getInexistantFilename($dir.$filename,true);
				if ($FormLister->fs->makeDir($dir) && move_uploaded_file($files['first']['tmp_name'],$filename)) {
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
		'rules' => $formValidateRules,	
		'fileRules' => $formFileRules,
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
	'prepare' => array(function($data, $modx, $DocLister, $e) use ($webuser_attr_table, $web_groups_table, $webusers_table){
		$data['fileextension_class'] = pathinfo($data['real_filename'], PATHINFO_EXTENSION);
		$data['viewer_id'] = $modx->db->getValue( $modx->db->select('fullname', $webuser_attr_table, "internalKey = " . $data['viewer_id']) );
		return $data;
	})
);
if($mode=='admin'){
	$fileparams['tpl'] = '@FILE: fileform_row_admin';
	$fileparams['ownerTPL'] = '@FILE: files_outer_admin';
	$fileparams['orderBy'] = 'id DESC';
	
}
elseif($mode=='user'){ 
	$fileparams['tpl'] = '@FILE: fileform_row';	
	$fileparams['ownerTPL'] = '@FILE: files_outer';	
	$fileparams['noneWrapOuter'] = '0';
	$fileparams['noneTPL'] = $noneTPL;
	$fileparams['orderBy'] = 'files_group_name ASC, upload_date DESC';
	$fileparams['addWhereList'] = 'c.viewer_id = ' . $uid;
}
$data['allfiles_list'] = $modx->runSnippet('DocLister', $fileparams);


$tpl = '@FILE: main';
$out = $DLTemplate->parseChunk( $tpl, $data, true );

return $out;
 