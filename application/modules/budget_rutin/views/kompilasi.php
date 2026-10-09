<div class="card border-0 shadow-sm mb-4" id="card-kompilasi">
    <div class="card-header bg-white py-3 border-bottom">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3">
                    <i class="fa fa-layer-group fs-4"></i>
                </div>
                <div>
                    <h5 class="card-title fw-bold text-dark mb-0">Kompilasi Budget Stock Rutin</h5>
                    <small class="text-muted">Rekapitulasi total alokasi kebutuhan barang per warehouse / gudang</small>
                </div>
            </div>
            <div>
                <a class="btn btn-sm btn-outline-secondary" href="<?= base_url('budget_rutin') ?>">
                    <i class="fa fa-arrow-left me-1"></i> Kembali ke Daftar
                </a>
            </div>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover align-middle mb-0" id="example1" style="width: 100%;">
                <thead class="table-light text-nowrap">
                    <tr>
                        <th class="text-center" width="40">#</th>
                        <th>Kategori</th>
                        <th>Nama Barang</th>
                        <th>Spesifikasi</th>
                        <th>Brand</th>
                        <th class="text-center bg-primary-subtle text-primary">Total</th>
                        <th class="text-center">Unit</th>
                        <?php
                        foreach ($group_header as $val => $valx) {
                            $cc = '';
                            if ($valx['cost_center'] != '0' && !empty($valx['cost_center'])) {
                                $cc = $valx['cost_center'];
                            }
                            echo "<th class='text-center'><u>" . strtoupper($valx['nm_dept']) . "</u>" . (!empty($cc) ? "<br><small class='text-muted'>" . $cc . "</small>" : "") . "</th>";
                        }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($group_barang as $val => $valx) {
                        $val++;
                        echo "<tr>";
                        echo "<td class='text-center text-muted small'>" . $val . "</td>";
                        echo "<td class='text-nowrap'><span class='badge bg-light text-dark border'>" . strtoupper($valx['jenisbarang']) . "</span></td>";
                        echo "<td><strong class='text-dark'>" . strtoupper($valx['nama']) . "</strong></td>";
                        echo "<td class='small text-muted'>" . strtoupper($valx['spec1']) . "</td>";
                        echo "<td class='small text-muted'>" . strtoupper($valx['spec2']) . "</td>";
                        $total_kebutuhan = 0;
                        foreach ($group_header as $val2 => $valx2) {
                            $get_qty = $this->db->query("SELECT a.kebutuhan_month FROM budget_rutin_detail a LEFT JOIN budget_rutin_header b ON a.code_budget=b.code_budget WHERE a.id_barang='" . $valx['id_barang'] . "' AND b.department='" . $valx2['department'] . "' AND b.costcenter='" . $valx2['costcenter'] . "' ")->result();
                            $total_kebutuhan += (!empty($get_qty)) ? $get_qty[0]->kebutuhan_month : 0;
                        }
                        echo "<td class='text-center font-monospace fw-bold text-primary bg-primary-subtle'>" . number_format($total_kebutuhan, 0, ',', '.') . "</td>";
                        echo "<td class='text-center'><span class='badge bg-secondary-subtle text-secondary border'>" . strtoupper($valx['satuan']) . "</span></td>";
                        foreach ($group_header as $val2 => $valx2) {
                            $get_qty = $this->db->query("SELECT a.kebutuhan_month FROM budget_rutin_detail a LEFT JOIN budget_rutin_header b ON a.code_budget=b.code_budget WHERE a.id_barang='" . $valx['id_barang'] . "' AND b.department='" . $valx2['department'] . "' AND b.costcenter='" . $valx2['costcenter'] . "' ")->result();
                            $qty = (!empty($get_qty) && $get_qty[0]->kebutuhan_month > 0) ? number_format($get_qty[0]->kebutuhan_month, 0, ',', '.') : '-';
                            echo "<td class='text-center font-monospace'>" . $qty . "</td>";
                        }
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-end pt-3 border-top mt-3">
            <a class="btn btn-outline-secondary px-3" href="<?= base_url('budget_rutin') ?>">
                <i class="fa fa-arrow-left me-1"></i> Kembali ke Daftar
            </a>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script src="https://cdn.datatables.net/buttons/1.6.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.1/js/buttons.html5.min.js"></script>

<script>
    $(function() {
        $("#example1").DataTable({
            paging: true,
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
            dom: '<"d-flex flex-wrap justify-content-between align-items-center mb-2"lfB>rt<"d-flex flex-wrap justify-content-between align-items-center mt-2"ip>',
            buttons: [
                {
                    extend: 'excel',
                    text: '<i class="fa fa-file-excel me-1 text-success"></i> Export Excel',
                    className: 'btn btn-sm btn-outline-success',
                }
            ],
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Menampilkan 0 data",
                infoFiltered: "(disaring dari _MAX_ total data)",
                zeroRecords: "Tidak ada data yang cocok",
                paginate: {
                    first: "Awal",
                    last: "Akhir",
                    next: "Lanjut",
                    previous: "Sebelum"
                }
            }
        });
    });
</script>
