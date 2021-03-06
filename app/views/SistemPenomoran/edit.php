<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
		<div class="container-fluid">
			<div class="row mb-2">
				<div class="col-sm-6">
					<h1>{title}</h1>
				</div>
				<div class="col-sm-6">
					<ol class="breadcrumb float-sm-right">
						<li class="breadcrumb-item"><a href="#">Home</a></li>
						<li class="breadcrumb-item active">{title}</li>
					</ol>
				</div>
			</div>
		</div><!-- /.container-fluid -->
  </section>

  <!-- Main content -->
  <section class="content">
		<div class="container-fluid">
			<div class="row">
				<div class="col-12">         
					<div class="card">
						<div class="card-body">
              <div class="row">
                <div class="col-md-6">
                  <form action="javascript:save()" id="form1">
                    <div class="form-group">
                      <label>Formulir :</label>
                      <select class="form-control" name="formulir" id="formulir" style="width:100%;">
                        <option value="permintaanPembelian" <?= $formulir == 'permintaanPembelian' ? 'selected' : '' ; ?>>Permintaan Pembelian</option>
                        <option value="fakturPembelian" <?= $formulir == 'fakturPembelian' ? 'selected' : '' ; ?>>Faktur Pembelian</option>
                        <option value="pesananPenjualan" <?= $formulir == 'pesananPenjualan' ? 'selected' : '' ; ?>>Pesanan Penjualan</option>
                        <option value="pengirimanBarang" <?= $formulir == 'pengirimanBarang' ? 'selected' : '' ; ?>>Pengiriman Barang</option>
                        <option value="fakturPenjualan" <?= $formulir == 'fakturPenjualan' ? 'selected' : '' ; ?>>Faktur Penjualan</option>
                        <option value="kasBank" <?= $formulir == 'kasBank' ? 'selected' : '' ; ?>>Kas Bank</option>
                        <option value="pengeluaranKasKecil" <?= $formulir == 'pengeluaranKasKecil' ? 'selected' : '' ; ?>>Pengeluaran Kas Kecil</option>
                        <option value="pemindahbukuan" <?= $formulir == 'pemindahbukuan' ? 'selected' : '' ; ?>>Pemindahbukuan</option>
                        <option value="pengajuanKasKecil" <?= $formulir == 'pengajuanKasKecil' ? 'selected' : '' ; ?>>Pengajuan Kas Kecil</option>
                        <option value="setorKasKecil" <?= $formulir == 'setorKasKecil' ? 'selected' : '' ; ?>>Setor Kas Kecil</option>
                      </select>
                    </div>
                    <div class="form-group">
                        <label>Format Penomoran :</label>
                        <input type="text" class="form-control" name="formatPenomoran" required value="{formatPenomoran}">
                    </div>
                    <div class="form-group">
                        <label>Awal Nomor :</label>
                        <input type="text" class="form-control" name="awalNomor" value="{awalNomor}">
                    </div>
                    <div class="text-right">
                        <a href="{site_url}SistemPenomoran" class="btn bg-danger"><?php echo lang('cancel') ?></a>
                        <button type="submit" class="btn bg-success"><?php echo lang('save') ?></button>
                    </div>
                  </form>
                </div>
              </div>
						</div>
					</div>
			</div>
			</div>
		</div>
    </section>
</div>
<script type="text/javascript">
	var base_url = '{site_url}SistemPenomoran/';

  $(document).ready(function () {
    $('#formulir').select2({
      placeholder : 'Pilih Formulir',
      allowClear  : true
    });
  })

    function save() {
      var form      = $('#form1')[0];
      var formData  = new FormData(form);
      $.ajax({
        url         : base_url + 'save/' + '<?= $this->uri->segment(3); ?>',
        dataType    : 'json',
        method      : 'post',
        data        : formData,
        contentType : false,
        processData : false,
        beforeSend  : function() {
          pageBlock();
        },
        afterSend : function() {
          unpageBlock();
        },
        success : function(data) {
          if(data.status == 'success') {
            swal("Berhasil!", "Data Berhasil Diedit!", "success");
            redirect(base_url);
          } else {
            swal("Gagal!", "Pikachu was caught!", "error");
          }
        },
        error : function() {
          swal("Gagal!", "Internal Server Error!", "error");
        }
      })
    }
</script>