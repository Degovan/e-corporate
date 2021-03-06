10

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
              <li class="breadcrumb-item"><a href="<?= base_url('pemesanan_penjualan'); ?>">Penjualan</a></li>
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
              <div class="card-header">
                <a href="{site_url}Faktur_penjualan/create" class="btn btn-primary">+ <?php echo lang('add_new') ?></a>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-xs table-striped table-borderless table-hover index_datatable">
                    <thead>
                      <tr class="table-active">
                        <th>ID</th>
                        <th><?php echo lang('notrans') ?></th>
                        <th><?php echo lang('Surat Jalan') ?></th>
                        <th>Nama Perusahaan</th>
                        <th><?php echo lang('Departemen') ?></th>
                        <th>Cabang</th>
                        <th><?php echo lang('note') ?></th>
                        <th><?php echo lang('date') ?></th>
                        <th><?php echo lang('date') ?>J/T</th>
                        <th><?php echo lang('supplier') ?></th>
                        <th><?php echo lang('Cara Bayar') ?></th>
                        <th><?php echo lang('warehouse') ?></th>
                        <th><?php echo lang('total') ?></th>
                        <th><?php echo lang('status') ?></th>
                        <th><?php echo lang('Ref KasBank') ?></th>
                        <th><?php echo lang('Setup Jurnal') ?></th>
                        <th><?php echo lang('Aksi') ?></th>
                      </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                      <tr class="table-active">
                        <th colspan="12" class="text-right"><?php echo lang('Total Faktur Penjualan') ?></th>
                        <th class="text-center"><div id="total"></div></th>
                        <th class="text-center"></th>
                        <th class="text-center"></th>
                        <th></th>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
