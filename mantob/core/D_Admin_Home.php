<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Omweb Website Management System
 *
 * @since	    version 2.3.5
 * @author	    mantob <kefu@mantob.com>
 * @license     http://www.mantob.com/license
 * @copyright   Copyright (c) 2013 - 9999, mantob.Com, Inc.
 */
class D_Admin_Home extends M_Controller {

    protected $field; // 自定义字段+含系统字段
    protected $verify; // 审核流程
    protected $sysfield; // 系统字段

    /**
     * 构造函数
     */

    public function __construct() {
        parent::__construct();
        $this->load->library('Dfield', array(APP_DIR));
        $this->sysfield = array(
            'hits' => array(
                'name' => lang('244'),
                'ismain' => 1,
                'fieldname' => 'hits',
                'fieldtype' => 'Text',
                'setting' => array(
                    'option' => array(
                        'value' => 0,
                        'width' => 157,
                    )
                )
            ),
            'author' => array(
                'name' => lang('101'),
                'ismain' => 1,
                'fieldtype' => 'Text',
                'fieldname' => 'author',
                'setting' => array(
                    'option' => array(
                        'width' => 157,
                        'value' => $this->admin['username']
                    ),
                    'validate' => array(
                        'tips' => lang('102'),
                        'check' => '_check_member',
                        'required' => 1,
                        'formattr' => ' /><input type="button" class="button" value="'.lang('103').'" onclick="dr_dialog_member(\'author\')" name="user"',
                    )
                )
            ),
            'inputtime' => array(
                'name' => lang('104'),
                'ismain' => 1,
                'fieldtype' => 'Date',
                'fieldname' => 'inputtime',
                'setting' => array(
                    'option' => array(
                        'width' => 140
                    ),
                    'validate' => array(
                        'required' => 1,
                        'formattr' => '',
                    )
                )
            ),
            'updatetime' => array(
                'name' => lang('105'),
                'ismain' => 1,
                'fieldtype' => 'Date',
                'fieldname' => 'updatetime',
                'setting' => array(
                    'option' => array(
                        'width' => 140
                    ),
                    'validate' => array(
                        'required' => 1,
                        'formattr' => '',
                    )
                )
            ),
            'inputip' => array(
                'name' => lang('106'),
                'ismain' => 1,
                'fieldtype' => 'Text',
                'fieldname' => 'inputip',
                'setting' => array(
                    'option' => array(
                        'width' => 157,
                        'value' => $this->input->ip_address()
                    ),
                    'validate' => array(
                        'formattr' => ' /><input type="button" class="button" value="'.lang('107').'" onclick="dr_dialog_ip(\'inputip\')" name="ip"',
                    )
                )
            )
        );
        $field = $this->get_cache('module-'.SITE_ID.'-'.APP_DIR, 'field');
        $this->field = $field ? array_merge($field, $this->sysfield) : $this->sysfield;
        if ($this->admin['adminid'] > 1) {
            $this->verify = $this->_get_verify();
        }
    }

