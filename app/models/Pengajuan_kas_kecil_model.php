<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pengajuan_kas_kecil_model extends CI_Model {

	private $id;
	private $status;

	public function save() {
		$id = $this->uri->segment(3);
		if($id) {
			foreach($this->input->post() as $key => $val) $this->db->set($key,strip_tags($val));
			$element2 = str_replace(".","",$this->input->post('nominal'));
			$nominal   = floatval(str_replace(",",".",$element2));
			$this->db->set('nominal',$nominal);
			$this->db->set('uby',get_user('username'));
			$this->db->set('udate',date('Y-m-d H:i:s'));
			$this->db->where('id', $id);
			$update = $this->db->update('tpengajuankaskecil');
			if($update) {
				$data['status'] = 'success'; 
				$data['message'] = lang('update_success_message');
			} else {
				$data['status'] = 'error';
				$data['message'] = lang('update_error_message');
			}
		} else {
      $this->load->helper('penomoran');
      $penomoran  = penomoran('pengajuanKasKecil', $this->input->post('perusahaan'));
			foreach($this->input->post() as $key => $val) $this->db->set($key,strip_tags($val));
			$this->db->set('nomor', $penomoran['nomor']);
			$this->db->set('nokwitansi', $penomoran['notrans']);
			$element2 = str_replace(".","",$this->input->post('nominal'));
			$nominal   = floatval(str_replace(",",".",$element2));
			$this->db->set('nominal',$nominal);
			$this->db->set('cby',get_user('username'));
			$this->db->set('cdate',date('Y-m-d H:i:s'));
			$insert = $this->db->insert('tpengajuankaskecil');
			if($insert) {
				$data['status'] = 'success';
				$data['message'] = lang('save_success_message');
			} else {
				$data['status'] = 'error';
				$data['message'] = lang('save_error_message');
			}
		}
		return $this->output->set_content_type('application/json')->set_output(json_encode($data));
	}

	public function delete() {
		$id = $this->uri->segment(3);
		$this->db->set('stdel','1');
		$this->db->set('dby',get_user('username'));
		$this->db->set('ddate',date('Y-m-d H:i:s'));
		$this->db->where('id', $id);
		$update = $this->db->update('tpengajuankaskecil');
		if($update) {
			$data['status'] = 'success';
			$data['message'] = lang('delete_success_message');
		} else {
			$data['status'] = 'error';
			$data['message'] = lang('delete_error_message');
		}
		return $this->output->set_content_type('application/json')->set_output(json_encode($data));
	}

	function get_kodeperusahaan($id){
        $query = $this->db->get_where('mperusahaan', array('idperusahaan' => $id));
        return $query;
    }

    public function cetakdata($tanggalawal,$tanggalakhir) {
		$this->db->select('tpengajuankaskecil.*,mperusahaan.nama_perusahaan');
		$this->db->join('mperusahaan','tpengajuankaskecil.perusahaan=mperusahaan.idperusahaan');
		if ((!empty($tanggalawal)) & (!empty($tanggalakhir))) {
			$this->db->where('tpengajuankaskecil.tanggal >=',$tanggalawal);
			$this->db->where('tpengajuankaskecil.tanggal <=',$tanggalakhir);
		}
		$this->db->where('tpengajuankaskecil.stdel', '0');
		$get = $this->db->get('tpengajuankaskecil');
		return $get->result_array();
	}

    function get_hitungsisakaskecil($idper){
		$query_pemindahbukuan = $this->db->query("SELECT nominal FROM tpemindahbukuankaskecil WHERE perusahaan='$idper' ");
		$nominal_pemindahbukuan=0;
		if($query_pemindahbukuan->num_rows()>0){
			foreach($query_pemindahbukuan->result() as $p){
				$nominal_pemindahbukuan	+=(integer) $p->nominal;
			}
		}

		$this->db->select('total');
		$query_pengeluaran	= $this->db->get_where('tpengeluarankaskecil', [
			'perusahaan'	=> $idper
		]);
		$jumlah_nominal_pengajuan = 0;
		if($query_pengeluaran->num_rows()>0){
			foreach($query_pengeluaran->result() as $p){
				$jumlah_nominal_pengajuan	+= (integer) $p->total;
			}
		}

		$query_setor = $this->db->query("SELECT nominal FROM tsetorkaskecil WHERE perusahaan='$idper'AND status='1' AND stdel='0'");
		$jumlah_nominal_setor =0;
		if($query_setor->num_rows()>0){
			foreach($query_setor->result() as $p){
				$jumlah_nominal_setor	+= (integer) $p->nominal;
			}
		}

		$hasil = $nominal_pemindahbukuan - $jumlah_nominal_pengajuan - $jumlah_nominal_setor;
		$data['hasil'] = $hasil;
		$this->output->set_content_type('application/json')->set_output(json_encode($data));
	}

	public function set($jenis, $isi)
	{
		$this->$jenis	= $isi;
	}
	
	public function validasi()
	{
		switch ($this->status) {
			case '0':
				$status	= 1;
				break;
			case '1':
				$status	= 0;
				break;
			
			default:
				# code...
				break;
		}
		$this->db->where('id', $this->id);
		return $this->db->update('tpengajuankaskecil', [
			'status' => $status
		]);
	}
}

