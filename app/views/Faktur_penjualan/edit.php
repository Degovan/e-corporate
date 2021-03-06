
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
              <li class="breadcrumb-item"><a href="{site_url}Pemesanan_penjualan/">Penjualan</a></li>
              <li class="breadcrumb-item"><a href="{site_url}Faktur_penjualan/">Faktur</a></li>
              <li class="breadcrumb-item active">Ubah</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid --> 
    </section>
 
    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <!-- SELECT2 EXAMPLE -->
        <div class="card card-danger">
          <div class="card-header">
            <h3 class="card-title">{subtitle} {title}</h3>

            <div class="card-tools">
              <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
               <a href="{site_url}Faktur_penjualan" class="btn btn-tool"><i class="fas fa-times"></i></a>
            </div>
          </div>
          <!-- /.card-header -->
          <div class="card-body">
             <form action="javascript:update()" id="form1">
                <div class="row">
                    <div class="col-md-3">
                        <input type="hidden" name="statusauto" value="0">
                        <input type="hidden" class="fakturid" name="fakturid" value="{id}">
                        <input type="hidden" class="idperusahaan" name="idperusahaan" value="{idperusahaan}">
                        <input type="hidden" class="pengirimanid" name="pengirimanid" value="{pengirimanid}">
                        <input type="hidden" class="pemesananid" name="pemesananid" value="<?php echo $pengiriman['pemesananid']; ?>">
                        <div class="form-group">
                            <label><?php echo lang('notrans') ?>:</label>
                            <input type="text" class="form-control" name="notrans" readonly placeholder="AUTO">
                        </div>
                        <div class="form-group">
                            <label><?php echo lang('supplier') ?>:</label>
                            <select class="form-control kontakid" name="kontakid" disabled></select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label><?php echo lang('date') ?>:</label>
                            <div class="input-group"> 
                                <input type="date" id="tanggal" class="form-control datepicker" name="tanggal" value="{tanggal}" readonly>
                            </div>
                        </div>
                        <div class="form-group">
                            <label><?php echo lang('warehouse') ?>:</label>
                            <select class="form-control gudangid" name="gudangid" disabled>
                                <option value=""></option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label><?php echo lang('date') ?> Jatuh Tempo:</label>
                            <div class="input-group"> 
                                <input type="date" id="tanggalJT"class="form-control datepicker" name="tanggalJT" required value="{tanggaltempo}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label><?php echo lang('Nomor Surat Jalan') ?>:</label>
                            <input type="text" id="nomorsuratjalan" class="form-control nomorsuratjalan" name="nomorsuratjalan" value="{nomorsuratjalan}" readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label><?php echo lang('Cara Bayar') ?>:</label>
                            <input type="text" class="form-control carabayar" name="carabayar" value="{carabayar}" required readonly></select>
                        </div>
                        <div class="form-group">
                            <label><?php echo lang('Departemen') ?>:</label>
                            <select class="form-control departemen" name="departemen" disabled></select>
                        </div>
                    </div>
                     <div class="col-md-3">
                        <div class="form-group">
                            <label><?php echo lang('Rekening') ?>:</label>
                            <select class="form-control rekening" name="rekening" value="{rekening}" disabled required></select>
                        </div>
                    </div>
                </div>
                <div class="mb-3 mt-3 table-responsive">
                    <div class="mt-3 mb-3">
                        <button type="button" class="btn btn-sm btn-primary btn_add_detail"><?php echo lang('add_new') ?></button>
                        <button type="button" class="btn btn-sm btn-primary btn_add_detail_budgetevent"><?php echo lang('Budget Event') ?></button>
                    </div>
                    <table class="table table-bordered" id="table_detail_item" width="100%">
                        <thead class="{bg_header}">
                            <tr>
                                <th>ID</th>
                                <th><?php echo lang('item') ?></th>
                                <th class="text-right"><?php echo lang('price') ?></th>
                                <th class="text-right"><?php echo lang('qty') ?></th>
                                <th class="text-right"><?php echo lang('subtotal') ?></th>
                                <th class="text-right"><?php echo lang('discount') ?></th>
                                <th class="text-right"><?php echo lang('Pajak') ?></th>
                                <th class="text-right"><?php echo lang('Biaya Pengiriman') ?></th>
                                <th class="text-right"><?php echo lang('total') ?></th>
                                <th class="text-center"><?php echo lang('tipe') ?></th>
                                <th>ID PENJUAL DETAIL</th>
                            </tr>
                        </thead>
                        <tbody> </tbody>
                        <tfoot class="bg-light">
                            <tr>
                                <th>ID</th>
                                <th colspan="6">&nbsp;</th>
                                <th class="text-right"><?php echo lang('total') ?></th>
                                <th class="text-center"><div id="total"></div></th>
                            </tr>
                        </tfoot>
                    </table>

                    <table class="table table-bordered" id="table_detail_budgetevent" hidden width="100%">
                        <thead class="{bg_header}">
                            <tr>
                                <th>ID</th>
                                <th><?php echo lang('item') ?></th>
                                <th class="text-right"><?php echo lang('price') ?></th>
                                <th class="text-right"><?php echo lang('qty') ?></th>
                                <th class="text-right"><?php echo lang('subtotal') ?></th>
                                <th class="text-right"><?php echo lang('discount') ?></th>
                                <th class="text-right"><?php echo lang('Pajak') ?></th>
                                <th class="text-right"><?php echo lang('Biaya Pengiriman') ?></th>
                                <th class="text-right"><?php echo lang('total') ?></th>
                                <th class="text-center"><?php echo lang('tipe') ?></th>
                            </tr>
                        </thead>
                        <tbody> </tbody>
                        <tfoot class="bg-light">
                            <tr>
                                <th>ID</th>
                                <th colspan="6">&nbsp;</th>
                                <th class="text-right"><?php echo lang('total') ?></th>
                                <th class="text-center"><div id="total_budgetevent"></div></th>
                            </tr>
                        </tfoot>
                    </table>

                </div>
                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><?php echo lang('note') ?>:</label>
                                            <textarea class="form-control catatan" name="catatan" rows="6"></textarea>
                                        </div>                       
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row">
                                            <div class="col-sm-8">
                                                <div class="form-group">
                                                    <label><?php echo lang('Uang Muka') ?>:</label>
                                                    <input type="text" class="form-control um" name="um" readonly>
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label><?php echo lang('Tanggal uang muka') ?>:</label>
                                                    <input type="date" class="form-control tanggaluangmuka" name="tanggaluangmuka" readonly>
                                                </div>
                                            </div>
                                        </div>
                                       
                                            <div class="form-group col-sm-4">
                                                <label><?php echo lang('Jumlah Term') ?>:</label>
                                                <input type="number" class="form-control jtem" name="jtem" readonly>
                                            </div>
                                        
                                        <div class="form-group">
                                            <label><?php echo lang('Total Uang Muka + Term') ?>:</label>
                                            <input type="text" class="form-control tum" name="tum" readonly>
                                        </div>
                                    </div>
                                    <input type="hidden" name="detail_array" id="detail_array">
                                </div>
                <input type="hidden" name="detail_array" id="detail_array">
                <div class="text-right">
                    <div class="btn-group">
                        <a href="{site_url}Faktur_penjualan" class="btn bg-danger"><?php echo lang('cancel') ?></a>
                        <button type="submit" class="btn bg-success"><?php echo lang('save') ?></button>
                    </div>
                </div>
            </form>
          </div>
        </div>
      </div>
    </div>

