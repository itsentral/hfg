<?php
$ENABLE_ADD     = has_permission('Payment_Jurnal.Add');
$ENABLE_MANAGE  = has_permission('Payment_Jurnal.Manage');
$ENABLE_VIEW    = has_permission('Payment_Jurnal.View');
$ENABLE_DELETE  = has_permission('Payment_Jurnal.Delete');
?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<div class="box box-primary">
    <div class="box-body">
        <div class="table-responsive">
            <table id="example1" class="table table-bordered table-striped" width='100%'>
                <thead>
                    <tr class='bg-blue'>
                        <th class="text-center">#</th>
                        <th class="text-center">No Transaksi</th>
                        <th class="text-center">Keperluan</th>
                        <th class="text-center">Tgl Jurnal</th>
                        <th class="text-center">Jumlah</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Option</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (!empty($results)) {
                        $numb = 0;
                        foreach ($results as $record) {
                            $numb++; ?>
                            <tr>
                                <td class="text-center"><?= $numb; ?></td>
                                <td><?= $record->id_payment ?></td>
                                <td><?= $record->keperluan ?></td>
                                <td><?= $record->tgl_jurnal ?></td>
                                <td class="text-right"><?= number_format($record->total, 2) ?></td>
                                <td class="text-center">
                                    <?php if ($record->sts == 1) {
                                        echo "<span class='badge badge-pill bg-blue'>Sudah Jurnal</span>";
                                    } else {
                                        echo "<span class='badge badge-pill bg-yellow'>Belum Jurnal</span>";
                                    } ?></td>
                                <td class="text-center">
                                    <?php
                                    echo "
                                        <a class='btn btn-sm btn-default viewed' href='javascript:void(0)' title='View Jurnal' data-id='" . $record->id_payment . "'><i class='fa fa-search'></i>
                                        </a> ";
                                    if ($record->sts != 1) {
                                        echo "<a class='btn btn-warning btn-sm edited' href='javascript:void(0)' title='Edit Jurnal' data-id='" . $record->id_payment . "'><i class='fa fa-check'></i>
                                            </a>
                                            ";
                                    }
                                    ?>
                                </td>
                            </tr>
                    <?php
                        }
                    }  ?>
                </tbody>
            </table>
        </div>
    </div>
    <!-- /.box-body -->
</div>
<!-- /.box -->
<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script>
    $(document).on('click', '.viewed', function(e) {
        window.location.href = base_url + active_controller + '/view_jurnal/' + $(this).data('id');
    });

    $(document).on('click', '.edited', function(e) {
        window.location.href = base_url + active_controller + '/edit_jurnal/' + $(this).data('id');
    });

    $(document).on('click', '.updated', function() {
        var id = $(this).data('id');

        swal({
                title: "Are you sure?",
                text: "Update this data ?",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-danger",
                confirmButtonText: "Yes, Process it!",
                cancelButtonText: "No, cancel process!",
                closeOnConfirm: true,
                closeOnCancel: false
            },
            function(isConfirm) {
                if (isConfirm) {
                    loading_spinner();
                    $.ajax({
                        url: base_url + active_controller + '/update_jurnal/' + id,
                        type: "POST",
                        cache: false,
                        dataType: 'json',
                        processData: false,
                        contentType: false,
                        success: function(data) {
                            if (data.status == 1) {
                                swal({
                                    title: "Update Success!",
                                    text: data.pesan,
                                    type: "success",
                                    timer: 5000
                                });
                                window.location.href = base_url + 'ros/index_jurnal_incoming';
                            } else if (data.status == 0) {
                                swal({
                                    title: "Update Failed!",
                                    text: data.pesan,
                                    type: "warning",
                                    timer: 5000
                                });
                            }
                        },
                        error: function() {
                            swal({
                                title: "Error Message !",
                                text: 'An Error Occured During Process. Please try again..',
                                type: "warning",
                                timer: 5000
                            });
                        }
                    });
                } else {
                    swal("Cancelled", "Data can be process again :)", "error");
                    return false;
                }
            });
    });

    $(function() {
        var table = $('#example1').DataTable({
            orderCellsTop: true,
            fixedHeader: true
        });
        $("#form-area").hide();
    });
</script>