    /**
     * 管理
     */
    public function index() {
        if (IS_POST && !$this->input->post('search')) {
            $ids = $this->input->post('ids', TRUE);
            if (!$ids) {
                exit(dr_json(0, lang('013')));
            }
            switch ($this->input->post('action')) {
                case 'del':
                    $ok = $no = 0;
                    foreach ($ids as $id) {
                        $data = $this->link
                                     ->where('id', (int) $id)
                                     ->select('id,catid,tableid')
                                     ->limit(1)
                                     ->get($this->content_model->prefix)
                                     ->row_array();
                        if ($data) {
                            if (!$this->is_category_auth($data['catid'], 'del')) {
                                $no++;
                            } else {
                                $ok++;
                                $this->content_model->delete_for_id((int)$data['id'], (int)$data['tableid']);
                            }
                        }
                    }
                    exit(dr_json($no ? 0 : 1, $no ? dr_lang('033', $ok, $no) : lang('000')));
                    break;
                case 'order':
                    $_data = $this->input->post('data');
                    foreach ($ids as $id) {
                        $this->link
                             ->where('id', $id)
                             ->update($this->content_model->prefix, $_data[$id]);
                    }
                    exit(dr_json(1, lang('000')));
                    break;
                case 'move':
                    $catid = $this->input->post('catid');
                    if (!$catid) {
                        exit(dr_json(0, lang('cat-20')));
                    }
                    if (!$this->is_auth(APP_DIR.'/admin/home/edit')
                        || !$this->is_category_auth($catid, 'edit')) {
                        exit(dr_json(0, lang('160')));
                    }
                    $this->content_model->move($ids, $catid);
                    exit(dr_json(1, lang('000')));
                    break;
                case 'flag':
                    if (!$this->is_auth(APP_DIR.'/admin/home/edit')) {
                        exit(dr_json(0, lang('160')));
                    }
                    $flag = $this->input->post('flagid');
                    $this->content_model->flag($ids, $flag);
                    exit(dr_json(1, lang('000')));
                    break;
                default :
                    exit(dr_json(0, lang('000')));
                    break;
            }
        }
        // 重置页数和统计
        if (IS_POST) {
            $_GET['page'] = $_GET['total'] = 0;
        }
        // 筛选结果
        $param = array();
        if ($this->input->get('flag')) {
            $param['flag'] = (int) $this->input->get('flag');
        }
        if ($this->input->get('catid')) {
            $catid = $param['catid'] = (int) $this->input->get('catid');
        }
        if ($this->input->get('search')) {
            $param['search'] = 1;
        }
        // 数据库中分页查询
        list($list, $param) = $this->content_model->limit_page($param, max((int)$_GET['page'], 1), (int)$_GET['total']);
        // 统计推荐位数量
        $_menu[lang('mod-01')] = $catid ? APP_DIR.'/admin/home/index/catid/'.$catid : APP_DIR.'/admin/home/index';
        $flag = $this->get_cache('module-'.SITE_ID.'-'.APP_DIR, 'setting', 'flag');
        if ($flag) {
            foreach ($flag as $id => $t) {
                if ($t['name'] && $id) {
                    $_menu["{$t['name']}(".$this->content_model->flag_total($id, $catid).")"] = $catid ?
                            APP_DIR.'/admin/home/index/flag/'.$id.'/catid/'.$catid :
                            APP_DIR.'/admin/home/index/flag/'.$id;
                }
            }
        }
        // 模块应用嵌入
        $app = array();
        $data = $this->get_cache('app');
        if ($data) {
            foreach ($data as $dir) {
                $a = $this->get_cache('app-' . $dir);
                if (isset($a['module'][APP_DIR]) && isset($a['related']) && $a['related']) {
                    $app[] = array(
                        'url' => dr_url($dir.'/content/index'),
                        'name' => $a['name'],
                        'field' => $a['related'],
                    );
                }
            }
        }
        // 模块表单嵌入
        $form = array();
        $data = $this->get_cache('module-'.SITE_ID.'-'.APP_DIR, 'form');
        if ($data) {
            foreach ($data as $t) {
                $form[] = array(
                    'url' => dr_url(APP_DIR.'/form_'.SITE_ID.'_'.$t['id'].'/index'),
                    'name' => $t['name'],
                );
            }
        }
        // 搜索参数
        if ($this->input->get('search')) {
            $_param = $this->cache->file->get($this->content_model->cache_file);
        } else {
            $_param = $this->input->post('data');
        }
        $_menu["<font color=red><b>".lang('mod-02')."</b></font>"] = $catid ? APP_DIR.'/admin/home/add/catid/'.$catid : APP_DIR . '/admin/home/add';
        isset($_param['catid']) && $catid = $param['catid'] = $_param['catid'];
        $_param = $_param ? $param + $_param : $param;
        // 栏目搜索
        $category = $this->get_cache('module-'.SITE_ID.'-'.APP_DIR, 'category');
        $this->template->assign(array(
            'app' => $app,
            'form' => $form,
            'list' => $list,
            'menu' => $this->get_menu($_menu),
            'flag' => isset($param['flag']) ? $param['flag'] : '',
            'flags' => $flag,
            'param' => $_param,
            'field' => $this->field,
            'pages' => $this->get_pagination(dr_url(APP_DIR.'/home/index', $param), $param['total']),
            'extend' => $this->get_cache('module-'.SITE_ID.'-'.APP_DIR, 'extend'),
            'select' => $this->select_category($category, 0, 'id=\'move_id\' name=\'catid\'', ' --- ', 1, 1),
            'select2' => $this->select_category($category, $catid, 'name=\'data[catid]\'', ' --- ', 0, 1),
            'is_category' => $this->is_category
        ));
        $this->template->display('content_index.html');
    }