<div id="modal_add_detail" class="modal fade">
    <div class="modal-dialog modal-lg">
        <form action="javascript:save_detail()" id="form2">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title"><?php echo lang('add_new') ?></h5>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="rowindex">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label><?php echo lang('nomor pengiriman') ?>:</label>
                                <select class="form-control nomor_pengiriman" name="nomor_pengiriman" required style="width:100%"></select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <div class="btn-group">
                        <button type="button" class="btn btn-danger" data-dismiss="modal"><?php echo lang('cancel') ?></button>
                        <button type="submit" class="btn btn-success"><?php echo lang('save') ?></button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>


<script type="text/javascript">
    var base_url = '{site_url}Faktur_penjualan/';
    $.fn.dataTable.Api.register( 'hasValue()' , function(value) {
        return this .data() .toArray() .toString() .toLowerCase() .split(',') .indexOf(value.toString().toLowerCase())>-1
    })

    var table_detail_item = $('#table_detail_item').DataTable({
        sort: false,
        info: false,
        searching: false,
        paging: false,
        columnDefs: [
            {targets: [0,9,10], visible: false},
            {targets: [2,7,4], className: 'text-right'},
            {targets: [3,5,6], className: 'text-center'},
        ],
        footerCallback: function ( row, data, start, end, display ) {

            var api = this.api(), data;
            var intVal = function ( i ) {
                return typeof i === 'string' ?
                    i.replace(/[\Rp.]/g, '').replace(/,00/g, '')*1 :
                    typeof i === 'number' ?
                        i : 0;
            };

            total = api.column(8).data().reduce( function (a, b) {
                return intVal(a) + intVal(b); 
            }, 0 );
           
            $('#total').html(formatRupiah(String(total), 'Rp. '));
        }
    })

    var table_detail_budgetevent = $('#table_detail_budgetevent').DataTable({
        sort: false,
        info: false,
        searching: false,
        paging: false,
        columnDefs: [
            {targets: [0,9], visible: false},
            {targets: [2,7,4,8], className: 'text-right'},
            {targets: [1], width: 100 },
            {targets: [3,5,6], className: 'text-center'},
        ],
        footerCallback: function ( row, data, start, end, display ) {

            var api = this.api(), data;
            var intVal = function ( i ) {
                return typeof i === 'string' ?
                    i.replace(/[\Rp.]/g, '').replace(/,00/g, '')*1 :
                    typeof i === 'number' ?
                        i : 0;
            };

            total = api.column(8).data().reduce( function (a, b) {
                return intVal(a) + intVal(b); 
            }, 0 );
           
            $('#total_budgetevent').html(formatRupiah(String(total), 'Rp. '));
        }
    })

    $(document).ready(function(){
        table_detail_item.clear().draw();
        table_detail_budgetevent.clear().draw();
        $(".rekening").attr("disabled", false);
        //mengisi combobox kontak
        ajax_select({ id: '.kontakid', url: base_url + 'select2_kontak', selected: { id: '{kontakid}' } });
        ajax_select({ id: '.gudangid', url: base_url + 'select2_gudang', selected: { id: '{gudangid}' } });
        ajax_select({ id: '.departemen', url: base_url + 'select2_departemen', selected: { id: '{departemen}' } });
        ajax_select({ id: '.nomor_pengiriman', url: base_url + 'select2_nomor_pengiriman/', selected: { id: null }});
        var idperusahaan = $("input.idperusahaan").val();
        // console.log(idperusahaan, '309_idperusahaan')
        // ajax_select({ id: '.rekening', url: base_url + 'select2_rekening/{rekening}', selected: { id: '{rekening}' }});
        ajax_select({ id: '.rekening', url: base_url + 'select2_rekening/'+ idperusahaan, selected: { id: '{rekening}' } });

        var carabayar = $('.carabayar').val(); //{carabayar}
        if (carabayar == 'cash'){
            document.getElementById("tanggalJT").readOnly = true;
        }else{
            document.getElementById("tanggalJT").readOnly = false; 
        }

        var pengirimanid = $('.pengirimanid').val();
        $.ajax({
            url         : base_url + 'get_detail_pengiriman',
            method      : 'post',
            async       : true,
            dataType    : 'json',
            data        : { pengirimanid : pengirimanid },
            success: function(data) {
                // console.log(data, 'data_get_detail_pengiriman')
                $('.carabayar').val(data[0].cara_pembayaran);

                var i;
                var no=0;
                var grandtotal = 0;
                
                for(i=0; i<data.length; i++){
                    var nama_item = ''; 
                    var tipe_item = data[i].tipe;
                    if (tipe_item == 'barang'){
                        nama_item = data[i].item;
                    }else if (tipe_item == 'jasa'){
                        nama_item = data[i].jasa;
                    }else{
                        nama_item = data[i].budgetevent;
                    }

                    var hasil_diskon = data[i].diskon;
                    if (data[i].diskon > 0){
                        nominaldiskon = (parseInt(data[i].diskon) * parseInt(data[i].subtotal) / parseInt(100));
                        hasil_diskon = parseInt(hasil_diskon) - parseInt(nominaldiskon);
                    }else{
                        nominaldiskon = 0;
                        hasil_diskon = parseInt(hasil_diskon) - parseInt(nominaldiskon);
                    }

                    grandtotal = grandtotal + parseInt(data[i].total);
                    table_detail_item.row.add([
                        data[i].itemid,
                        nama_item,
                        `${formatRupiah(data[i].harga,'Rp. ')}`,
                        `${data[i].jumlah}`,
                        `${formatRupiah(data[i].subtotal,'Rp. ')}`,
                        `${formatRupiah(String(hasil_diskon),'Rp. ')}`,
                        `${formatRupiah(data[i].ppn,'Rp. ')}`,
                        `${formatRupiah(data[i].biaya_pengiriman,'Rp. ')}`,
                        `${formatRupiah(data[i].total,'Rp. ')}`,
                        `${data[i].tipe}`,
                        `${data[i].idpenjualdetail}`
                    ]).draw( false );
                    no++;
                }

            }
        })

        $.ajax({
            url         : base_url + 'get_detail_angsuran',
            method      : 'post',
            async       : true,
            dataType    : 'json',
            data        : { id : $('.pemesananid').val() },
            success: function(data) {
                var i;
                for(i=0; i<data.length; i++){ 
                    var uangmuka = data[i].uangmuka; 
                    var jumlahterm = data[i].jumlahterm;
                    var total = data[i].total;
                }
                $('.um').val(formatRupiah(uangmuka, 'Rp.')); 
                $('.jtem').val(jumlahterm);
                $('.tum').val(formatRupiah(total, 'Rp.')); 
                $('.tanggaluangmuka').val('<?php echo $pemesanan["tanggal"] ?>'); 
            }
        })

        $.ajax({
            url         : base_url + 'get_detail_budgetevent',
            method      : 'post',
            async       : true,
            dataType    : 'json',
            data        : { id : $('.pemesananid').val() },
            success: function(data) {
                var i;
                var no=0;
                var grandtotal = 0;
                for(i=0; i<data.length; i++){
                    var hasil_diskon = data[i].subtotal;
                    if (data[i].diskon > 0){
                        nominaldiskon = (parseInt(data[i].diskon) * parseInt(data[i].subtotal) / parseInt(100));
                        hasil_diskon = parseInt(hasil_diskon) - parseInt(nominaldiskon);
                    }else{
                    nominaldiskon = 0;
                    hasil_diskon = parseInt(hasil_diskon) - parseInt(nominaldiskon);
                    }
                    grandtotal = grandtotal + parseInt(data[i].total);
                    table_detail_budgetevent.row.add([
                        data[i].idbudgetevent,
                        data[i].budgetevent,
                        `${formatRupiah(data[i].harga,'Rp. ')}`,
                        `${data[i].jumlah}`,
                        `${formatRupiah(data[i].subtotal,'Rp. ')}`,
                        `${formatRupiah(String(hasil_diskon),'Rp. ')}`,
                        `${formatRupiah(data[i].ppn,'Rp. ')}`,
                        `${formatRupiah(data[i].biaya_pengiriman,'Rp. ')}`,
                        `${formatRupiah(data[i].total,'Rp. ')}`,
                        `budgetevent`,
                    ]).draw( false );
                    no++;
                }
            }
        })

    })

    //apabila kontakid telah dipilih
    $('.kontakid').change(function(e){
        table_detail_item.clear().draw();
        table_detail_budgetevent.clear().draw();
        var kontakid = $(this).val();
        ajax_select({ id: '.nomor_pengiriman', url: base_url + 'select2_nomor_pengiriman/'+kontakid, selected: { id: null } });
        
    })
    //memunculkan modal
    $(document).on('click','.btn_add_detail',function(){
        $("#table_detail_item").attr("hidden", false);
        $("#table_detail_budgetevent").attr("hidden", true);
        $('#modal_add_detail').modal('show')
    })

    //memunculkan tabel budget event
    $(document).on('click','.btn_add_detail_budgetevent',function(){
        $("#table_detail_item").attr("hidden", true);
        $("#table_detail_budgetevent").attr("hidden", false);
    })


    function save_detail() {
        table_detail_item.clear().draw();
        table_detail_budgetevent.clear().draw();
        var pengirimanid = $('.nomor_pengiriman').val();
        $('.pengirimanid').val(pengirimanid);
        idpengiriman = $('.nomor_pengiriman').children('option:selected').val();
        $(".gudangid").val($("#gudangid").data("default-value"));
        $(".departemen").val($("#departemen").data("default-value"));
        $('.nomorsuratjalan').val('');
        $('.carabayar').val('');
        $.ajax({
            url         : base_url + 'get_detail_pengiriman',
            method      : 'post',
            async       : true,
            dataType    : 'json',
            data        : { pengirimanid : pengirimanid },
            success: function(data) {
                var i;
                var no=0;
                var grandtotal = 0;

                for(i=0; i<data.length; i++){
                    if(table_detail_item.hasValue(data[i].idpenjualdetail)) {
                        swal("Gagal!", "Nomor pengiriman tersebut telah ada!", "error");
                        return;
                    }

                    var nama_item = ''; 
                    var tipe_item = data[i].tipe;
                    if (tipe_item == 'barang'){
                        nama_item = data[i].item;
                    }else if (tipe_item == 'jasa'){
                        nama_item = data[i].jasa;
                    }else{
                        nama_item = data[i].inventaris;
                    }

                    var hasil_diskon = data[i].subtotal;
                    if (data[i].diskon > 0){
                        nominaldiskon = (parseInt(data[i].diskon) * parseInt(data[i].subtotal) / parseInt(100));
                        hasil_diskon = parseInt(hasil_diskon) - parseInt(nominaldiskon);
                    }else{
                        nominaldiskon = 0;
                        hasil_diskon = parseInt(hasil_diskon) - parseInt(nominaldiskon);
                    }

                    grandtotal = grandtotal + parseInt(data[i].total);
                    table_detail_item.row.add([
                        data[i].itemid,
                        nama_item,
                        `${formatRupiah(data[i].harga,'Rp. ')}`,
                        `${data[i].jumlah}`,
                        `${formatRupiah(data[i].subtotal,'Rp. ')}`,
                        `${formatRupiah(String(hasil_diskon),'Rp. ')}`,
                        `${formatRupiah(data[i].ppn,'Rp. ')}`,
                        `${formatRupiah(data[i].biaya_pengiriman,'Rp. ')}`,
                        `${formatRupiah(data[i].total,'Rp. ')}`,
                        `${data[i].tipe}`,
                        `${data[i].idpenjualdetail}`
                        ]).draw( false );
                    no++;
                }
            }
        })
       
       $.ajax({
            url : base_url + 'get_data_pengiriman',
            method : "POST",
            data : {idpengiriman: idpengiriman},
            async : true,
            dataType : 'json',
            success: function(data){
                console.log(data[i].cara_pembayaran, 'cara_pembayaran')
                var i;
                for(i=0; i<data.length; i++){ 
                    $('.idperusahaan').val(data[i].idperusahaan); 
                    $('.nomorsuratjalan').val(data[i].nomorsuratjalan);
                    $('.carabayar').val(data[i].cara_pembayaran);
                    $('.tanggaluangmuka').val(data[i].tgl_pemesanan);
                    $(".rekening").attr("disabled", false);

                    ajax_select({ id: '.gudangid', url: base_url + 'select2_gudang', selected:{id: data[i].gudangid}});
                    ajax_select({ id: '.departemen', url: base_url + 'select2_departemen', selected:{id: data[i].departemen}});
                    
                    if (data[i].carabayar == 'cash'){
                        $('#tanggal').val('{tanggal}'); 
                        $('#tanggalJT').val('{tanggal}');
                        document.getElementById("tanggalJT").readOnly = true;
                    }else{
                        $('#tanggal').val('{tanggal}'); 
                        $('#tanggalJT').val('');
                        document.getElementById("tanggalJT").readOnly = false; 
                    }

                    ajax_select({ id: '.rekening', url: base_url + 'select2_rekening/'+ data[i].idperusahaan });

                    $.ajax({
                        url         : base_url + 'get_detail_angsuran',
                        method      : 'post',
                        async       : true,
                        dataType    : 'json',
                        data        : { id : data[i].pemesananid },
                        success: function(data) {
                            var i;
                            for(i=0; i<data.length; i++){ 
                                var uangmuka = data[i].uangmuka; 
                                var jumlahterm = data[i].jumlahterm;
                                var total = data[i].total;
                            }
                            $('.um').val(formatRupiah(uangmuka, 'Rp.')); 
                            $('.jtem').val(jumlahterm);
                            $('.tum').val(formatRupiah(total, 'Rp.')); 
                        }
                    })


                    $.ajax({
                        url         : base_url + 'get_detail_budgetevent',
                        method      : 'post',
                        async       : true,
                        dataType    : 'json',
                        data        : { id : data[i].pemesananid },
                        success: function(data) {
                            var i;
                            var no=0;
                            var grandtotal = 0;

                            for(i=0; i<data.length; i++){
                                var hasil_diskon = data[i].subtotal;
                                if (data[i].diskon > 0){
                                    nominaldiskon = (parseInt(data[i].diskon) * parseInt(data[i].subtotal) / parseInt(100));
                                    hasil_diskon = parseInt(hasil_diskon) - parseInt(nominaldiskon);
                                }else{
                                    nominaldiskon = 0;
                                    hasil_diskon = parseInt(hasil_diskon) - parseInt(nominaldiskon);
                                }

                                grandtotal = grandtotal + parseInt(data[i].total);
                                table_detail_budgetevent.row.add([
                                    data[i].idbudgetevent,
                                    data[i].budgetevent,
                                    `${formatRupiah(data[i].harga,'Rp. ')}`,
                                    `${data[i].jumlah}`,
                                    `${formatRupiah(data[i].subtotal,'Rp. ')}`,
                                    `${formatRupiah(String(hasil_diskon),'Rp. ')}`,
                                    `${formatRupiah(data[i].ppn,'Rp. ')}`,
                                    `${formatRupiah(data[i].biaya_pengiriman,'Rp. ')}`,
                                    `${formatRupiah(data[i].total,'Rp. ')}`,
                                    `budgetevent`,
                                    ]).draw( false );
                                no++;
                            }
                        }
                    })

                }
                
        
            }
        });
        $('#modal_add_detail').modal('hide');
    }

    function update() {
        var form = $('#form1')[0];
        var formData = new FormData(form);
        
        $.ajax({
            url: base_url + 'update',
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
                    swal("Berhasil!", data.message, "success");
                    redirect(base_url);
                } else {
                    swal("Gagal!", data.message, "error");
                }
            },
            error: function() {
                swal("Gagal!", "Internal Server Error", "error");
            }
        })
    }
    

</script>