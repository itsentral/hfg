<?php
$ENABLE_ADD     = has_permission('ROS.Add');
$ENABLE_MANAGE  = has_permission('ROS.Manage');
$ENABLE_VIEW    = has_permission('ROS.View');
$ENABLE_DELETE  = has_permission('ROS.Delete');

$no_ros = (isset($header_ros)) ? $header_ros['id'] : 'New';
$no_po = (isset($header_ros)) ? $header_ros['no_po'] : null;
$nm_supplier = (isset($header_ros)) ? $header_ros['nm_supplier'] : null;
$awb_bl_date = (isset($header_ros)) ? $header_ros['awb_bl_date'] : null;
$awb_bl_number = (isset($header_ros)) ? $header_ros['awb_bl_number'] : null;
$eta_warehouse = (isset($header_ros)) ? $header_ros['eta_warehouse'] : null;
$kurs_pib = (isset($header_ros)) ? $header_ros['kurs_pib'] : 0;
$cost_bm = (isset($header_ros)) ? $header_ros['cost_bm'] : 0;
$cost_ppn = (isset($header_ros)) ? $header_ros['cost_ppn'] : 0;
$cost_pph = (isset($header_ros)) ? $header_ros['cost_pph'] : 0;
$freight_cost_persen = (isset($header_ros)) ? $header_ros['freight_cost'] : 0;
$no_pengajuan_pib = (isset($header_ros)) ? $header_ros['no_pengajuan_pib'] : null;
$no_billing = (isset($header_ros)) ? $header_ros['no_biling'] : null;
$id_supplier = (isset($header_ros)) ? $header_ros['id_supplier'] : null;
?>
<style type="text/css">
    thead input {
        width: 100%;
    }
</style>