    /**
     * 审核
     */
    public function verify() {

        if ($this->admin['adminid'] > 1 && !$this->verify) {
            $this->admin_msg(lang('337'));
        }

        if (IS_POST && $this->input->post('action') != 'search') {
            $ids = $this->input->post('ids', TRUE);
            if (!$ids) {
                exit(dr_json(0, lang('013')));
            }
            if ($this->admin['adminid'] > 1) {
                // 非管理员角色只能操作自己审核的
                $status = array();
                foreach ($this->verify as $t) {
                    $status+=$t['status'];
                }
                $where = '`status` IN ('.implode(',', $status).')';
            } else {
                $where = '';
            }
            switch ($this->input->post('action')) {
                case 'del': // 删除
                    $this->load->model('attachment_model');
                    foreach ($ids as $id) {
                        $data = $this->link // 主表状态
                                     ->where($where ? $where.' AND `id`='.(int)$id : '`id`='.(int)$id)
                                     ->select('uid,catid')
                                     ->limit(1)
                                     ->get($this->content_model->prefix.'_index')
                                     ->row_array();
                        if ($data) {
                            // 删除数据
                            $this->content_model->del_verify($id);
                            // 删除表对应的附件
                            $this->attachment_model->delete_for_table($this->content_model->prefix.'_verify-'.$id);
                        }
                    }
                    exit(dr_json(1, lang('000')));
                    break;
                case 'flag': // 标记
                    $js = $error = array();
                    if (!$this->input->post('flagid')) {
                        exit(dr_json(0, lang('013')));
                    }
                    foreach ($ids as $id) {
                        $result = $this->_verify($id, NULL, $where ? $where.' AND `id`='.(int)$id : '`id`='.(int)$id);
                        if (is_array($result)) {
                            if (MODULE_HTML) {
                                $js[] = dr_module_create_show_file($result['id'], 1);
                                $js[] = dr_module_create_list_file($result['catid'], 1);
                            }
                        } elseif ($result) {
                            $error[] = str_replace('<br>', '', $result);
                        }
                    }
                    if ($error) {
                        exit(dr_json(1, $error, $js));
                    } else {
                        exit(dr_json(2, lang('000'), $js));
                    }
                    break;
                default:
                    exit(dr_json(0, lang('047')));
                    break;
            }
        }
        $param = array();
        $param['status'] = (int) $this->input->get('status');
        if ($this->admin['adminid'] == 1) {
            // 管理员角色列出所有审核流程
            $where = '`status`='.$param['status'];
            for ($i = 0; $i < 9; $i++) {
                $total = (int) $this->db->where('status', $i)->count_all_results($this->content_model->prefix.'_verify');
                $_menu[lang('05'.$i).' ('.$total.')'] = APP_DIR.'/admin/home/verify'.(isset($_GET['status']) || $i ? '/status/'.$i : '');
            }
        } else {
            // 非管理员角色列出自己审核的
            $status = array();
            foreach ($this->verify as $t) {
                $status+=$t['status'];
            }
            if ($param['status']) {
                $where = '`status` IN ('.implode(',', $status).')';
            } else {
                $where = '`status`=0 AND `backuid`='.$this->uid;
            }
        }
        // 栏目筛选
        if ($this->input->get('catid')) {
            $param['catid'] = (int) $this->input->get('catid');
            $where.= ' AND `catid` = '.$param['catid'];
        }
        // 获取总数量
        $param['total'] = $total = $this->input->get('total') ? $this->input->get('total') : $this->link->where($where)->count_all_results($this->content_model->prefix.'_verify');
        $page = max(1, (int) $this->input->get('page'));
        $data = $this->link
                     ->select('id,catid,author,content,inputtime,status')
                     ->where($where)
                     ->limit(SITE_ADMIN_PAGESIZE, SITE_ADMIN_PAGESIZE * ($page - 1))
                     ->order_by('inputtime DESC, id DESC')
                     ->get($this->content_model->prefix . '_verify')
                     ->result_array();

        if ($this->admin['adminid'] > 1) {
            // 被退回
            $_total = $this->link
                           ->where('`status`=0 AND `backuid`='.$this->uid)
                           ->count_all_results($this->content_model->prefix.'_verify');
            $_menu[lang('050').' ('.$_total.')'] = APP_DIR.'/admin/home/verify';
            // 我的审核
            $_total = $this->link
                           ->where_in('status', $status)
                           ->count_all_results($this->content_model->prefix.'_verify');
            $_menu[lang('120').' ('.$_total.')'] = APP_DIR.'/admin/home/verify/status/1';
        }

        $this->template->assign(array(
            'list' => $data,
            'menu' => $this->get_menu($_menu),
            'param' => $param,
            'pages' => $this->get_pagination(dr_url(APP_DIR.'/home/verify', $param), $param['total'])
        ));
        $this->template->display('content_verify.html');
    }

