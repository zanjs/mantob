<?php
/**
 * 获取已上传的文件列表
 * User: Jinqn
 * Date: 14-04-09
 * Time: 上午10:17
 */
include "Uploader.class.php";

/* 判断类型 */
switch ($_GET['action']) {
    /* 列出文件 */
    case 'listfile':
        $allowFiles = $CONFIG['fileManagerAllowFiles'];
        $listSize = $CONFIG['fileManagerListSize'];
        $path = $CONFIG['fileManagerListPath'];
        break;
    /* 列出图片 */
    case 'listimage':
    default:
        $allowFiles = $CONFIG['imageManagerAllowFiles'];
        $listSize = $CONFIG['imageManagerListSize'];
        $path = $CONFIG['imageManagerListPath'];
}
$allowFiles = substr(str_replace(".", ",", join("", $allowFiles)), 1);

/* 获取参数 */
$size = isset($_GET['size']) ? $_GET['size'] : $listSize;
$start = isset($_GET['start']) ? $_GET['start'] : 0;
$end = $start + $size;

/* 获取文件列表 */
$this->load->model('attachment_model');
$data = $this->attachment_model->get_unused($this->uid, $allowFiles);
if (!$data) {
    return json_encode(array(
        "state" => "no match file",
        "list" => array(),
        "start" => $start,
        "total" => count($files)
    ));
}

$files = array();
foreach ($data as $t) {
    $files[] = array(
        'id' => 'mantob_img_'.$t['id'],
        'url'=> dr_file($t['attachment']),
        'mtime'=> $t['inputtime']
    );
}

if (!count($files)) {
    return json_encode(array(
        "state" => "no match file",
        "list" => array(),
        "start" => $start,
        "total" => count($files)
    ));
}

/* 返回数据 */
$result = json_encode(array(
    "state" => "SUCCESS",
    "list" => $files,
    "start" => $start,
    "total" => count($files)
));

return $result;
