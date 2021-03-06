<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><?= $title; ?></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url(); ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('anggaran_belanja'); ?>">{title}</a></li>
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
                <div class="col-md-12">
                    <form action="javascript:save()" id="formSaldoAwal">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Tambah Saldo Awal Neraca</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label>Nomor :</label>
                                            <input type="hidden" name="idSaldoAwal" value="<?= $this->uri->segment(3); ?>">
                                            <input type="text" class="form-control" placeholder="(Auto)" name="nomor" readonly value="<?= $saldoAwal['no']; ?>">
                                        </div>
                                    </div>
									<div class="col-6">
										<div class="form-group">
                                            <label></label>
                                            <div class="form-check">
												<input class="form-check-input" type="checkbox" value="" id="defaultCheck1" checked>
												<label class="form-check-label" for="defaultCheck1">
													Penomoran Otomatis
												</label>
											</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
									<div class="col-6">
                                        <div class="form-group">
                                            <label>Tanggal :</label>
                                            <input type="date" class="form-control" placeholder="Tanggal" name="tanggal" required value="<?= $saldoAwal['tanggal']; ?>">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label>Perusahaan :</label>
                                            <?php
                                                if ($this->session->userid !== '1') { ?>
                                                    <input type="hidden" name="perusahaan" value="<?= $this->session->idperusahaan; ?>">
                                                    <input type="text" class="form-control" value="<?= $this->session->perusahaan; ?>" disabled>
                                                <?php } else { ?>
                                                    <select class="form-control perusahaan" name="perusahaan" style="width: 100%;" id="perusahaan"></select>
                                                <?php }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label>Keterangan :</label>
                                            <input type="text" class="form-control" placeholder="Keterangan" name="keterangan" required value="<?= $saldoAwal['keterangan']; ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Rincian Saldo Awal</h3>
                            </div>
							<div class="card-header">
							<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">
								+ Tambah Akun
							</button>

							<!-- Modal -->
							<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
								<div class="modal-dialog modal-lg" role="document">
									<div class="modal-content">
									<div class="modal-header">
										<h5 class="modal-title" id="exampleModalLabel">Pilih Akun</h5>
										<button type="button" class="close" data-dismiss="modal" aria-label="Close">
										<span aria-hidden="true">&times;</span>
										</button>
									</div>
									<div class="modal-body">
                                        <table class="table table-striped table-borderless table-hover" id="tabelNoAkun">
                                            <thead>
                                                <tr class="table-active">
                                                    <th scope="col">#</th>
                                                    <th scope="col">Kode Akun</th>
                                                    <th scope="col">Nama akun</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
									</div>
									<div class="modal-footer">
										<button type="button" class="btn btn-primary" data-dismiss="modal">Save changes</button>
									</div>
									</div>
								</div>
							</div>
                            </div>
                            <div class="card-body">
								<div class="row">
									<div class="col-12">
                                        <table class="table table-striped table-borderless table-hover" id="tabelRincian">
                                            <thead>
                                                <tr class="table-active">
                                                    <th scope="col" width="30%">Kode Akun</th>
                                                    <th scope="col" width="30%">Nama akun</th>
                                                    <th scope="col" width="20%" class="text-center">Debit</th>
													<th scope="col" width="20%" class="text-center">Kredit</th>
                                                </tr>
                                            </thead>
                                            <tbody id="rincianAkun"></tbody>
                                            <tfoot>
                                                <tr>
                                                    <th colspan="2" class="text-right">Total :</th>
                                                    <th class="text-right" id="totalDebit">Rp. 0,00</th>
                                                    <th class="text-right" id="totalKredit">Rp. 0,00</th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
								</div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-info"><i class="fas fa-save"></i> Simpan</button>
                            </div>
                        </div>
                    </form>
                    <!--/.col (left) -->
                <!--/.col (right) -->
                </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>