    /**
     * 添加
     */
    public function add() {

        $error = $data = array();
        $catid = (int) $this->input->get('catid');
        $result = '';
        if ($catid && !$this->is_category_auth($catid, 'add') && $this->is_category) {
            $this->admin_msg(lang('160'));
        }

        if (IS_POST) {
            $catid = (int) $this->input->post('catid');
            if (!$this->is_category_auth($catid, 'add') && $this->is_category) {
                $this->admin_msg(lang('160'));
            }
            $cate = $this->is_category ? $this->get_cache('module-'.SITE_ID.'-'.APP_DIR, 'category', $catid, 'field') : NULL;
            $field = $cate ? array_merge($this->field, $cate) : $this->field;

            // 设置uid便于校验处理
            $uid = $this->input->post('data[author]') ? get_member_id($this->input->post('data[author]')) : 0;
            $_POST['data']['id'] = $id;
            $_POST['data']['uid'] = $uid;
            $data = $this->validate_filter($field);
            $backurl = $this->input->post('backurl');

            if (isset($data['error'])) {
                $error = $data;
                $data = $this->input->post('data', TRUE);
            } elseif (!$catid && $this->is_category) {
                $data = $this->input->post('data', TRUE);
                $error = array('error' => 'catid', 'msg' => lang('cat-22'));
            } else {
                $data[1]['uid'] = $uid;
                $data[1]['catid'] = $this->is_category ? $catid : 0;
                $data[1]['status'] = 9;
                if (($id = $this->content_model->add($data)) != FALSE) {
                    $mark = $this->content_model->prefix.'-'.$id;
                    if ($this->is_category) {
                        $member = $this->member_model->get_base_member($uid);
                        $category = $this->get_cache('module-'.SITE_ID.'-'.APP_DIR, 'category', $catid);
                        $rule = $category['permission'][$member['markrule']];
                        // 积分处理
                        if ($rule['experience'] + $member['experience'] >= 0) {
                            $this->member_model->update_score(0, $uid, $rule['experience'], $mark, "lang,m-151,{$category['name']}", 1);
                        }
                        // 虚拟币处理
                        if ($rule['score'] + $member['score'] >= 0) {
                            $this->member_model->update_score(1, $uid, $rule['score'], $mark, "lang,m-151,{$category['name']}", 1);
                        }
                    }
                    // 操作成功处理附件
                    $this->attachment_handle($data[1]['uid'], $mark, $field);
                    // 处理推荐位
                    $update = $this->input->post('flag');
                    if ($update) {
                        foreach ($update as $i) {
                            $this->link->insert(SITE_ID.'_'.APP_DIR.'_flag', array(
                                'id' => $id,
                                'uid' => $uid,
                                'flag' => $i,
                                'catid' => $catid
                            ));
                        }
                    }
                    // 创建静态页面链接
                    $create = MODULE_HTML ? dr_module_create_show_file($id, 1) : '';
                    if ($this->input->post('action') == 'back') {
                        $this->admin_msg(
                            lang('000').
                            ($create ? "<script src='".$create."'></script>".dr_module_create_list_file($catid) : ''),
                            $backurl,
                            1,
                            1
                        );
                    } else {
                        unset($data);
                        $result = lang('000');
                    }
                }
            }
        }

        $this->template->assign(array(
            'data' => $data,
            'page' => max((int) $this->input->post('page'), 0),
            'flag' => $this->get_cache('module-'.SITE_ID.'-'.APP_DIR, 'setting', 'flag'),
            'menu' => $this->get_menu(array(
                lang('back') => $backurl ? $backurl : ($_SERVER['HTTP_REFERER'] && strpos($_SERVER['HTTP_REFERER'], '?') ? $_SERVER['HTTP_REFERER'] : APP_DIR.'/admin/home/index/catid/'.$catid),
                lang('mod-02') => APP_DIR.'/admin/home/add'
            )),
            'catid' => $catid,
            'error' => $error,
            'result' => $result,
            'create' => $create,
            'myflag' => $this->input->post('flag'),
            'select' => $this->is_category ? $this->select_category($this->get_cache('module-'.SITE_ID.'-'.APP_DIR, 'category'), $catid, 'id=\'dr_catid\' name=\'catid\' onChange="show_category_field(this.value)"', '', 1, 1) : NULL,
            'backurl' => $backurl ? $backurl : $_SERVER['HTTP_REFERER'],
            'myfield' => $this->field_input($this->field, $data, TRUE),
            'is_category' => $this->is_category
        ));
        $this->template->display('content_add.html');
    }

