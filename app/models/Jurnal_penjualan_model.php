<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Jurnal_penjualan_model extends CI_Model {

	public function get_count_jurnal($tanggalawal, $tanggalakhir) {
		$this->db->where('tjurnalpenjualan.tanggal >=', $tanggalawal . ' 00:00:00');
		$this->db->where('tjurnalpenjualan.tanggal <=', $tanggalakhir . ' 23:59:00');
		$this->db->where('tjurnalpenjualan.status', '1');
		$get = $this->db->count_all_results('tjurnalpenjualan');
		return $get;
	}

	public function get_jurnal($offset, $limit, $tanggalawal, $tanggalakhir) {
		$this->db->where('tjurnalpenjualan.tanggal >=', $tanggalawal . ' 00:00:00');
		$this->db->where('tjurnalpenjualan.tanggal <=', $tanggalakhir . ' 23:59:00');
		$this->db->where('tjurnalpenjualan.status', '1');
		$this->db->order_by('tjurnalpenjualan.id', 'desc');
		$get = $this->db->get('tjurnalpenjualan', $limit, $offset);
		return $get->result_array();
	}

	public function get_jurnal_print($tanggalawal, $tanggalakhir) {
		$this->db->where('tjurnalpenjualan.tanggal >=', $tanggalawal . ' 00:00:00');
		$this->db->where('tjurnalpenjualan.tanggal <=', $tanggalakhir . ' 23:59:00');
		$this->db->where('tjurnalpenjualan.status', '1');
		$this->db->order_by('tjurnalpenjualan.id', 'desc');
		$get = $this->db->get('tjurnalpenjualan');
		return $get->result_array();
	}

	public function get_jurnal_detail($idjurnal) {
		$this->db->select('tjurnalpenjualandetail.*, mnoakun.namaakun');
		$this->db->join('mnoakun', 'tjurnalpenjualandetail.noakun = mnoakun.noakun', 'left');
		$this->db->where('tjurnalpenjualandetail.idjurnal', $idjurnal);
		$this->db->order_by('tjurnalpenjualandetail.debet', 'desc');
		$get = $this->db->get('tjurnalpenjualandetail');
		return $get->result_array();
	}

	public function get_jurnal_penjualan_detail($idjurnal) {
		$this->db->select('tjurnalpenjualandetail.*, mnoakunpengaturan.nama as namapengaturan, mnoakun.namaakun as lain_lain,mitem.nama as namaitem');
		$this->db->join('mnoakunpengaturan', 'tjurnalpenjualandetail.noakun = mnoakunpengaturan.noakun', 'left');
		$this->db->join('mnoakun', 'tjurnalpenjualandetail.noakun = mnoakun.akunno', 'left');
		$this->db->join('mitem', '(tjurnalpenjualandetail.noakun = mitem.noakunjual) OR (tjurnalpenjualandetail.noakun = mitem.noakunpersediaan)', 'left');
		$this->db->where('tjurnalpenjualandetail.idjurnal', $idjurnal);
		$this->db->order_by('tjurnalpenjualandetail.debet', 'desc');
		$get = $this->db->get('tjurnalpenjualandetail');
		return $get->result_array();
	}	

	public function get_pemesanan_pengiriman($idpengiriman) {
		$this->db->select('tpengiriman.*, tpemesanan.id, mdepartemen.nama as namadepartemen');
		$this->db->join('tpemesanan', 'tpengiriman.pemesananid = tpemesanan.id', 'left');
		$this->db->join('mdepartemen', 'tpemesanan.departemen = mdepartemen.id', 'left');
		$this->db->where('tpengiriman.id', $idpengiriman);
		$get = $this->db->get('tpengiriman');
		return $get->result_array();
	}

	public function get_pemesanan_pengiriman_penjualan($idpengiriman) {
		$this->db->select('tpengirimanpenjualan.*, tpemesananpenjualan.id, mdepartemen.nama as namadepartemen');
		$this->db->join('tpemesananpenjualan', 'tpengirimanpenjualan.pemesananid = tpemesananpenjualan.id', 'left');
		$this->db->join('mdepartemen', 'tpemesananpenjualan.departemen = mdepartemen.id', 'left');
		$this->db->where('tpengirimanpenjualan.id', $idpengiriman);
		$get = $this->db->get('tpengirimanpenjualan');
		return $get->result_array();
	}

	public function save() {
		$this->db->set('tanggal',$this->input->post('tanggal', TRUE));
		$this->db->set('tipe','4');
		$this->db->set('stauto','0');
		if($this->input->post('keterangan', TRUE)) $this->db->set('keterangan',$this->input->post('keterangan', TRUE));
		else $this->db->set('keterangan','Jurnal Umum');
		$this->db->set('cby',get_user('username'));
		$this->db->set('cdate',date('Y-m-d H:i:s'));
		$insertHead = $this->db->insert('tjurnalpenjualan');

		if($insertHead) {
			$idjurnal = $this->db->insert_id();
			$detail_array = $this->input->post('detail_array');
			$detail_array = json_decode($detail_array);
			foreach($detail_array as $row) {
				$this->db->set('idjurnal',$idjurnal);
				$this->db->set('noakun',$row[0]);
				$this->db->set('debet',remove_comma($row[2]));
				$this->db->set('kredit',remove_comma($row[3]));
				$this->db->set('keterangan','-');
				$this->db->insert('tjurnalpenjualandetail');
			}
			$data['status'] = 'success';
			$data['message'] = lang('update_success_message');
		}
		return $this->output->set_content_type('application/json')->set_output(json_encode($data));
	}

}

