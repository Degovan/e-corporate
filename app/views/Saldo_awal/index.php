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
            <div class="card-header">
              <a href="{site_url}saldo_awal/tambah" class="btn btn-primary">+ <?php echo lang('add_new') ?></a>
            </div>
            <div class="card-body">
              <div class="table-responsive">
								<table class="table table-xs table-striped table-borderless table-hover index_datatable" onload="return data()">
									<thead>
										<tr class="table-active">
                      <th>Nomor</th>
                      <th>Tanggal</th>
                      <th>Perusahaan</th>
                      <th>Keterangan</th>
                      <th>Debit</th>
                      <th>Kredit</th>
                      <th>Opsi</th>
										</tr>
									</thead>
									<tbody></tbody>
									<tfoot>
										<tr class="table-active">
                      <th colspan="4" class="text-left"><B>Total<B></th>
                      <th></th>
                      <th></th>
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
<script>
  var base_url = '{site_url}saldo_awal/';
  var table = $('.index_datatable').DataTable({
		ajax: {
			url     : base_url + 'index_datatable',
			type    : 'post',
		},
		stateSave: true,
		autoWidth: false,
        dom: '<"datatable-header"fl><"datatable-scroll"t><"datatable-footer"p>',
        language: {
            search: '<span></span> _INPUT_',
            searchPlaceholder: 'Type to filter...',
        },
        columns: [
            {
              data    : 'no',
              render  : function (data, type, row) {
                return `<span class="btn btn-sm btn-info">${data}</span>`;
              }
            },
            {data : 'tanggal'},
            {data : 'nama_perusahaan'},
            {data : 'keterangan'},
            {
              data  : "debit",
              render: function(data,type,row) {
                return formatRp(data) ;
              }
            },
            {
              data  : "kredit",
              render: function(data,type,row) {
                return formatRp(data);
              }
            },
            {
              render: function(data, type, row) {
                var aksi = `
                  <div class="list-icons"> 
                    <div class="dropdown"> 
                      <a href="#" class="list-icons-item" data-toggle="dropdown"> <i class="fas fa-bars"></i> </a> 
                      <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item" href="` + base_url + `edit/` + row.idSaldoAwal + `"><i class="fas fa-pencil-alt"></i> Edit</a>
                        <a href="javascript:deleteData('` + row.idSaldoAwal + `')" class="dropdown-item delete"><i class="fas fa-trash"></i> <?php echo lang('delete') ?></a>
                      </div> 
                    </div> 
                  </div>`;
                return aksi;
              }
            }
        ],
        "footerCallback": function ( row, data, start, end, display ) {
          var api = this.api(), data;

          // Remove the formatting to get integer data for summation
          var intVal = function ( i ) {
            return typeof i === 'string' ?
              i.replace(/(Rp.|,00)/g, '')*1 :
              typeof i === 'number' ?
                i : 0;
          };

          // Total over all pages
          totalDebit = api
            .column( 4 )
            .data()
            .reduce( function (a, b) {
              return intVal(a) + intVal(b);
            }, 0 );

          totalKredit = api
            .column( 5 )
            .data()
            .reduce( function (a, b) {
              return intVal(a) + intVal(b);
            }, 0 );

          // Update footer
          $( api.column( 4 ).footer() ).html(
            formatRp(String(totalDebit))
          );
          $( api.column( 5 ).footer() ).html(
            formatRp(String(totalKredit))
          );
        }
      });
      function formatRp(bilangan){
        var	number_string = bilangan.toString(),
        split	          = number_string.split('.'),
        sisa 	          = split[0].length % 3,
        rupiah 	        = split[0].substr(0, sisa),
        ribuan 	        = split[0].substr(sisa).match(/\d{1,3}/gi);
          
      if (ribuan) {
        separator = sisa ? '.' : '';
        rupiah    += separator + ribuan.join('.');
      }
      rupiah  = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
      return rupiah;
      }
</script>