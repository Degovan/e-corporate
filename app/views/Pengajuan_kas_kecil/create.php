
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
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">{title}</h3>
        </div>
        <div class="card-body">
          <form action="javascript:save()" id="form1">
            <div class="row">
              <div class="col-md-3">
                <div class="form-group">
                  <label><?php echo lang('no_receipt') ?>:</label>
                  <input type="text" class="form-control" id="nokwitansi" name="nokwitansi" placeholder="AUTO" readonly value="{kode_otomatis}">
                </div>
                <div class="form-group">
                  <label><?php echo lang('company') ?>:</label>
                  <?php
                    if ($this->session->userid !== '1') { ?>
                      <input type="hidden" name="perusahaan" value="<?= $this->session->idperusahaan; ?>">
                      <input type="text" class="form-control" value="<?= $this->session->perusahaan; ?>" disabled>
                    <?php } else { ?>
                      <select class="form-control perusahaan" name="perusahaan" style="width: 100%;" id="perusahaan"></select>
                    <?php }
                  ?>
                </div>   
                <div class="form-group">
                  <label><?php echo lang('cash') ?>:</label>
                  <select id="kas" class="form-control kas" name="kas" required></select>
                </div>
                <div class="form-group">
                  <label><?php echo lang('nominal') ?>:</label>
                  <input type="text" class="form-control nominal" id="nominal" name="nominal" required>
                </div> 
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label><?php echo lang('date') ?>:</label>
                  <input type="date" class="form-control datepicker" name="tanggal" required value="{tanggal}">
                </div>  
                <div class="form-group">
                  <label><?php echo lang('recipients_name') ?>:</label>
                  <select id="pejabat" class="form-control" name="pejabat" required></select>
                </div> 
                <div class="form-group">
                  <label><?php echo lang('Rekening Bank') ?>:</label>
                  <select id="rekening" class="form-control rekening" name="rekening" required></select>
                </div>
                <div class="form-group">
                  <label>Sisa Kas Bank : </label>
                  <input type="text" name="sisaKasBank" id="sisaKasBank" disabled class="form-control text-right">
                </div>
                <div class="form-group">
                  <label><?php echo lang('remaining_petty_cash') ?>:</label>
                  <input type="text" id="sisa_kas_kecil" class="form-control sisa_kas_kecil text-right" name="" readonly>
                </div>               
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label><?php echo lang('information') ?>:</label>
                  <textarea class="form-control" name="keterangan" rows="3"></textarea>
                </div>
              </div>
            </div>
        </div>
        <div class="card-footer">
          <div class="text-left">
            <div class="btn-group">
              <a href="{site_url}pengajuan_kas_kecil" class="btn bg-danger"><?php echo lang('cancel') ?></a>
              &nbsp;<button type="submit" class="btn bg-success"><?php echo lang('save') ?></button>
            </div>
          </div>
          </form>     
        </div>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
  var base_url = '{site_url}pengajuan_kas_kecil/';

  $(document).ready(function(){
    //combbox perusahaan
    ajax_select({
      id: '#perusahaan',
      url: base_url + 'select2_mperusahaan',
    });
    //combobox kas/noakun
    ajax_select({
      id: '#kas',
      url: base_url + 'select2_mnoakun/',
    });
  })

  if ('<?= $this->session->userid; ?>' == '1') {
    ajax_select({
      id: '#perusahaan',
      url: '{site_url}perusahaan/select2',
    });
    
    $('#perusahaan').change(function(e) {
      $("#pejabat").val($("#pejabat").data("default-value"));
      $("#rekening").val($("#rekening").data("default-value"));
      $('input[id=sisa_kas_kecil]').val('0'); 
      var perusahaanId = $('select[name=perusahaan]').val();
      ajax_select({
        id: '#pejabat',
        url: base_url + 'select2_mdepartemen_pejabat/' + perusahaanId,
      });

      ajax_select({
        id: '#rekening',
        url: base_url + 'select2_mrekening_perusahaan/' + perusahaanId,
      });

      $.ajax({
        url: base_url + 'get_hitungsisakaskecil',
        method: 'post',
        datatype: 'json',
        data: {
                idper: $('select[name=perusahaan]').val(),
            },
        success: function(data){
            $('input[id=sisa_kas_kecil]').val(formatRp(String(data.hasil))+',00');     
        }
      });
      var id=$('select[name=perusahaan]').val();
      $.ajax({
        url : base_url + 'get_kode_perusahaan',
        method : "POST",
        data : {id: id},
        async : true,
        dataType : 'json',
        success: function(data){
          var kodeper = '';
          var i;
          for(i=0; i<data.length; i++){ kodeper += data[i].kode; }
                  
          var nomor = '{kode_otomatis}';
          var tipe = 'KK';
          var tahun = '{tahun}';
          var kodeperusahaan = kodeper;
          document.getElementById("form1").nokwitansi.value = nomor+'/'+kodeperusahaan+'/'+tipe+'/'+tahun;
        }
      });
    })
  } else {
    ajax_select({
      id: '#pejabat',
      url: base_url + 'select2_mdepartemen_pejabat/<?= $this->session->idperusahaan; ?>',
    });

    ajax_select({
      id: '#rekening',
      url: base_url + 'select2_mrekening_perusahaan/<?= $this->session->idperusahaan; ?>',
    });

    $.ajax({
      url       : base_url + 'get_hitungsisakaskecil',
      method    : 'post',
      datatype  : 'json',
      data      : {
        idper : '<?= $this->session->idperusahaan; ?>',
      },
      success : function(data){
        $('input[id=sisa_kas_kecil]').val(formatRp(String(data.hasil))+',00');     
      }
    });

    $.ajax({
      url     : base_url + 'get_kode_perusahaan',
      method  : "POST",
      data    : {
        id  : '<?= $this->session->idperusahaan; ?>'
      },
      async     : true,
      dataType  : 'json',
      success   : function(data){
        var kodeper = '';
        var i;
        for(i=0; i<data.length; i++){ kodeper += data[i].kode; }
        var nomor = '{kode_otomatis}';
        var tipe = 'KK';
        var tahun = '{tahun}';
        var kodeperusahaan = kodeper;
        document.getElementById("form1").nokwitansi.value = nomor+'/'+kodeperusahaan+'/'+tipe+'/'+tahun;
      }
    });
  }

    //ubah format nominal
    $(document).on('keyup','.nominal, .nominal',function(){
        var val = $(this).val();
        $(this).val( formatRp(String(val)) );
    })

    //simpan data
    function save() {
        var form = $('#form1')[0];
        var formData = new FormData(form);
        $.ajax({
            url: base_url + 'save',
            dataType: 'json',
            method: 'post',
            data: formData,
            contentType: false,
            processData: false,
            beforeSend: function() {
                pageBlock();
            },
            afterSend: function() {
                unpageBlock();
            },
            success: function(data) {
                if(data.status == 'success') {
                    swal("Berhasil!", "Berhasil Menambah Data", "success");
                    redirect(base_url);
                } else {
                    swal("Gagal!", "Gagal Menambah Data", "error");
                }
            },
            error: function() {
                swal("Gagal!", "Internal Server Error", "error");
            }
        })
    }

    $('#rekening').change(function(){ 
        $.ajax({
            url         : '{site_url}kas_bank/sisaKasBank',
            method      : 'post',
            datatype    : 'json',
            data        : {
                idRekening  : $('#rekening').val()
            },
            success     : function (data) {
                data1=data.toString().replaceAll(".",",");
                
                $('#sisaKasBank').val(formatRp(String(data1)));
            }
        })
    }); 
    function formatRp(angka, prefix){
        var number_string = angka.replace(/[.]/g, '').toString(),
        split           = number_string.split(','),
        sisa             = split[0].length % 3,
        rupiah             = split[0].substr(0, sisa),
        ribuan             = split[0].substr(sisa).match(/\d{3}/gi);
    // tambahkan titik jika yang di input sudah menjadi angka satuan ribuan
        if(ribuan){
            separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }
        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        return prefix == undefined ? rupiah : (rupiah ? 'Rp. ' + rupiah : '');
      }
</script>