<script type="text/javascript">
	var base_url = '{site_url}Faktur_penjualan/';
	var table = $('.index_datatable').DataTable({
		ajax: {
			url: base_url + 'index_datatable',
			type: 'post',
		},
		pageLength: 20,
		stateSave: false,
		autoWidth: false,
		order: [ [0, 'desc'] ],
        dom: '<"datatable-header"fl><"datatable-scroll"t><"datatable-footer"p>',
        language: {
            search: '<span></span> _INPUT_',
            searchPlaceholder: 'Type to filter...',
        },
    columns: [
      {data: 'id', visible: false},
      {
        data: 'notrans',
        render: function(data,type,row) {
          var link = base_url + 'detail/' + row.id;
          return '<a href="'+link+'" class="btn btn-sm btn-info">'+data+'</a>';
        }
      },
      {data: 'nomorsuratjalan'},
      {data: 'nama_perusahaan'},
      {data: 'namadepartemen'},
      {data: 'cabang'},
      {data: 'catatan', orderable: false},
      {data: 'tanggal'},
      {data: 'tanggaltempo'},
      {data: 'supplier'},
      {data: 'cara_pembayaran'},
      {data: 'gudang'},
      {
        data: 'total', className: 'text-right',
        render: function(data) {
          return formatRupiah(data) + ',00';
        }
      },
      {
        data: 'status', className: 'text-center',
        render: function(data) {
          if(data == '3') return '<span class="badge badge-success"><?php echo lang('done') ?></sapan>';
          else if(data == '2') return '<span class="badge badge-warning"><?php echo lang('partial') ?></sapan>';
          else if(data == '1') return '<span class="badge badge-danger"><?php echo lang('pending') ?></sapan>';
        }
      },
      {data : 'nomor_kas_bank'},
      {
        data      : 'kodeJurnal',
        className : 'text-center'
      },
      {
        data  : 'id',
        width: 40, 
        orderable: false,
        render: function(data,type,row) { 
          var tombol = '';
          if (row.stts_kas != 1){
              tombol += ` <a href="`+base_url+`edit/`+data+`" class="dropdown-item"><i class="fas fa-pencil-alt"></i> Ubah</a>
                    <a href="javascript:deleteData('` + data+ `')" class="dropdown-item delete"><i class="fas fa-trash"></i> Hapus</a>`;
          }
          if (row.status == 1) {
            var tombolValidasi  = `<button type="button" onclick="aksiData('validasi', '${data}')" class="dropdown-item text-success"><i class="fas fa-check"></i> Validasi</button>`;
          } else {
            var tombolValidasi  = `<button type="button" onclick="aksiData('validasi', '${data}')" class="dropdown-item text-success"><i class="fas fa-times"></i> Batal Validasi</button>`;
          }
          var aksi = `
              <div class="list-icons"> 
                <div class="dropdown"> 
                  <a href="#" class="list-icons-item" data-toggle="dropdown"> <i class="fas fa-bars"></i> </a> 
                  <div class="dropdown-menu dropdown-menu-right">
                    `+tombol+`
                    <button class="btn btn-success btn-sm dropdown-item text-warning" onclick="printData(this)" idFakturPenjualan="${data}"><i class="fas fa-print"></i> Cetak</button>
                    <form method="post" id="formAksi${data}">
                      <input type="hidden" value="${data}" name="id${data}">
                      <input type="hidden" value="${row.status}" name="status${data}">`+
                      tombolValidasi
                    + `</form>
                  </div> 
                </div> 
              </div>`;
          return aksi;
        }
      },

    ],
    footerCallback: function ( row, data, start, end, display ) {

        var api = this.api(), data;
        var intVal = function ( i ) {
            return typeof i === 'string' ?
                i.replace(/[\Rp.]/g, '').replace(/,00/g, '')*1 :
                typeof i === 'number' ?
                    i : 0;
        };

        total = api.column(12).data().reduce( function (a, b) {
            return intVal(a) + intVal(b); 
        }, 0 );
        $('#total').html(formatRupiah(String(total)) + ',00');
    }
	});

	function aksiData(jenis, data) {
    switch (jenis) {
      case 'validasi':
        var warning = 'Anda yakin akan memvalidasi data?';
        var url     = base_url + 'validasi';
        break;
    
      default:
        break;
    }
		swal(warning, {
      buttons: {
        cancel: "Batal",
        catch: {
        text: "Ya, Yakin",
        value: "ya",
        },
      },
		})
		.then((value) => {
			switch (value) {
				case "ya":
          var form    = new FormData($(`#formAksi${data}`)[0]);
          var id      = form.get(`id${data}`);
          console.log(id);
          var status  = form.get(`status${data}`);
          $.ajax({
              url     : base_url + 'validasi',
              method  : 'post',
              data    : {
                  'id'      : id,
                  'status'  : status
              },
              beforeSend: function() {
                  pageBlock();
              },
              afterSend: function() {
                  unpageBlock();
              },
              success: function(data) {
                  if(data.status == 'success') {
                      swal("Berhasil!", data.pesan, "success");
                      setTimeout(function() { table.ajax.reload() }, 100);
                  } else {
                      swal("Gagal!", data.pesan, "error");
                  }
              },
              error: function() {
                  swal("Gagal!", "Internal Server Error!", "error");
              }
          })
          break;
			}
		});
	}

  function printData(elemen) {
		id	= $(elemen).attr('idFakturPenjualan');
		swal("Pilih format?", {
			buttons: {
				cancel	: "Batal",
				pdf		: {
					text	: "PDF",
					value	: "pdf",
				},
				excel	: {
					text	: "Excel",
					value	: "excel",
				}
			},
		})
		.then((value) => {
			if (value == 'pdf' || value == 'excel') {
				redirect(base_url + 'print/' + value + '/' + id);
			}
		});
	}

	function deleteData(id) {
		swal("Anda yakin akan menghapus data?", {
			buttons: {
				cancel: "Batal",
				catch: {
				text: "Ya, Yakin",
				value: "ya",
				},
			},
		})
		.then((value) => {
			switch (value) {
				case "ya":
				$.ajax({
					url: base_url + 'delete/' + id,
					beforeSend: function() {
					pageBlock();
					},
					afterSend: function() {
					unpageBlock();
					},
					success: function(data) {
					if(data.status == 'success') {
						swal("Berhasil!", "Data Berhasil Dihapus!", "success");
						setTimeout(function() { table.ajax.reload() }, 100);
					} else {
						swal("Gagal!", "Pikachu was caught!", "error");
					}
					},
					error: function() {
					swal("Gagal!", "Internal Server Error!", "error");
					}
				})
				break;
			}
		});
	}
</script>
