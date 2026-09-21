<?php
class Master_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function Simpan($table, $data){
		return $this->db->insert($table, $data);
	}
	public function getData($table, $where_field='', $where_value=''){
		if($where_field !='' && $where_value!=''){
			$query = $this->db->get_where($table, array($where_field=>$where_value));
		}else{
			$query = $this->db->get($table);
	}
		return $query->result();
	}
	public function getDataArrayMultiple($table, $ArrWhere=''){
	if(!empty($ArrWhere)){
			$query = $this->db->get_where($table, $ArrWhere);
		}else{
			$query = $this->db->get($table);
	}
		return $query->result();
	}
	public function getDataOrderBy($table, $where_field='', $where_value='', $field_order=''){
		if($where_field !='' && $where_value!=''){
			$query = $this->db->order_by($field_order, 'ASC')->get_where($table, array($where_field=>$where_value));
		}else{
			$query = $this->db->order_by($field_order, 'ASC')->get($table);
	}
		return $query->result();
	}
	public function getDataArray($table, $where_field='', $where_value='', $keyArr='', $valArr='', $where_field2='', $where_value2=''){
		if($where_field !='' && $where_value!=''){
			$query = $this->db->get_where($table, array($where_field=>$where_value));
		}
		if($where_field2 !='' && $where_value2 != '' && $where_field !='' && $where_value!=''){
			$query = $this->db->get_where($table, array($where_field=>$where_value, $where_field2=>$where_value2));
		}
		else{
			$query = $this->db->get($table);
		}
		$dataArr = $query->result_array();
		if(!empty($keyArr) && !empty($valArr)){
			$Arr_Data = array();
			foreach($dataArr as $key=>$val){
				$nilai_id = $val[$keyArr];
				if(eapty($valArr)){
					$Arr_Data[$nilai_id] = $val;
				}else{
					$Arr_Data[$nilai_id] = $nilai_id;
				}
			}
			return $Arr_Data;
		}else{
			return $dataArr;
		}
	}
	public function getCount($table, $where_field='', $where_value=''){
		if($where_field !='' && $where_value!=''){
			$query = $this->db->get_where($table, array($where_field=>$where_value));
	}else{
			$query = $this->db->get($table);
	}
		return $query->num_rows();
	}
	public function getUpdate($table, $data, $where_field='', $where_value=''){
		if($where_field !='' && $where_value!=''){
			$query = $this->db->where(array($where_field=>$where_value));
		}
		$result = $this->db->update($table, $data);
		return $result;
	}
	public function getDelete($table, $where_field, $where_value){
		$result = $this->db->delete($table, array($where_field=>$where_value));
		return $result;
	}
	public function getMenu($where=array()){
		$aMenu = array();
		if(!empty($where)){
			$query = $this->db->get_where('menus', $where);
		}else{
			$query = $this->db->get('menus');
		}
		$results = $query->result_array();
		if($results){
			foreach($results as $key=>$vals){
				$aMenu[$vals['id']] = $vals['name'];
			}
		}
		return $aMenu;
	}
	public function getArray($table, $WHERE=array(), $keyArr='', $valArr=''){
		if($WHERE){
			$query = $this->db->get_where($table, $WHERE);
		}else{
			$query = $this->db->get($table);
		}
		$dataArr = $query->result_array();
		if(!empty($keyArr)){
			$Arr_Data = array();
			foreach($dataArr as $key=>$val){
				$nilai_id = $val[$keyArr];
				if(!empty($valArr)){
					$nilai_val = $val[$valArr];
					$Arr_Data[$nilai_id] = $nilai_val;
				}else{
					$Arr_Data[$nilai_id] = $val;
				}
			}
			return $Arr_Data;
		}else{
			return $dataArr;
		}
	}
}
