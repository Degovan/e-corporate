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
                    <form action="javascript:save()" id="form">
                        <div class="card">
                            <div class="card-header">
                                <div class="header-elements-inline">
                                    <h5 class="card-title">{subtitle}</h5>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <form action="javascript:save()" id="form1">
                                            <div class="form-group">
                                                <label><?php echo lang('Perusahaan') ?>:</label>
                                                <select class="form-control perusahaan" name="perusahaan" required></select>
                                                <?php
                                                    if ($this->session->userid !== 1) { ?>
                                                        <input type="hidden" name="idperusahaan" value="<?= $this->session->idperusahaan; ?>" id="perusahaan">
                                                        <input type="text" class="form-control" value="<?= $this->session->perusahaan; ?>" disabled>
                                                    <?php } else { ?>
                                                        <select class="form-control perusahaan" name="perusahaan" style="width: 100%;" id="perusahaan"></select>
                                                    <?php }
                                                ?>
                                                <label><?php echo lang('nama') ?>:</label>
                                                <input type="text" class="form-control" name="nama" required>
                                                <label><?php echo lang('no rek') ?>:</label>
                                                <input type="text" class="form-control" name="norek" required>
                                                <label><?php echo lang('no akun') ?>:</label>
                                                <select class="form-control akunno" name="akunno" required></select>
                                            </div>
                                            <div class="text-right">
                                                <a href="{site_url}rekening" class="btn bg-danger"><?php echo lang('cancel') ?></a>
                                                <button type="submit" class="btn bg-success"><?php echo lang('save') ?></button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
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
<script type="text/javascript">
	var base_url = '{site_url}rekening/';
	$(document).ready(function(){
        if ('<?= $this->session->userid; ?>' == 1) {
            ajax_select({ 
                id          : '.perusahaan', 
                url         : base_url + 'select2_id_perusahaan', 
                selected    : { 
                    id  : '' 
                } 
            });
        }
        ajax_select({ id: '.akunno', url: base_url + 'select2_akunno', selected: { id: '' } });
    })
    function save() {
        var form = $('#form')[0];
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
</script>