<script>
    var base_url        = '{site_url}saldo_awal/';
    var detailSaldoAwal = []; 

	$(document).ready(function() {
        if ('<?= $this->session->userid; ?>' == 1) {
            var idPerusahaan    = '<?= $saldoAwal['perusahaan']; ?>'; 
        } else {
            var idperusahaan    = '<?= $this->session->perusahaan; ?>';
            $('#perusahaan').attr('readonly'); 
        }
        ajax_select({
            id			: '#perusahaan',
            url			: '{site_url}perusahaan/select2',
            selected	: {
                id	: idPerusahaan
            }
        });
        
        $.ajax({
            url     : base_url + 'getDetailSaldoAwal',
            type    : 'post',
            data    : {
                idSaldoAwal : '<?= $this->uri->segment(3); ?>'
            },
            success : function (response) {
                // console.log(response);
                data1   = 0;
                data2   = 0;
                response.forEach(element => {
                    detailSaldoAwal.push(element.idakun);
                    $('#rincianAkun').append(
                        `<tr idAkun="${element.idakun}">
                            <td>
                                <input type="hidden" value="${element.idakun}" name="idAkun[]">
                                ${element.noAkun}
                            </td>
                            <td>${element.namaakun}</td>
                            <td>
                                <input type="text" name="debit[]" onkeyup="nilai(this), hitung('debit')" class="form-control" value="${formatRp(element.debet)}">
                            </td>
                            <td>
                                <input type="text" name="kredit[]" onkeyup="nilai(this), hitung('kredit')" class="form-control" value="${formatRp(element.kredit)}">
                            </td>
                        </tr>`
                    )
                    data1   += parseFloat(element.debet);
                    data2   += parseFloat(element.kredit);
                });
                $('#totalDebit').html(formatRp(data1.toFixed(2)));
                $('#totalKredit').html(formatRp(data2.toFixed(2)));
            }
        })

        $('#tabelNoAkun').DataTable({
            ajax: {
                url     : '{site_url}noakun/index_datatable/123',
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
                    data    : 'idakun',
                    render  : function(data,type,row) {
                        if (detailSaldoAwal.includes(data)) {
                            var checked = 'checked';
                        } else {
                            var checked = '';
                        }
                        return `
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="" id="defaultCheck1" onchange="tambahAkun(this)" idAkun="${data}" noAkun="${row.akunno}" namaAkun="${row.namaakun}" ${checked}>
                            </div>`;
                    }
                },
                {data: 'akunno'},
                {data: 'namaakun'},
            ]
        });
    })

    

    function tambahAkun(elemen) {
        var idAkun      = $(elemen).attr('idAkun');
        var noAkun      = $(elemen).attr('noAkun');
        var namaAkun    = $(elemen).attr('namaAkun');
        var status      = $(elemen).is(':checked');
        if (status) {
            $('#rincianAkun').append(
                `<tr idAkun="${idAkun}">
                    <td>
                        <input type="hidden" value="${idAkun}" name="idAkun[]">
                        ${noAkun}
                    </td>
                    <td>${namaAkun}</td>
                    <td>
                        <input type="text" name="debit[]" onkeyup="nilai(this), hitung('debit')" class="form-control">
                    </td>
                    <td>
                        <input type="text" name="kredit[]" onkeyup="nilai(this), hitung('kredit')" class="form-control">
                    </td>
                </tr>`
            )
        } else {
            $(`tr[idAkun = ${idAkun}]`).remove();
        }
    }

    function nilai(elemen) {
        var nilai   = $(elemen).val();
        $(elemen).val(formatRp(String(nilai)));
    }

    function hitung(jenis) {
        switch (jenis) {
            case 'debit':
                data    = new FormData($('#formSaldoAwal')[0]);
                data0   = data.getAll('debit[]');
                data1   = 0;
                data0.forEach(element => {
                    element1 = element.replaceAll(".", "");
                    data1   += parseFloat(element1.replaceAll(",", "."));
                });
                dataoke=data1.toFixed(2).toString();
                dataoke2=dataoke.replace(".",",");
                $('#totalDebit').html(formatRp(String(dataoke2)));
                break;
            case 'kredit':
                data    = new FormData($('#formSaldoAwal')[0]);
                data0   = data.getAll('kredit[]');
                data1   = 0;
                data0.forEach(element => {
                    element1 = element.replaceAll(".", "");
                    data1   += parseFloat(element1.replaceAll(",", "."));
                });
                dataoke=data1.toFixed(2).toString();
                dataoke2=dataoke.replace(".",",");
                
                $('#totalKredit').html(formatRp(String(dataoke2)));
                break;
        
            default:
                break;
        }
    }
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
    function save() {
        var form        = $('#formSaldoAwal')[0];
        var formData    = new FormData(form);
        var totalDebit  = $('#totalDebit').html();
        var totalKredit = $('#totalKredit').html();
        if(totalDebit !== totalKredit) {
            swal("Gagal!", "Angka Debit dan Kredit Tidak Sama", "error");
        } else {
            $.ajax({
                url: base_url + 'save/' + '<?= $this->uri->segment(3); ?>',
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
    }
</script>