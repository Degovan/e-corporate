<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Rekanan extends User_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->model('Rekanan_model','model');
	}

	public function index() {
		$data['subtitle'] = lang('list');
		$data = array_merge($data,path_info());
		$this->parser->parse('Rekanan/index',$data);
	}

	public function index_datatable() {
		$this->load->library('Datatables');
		$this->datatables->select('mrekanan.*');
		$this->datatables->where('mrekanan.del', '0');
		$this->datatables->join('mpegawai', 'mrekanan.cby = mpegawai.id','left');
		$this->datatables->from('mrekanan');
		return print_r($this->datatables->generate());
	}

	public function create() {
		$data['subtitle'] = lang('add_new');
		$data = array_merge($data,path_info());
		$this->parser->parse('Rekanan/create',$data);
	}

	public function edit($id = null) {
		if($id) {
			$data = get_by_id('id',$id,'mrekanan');
			$data['subtitle'] = lang('edit');
			$data = array_merge($data,path_info());
			$this->parser->parse('Rekanan/edit',$data);
		} else {
			show_404();
		}
	}

	public function save() {
		$this->model->save();
	}

	public function delete() {
		$this->model->delete();
	}

	// additional
	public function select2_mpegawaihakakses($id = null) {
		$term = $this->input->get('q');
		$this->db->select('mpegawaihakakses.id, mpegawaihakakses.nama as text');
		$this->db->where('mpegawaihakakses.del', '0');
		$this->db->limit(10);
		if($term) $this->db->like('mpegawaihakakses', $term);
		if($id) $data = $this->db->where('id', $id)->get('mpegawaihakakses')->row_array();
		else $data = $this->db->get('mpegawaihakakses')->result_array();
		$this->output->set_content_type('application/json')->set_output(json_encode($data));
	}

	public function select2($perusahaan, $id = null)
	{	
		$term = $this->input->get('q');
		$this->db->select('mkontak.id, mkontak.nama as text');
		$this->db->where('mkontak.perusahaan', $perusahaan);
      	$this->db->order_by('nama', 'ASC');
      	if ($term) $this->db->like('mkontak.nama', $term);
      	$data = $this->db->get('mkontak')->result_array();
		// $data	= $this->model->select2($perusahaan, $id);
		$this->output->set_content_type('application/json')->set_output(json_encode($data));
	}
	public function select21($perusahaan, $id = null)
	{
		
		$data	= $this->model->select2($perusahaan, $id);
		$this->output->set_content_type('application/json')->set_output(json_encode($data));
	}
}