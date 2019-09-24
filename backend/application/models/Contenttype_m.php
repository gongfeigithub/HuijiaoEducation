<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Contenttype_m extends MY_Model
{
    protected $_table_name = 'tbl_huijiao_content_type';
    protected $_primary_key = 'id';
    protected $_primary_filter = 'intval';
    protected $_order_by = "tbl_huijiao_content_type.contenttype_no asc";

    function __construct()
    {
        parent::__construct();
    }

    public function getItemsByPage($arr = array(), $pageId, $cntPerPage)
    {
        $this->db->select($this->_table_name . '.*');
        $this->db->like($arr)
            ->from($this->_table_name)
            ->order_by($this->_order_by)
            ->limit($cntPerPage, $pageId);
        $query = $this->db->get();
        return $query->result();
    }

    public function get_count($arr = array())
    {
        $this->db->like($arr)
            ->from($this->_table_name)
            ->order_by($this->_order_by);
        $query = $this->db->get();
        return $query->num_rows();
    }

    public function getFilteredContentTypes($array = array(), $queryStr = '')
    {
        if ($queryStr != '') {
            $this->db->where(
                '(tbl_huijiao_subject.title like \'%' . $queryStr . '%\' '
                . 'or tbl_huijiao_terms.title like \'%' . $queryStr . '%\' '
                . 'or tbl_huijiao_content_type.title like \'%' . $queryStr . '%\' '
                . 'or tbl_huijiao_contents.title like \'%' . $queryStr . '%\' '
                . 'or tbl_huijiao_course_type.title like \'%' . $queryStr . '%\')'
            );
        }
        $this->db->select('tbl_huijiao_content_type.*')
            ->from('tbl_huijiao_contents')
            ->join('tbl_huijiao_course_type', 'tbl_huijiao_contents.course_type_id = tbl_huijiao_course_type.id', 'left')
            ->join('tbl_huijiao_content_type', 'tbl_huijiao_contents.content_type_no = tbl_huijiao_content_type.id', 'left')
            ->join('tbl_huijiao_terms', 'tbl_huijiao_course_type.term_id = tbl_huijiao_terms.id', 'left')
            ->join('tbl_huijiao_subject', 'tbl_huijiao_terms.subject_id = tbl_huijiao_subject.id', 'left');
        $this->db->like($array)
            ->group_by('tbl_huijiao_content_type.id')
            ->order_by($this->_order_by);
        $query = $this->db->get();
        return $query->result();
    }


    public function getItems()
    {
        $this->db->select('*')
            ->from($this->_table_name)
            ->order_by($this->_order_by);
        $query = $this->db->get();
        return $query->result();
    }

    public function add($arr)
    {
        $this->db->insert($this->_table_name, $arr);
        return $this->db->insert_id();
    }

    public function get_single($arr = array())
    {
        return parent::get_single($arr);
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
}
