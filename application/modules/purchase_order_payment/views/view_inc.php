<?php

?>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<div class="form-group row mb-2">
    <div class="col-md-6">
        <label for="">Nama Supplier</label>
        <input type="hidden" name="kode_supplier" value="<?= $data_invoice['id_supplier'] ?>">
        <input type="text" name="nama_supplier" id="" class="form-control form-control-sm nama_supplier" value="<?= $data_invoice['nm_supplier'] ?>" readonly>
    </div>
    <div class="col-md-6">
        <label for="">Nomor Incoming</label>
        <input type="text" name="nomor_po" id="" class="form-control form-control-sm nomor_po" value="<?= $data_invoice['no_incoming'] ?>" readonly>
    </div>
</div>

<div class="form-group row mb-2">
    <div class="col-md-6">
        <label for="">Nomor Invoice</label>
        <input type="text" name="nomor_invoice" id="" class="form-control form-control-sm nomor_invoice" value="<?= $data_invoice['invoice_no'] ?>" required>
    </div>
    <div class="col-md-6">
        <label for="">Nomor Faktur Pajak</label>
        <input type="text" name="nomor_faktur_pajak" id="" class="form-control form-control-sm nomor_faktur_pajak" value="<?= $data_invoice['no_faktur_pajak'] ?>">
    </div>
</div>

<div class="form-group row mb-2">
    <div class="col-md-4">
        <label for="">Receive Invoice Date</label>
        <input type="date" name="invoice_date" id="" class="form-control form-control-sm" value="<?= $data_invoice['invoice_date'] ?>" required>
    </div>
    <div class="col-md-4">
        <label for="">Invoice Date</label>
        <input type="date" name="invoice_date_real" id="" class="form-control form-control-sm invoice_date_real" value="<?= $data_invoice['invoice_date_real'] ?>">
    </div>
    <div class="col-md-4">
        <label for="">Tanggal Faktur Pajak</label>
        <input type="date" name="tanggal_faktur_pajak" id="" class="form-control form-control-sm tanggal_faktur_pajak" value="<?= $data_invoice['tanggal_faktur_pajak'] ?>">
    </div>
</div>

<hr>
<div class="mb-2"><b>Informasi Pembayaran</b></div>
<div class="form-group row mb-2">
    <div class="col-md-6">
        <label for="">Currency</label>
        <input type="text" name="currency" id="" class="form-control form-control-sm currency" value="<?= $data_invoice['curr'] ?>" readonly>
    </div>
    <div class="col-md-6">
        <label for="">Kurs</label>
        <input type="text" name="kurs" id="" value="<?= number_format($data_invoice['kurs'], 2) ?>" class="form-control form-control-sm text-right auto_num">
    </div>
</div>

<div class="form-group row mb-2">
    <div class="col-md-6">
        <label for="">Nilai Disc</label>
        <input type="text" name="nilai_disc" id="" class="form-control form-control-sm text-right nilai_disc auto_num" value="<?= number_format($data_invoice['nilai_disc'], 2) ?>" readonly>
    </div>
    <div class="col-md-6">
        <label for="">Nilai PPN</label>
        <input type="text" name="nilai_ppn" id="" class="form-control form-control-sm text-right nilai_ppn auto_num" value="<?= number_format($data_invoice['nilai_ppn'], 2) ?>" readonly>
    </div>
</div>

<div class="form-group row mb-2">
    <div class="col-md-6">
        <label for="">Value DP</label>
        <input type="text" name="value_dp" id="" class="form-control form-control-sm text-right value_dp" value="<?= number_format($data_invoice['value_dp'], 2) ?>" readonly>
    </div>
    <div class="col-md-6">
        <label for="">Total Incoming</label>
        <input type="text" name="total_pembelian" id="" class="form-control form-control-sm text-right total_pembelian" value="<?= number_format($data_invoice['total_pembelian'], 2) ?>" readonly>
    </div>
</div>

<div class="form-group row mb-2">
    <div class="col-md-6">
        <label for="">Total Invoice</label>
        <input type="text" name="total_invoice" id="" class="form-control form-control-sm text-right total_invoice auto_num" value="<?= number_format($data_invoice['total_invoice'], 2) ?>" required>
    </div>
    <div class="col-md-6">
        <label for="">Request Payment PO</label>
        <input type="text" name="req_payment_po" id="" class="form-control form-control-sm text-right req_payment_po auto_num" value="<?= number_format($data_invoice['req_payment_po'], 2) ?>" required>
    </div>
</div>