    /**
     * 修改
     */
    public function edit() {

        $id = (int) $this->input->get('id');
        $data = $this->content_model->get($id);
        $catid = $data['catid'];
        $error = $myflag = array();
        $result = '';

        if (!$data) {
            $this->admin_msg(lang('019'));
        }
        if (!$this->is_category_auth($catid, 'edit') && $this->is_category) {
            $this->admin_msg(lang('160'));
        }

        $flag = $this->link
                     ->where('id', $id)
                     ->get(SITE_ID.'_'.APP_DIR.'_flag')
                     ->result_array();
        if ($flag) {
            foreach ($flag as $t) {
                $myflag[] = $t['flag'];
            }
        }
        unset($flag);

        if (IS_POST) {
            $_data = $data;
            $catid = (int) $this->input->post('catid');
            if (!$this->is_category_auth($catid, 'edit')) {
                $this->admin_msg(lang('160'));
            }
            $cate = $this->is_category ? $this->get_cache('module-'.SITE_ID.'-'.APP_DIR, 'category', $catid, 'field') : NULL;
            $field = $cate ? array_merge($this->field, $cate) : $this->field;
            // 设置uid便于校验处理
            $uid = $this->input->post('data[author]') ? get_member_id($this->input->post('data[author]')) : 0;
            $_POST['data']['id'] = $id;
            $_POST['data']['uid'] = $uid;
            $data = $this->validate_filter($field, $_data);
            $backurl = $this->input->post('backurl');
            if (isset($data['error'])) {
                $error = $data;
            } elseif (!$catid && $this->is_category) {
                $error = array('error' => 'catid', 'msg' => lang('cat-22'));
            } else {
                $data[1]['uid'] = $uid;
                $data[1]['catid'] = $this->is_category ? $catid : 0;
                $data[1]['status'] = 9;
                $data[1]['updatetime'] = $this->input->post('no_time') ? $_data['updatetime'] : $data[1]['updatetime'];
                $this->content_model->edit($_data, $data);
                // 操作成功处理附件
                $this->attachment_handle($data[1]['uid'], $this->content_model->prefix.'-'.$id, $field, $_data);
                // 处理推荐位
                $update = $this->input->post('flag');
                if ($update !== $myflag) {
                    // 删除旧的
                    if ($myflag) {
                        $this->link
                                ->where('id', $id)
                                ->where_in('flag', $myflag)
                                ->delete(SITE_ID.'_'.APP_DIR.'_flag');
                    }
                    // 增加新的
                    if ($update) {
                        foreach ($update as $i) {
                            $this->link->insert(SITE_ID.'_'.APP_DIR.'_flag', array(
                                'id' => $id,
                                'uid' => $uid,
                                'flag' => $i,
                                'catid' => $catid
                            ));
                        }
                    }
                }
                //exit;
                $this->admin_msg(
                    lang('000') .
                    (MODULE_HTML ? dr_module_create_show_file($id).dr_module_create_list_file($catid) : ''),
                    $backurl,
                    1,
                    1
                );
            }
            $data = $this->input->post('data');
            $myflag = $this->input->post('flag');
        }

        $data['updatetime'] = SYS_TIME;
        $this->template->assign(array(
            'data' => $data,
            'page' => max((int) $this->input->post('page'), 0),
            'flag' => $this->get_cache('module-'.SITE_ID.'-'.APP_DIR, 'setting', 'flag'),
            'menu' => $this->get_menu(array(
                lang('back') => $backurl ? $backurl : ($_SERVER['HTTP_REFERER'] && strpos($_SERVER['HTTP_REFERER'], '?') ? $_SERVER['HTTP_REFERER'] : APP_DIR.'/admin/home/index/catid/'.$catid),
                lang('mod-02') => APP_DIR.'/admin/home/add/catid/'.$catid
            )),
            'catid' => $catid,
            'error' => $error,
            'myflag' => $myflag,
            'result' => $result,
            'select' => $this->is_category ? $this->select_category($this->get_cache('module-'.SITE_ID.'-'.APP_DIR, 'category'), $data['catid'], 'id=\'dr_catid\' name=\'catid\' onChange="show_category_field(this.value)"', '', 1, 1) : $this->is_category,
            'backurl' => $backurl ? $backurl : $_SERVER['HTTP_REFERER'],
            'myfield' => $this->field_input($this->field, $data, TRUE),
            'is_category' => $this->is_category
        ));
        $this->template->display('content_add.html');
    }

