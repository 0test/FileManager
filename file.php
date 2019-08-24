<?php 

define('MODX_API_MODE', true);
define('IN_MANAGER_MODE', true);

include_once("../../../index.php");
$modx->db->connect();
if (empty ($modx->config)) {
    $modx->getSettings();
}
$uid = $modx->getLoginUserID('web');
$fid = $modx->db->escape($_GET['id']);
if(!$uid || !$fid ){$modx->sendForward($modx->config['error_page']); return false;}

$upload_path = "assets/files/userfiles/";
$files_table = $modx->getFullTableName('userfiles');

if($_GET['act'] == 'show'){
	$res  = $modx->db->select("real_filename,viewer_id,user_filename", $files_table,  "id=$fid  AND (owner_id=$uid OR viewer_id=$uid)", "id DESC", 1);
	  

	if( $modx->db->getRecordCount( $res ) == 1 ) {
		$res = $modx->db->getRow($res);
		$filepath =  $upload_path . 'uid_' . $res['viewer_id'] . '/'. $res['real_filename'];
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename=' . $res['user_filename']);
		exit(readfile(MODX_BASE_PATH. '/' . $filepath));
	}
	else{
		echo 'file not found';
	}
}