<div class="form-group row mb-2">
    <div class="col-md-6">
        <label for="">Upload Invoice</label>
        <div class="d-flex flex-column gap-2">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <input type="file" name="upload_invoice" id="photo" class="d-none" accept=".pdf,.jpg,.jpeg,.png">

                <button type="button" class="btn btn-outline-warning" id="btnPickInv">
                    <i class="ti ti-upload me-1"></i> Choose File
                </button>

                <span class="text-muted" id="InvFileName">No file chosen</span>

                <button type="button" class="btn btn-icon-delete" id="btnClearInv" style="display:none;">
                    <i class="ti ti-trash"></i>
                </button>
            </div>
            <small class="text-muted">
                Allowed: PDF/JPG/PNG. Max size 2MB.
            </small>

            <!-- existing file -->
            <?php if (!empty($data_invoice['link_doc'])) : ?>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="badge bg-light text-dark border">
                        <i class="ti ti-file-description me-1"></i> File Invoice
                    </span>
                    <a href="<?= base_url() . $data_invoice['link_doc']; ?>" target="_blank" class="btn btn-sm btn-success">
                        <i class="ti ti-download me-1"></i> Download
                    </a>
                    <a href="<?= base_url() . $data_invoice['link_doc']; ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                        <i class="ti ti-eye me-1"></i> Preview
                    </a>
                </div>
            <?php endif; ?>

        </div>
    </div>
    <div class="col-md-6">
        <label for="">Notes</label>
        <textarea name="notes" class="form-control form-control-sm notes"><?= $data_invoice['notes'] ?></textarea>
    </div>
</div>

<div class="form-group row mb-2">
    <div class="col-md-4">
        <label for="">Bank</label>
        <input type="text" name="bank" id="" class="form-control form-control-sm" placeholder="- Bank -" value="<?= $data_invoice['bank'] ?>">
    </div>
    <div class="col-md-4">
        <label for="">No. Bank</label>
        <input type="text" name="no_bank" id="" class="form-control form-control-sm" placeholder="- No. Bank -" value="<?= $data_invoice['no_bank'] ?>">
    </div>
    <div class="col-md-4">
        <label for="">Nama</label>
        <input type="text" name="nm_acc_bank" id="" class="form-control form-control-sm" placeholder="- Nama Acc Bank -" value="<?= $data_invoice['nm_acc_bank'] ?>">
    </div>
</div>


<?php
foreach ($no_incoming as $id_incoming) {
?>
    <div class="col-md-12">
        <hr>
        <div class="mb-2"><b>Detail Incoming : <?= $id_incoming ?></b></div>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th class="text-center">No</th>
                    <th class="text-center">Nomor PO</th>
                    <th class="text-center">Produk</th>
                    <th class="text-center">Qty PO</th>
                    <th class="text-center">Qty Incoming</th>
                    <th class="text-center">Harga Satuan</th>
                    <th class="text-center">Total Harga</th>
                </tr>
            </thead>
            <tbody>
                <?php

                $get_detail_inc = $this->db->query("
                        SELECT
                            e.qty_oke as qty_order,   
                            b.qty as qty_po,
                            b.hargasatuan as hargasatuan,
                            c.no_surat as no_surat,
                            d.nama as nm_material
                        FROM
                            tr_incoming_check_detail a
                            LEFT JOIN dt_trans_po b ON b.id = a.id_po_detail
                            LEFT JOIN tr_purchase_order c ON c.no_po  = b.no_po
                            LEFT JOIN new_inventory_4 d ON d.code_lv4 = a.id_material 
                            JOIN tr_checked_incoming_detail e ON e.kode_trans = a.kode_trans AND e.id_material = a.id_material
                        WHERE
                            a.kode_trans = '" . $id_incoming . "'

                        UNION ALL

                        SELECT 
                            a.qty_oke as qty_order,
                            b.qty as qty_po,
                            b.hargasatuan as hargasatuan,
                            c.no_surat as no_surat,
                            d.stock_name as nm_material
                        FROM
                            warehouse_adjustment_detail a
                            LEFT JOIN dt_trans_po b ON b.id = a.no_ipp
                            LEFT JOIN tr_purchase_order c ON c.no_po  = b.no_po
                            LEFT JOIN accessories d ON d.id = a.id_material 
                        WHERE
                            a.kode_trans = '" . $id_incoming . "'
                    ")->result();
                if (!$get_detail_inc) {
                    print_r($this->db->error($get_detail_inc));
                    exit;
                }


                $no = 1;
                foreach ($get_detail_inc as $item) {
                    echo '<tr>';
                    echo '<td class="text-center">' . $no . '</td>';
                    echo '<td class="text-center">' . $item->no_surat . '</td>';
                    echo '<td class="text-center">' . $item->nm_material . '</td>';
                    echo '<td class="text-center">' . number_format($item->qty_po) . '</td>';
                    echo '<td class="text-center">' . number_format($item->qty_order) . '</td>';
                    echo '<td class="text-center">' . number_format($item->hargasatuan) . '</td>';
                    echo '<td class="text-center">' . number_format($item->qty_order * $item->hargasatuan) . '</td>';
                    echo '</tr>';
                    $no++;
                }
                ?>
            </tbody>
        </table>
    </div>
<?php
}
?>
</div>


<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>

<script>
    $(document).ready(function() {
        $('.select2_modal').select2({
            width: '100%',
            dropdownParent: $('#dialog-popup')
        });
    });
    $('.auto_num').autoNumeric('init');
    $(document).on('change', '.persen_dp', function() {
        var total_pembelian = $('.total_pembelian').val();
        if (total_pembelian == '' || total_pembelian == null) {
            total_pembelian = 0;
        } else {
            total_pembelian = total_pembelian.split(',').join('');
            total_pembelian = parseFloat(total_pembelian);
        }

        var persen_dp = parseFloat($(this).val());

        var value_dp = (total_pembelian * persen_dp / 100);

        $('.value_dp').val(value_dp.toLocaleString());
    });
</script>