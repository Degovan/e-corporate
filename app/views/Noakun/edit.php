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
            <li class="breadcrumb-item active">{subtitle}</li>
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
              <form action="javascript:save()" id="form1">
                <div class="row">
                  <div class="col-md-2">
                    <div class="form-group">
                      <label><?php echo lang('Tipe') ?>:</label>
                      <input type="number" class="form-control" onkeyup="sum();" name="tipe" id="tipe" value="{tipe}">
                    </div>
                  </div>
                  <div class="col-md-2">
                    <div class="form-group">
                      <label><?php echo lang('Kelompok') ?>:</label>
                      <input type="number" class="form-control" onkeyup="sum();" name="noakunheader" id="noakunheader" value="{noakunheader}">
                    </div>                      
                  </div>
                  <div class="col-md-2">
                    <div class="form-group">
                      <label><?php echo lang('Jenis') ?>:</label>
                      <input type="number" class="form-control" onkeyup="sum();" name="jenis" id="jenis" value="{jenis}">
                    </div>                    
                  </div>
                </div>
                <div class="row">                	
                  <div class="col-md-3">
                    <div class="form-group">
                      <label><?php echo lang('Objek') ?>:</label>
                      <input type="number" class="form-control" onkeyup="sum();" name="objek" id="objek" value="{objek}">
                    </div>                      
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label><?php echo lang('Rincian') ?>:</label>
                      <input type="number" class="form-control" onkeyup="sum();" name="rincian" id="rincian" value="{rincian}">
                    </div>                      
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label><?php echo lang('name') ?>:</label>
                      <input type="text" readonly class="form-control" name="akunno" onkeyup="sum();" id="akunno" value="{akunno}">
                      <input type="text" class="form-control" name="namaakun" required value="{namaakun}">
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-3">
                    <div class="form-group">
                      <label><?php echo lang('payment_status') ?>:</label>
                      <select class="form-control stbayar" name="stbayar" required></select>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label><?php echo lang('header_status') ?>:</label>
                      <select class="form-control stheader" name="stheader" required></select>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-3">
                    <div class="form-group">
                      <label><?php echo lang('default_balance') ?>:</label>
                      <select class="form-control stdebet" name="stdebet" required></select>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label><?php echo lang('Kategori Akun') ?>:</label>
                      <select class="form-control kategoriakun" name="kategoriakun" required>
                        <option value="Cash/Bank" <?= $kategoriakun == 'Cash/Bank' ? 'selected' : '' ; ?>>Cash/Bank</option>
                        <option value="Other Asset" <?= $kategoriakun == 'Other Asset' ? 'selected' : '' ; ?>>Other Asset</option>
                        <option value="Account Receivable" <?= $kategoriakun == 'Account Receivable' ? 'selected' : '' ; ?>>Account Receivable</option>
                        <option value="Revenue" <?= $kategoriakun == 'Revenue' ? 'selected' : '' ; ?>>Revenue</option>
                      </select>
                    </div>
                  </div>
                </div>
                <div class="text-left">
                  <a href="{site_url}noakun" class="btn bg-danger"><?php echo lang('cancel') ?></a>
                  <button type="submit" class="btn bg-success"><?php echo lang('save') ?></button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<script type="text/javascript">
	var base_url  = '{site_url}noakun/';
  $(document).ready(function(){
    ajax_select({ 
      id        : '.noakunheader', 
      url       : base_url + 'select2_noakunheader', 
      selected  : { 
        id  : '' 
      } 
    });

    $('.stbayar').select2({
      placeholder: 'Select an Option',
      data: [
        {id: '1', text: 'YA'},
        {id: '0', text: 'TIDAK'},
      ]
    }).val('{stbayar}').trigger('change');

    $('.stheader').select2({
      placeholder: 'Select an Option',
      data: [
        {id: '1', text: 'YA'},
        {id: '0', text: 'TIDAK'},
      ]
    }).val('{stheader}').trigger('change');
    
    $('.stdebet').select2({
      placeholder: 'Select an Option',
      data: [
        {id: '1', text: 'DEBET'},
        {id: '0', text: 'KREDIT'},
      ]
    }).val('{stdebet}').trigger('change');
  })

  $(document).on('change','.stbayar', function() {
    if($(this).val() == '1') {
      $('.stdebet').attr('disabled',false);
    } else {
      $('.stdebet').attr('disabled',true);
    }
  })

  function sum() {
    var txtFirstNumberValue   = document.getElementById('tipe').value;
    var txtSecondNumberValue  = document.getElementById('noakunheader').value;
    var txtTNumberValue       = document.getElementById('jenis').value;
    var txtFNumberValue       = document.getElementById('objek').value;
    var txtVNumberValue       = document.getElementById('rincian').value;

    if (txtFirstNumberValue) {
      var result  = txtFirstNumberValue;
      if (txtSecondNumberValue) {
        result  += '.';
        result  += txtSecondNumberValue;
        if (txtTNumberValue) {
          result  += '.';
          result  += txtTNumberValue;
          if (txtFNumberValue) {
            result  += '.';
            result  += txtFNumberValue;
            if (txtVNumberValue) {
              result  += '.';
              result  += txtVNumberValue;
            }
          }
        }
      }
    }
    
    document.getElementById('akunno').value = result;
  }

  function save() {
    var form      = $('#form1')[0];
    var formData  = new FormData(form);
    $.ajax({
      url         : base_url + 'save/<?= $this->uri->segment(3); ?>',
      dataType    : 'json',
      method      : 'post',
      data        : formData,
      contentType : false,
      processData : false,
      beforeSend  : function() {
        pageBlock();
      },
      afterSend: function() {
        unpageBlock();
      },
      success: function(data) {
        if (data.status == 'success') {
					swal("Berhasil!", "Berhasil edit data", "success");
          redirect('{site_url}nomor_akun');
				} else {
					swal("Gagal!", "Gagal edit data", "error");
				}
      },
      error: function() {
        swal("Gagal!", "Internal server error", "error");
      }
    })
  }
</script>