<div class="card">
    <div class="card-body">
        <form action="" method="post" id="frm-data" enctype="multipart/form-data">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <h5 class="mb-0 fw-bold">Form Report of Shipment</h5>
                <span class="text-muted small">(*) wajib diisi</span>
            </div>
            <hr class="mt-2">
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-group row">
                        <div class="col-md-4">
                            <label for="">No. ROS</label>
                        </div>
                        <div class="col-md-8">
                            <input type="text" name="no_ros" id="" class="form-control form-control-sm no_ros" value="<?= $no_ros ?>" readonly>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <div class="col-md-4">
                            <label for="">Supplier <span class="text-danger">*</span></label>
                        </div>
                        <div class="col-md-8">
                            <select name="supplier_name" id="" class="form-control form-control-sm select2 get_supplier" required>
                                <option value="">Select Supplier</option>
                                <?php
                                foreach ($list_supplier as $item) {
                                    $selected = '';
                                    if ($item['kode_supplier'] == $id_supplier) {
                                        $selected = 'selected';
                                    }
                                    echo '<option value="' . $item['kode_supplier'] . '" ' . $selected . '>' . $item['nama'] . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="table-responsive">
                    <div class="col-md-12">
                        <?php
                        if ($no_ros == 'New') {
                        ?>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width: 70%;">No. PO</th>
                                        <th class="text-center" style="width: 30%;">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="no_po">
                                </tbody>
                            </table>
                        <?php
                        } else { ?>
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <div class="col-md-4">
                                        <label for="">Nomor PO</label>
                                    </div>
                                    <div class="col-md-8">
                                        <label><?= str_replace(',', ', ', $no_po) ?></label>
                                        <input type="hidden" name="no_po" class="form-control" value="<?= str_replace(',', ', ', $no_po) ?>" readonly>
                                    </div>
                                </div>
                            </div>
                            <hr>
                        <?php }
                        ?>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-group row">
                        <div class="col-md-4">
                            <label for="">AWB / BL Number</label>
                        </div>
                        <div class="col-md-8">
                            <input type="text" name="awb_bl_number" id="" class="form-control" value="<?= $awb_bl_number ?>">
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <div class="col-md-4">
                            <label for="">AWB / BL Date</label>
                        </div>
                        <div class="col-md-8">
                            <input type="date" name="awb_bl_date" id="" class="form-control" value="<?= $awb_bl_date ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-group row">
                        <div class="col-md-4">
                            <label for="">ETA Warehouse</label>
                        </div>
                        <div class="col-md-8">
                            <input type="date" name="eta_warehouse" id="" class="form-control" value="<?= $eta_warehouse ?>">
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <div class="col-md-4">
                            <label for="">ATA POD</label>
                        </div>
                        <div class="col-md-8">
                            <input type="date" name="ata_pod" id="" class="form-control" value="<?= $eta_warehouse ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-group row">
                        <div class="col-md-4">
                            <label for="">Kurs PIB</label>
                        </div>
                        <div class="col-md-8">
                            <input type="text" name="kurs_pib" id="" class="form-control auto_num kurs_pib" value="<?= $kurs_pib ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="table-packing">
                            <thead>
                                <tr>
                                    <th class="text-center">No.</th>
                                    <th class="text-center" style="min-width: 200px;">Nama Barang</th>
                                    <th class="text-center">Satuan</th>
                                    <th class="text-center">Currency</th>
                                    <th class="text-center">Price/Unit</th>
                                    <th class="text-center">Price/Unit (Rp)</th>
                                    <th class="text-center">Qty PO</th>
                                    <th class="text-center" style="min-width: 100px;">Berat Kotor</th>
                                    <th class="text-center" style="min-width: 100px;">Berat Bersih</th>
                                    <th class="text-center" style="min-width: 100px;">Length</th>
                                    <th class="text-center" style="min-width: 120px;">Biaya Masuk</th>
                                    <th class="text-center" style="min-width: 120px;">Forwarding Cost</th>
                                    <th class="text-center" style="min-width: 120px;">Nilai Total</th>
                                    <th class="text-center" style="min-width: 200px;">No. Coil</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody class="list_detail_po">
                                <?php
                                $ttl_price_detail = 0;
                                if (isset($detail_ros) && !empty($detail_ros)) {
                                    // 1. Grouping data berdasarkan id_po_detail
                                    $grouped_data = [];
                                    foreach ($detail_ros as $item) {
                                        $grouped_data[$item['id_po_detail']][] = $item;
                                    }

                                    $no = 1;
                                    foreach ($grouped_data as $id_po_detail => $rows) {
                                        // Ambil baris pertama sebagai referensi data material
                                        $first_row = $rows[0];

                                        // Hitung nilai pengurang (dari ROS lain)
                                        $this->db->select('IF(SUM(a.qty_packing_list) IS NULL, 0, SUM(a.qty_packing_list)) as nilai_pengurang');
                                        $this->db->from('tr_ros_detail a');
                                        $this->db->where('a.id_po_detail', $id_po_detail);
                                        $this->db->where('a.no_ros <>', $first_row['no_ros']);
                                        $get_nilai_ros_used = $this->db->get()->row_array();
                                        $nilai_pengurang = (!empty($get_nilai_ros_used)) ? $get_nilai_ros_used['nilai_pengurang'] : 0;

                                        // Loop setiap data coil/packing list untuk material ini
                                        foreach ($rows as $index => $item) {
                                            if ($index === 0) {
                                                // BARIS UTAMA (HEADER MATERIAL)
                                                echo '<tr class="row-material" data-id="' . $id_po_detail . '">';
                                                echo '<td class="text-center no-urut">' . $no . '</td>';
                                                echo '<td class="text-center">' . $item['nm_barang'] . '</td>';
                                                echo '<td class="text-center">' . ucfirst($item['unit_satuan']) . '</td>';
                                                echo '<td class="text-center">' . $item['currency'] . '</td>';
                                                echo '<td class="text-end">' . number_format($item['price_unit']) . '</td>';
                                                echo '<td class="text-end">' . number_format($item['price_unit'] * $kurs_pib) . '</td>';
                                                echo '<td class="text-center">' . number_format($item['qty_po']) . '</td>';
                                            } else {
                                                // BARIS CHILD (COIL BERIKUTNYA)
                                                echo '<tr class="child-' . $id_po_detail . '">';
                                                echo '<td colspan="7"></td>'; // Kosongkan kolom material info
                                            }

                                            // KOLOM INPUT (Sama untuk baris utama maupun child)
                                            echo '<td><input type="text" name="dt[' . $id_po_detail . '][berat_kotor][]" class="form-control auto_num text-end" value="' . $item['berat_kotor'] . '"></td>';
                                            echo '<td><input type="text" name="dt[' . $id_po_detail . '][berat_bersih][]" class="form-control auto_num text-end" value="' . $item['berat_bersih'] . '"></td>';
                                            echo '<td><input type="text" name="dt[' . $id_po_detail . '][length][]" class="form-control auto_num text-end" value="' . $item['length'] . '"></td>';
                                            echo '<td><input type="text" name="dt[' . $id_po_detail . '][biaya_masuk][]" class="form-control auto_num text-end calculate" value="' . $item['biaya_masuk'] . '"></td>';
                                            echo '<td><input type="text" name="dt[' . $id_po_detail . '][forwarding][]" class="form-control auto_num text-end calculate" value="' . $item['forwarding_cost'] . '"></td>';
                                            echo '<td><input type="text" name="dt[' . $id_po_detail . '][total_nilai][]" class="form-control auto_num text-end calculate" value="' . $item['total_nilai'] . '"></td>';
                                            echo '<td><input type="text" name="dt[' . $id_po_detail . '][no_coil][]" class="form-control" value="' . $item['no_coil'] . '"></td>';

                                            echo '<td class="text-center">';
                                            if ($index === 0) {
                                                // Tombol Tambah di baris pertama
                                                echo '<button type="button" class="btn btn-sm btn-primary add-row-child" data-id="' . $id_po_detail . '"><i class="fa fa-plus"></i></button>';
                                            } else {
                                                // Tombol Hapus di baris child
                                                echo '<button type="button" class="btn btn-sm btn-danger remove-row"><i class="fa fa-trash"></i></button>';
                                            }
                                            echo '</td>';
                                            echo '</tr>';

                                            // Hitung Total Price untuk footer
                                            $ttl_price_detail += $item['total_nilai'];
                                        }
                                        $no++;
                                    }
                                }
                                ?>
                            </tbody>
                            <tbody>
                                <tr>
                                    <td colspan="12" align="right">
                                        <b>Grand Total</b>
                                    </td>
                                    <td align="right" colspan="2" class="ttl_price_detail_col" id="ttl_total_price"><?= number_format($ttl_price_detail, 2) ?></td>
                                </tr>
                            </tbody>
                        </table>

                        <input type="hidden" name="ttl_total_price" class="ttl_total_price" value="<?= $ttl_price_detail ?>">
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-between mb-2">
                <h5 class="mb-0 fw-bold">F&C Cost Estimation | Pemberitahuan Import Barang</h5>
            </div>
            <hr class="mt-2">

            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th class="text-center">No.</th>
                                    <th class="text-center">Item Pembiayaan</th>
                                    <th class="text-center">Cost</th>
                                    <th class="text-center"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-center">1</td>
                                    <td class="text-center">BM</td>
                                    <td class="">
                                        <input type="text" name="cost_bm" id="cost_bm" class="form-control form-control-sm input_bm text-end auto_num" value="<?= $cost_bm ?>">
                                    </td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td class="text-center">2</td>
                                    <td class="text-center">PPN</td>
                                    <td class="">
                                        <input type="text" name="cost_ppn" id="" class="form-control form-control-sm input_ppn text-end auto_num" value="<?= $cost_ppn ?>">
                                    </td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td class="text-center">3</td>
                                    <td class="text-center">PPH</td>
                                    <td class="">
                                        <input type="text" name="cost_pph" id="" class="form-control form-control-sm input_pph text-end auto_num" value="<?= $cost_pph ?>">
                                    </td>
                                    <td></td>
                                </tr>
                            </tbody>
                            <tbody class="list_custom_pib">
                                <?php
                                $no = 4;
                                $ttl_custom_pib = 0;
                                foreach ($list_custom_pib as $item) {
                                    echo '<tr>';
                                    echo '<td class="text-center">' . $no . '</td>';
                                    echo '<td class="text-center">' . $item['nm_item_pembiayaan'] . '</td>';
                                    echo '<td class="text-center">
                                        <input type="text" name="" id="" class="form-control form-control-sm text-end auto_num cost_pib_custom cost_pib_custom_' . $item['id'] . '" data-id="' . $item['id'] . '" value="' . $item['nilai_cost'] . '">
                                    </td>';
                                    echo '<td class="text-center">
                                        <button type="button" class="btn btn-sm btn-danger del_custom_pib" data-id="' . $item['id'] . '"><i class="fa fa-trash"></i></button>
                                    </td>';
                                    echo '</tr>';

                                    $ttl_custom_pib += $item['nilai_cost'];

                                    $no++;
                                }
                                ?>
                            </tbody>
                            <tbody>
                                <tr>
                                    <td class="text-center">

                                    </td>
                                    <td>
                                        <input type="text" name="" id="" class="form-control form-control-sm biaya_name">
                                    </td>
                                    <td>
                                        <input type="text" name="" id="" class="form-control form-control-sm auto_num text-end cost_biaya">
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-success add_custom_pembiayaan">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                            <tbody>
                                <tr>
                                    <td class="text-center" colspan="2">
                                        <b>TOTAL</b>
                                    </td>
                                    <td class="text-end total_pib"><?= number_format($cost_bm + $cost_ppn + $cost_pph + $ttl_custom_pib) ?></td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row mb-3">
                        <div class="col-md-4">
                            <label for="no_pengajuan_pib">Nomor Pengajuan PIB</label>
                        </div>
                        <div class="col-md-8">
                            <input type="text" name="no_pengajuan_pib" id="no_pengajuan_pib" class="form-control form-control-sm" value="<?= $no_pengajuan_pib ?>">
                        </div>
                    </div>
                    <div class="form-group row mb-3">
                        <div class="col-md-4">
                            <label for="no_billing">Nomor Billing</label>
                        </div>
                        <div class="col-md-8">
                            <input type="text" name="no_billing" id="no_billing" class="form-control form-control-sm" value="<?= $no_billing ?>">
                        </div>
                    </div>
                    <div class="form-group row mb-3">
                        <div class="col-md-4">
                            <label for="">Upload PIB</label>
                        </div>
                        <div class="col-md-8">
                            <!-- wrapper -->
                            <div class="d-flex flex-column gap-2">
                                <!-- custom file input -->
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <!-- input aslinya disembunyikan -->
                                    <input type="file" name="upload_pib" id="photo" class="d-none" accept=".pdf,.jpg,.jpeg,.png">

                                    <!-- tombol pilih file -->
                                    <button type="button" class="btn btn-outline-warning" id="btnPickPib">
                                        <i class="ti ti-upload me-1"></i> Choose File
                                    </button>

                                    <!-- nama file -->
                                    <span class="text-muted" id="pibFileName">No file chosen</span>

                                    <!-- tombol clear -->
                                    <button type="button" class="btn btn-icon-delete" id="btnClearPib" style="display:none;">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </div>

                                <!-- hint -->
                                <small class="text-muted">
                                    Allowed: PDF/JPG/PNG. Max size 2MB.
                                </small>

                                <!-- existing file -->
                                <!-- <?php if (!empty($file_msds)) : ?>
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <span class="badge bg-light text-dark border">
                                            <i class="ti ti-file-description me-1"></i> Existing MSDS
                                        </span>
                                        <a href="<?= base_url() . $file_msds; ?>" target="_blank" class="btn btn-sm btn-success">
                                            <i class="ti ti-download me-1"></i> Download
                                        </a>
                                        <a href="<?= base_url() . $file_msds; ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                            <i class="ti ti-eye me-1"></i> Preview
                                        </a>
                                    </div>
                                <?php endif; ?> -->
                            </div>
                        </div>
                        <script>
                            (function() {
                                const input = document.getElementById('photo');
                                const btnPick = document.getElementById('btnPickPib');
                                const btnClear = document.getElementById('btnClearPib');
                                const fileName = document.getElementById('pibFileName');

                                if (!input || !btnPick || !fileName) return;

                                btnPick.addEventListener('click', function() {
                                    input.click();
                                });

                                input.addEventListener('change', function() {
                                    const name = (input.files && input.files.length) ? input.files[0].name : 'No file chosen';
                                    fileName.textContent = name;

                                    if (input.files && input.files.length) {
                                        btnClear.style.display = 'inline-flex';
                                    } else {
                                        btnClear.style.display = 'none';
                                    }
                                });

                                if (btnClear) {
                                    btnClear.addEventListener('click', function() {
                                        input.value = '';
                                        fileName.textContent = 'No file chosen';
                                        btnClear.style.display = 'none';
                                    });
                                }
                            })();
                        </script>
                    </div>
                    <div class="form-group row mb-3">
                        <div class="col-md-4">
                            <label for="">Keterangan</label>
                        </div>
                        <div class="col-md-8">
                            <textarea name="keterangan" id="" cols="30" rows="5" class="form-control form-control-sm"><?= isset($header_ros) ? $header_ros['keterangan'] : null ?></textarea>
                        </div>
                    </div>
                </div>
            </div>


            <div class="d-flex align-items-center justify-content-between mb-2">
                <h5 class="mb-0 fw-bold">Freight Cost Forecast</h5>
            </div>
            <hr class="mt-2">
            <div class="row mb-3">
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th class="text-center">No.</th>
                                    <th class="text-center">Item Pembiayaan</th>
                                    <th class="text-center">%</th>
                                    <th class="text-center">Total Cost</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-center">1</td>
                                    <td class="text-center">Freight Cost</td>
                                    <td class="">
                                        <input type="text" name="freight_cost_persen" id="" class="form-control auto_num form-control-sm freight_cost_persen" value="<?= $freight_cost_persen ?>">
                                        <input type="hidden" name="freight_cost" class="freight_cost">
                                    </td>
                                    <td class="text-end freight_cost_val">
                                        <?php
                                        if ($freight_cost_persen > 0) {
                                            echo number_format($ttl_price_detail * $freight_cost_persen / 100);
                                        } else {
                                            echo '0';
                                        }
                                        ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>


            <div class="row">
                <div class="col-md-12 text-center">
                    <a href="<?= base_url('./ros') ?>" class="btn btn-md btn-secondary"><i class="fa fa-arrow-left"></i> Back</a>
                    <button type="submit" class="btn btn-md btn-success" name="save"><i class="fa fa-save"></i> Save</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- DataTables -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>

<!-- page script -->
<script type="text/javascript">
    $('.select2').select2();
    $('.auto_num').autoNumeric('init');

    $(document).on('click', '.no_po', function() {
        var no_po = [];
        $('.no_po').each(function() {
            var val = $(this).val();
            if ($(this).prop('checked')) {
                no_po.push(val);
            }
        });
        var no_po = no_po.join(',');
        var kurs_pib = $('.kurs_pib').val();
        if (kurs_pib == '' || kurs_pib == null) {
            kurs_pib = 0;
        } else {
            kurs_pib = kurs_pib.split(",").join("");
            kurs_pib = parseFloat(kurs_pib);
        }

        get_list_detail_po(no_po, kurs_pib);
        ttl_price();
    });

    $(document).on('change', '.qty_packing_list', function() {
        var id = $(this).data('id');
        var harga_satuan = $(this).data('harga_satuan');
        var kurs_pib = $('.kurs_pib').val();
        if (kurs_pib == '' || kurs_pib == null) {
            kurs_pib = 1
        } else {
            kurs_pib = kurs_pib.split(',').join('');
            kurs_pib = parseFloat(kurs_pib);
        }

        var nilai = $(this).val();
        if (nilai == '' || nilai == null) {
            nilai = 0;
        } else {
            nilai = nilai.split(",").join("");
            nilai = parseFloat(nilai);
        }

        var total = ((harga_satuan * kurs_pib) * nilai);
        var totala = total.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

        $('.total_price_' + id).html(totala);
        ttl_price();
    });

    $(document).on('change', '.kurs_pib', function() {
        var no_po = [];
        $('.no_po').each(function() {
            var val = $(this).val();
            if ($(this).prop('checked')) {
                no_po.push(val);
            }
        });
        var no_po = no_po.join(',');
        var kurs_pib = $(this).val();
        if (kurs_pib == '' || kurs_pib == null) {
            kurs_pib = 1
        } else {
            kurs_pib = kurs_pib.split(',').join('');
            kurs_pib = parseFloat(kurs_pib);
        }

        get_list_detail_po(no_po, kurs_pib);
    });

    $(document).on('change', '.input_bm', function() {
        hitung_pib();
    });
    $(document).on('change', '.input_ppn', function() {
        hitung_pib();
    });
    $(document).on('change', '.input_pph', function() {
        hitung_pib();
    });

    $(document).on('click', '.add_custom_pembiayaan', function() {
        var no_ros = $('.no_ros').val();
        var biaya_name = $('.biaya_name').val();
        var cost_biaya = $('.cost_biaya').val();
        if (cost_biaya == '' || cost_biaya == null) {
            cost_biaya = 0
        } else {
            cost_biaya = cost_biaya.split(',').join('');
            cost_biaya = parseFloat(cost_biaya);
        }

        $.ajax({
            type: "POST",
            url: siteurl + active_controller + '/add_custom_pembiayaan',
            data: {
                'no_ros': no_ros,
                'biaya_name': biaya_name,
                'cost_biaya': cost_biaya
            },
            cache: false,
            dataType: 'json',
            beforeSend: function(result) {
                $('.add_custom_pembiayaan').html('<i class="fa fa-spinner fa-spin"></i>');
            },
            success: function(result) {
                if (result.status == '1') {
                    swal({
                        title: 'Success !',
                        text: 'Data has been saved !',
                        type: 'success'
                    });
                } else {
                    swal({
                        title: 'Failed !',
                        text: 'Data has not been saved !',
                        type: 'error'
                    });
                }
                refresh_list_pib();
                hitung_pib();

                $('.add_custom_pembiayaan').html('<i class="fa fa-plus"></i>');
            },
            error: function(result) {
                swal({
                    title: 'Error !',
                    text: 'Please try again later !',
                    type: 'error'
                });

                $('.add_custom_pembiayaan').html('<i class="fa fa-plus"></i>');
            }
        });
    });

    $(document).on('click', '.del_custom_pib', function() {
        var id = $(this).data('id');
        var no_ros = $('.no_ros').val();

        swal({
                title: "Warning !",
                text: "This data will be deleted !",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes, Delete it!",
                cancelButtonText: "Cancel!",
                closeOnConfirm: false,
                closeOnCancel: true
            },
            function(isConfirm) {
                if (isConfirm) {
                    $.ajax({
                        type: 'POST',
                        url: siteurl + active_controller + '/del_custom_pib',
                        data: {
                            'id': id,
                            'no_ros': no_ros
                        },
                        cache: false,
                        dataType: 'json',
                        success: function(result) {
                            if (result == 1) {
                                swal({
                                    title: 'Success !',
                                    text: 'Data successfully deleted !',
                                    type: 'success'
                                });
                            } else {
                                swal({
                                    title: 'Failed !',
                                    text: 'Delete data failed !',
                                    type: 'error'
                                });
                            }

                            refresh_list_pib();
                            hitung_pib();
                        },
                        error: function(result) {
                            swal({
                                title: 'Error !',
                                text: 'Please try again later !',
                                type: 'error'
                            });
                        }
                    });
                }
            });
    });

    $(document).on('change', '.freight_cost_persen', function() {
        var ttl_total_price = $('.ttl_total_price').val();
        if (ttl_total_price == '' || ttl_total_price == null) {
            ttl_total_price = 1;
        } else {
            ttl_total_price = ttl_total_price.split(',').join('');
            ttl_total_price = parseFloat(ttl_total_price);
        }

        var persen = $(this).val();

        var nilai_freight = 0;
        if (persen > 0) {
            var nilai_freight = (ttl_total_price * persen / 100);
        }

        $('.freight_cost').val(nilai_freight);
        $('.freight_cost_val').html(nilai_freight.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }));
    });

    $(document).on('change', '.get_supplier', function() {
        var supplier = $(this).val();

        $.ajax({
            type: 'POST',
            url: siteurl + active_controller + 'get_po_by_supplier',
            data: {
                'supplier': supplier
            },
            cache: false,
            success: function(result) {
                $('.no_po').html(result);
                // $('.select2').select2();
            },
            error: function(result) {
                swal({
                    title: 'Error !',
                    text: 'Please try again later !',
                    type: 'error'
                });
            }
        });
    });

    $(document).on('click', '.add-row-child', function() {
        var id_po_detail = $(this).data('id');
        var $row = $(this).closest('tr');

        var newRow = `
        <tr class="child-${id_po_detail}">
            <td colspan="7"></td> <td><input type="text" name="dt[${id_po_detail}][berat_kotor][]" class="form-control auto_num text-end"></td>
            <td><input type="text" name="dt[${id_po_detail}][berat_bersih][]" class="form-control auto_num text-end"></td>
            <td><input type="text" name="dt[${id_po_detail}][length][]" class="form-control auto_num text-end"></td>
            <td><input type="text" name="dt[${id_po_detail}][biaya_masuk][]" class="form-control auto_num text-end calculate"></td>
            <td><input type="text" name="dt[${id_po_detail}][forwarding][]" class="form-control auto_num text-end calculate"></td>
            <td><input type="text" name="dt[${id_po_detail}][total_nilai][]" class="form-control auto_num text-end calculate"></td>
            <td><input type="text" name="dt[${id_po_detail}][no_coil][]" class="form-control"></td>
            <td class="text-center"><button type="button" class="btn btn-sm btn-danger remove-row"><i class="fa fa-trash"></i></button></td>
        </tr>
    `;

        // Masukkan baris baru tepat setelah baris material terakhir
        var lastChild = $(`.child-${id_po_detail}`).last();
        if (lastChild.length > 0) {
            lastChild.after(newRow);
        } else {
            $row.after(newRow);
        }

        $('.auto_num').autoNumeric('init');
    });

    // Fungsi Kalkulasi (Per Baris & Update Tabel Bawah)
    $(document).on('keyup change', '.calculate', function() {
        var row = $(this).closest('tr');
        var hargasatuan_rp = 0;

        // 1. Ambil Harga Satuan RP (dari baris sendiri atau baris parent)
        if (row.hasClass('row-material')) {
            hargasatuan_rp = parseFloat(row.find('.hargasatuan_rp').text().replace(/,/g, '')) || 0;
        } else {
            var parentId = row.attr('class').split('-')[1];
            hargasatuan_rp = parseFloat($(`.row-material[data-id="${parentId}"]`).find('.hargasatuan_rp').text().replace(/,/g, '')) || 0;
        }

        // 2. Kalkulasi Total per Baris
        var biaya_masuk = parseFloat(row.find('input[name*="biaya_masuk"]').val().replace(/,/g, '')) || 0;
        var forwarding = parseFloat(row.find('input[name*="forwarding"]').val().replace(/,/g, '')) || 0;

        var total_baris = hargasatuan_rp + biaya_masuk + forwarding;

        // Set Nilai Total di baris tersebut (jika kolom total adalah input, gunakan .val(), jika text gunakan .text())
        var total_col = row.find('input[name*="total_nilai"], .total_material');
        if (total_col.is('input')) {
            total_col.val(total_baris).autoNumeric('set', total_baris);
        } else {
            total_col.text(total_baris.toLocaleString('en-US'));
        }

        // 3. SUM TOTAL UNTUK TABEL BAWAH (BM & GRAND TOTAL)
        var sum_biaya_masuk = 0;
        var sum_grand_total = 0;

        // Loop semua baris yang punya class .calculate
        $('tr').each(function() {
            var bm = parseFloat($(this).find('input[name*="biaya_masuk"]').val()?.replace(/,/g, '')) || 0;
            var total = 0;

            // Ambil nilai total dari input atau dari text()
            var total_el = $(this).find('input[name*="total_nilai"], .total_material');
            if (total_el.is('input')) {
                total = parseFloat(total_el.val().replace(/,/g, '')) || 0;
            } else {
                total = parseFloat(total_el.text().replace(/,/g, '')) || 0;
            }

            sum_biaya_masuk += bm;
            sum_grand_total += total;
        });

        // Update Input BM di tabel bawah
        $('#cost_bm').val(sum_biaya_masuk).autoNumeric('set', sum_biaya_masuk);

        // Update Input Grand Total (di footer tabel atas)
        $('.ttl_price_detail_col').text(sum_grand_total.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }));

        $('.ttl_total_price').val(sum_grand_total).autoNumeric('set', sum_grand_total);
    });

    // Pastikan saat hapus baris, angka di bawah juga terupdate
    $(document).on('click', '.remove-row', function() {
        $(this).closest('tr').remove();
        $('.calculate').first().trigger('change'); // Pancing kalkulasi ulang
    });

    $(document).on('submit', '#frm-data', function(e) {
        e.preventDefault();

        // Ambil Grand Total dari footer tabel yang sudah diupdate otomatis oleh fungsi .calculate
        // Kita ambil dari text lalu bersihkan komanya
        var grand_total_text = $('.ttl_price_detail_col').text().split(',').join('');
        var ttl_price = parseFloat(grand_total_text) || 0;

        // Tambahan: Validasi jika input BM di bawah masih kosong padahal di tabel atas ada isinya
        var cost_bm = $('#cost_bm').val() ? parseFloat($('#cost_bm').val().split(',').join('')) : 0;

        // Jika ttl_price masih 0, cek apakah ada input manual di kolom Biaya Masuk/Forwarding
        if (ttl_price <= 0) {
            swal({
                title: 'Warning !',
                text: 'Please input the data correctly (Biaya Masuk/Forwarding/Qty) before save !',
                type: 'warning'
            });
            return false; // Berhenti di sini
        }

        // Jika sudah ada nilai, lanjutkan proses SweetAlert Save seperti biasa
        swal({
                title: "Warning !",
                text: "Data will be saved !",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Save",
                cancelButtonText: "Cancel",
                closeOnConfirm: false,
                closeOnCancel: true
            },
            function(isConfirm) {
                if (isConfirm) {
                    var formdata = new FormData($('#frm-data')[0]);
                    $.ajax({
                        type: 'POST',
                        url: siteurl + active_controller + '/save_ros',
                        data: formdata,
                        cache: false,
                        dataType: 'json',
                        processData: false,
                        contentType: false,
                        success: function(result) {
                            if (result.status == '1') {
                                swal({
                                    title: 'Success !',
                                    text: 'Success, ROS has been saved !',
                                    type: 'success'
                                });
                                window.location.href = siteurl + active_controller;
                            } else {
                                swal({
                                    title: 'Failed !',
                                    text: result.msg,
                                    type: 'error'
                                });
                            }
                        },
                        error: function() {
                            swal({
                                title: 'Error !',
                                text: 'Please try again later !',
                                type: 'error'
                            });
                        }
                    });
                }
            });
    });

    function get_list_detail_po(no_po = null, kurs_pib = 1) {
        $.ajax({
            type: "POST",
            url: siteurl + active_controller + '/get_no_po_detail',
            data: {
                'no_po': no_po,
                'kurs_pib': kurs_pib
            },
            cache: false,
            dataType: 'json',
            success: function(result) {
                $('.list_detail_po').html(result.list_detail_pr);
                $('.ttl_price_detail_col').html(result.ttl_price_detail.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));

                $('.auto_num').autoNumeric();
            },
            error: function(result) {
                swal({
                    title: 'Error !',
                    text: 'Please try again later !',
                    type: 'error'
                });
            }
        });
    }

    function hitung_pib() {
        var no_ros = $('.no_ros').val();

        var bm = $('.input_bm').val();
        if (bm == '' || bm == null) {
            bm = 0
        } else {
            bm = bm.split(',').join('');
            bm = parseFloat(bm);
        }

        var ppn = $('.input_ppn').val();
        if (ppn == '' || ppn == null) {
            ppn = 0
        } else {
            ppn = ppn.split(',').join('');
            ppn = parseFloat(ppn);
        }

        var pph = $('.input_pph').val();
        if (pph == '' || pph == null) {
            pph = 0
        } else {
            pph = pph.split(',').join('');
            pph = parseFloat(pph);
        }

        var total_pib = (bm + ppn + pph);
        var total_pib_custom = 0;
        $.ajax({
            type: 'POST',
            url: siteurl + active_controller + '/hitung_custom_pib',
            data: {
                'no_ros': no_ros
            },
            cache: false,
            dataType: 'json',
            success: function(result) {
                total_pib += result.ttl_custom_pib;
                var totalpib = total_pib.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });

                $('.total_pib').html(totalpib);
            },
            error: function(result) {
                swal({
                    title: 'Error !',
                    text: 'Please try again later !',
                    type: 'error'
                });
            }
        });




    }

    function refresh_list_pib() {
        var no_ros = $('.no_ros').val();

        $.ajax({
            type: 'POST',
            url: siteurl + active_controller + '/refresh_list_pib',
            data: {
                'no_ros': no_ros
            },
            cache: false,
            dataType: 'json',
            success: function(result) {
                $('.list_custom_pib').html(result.hasil);
                hitung_pib();

                $('.auto_num').autoNumeric();
            },
            error: function(result) {
                swal({
                    title: 'Error !',
                    text: 'Please try again later !',
                    type: 'error'
                });
            }
        });
    }

    function ttl_price() {
        var kurs_pib = $('.kurs_pib').val();
        // alert(kurs_pib);
        if (kurs_pib == '' || kurs_pib == null) {
            kurs_pib = 1;
        } else {
            kurs_pib = kurs_pib.split(',').join('');
            kurs_pib = parseFloat(kurs_pib);
        }

        var ttl_price = 0;
        $('.qty_packing_list').each(function() {
            var qty_pack = $(this).val();
            var hargasatuan = $(this).data('harga_satuan');

            if (qty_pack == '' || qty_pack == null) {
                qty_pack = 0;
            } else {
                qty_pack = qty_pack.split(',').join('');
                qty_pack = parseFloat(qty_pack);
            }

            ttl_price += ((hargasatuan * kurs_pib) * qty_pack);
        });

        $('.ttl_total_price').val(ttl_price);
        $('.ttl_price_detail_col').html(ttl_price.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }));
    }
</script>