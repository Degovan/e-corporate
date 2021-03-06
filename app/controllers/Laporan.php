<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Laporan extends User_Controller {

	private $perusahaan;
	private $rekening;
	private $tanggal;
	private $kasKecil;
	private $tanggalAwal;
	private $tanggalAkhir;

	public function __construct() {
		parent::__construct();
		$this->perusahaan	= $this->input->get('perusahaan');
		$this->rekening		= $this->input->get('rekening');
		$this->tanggal		= $this->input->get('tanggal');
		$this->kasKecil		= $this->input->get('kasKecil');
		$this->tanggalAwal	= $this->input->get('tanggalAwal');
		$this->tanggalAkhir	= $this->input->get('tanggalAkhir');
		$this->jenis		= $this->input->get('jenis');
		$this->load->model('Perusahaan_model');
		$this->load->model('Neraca_model');
	}

	public function kasBank() {
		if ($this->perusahaan) {
			$this->LaporanModel->set('perusahaan', $this->perusahaan);
			$this->LaporanModel->set('rekening', $this->rekening);
			$this->LaporanModel->set('tanggalAwal', $this->tanggalAwal);
			$this->LaporanModel->set('tanggalAkhir', $this->tanggalAkhir);
			$data['laporan']		= $this->LaporanModel->getLaporanKasBank();
			$data['tanggalAwal']	= $this->tgl_indo($this->tanggalAwal);
			$data['tanggalAkhir']	= $this->tgl_indo($this->tanggalAkhir);
			$data['perusahaan']		= $this->Perusahaan_model->get_by_id($this->perusahaan);
			$data['rekening']		= $this->rekening;
			switch ($this->input->get('jenis')) {
				case 'pdf':
					$this->load->library('pdf');
					$pdf			= $this->pdf;
					$data['title']	= 'Laporan Kas Bank';
					$data['css']	= file_get_contents(FCPATH.'assets/css/print.min.css');
					$data			= array_merge($data,path_info());
					$html 			= $this->load->view('laporan/kasBank/print', $data, TRUE);
					$pdf->loadHtml($html);
					$pdf->setPaper('A4', 'portrait');
					$pdf->render();
					$time = time();
					$pdf->stream("Laporan Kas Bank". $time, array("Attachment" => false));
					break;
				case 'excel':
					$spreadsheet	= \PhpOffice\PhpSpreadsheet\IOFactory::load('assets/Buku Kas Bank Excel.xlsx');
					$worksheet		= $spreadsheet->getActiveSheet();
					$worksheet->getCell('A1')->setValue($data['perusahaan']['nama_perusahaan']);
					$worksheet->getCell('A3')->setValue('From ' . $data['tanggalAwal'] . ' to ' . $data['tanggalAkhir']);
					$no = 0;
					$x	= 6;
					foreach ($data['laporan'] as $key) {
						foreach ($key as $value) { 
							$worksheet->getCell('A' . $x)->setValue($value['tanggal']);
							$worksheet->getCell('B' . $x)->setValue($value['no']);
							$worksheet->getCell('C' . $x)->setValue($value['keterangan']);
							$worksheet->mergeCells('C' . $x . ':' . 'D' . $x);
							$worksheet->getCell('E' . $x)->setValue(number_format($value['debet'],2,',','.'));
							$worksheet->getCell('F' . $x)->setValue(number_format($value['kredit'],2,',','.'));
							$worksheet->getCell('G' . $x)->setValue(number_format(($value['debet'] - $value['kredit']),2,',','.'));
							$x++;
						}
					}
					$writer = new Xlsx($spreadsheet);
					$filename = 'LaporanBukuBank';
					
					header('Content-Type: application/vnd.ms-excel');
					header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
					header('Cache-Control: max-age=0');
			
					$writer->save('php://output');
					break;
				
				default:
					# code...
					break;
			}
		} else {
			$data['title']		= 'Laporan Kas Bank';
			$data['subtitle']	= lang('list');
			$data['content']	= 'laporan/kasBank/index';
			$data				= array_merge($data,path_info());
			$this->parser->parse('template',$data);
		}
	}

	public function outstandingInvoice()
	{
		if ($this->perusahaan) {
			$this->LaporanModel->set('perusahaan', $this->perusahaan);
			$this->LaporanModel->set('tanggal', $this->tanggal);
			$data['laporan']	= $this->LaporanModel->getOutstandingInvoice();
			$data['tanggal']	= $this->tgl_indo($this->tanggal);
			$data['perusahaan']	= $this->Perusahaan_model->get_by_id($this->perusahaan);

			switch ($this->input->get('jenis')) {
				case 'pdf':
					$this->load->library('pdf');
					$pdf			= $this->pdf;
					$data['title']	= 'Outstanding Invoice Report';
					$data['css']	= file_get_contents(FCPATH.'assets/css/print.min.css');
					$data			= array_merge($data,path_info());
					$html 			= $this->load->view('laporan/outstandingInvoice/print', $data, TRUE);
					$pdf->loadHtml($html);
					$pdf->setPaper('A4', 'portrait');
					$pdf->render();
					$time = time();
					$pdf->stream("Outstanding Invoice Report". $time, array("Attachment" => false));
					break;
				case 'excel':
					$spreadsheet	= \PhpOffice\PhpSpreadsheet\IOFactory::load('assets/Outstanding Invoice.xlsx');
					$worksheet		= $spreadsheet->getActiveSheet();
					$worksheet->getCell('A1')->setValue($data['perusahaan']['nama_perusahaan']);
					$worksheet->getCell('A3')->setValue('As of ' . $data['tanggal']);
					$no = 0;
					$x	= 6;
					foreach ($data['laporan'] as $key) { 
						$tanggal            = new DateTime($key['tanggal']);
						$tanggalTempo       = new DateTime($key['tanggaltempo']);
						$tanggalSekarang    = new DateTime();
						$selisih            = $tanggalTempo->diff($tanggal)->days;
						$selisih1           = $tanggalSekarang->diff($tanggal)->days;
						$usiaHutang	        = $selisih1 - $selisih;
						$worksheet->getCell('A' . $x)->setValue($key['nomorsuratjalan']);
						$worksheet->getCell('B' . $x)->setValue($key['nama_perusahaan']);
						$worksheet->getCell('B' . $x + 1)->setValue($key['tanggal']);
						$worksheet->getCell('C' . $x)->setValue($key['tanggaltempo']);
						$worksheet->getCell('D' . $x)->setValue(number_format($key['total'],2,',','.'));
						$worksheet->getCell('E' . $x)->setValue(number_format($key['sisatagihan'],2,',','.'));
						$worksheet->getCell('G' . $x)->setValue(number_format($usiaHutang));
						$worksheet->mergeCells('A' . $x . ':' . 'A' . $x + 1);
						$worksheet->mergeCells('C' . $x . ':' . 'C' . $x + 1);
						$worksheet->mergeCells('D' . $x . ':' . 'D' . $x + 1);
						$worksheet->mergeCells('E' . $x . ':' . 'E' . $x + 1);
						$worksheet->mergeCells('F' . $x . ':' . 'F' . $x + 1);
						$worksheet->mergeCells('G' . $x . ':' . 'G' . $x + 1);
					}
					$writer = new Xlsx($spreadsheet);
					$filename = 'OutstandingInvoice';
					
					header('Content-Type: application/vnd.ms-excel');
					header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
					header('Cache-Control: max-age=0');
			
					$writer->save('php://output');
					break;
				default:
					# code...
					break;
			}
		} else {
			$data['title']		= 'Outstanding Invoice Report';
			$data['subtitle']	= lang('list');
			$data['content']	= 'laporan/outstandingInvoice/index';
			$data				= array_merge($data,path_info());
			$this->parser->parse('template',$data);
		}
	}

	public function outstandingPayable()
	{
		if ($this->perusahaan) {
			$this->LaporanModel->set('perusahaan', $this->perusahaan);
			$this->LaporanModel->set('tanggal', $this->tanggal);
			$data['laporan']	= $this->LaporanModel->getOutstandingPayable();
			$data['tanggal']	= $this->tgl_indo($this->tanggal);
			$data['perusahaan']	= $this->Perusahaan_model->get_by_id($this->perusahaan);
			switch ($this->input->get('jenis')) {
				case 'pdf':
					$this->load->library('pdf');
					$pdf			= $this->pdf;
					$data['title']	= 'Outstanding Payable Report';
					$data['css']	= file_get_contents(FCPATH.'assets/css/print.min.css');
					$data			= array_merge($data,path_info());
					$html 			= $this->load->view('laporan/outstandingPayable/print', $data, TRUE);
					$pdf->loadHtml($html);
					$pdf->setPaper('A4', 'portrait');
					$pdf->render();
					$time = time();
					$pdf->stream("Outstanding Payable Report". $time, array("Attachment" => false));
					break;
				case 'excel':
					$spreadsheet	= \PhpOffice\PhpSpreadsheet\IOFactory::load('assets/Outstanding Payable.xlsx');
					$worksheet		= $spreadsheet->getActiveSheet();
					$worksheet->getCell('A1')->setValue($data['perusahaan']['nama_perusahaan']);
					$worksheet->getCell('A3')->setValue('As of ' . $data['tanggal']);
					$no = 0;
					$x	= 6;
					foreach ($data['laporan'] as $key) { 
						foreach ($key as $value) { 
							$tanggal            = new DateTime($key['tanggal']);
							$tanggalTempo       = new DateTime($key['tanggaltempo']);
							$tanggalSekarang    = new DateTime();
							$selisih            = $tanggalTempo->diff($tanggal)->days;
							$selisih1           = $tanggalSekarang->diff($tanggal)->days;
							$usiaHutang	        = $selisih1 - $selisih;
							$worksheet->getCell('A' . $x)->setValue($key['nomorsuratjalan']);
							$worksheet->getCell('B' . $x)->setValue($key['nama_perusahaan']);
							$worksheet->getCell('B' . $x + 1)->setValue($key['tanggal']);
							$worksheet->getCell('C' . $x)->setValue($key['tanggaltempo']);
							$worksheet->getCell('D' . $x)->setValue(number_format($key['total'],2,',','.'));
							$worksheet->getCell('E' . $x)->setValue(number_format($key['sisatagihan'],2,',','.'));
							$worksheet->getCell('G' . $x)->setValue(number_format($usiaHutang));
							$worksheet->mergeCells('A' . $x . ':' . 'A' . $x + 1);
							$worksheet->mergeCells('C' . $x . ':' . 'C' . $x + 1);
							$worksheet->mergeCells('D' . $x . ':' . 'D' . $x + 1);
							$worksheet->mergeCells('E' . $x . ':' . 'E' . $x + 1);
							$worksheet->mergeCells('F' . $x . ':' . 'F' . $x + 1);
							$worksheet->mergeCells('G' . $x . ':' . 'G' . $x + 1);
						} 
					}
					$writer = new Xlsx($spreadsheet);
					$filename = 'OutstandingInvoice';
					
					header('Content-Type: application/vnd.ms-excel');
					header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
					header('Cache-Control: max-age=0');
			
					$writer->save('php://output');
				default:
					# code...
					break;
			}
		} else {
			$data['title']		= 'Outstanding Payable Report';
			$data['subtitle']	= lang('list');
			$data['content']	= 'laporan/outstandingPayable/index';
			$data				= array_merge($data,path_info());
			$this->parser->parse('template',$data);
		}
	}

	public function projectList()
	{
		if ($this->perusahaan) {
			$this->LaporanModel->set('perusahaan', $this->perusahaan);
			$this->LaporanModel->set('tanggal', $this->tanggal);
			$this->LaporanModel->set('tanggalAwal', $this->tanggalAwal);
			$this->LaporanModel->set('tanggalAkhir', $this->tanggalAkhir);
			$data['laporan']		= $this->LaporanModel->getProject();
			$data['tanggalAwal']	= $this->tgl_indo($this->tanggalAwal);
			$data['tanggalAkhir']	= $this->tgl_indo($this->tanggalAkhir);
			$data['perusahaan']		= $this->Perusahaan_model->get_by_id($this->perusahaan);
			switch ($this->input->get('jenis')) {
				case 'pdf':
					$this->load->library('pdf');
					$pdf			= $this->pdf;
					$data['title']	= 'Project List';
					$data['css']	= file_get_contents(FCPATH.'assets/css/print.min.css');
					$data			= array_merge($data,path_info());
					$html 			= $this->load->view('laporan/projectList/print', $data, TRUE);
					$pdf->loadHtml($html);
					$pdf->setPaper('A4', 'portrait');
					$pdf->render();
					$time = time();
					$pdf->stream("Project List Report". $time, array("Attachment" => false));
					break;
				case 'excel':
					$spreadsheet	= \PhpOffice\PhpSpreadsheet\IOFactory::load('assets/Project List.xlsx');
					$worksheet		= $spreadsheet->getActiveSheet();
					$worksheet->getCell('A1')->setValue($data['perusahaan']['nama_perusahaan']);
					$worksheet->getCell('A3')->setValue('From ' . $data['tanggalAwal'] . ' to ' . $data['tanggalAkhir']);
					$no = 0;
					$x	= 6;
					foreach ($data['laporan'] as $key) {
						$worksheet->getCell('A' . $x)->setValue($key['noEvent']);
						$worksheet->getCell('B' . $x)->setValue($key['deskripsi']);
						$worksheet->getCell('C' . $x)->setValue($key['region']);
						$worksheet->getCell('D' . $x)->setValue($key['cabang']);
						$worksheet->getCell('E' . $x)->setValue(number_format($key['totalPendapatan'],2,',','.'));
						$worksheet->getCell('F' . $x)->setValue(number_format($key['totalHPP'],2,',','.'));
						$worksheet->getCell('G' . $x)->setValue($key['kodeEvent']);
						$worksheet->getCell('H' . $x)->setValue($key['kelompokUmur']);
						$worksheet->getCell('I' . $x)->setValue($this->tgl_indo($key['tanggalMulai']));
						$worksheet->getCell('J' . $x)->setValue($this->tgl_indo($key['tanggalSelesai']));
					} 
					$writer = new Xlsx($spreadsheet);
					$filename = 'ProjectList';
					
					header('Content-Type: application/vnd.ms-excel');
					header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
					header('Cache-Control: max-age=0');
			
					$writer->save('php://output');
					break;
				
				default:
					# code...
					break;
			}
		} else {
			$data['title']		= 'Project List';
			$data['subtitle']	= lang('list');
			$data['content']	= 'laporan/projectList/index';
			$data				= array_merge($data,path_info());
			$this->parser->parse('template',$data);
		}
	}

	public function tgl_indo($tanggal){
		$bulan = array (
			1 =>   'Januari',
			'Februari',
			'Maret',
			'April',
			'Mei',
			'Juni',
			'Juli',
			'Agustus',
			'September',
			'Oktober',
			'November',
			'Desember'
		);
		$pecahkan = explode('-', $tanggal);
		return $pecahkan[2] . ' ' . $bulan[ (int)$pecahkan[1] ] . ' ' . $pecahkan[0];
	}

	public function bukuPembantuKasKecil()
	{
		$data['laporan']	= null;
		$data['tanggal']	= null;
		if ($this->perusahaan) {
			$this->LaporanModel->set('perusahaan', $this->perusahaan);
			$this->LaporanModel->set('kasKecil', $this->kasKecil);
			$this->LaporanModel->set('tanggalAwal', $this->tanggalAwal);
			$this->LaporanModel->set('tanggalAkhir', $this->tanggalAkhir);
			$data['laporan'] = $this->LaporanModel->getBukuPembantuKasKecil();
			$data['tanggal'] = $this->tgl_indo($this->tanggalAkhir);
			$tanggalAwal = date('Y-m-d', strtotime('-1 days', strtotime($this->tanggalAkhir)));
			// $data['tanggalAwal']	= $this->tgl_indo($tanggalAwal);
			$data['tanggalAwal'] = $this->tanggalAwal;
			$data['tanggalAkhir'] = $this->tanggalAkhir;
			$this->LaporanModel->set('tanggal', $tanggalAwal);
			$kasBank = $this->LaporanModel->getBukuPembantuKasKecil('total');
			$data['jumlahDebetAwal'] = 0;
			$data['jumlahKreditAwal'] = 0;
			foreach ($kasBank as $key) {
				foreach ($key as $value) {
					$data['jumlahDebetAwal'] += $value['debet'];
					$data['jumlahKreditAwal'] += $value['kredit'];
				}
			}
			$data['perusahaan']	= $this->perusahaan;
			$data['rekening']	= $this->rekening;
			$data['tanggalA']	= $this->tanggal;
		}
		$data['title']		= 'Laporan Buku Pembantu Kas Kecil';
		$data['subtitle']	= lang('list');
		
		if(empty($this->input->get('jenis'))){
			$data['content']	= 'laporan/bukuPembantuKasKecil/index';
			$data = array_merge($data,path_info());
			$this->parser->parse('template',$data);
		} else {
			// $data['tanggal'] = $this->tgl_indo($this->tanggal);
			// $data['rekening'] = $data['laporan'][0][0]['nama']. " - " . $data['laporan'][0][0]['norek'];
			$data['perusahaan'] = $this->Perusahaan_model->get_by_id($this->perusahaan);
			// echo json_encode($data); die();

			switch ($this->input->get('jenis')) {
				case 'pdf':
					$this->load->library('pdf');
					$pdf = $this->pdf;
					$data['css'] = file_get_contents(FCPATH.'assets/css/print.min.css');
					$data = array_merge($data,path_info());

					$html = $this->load->view('laporan/bukuPembantuKasKecil/print', $data, TRUE);
					// echo $html;
					$pdf->loadHtml($html);
					$pdf->setPaper('A4', 'portrait');
					$pdf->render();
					$time = time();
					$pdf->stream("Profit & Loss (Standar)". $time, array("Attachment" => false));
					break;
					
				case 'excel':
					include_once APPPATH . 'third_party/PHPExcel.php';

					$object = new PHPExcel();
					$object->setActiveSheetIndex(0);

					$table_columns = array("Date", "Source No.", "Description", "Increase", "Decrease", "Balance");
					$column = 0;

					foreach($table_columns as $field)
					{
						$object->getActiveSheet()->setCellValueByColumnAndRow($column, 1, $field);
						$column++;
					}

					$excel_row = 2;
					foreach($data['laporan'][0] as $row)
					{
						$object->getActiveSheet()->setCellValueByColumnAndRow(0, $excel_row, $row['tanggal']);
						$object->getActiveSheet()->setCellValueByColumnAndRow(1, $excel_row, $row['no']);
						$object->getActiveSheet()->setCellValueByColumnAndRow(2, $excel_row, $row['keterangan']);
						$object->getActiveSheet()->setCellValueByColumnAndRow(3, $excel_row, number_format($row['debet'],2,',','.'));
						$object->getActiveSheet()->setCellValueByColumnAndRow(4, $excel_row, number_format($row['kredit'],2,',','.'));
						$object->getActiveSheet()->setCellValueByColumnAndRow(5, $excel_row, number_format(($row['debet']-$row['kredit']),2,',','.'));
						$excel_row++;
					}

					$object_writer = PHPExcel_IOFactory::createWriter($object, 'Excel2007');
					header('Content-Type: application/vnd.ms-excel');
					header('Content-Disposition: attachment;filename="bukuPembantuKasKecil.xlsx"');
					$object_writer->save('php://output');
					break;
				
				default:
					# code...
					break;
			}
		}
  }
  
  public function labarugiStandar()
  {
    $data['title']		= 'Profit & Loss (Standar)';
    if ($this->perusahaan) {
			$this->LaporanModel->set('perusahaan', $this->perusahaan);
			$this->LaporanModel->set('tanggalAwal', $this->tanggalAwal);
			$this->LaporanModel->set('tanggalAkhir', $this->tanggalAkhir);
      $data['laporan']		  = $this->LaporanModel->labarugiStandar();
			$data['tanggalAwal']	= $this->tgl_indo($this->tanggalAwal);
			$data['tanggalAkhir']	= $this->tgl_indo($this->tanggalAkhir);
			$data['perusahaan']		= $this->Perusahaan_model->get_by_id($this->perusahaan);
			switch ($this->input->get('jenis')) {
				case 'pdf':
					$this->load->library('pdf');
					$pdf            = $this->pdf;
					$data['css']	  = file_get_contents(FCPATH.'assets/css/print.min.css');
					$data			      = array_merge($data,path_info());
					$html 			    = $this->load->view('laporan/profit&Loss/print', $data, TRUE);
					$pdf->loadHtml($html);
					$pdf->setPaper('A4', 'portrait');
					$pdf->render();
					$time = time();
					$pdf->stream("Profit & Loss (Standar)". $time, array("Attachment" => false));
					break;
				case 'excel':
					$spreadsheet	= \PhpOffice\PhpSpreadsheet\IOFactory::load('assets/Project List.xlsx');
					$worksheet		= $spreadsheet->getActiveSheet();
					$worksheet->getCell('A1')->setValue($data['perusahaan']['nama_perusahaan']);
					$worksheet->getCell('A3')->setValue('From ' . $data['tanggalAwal'] . ' to ' . $data['tanggalAkhir']);
					$no = 0;
					$x	= 6;
					foreach ($data['laporan'] as $key) {
						$worksheet->getCell('A' . $x)->setValue($key['noEvent']);
						$worksheet->getCell('B' . $x)->setValue($key['deskripsi']);
						$worksheet->getCell('C' . $x)->setValue($key['region']);
						$worksheet->getCell('D' . $x)->setValue($key['cabang']);
						$worksheet->getCell('E' . $x)->setValue(number_format($key['totalPendapatan'],2,',','.'));
						$worksheet->getCell('F' . $x)->setValue(number_format($key['totalHPP'],2,',','.'));
						$worksheet->getCell('G' . $x)->setValue($key['kodeEvent']);
						$worksheet->getCell('H' . $x)->setValue($key['kelompokUmur']);
						$worksheet->getCell('I' . $x)->setValue($this->tgl_indo($key['tanggalMulai']));
						$worksheet->getCell('J' . $x)->setValue($this->tgl_indo($key['tanggalSelesai']));
					} 
					$writer = new Xlsx($spreadsheet);
					$filename = 'ProjectList';
					
					header('Content-Type: application/vnd.ms-excel');
					header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
					header('Cache-Control: max-age=0');
			
					$writer->save('php://output');
					break;
				
				default:
					# code...
					break;
			}
		} else {
			$data['content']	= 'laporan/profit&Loss/index';
			$data				      = array_merge($data,path_info());
			$this->parser->parse('template',$data);
		}
  }

  public function labarugiMultiPeriod()
  {
    $data['title']		= 'Profit & Loss (Multi Period)';
    if ($this->perusahaan) {
      $this->LaporanModel->set('tanggalAwal', $this->tanggalAwal);
      $this->LaporanModel->set('tanggalAkhir', $this->tanggalAkhir);
      $this->LaporanModel->set('perusahaan', $this->perusahaan);
      $data['laporan']      = $this->LaporanModel->labarugiMultiPeriod();
			$data['tanggalAwal']	= $this->tgl_indo($this->tanggalAwal);
      $data['tanggalAkhir']	= $this->tgl_indo($this->tanggalAkhir);
      $data['perusahaan']   = $this->Perusahaan_model->get_by_id($this->perusahaan);
			switch ($this->input->get('jenis')) {
				case 'pdf':
					$this->load->library('pdf');
					$pdf            = $this->pdf;
					$data['css']	  = file_get_contents(FCPATH.'assets/css/print.min.css');
					$data			      = array_merge($data,path_info());
					$html           = $this->load->view('laporan/profit&Loss/multiPeriod/print', $data, TRUE);
					$pdf->loadHtml($html);
					$pdf->setPaper('A4', 'landscape');
					$pdf->render();
					$time = time();
					$pdf->stream("Profit & Loss (Multi Period)". $time, array("Attachment" => false));
					break;
				case 'excel':
					$spreadsheet	= \PhpOffice\PhpSpreadsheet\IOFactory::load('assets/Project List.xlsx');
					$worksheet		= $spreadsheet->getActiveSheet();
					$worksheet->getCell('A1')->setValue($data['perusahaan']['nama_perusahaan']);
					$worksheet->getCell('A3')->setValue('From ' . $data['tanggalAwal'] . ' to ' . $data['tanggalAkhir']);
					$no = 0;
					$x	= 6;
					foreach ($data['laporan'] as $key) {
						$worksheet->getCell('A' . $x)->setValue($key['noEvent']);
						$worksheet->getCell('B' . $x)->setValue($key['deskripsi']);
						$worksheet->getCell('C' . $x)->setValue($key['region']);
						$worksheet->getCell('D' . $x)->setValue($key['cabang']);
						$worksheet->getCell('E' . $x)->setValue(number_format($key['totalPendapatan'],2,',','.'));
						$worksheet->getCell('F' . $x)->setValue(number_format($key['totalHPP'],2,',','.'));
						$worksheet->getCell('G' . $x)->setValue($key['kodeEvent']);
						$worksheet->getCell('H' . $x)->setValue($key['kelompokUmur']);
						$worksheet->getCell('I' . $x)->setValue($this->tgl_indo($key['tanggalMulai']));
						$worksheet->getCell('J' . $x)->setValue($this->tgl_indo($key['tanggalSelesai']));
					} 
					$writer = new Xlsx($spreadsheet);
					$filename = 'ProjectList';
					
					header('Content-Type: application/vnd.ms-excel');
					header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
					header('Cache-Control: max-age=0');
			
					$writer->save('php://output');
					break;
				
				default:
					# code...
					break;
			}
		} else {
			$data['content']	= 'laporan/profit&Loss/multiPeriod/index';
			$data				      = array_merge($data,path_info());
			$this->parser->parse('template',$data);
		}
  }

  public function labarugiComparePeriod()
  {
    $data['title']		= 'Profit & Loss (Compare Period)';
    if ($this->perusahaan) {
      $data['laporan']      = $this->LaporanModel->labarugiComparePeriod();
			$data['tanggalAwal']	= $this->tgl_indo($this->input->get('tanggalAwalPeriodeAwal')) . ' - ' . $this->tgl_indo($this->input->get('tanggalAkhirPeriodeAwal'));
			$data['tanggalAkhir']	= $this->tgl_indo($this->input->get('tanggalAwalPeriodeAkhir')) . ' - ' . $this->tgl_indo($this->input->get('tanggalAkhirPeriodeAkhir'));
      $data['perusahaan']   = $this->Perusahaan_model->get_by_id($this->perusahaan);
			switch ($this->input->get('jenis')) {
				case 'pdf':
					$this->load->library('pdf');
					$pdf            = $this->pdf;
					$data['css']	  = file_get_contents(FCPATH.'assets/css/print.min.css');
					$data			      = array_merge($data,path_info());
					$html           = $this->load->view('laporan/profit&Loss/comparePeriod/print', $data, TRUE);
					$pdf->loadHtml($html);
					$pdf->setPaper('A4', 'landscape');
					$pdf->render();
					$time = time();
					$pdf->stream("Profit & Loss (Compare Period)". $time, array("Attachment" => false));
					break;
				case 'excel':
					$spreadsheet	= \PhpOffice\PhpSpreadsheet\IOFactory::load('assets/Project List.xlsx');
					$worksheet		= $spreadsheet->getActiveSheet();
					$worksheet->getCell('A1')->setValue($data['perusahaan']['nama_perusahaan']);
					$worksheet->getCell('A3')->setValue('From ' . $data['tanggalAwal'] . ' to ' . $data['tanggalAkhir']);
					$no = 0;
					$x	= 6;
					foreach ($data['laporan'] as $key) {
						$worksheet->getCell('A' . $x)->setValue($key['noEvent']);
						$worksheet->getCell('B' . $x)->setValue($key['deskripsi']);
						$worksheet->getCell('C' . $x)->setValue($key['region']);
						$worksheet->getCell('D' . $x)->setValue($key['cabang']);
						$worksheet->getCell('E' . $x)->setValue(number_format($key['totalPendapatan'],2,',','.'));
						$worksheet->getCell('F' . $x)->setValue(number_format($key['totalHPP'],2,',','.'));
						$worksheet->getCell('G' . $x)->setValue($key['kodeEvent']);
						$worksheet->getCell('H' . $x)->setValue($key['kelompokUmur']);
						$worksheet->getCell('I' . $x)->setValue($this->tgl_indo($key['tanggalMulai']));
						$worksheet->getCell('J' . $x)->setValue($this->tgl_indo($key['tanggalSelesai']));
					} 
					$writer = new Xlsx($spreadsheet);
					$filename = 'ProjectList';
					
					header('Content-Type: application/vnd.ms-excel');
					header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
					header('Cache-Control: max-age=0');
			
					$writer->save('php://output');
					break;
				
				default:
					# code...
					break;
			}
		} else {
			$data['content']	= 'laporan/profit&Loss/comparePeriod/index';
			$data				      = array_merge($data,path_info());
			$this->parser->parse('template',$data);
		}
  }

  public function salesReceiptsDetail()
  {
    $data['title']  = 'Sales Receipts Detail';
    if ($this->perusahaan) {
      $this->LaporanModel->set('tanggalAwal', $this->tanggalAwal);
      $this->LaporanModel->set('tanggalAkhir', $this->tanggalAkhir);
      $this->LaporanModel->set('perusahaan', $this->perusahaan);
      $data['laporan']      = $this->LaporanModel->salesReceiptsDetail();
			$data['tanggalAwal']	= $this->tgl_indo($this->tanggalAwal);
      $data['tanggalAkhir']	= $this->tgl_indo($this->tanggalAkhir);
      $data['perusahaan']   = $this->Perusahaan_model->get_by_id($this->perusahaan);
			switch ($this->input->get('jenis')) {
				case 'pdf':
					$this->load->library('pdf');
					$pdf            = $this->pdf;
					$data['css']	  = file_get_contents(FCPATH.'assets/css/print.min.css');
					$data			      = array_merge($data,path_info());
					$html           = $this->load->view('laporan/salesReceiptsDetail/print', $data, TRUE);
					$pdf->loadHtml($html);
					$pdf->setPaper('A4', 'portrait');
					$pdf->render();
					$time = time();
					$pdf->stream("Sales Receipts Detail)". $time, array("Attachment" => false));
					break;
				case 'excel':
					$spreadsheet	= \PhpOffice\PhpSpreadsheet\IOFactory::load('assets/Sales Receipts Detail.xlsx');
					$worksheet		= $spreadsheet->getActiveSheet();
					$worksheet->getCell('A1')->setValue($data['perusahaan']['nama_perusahaan']);
					$worksheet->getCell('A3')->setValue('From ' . $data['tanggalAwal'] . ' to ' . $data['tanggalAkhir']);
					$no = 0;
          $x	= 7;
          $styleArray   = ['font' => ['bold'  => true]];
          $styleArray0  = ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]];
          foreach ($laporan as $key) {
            $worksheet->getCell('A' . $x)->setValue('');
            $x++;
            $worksheet->getCell('A' . $x)->setValue($key['formNo']);
            $worksheet->getCell('B' . $x)->setValue($key['recvDate']);
            $worksheet->mergeCells('C' . $x . ':' . 'D' . $x);
            $worksheet->getCell('C' . $x)->setValue($key['recvDate']);
            $worksheet->getCell('E' . $x)->setValue($key['namaCustomer']);
            $worksheet->getStyle('A' . $x . ':' . 'E' . $x)->applyFromArray($styleArray);
            $x++;
            $worksheet->getCell('A' . $x)->setValue($key['invoiceNo']);
            $worksheet->getCell('B' . $x)->setValue($key['invoiceDate']);
            $worksheet->getCell('C' . $x)->setValue($key['total']);
            $worksheet->getCell('D' . $x)->setValue($key['total']);
            $worksheet->getCell('E' . $x)->setValue($key['diskon']);
            $worksheet->getStyle('A' . $x . ':' . 'E' . $x)->applyFromArray($styleArray0);
          }
					$writer   = new Xlsx($spreadsheet);
					$filename = 'SalesReceiptsDetail';
					
					header('Content-Type: application/vnd.ms-excel');
					header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
					header('Cache-Control: max-age=0');
			
					$writer->save('php://output');
					break;
				
				default:
					# code...
					break;
			}
		} else {
			$data['content']	= 'laporan/salesReceiptsDetail/index';
			$data				      = array_merge($data,path_info());
			$this->parser->parse('template',$data);
		}
  }

  public function purchasePaymentDetail()
  {
    $data['title']  = 'Purchase Payment Detail';
    if ($this->perusahaan) {
      $this->LaporanModel->set('tanggalAwal', $this->tanggalAwal);
      $this->LaporanModel->set('tanggalAkhir', $this->tanggalAkhir);
      $this->LaporanModel->set('perusahaan', $this->perusahaan);
      $data['laporan']      = $this->LaporanModel->purchasePaymentDetail();
			$data['tanggalAwal']	= $this->tgl_indo($this->tanggalAwal);
      $data['tanggalAkhir']	= $this->tgl_indo($this->tanggalAkhir);
      $data['perusahaan']   = $this->Perusahaan_model->get_by_id($this->perusahaan);
			switch ($this->input->get('jenis')) {
				case 'pdf':
					$this->load->library('pdf');
					$pdf            = $this->pdf;
					$data['css']	  = file_get_contents(FCPATH.'assets/css/print.min.css');
					$data			      = array_merge($data,path_info());
					$html           = $this->load->view('laporan/purchasePaymentDetail/print', $data, TRUE);
					$pdf->loadHtml($html);
					$pdf->setPaper('A4', 'portrait');
					$pdf->render();
					$time = time();
					$pdf->stream("Purchase Payment Detail)". $time, array("Attachment" => false));
					break;
				case 'excel':
					$spreadsheet	= \PhpOffice\PhpSpreadsheet\IOFactory::load('assets/Project List.xlsx');
					$worksheet		= $spreadsheet->getActiveSheet();
					$worksheet->getCell('A1')->setValue($data['perusahaan']['nama_perusahaan']);
					$worksheet->getCell('A3')->setValue('From ' . $data['tanggalAwal'] . ' to ' . $data['tanggalAkhir']);
					$no = 0;
					$x	= 6;
					foreach ($data['laporan'] as $key) {
						$worksheet->getCell('A' . $x)->setValue($key['noEvent']);
						$worksheet->getCell('B' . $x)->setValue($key['deskripsi']);
						$worksheet->getCell('C' . $x)->setValue($key['region']);
						$worksheet->getCell('D' . $x)->setValue($key['cabang']);
						$worksheet->getCell('E' . $x)->setValue(number_format($key['totalPendapatan'],2,',','.'));
						$worksheet->getCell('F' . $x)->setValue(number_format($key['totalHPP'],2,',','.'));
						$worksheet->getCell('G' . $x)->setValue($key['kodeEvent']);
						$worksheet->getCell('H' . $x)->setValue($key['kelompokUmur']);
						$worksheet->getCell('I' . $x)->setValue($this->tgl_indo($key['tanggalMulai']));
						$worksheet->getCell('J' . $x)->setValue($this->tgl_indo($key['tanggalSelesai']));
					} 
					$writer = new Xlsx($spreadsheet);
					$filename = 'ProjectList';
					
					header('Content-Type: application/vnd.ms-excel');
					header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
					header('Cache-Control: max-age=0');
			
					$writer->save('php://output');
					break;
				
				default:
					# code...
					break;
			}
		} else {
			$data['content']	= 'laporan/purchasePaymentDetail/index';
			$data				      = array_merge($data,path_info());
			$this->parser->parse('template',$data);
		}
  }

	public function print()
	{
		switch ($this->input->get('tombol')) {
			case 'pdf':
				$this->load->library('pdf');
				$pdf			= $this->pdf;
				$data['title']	= 'Laporan Kas Bank';
				$data['css']	= file_get_contents(FCPATH.'assets/css/print.min.css');
				$data			= array_merge($data,path_info());
				$html 			= $this->load->view('laporan/kasBank/print', $data, TRUE);
				$pdf->loadHtml($html);
				$pdf->setPaper('A4', 'portrait');
				$pdf->render();
				$time = time();
				$pdf->stream("Laporan Kas Bank". $time, array("Attachment" => false));
				break;
			
			default:
				# code...
				break;
		}
	}

	public function laporan_utang()
	{
		$data['title']		= 'Laporan Utang Usaha';
		$data['subtitle']	= lang('list');
		$data['content']	= 'laporan/laporan_utang';
		$data['perusahaan'] = $this->db->get('mperusahaan')->result_array();
		$data = array_merge($data,path_info());
		$this->parser->parse('template',$data);
	}

	public function export_laporan_utang()
	{
		$id_perusahaan = $this->input->post('perusahaan');		
		$tgl = $this->input->post('tanggal');
		$pch_tgl = explode('-', date('Y-m-d', strtotime($tgl)));
		$bulan = $this->bulan($pch_tgl[1]);
		$tanggal = $pch_tgl[2].' '.$bulan.' '.$pch_tgl[0];
		if($this->input->post('pdf')){
			$this->load->library('pdf');
			$data['get_per']		= $this->Perusahaan_model->get_by_id($id_perusahaan);

			$data['nama_perusahaan'] = $data['get_per']['nama_perusahaan'];
			$data['title']		= 'Aging Paylable Details';
			$data['title2']		= 'As of '.$tanggal;
			$data['tanggal_skg']	= $tgl;
			$this->db->select("sisatagihan as jml_utang, noFaktur as no_invoice, tanggal as tanggal, tanggaltempo as tempo");
		    $this->db->from("tfaktur");
		    $this->db->where("sisatagihan >",0);
		    $this->db->where("tanggal <=", $tgl);
		    $this->db->where("perusahaanid", $id_perusahaan);
		    $query1 = $this->db->get_compiled_select(); // It resets the query just like a get()

		    $this->db->select("jumlah as jml_utang, noInvoice as no_invoice, tanggal as tanggal, tanggalTempo as tempo");
		    $this->db->from("SaldoAwalHutang");
		    $this->db->where("tanggal <=", $tgl);
		    $this->db->like("tanggal", $pch_tgl[0], "after");
		    $this->db->where("perusahaan", $id_perusahaan);
		    $query2 = $this->db->get_compiled_select(); 

			$data['utang']		= $this->db->query($query1." UNION ".$query2)->result_array();
			$this->load->view('laporan/cetak_laporan_utang', $data);

	    } else {
			include_once APPPATH . 'third_party/PHPExcel.php';
        	$get_per		= $this->Perusahaan_model->get_by_id($id_perusahaan);

			$nama_perusahaan = $get_per['nama_perusahaan'];
			$title	= 'Aging Paylable Details';
			$title2		= 'As of '.$tanggal;
			$tanggal_skg	= $tgl;
			$tanggal_judul = $tanggal;
			$this->db->select("sisatagihan as jml_utang, noFaktur as no_invoice, tanggal as tanggal, tanggaltempo as tempo");
		    $this->db->from("tfaktur");
		    $this->db->where("sisatagihan >",0);
		    $this->db->where("tanggal <=", $tgl);
		    $this->db->where("perusahaanid", $id_perusahaan);
		    $query1 = $this->db->get_compiled_select(); // It resets the query just like a get()

		    $this->db->select("jumlah as jml_utang, noInvoice as no_invoice, tanggal as tanggal, tanggalTempo as tempo");
		    $this->db->from("SaldoAwalHutang");
		    $this->db->where("tanggal <=", $tgl);
		    $this->db->like("tanggal", $pch_tgl[0], "after");
		    $this->db->where("perusahaan", $id_perusahaan);
		    $query2 = $this->db->get_compiled_select(); 

			$utang		= $this->db->query($query1." UNION ".$query2)->result_array();

	        $excel = new PHPExcel();

	        $excel1 = new PHPExcel_Worksheet($excel, 'Paylable');

			// Attach the "My Data" worksheet as the first worksheet in the PHPExcel object
			$excel->addSheet($excel1, 0);

	        $excel->getProperties()
	                ->setCreator('Ecorporate')
	                ->setLastModifiedBy('Ecorporate')
	                ->setTitle('Data Paylable Payment')
	                ->setSubject('Paylable Payment')
	                ->setDescription('Paylable Payment '.$tanggal)
	                ->setKeyWords('Paylable Payment');

	        $style_col = [
	        	'fill' => [
	                'type' => PHPExcel_Style_Fill::FILL_SOLID,
	                'color' => ['rgb' => 'd9e1f2']
	            ],
	            'font' => ['bold' => true],
	            'alignment' => [
	                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	            ],
	            'borders' => [
	                'top' => ['style' => PHPExcel_Style_Border::BORDER_THIN],
	                'bottom' => ['style' => PHPExcel_Style_Border::BORDER_THIN],
	                'right' => ['style' => PHPExcel_Style_Border::BORDER_THIN],
	                'left' => ['style' => PHPExcel_Style_Border::BORDER_THIN],
	            ]
	        ];

	        $style_row = [
	            'alignment' => [
	                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	            ],
	            'borders' => [
	                'right' => ['style' => PHPExcel_Style_Border::BORDER_THIN],
	                'left' => ['style' => PHPExcel_Style_Border::BORDER_THIN],
	            ]
	        ];

	        $style_row_bawah = [
	            'alignment' => [
	                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	            ],
	            'borders' => [
	            	'top' => ['style' => PHPExcel_Style_Border::BORDER_THIN],
	                'right' => ['style' => PHPExcel_Style_Border::BORDER_THIN],
	                'left' => ['style' => PHPExcel_Style_Border::BORDER_THIN],
	                'bottom' => ['style' => PHPExcel_Style_Border::BORDER_THIN],
	            ]
	        ];

	        $style_row_full = [
	            'alignment' => [
	                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	            ],
	            'borders' => [
	                'top' => ['style' => PHPExcel_Style_Border::BORDER_THIN],
	                'bottom' => ['style' => PHPExcel_Style_Border::BORDER_THIN],
	                'right' => ['style' => PHPExcel_Style_Border::BORDER_THIN],
	                'left' => ['style' => PHPExcel_Style_Border::BORDER_THIN],
	            ]
	        ];

	        $excel->setActiveSheetIndex(0)->setCellValue('B1', $nama_perusahaan);
	        $excel->getActiveSheet()->mergeCells('B1:J1');
	        $excel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);
	        $excel->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

	        $excel->setActiveSheetIndex(0)->setCellValue('B2', $title);
	        $excel->getActiveSheet()->mergeCells('B2:J2');
	        $excel->getActiveSheet()->getStyle('B2')->getFont()->setBold(true);
	        $excel->getActiveSheet()->getStyle('B2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

	        $excel->setActiveSheetIndex(0)->setCellValue('B3', $title2);
	        $excel->getActiveSheet()->mergeCells('B3:J3');
	        $excel->getActiveSheet()->getStyle('B3')->getFont()->setBold(true);
	        $excel->getActiveSheet()->getStyle('B3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

	        $excel->setActiveSheetIndex(0)->setCellValue('B5', 'Invoice No.');
	        $excel->setActiveSheetIndex(0)->setCellValue('C5', 'Invoice Date');
	        $excel->setActiveSheetIndex(0)->setCellValue('D5', 'Total Primer Curr');
	        $excel->setActiveSheetIndex(0)->setCellValue('E5', 'Not Yet');
	        $excel->setActiveSheetIndex(0)->setCellValue('F5', '1-30');
	        $excel->setActiveSheetIndex(0)->setCellValue('G5', '31-60');
	        $excel->setActiveSheetIndex(0)->setCellValue('H5', '61-90');
	        $excel->setActiveSheetIndex(0)->setCellValue('I5', '91-120');
	        $excel->setActiveSheetIndex(0)->setCellValue('J5', '>120');
	        
	        $excel->getActiveSheet()->getStyle('B5')->applyFromArray($style_col);
	        $excel->getActiveSheet()->getStyle('C5')->applyFromArray($style_col);
	        $excel->getActiveSheet()->getStyle('D5')->applyFromArray($style_col);
	        $excel->getActiveSheet()->getStyle('E5')->applyFromArray($style_col);
	        $excel->getActiveSheet()->getStyle('F5')->applyFromArray($style_col);
	        $excel->getActiveSheet()->getStyle('G5')->applyFromArray($style_col);
	        $excel->getActiveSheet()->getStyle('H5')->applyFromArray($style_col);
	        $excel->getActiveSheet()->getStyle('I5')->applyFromArray($style_col);
	        $excel->getActiveSheet()->getStyle('J5')->applyFromArray($style_col);

	        $numrow = 6;
	        $no = 1; 
            $total=0;
            $not_yet=0;
            $_130=0;
            $_3160=0;
            $_6190=0;
            $_91120=0;
            $_120=0;

            foreach($utang as $u){
                $total += $u['jml_utang'];
                $tanggal = new DateTime($tanggal_skg);
                $tempo = new DateTime($u['tempo']);
                $h = $tempo->diff($tanggal)->days + 1;
                if($h == 0 ){
                    $not_yet += $u['jml_utang'];
                } else {
                    $not_yet += 0;
                } 
                
                if($h > 30 && $h <= 60){
                    $_130 += $u['jml_utang'];
                } else {
                    $_130 += 0;
                } 

                if($h > 30 && $h <= 60){
                    $_3160 += $u['jml_utang'];
                } else {
                    $_3160 += 0;
                } 

                if($h > 60 && $h <= 90){
                    $_6190 += $u['jml_utang'];
                } else {
                    $_6190 += 0;
                } 

                if($h > 90 && $h <= 120){
                    $_91120 += $u['jml_utang'];
                } else {
                    $_91120 += 0;
                } 

                if($h > 120){
                    $_120 += $u['jml_utang'];
                } else {
                    $_120 += 0;
                }

	            $excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, $u['no_invoice']);
	            $excel->getActiveSheet()->getStyle('B'.$numrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	            $excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, date('d F Y', strtotime($u['tanggal'])));
	            $excel->getActiveSheet()->getStyle('C'.$numrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	            $excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, number_format($u['jml_utang'], 0, ',', '.'));
	            $excel->getActiveSheet()->getStyle('D'.$numrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	            $excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $h == 0 ? number_format($u['jml_utang'], 0, ',', '.') : 0);
	            $excel->getActiveSheet()->getStyle('E'.$numrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	            $excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow, $h > 0 && $h <= 30 ? number_format($u['jml_utang'], 0, ',', '.') : 0);
	            $excel->getActiveSheet()->getStyle('F'.$numrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	            $excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow, $h > 30 && $h <= 60 ? number_format($u['jml_utang'], 0, ',', '.') : 0);
	            $excel->getActiveSheet()->getStyle('G'.$numrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	            $excel->setActiveSheetIndex(0)->setCellValue('H'.$numrow, $h > 60 && $h <= 90 ? number_format($u['jml_utang'], 0, ',', '.') : 0);
	            $excel->getActiveSheet()->getStyle('H'.$numrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	            $excel->setActiveSheetIndex(0)->setCellValue('I'.$numrow, $h > 90 && $h <= 120 ? number_format($u['jml_utang'], 0, ',', '.') : 0);
	            $excel->getActiveSheet()->getStyle('I'.$numrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	            $excel->setActiveSheetIndex(0)->setCellValue('J'.$numrow, $h > 120 ? number_format($u['jml_utang'], 0, ',', '.') : 0);
	            $excel->getActiveSheet()->getStyle('J'.$numrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

	            $excel->getActiveSheet()->getStyle('B'.$numrow)->applyFromArray($style_row);
	            $excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_row);
	            $excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_row);
	            $excel->getActiveSheet()->getStyle('E'.$numrow)->applyFromArray($style_row);
	            $excel->getActiveSheet()->getStyle('F'.$numrow)->applyFromArray($style_row);
	            $excel->getActiveSheet()->getStyle('G'.$numrow)->applyFromArray($style_row);
	            $excel->getActiveSheet()->getStyle('H'.$numrow)->applyFromArray($style_row);
	            $excel->getActiveSheet()->getStyle('I'.$numrow)->applyFromArray($style_row);
	            $excel->getActiveSheet()->getStyle('J'.$numrow)->applyFromArray($style_row);
	            
	            $numrow=$numrow+1;
	            
	        }
	        
	        

	        $numrow_terbilang = $numrow;
	        
	        $excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow_terbilang,'');
            $excel->getActiveSheet()->getStyle('B'.$numrow_terbilang)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow_terbilang,'');
            $excel->getActiveSheet()->getStyle('C'.$numrow_terbilang)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow_terbilang, number_format($total, 0, ',', '.'));
            $excel->getActiveSheet()->getStyle('D'.$numrow_terbilang)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow_terbilang, number_format($not_yet, 0, ',', '.') );
            $excel->getActiveSheet()->getStyle('E'.$numrow_terbilang)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow_terbilang, number_format($_130, 0, ',', '.') );
            $excel->getActiveSheet()->getStyle('F'.$numrow_terbilang)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow_terbilang, number_format($_3160, 0, ',', '.') );
            $excel->getActiveSheet()->getStyle('G'.$numrow_terbilang)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->setActiveSheetIndex(0)->setCellValue('H'.$numrow_terbilang, number_format($_6190, 0, ',', '.') );
            $excel->getActiveSheet()->getStyle('H'.$numrow_terbilang)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->setActiveSheetIndex(0)->setCellValue('I'.$numrow_terbilang, number_format($_91120, 0, ',', '.') );
            $excel->getActiveSheet()->getStyle('I'.$numrow_terbilang)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->setActiveSheetIndex(0)->setCellValue('J'.$numrow_terbilang, number_format($_120, 0, ',', '.') );
            $excel->getActiveSheet()->getStyle('J'.$numrow_terbilang)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            $excel->getActiveSheet()->getStyle('B'.$numrow_terbilang)->applyFromArray($style_row_bawah);
            $excel->getActiveSheet()->getStyle('C'.$numrow_terbilang)->applyFromArray($style_row_bawah);
            $excel->getActiveSheet()->getStyle('D'.$numrow_terbilang)->applyFromArray($style_row_bawah);
            $excel->getActiveSheet()->getStyle('E'.$numrow_terbilang)->applyFromArray($style_row_bawah);
            $excel->getActiveSheet()->getStyle('F'.$numrow_terbilang)->applyFromArray($style_row_bawah);
            $excel->getActiveSheet()->getStyle('G'.$numrow_terbilang)->applyFromArray($style_row_bawah);
            $excel->getActiveSheet()->getStyle('H'.$numrow_terbilang)->applyFromArray($style_row_bawah);
            $excel->getActiveSheet()->getStyle('I'.$numrow_terbilang)->applyFromArray($style_row_bawah);
            $excel->getActiveSheet()->getStyle('J'.$numrow_terbilang)->applyFromArray($style_row_bawah);
			
	        $excel->getActiveSheet()->getColumnDimension('A')->setWidth(0.5);
	        $excel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
	        $excel->getActiveSheet()->getColumnDimension('C')->setWidth(18);
	        $excel->getActiveSheet()->getColumnDimension('D')->setWidth(16);
	        $excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
	        $excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
	        $excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
	        $excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
	        $excel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
	        $excel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
	        $excel->getActiveSheet()->getRowDimension('5')->setRowHeight(26);
	        $excel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(-1);

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="Aging Paylable Payment ' . $tanggal_judul. '.xlsx"'); // Set nama file excel nya
            header('Cache-Control: max-age=0');

            $write = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
            $write->save('php://output');
		}
	}

	public function laporan_piutang()
	{
		$data['title']		= 'Laporan Piutang Usaha';
		$data['subtitle']	= lang('list');
		$data['content']	= 'laporan/laporan_piutang';
		$data['perusahaan'] = $this->db->get('mperusahaan')->result_array();
		$data = array_merge($data,path_info());
		$this->parser->parse('template',$data);
	}

	public function export_laporan_piutang()
	{
		$id_perusahaan = $this->input->post('perusahaan');		
		$tgl = $this->input->post('tanggal');
		$pch_tgl = explode('-', date('Y-m-d', strtotime($tgl)));
		$bulan = $this->bulan($pch_tgl[1]);
		$tanggal = $pch_tgl[2].' '.$bulan.' '.$pch_tgl[0];
		if($this->input->post('pdf')){
			$this->load->library('pdf');
			$data['get_per']		= $this->Perusahaan_model->get_by_id($id_perusahaan);

			$data['nama_perusahaan'] = $data['get_per']['nama_perusahaan'];
			$data['title']		= 'Aging Receivable Details';
			$data['title2']		= 'As of '.$tanggal;
			$data['tanggal_skg']	= $tgl;
			$this->db->select("sisatagihan as jml_utang, noSSP as no_invoice, tanggal as tanggal, tanggaltempo as tempo");
      $this->db->from("tfakturpenjualan");
      $this->db->where("sisatagihan >",0);
      $this->db->where("tanggal <=", $tgl);
      $this->db->where("idperusahaan", $id_perusahaan);
      $query1 = $this->db->get_compiled_select(); // It resets the query just like a get()

      $this->db->select("jumlah as jml_utang, noInvoice as no_invoice, tanggal as tanggal, tanggalTempo as tempo");
      $this->db->from("SaldoAwalPiutang");
      $this->db->where("tanggal <=", $tgl);
      $this->db->like("tanggal", $pch_tgl[0], "after");
      $this->db->where("perusahaan", $id_perusahaan);
      $query2 = $this->db->get_compiled_select(); 

			$data['piutang']		= $this->db->query($query1." UNION ".$query2)->result_array();
			$this->load->view('laporan/cetak_laporan_piutang', $data);

	    } else {
			include_once APPPATH . 'third_party/PHPExcel.php';
        	$get_per		= $this->Perusahaan_model->get_by_id($id_perusahaan);

			$nama_perusahaan = $get_per['nama_perusahaan'];
			$title	= 'Aging Receivable Details';
			$title2		= 'As of '.$tanggal;
			$tanggal_skg	= $tgl;
			$tanggal_judul = $tanggal;
			$this->db->select("sisatagihan as jml_utang, noSSP as no_invoice, tanggal as tanggal, tanggaltempo as tempo");
		    $this->db->from("tfakturpenjualan");
		    $this->db->where("sisatagihan >",0);
		    $this->db->where("tanggal <=", $tgl);
		    $this->db->where("idperusahaan", $id_perusahaan);
		    $query1 = $this->db->get_compiled_select(); // It resets the query just like a get()

		    $this->db->select("jumlah as jml_utang, noInvoice as no_invoice, tanggal as tanggal, tanggalTempo as tempo");
		    $this->db->from("SaldoAwalPiutang");
		    $this->db->where("tanggal <=", $tgl);
		    $this->db->like("tanggal", $pch_tgl[0], "after");
		    $this->db->where("perusahaan", $id_perusahaan);
		    $query2 = $this->db->get_compiled_select(); 

			$utang		= $this->db->query($query1." UNION ".$query2)->result_array();

	        $excel = new PHPExcel();

	        $excel1 = new PHPExcel_Worksheet($excel, 'Receivable');

			// Attach the "My Data" worksheet as the first worksheet in the PHPExcel object
			$excel->addSheet($excel1, 0);

	        $excel->getProperties()
	                ->setCreator('Ecorporate')
	                ->setLastModifiedBy('Ecorporate')
	                ->setTitle('Data Receivable Payment')
	                ->setSubject('Receivable Payment')
	                ->setDescription('Receivable Payment '.$tanggal)
	                ->setKeyWords('Receivable Payment');

	        $style_col = [
	        	'fill' => [
	                'type' => PHPExcel_Style_Fill::FILL_SOLID,
	                'color' => ['rgb' => 'd9e1f2']
	            ],
	            'font' => ['bold' => true],
	            'alignment' => [
	                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	            ],
	            'borders' => [
	                'top' => ['style' => PHPExcel_Style_Border::BORDER_THIN],
	                'bottom' => ['style' => PHPExcel_Style_Border::BORDER_THIN],
	                'right' => ['style' => PHPExcel_Style_Border::BORDER_THIN],
	                'left' => ['style' => PHPExcel_Style_Border::BORDER_THIN],
	            ]
	        ];

	        $style_row = [
	            'alignment' => [
	                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	            ],
	            'borders' => [
	                'right' => ['style' => PHPExcel_Style_Border::BORDER_THIN],
	                'left' => ['style' => PHPExcel_Style_Border::BORDER_THIN],
	            ]
	        ];

	        $style_row_bawah = [
	            'alignment' => [
	                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	            ],
	            'borders' => [
	            	'top' => ['style' => PHPExcel_Style_Border::BORDER_THIN],
	                'right' => ['style' => PHPExcel_Style_Border::BORDER_THIN],
	                'left' => ['style' => PHPExcel_Style_Border::BORDER_THIN],
	                'bottom' => ['style' => PHPExcel_Style_Border::BORDER_THIN],
	            ]
	        ];

	        $style_row_full = [
	            'alignment' => [
	                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	            ],
	            'borders' => [
	                'top' => ['style' => PHPExcel_Style_Border::BORDER_THIN],
	                'bottom' => ['style' => PHPExcel_Style_Border::BORDER_THIN],
	                'right' => ['style' => PHPExcel_Style_Border::BORDER_THIN],
	                'left' => ['style' => PHPExcel_Style_Border::BORDER_THIN],
	            ]
	        ];

	        $excel->setActiveSheetIndex(0)->setCellValue('B1', $nama_perusahaan);
	        $excel->getActiveSheet()->mergeCells('B1:J1');
	        $excel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);
	        $excel->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

	        $excel->setActiveSheetIndex(0)->setCellValue('B2', $title);
	        $excel->getActiveSheet()->mergeCells('B2:J2');
	        $excel->getActiveSheet()->getStyle('B2')->getFont()->setBold(true);
	        $excel->getActiveSheet()->getStyle('B2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

	        $excel->setActiveSheetIndex(0)->setCellValue('B3', $title2);
	        $excel->getActiveSheet()->mergeCells('B3:J3');
	        $excel->getActiveSheet()->getStyle('B3')->getFont()->setBold(true);
	        $excel->getActiveSheet()->getStyle('B3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

	        $excel->setActiveSheetIndex(0)->setCellValue('B5', 'Invoice No.');
	        $excel->setActiveSheetIndex(0)->setCellValue('C5', 'Invoice Date');
	        $excel->setActiveSheetIndex(0)->setCellValue('D5', 'Total Primer Curr');
	        $excel->setActiveSheetIndex(0)->setCellValue('E5', 'Not Yet');
	        $excel->setActiveSheetIndex(0)->setCellValue('F5', '1-30');
	        $excel->setActiveSheetIndex(0)->setCellValue('G5', '31-60');
	        $excel->setActiveSheetIndex(0)->setCellValue('H5', '61-90');
	        $excel->setActiveSheetIndex(0)->setCellValue('I5', '91-120');
	        $excel->setActiveSheetIndex(0)->setCellValue('J5', '>120');
	        
	        $excel->getActiveSheet()->getStyle('B5')->applyFromArray($style_col);
	        $excel->getActiveSheet()->getStyle('C5')->applyFromArray($style_col);
	        $excel->getActiveSheet()->getStyle('D5')->applyFromArray($style_col);
	        $excel->getActiveSheet()->getStyle('E5')->applyFromArray($style_col);
	        $excel->getActiveSheet()->getStyle('F5')->applyFromArray($style_col);
	        $excel->getActiveSheet()->getStyle('G5')->applyFromArray($style_col);
	        $excel->getActiveSheet()->getStyle('H5')->applyFromArray($style_col);
	        $excel->getActiveSheet()->getStyle('I5')->applyFromArray($style_col);
	        $excel->getActiveSheet()->getStyle('J5')->applyFromArray($style_col);

	        $numrow = 6;
	        $no = 1; 
            $total=0;
            $not_yet=0;
            $_130=0;
            $_3160=0;
            $_6190=0;
            $_91120=0;
            $_120=0;

            foreach($utang as $u){
                $total += $u['jml_utang'];
                $tanggal = new DateTime($tanggal_skg);
                $tempo = new DateTime($u['tempo']);
                $h = $tempo->diff($tanggal)->days + 1;
                if($h == 0 ){
                    $not_yet += $u['jml_utang'];
                } else {
                    $not_yet += 0;
                } 
                
                if($h > 30 && $h <= 60){
                    $_130 += $u['jml_utang'];
                } else {
                    $_130 += 0;
                } 

                if($h > 30 && $h <= 60){
                    $_3160 += $u['jml_utang'];
                } else {
                    $_3160 += 0;
                } 

                if($h > 60 && $h <= 90){
                    $_6190 += $u['jml_utang'];
                } else {
                    $_6190 += 0;
                } 

                if($h > 90 && $h <= 120){
                    $_91120 += $u['jml_utang'];
                } else {
                    $_91120 += 0;
                } 

                if($h > 120){
                    $_120 += $u['jml_utang'];
                } else {
                    $_120 += 0;
                }

	            $excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, $u['no_invoice']);
	            $excel->getActiveSheet()->getStyle('B'.$numrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	            $excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, date('d F Y', strtotime($u['tanggal'])));
	            $excel->getActiveSheet()->getStyle('C'.$numrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	            $excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, number_format($u['jml_utang'], 0, ',', '.'));
	            $excel->getActiveSheet()->getStyle('D'.$numrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	            $excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow, $h == 0 ? number_format($u['jml_utang'], 0, ',', '.') : 0);
	            $excel->getActiveSheet()->getStyle('E'.$numrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	            $excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow, $h > 0 && $h <= 30 ? number_format($u['jml_utang'], 0, ',', '.') : 0);
	            $excel->getActiveSheet()->getStyle('F'.$numrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	            $excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow, $h > 30 && $h <= 60 ? number_format($u['jml_utang'], 0, ',', '.') : 0);
	            $excel->getActiveSheet()->getStyle('G'.$numrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	            $excel->setActiveSheetIndex(0)->setCellValue('H'.$numrow, $h > 60 && $h <= 90 ? number_format($u['jml_utang'], 0, ',', '.') : 0);
	            $excel->getActiveSheet()->getStyle('H'.$numrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	            $excel->setActiveSheetIndex(0)->setCellValue('I'.$numrow, $h > 90 && $h <= 120 ? number_format($u['jml_utang'], 0, ',', '.') : 0);
	            $excel->getActiveSheet()->getStyle('I'.$numrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	            $excel->setActiveSheetIndex(0)->setCellValue('J'.$numrow, $h > 120 ? number_format($u['jml_utang'], 0, ',', '.') : 0);
	            $excel->getActiveSheet()->getStyle('J'.$numrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

	            $excel->getActiveSheet()->getStyle('B'.$numrow)->applyFromArray($style_row);
	            $excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_row);
	            $excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_row);
	            $excel->getActiveSheet()->getStyle('E'.$numrow)->applyFromArray($style_row);
	            $excel->getActiveSheet()->getStyle('F'.$numrow)->applyFromArray($style_row);
	            $excel->getActiveSheet()->getStyle('G'.$numrow)->applyFromArray($style_row);
	            $excel->getActiveSheet()->getStyle('H'.$numrow)->applyFromArray($style_row);
	            $excel->getActiveSheet()->getStyle('I'.$numrow)->applyFromArray($style_row);
	            $excel->getActiveSheet()->getStyle('J'.$numrow)->applyFromArray($style_row);
	            
	            $numrow=$numrow+1;
	            
	        }
	        
	        

	        $numrow_terbilang = $numrow;
	        
	        $excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow_terbilang,'');
            $excel->getActiveSheet()->getStyle('B'.$numrow_terbilang)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow_terbilang,'');
            $excel->getActiveSheet()->getStyle('C'.$numrow_terbilang)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow_terbilang, number_format($total, 0, ',', '.'));
            $excel->getActiveSheet()->getStyle('D'.$numrow_terbilang)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->setActiveSheetIndex(0)->setCellValue('E'.$numrow_terbilang, number_format($not_yet, 0, ',', '.') );
            $excel->getActiveSheet()->getStyle('E'.$numrow_terbilang)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->setActiveSheetIndex(0)->setCellValue('F'.$numrow_terbilang, number_format($_130, 0, ',', '.') );
            $excel->getActiveSheet()->getStyle('F'.$numrow_terbilang)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->setActiveSheetIndex(0)->setCellValue('G'.$numrow_terbilang, number_format($_3160, 0, ',', '.') );
            $excel->getActiveSheet()->getStyle('G'.$numrow_terbilang)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->setActiveSheetIndex(0)->setCellValue('H'.$numrow_terbilang, number_format($_6190, 0, ',', '.') );
            $excel->getActiveSheet()->getStyle('H'.$numrow_terbilang)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->setActiveSheetIndex(0)->setCellValue('I'.$numrow_terbilang, number_format($_91120, 0, ',', '.') );
            $excel->getActiveSheet()->getStyle('I'.$numrow_terbilang)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->setActiveSheetIndex(0)->setCellValue('J'.$numrow_terbilang, number_format($_120, 0, ',', '.') );
            $excel->getActiveSheet()->getStyle('J'.$numrow_terbilang)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            $excel->getActiveSheet()->getStyle('B'.$numrow_terbilang)->applyFromArray($style_row_bawah);
            $excel->getActiveSheet()->getStyle('C'.$numrow_terbilang)->applyFromArray($style_row_bawah);
            $excel->getActiveSheet()->getStyle('D'.$numrow_terbilang)->applyFromArray($style_row_bawah);
            $excel->getActiveSheet()->getStyle('E'.$numrow_terbilang)->applyFromArray($style_row_bawah);
            $excel->getActiveSheet()->getStyle('F'.$numrow_terbilang)->applyFromArray($style_row_bawah);
            $excel->getActiveSheet()->getStyle('G'.$numrow_terbilang)->applyFromArray($style_row_bawah);
            $excel->getActiveSheet()->getStyle('H'.$numrow_terbilang)->applyFromArray($style_row_bawah);
            $excel->getActiveSheet()->getStyle('I'.$numrow_terbilang)->applyFromArray($style_row_bawah);
            $excel->getActiveSheet()->getStyle('J'.$numrow_terbilang)->applyFromArray($style_row_bawah);
			
	        $excel->getActiveSheet()->getColumnDimension('A')->setWidth(0.5);
	        $excel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
	        $excel->getActiveSheet()->getColumnDimension('C')->setWidth(18);
	        $excel->getActiveSheet()->getColumnDimension('D')->setWidth(16);
	        $excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
	        $excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
	        $excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
	        $excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
	        $excel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
	        $excel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
	        $excel->getActiveSheet()->getRowDimension('5')->setRowHeight(26);
	        $excel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(-1);

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="Aging Receivable Payment ' . $tanggal_judul. '.xlsx"'); // Set nama file excel nya
            header('Cache-Control: max-age=0');

            $write = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
            $write->save('php://output');
		}
	}

	public function laporan_neraca()
	{
		$data['title']		  = 'Laporan Neraca';
		$data['subtitle']	  = lang('list');
		$data['content']	  = 'laporan/laporan_neraca';
		$data['perusahaan'] = $this->db->get('mperusahaan')->result_array();
		$data               = array_merge($data,path_info());
		$this->parser->parse('template',$data);
	}

	public function export_laporan_neraca()
	{
		$this->Neraca_model->set('perusahaan', $this->perusahaan);
		$this->Neraca_model->set('tanggalAwal', $this->tanggalAwal);
		$this->Neraca_model->set('tanggalAkhir', $this->tanggalAkhir);
		$pch_tgl_a = explode('-', date('Y-m-d', strtotime($this->tanggalAwal)));
		$pch_tgl_b = explode('-', date('Y-m-d', strtotime($this->tanggalAkhir)));
		$bulan_a = $this->bulan($pch_tgl_a[1]);
		$tanggal_a = $pch_tgl_a[2].' '.$bulan_a.' '.$pch_tgl_a[0];
		$bulan_b = $this->bulan($pch_tgl_b[1]);
		$tanggal_b = $pch_tgl_a[2].' '.$bulan_b.' '.$pch_tgl_b[0];
		if($this->input->get('pdf')){
      $this->load->library('pdf');
			$pdf                      = $this->pdf;
			$data['get_per']		      = $this->Perusahaan_model->get_by_id($this->perusahaan);
			$data['nama_perusahaan']  = $data['get_per']['nama_perusahaan'];
			$data['title']		        = 'Balance Sheet(Compare Month)';
			$data['title2']		        = 'Period '.$tanggal_a.' to '.$tanggal_b;
			$data['getasetlancar']		= $this->Neraca_model->getasetlancar();
			$data['getliabilitas']  = $this->Neraca_model->getliabilitas();
			$data['gettotallabarugi'] = $this->Neraca_model->gettotallabarugi();
			$data['ekuitas']          = $this->Neraca_model->getEkuitas();
			$tanggalAwal_             = date('Y-m-d', strtotime('-1 month', strtotime($this->tanggalAwal))); 
			$data['periode_ini']      = date('F Y', strtotime($this->tanggalAkhir));
			$data['periode_lalu']     = date('F Y', strtotime($tanggalAwal_));
      $data['css']              = file_get_contents(FCPATH.'assets/css/print.min.css');
      $data                     = array_merge($data,path_info());
      $html 			              = $this->load->view('laporan/cetak_laporan_neraca', $data, TRUE);
      $pdf->loadHtml($html);
      $pdf->setPaper('A4', 'portrait');
      $pdf->render();
      $time = time();
      $pdf->stream("Balance Sheet (Compare Month)". $time, array("Attachment" => false));
	    } else {
			include_once APPPATH . 'third_party/PHPExcel.php';
        	$get_per		= $this->Perusahaan_model->get_by_id($this->perusahaan);

			$nama_perusahaan = $get_per['nama_perusahaan'];
			$title		= 'Balance Sheet(Compare Month)';
			$title2		= 'Period '.$tanggal_a.' to '.$tanggal_b;
			$getasetlancar		= $this->Neraca_model->getasetlancar();
			// $data['getasettetap'] = $this->model->getasettetap($data['tanggal']);
			$getliabilitas		= $this->Neraca_model->getliabilitas();
			// $data['getmodal'] = $this->model->getmodal($data['tanggal']);
			$gettotallabarugi	= $this->Neraca_model->gettotallabarugi();
			$ekuitas			= $this->Neraca_model->getEkuitas();
			$tanggalAwal_ = date('Y-m-d', strtotime('-1 month', strtotime($this->tanggalAwal))); 
			$periode_ini = date('F Y', strtotime($this->tanggalAkhir));
			$periode_lalu= date('F Y', strtotime($tanggalAwal_));

	        $excel = new PHPExcel();

	        $excel1 = new PHPExcel_Worksheet($excel, 'Neraca');

			// Attach the "My Data" worksheet as the first worksheet in the PHPExcel object
			$excel->addSheet($excel1, 0);

	        $excel->getProperties()
	                ->setCreator('Ecorporate')
	                ->setLastModifiedBy('Ecorporate')
	                ->setTitle('Data Receivable Payment')
	                ->setSubject('Receivable Payment')
	                ->setDescription('Receivable Payment ')
	                ->setKeyWords('Receivable Payment');

	        $style_col = [
	        	'fill' => [
	                'type' => PHPExcel_Style_Fill::FILL_SOLID,
	                'color' => ['rgb' => 'd9e1f2']
	            ],
	            'font' => ['bold' => true],
	            'alignment' => [
	                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	            ],
	            'borders' => [
	                'top' => ['style' => PHPExcel_Style_Border::BORDER_THIN],
	                'bottom' => ['style' => PHPExcel_Style_Border::BORDER_THIN],
	                'right' => ['style' => PHPExcel_Style_Border::BORDER_THIN],
	                'left' => ['style' => PHPExcel_Style_Border::BORDER_THIN],
	            ]
	        ];

	        $style_row = [
	            'alignment' => [
	                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	            ],
	            'borders' => [
	                'right' => ['style' => PHPExcel_Style_Border::BORDER_THIN],
	                'left' => ['style' => PHPExcel_Style_Border::BORDER_THIN],
	            ]
	        ];

	        $style_row_bawah = [
	            'alignment' => [
	                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	            ],
	            'borders' => [
	            	'top' => ['style' => PHPExcel_Style_Border::BORDER_THIN],
	                'right' => ['style' => PHPExcel_Style_Border::BORDER_THIN],
	                'left' => ['style' => PHPExcel_Style_Border::BORDER_THIN],
	                'bottom' => ['style' => PHPExcel_Style_Border::BORDER_THIN],
	            ]
	        ];

	        $style_row_full = [
	            'alignment' => [
	                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	            ],
	            'borders' => [
	                'top' => ['style' => PHPExcel_Style_Border::BORDER_THIN],
	                'bottom' => ['style' => PHPExcel_Style_Border::BORDER_THIN],
	                'right' => ['style' => PHPExcel_Style_Border::BORDER_THIN],
	                'left' => ['style' => PHPExcel_Style_Border::BORDER_THIN],
	            ]
	        ];

	        $excel->setActiveSheetIndex(0)->setCellValue('B1', $nama_perusahaan);
	        $excel->getActiveSheet()->mergeCells('B1:D1');
	        $excel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);
	        $excel->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

	        $excel->setActiveSheetIndex(0)->setCellValue('B2', $title);
	        $excel->getActiveSheet()->mergeCells('B2:D2');
	        $excel->getActiveSheet()->getStyle('B2')->getFont()->setBold(true);
	        $excel->getActiveSheet()->getStyle('B2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

	        $excel->setActiveSheetIndex(0)->setCellValue('B3', $title2);
	        $excel->getActiveSheet()->mergeCells('B3:D3');
	        $excel->getActiveSheet()->getStyle('B3')->getFont()->setBold(true);
	        $excel->getActiveSheet()->getStyle('B3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

	        $excel->setActiveSheetIndex(0)->setCellValue('B5', 'ASET');
	        $excel->setActiveSheetIndex(0)->setCellValue('C5', $periode_ini);
	        $excel->setActiveSheetIndex(0)->setCellValue('D5', $periode_lalu);
	        
	        $excel->getActiveSheet()->getStyle('B5')->applyFromArray($style_col);
	        $excel->getActiveSheet()->getStyle('C5')->applyFromArray($style_col);
	        $excel->getActiveSheet()->getStyle('D5')->applyFromArray($style_col);

	        $excel->setActiveSheetIndex(0)->setCellValue('B6', 'ASET LANCAR');
	        $excel->getActiveSheet()->mergeCells('B6:D6');
	        $excel->getActiveSheet()->getStyle('B6')->getFont()->setBold(true);
	        $excel->getActiveSheet()->getStyle('B6')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

	        $excel->getActiveSheet()->getStyle('B6')->applyFromArray($style_row_bawah);
	        $excel->getActiveSheet()->getStyle('C6')->applyFromArray($style_row_bawah);
	        $excel->getActiveSheet()->getStyle('D6')->applyFromArray($style_row_bawah);

	        $numrow = 7;

	        $totalAsetLancarPeriodeKini = 0;
            $totalAsetLancar            = 0;
            if ($getasetlancar) {
                foreach ($getasetlancar as $key) { 
                	$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, $key['namaakun']);
		            $excel->getActiveSheet()->getStyle('B'.$numrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
		            $excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, number_format($key['debetPeriodeKini'],2,',','.'));
		            $excel->getActiveSheet()->getStyle('C'.$numrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
		            $excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, number_format($key['debet'], 2, ',', '.'));
		            $excel->getActiveSheet()->getStyle('D'.$numrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

		            $excel->getActiveSheet()->getStyle('B'.$numrow)->applyFromArray($style_row);
		            $excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_row);
		            $excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_row);
        
                 
                    $totalAsetLancarPeriodeKini += $key['debetPeriodeKini'];
                    $totalAsetLancar            += $key['debet'];

                    $numrow=$numrow+1;
                }
            } 
            
	        
	        $excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow,'TOTAL ASET LANCAR');
            $excel->getActiveSheet()->getStyle('B'.$numrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, number_format($totalAsetLancarPeriodeKini,2,',','.'));
            $excel->getActiveSheet()->getStyle('C'.$numrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, number_format($totalAsetLancar,2,',','.'));
            $excel->getActiveSheet()->getStyle('D'.$numrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            $excel->getActiveSheet()->getStyle('B'.$numrow)->applyFromArray($style_row_bawah);
            $excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_row_bawah);
            $excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_row_bawah);

            $numrow = $numrow;
            $numrowlia = $numrow+1;

            $excel->setActiveSheetIndex(0)->setCellValue('B'.intval($numrow+1), 'LIADIBILITAS DAN EKUITAS');
	        $excel->setActiveSheetIndex(0)->setCellValue('C'.intval($numrow+1), 'PERIODE INI');
	        $excel->setActiveSheetIndex(0)->setCellValue('D'.intval($numrow+1), 'PERIODE LALU');
	        
	        $excel->getActiveSheet()->getStyle('B'.intval($numrow+1))->applyFromArray($style_col);
	        $excel->getActiveSheet()->getStyle('C'.intval($numrow+1))->applyFromArray($style_col);
	        $excel->getActiveSheet()->getStyle('D'.intval($numrow+1))->applyFromArray($style_col);

	        $excel->setActiveSheetIndex(0)->setCellValue('B'.intval($numrow+2), 'LIADIBILITAS');
	        $excel->getActiveSheet()->mergeCells('B'.intval($numrow+2).':D'.intval($numrow+2));
	        $excel->getActiveSheet()->getStyle('B'.intval($numrow+2))->getFont()->setBold(true);
	        $excel->getActiveSheet()->getStyle('B'.intval($numrow+2))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

	        $excel->getActiveSheet()->getStyle('B'.intval($numrow+2))->applyFromArray($style_row_bawah);
	        $excel->getActiveSheet()->getStyle('C'.intval($numrow+2))->applyFromArray($style_row_bawah);
	        $excel->getActiveSheet()->getStyle('D'.intval($numrow+2))->applyFromArray($style_row_bawah);

	        $numrow = $numrow + 3;
	        $totalLiabilitas    = 0;
                if ($getliabilitas) {
                    foreach ($getliabilitas as $key) { 
                	$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, $key['namaakun']);
		            $excel->getActiveSheet()->getStyle('B'.$numrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
		            $excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, number_format($key['kredit'],2,',','.'));
		            $excel->getActiveSheet()->getStyle('C'.$numrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
		            $excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, number_format($key['kredit'], 2, ',', '.'));
		            $excel->getActiveSheet()->getStyle('D'.$numrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

		            $excel->getActiveSheet()->getStyle('B'.$numrow)->applyFromArray($style_row);
		            $excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_row);
		            $excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_row);
        
                 
                    $totalLiabilitas += $key['kredit'];

                    $numrow=$numrow+1;
                }
            } 
            
	        
	        $excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow,'TOTAL LIADIBILITAS');
            $excel->getActiveSheet()->getStyle('B'.$numrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, number_format($totalLiabilitas,2,',','.'));
            $excel->getActiveSheet()->getStyle('C'.$numrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, number_format($totalLiabilitas,2,',','.'));
            $excel->getActiveSheet()->getStyle('D'.$numrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            $excel->getActiveSheet()->getStyle('B'.$numrow)->applyFromArray($style_row_bawah);
            $excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_row_bawah);
            $excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_row_bawah);

			$numrow = $numrow;
            $numroww = $numrow;

	        $excel->setActiveSheetIndex(0)->setCellValue('B'.intval($numrow+1), 'EKUITAS');
	        $excel->getActiveSheet()->mergeCells('B'.intval($numrow+1).':D'.intval($numrow+1));
	        $excel->getActiveSheet()->getStyle('B'.intval($numrow+1))->getFont()->setBold(true);
	        $excel->getActiveSheet()->getStyle('B'.intval($numrow+1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

	        $excel->getActiveSheet()->getStyle('B'.intval($numrow+1))->applyFromArray($style_row_bawah);
	        $excel->getActiveSheet()->getStyle('C'.intval($numrow+1))->applyFromArray($style_row_bawah);
	        $excel->getActiveSheet()->getStyle('D'.intval($numrow+1))->applyFromArray($style_row_bawah);

	        $numrow = $numrow + 2;
	        $totalEkuitas    = 0;
                if ($ekuitas) {
                    foreach ($ekuitas as $key) {
	                	$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, $key['namaakun']);
			            $excel->getActiveSheet()->getStyle('B'.$numrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
			            $excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, number_format($key['kredit'],2,',','.'));
			            $excel->getActiveSheet()->getStyle('C'.$numrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
			            $excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, number_format($key['kredit'], 2, ',', '.'));
			            $excel->getActiveSheet()->getStyle('D'.$numrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

			            $excel->getActiveSheet()->getStyle('B'.$numrow)->applyFromArray($style_row);
			            $excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_row);
			            $excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_row);
	        
	                 
	                    $totalEkuitas += $key['kredit'];

	                    $numrow=$numrow+1;
	                }
	            } 
            $excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, 'Laba / Rugi Bersih Berjalan');
            $excel->getActiveSheet()->getStyle('B'.$numrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, number_format($gettotallabarugi,2,',','.'));
            $excel->getActiveSheet()->getStyle('C'.$numrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, '');
            $excel->getActiveSheet()->getStyle('D'.$numrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            $excel->getActiveSheet()->getStyle('B'.$numrow)->applyFromArray($style_row);
            $excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_row);
            $excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_row);
	        
	        $excel->setActiveSheetIndex(0)->setCellValue('B'.intval($numrow+1),'TOTAL EKUITAS');
            $excel->getActiveSheet()->getStyle('B'.intval($numrow+1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->setActiveSheetIndex(0)->setCellValue('C'.intval($numrow+1), number_format($totalEkuitas + $gettotallabarugi,2,',','.'));
            $excel->getActiveSheet()->getStyle('C'.intval($numrow+1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $excel->setActiveSheetIndex(0)->setCellValue('D'.intval($numrow+1), number_format($totalEkuitas,2,',','.'));
            $excel->getActiveSheet()->getStyle('D'.intval($numrow+1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $excel->getActiveSheet()->getStyle('B'.intval($numrow+1))->applyFromArray($style_row_bawah);
            $excel->getActiveSheet()->getStyle('C'.intval($numrow+1))->applyFromArray($style_row_bawah);
            $excel->getActiveSheet()->getStyle('D'.intval($numrow+1))->applyFromArray($style_row_bawah);

            $excel->setActiveSheetIndex(0)->setCellValue('B'.intval($numrow+2),'TOTAL LIADIBILITAS DAN EKUITAS');
            $excel->getActiveSheet()->getStyle('B'.intval($numrow+2))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->setActiveSheetIndex(0)->setCellValue('C'.intval($numrow+2), number_format($totalEkuitas + $gettotallabarugi + $totalLiabilitas,2,',','.'));
            $excel->getActiveSheet()->getStyle('C'.intval($numrow+2))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $excel->setActiveSheetIndex(0)->setCellValue('D'.intval($numrow+2), number_format($totalEkuitas + $totalLiabilitas,2,',','.'));
            $excel->getActiveSheet()->getStyle('D'.intval($numrow+2))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            $excel->getActiveSheet()->getStyle('B'.intval($numrow+2))->applyFromArray($style_row_bawah);
            $excel->getActiveSheet()->getStyle('C'.intval($numrow+2))->applyFromArray($style_row_bawah);
            $excel->getActiveSheet()->getStyle('D'.intval($numrow+2))->applyFromArray($style_row_bawah);

	        $excel->getActiveSheet()->getColumnDimension('A')->setWidth(0.5);
	        $excel->getActiveSheet()->getColumnDimension('B')->setWidth(50);
	        $excel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
	        $excel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
	        $excel->getActiveSheet()->getRowDimension('5')->setRowHeight(26);
	        $excel->getActiveSheet()->getRowDimension($numrowlia)->setRowHeight(26);
	        $excel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(-1);

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="Balance Sheet Period '.$tanggal_a.' to '.$tanggal_b. '.xlsx"'); // Set nama file excel nya
            header('Cache-Control: max-age=0');

            $write = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
            $write->save('php://output');
		}
	}

	public function laporan_neraca_standar()
	{
		$data['title']		= 'Laporan Neraca Standar';
		$data['subtitle']	= lang('list');
		$data['content']	= 'laporan/laporan_neraca_standar';
		$data['perusahaan'] = $this->db->get('mperusahaan')->result_array();
		$data = array_merge($data,path_info());
		$this->parser->parse('template',$data);
	}

	public function export_laporan_neraca_standar()
	{
		$this->Neraca_model->set('perusahaan', $this->perusahaan);
		$this->Neraca_model->set('tanggalAwal', $this->tanggalAwal);
		$pch_tgl_a = explode('-', date('Y-m-d', strtotime($this->tanggalAwal)));
		$bulan_a = $this->bulan($pch_tgl_a[1]);
		$tanggal_a = $pch_tgl_a[2].' '.$bulan_a.' '.$pch_tgl_a[0];

		if($this->input->get('pdf')){
			$this->load->library('pdf');
			
			$data['title'] = 'Balance Sheet (Standard)';
			$data['get_per'] = $this->Perusahaan_model->get_by_id($this->perusahaan);
			$data['nama_perusahaan'] = $data['get_per']['nama_perusahaan'];
			$data['title2'] = 'As of '.$tanggal_a;
			$data['css'] = file_get_contents(FCPATH.'assets/css/print.min.css');
			$data['getasetlancar'] = $this->Neraca_model->getasetlancar_standard();
			$data['asetTetap'] = $this->Neraca_model->getAsetTetapStandar();
			$data['getliabilitas'] = $this->Neraca_model->getliabilitas_standard();
			// $data['getmodal'] = $this->model->getmodal($data['tanggal']);
			$data['gettotallabarugi'] = $this->Neraca_model->gettotallabarugi_standard();
			$data['ekuitas'] = $this->Neraca_model->getEkuitas_standard();
			$data = array_merge($data,path_info());
			$html = $this->load->view('laporan/cetak_laporan_neraca_standar', $data, TRUE);
			
			$pdf = $this->pdf;
			$pdf->loadHtml($html);
			$pdf->setPaper('A4', 'portrait');
			$pdf->render();
			$time = time();
			$pdf->stream("Balance Sheet (Standard)". $time, array("Attachment" => false));
		} else {
			include_once APPPATH . 'third_party/PHPExcel.php';
			
      		$get_per = $this->Perusahaan_model->get_by_id($this->perusahaan);
			$nama_perusahaan = $get_per['nama_perusahaan'];
			$title = 'Balance Sheet(Compare Month)';
			$title2 = 'Period '.$tanggal_a;
			$getasetlancar = $this->Neraca_model->getasetlancar_standard();
			// $data['getasettetap'] = $this->model->getasettetap($data['tanggal']);
			$getliabilitas = $this->Neraca_model->getliabilitas_standard();
			// $data['getmodal'] = $this->model->getmodal($data['tanggal']);
			$gettotallabarugi = $this->Neraca_model->gettotallabarugi_standard();
			$ekuitas = $this->Neraca_model->getEkuitas_standard();

			$excel = new PHPExcel();
			$excel1 = new PHPExcel_Worksheet($excel, 'Neraca');

			// Attach the "My Data" worksheet as the first worksheet in the PHPExcel object
			$excel->addSheet($excel1, 0);
	        $excel->getProperties()
	                ->setCreator('Ecorporate')
	                ->setLastModifiedBy('Ecorporate')
	                ->setTitle('Data Receivable Payment')
	                ->setSubject('Receivable Payment')
	                ->setDescription('Receivable Payment ')
	                ->setKeyWords('Receivable Payment');

	        $style_col = [
	        	'fill' => [
	                'type' => PHPExcel_Style_Fill::FILL_SOLID,
	                'color' => ['rgb' => 'd9e1f2']
	            ],
	            'font' => ['bold' => true],
	            'alignment' => [
	                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	            ],
	            'borders' => [
	                'top' => ['style' => PHPExcel_Style_Border::BORDER_THIN],
	                'bottom' => ['style' => PHPExcel_Style_Border::BORDER_THIN],
	                'right' => ['style' => PHPExcel_Style_Border::BORDER_THIN],
	                'left' => ['style' => PHPExcel_Style_Border::BORDER_THIN],
	            ]
	        ];

	        $style_row = [
	            'alignment' => [
	                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	            ],
	            'borders' => [
	                'right' => ['style' => PHPExcel_Style_Border::BORDER_THIN],
	                'left' => ['style' => PHPExcel_Style_Border::BORDER_THIN],
	            ]
	        ];

	        $style_row_bawah = [
	            'alignment' => [
	                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	            ],
	            'borders' => [
	            	'top' => ['style' => PHPExcel_Style_Border::BORDER_THIN],
	                'right' => ['style' => PHPExcel_Style_Border::BORDER_THIN],
	                'left' => ['style' => PHPExcel_Style_Border::BORDER_THIN],
	                'bottom' => ['style' => PHPExcel_Style_Border::BORDER_THIN],
	            ]
	        ];

	        $style_row_full = [
	            'alignment' => [
	                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	            ],
	            'borders' => [
	                'top' => ['style' => PHPExcel_Style_Border::BORDER_THIN],
	                'bottom' => ['style' => PHPExcel_Style_Border::BORDER_THIN],
	                'right' => ['style' => PHPExcel_Style_Border::BORDER_THIN],
	                'left' => ['style' => PHPExcel_Style_Border::BORDER_THIN],
	            ]
	        ];

	        $excel->setActiveSheetIndex(0)->setCellValue('A1', '');
	        $excel->setActiveSheetIndex(0)->setCellValue('B1', $nama_perusahaan);
	        $excel->getActiveSheet()->mergeCells('B1:D1');
	        $excel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);
	        $excel->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

	        $excel->setActiveSheetIndex(0)->setCellValue('A2', '');
	        $excel->setActiveSheetIndex(0)->setCellValue('B2', $title);
	        $excel->getActiveSheet()->mergeCells('B2:D2');
	        $excel->getActiveSheet()->getStyle('B2')->getFont()->setBold(true);
	        $excel->getActiveSheet()->getStyle('B2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

	        $excel->setActiveSheetIndex(0)->setCellValue('A3', '');
	        $excel->setActiveSheetIndex(0)->setCellValue('B3', $title2);
	        $excel->getActiveSheet()->mergeCells('B3:D3');
	        $excel->getActiveSheet()->getStyle('B3')->getFont()->setBold(true);
	        $excel->getActiveSheet()->getStyle('B3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

	        $excel->setActiveSheetIndex(0)->setCellValue('A5', 'COA');
	        $excel->setActiveSheetIndex(0)->setCellValue('B5', 'ASET');
	        $excel->setActiveSheetIndex(0)->setCellValue('C5', 'PERIODE INI');
	        $excel->setActiveSheetIndex(0)->setCellValue('D5', 'PERIODE LALU');
	        
	        $excel->getActiveSheet()->getStyle('A5')->applyFromArray($style_col);
	        $excel->getActiveSheet()->getStyle('B5')->applyFromArray($style_col);
	        $excel->getActiveSheet()->getStyle('C5')->applyFromArray($style_col);
	        $excel->getActiveSheet()->getStyle('D5')->applyFromArray($style_col);

	        $excel->setActiveSheetIndex(0)->setCellValue('A6', '');
	        $excel->setActiveSheetIndex(0)->setCellValue('B6', 'ASET LANCAR');
	        $excel->getActiveSheet()->mergeCells('B6:D6');
	        $excel->getActiveSheet()->getStyle('B6')->getFont()->setBold(true);
	        $excel->getActiveSheet()->getStyle('B6')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

	        $excel->getActiveSheet()->getStyle('A6')->applyFromArray($style_row_bawah);
	        $excel->getActiveSheet()->getStyle('B6')->applyFromArray($style_row_bawah);
	        $excel->getActiveSheet()->getStyle('C6')->applyFromArray($style_row_bawah);
	        $excel->getActiveSheet()->getStyle('D6')->applyFromArray($style_row_bawah);

	        $numrow = 7;

	        $totalAsetLancarPeriodeKini = 0;
            $totalAsetLancar = 0;
            if ($getasetlancar) {
                foreach ($getasetlancar as $key) { 
                	$excel->setActiveSheetIndex(0)->setCellValue('A'.$numrow, $key['akunno']);
                	$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, $key['namaakun']);
		            $excel->getActiveSheet()->getStyle('B'.$numrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
		            $excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, number_format($key['debetPeriodeKini'],2,',','.'));
		            $excel->getActiveSheet()->getStyle('C'.$numrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
		            $excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, number_format($key['debet'], 2, ',', '.'));
		            $excel->getActiveSheet()->getStyle('D'.$numrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

		            $excel->getActiveSheet()->getStyle('B'.$numrow)->applyFromArray($style_row);
		            $excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_row);
		            $excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_row);
        
                 
                    $totalAsetLancarPeriodeKini += $key['debetPeriodeKini'];
                    $totalAsetLancar += $key['debet'];

                    $numrow=$numrow+1;
                }
            } 
            
	        
	        $excel->setActiveSheetIndex(0)->setCellValue('A'.$numrow,'');
	        $excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow,'TOTAL ASET LANCAR');
            $excel->getActiveSheet()->getStyle('B'.$numrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, number_format($totalAsetLancarPeriodeKini,2,',','.'));
            $excel->getActiveSheet()->getStyle('C'.$numrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, number_format($totalAsetLancar,2,',','.'));
            $excel->getActiveSheet()->getStyle('D'.$numrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            $excel->getActiveSheet()->getStyle('A'.$numrow)->applyFromArray($style_row_bawah);
            $excel->getActiveSheet()->getStyle('B'.$numrow)->applyFromArray($style_row_bawah);
            $excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_row_bawah);
            $excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_row_bawah);

            $numrow = $numrow;
            $numrowlia = $numrow+1;

            $excel->setActiveSheetIndex(0)->setCellValue('A'.intval($numrow+1), '');
            $excel->setActiveSheetIndex(0)->setCellValue('B'.intval($numrow+1), 'LIADIBILITAS DAN EKUITAS');
	        $excel->setActiveSheetIndex(0)->setCellValue('C'.intval($numrow+1), 'PERIODE INI');
	        $excel->setActiveSheetIndex(0)->setCellValue('D'.intval($numrow+1), 'PERIODE LALU');
	        
	        $excel->getActiveSheet()->getStyle('A'.intval($numrow+1))->applyFromArray($style_col);
	        $excel->getActiveSheet()->getStyle('B'.intval($numrow+1))->applyFromArray($style_col);
	        $excel->getActiveSheet()->getStyle('C'.intval($numrow+1))->applyFromArray($style_col);
	        $excel->getActiveSheet()->getStyle('D'.intval($numrow+1))->applyFromArray($style_col);

	        $excel->setActiveSheetIndex(0)->setCellValue('A'.intval($numrow+2), '');
	        $excel->setActiveSheetIndex(0)->setCellValue('B'.intval($numrow+2), 'LIADIBILITAS');
	        $excel->getActiveSheet()->mergeCells('B'.intval($numrow+2).':D'.intval($numrow+2));
	        $excel->getActiveSheet()->getStyle('B'.intval($numrow+2))->getFont()->setBold(true);
	        $excel->getActiveSheet()->getStyle('B'.intval($numrow+2))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

	        $excel->getActiveSheet()->getStyle('A'.intval($numrow+2))->applyFromArray($style_row_bawah);
	        $excel->getActiveSheet()->getStyle('B'.intval($numrow+2))->applyFromArray($style_row_bawah);
	        $excel->getActiveSheet()->getStyle('C'.intval($numrow+2))->applyFromArray($style_row_bawah);
	        $excel->getActiveSheet()->getStyle('D'.intval($numrow+2))->applyFromArray($style_row_bawah);

	        $numrow = $numrow + 3;
	        $totalLiabilitas = 0;
                if ($getliabilitas) {
                    foreach ($getliabilitas as $key) { 
                	$excel->setActiveSheetIndex(0)->setCellValue('A'.$numrow, $key['akunno']);
                	$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, $key['namaakun']);
		            $excel->getActiveSheet()->getStyle('B'.$numrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
		            $excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, number_format($key['kredit'],2,',','.'));
		            $excel->getActiveSheet()->getStyle('C'.$numrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
		            $excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, number_format($key['kredit'], 2, ',', '.'));
		            $excel->getActiveSheet()->getStyle('D'.$numrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

		            $excel->getActiveSheet()->getStyle('B'.$numrow)->applyFromArray($style_row);
		            $excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_row);
		            $excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_row);
        
                 
                    $totalLiabilitas += $key['kredit'];

                    $numrow=$numrow+1;
                }
            } 
            
	        
	        $excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow,'TOTAL LIADIBILITAS');
            $excel->getActiveSheet()->getStyle('B'.$numrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, number_format($totalLiabilitas,2,',','.'));
            $excel->getActiveSheet()->getStyle('C'.$numrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, number_format($totalLiabilitas,2,',','.'));
            $excel->getActiveSheet()->getStyle('D'.$numrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            $excel->getActiveSheet()->getStyle('B'.$numrow)->applyFromArray($style_row_bawah);
            $excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_row_bawah);
            $excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_row_bawah);

			$numrow = $numrow;
            $numroww = $numrow;

	        $excel->setActiveSheetIndex(0)->setCellValue('B'.intval($numrow+1), 'EKUITAS');
	        $excel->getActiveSheet()->mergeCells('B'.intval($numrow+1).':D'.intval($numrow+1));
	        $excel->getActiveSheet()->getStyle('B'.intval($numrow+1))->getFont()->setBold(true);
	        $excel->getActiveSheet()->getStyle('B'.intval($numrow+1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

	        $excel->getActiveSheet()->getStyle('B'.intval($numrow+1))->applyFromArray($style_row_bawah);
	        $excel->getActiveSheet()->getStyle('C'.intval($numrow+1))->applyFromArray($style_row_bawah);
	        $excel->getActiveSheet()->getStyle('D'.intval($numrow+1))->applyFromArray($style_row_bawah);

	        $numrow = $numrow + 2;
	        $totalEkuitas = 0;
                if ($ekuitas) {
                    foreach ($ekuitas as $key) {
	                	$excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, $key['namaakun']);
			            $excel->getActiveSheet()->getStyle('B'.$numrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
			            $excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, number_format($key['kredit'],2,',','.'));
			            $excel->getActiveSheet()->getStyle('C'.$numrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
			            $excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, number_format($key['kredit'], 2, ',', '.'));
			            $excel->getActiveSheet()->getStyle('D'.$numrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

			            $excel->getActiveSheet()->getStyle('B'.$numrow)->applyFromArray($style_row);
			            $excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_row);
			            $excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_row);
	        
	                 
	                    $totalEkuitas += $key['kredit'];

	                    $numrow=$numrow+1;
	                }
	            } 
            $excel->setActiveSheetIndex(0)->setCellValue('B'.$numrow, 'Laba / Rugi Bersih Berjalan');
            $excel->getActiveSheet()->getStyle('B'.$numrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $excel->setActiveSheetIndex(0)->setCellValue('C'.$numrow, number_format($gettotallabarugi,2,',','.'));
            $excel->getActiveSheet()->getStyle('C'.$numrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $excel->setActiveSheetIndex(0)->setCellValue('D'.$numrow, '');
            $excel->getActiveSheet()->getStyle('D'.$numrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            $excel->getActiveSheet()->getStyle('B'.$numrow)->applyFromArray($style_row);
            $excel->getActiveSheet()->getStyle('C'.$numrow)->applyFromArray($style_row);
            $excel->getActiveSheet()->getStyle('D'.$numrow)->applyFromArray($style_row);
	        
	        $excel->setActiveSheetIndex(0)->setCellValue('B'.intval($numrow+1),'TOTAL EKUITAS');
            $excel->getActiveSheet()->getStyle('B'.intval($numrow+1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->setActiveSheetIndex(0)->setCellValue('C'.intval($numrow+1), number_format($totalEkuitas + $gettotallabarugi,2,',','.'));
            $excel->getActiveSheet()->getStyle('C'.intval($numrow+1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $excel->setActiveSheetIndex(0)->setCellValue('D'.intval($numrow+1), number_format($totalEkuitas,2,',','.'));
            $excel->getActiveSheet()->getStyle('D'.intval($numrow+1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $excel->getActiveSheet()->getStyle('B'.intval($numrow+1))->applyFromArray($style_row_bawah);
            $excel->getActiveSheet()->getStyle('C'.intval($numrow+1))->applyFromArray($style_row_bawah);
            $excel->getActiveSheet()->getStyle('D'.intval($numrow+1))->applyFromArray($style_row_bawah);

            $excel->setActiveSheetIndex(0)->setCellValue('B'.intval($numrow+2),'TOTAL LIADIBILITAS DAN EKUITAS');
            $excel->getActiveSheet()->getStyle('B'.intval($numrow+2))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->setActiveSheetIndex(0)->setCellValue('C'.intval($numrow+2), number_format($totalEkuitas + $gettotallabarugi + $totalLiabilitas,2,',','.'));
            $excel->getActiveSheet()->getStyle('C'.intval($numrow+2))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $excel->setActiveSheetIndex(0)->setCellValue('D'.intval($numrow+2), number_format($totalEkuitas + $totalLiabilitas,2,',','.'));
            $excel->getActiveSheet()->getStyle('D'.intval($numrow+2))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            $excel->getActiveSheet()->getStyle('B'.intval($numrow+2))->applyFromArray($style_row_bawah);
            $excel->getActiveSheet()->getStyle('C'.intval($numrow+2))->applyFromArray($style_row_bawah);
            $excel->getActiveSheet()->getStyle('D'.intval($numrow+2))->applyFromArray($style_row_bawah);

	        $excel->getActiveSheet()->getColumnDimension('A')->setWidth(0.5);
	        $excel->getActiveSheet()->getColumnDimension('B')->setWidth(50);
	        $excel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
	        $excel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
	        $excel->getActiveSheet()->getRowDimension('5')->setRowHeight(26);
	        $excel->getActiveSheet()->getRowDimension($numrowlia)->setRowHeight(26);
	        $excel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(-1);

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="Balance Sheet Period '.$tanggal_a.'.xlsx"'); // Set nama file excel nya
            header('Cache-Control: max-age=0');

            $write = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
            $write->save('php://output');
		}
	}

	private function bulan($bulan)
    {
        $bulan=$bulan;
        switch ($bulan) {
          case '01':
            $bulan= "Jan";
            break;
          case '02':
            $bulan= "Feb";
            break;
          case '03':
            $bulan= "Mar";
            break;
          case '04':
            $bulan= "Apr";
            break;
          case '05':
            $bulan= "May";
            break;
          case '06':
            $bulan= "Jun";
            break;
          case '07':
            $bulan= "Jul";
            break;
          case '08':
            $bulan= "Aug";
            break;
          case '09':
            $bulan= "September";
            break;
          case '10':
            $bulan= "Oct";
            break;
          case '11':
            $bulan= "Nov";
            break;
          case '12':
            $bulan= "Dec";
            break;
          default:
            $bulan= "Isi variabel tidak di temukan";
            break;
        }

        return $bulan;
    }

  public function neracaMultiPeriod() {
    if ($this->perusahaan) {
      $tanggalAwal  = strtotime($this->tanggalAwal);
      $tanggalAkhir = strtotime($this->tanggalAkhir);
      $numBulan     = 1 + (date("Y", $tanggalAkhir) - date("Y", $tanggalAwal)) * 12;
      $numBulan     += date("m", $tanggalAkhir) - date("m", $tanggalAwal);
      $tahun        = substr($this->tanggalAwal, 0, 4);
      $bulan        = substr($this->tanggalAwal, 5, 2);
      for ($i=0; $i < $numBulan; $i++) { 
        $this->Neraca_model->set('tanggalAwal', $tahun . $bulan . '-01');
        $this->Neraca_model->set('perusahaan', $this->perusahaan);
        $data['laporan'][$bulan . '-' . $tahun]['asetLancar']    = $this->Neraca_model->getasetlancar_standard();
        $data['laporan'][$bulan . '-' . $tahun]['liabilitas']    = $this->Neraca_model->getliabilitas_standard();
        $data['laporan'][$bulan . '-' . $tahun]['totalLabarugi'] = $this->Neraca_model->gettotallabarugi_standard();
        $data['laporan'][$bulan . '-' . $tahun]['ekuitas']       = $this->Neraca_model->getEkuitas_standard();
        $bulan++;
        if ($bulan > 12) {
          $bulan  = 1;
          $tahun++;
        }
      }
			$data['tanggalAwal']	= $this->tgl_indo($this->tanggalAwal);
      $data['tanggalAkhir']	= $this->tgl_indo($this->tanggalAkhir);
      $data['perusahaan'] = $this->Perusahaan_model->get_by_id($this->perusahaan);
      switch ($this->input->get('jenis')) {
        case 'pdf':
          $this->load->library('pdf');
          $pdf            = $this->pdf;
          $data['title']	= 'Balance Sheet (Multi Period)';
          $data['css']    = file_get_contents(FCPATH.'assets/css/print.min.css');
          $data		  	    = array_merge($data,path_info());
          $html 			    = $this->load->view('laporan/neracaMultiPeriod/print', $data, TRUE);
          $pdf->loadHtml($html);
          $pdf->setPaper('legal', 'landscape');
          $pdf->render();
          $time = time();
          $pdf->stream("Balance Sheet (Multi Period)". $time, array("Attachment" => false));
          break;
        case 'excel':
          $spreadsheet	= \PhpOffice\PhpSpreadsheet\IOFactory::load('assets/Buku Kas Bank Excel.xlsx');
          $worksheet		= $spreadsheet->getActiveSheet();
          $worksheet->getCell('A1')->setValue($data['perusahaan']['nama_perusahaan']);
          $worksheet->getCell('A3')->setValue('From ' . $data['tanggalAwal'] . ' to ' . $data['tanggalAkhir']);
          $no = 0;
          $x	= 6;
          foreach ($data['laporan'] as $key) {
            foreach ($key as $value) { 
              $worksheet->getCell('A' . $x)->setValue($value['tanggal']);
              $worksheet->getCell('B' . $x)->setValue($value['no']);
              $worksheet->getCell('C' . $x)->setValue($value['keterangan']);
              $worksheet->mergeCells('C' . $x . ':' . 'D' . $x);
              $worksheet->getCell('E' . $x)->setValue(number_format($value['debet'],2,',','.'));
              $worksheet->getCell('F' . $x)->setValue(number_format($value['kredit'],2,',','.'));
              $worksheet->getCell('G' . $x)->setValue(number_format(($value['debet'] - $value['kredit']),2,',','.'));
              $x++;
            }
          }
          $writer = new Xlsx($spreadsheet);
          $filename = 'LaporanBukuBank';
          
          header('Content-Type: application/vnd.ms-excel');
          header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
          header('Cache-Control: max-age=0');
      
          $writer->save('php://output');
          break;
        
        default:
          # code...
          break;
      }
    } else {
      $data['title']		= 'Laporan Neraca Multi Period';
      $data['subtitle']	= lang('list');
      $data['content']	= 'laporan/neracaMultiPeriod/index';
      $data				      = array_merge($data,path_info());
      $this->parser->parse('template',$data);
    }
  }
}