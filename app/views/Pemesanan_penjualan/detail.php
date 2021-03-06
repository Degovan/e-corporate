
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
                        <li class="breadcrumb-item"><a href="{site_url}Pemesanan_penjualan">Penjualan</a></li>
                        <li class="breadcrumb-item active">{title}</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- SELECT2 EXAMPLE -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Detail {title}</h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6 text-left">
                        </div>
                        <div class="col-md-6 text-right">
                            <?php if ($status == '4'): ?>
                                <h1 class="text-danger font-weight-bold text-uppercase"><?php echo lang('pending') ?></h1>
                            <?php elseif ($status == '5'): ?>
                                <h1 class="text-primary font-weight-bold text-uppercase"><?php echo lang('Validasi') ?></h1>
                            <?php elseif ($status == '2'): ?>
                                <h1 class="text-warning font-weight-bold text-uppercase"><?php echo lang('partial') ?></h1>
                            <?php  elseif ($status == '3'): ?>
                                <h1 class="text-success font-weight-bold text-uppercase"><?php echo lang('done') ?></h1>
                            <?php endif ?>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <td><?php echo lang('notrans') ?></td>
                                        <td class="font-weight-bold">{notrans}</td>
                                    </tr>
                                    <tr>
                                        <td><?php echo lang('date') ?></td>
                                        <td class="font-weight-bold">{tanggal}</td>
                                    </tr>
                                    
                                        <tr>
                                            <td><?php echo lang('supplier') ?></td>
                                            <td class="font-weight-bold"><?php echo $kontak['nama'] ?></td>
                                        </tr>
                                    <?php if (($jenis_barang == 'barang_dagangan') OR ($jenis_pembelian == 'barang_dan_jasa')): ?>
                                        <tr>
                                            <td><?php echo lang('warehouse') ?></td>
                                            <td class="font-weight-bold"><?php echo $gudang['nama'] ?></td>
                                        </tr>
                                    <?php endif?>
                                    <tr>
                                        <td><?php echo lang('note') ?></td>
                                        <td class="font-weight-bold">{catatan}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <td><?php echo lang('subtotal') ?></td>
                                        <td class="text-right font-weight-bold"><?= number_format($subtotal,0,',','.'); ?></td>
                                    </tr>
                                    <tr>
                                        <td><?php echo lang('discount') ?></td>
                                        <td class="text-right font-weight-bold"><?= number_format($diskon,0,',','.'); ?></td>
                                    </tr>
                                    <tr>
                                        <td><?php echo lang('Pajak') ?></td>
                                        <td class="text-right font-weight-bold"><?= number_format($ppn,0,',','.'); ?></td>
                                    </tr>
                                    <tr>
                                        <td><?php echo lang('Biaya Pengiriman') ?></td>
                                        <td class="text-right font-weight-bold"><?= number_format($biaya_pengiriman,0,',','.'); ?></td>
                                    </tr>
                                    <tr class="bg-light">
                                        <td><?php echo lang('total') ?></td>
                                        <td class="text-right font-weight-bold"><?= number_format($total,0,',','.'); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table class="table table-xs table-striped table-borderless table-hover">
                                    <thead>
                                        <tr class="table-active">
                                            <th><?php echo lang('item') ?></th>
                                            <th class="text-right"><?php echo lang('price') ?></th>
                                            <th class="text-right"><?php echo lang('qty') ?></th>
                                            <th class="text-right"><?php echo lang('subtotal') ?></th>
                                            <th class="text-right"><?php echo lang('discount') ?></th>
                                            <th class="text-right"><?php echo lang('Pajak') ?></th>
                                            <th class="text-right"><?php echo lang('Biaya Pengiriman') ?></th>
                                            <th class="text-right">No Akun</th>
                                            <th class="text-right"><?php echo lang('total') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $grandtotal = 0; ?>
                                        <?php foreach ($pemesanandetail as $row): ?>
                                            <?php $grandtotal = $row['total'] + $grandtotal ?>
                                            <tr>
                                                <td>
                                                    <?php 
                                                    if ($row['tipe']=='barang'){
                                                        echo $row['item']; 
                                                    }else{
                                                        echo $row['lain_lain'];
                                                    }
                                                    ?>    
                                                </td>
                                                <td class="text-right"><?= number_format($row['harga'],2,',','.'); ?></td>
                                                <td class="text-right"><?= number_format($row['jumlah']) ?></td>
                                                <td class="text-right"><?= number_format($row['subtotal'],2,',','.'); ?></td>
                                                <td class="text-right"><?= number_format($row['diskon']) ?>%</td>
                                                <td class="text-right">
                                                    <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#modalPajak<?= $row['id']; ?>" title="Detail Pajak">
                                                        <i class="fas fa-balance-scale"></i>
                                                    </button>
                                                    <div class="modal fade" id="modalPajak<?= $row['id']; ?>">
                                                        <div class="modal-dialog modal-xl">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h4 class="modal-title">Pajak</h4>
                                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                        <span aria-hidden="true">&times;</span>
                                                                    </button>
                                                                </div>
                                                                <form id="form_pajak" action="javascript:total_pajak('', '${no}')" enctype="multipart/form-data" method="POST">
                                                                    <div class="modal-body">
                                                                        <div class="table-responsive">
                                                                            <table class="table table-xs table-striped table-borderless table-hover index_datatable" style="width:100%" id="pajak">
                                                                                <thead>
                                                                                    <tr class="table-active">
                                                                                        <th>Nama Pajak</th>
                                                                                        <th>Kode Akun</th>
                                                                                        <th>Nama Akun</th>
                                                                                        <th>Nominal</th>
                                                                                    </tr>
                                                                                </thead>
                                                                                <tbody id="isi_tbody_pajak">
                                                                                    <?php
                                                                                        if ($row['pajak']) {
                                                                                            foreach ($row['pajak'] as $key) { ?>
                                                                                                <tr>
                                                                                                    <td><?= $key['nama_pajak']; ?></td>
                                                                                                    <td><?= $key['akunno']; ?></td>
                                                                                                    <td><?= $key['namaakun']; ?></td>
                                                                                                    <td>
                                                                                                        <?php 
                                                                                                            switch ($key['pengurangan']) {
                                                                                                                case '0':
                                                                                                                    echo number_format($key['nominal'],2,',','.');
                                                                                                                    break;
                                                                                                                case '1':
                                                                                                                    echo '-' . number_format($key['nominal'],2,',','.');
                                                                                                                    break;
                                                                                                                
                                                                                                                default:
                                                                                                                    # code...
                                                                                                                    break;
                                                                                                            }
                                                                                                        ?>
                                                                                                    </td>
                                                                                                </tr>
                                                                                            <?php }
                                                                                        }
                                                                                    ?>
                                                                                </tbody>
                                                                            </table>
                                                                        </div>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-right"><?= number_format($row['biaya_pengiriman'],2,',','.') ?></td>
                                                <td class="text-right"><?=  $row['akunno']; ?></td>
                                                <td class="text-right"><?= number_format($row['total'],2,',','.'); ?></td>
                                            </tr>
                                        <?php endforeach ?>
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-active">
                                            <td class="font-weight-bold text-right" colspan="8"><?php echo lang('grand_total') ?></td>
                                            <td class="font-weight-bold text-right"><?= number_format($grandtotal,2,',','.'); ?></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <form action="javascript:save()" id="form">
                        <input type="hidden" name="idpemesanan" value="<?= $this->uri->segment(3); ?>" readonly>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                &nbsp;                  
                            </div>
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-sm-8">
                                        <div class="form-group">
                                            <label><?php echo lang('Uang Muka') ?>:</label>
                                            <input type="text" class="form-control um" name="um" value="<?= number_format(floatval($angsuran['uangmuka']),0,',','.')?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label><?php echo lang('Jumlah Term') ?>:</label>
                                                <input type="number" class="form-control jtem" name="jtem" min="0" max="8" value="<?= $angsuran['jumlahterm'] ?>" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    
                                        <?php for ($i=1; $i <= $angsuran['jumlahterm'] ; $i++) {  ?>
                                        <?php if (($i == 1) || ($i == 5)) { echo '<div class="col-md-6">'; } ?>
                                            <div class="form-group a<?= $i ?>" hidden >
                                                <label><?php echo lang('Term '.$i) ?>:</label>
                                                <input type="text" class="form-control" name="a<?= $i ?>" placeholder="Angsuran <?= $i ?>" 
                                                value="<?php if ($angsuran['a'.$i] != ''){ echo number_format($angsuran['a'.$i],0,',','.'); }?>" <?php if ($angsuran['a'.$i] != ''){ echo 'readonly'; } ?> onkeyup="UbahInputRUpiah(this);SUMTOTAL_UM_Term();">
                                            </div>
                                        <?php if (($i == 4) || ($i == 8) || ($i ==  $angsuran['jumlahterm'])){ echo '</div>'; } ?>
                                        <?php } ?>
                                </div>
                                    <div class="form-group">
                                        <label><?php echo lang('Total Uang Muka + Term') ?>:</label>
                                        <input type="text" class="form-control tum" name="tum" readonly value="<?= number_format($angsuran['total'],0,',','.')?>">
                                    </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="btn-group">
                                <a href="{site_url}Pemesanan_penjualan" class="btn bg-danger"><?php echo lang('cancel') ?></a>
                                <button type="submit" class="btn bg-success"><?php echo lang('save') ?></button>
                            </div>
                        </div>
                    </form>
                </div>
                <!-- /.card-body -->
                <div class="card-footer">          
                </div>
            </div>
        </div>
    </section>