    /**
     * 修改审核文档
     */
    public function verifyedit() {

        $id = (int) $this->input->get('id');
        $data = $this->content_model->get_verify($id);
        $catid = $data['catid'];
        $error = array();
        if (!$data) {
            $this->admin_msg(lang('019'));
        }

        if (IS_POST) {
            $_data = $data;
            $catid = (int)$this->input->post('catid');
            $cate = $this->get_cache('module-'.SITE_ID.'-'.APP_DIR, 'category', $catid, 'field');
            $field = $cate ? array_merge($this->field, $cate) : $this->field;
            // 设置uid便于校验处理
            $uid = $this->input->post('data[author]') ? get_member_id($this->input->post('data[author]')) : 0;
            $_POST['data']['id'] = $id;
            $_POST['data']['uid'] = $uid;
            $data = $this->validate_filter($field, $_data);
            if (isset($data['error'])) {
                $error = $data;
                $data = $this->input->post('data', TRUE);
            } elseif (!$catid) {
                $data = $this->input->post('data', TRUE);
                $error = array('error' => 'catid', 'msg' => lang('cat-22'));
            } elseif (!$this->input->post('flagid')) {
                $data = $this->input->post('data', TRUE);
                $error = array('error' => 'flagid', 'msg' => lang('161'));
            } else {
                $data[1]['uid'] = $uid;
                $data[1]['catid'] = $catid;
                $result = $this->_verify($id, $data, '`id`='.$id);
                if (is_array($result)) {
                    $this->admin_msg(
                        lang('000').
                        (MODULE_HTML ? dr_module_create_show_file($id).dr_module_create_list_file($catid) : ''),
                        $this->input->post('backurl'),
                        1,
                        1
                    );
                } elseif ($result) {
                    $this->admin_msg($result);
                }
                $this->admin_msg(lang('000'), $this->input->post('backurl'), 1);
            }
        }

        if ($data['status'] == 0) { // 退回
            $backuri = APP_DIR.'/admin/home/verify/status/0';
        } elseif ($data['status'] > 0 && $data['status'] < 9) {
            $backuri = APP_DIR.'/admin/home/verify/status/'.$data['status'];
        } else {
            $backuri = APP_DIR.'/admin/home/verify/';
        }

        $this->template->assign(array(
            'data' => $data,
            'page' => max((int) $this->input->post('page'), 0),
            'menu' => $this->get_menu(array(
                lang('back') => $backuri,
                lang('edit') => APP_DIR.'/admin/home/verifyedit/id/'.$data['id']
            )),
            'catid' => $catid,
            'error' => $error,
            'select' => $this->select_category($this->get_cache('module-'.SITE_ID.'-'.APP_DIR, 'category'), $data['catid'], 'id=\'dr_catid\' name=\'catid\' onChange="show_category_field(this.value)"', '', 1),
            'backurl' => $_SERVER['HTTP_REFERER'],
            'myfield' => $this->field_input($this->field, $data, TRUE),
        ));
        $this->template->display('content_edit.html');
    }

