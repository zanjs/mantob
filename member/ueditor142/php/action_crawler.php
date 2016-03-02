<?php
/**
 * 抓取远程图片
 * User: Jinqn
 * Date: 14-04-14
 * Time: 下午19:18
 */
set_time_limit(0);
include("Uploader.class.php");

/* 上传配置 */
$config = array(
    "pathFormat" => $CONFIG['catcherPathFormat'],
    "maxSize" => $CONFIG['catcherMaxSize'],
    "allowFiles" => $CONFIG['catcherAllowFiles'],
    "oriName" => "remote.png"
);
$fieldName = $CONFIG['catcherFieldName'];

/* 抓取远程图片 */
$list = array();
if (isset($_POST[$fieldName])) {
    $source = $_POST[$fieldName];
} else {
    $source = $_GET[$fieldName];
}
foreach ($source as $imgUrl) {
    $item = new Uploader($imgUrl, $config, "remote");
    $info = $item->getFileInfo();
    // 处理程序
    if (isset($info['state']) && $info['state'] == 'SUCCESS' && $info['size']) {
        $this->load->model('attachment_model');
        list($id, $file, $b) = $this->attachment_model->upload($this->uid, array(
            'file_ext' => $info['type'],
            'file_size' => $info['size'] / 1024,
            'full_path' => FCPATH.DR_UE_PATH.$info['url'],
            'client_name' => str_replace($info['type'], '', $info['original']),
        ));
        $info['id'] = 'mantob_img_'.$id;
        $info['url'] = $file;
    }
    array_push($list, array(
        "state" => $info["state"],
        "id" => $info['id'],
        "url" => $info["url"],
        "source" => $imgUrl
    ));
}

/* 返回抓取数据 */
return json_encode(array(
    'state'=> count($list) ? 'SUCCESS':'ERROR',
    'list'=> $list
));