</div>
    <script>
        var base_url = '{site_url}Pemesanan_penjualan/';

        $(document).ready(function(){
            var kontak  = '<?= $kontak['id']; ?>'
            // ajax_select({ id: '.kontakid', url: base_url + 'select2_kontak', selected: { id: null } });
            
            //$.ajax({
                //url: base_url + 'select2_kontak',
                //method: 'get',
                //datatype: 'json',
                //success: function(data) {
                    //isi = "";
                    //for (let index = 0; index < data.length; index++) {
                        //if (kontak == data[index].id) {
                            //isi += `<option value="${data[index].id}" selected>${data[index].text}</option>`
                       // } else {
                            //isi += `<option value="${data[index].id}">${data[index].text}</option>`
                       // }
                    //}
                    //$('.kontakid').append(isi);
                    //$('.kontakid').select2();
                //}
            //})

            //hidden term 1 - 8
            for (var i = 1; i <= 8; i++) {
                $('.a'+i).attr("hidden", true);
            } 

            //menampilkan jumlah term
            var jumlahterm  = $('.jtem').val();
            for (var j = 1; j <= jumlahterm; j++) {
                $('.a'+j).attr("hidden", false);
            }    

        });


        //setting keyup format rupiah
        function UbahInputRUpiah(nama_inputan){
            $(nama_inputan).on('keyup',function(){
                var nilai= $(this).val();
                $(this).val(formatRupiah(String(nilai), 'Rp. '));
            });
        }

        //hitung total uang muka dan term
        function SUMTOTAL_UM_Term(){
            var totalangsuran = 0;
            for (var i = 1; i <= 8; i++) {
                angsuran = $('input[name=a'+i+']').val().replace(/[^,\d]/g, '').toString();
                if (angsuran == ''){
                    totalangsuran = totalangsuran + 0;
                }else{
                    totalangsuran = totalangsuran + parseInt(angsuran);
                }
            } 
            uang_muka = $('input[name=um]').val().replace(/[^,\d]/g, '').toString();
            if (uang_muka == ''){
                hasil_um_term = 0 + parseInt(totalangsuran);
            }else{
                hasil_um_term = parseInt(uang_muka) + parseInt(totalangsuran);
            }
             
            $('input[name=tum]').val(formatRupiah(String(hasil_um_term), 'Rp. ')); 
        }

        $(document).on('change','.kontakid',function(){
            var kontakid    = $(this).val();
            var idpemesanan = '<?= $this->uri->segment(3); ?>';
            $.ajax({
                url: base_url + 'update_kontakid/' + idpemesanan,
                method: 'post',
                datatype: 'json',
                data: {
                    kontakid: kontakid
                },
                beforeSend: function() {
                    pageBlock();
                },
                afterSend: function() {
                    unpageBlock();
                },
                success: function(data) {
                    if(data.status == 'success') {
                        swal("Berhasil!", "Berhasil Mengupdate Data", "success");
                    } else {
                        swal("Gagal!", "Gagal Mengupdate Data", "error");
                    }
                },
                error: function() {
                    swal("Gagal!", "Internal Server Error", "error");
                }
            })
        })

        function save() {
            var form = $('#form')[0];
            var formData = new FormData(form);
            $.ajax({
                url: base_url + 'tambah_angsuran',
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
                        swal("Berhasil!", "Berhasil Mengupdate Data", "success");
                        redirect(base_url);
                    } else {
                        swal("Gagal!", "Gagal Mengupdate Data", "error");
                    }
                },
                error: function() {
                    swal("Gagal!", "Internal Server Error", "error");
                }
            })
        }
    </script>