    // 审核内容
    public function _verify($id, $data, $_where) {
        // 获得审核数据
        $verify = $this->content_model->get_verify($id);
        if (!$verify) {
            return;
        }
        // 通过审核
        if ($this->input->post('flagid') > 0) {
            // 查询当前的审核状态id
            $status = $this->_get_verify_status($verify['uid'], $verify['catid'], $verify['status']);
            // 权限验证
            if ($status == 9) {
                $member = $this->member_model->get_base_member($verify['uid']);
                $category = $this->get_cache('module-'.SITE_ID.'-'.APP_DIR, 'category', $verify['catid']);
                // 标示
                $rule = $category['permission'][$member['markrule']];
                $mark = $this->content_model->prefix.'-'.$id;
                // 积分处理
                if ($rule['experience']) {
                    $this->member_model->update_score(0, $verify['uid'], $rule['experience'], $mark, "lang,m-151,{$category['name']}", 1);
                }
                // 虚拟币处理
                if ($rule['score']) {
                    // 虚拟币判断重复
                    if (!$this->db
                              ->where('type', 1)
                              ->where('mark', $mark)
                              ->count_all_results('member_scorelog_'.(int)substr((string)$verify['uid'], -1, 1))) {
                        if ($rule['score'] + $member['score'] < 0) {
                            // 数量不足提示
                            return dr_lang('m-118', $verify['title'],  $member['username'], SITE_SCORE, abs($rule['score']));
                        }
                        $this->member_model->update_score(1, $verify['uid'], $rule['score'], $mark, "lang,m-151,{$category['name']}", 1);
                    }
                }
            }
            // 筛选字段
            if (!$data) {
                $data = array();
                foreach ($this->field as $field) {
                    if ($field['fieldtype'] == 'Group') {
                        continue;
                    }
                    if ($field['fieldtype'] == 'Baidumap') {
                        $data[$field['ismain']][$field['fieldname'].'_lng'] = (double)$verify[$field['fieldname'].'_lng'];
                        $data[$field['ismain']][$field['fieldname'].'_lat'] = (double)$verify[$field['fieldname'].'_lat'];
                    } else {
                        $value = $verify[$field['fieldname']];
                        if (strpos($field['setting']['option']['fieldtype'], 'INT') !== FALSE) {
                            $value = (int)$value;
                        } elseif ($field['setting']['option']['fieldtype'] == 'DECIMAL'
                            || $field['setting']['option']['fieldtype'] == 'FLOAT') {
                            $value = (double)$value;
                        }
                        $data[$field['ismain']][$field['fieldname']] = $value;
                    }
                }
                $data[1]['id'] = $data[0]['id'] = $id;
                $data[1]['uid'] = (int)$verify['uid'];
                $data[1]['catid'] = $data[1]['catid'] ? $data[1]['catid'] : (int)$verify['catid'];
                $data[1]['author'] = $verify['author'];
            }
            $data[1]['status'] = $status;
            // 保存内容
            $this->content_model->edit($verify, $data);
            // 审核通过
            if ($status == 9) {
                $mark = $this->content_model->prefix.'-'.$id;
                // 操作成功处理附件
                $this->attachment_handle($data[1]['uid'], $mark, $this->field, $data);
                $this->member_model->add_notice(
                    $data[1]['uid'],
                    3,
                    dr_lang('m-084', $verify['title'])
                );
                return array('id' => $id, 'catid' => $data[1]['catid']);
            }
        } else {
            // 拒绝审核
            $this->link // 更改主表状态
                 ->where($_where)
                 ->update($this->content_model->prefix, array('status' => 0));
            $this->link // 更改索引表状态
                 ->where($_where)
                 ->update($this->content_model->prefix.'_index', array('status' => 0));
            $this->link // 更改审核表状态
                 ->where($_where)
                 ->update($this->content_model->prefix.'_verify', array(
                    'status' => 0,
                    'backuid' => (int)$this->uid,
                    'backinfo' => dr_array2string(array(
                        'uid' => $this->uid,
                        'author' => $this->admin['username'],
                        'rolename' => $this->admin['role']['name'],
                        'optiontime' => SYS_TIME,
                        'backcontent' => $this->input->post('backcontent')
                    ))
                )
            );
            $this->member_model->add_notice(
                $verify['uid'],
                3,
                dr_lang('m-124', $verify['title'], MEMBER_URL.'index.php?s='.APP_DIR.'&c=back&m=edit&id='.$id)
            );
        }
    }

    /**
     * 更新URL
     */
    public function url() {

        $cfile = SITE_ID.APP_DIR.$this->uid.$this->input->ip_address().'_content_url';

        if (IS_POST) {
            $catid = $this->input->post('catid');
            $query = $this->link;
            if (count($catid) > 1 || $catid[0]) {
                $query->where_in('catid', $catid);
            }
            $data = $query->select('id')->get($this->content_model->prefix.'_index')->result_array();
            if ($data) {
                $id = array();
                foreach ($data as $t) {
                    $id[] = $t['id'];
                }
                $this->cache->file->save($cfile, $id, 7200); // 缓存搜索结果->id
                $this->mini_msg(dr_lang('132', count($id)), dr_url(APP_DIR.'/home/url', array('todo' => 1)), 2);
            } else {
                $this->mini_msg(lang('133'));
            }
        }

        if ($this->input->get('todo')) {

            $id = $this->cache->file->get($cfile); // 取缓存搜索结果->id

            if (!$id) {
                $this->mini_msg(lang('134'));
            }

            $page = max(1, (int) $this->input->get('page'));
            $psize = 50;
            $total = count($id);
            $tpage = ceil($total / $psize); // 总页数

            if ($page > $tpage) { // 更新完成删除缓存
                $this->cache->file->delete($cfile);
                $this->mini_msg(lang('360'), NULL, 1);
            }

            $module = $this->get_cache('module-'.SITE_ID.'-'.APP_DIR);
            $table = $this->content_model->prefix;
            $data = $this->link
                         ->where_in('id', $id)
                         ->limit($psize, $psize * ($page - 1))
                         ->order_by('id DESC')
                         ->get($table)
                         ->result_array();
            foreach ($data as $t) {
                $url = dr_show_url($module, $t);
                $this->link->update($table, array('url' => $url), 'id='.$t['id']);
                if ($module['extend']) {
                    $extend = $this->link
                                   ->where('cid', $t['id'])
                                   ->order_by('id DESC')
                                   ->get($table.'_extend')
                                   ->result_array();
                    if ($extend) {
                        foreach ($extend as $e) {
                            $url = dr_extend_url($module, $e);
                            $this->link->update($table.'_extend', array('url' => $url), 'id='.(int)$e['id']);
                        }
                    }
                }
            }
            $this->mini_msg(dr_lang('135', "$tpage/$page"), dr_url(APP_DIR . '/home/url', array('todo' => 1, 'page' => $page + 1)), 2, 0);
        } else {
            $this->template->assign(array(
                'menu' => $this->get_menu(array(
                    lang('136') => APP_DIR.'/admin/home/url',
                    lang('001') => 'admin/module/cache'
                )),
                'select' => $this->select_category($this->get_cache('module-'.SITE_ID.'-'.APP_DIR, 'category'), 0, 'id="dr_synid" name=\'catid[]\' multiple style="width:200px;height:250px;"', ''),
            ));
            $this->template->display('content_url.html');
        }
    }

