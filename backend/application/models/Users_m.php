<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Users_m extends MY_Model
{

    protected $_table_name = 'tbl_user';
    protected $_primary_key = 'id';
    protected $_primary_filter = 'intval';
    protected $_order_by = "tbl_user.register_time desc";

    function __construct()
    {
        parent::__construct();
        //$this->db->where('password is null');
        //$this->db->delete($this->_table_name);
    }

    public function update_usage_time()
    {
        $loginUserId = $this->session->userdata('loginuserID');
        $userInfo = array('update_time' => date('Y-m-d H:i:s'));
        $this->edit($userInfo, $loginUserId);
    }

    public function getItemsByPage($arr = array(), $pageId, $cntPerPage, $type = 'admin')
    {
        $this->db->select($this->_table_name . '.*');
        $this->db->select('tbl_huijiao_subject.title as subject, tbl_huijiao_subject.id as subject_id');
        $this->db->select('tbl_huijiao_terms.title as term, tbl_huijiao_terms.id as term_id');
        if ($type != 'admin') $this->db->where($this->_table_name . '.user_id != 0');
        $this->db->like($arr)
            ->from($this->_table_name)
            ->join('tbl_huijiao_terms', $this->_table_name . '.term_id = tbl_huijiao_terms.id', 'left')
            ->join('tbl_huijiao_subject', 'tbl_huijiao_terms.subject_id = tbl_huijiao_subject.id', 'left')
            ->order_by($this->_order_by)
            ->limit($cntPerPage, $pageId);
        $query = $this->db->get();
        return $query->result();
    }

    public function get_count($arr = array(), $type = 'admin')
    {
        if ($type != 'admin') $this->db->where($this->_table_name . '.user_id != 0');
        $this->db->like($arr)
            ->from($this->_table_name)
            ->join('tbl_huijiao_terms', $this->_table_name . '.term_id = tbl_huijiao_terms.id', 'left')
            ->join('tbl_huijiao_subject', 'tbl_huijiao_terms.subject_id = tbl_huijiao_subject.id', 'left')
            ->order_by($this->_order_by);
        $query = $this->db->get();
        return $query->num_rows();
    }

    public function prepareSchoolInfo()
    {
        $this->db->select('id,user_info');
        $this->db->from($this->_table_name);
        $this->db->where('user_school is null');
        $query = $this->db->get();
        $result = $query->result();
        if (count($result) <= 0) return false;
        $isChanged = false;
        foreach ($result as $item) {
            $userInfo = json_decode($item->user_info);
            if ($userInfo == '') continue;
            $school = $userInfo->organName;
            $this->db->where('id', $item->id);
            $this->db->update($this->_table_name, array('user_school' => $school));
            $isChanged = true;
        }
        return $isChanged;
    }

    public function getSchoolItemsByPage($arr = array(), $pageId, $cntPerPage, $type = 'admin')
    {
        $this->db->select($this->_table_name . '.*');
        $this->db->select('count(id) as user_count');
        $this->db->select('sum( case when user_type = 1 then 1 else 0 end) as teacher_count');
        $this->db->select('sum( case when user_type = 2 then 1 else 0 end) as student_count');
        $this->db->like($arr)
            ->from($this->_table_name)
            ->order_by($this->_order_by)
            ->group_by($this->_table_name . '.user_school')
            ->limit($cntPerPage, $pageId);
        $query = $this->db->get();
        return $query->result();
    }

    public function get_school_count($arr = array(), $type = 'admin')
    {
        if ($type != 'admin') $this->db->where($this->_table_name . '.user_id != 0');
        $this->db->like($arr)
            ->from($this->_table_name)
            ->order_by($this->_order_by)
            ->group_by($this->_table_name . '.user_school');
        $query = $this->db->get();
        return $query->num_rows();
    }

    public function getItems()
    {
        $this->db->select('*')
            ->from($this->_table_name)
            ->order_by($this->_order_by);
        $query = $this->db->get();
        return $query->result();
    }

    public function addUserAction($dateStr, $arr){

        $this->db->from('tbl_user_action');
        $this->db->where('action_date', $dateStr);
        $query = $this->db->get();
        $temp = $query->result();
        if ($temp == null)
            $this->db->insert('tbl_user_action', $arr);
        else {
            $this->db->where('id', $temp[0]->id);
            $this->db->update('tbl_user_action', $arr);
        }
    }

    public function get_single($arr = array())
    {
        return parent::get_single($arr);
    }

    public function getInfo()
    {
        $this->db->select('register_count, create_time, register_time, update_time');
        $this->db->from($this->_table_name);
        $this->db->where('tbl_user.status', 1);
        $query = $this->db->get();
        $total_users = $query->result();

        $this->db->from('tbl_user_action');
        $query = $this->db->get();
        $total_actions = $query->result();

        return array('total_users' => $total_users, 'total_actions' => $total_actions);
    }

    public function get_where($array = array(), $subCondition = NULL)
    {
        return parent::get_where($array, $subCondition); // TODO: Change the autogenerated stub
    }

    public function delete($item_id)
    {
        $this->db->where($this->_primary_key, $item_id);
        $this->db->delete($this->_table_name);
        return $this->getItems();
    }

    public function publish($item_id, $publish_st, $site_id = 1)
    {
        $this->db->set('status', $publish_st);
        $this->db->where($this->_primary_key, $item_id);
        $this->db->update($this->_table_name);
        return $this->getItems();
    }

    public function edit($arr, $item_id)
    {
        $this->db->where($this->_primary_key, $item_id);
        $this->db->update($this->_table_name, $arr);
        return $this->getItems();
    }

    public function get_users()
    {
        $users_data = array();
        $SQL = "SELECT  *
                FROM " . $this->_table_name . "                
                ORDER By id ASC 
                ;";
        $query = $this->db->query($SQL);
        $users_data = $query->result();
        return $users_data;
    }

    function insert($array)
    {
        return parent::insert($array); // TODO: Change the autogenerated stub
    }

    public function add($param)
    {
        $arr = array(
            'user_account' => $param['user_account'],
            'user_name' => $param['user_name'],
            'password' => $this->hash($param['password']),
            'user_type' => $param['user_type'],
            'user_nickname' => $param['user_nickname'],
            'term_id' => $param['term_id'],
            'status' => $param['status'],
            'user_info' => $param['user_info'],
            'information' => $param['information'],
            'register_count' => $param['register_count'],
            'create_time' => $param['create_time'],
            'register_time' => $param['register_time'],
            'update_time' => $param['update_time'],
        );

        $this->db->insert($this->_table_name, $arr);
        return $this->db->insert_id();
    }

    public function get_single_user($user_id)
    {
        $user_data = array();
        $SQL = 'SELECT  *
                FROM ' . $this->_table_name . '
                WHERE id = ' . $user_id . ' 
                ;';
        $query = $this->db->query($SQL);
        $user_data = $query->row();
        return $user_data;
    }

    public function get_student_user($user_id)
    {
        $user_data = array();
        $this->db->select($this->_table_name . '.*');
        $this->db->select('tbl_class.id as class_id, tbl_class.class_name as class_name, tbl_class.teacher_id as teacher_id');
        $this->db->where('tbl_user.id', $user_id)
            ->from($this->_table_name)
            ->join('tbl_class', $this->_table_name . '.user_class = tbl_class.class_name', 'left');
        $query = $this->db->get();
        $user_data = $query->row();
        return $user_data;
    }

    public function get_single_by_name($arr)
    {
        $user_data = array();
        $this->db->select('*')
            ->from($this->_table_name)
            ->where($arr);
        $query = $this->db->get();
        $user_data = $query->row();
        return $user_data;
    }

    function update_user($data, $id = NULL)///for update session of login time
    {
        $this->db->where('id', $id);
        $this->db->set($data);
        $this->db->update($this->_table_name);
        return $this->get_single_user($id);
    }

    function update_user_login_num($id)///for update session of login time
    {
        $this->db->set('register_count', 'register_count+1', FALSE);
        $this->db->where('id', $id);
        $this->db->update($this->_table_name);
    }

    public function hash($string)
    {
        return parent::hash($string);
    }

}
