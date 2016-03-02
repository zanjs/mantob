<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Omweb Website Management System
 *
 * @since	    version 2.3.0
 * @author	    mantob <kefu@mantob.com>
 * @license     http://www.mantob.com/license
 * @copyright   Copyright (c) 2013 - 9999, mantob.Com, Inc.
 */
class C_Model extends CI_Model {

    public $link; // 当前模型的数据库对象
    public $where; // 管理角色组数据筛选条件
    public $prefix; // 主表名称（其他表的前缀部分）
    public $cache_file;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->link = $this->site[SITE_ID];
        $this->prefix = $this->db->dbprefix(SITE_ID.'_'.APP_DIR);
        // 管理角色组数据筛选条件
        if (IS_ADMIN && $this->admin['adminid'] > 1) {
            $catid = array();
            $category = $this->ci->get_cache('module-'.SITE_ID.'-'.APP_DIR, 'category');
            if ($category) {
                foreach ($category as $c) {
                    // 具有管理权限的栏目id集合
                    if (!$c['child']
                        && $c['setting']['admin'][$this->admin['adminid']]['show'] == 1) {
                        $catid[] = $c['id'];
                    }
                }
                $this->where = '`catid` IN ('.implode(',', $catid).')';
                unset($category);
            }
        }
    }

    /**
     * 条件查询
     *
     * @param	object	$select	查询对象
     * @param	array	$param	条件参数
     * @return	array	
     */
    private function _where(&$select, $param) {

        $_param = array();
        $this->cache_file = md5($this->duri->uri(1).$this->uid.SITE_ID.$this->input->ip_address().$this->input->user_agent()); // 缓存文件名称
        // 存在POST提交时，重新生成缓存文件
        if (IS_POST) {
            $data = $this->input->post('data');
            foreach ($data as $i => $t) {
                if (!$t) {
                    unset($data[$i]);
                }
            }
            $this->cache->file->save($this->cache_file, $data, 3600);
            $param['search'] = 1;
            unset($_GET['page']);
        }

        // 存在search参数时，读取缓存文件
        if ($param['search'] == 1) {
            $data = $this->cache->file->get($this->cache_file);
            $_param['search'] = 1;
            if (isset($data['keyword']) && $data['keyword'] && $data['field']) {
                if ($data['field'] == 'id') {
                    $id = array();
                    $ids = explode(',', $data['keyword']);
                    foreach ($ids as $i) {
                        $id[] = (int) $i;
                    }
                    $select->where_in('id', $id);
                } else {
                    $select->like($data['field'], urldecode($data['keyword']));
                }
            }
            // 时间搜索
            if (isset($data['start']) && $data['start']) {
                $data['end'] = strtotime(date('Y-m-d 23:59:59', $data['end'] ? $data['end'] : SYS_TIME));
                $data['start'] = strtotime(date('Y-m-d 00:00:00', $data['start']));
                $select->where('updatetime BETWEEN '.$data['start'].' AND '.$data['end']);
            } elseif (isset($data['end']) && $data['end']) {
                $data['end'] = strtotime(date('Y-m-d 23:59:59', $data['end']));
                $data['start'] = 0;
                $select->where('updatetime BETWEEN '.$data['start'].' AND '.$data['end']);
            }
            if (isset($data['catid'])) {
                $param['catid'] = $data['catid'];
            }
        }

        if (isset($param['flag'])) {
            $_param['flag'] = $param['flag'];
            $select->where('flag', $param['flag']);
        }

        if (isset($param['catid']) && $param['catid']) {
            $_param['catid'] = $param['catid'];
            $cat = $this->ci->get_cache('module-'.SITE_ID.'-'.APP_DIR, 'category', $param['catid']);
            if ($cat['child']) {
                $select->where_in('catid', explode(',', $cat['childids']));
            } else {
                $select->where('catid', $param['catid']);
            }
        }

        if ($this->where) {
            $select->where($this->where);
        }

        return $_param;
    }

    /**
     * 数据分页显示
     *
     * @param	array	$param	条件参数
     * @param	intval	$page	页数
     * @param	intval	$total	总数据
     * @return	array	
     */
    public function limit_page($param, $page, $total) {

        if (!$total || IS_POST) {
            $select = $this->link->select('count(*) as total');
            $_param = $this->_where($select, $param);
            if ($_param) {
                $select->order_by('id');
            }
            $data = $select->get(isset($param['flag']) ? $this->prefix.'_flag' : $this->prefix)->row_array();
            unset($select);
            $total = (int) $data['total'];
            if (!$total) {
                return array(array(), array('total' => 0));
            }
            $page = 1;
        }

        $select = $this->link->limit(SITE_ADMIN_PAGESIZE, SITE_ADMIN_PAGESIZE * ($page - 1));
        $_param = $this->_where($select, $param);
        $_order = isset($_GET['order']) && strpos($_GET['order'], "undefined") !== 0 ? $this->input->get('order') : 'displayorder DESC,updatetime DESC';
        if (isset($_param['flag'])) {
            $in = array();
            $ids = $select->select('id')->get($this->prefix.'_flag')->result_array();
            if ($ids) {
                foreach ($ids as $t) {
                    $in[] = $t['id'];
                }
                $data = $this->link
                             ->where_in('id', $in)
                             ->order_by($_order)
                             ->get($this->prefix)
                             ->result_array();
            }
        } else {
            $data = $select->order_by($_order)
                           ->get($this->prefix)
                           ->result_array();
        }
        $_param['total'] = $total;
        $_param['order'] = $_order;

        return array($data, $_param);
    }

    /**
     * 条件查询
     *
     * @param	object	$select	查询对象
     * @param	array	$param	条件参数
     * @param	intval	$cid	文档id
     * @return	array	
     */
    private function _extend_where(&$select, $param, $cid) {

        $_param = array();
        // 缓存文件名称
        $this->cache_file = md5($this->duri->uri(1).$this->uid.$cid.SITE_ID.$this->input->ip_address().$this->input->user_agent());
        // 存在POST提交时，重新生成缓存文件
        if (IS_POST) {
            $data = $this->input->post('data');
            foreach ($data as $i => $t) {
                if (!$t) {
                    unset($data[$i]);
                }
            }
            $this->cache->file->save($this->cache_file, $data, 3600);
            $param['search'] = 1;
            unset($_GET['page']);
        }
        // 存在search参数时，读取缓存文件
        if ($param['search'] == 1) {
            $data = $this->cache->file->get($this->cache_file);
            $_param['search'] = 1;
            if (isset($data['keyword']) && $data['keyword']) {
                $select->like('name', urldecode($data['keyword']));
            }
            // 时间搜索
            if (isset($data['start']) && $data['start']) {
                $data['end'] = strtotime(date('Y-m-d 23:59:59', $data['end'] ? $data['end'] : SYS_TIME));
                $data['start'] = strtotime(date('Y-m-d 00:00:00', $data['start']));
                $select->where('updatetime BETWEEN '.$data['start'].' AND '.$data['end']);
            } elseif (isset($data['end']) && $data['end']) {
                $data['end'] = strtotime(date('Y-m-d 23:59:59', $data['end']));
                $data['start'] = 0;
                $select->where('updatetime BETWEEN '.$data['start'].' AND '.$data['end']);
            }
        }
        // 类别判断
        if (isset($param['type'])) {
            $_param['type'] = (int)$param['type'];
            $select->where('mytype', (int)$param['type']);
        }
        $select->where('cid', (int)$cid);

        return $_param;
    }

    /**
     * 数据分页显示
     *
     * @param	intval	$cid	文档id
     * @param	array	$param	条件参数
     * @return	array	
     */
    public function extend_limit_page($cid, $param) {

        if (IS_POST) {
            $page = 1;
            $total = 0;
        } else {
            $page = max((int) $this->input->get('page'), 1);
            $total = (int) $this->input->get('total');
        }
        if (!$total) {
            $select = $this->link->select('count(*) as total');
            $_param = $this->_extend_where($select, $param, $cid);
            if ($_param) {
                $select->order_by('id');
            }
            $data = $select->get($this->prefix.'_extend')->row_array();
            unset($select);
            $total = (int) $data['total'];
            if (!$total) {
                return array(array(), array('total' => 0));
            }
        }

        $select = $this->link->limit(SITE_ADMIN_PAGESIZE, SITE_ADMIN_PAGESIZE * ($page - 1));
        $_param = $this->_extend_where($select, $param, $cid);

        $data = $select->order_by('displayorder DESC,inputtime DESC')
                       ->get($this->prefix.'_extend')
                       ->result_array();
        $_param['total'] = $total;

        return array($data, $_param);
    }

    /**
     * 发布前，先生成一个索引数据
     *
     * @param	array	$data
     * @return	array	
     */
    private function index($data) {

        $this->link->insert($this->prefix.'_index', array(
            'uid' => $data[1]['uid'],
            'catid' => $data[1]['catid'],
            'status' => $data[1]['status'],
            'inputtime' => $data[1]['inputtime']
        ));

        return $this->link->insert_id();
    }

    /**
     * 发布
     *
     * @param	array	$data
     * @return	array	
     */
    public function add($data) {

        // 发布之前挂钩点
        $this->hooks->call_hook('content_add_before', $data);

        // 生成索引id
        $data[0]['id'] = $data[1]['id'] = $id = $this->index($data);
        $data[0]['uid'] = $data[1]['uid'];
        $data[1]['hits'] = 0;
        $data[0]['catid'] = $data[1]['catid'];

        if (!$id) {
            return FALSE;
        }

        // 副表以5w左右数据量无限分表
        $data[1]['tableid'] = floor($id / 50000);
        // 格式化字段值
        $data = $this->get_content_data($data);
        if ($data[1]['status'] == 9) { // 审核通过
            if ($this->is_category) {
                $data = $this->replace_category_data($id, $data);
            }
            $data[1]['url'] = dr_show_url($this->ci->get_cache('module-'.SITE_ID.'-'.APP_DIR), $data[1]);
            if (!$this->link->query("SHOW TABLES LIKE '".$this->prefix.'_data_'.$data[1]['tableid']."'")->row_array()) {
                // 附表不存在时创建附表
                $sql = $this->link->query("SHOW CREATE TABLE `{$this->prefix}_data_0`")->row_array();
                $this->link->query(str_replace(
                    array($sql['Table'], 'CREATE TABLE '),
                    array($this->prefix.'_data_'.$data[1]['tableid'], 'CREATE TABLE IF NOT EXISTS '),
                    $sql['Create Table']
                ));
            }
            $this->link->replace($this->prefix, $data[1]); // 主表
            $this->link->replace($this->prefix.'_data_'.$data[1]['tableid'], $data[0]); // 副表
            if (isset($data[1]['keywords']) && $data[1]['keywords']) {
                $this->update_tag($data[1]['keywords']); // 更新tag表
            }
            $this->sns_share($data[1]); // 分享信息
        } else {
            // 非审核通过状态的文档写入审核表
            $verify = array(
                'id' => $data[1]['id'],
                'uid' => $data[1]['uid'],
                'catid' => $data[1]['catid'],
                'author' => $data[1]['author'],
                'status' => $data[1]['status'],
                'content' => dr_array2string(array_merge($data[0], $data[1])),
                'backuid' => 0,
                'backinfo' => '',
                'inputtime' => $data[1]['inputtime']
            );
            $this->link->replace($this->prefix.'_verify', $verify); // 审核表
            unset($verify);
        }

        // 发布之后挂钩点
        $this->hooks->call_hook('content_add_after', $data);
        $this->hooks->call_hook('syn_content_add', array('syn' => $this->syn_content, 'data' => $data));

        return $id;
    }

    // 修改
    public function edit($_data, $data) {

        // 参数判断
        if (!$data || !$_data) {
            return FALSE;
        }

        // 被退回处理
        if (isset($data[1]['back']) && $this->admin) {
            $backinfo = array(
                'uid' => $this->uid,
                'author' => $this->admin['username'],
                'rolename' => $this->admin['role']['name'],
                'optiontime' => SYS_TIME,
                'backcontent' => $data[1]['back']
            );
            unset($data[1]['back']);
        }
        // 格式化字段值
        $data = $this->get_content_data($data, $_data);
        // 分析栏目字段数据
        if ($this->is_category) {
            $data = $this->replace_category_data($_data['id'], $data);
        }
        // 生成url地址
        $data[1]['url'] = dr_show_url($this->ci->get_cache('MODULE-'.SITE_ID.'-'.APP_DIR), array_merge($_data, $data[1]));
        // 更新索引表
        $this->link
             ->where('id', $_data['id'])
             ->update($this->prefix.'_index', array(
                'uid' => $data[1]['uid'],
                'catid' => $data[1]['catid'],
                'status' => $data[1]['status']
        ));

        if ($data[1]['status'] == 9) {
            // 提交为审核通过状态
            $data[1]['id'] = $data[0]['id'] = $_data['id'];
            $data[0]['uid'] = $data[1]['uid'];
            $data[0]['catid'] = $data[1]['catid'];
            // 副表以5w左右数据量无限分表
            $data[1]['tableid'] = $_data['tableid'] ? $_data['tableid'] : floor($_data['id'] / 50000);
            if (!$this->link->query("SHOW TABLES LIKE '".$this->prefix.'_data_'.$data[1]['tableid']."'")->row_array()) {
                // 附表不存在时创建附表
                $sql = $this->link->query("SHOW CREATE TABLE `{$this->prefix}_data_0`")->row_array();
                $this->link->query(str_replace(
                    array($sql['Table'], 'CREATE TABLE '),
                    array($this->prefix.'_data_'.$data[1]['tableid'], 'CREATE TABLE IF NOT EXISTS '),
                    $sql['Create Table']
                ));
            }
            // 主表更新
            if ($this->link->where('id', $_data['id'])->count_all_results($this->prefix)) {
                $this->link->where('id', $_data['id'])->update($this->prefix, $data[1]);
            } else {
                $this->link->replace($this->prefix, $data[1]);
            }
            $this->link // 副表
                 ->replace($this->prefix.'_data_'.$data[1]['tableid'], $data[0]);

            $this->link // 审核表
                 ->where('id', $_data['id'])
                 ->delete($this->prefix.'_verify');
            // 更新tag表
            if (isset($data[1]['keywords']) && $data[1]['keywords']) {
                $this->update_tag($data[1]['keywords']);
            }
            //$this->sns_share($data[1]); // 分享信息
        } else {
            // 检查合并审核数据
            $content = $data[0] ? array_merge($data[0], $data[1]) : $data[1];
            if (!$content) {
                return FALSE;
            }
            // 更新主表
            $this->link->where('id', $_data['id'])->update($this->prefix, array('status' => $data[1]['status']));
            // 更新审核表
            $this->link->replace($this->prefix.'_verify', array(
                'id' => $_data['id'],
                'uid' => $data[1]['uid'],
                'catid' => $data[1]['catid'],
                'author' => $data[1]['author'],
                'status' => $data[1]['status'],
                'content' => dr_array2string($content),
                'backuid' => (int) $this->uid,
                'backinfo' => $this->admin ? dr_array2string($backinfo) : '',
                'inputtime' => SYS_TIME
            ));
        }

        $this->ci->clear_cache('hits'. APP_DIR.SITE_ID.$_data['id']);
        $this->ci->clear_cache((SITE_MOBILE === TRUE ? 'm' : '').'show'.APP_DIR.SITE_ID.$_data['id']);

        return $_data['id'];
    }


    /**
     * 发布前，先生成一个索引数据
     *
     * @param	array	$data
     * @return	array
     */
    private function extend_index($data) {

        $this->link->insert($this->prefix.'_extend_index', array(
            'cid' => $data[1]['cid'],
            'uid' => $data[1]['uid'],
            'catid' => $data[1]['catid'],
            'status' => $data[1]['status'],
        ));

        return $this->link->insert_id();
    }

    /**
     * 发布
     *
     * @param	array	$data
     * @return	array
     */
    public function add_extend($data) {

        // 发布之前挂钩点
        $this->hooks->call_hook('extend_add_before', $data);

        // 生成索引id
        $data[0]['id'] = $data[1]['id'] = $id = $this->extend_index($data);
        $data[0]['uid'] = $data[1]['uid'];
        $data[0]['cid'] = $data[1]['cid'];
        $data[0]['catid'] = $data[1]['catid'];

        if (!$id) {
            return FALSE;
        }

        // 副表以5w左右数据量无限分表
        $data[1]['tableid'] = floor($id / 50000);
        // 格式化字段值
        $data = $this->get_content_extend_data($data);
        if ($data[1]['status'] == 9) { // 审核通过]
            $data[1]['url'] = dr_extend_url($this->ci->get_cache('module-'.SITE_ID.'-'.APP_DIR), $data[1]);
            if (!$this->link->query("SHOW TABLES LIKE '".$this->prefix.'_extend_data_'.$data[1]['tableid']."'")->row_array()) {
                // 附表不存在时创建附表
                $sql = $this->link->query("SHOW CREATE TABLE `{$this->prefix}_extend_data_0`")->row_array();
                $this->link->query(str_replace(
                    array($sql['Table'], 'CREATE TABLE '),
                    array($this->prefix.'_extend_data_'.$data[1]['tableid'], 'CREATE TABLE IF NOT EXISTS '),
                    $sql['Create Table']
                ));
            }
            $this->link->replace($this->prefix.'_extend', $data[1]); // 主表
            $this->link->replace($this->prefix.'_extend_data_'.$data[1]['tableid'], $data[0]); // 副表
            // 分享信息
            $data[1]['title'] = trim($this->content['title']).' - '.$data[1]['name'];
            $this->sns_share($data[1]); // 分享信息
            // 更新内容表时间
            $this->link
                ->where('id', (int)$data[1]['cid'])
                ->update($this->prefix, array('updatetime' => SYS_TIME));
            $this->link // 审核表
                 ->where('id', (int)$data[1]['id'])
                 ->delete($this->prefix.'_extend_verify');
        } else {
            $content = array_merge($data[0], $data[1]);
            $content['title'] = $this->content['title'];
            // 非审核通过状态的文档写入审核表
            $this->link->replace($this->prefix.'_extend_verify', array(
                'id' => $data[1]['id'],
                'uid' => $data[1]['uid'],
                'cid' => $data[1]['cid'],
                'catid' => $data[1]['catid'],
                'author' => $data[1]['author'],
                'status' => $data[1]['status'],
                'content' => dr_array2string($content),
                'backuid' => 0,
                'backinfo' => '',
                'inputtime' => $data[1]['inputtime']
            )); // 审核表
        }

        $this->ci->clear_cache((SITE_MOBILE === TRUE ? 'm' : '').'extend'.APP_DIR.SITE_ID.$id);

        // 发布之后挂钩点
        $this->hooks->call_hook('extend_add_after', $data);

        return $id;
    }

    // 修改
    public function edit_extend($_data, $data) {

        // 参数判断
        if (!$data || !$_data) {
            return FALSE;
        }

        // 被退回处理
        if (isset($data[1]['back']) && $this->admin) {
            $backinfo = array(
                'uid' => $this->uid,
                'author' => $this->admin['username'],
                'rolename' => $this->admin['role']['name'],
                'optiontime' => SYS_TIME,
                'backcontent' => $data[1]['back']
            );
            unset($data[1]['back']);
        }
        // 格式化字段值
        $data = $this->get_content_extend_data($data, $_data);
        // 生成url地址
        $data[1]['url'] = dr_extend_url($this->ci->get_cache('MODULE-'.SITE_ID.'-'.APP_DIR), array_merge($_data, $data[1]));
        // 更新索引表
        $this->link
             ->where('id', $_data['id'])
             ->update($this->prefix.'_extend_index', array(
                'uid' => $data[1]['uid'],
                'catid' => $data[1]['catid'],
                'status' => $data[1]['status']
             )
        );
        if ($data[1]['status'] == 9) {
            // 提交为审核通过状态
            $data[1]['id'] = $data[0]['id'] = $_data['id'];
            $data[0]['uid'] = $data[1]['uid'];
            $data[0]['cid'] = $data[1]['cid'];
            $data[0]['catid'] = $data[1]['catid'];
            // 副表以5w左右数据量无限分表
            $data[1]['tableid'] = $_data['tableid'] ? $_data['tableid'] : floor($_data['id'] / 50000);
            if (!$this->link->query("SHOW TABLES LIKE '".$this->prefix.'_extend_data_'.$data[1]['tableid']."'")->row_array()) {
                // 附表不存在时创建附表
                $sql = $this->link->query("SHOW CREATE TABLE `{$this->prefix}_extend_data_0`")->row_array();
                $this->link->query(str_replace(
                    array($sql['Table'], 'CREATE TABLE '),
                    array($this->prefix.'_extend_data_'.$data[1]['tableid'], 'CREATE TABLE IF NOT EXISTS '),
                    $sql['Create Table']
                ));
            }
            // 主表更新
            if ($this->link->where('id', $_data['id'])->count_all_results($this->prefix.'_extend')) {
                $this->link->where('id', $_data['id'])->update($this->prefix.'_extend', $data[1]);
            } else {
                $this->link->replace($this->prefix.'_extend', $data[1]);
            }
            $this->link // 副表
                 ->replace($this->prefix.'_extend_data_'.$data[1]['tableid'], $data[0]);

            if ($_data['status'] < 9) {
                $this->link // 审核表
                     ->where('id', $_data['id'])
                     ->delete($this->prefix.'_extend_verify');
            }
            // 更新内容表时间
            $this->link
                 ->where('id', $data[1]['cid'])
                 ->update($this->prefix, array('updatetime' => SYS_TIME));
        } else {
            // 检查合并审核数据
            $content = $data[0] ? array_merge($data[0], $data[1]) : $data[1];
            if (!$content) {
                return FALSE;
            }
            $content['title'] = $this->content['title'];
            // 更新主表
            $this->link
                 ->where('id', (int)$_data['id'])
                 ->update($this->prefix.'_extend', array('status' => $data[1]['status']));
            // 更新审核表
            $this->link->replace($this->prefix.'_extend_verify', array(
                'id' => $_data['id'],
                'uid' => $data[1]['uid'],
                'cid' => $data[1]['cid'],
                'catid' => $data[1]['catid'],
                'author' => $data[1]['author'],
                'status' => $data[1]['status'],
                'content' => dr_array2string($content),
                'backuid' => (int) $this->uid,
                'backinfo' => $this->admin ? dr_array2string($backinfo) : '',
                'inputtime' => SYS_TIME
            ));
        }

        $this->ci->clear_cache((SITE_MOBILE === TRUE ? 'm' : '').'extend'.APP_DIR.SITE_ID.$id);

        return $_data['id'];
    }

    // 筛选出栏目表字段
    private function replace_category_data($id, $data) {

        $catfield = $this->ci->get_cache('MODULE-'.SITE_ID.'-'.APP_DIR, 'category', $data[1]['catid'], 'field');

        if ($catfield) {

            $cdata = array();
            $cdata[0]['id'] = $cdata[1]['id'] = $id;
            $cdata[0]['uid'] = $cdata[1]['uid'] = $data[1]['uid'];
            $cdata[0]['catid'] = $cdata[1]['catid'] = $data[1]['catid'];

            // 主表内容
            foreach ($data[1] as $i => $t) {
                if (strpos($i, '_lng') || strpos($i, '_lat')) {
                    $i = str_replace(array('_lng', '_lat'), '', $i);
                    if (isset($catfield[$i]) && $catfield[$i]['ismain'] == 1
                        && !isset($cdata[1][$i.'_lng'])) {
                        $cdata[1][$i.'_lng'] = $data[1][$i.'_lng'];
                        $cdata[1][$i.'_lat'] = $data[1][$i.'_lat'];
                        unset($data[1][$i.'_lng'], $data[1][$i.'_lat']);
                    }
                } else {
                    if (isset($catfield[$i]) && $catfield[$i]['ismain'] == 1) {
                        $cdata[1][$i] = $t;
                        unset($data[1][$i]);
                    }
                }
            }
            $this->link->replace($this->prefix.'_category_data', $cdata[1]); // 栏目主表
            // 附表内容
            if ($data[0]) {
                foreach ($data[0] as $i => $t) {
                    if (strpos($i, '_lng') || strpos($i, '_lat')) {
                        $i = str_replace(array('_lng', '_lat'), '', $i);
                        if (isset($catfield[$i]) && $catfield[$i]['ismain'] == 0
                            && !isset($cdata[0][$i.'_lng'])) {
                            $cdata[0][$i.'_lng'] = $data[0][$i.'_lng'];
                            $cdata[0][$i.'_lat'] = $data[0][$i.'_lat'];
                            unset($data[0][$i.'_lng'], $data[0][$i.'_lat']);
                        }
                    } else {
                        if (isset($catfield[$i]) && $catfield[$i]['ismain'] == 0) {
                            $cdata[0][$i] = $t;
                            unset($data[0][$i]);
                        }
                    }
                }

                // 副表以5w左右数据量无限分表
                $data[1]['tableid'] = $data[1]['tableid'] ? $data[1]['tableid'] : floor($id / 50000);
                if (!$this->link->query("SHOW TABLES LIKE '".$this->prefix.'_category_data_'.$data[1]['tableid']."'")->row_array()) {
                    // 附表不存在时创建附表
                    $sql = $this->link->query("SHOW CREATE TABLE `{$this->prefix}_category_data_0`")->row_array();
                    $this->link->query(str_replace(
                        array($sql['Table'], 'CREATE TABLE '),
                        array($this->prefix.'_category_data_'.$data[1]['tableid'], 'CREATE TABLE IF NOT EXISTS '),
                        $sql['Create Table']
                    ));
                }
                $this->link->replace($this->prefix.'_category_data_'.$data[1]['tableid'], $cdata[0]); // 副表
            }
        }

        return $data;
    }

    // 获取扩展内容
    public function get_extend($id) {

        $id = (int) $id;
        if (!$id) {
            return NULL;
        }

        $data1 = $this->link // 主表
                      ->where('id', $id)
                      ->limit(1)
                      ->get($this->prefix.'_extend')
                      ->row_array();

        $data2 = $this->link // 副表
                      ->where('id', $id)
                      ->limit(1)
                      ->get($this->prefix.'_extend_data_'.(int)$data1['tableid'])
                      ->row_array();

        return $data1 + $data2;
    }

    // 获取内容
    public function get($id) {

        if (!$id) {
            return NULL;
        }

        $data1 = $this->link // 主表
                      ->where('id', $id)
                      ->limit(1)
                      ->get($this->prefix)
                      ->row_array();
        if (!$data1) {
            return NULL;
        }

        $data2 = $this->link // 副表
                      ->where('id', $id)
                      ->limit(1)
                      ->get($this->prefix.'_data_'.$data1['tableid'])
                      ->row_array();

        $data3 = $this->link // 栏目附加数据
                      ->where('id', $id)
                      ->limit(1)
                      ->get($this->prefix.'_category_data')
                      ->row_array();
        if ($data3) {
            $data4 = $this->link // 栏目附加数据副表
                          ->where('id', $id)
                          ->limit(1)
                          ->get($this->prefix.'_category_data_'.$data1['tableid'])
                          ->row_array();
        }

        // 数据组合
        $data = array();
        $data = $data2 ? $data1 + $data2 : $data1;
        $data = $data3 ? $data + $data3 : $data;
        $data = $data4 ? $data + $data4 : $data;

        return $data;
    }

    // 获取审核信息
    public function get_verify($id) {

        if (!$id) {
            return NULL;
        }

        $data = $this->link // 主表
                     ->where('id', $id)
                     ->limit(1)
                     ->get($this->prefix.'_verify')
                     ->row_array();
        if (!$data) {
            return NULL;
        }

        $content = dr_string2array($data['content']);
        $data['backinfo'] = dr_string2array($data['backinfo']);
        unset($content['status'], $content['catid'], $content['uid']);

        return $content + $data;
    }

    // 获取审核信息
    public function get_extend_verify($id) {

        if (!$id) {
            return NULL;
        }

        // 主表
        $data = $this->link
                     ->where('id', $id)
                     ->limit(1)
                     ->get($this->prefix.'_extend_verify')
                     ->row_array();
        if (!$data) {
            return NULL;
        }

        $content = dr_string2array($data['content']);
        $data['backinfo'] = dr_string2array($data['backinfo']);
        unset($content['status'], $content['catid'], $content['uid']);

        return $content + $data;
    }

    /**
     * 社区分享
     *
     * @param	array	$data	文档数据内容
     * @return  NULL
     */
    public function sns_share($data) {

        $url = $data['url']; // 地址
        $uid = $data['uid'] ? $data['uid'] : $this->uid;
        $title = $data['title'].($data['description'] ? ' '.$data['description'] : ''); // 标题
        $thumb = $data['thumb'] ? dr_thumb($data['thumb']) : ''; // 缩略图

        // 添加到QQ分享任务队列
        if ($this->input->post('qq_share') && $this->member['oauth']['qq']) {
            $this->cron_model->add(2, array(
                'uid' => $uid,
                'url' => $url,
                'thumb' => $thumb,
                'title' => $title,
            ));
        }

        // 添加到新浪分享任务队列
        if ($this->input->post('sina_share') && $this->member['oauth']['sina']) {
            $this->cron_model->add(4, array(
                'uid' => $uid,
                'url' => $url,
                'thumb' => $thumb,
                'title' => $title,
            ));
        }
    }

    // 审核后执行的操作
    public function verify_notice($id, $data) {
        $this->member_model->add_notice($data[1]['uid'], 3, dr_lang('m-084', $data[1]['title']));
    }

    /**
     * 删除静态页面
     *
     * @param	string	$data	文件序列化字符串
     * @return  NULL
     */
    public function delete_html_file($data) {

        if (!$data) {
            return NULL;
        }

        foreach ($data as $t) {
            $filepath = dr_string2array($t['filepath']);
            $this->link->where('id', (int)$t['id'])->delete($this->prefix.'_html');
            if ($filepath) {
                foreach ($filepath as $file) {
                    unlink($file);
                    dr_rmdir(dirname($file));
                }
            }
        }
    }

    /**
     * 删除内容
     *
     * @param	intval	$id			模块内容的id
     * @param	intval	$tableid	模块内容附表id
     * @return  NULL
     */
    public function delete_for_id($id, $tableid) {

        if (!$id) {
            return NULL;
        }

        // 删除缓存
        $this->ci->clear_cache('hits'. APP_DIR.SITE_ID.$id);
        $this->ci->clear_cache((SITE_MOBILE === TRUE ? 'm' : '').'show'.APP_DIR.SITE_ID.$id);

        // 删除表对应的附件
        $this->load->model('attachment_model');
        $this->attachment_model->delete_for_table($this->prefix.'-'.$id);
        $this->attachment_model->delete_for_table($this->prefix.'_verify-'.$id);

        // 删除审核表
        $this->link
             ->where('id', $id)
             ->delete($this->prefix.'_verify');
        // 删除索引表
        $this->link
             ->where('id', $id)
             ->delete($this->prefix.'_index');
        // 删除附表表
        $this->link
             ->where('id', $id)
             ->delete($this->prefix.'_data_'.(int)$tableid);
        // 删除标记表
        $this->link
             ->where('id', $id)
             ->delete($this->prefix.'_flag');
        // 删除主表
        $this->link
             ->where('id', $id)
             ->delete($this->prefix);

        if ($this->is_category) {
            // 删除栏目附加表
            $this->link
                 ->where('id', $id)
                 ->delete($this->prefix.'_category_data');
            // 删除栏目附加表
            $this->link
                 ->where('id', $id)
                 ->delete($this->prefix.'_category_data_'.(int)$tableid);
        }
        // 删除收藏表
        for ($i = 0; $i < 10; $i++) {
            $this->link
                 ->where('id', $id)
                 ->delete($this->prefix.'_favorite_'.$i);
        }
        // 删除应用的相关表
        $app = $this->ci->get_cache('app');
        if ($app) {
            foreach ($app as $dir) {
                if (is_file(FCPATH.'app/'.$dir.'/models/'.$dir.'_model.php')) {
                    $this->load->add_package_path(FCPATH.'app/'.$dir.'/');
                    $this->load->model($dir.'_model', 'app_model');
                    $this->app_model->delete_for_cid($id, APP_DIR);
                }
            }
        }
        // 删除文件
        if (MODULE_HTML) {
            $this->delete_html_file($this->link
                                         ->select('filepath,id')
                                         ->where('rid', $id)
                                         ->where('type', 1)
                                         ->get($this->prefix.'_html')
                                         ->result_array());
            $this->link
                 ->where('rid', $id)
                 ->where('type', 1)
                 ->delete($this->prefix.'_html');
        }
        // 删除扩展内容
        if ($this->ci->get_cache('module-'.SITE_ID.'-'.APP_DIR, 'extend')) {
            $data = $this->link
                         ->select('tableid,id')
                         ->where('cid', $id)
                         ->get($this->prefix.'_extend')
                         ->result_array();
            if ($data) {
                // 逐一删除内容
                foreach ($data as $t) {
                    $this->delete_extend_for_id($t['id'], $id, $t['tableid']);
                }
            }
        }
        $this->link->db_debug = FALSE;
        // 删除表单数据
        $form = $this->link
                     ->where('disabled', 0)
                     ->order_by('id ASC')
                     ->get(SITE_ID.'_'.APP_DIR.'_form')
                     ->result_array();
        if ($form) {
            foreach ($form as $f) {
                // 删除表对应的附件
                $table = SITE_ID.'_'.APP_DIR.'_form_'.$f['id'];
                $data = $this->link
                             ->where('cid', $id)
                             ->get($table)
                             ->result_array();
                if ($data) {
                    foreach ($data as $t) {
                        $this->link->where('id', $t['id'])->delete($table);
                        $this->attachment_model->delete_for_table($table.'-'.$t['id']);
                    }
                }
            }
        }
    }

    /**
     * 删除扩展内容
     *
     * @param	array	$ids	id数组
     * @return  NULL
     */
    public function delete_extend_for_ids($ids) {

        if (!$ids) {
            return NULL;
        }

        $data = $this->link
                     ->select('tableid,id')
                     ->where_in('id', $ids)
                     ->get($this->prefix.'_extend')
                     ->result_array();
        if (!$data) {
            return NULL;
        }

        // 逐一删除内容
        foreach ($data as $t) {
            $this->delete_extend_for_id($t['id'], $t['cid'], $t['tableid']);
        }
    }

    /**
     * 删除扩展内容
     *
     * @param	array	$ids	id数组
     * @return  NULL
     */
    public function delete_extend_for_id($id, $cid, $tableid) {

        if (!$id) {
            return NULL;
        }

        // 删除缓存
        $this->ci->clear_cache((SITE_MOBILE === TRUE ? 'm' : '').'extend'.APP_DIR.SITE_ID.$id);

        // 删除表对应的附件
        $this->load->model('attachment_model');
        $this->attachment_model->delete_for_table($this->content_model->prefix.'-'.$cid.'-'.$id);
        $this->attachment_model->delete_for_table($this->content_model->prefix.'_verify-'.$cid.'-'.$id);

        // 删除索引表
        $this->link
             ->where('id', $id)
             ->delete($this->prefix.'_extend_index');
        $this->link
             ->where('id', $id)
             ->delete($this->prefix.'_extend');
        $this->link
             ->where('id', $id)
             ->delete($this->prefix.'_extend_verify');
        // 删除附表
        $this->link
             ->where('id', $id)
             ->delete($this->prefix.'_extend_data_'.(int)$tableid);
        // 删除文件
        if (MODULE_HTML) {
            $this->delete_html_file($this->link
                                         ->select('filepath,id')
                                         ->where('rid', $id)
                                         ->where('type', 2)
                                         ->get($this->prefix.'_html')
                                         ->result_array());
            $this->link
                 ->where('rid', $id)
                 ->where('type', 2)
                 ->delete($this->prefix.'_html');
        }
    }

    // 删除审核
    public function del_extend_verify($id) {

        if (!$id) {
            return NULL;
        }

        // 删除审核表
        $this->link
             ->where('id', $id)
             ->delete($this->prefix.'_extend_verify');

        // 当主表无数据时才删除索引表
        if (!$this->link->where('id', $id)->count_all_results($this->prefix.'_extend')) {
            $this->link
                 ->where('id', $id)
                 ->delete($this->prefix.'_extend_index');
            return TRUE;
        } else {
            // 主表有数据时 恢复为通过状态
            $this->link
                 ->where('id', $id)
                 ->update($this->prefix.'_extend_index', array('status' => 9));
            $this->link
                 ->where('id', $id)
                 ->update($this->prefix.'_extend', array('status' => 9));
            return FALSE;
        }
    }

    // 删除审核
    public function del_verify($id) {

        if (!$id) {
            return NULL;
        }

        // 删除审核表
        $this->link
             ->where('id', $id)
             ->delete($this->prefix.'_verify');

        // 当主表无数据时才删除索引表
        if (!$this->link->where('id', $id)->count_all_results($this->prefix)) {
            $this->link
                 ->where('id', $id)
                 ->delete($this->prefix.'_index');
            return TRUE;
        } else {
            // 主表有数据时 恢复为通过状态
            $this->link
                 ->where('id', $id)
                 ->update($this->prefix.'_index', array('status' => 9));
            $this->link
                 ->where('id', $id)
                 ->update($this->prefix, array('status' => 9));
            return FALSE;
        }
    }

    // 文档标记
    public function flag($ids, $flag) {

        if (!$ids || !$flag) {
            return NULL;
        }

        $data = $this->link
                     ->where_in('id', $ids)
                     ->select('catid,id')
                     ->get($this->prefix)
                     ->result_array();
        if (!$data) {
            return NULL;
        }

        $i = 0;

        foreach ($data as $t) {
            if ($flag > 0) {
                // 增加推荐位
                if (!$this->link
                          ->where('id', $t['id'])
                          ->where('flag', $flag)
                          ->count_all_results($this->prefix.'_flag')) {
                    $this->link->replace($this->prefix.'_flag', array(
                        'id' => $t['id'],
                        'uid' => $this->uid,
                        'flag' => $flag,
                        'catid' => $t['catid']
                    ));
                    $i ++;
                }
            } elseif ($flag < 0) {
                // 取消推荐位
                $this->link
                     ->where('id', $t['id'])
                     ->where('flag', abs($flag))
                     ->delete($this->prefix.'_flag');

                $i ++;
            }
        }

        return $i;
    }

    // 推荐位统计
    public function flag_total($id, $catid = NULL, $uid = NULL) {

        if ($this->where) {
            $this->link->where($this->where);
        }
        if ($uid) {
            $this->link->where('uid', $uid);
        }
        if ($catid) {
            $this->link->where('catid', $catid);
        }
        $this->link->where('flag', $id);

        return $this->link->count_all_results($this->prefix.'_flag');
    }

    // 更新文档时间
    public function updatetime($id) {
        $this->db
             ->where('uid', $this->uid)
             ->where_in('id', $id)
             ->update($this->prefix, array('updatetime' => SYS_TIME));
    }

    // 移动栏目
    public function move($id, $catid) {

        if (!$id || !$catid) {
            return FALSE;
        }

        $this->link
             ->where_in('id', $id)
             ->update($this->prefix, array('catid' => $catid));
        $this->link
             ->where_in('id', $id)
             ->update($this->prefix.'_index', array('catid' => $catid));

        if ($this->ci->get_cache('module-'.SITE_ID.'-'.APP_DIR, 'extend')) {
            $this->link
                 ->where_in('cid', $id)
                 ->update($this->prefix.'_extend', array('catid' => $catid));
            $this->link
                 ->where_in('cid', $id)
                 ->update($this->prefix.'_extend_index', array('catid' => $catid));
        }

        return TRUE;
    }

    // 移动扩展
    public function extend_move($ids, $type) {

        if (!$ids) {
            return FALSE;
        }

        $this->link
             ->where_in('id', $ids)
             ->update($this->prefix.'_extend', array('mytype' => $type));

        return TRUE;
    }

    // 更新至tag表
    public function update_tag($keyword) {
        $array = explode(',', str_replace('，', ',', $keyword));
        foreach ($array as $name) {
            $name = trim($name);
            if (strlen($name) > 2
                && strlen($name) < 30
                && !$this->link->where('name', $name)->count_all_results($this->prefix.'_tag')) {
                $this->link->replace($this->prefix.'_tag', array(
                    'name' => $name,
                    'code' => dr_word2pinyin($name),
                    'hits' => 0
                ));
            }
        }
    }

    // 获取内容（用于商品订单），模块可重写
    public function get_item_data($id) {
        return NULL;
    }

    // 格式化字段值，模块可重写
    protected function get_content_data($data, $_data = NULL) {
        if (!$data[1]['description']) {
            $data[1]['description'] = trim(dr_strcut(dr_clearhtml($data[0]['content']), 200));
        }
        return $data;
    }

    // 格式化字段值，模块可重写
    protected function get_content_extend_data($data, $_data = NULL) {
        return $data;
    }

    // 保存html文件记录
    public function set_html($type, $uid, $cid, $rid, $catid, $filepath) {

        $table = $this->prefix.'_html';
        if ($type != 3
            && $this->link->where('rid', $rid)->where('type', $type)->count_all_results($table)) {
            $this->link
                 ->where('rid', $rid)
                 ->where('type', $type)
                 ->update($table, array(
                    'cid' => $cid,
                    'uid' => $uid,
                    'type' => $type,
                    'catid' => $catid,
                    'filepath' => dr_array2string($filepath)
                 )
            );
        } else {
            $this->link->insert($table, array(
                'rid' => $rid,
                'cid' => $cid,
                'uid' => $uid,
                'type' => $type,
                'catid' => $catid,
                'filepath' => dr_array2string($filepath),
            ));
        }
    }

}