    /**
     * 生成静态
     */
    public function html() {

        $mod = $this->get_cache('module-'.SITE_ID.'-'.APP_DIR);
        if (!$mod['html']) {
            $html = 1;
        } elseif (SITE_ID > 1 && !$mod['domain']) {
            $html = 2;
        } else {
            $rule = FALSE;
            foreach ($mod['category'] as $t) {
                if ($t['setting']['urlrule']) {
                    $rule = TRUE;
                    break;
                }
            }
            $html = $rule ? 0 : 3;
        }

        set_cookie('mobile', -1, -1);

        $this->template->assign(array(
            'html' => $html,
            'menu' => $this->get_menu(array(
                lang('html-621') => APP_DIR.'/admin/home/html',
            )),
            'extend' => $mod['extend'] ? 1 : 0,
            'select' => $this->select_category($mod['category'], 0, 'name=\'data[catid]\'', '全部'),
        ));
        $this->template->display('content_html.html');
    }

    /**
     * 清除静态文件
     */
    public function clear() {

        $type = (int) $this->input->get('type');
        $page = (int) $this->input->get('page');
        $total = (int) $this->input->get('total');

        if ($page == 0 && !$total) {
            if ($type == 1) {
                $this->link->where('type', 3);
            } else {
                $this->link->where('type <>', 3);
            }
            $total = $this->link->count_all_results($this->content_model->prefix.'_html');
            $this->mini_msg('正在统计静态文件数量...', dr_url(APP_DIR.'/home/clear', array('type' => $type, 'page' => 1, 'total' => $total)));
        }
        $pagesize = 100; // 每次清除数量
        $count = ceil($total / $pagesize); // 计算总页数
        if ($page > $count) {
            $this->mini_msg('全部清除完成');
        }
        if ($type == 1) {
            $this->link->where('type', 3);
        } else {
            $this->link->where('type <>', 3);
        }
        $data = $this->link
                     ->select('filepath,id')
                     ->limit($pagesize, $pagesize * ($page - 1))
                     ->get($this->content_model->prefix.'_html')
                     ->result_array();
        $this->content_model->delete_html_file($data);
        $next = $page + 1;
        $this->mini_msg("共{$total}个文件，共需清理{$count}次，每次删除{$pagesize}个，正在进行第{$next}次...", dr_url(APP_DIR . '/home/clear', array('type' => $type, 'page' => $next, 'total' => $total)), 2, 0);
    }

    // 复制文章
    public function copy() {

        $id = (int)$this->input->get('id');
        $row = $this->content_model->get($id);
        if (!$row) {
            exit(dr_json(0, lang('019')));
        }
        // 格式化字段
        $data = array();
        foreach ($this->field as $field) {
            if ($field['fieldtype'] == 'Group') {
                continue;
            }
            if ($field['fieldtype'] == 'Baidumap') {
                $data[$field['ismain']][$field['fieldname'].'_lng'] = (double)$row[$field['fieldname'].'_lng'];
                $data[$field['ismain']][$field['fieldname'].'_lat'] = (double)$row[$field['fieldname'].'_lat'];
            } else {
                $value = $row[$field['fieldname']];
                if (strpos($field['setting']['option']['fieldtype'], 'INT') !== FALSE) {
                    $value = (int)$value;
                } elseif ($field['setting']['option']['fieldtype'] == 'DECIMAL'
                    || $field['setting']['option']['fieldtype'] == 'FLOAT') {
                    $value = (double)$value;
                }
                $data[$field['ismain']][$field['fieldname']] = $value;
            }
        }
        $data[1]['uid'] = (int)$row['uid'];
        $data[1]['catid'] = (int)$row['catid'];
        $data[1]['author'] = $row['author'];
        $data[1]['status'] = 9;
        // 入库
        if (($id = $this->content_model->add($data)) != FALSE) {
            exit(dr_json(1, lang('000')));
        } else {
            exit(dr_json(0, lang('357')));
        }

    }

}
