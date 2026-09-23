<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0 fw-bold text-primary">
            <i class="fa fa-line-chart me-2"></i><?= $title ?>
        </h5>
    </div>
    <div class="card-body">
        <form action="#" method="POST" id="form_proses_bro">
            <div class="row g-3 mb-4 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-bold">Month</label>
                    <select id="bulan" name="bulan" class="form-select form-select-sm">
                        <option value="0">All Month</option>
                        <?php
                        $months = array(
                            "01" => "January", "02" => "February", "03" => "March",
                            "04" => "April", "05" => "May", "06" => "June",
                            "07" => "July", "08" => "August", "09" => "September",
                            "10" => "October", "11" => "November", "12" => "December"
                        );
                        $current_month = date('m');
                        foreach ($months as $num => $name) {
                            $selected = ($num == $current_month) ? 'selected' : '';
                            echo "<option value='{$num}' {$selected}>{$name}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Year</label>
                    <select id="tahun" name="tahun" class="form-select form-select-sm">
                        <option value="0">All Year</option>
                        <?php
                        $current_year = date('Y');
                        for ($y = $current_year; $y >= $current_year - 10; $y--) {
                            echo "<option value='{$y}'>{$y}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Category</label>
                    <select id="kategory" name="kategory" class="form-select form-select-sm">
                        <option value="0">All Category</option>
                        <?php foreach ($kategori as $valx): ?>
                            <option value="<?= $valx['id'] ?>"><?= strtoupper($valx['nm_category']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-primary btn-sm w-100" id="search_filter">
                        <i class="fa fa-search me-1"></i> Filter Data
                    </button>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table id="example1" class="table table-bordered table-striped table-hover align-middle w-100">
                <thead class="table-primary text-center">
                    <tr>
                        <th width="4%">#</th>
                        <th>Kode Asset</th>
                        <th>Asset Name</th>
                        <th width="8%">Tgl Perolehan</th>
                        <th width="10%">Category</th>
                        <th width="15%">Kelompok Penyusutan</th>
                        <th width="10%">Costcenter</th>
                        <th width="6%">Depresiasi</th>
                        <th width="10%" class="text-end">Nilai Perolehan</th>
                        <th width="9%" class="text-end">Depresiasi /Bln</th>
                        <th width="10%" class="text-end">Total Depresiasi</th>
                        <th width="10%" class="text-end">Sisa Nilai</th>
                    </tr>
                </thead>
                <tbody></tbody>
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="8" class="text-end">Total:</td>
                        <td class="text-end"></td>
                        <td class="text-end"></td>
                        <td class="text-end"></td>
                        <td class="text-end"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        var kdcab = $('#kdcab').val();
        var tgl = $('#tanggalx').val();
        var kategori = $('#kategory').val();
        var bulan = $('#bulan').val();
        var tahun = $('#tahun').val();
        DataTables(kdcab, tgl, kategori, bulan, tahun);
    });

    $(document).on('click', '#search_filter', function(e) {
        e.preventDefault();
        var kdcab = $('#kdcab').val();
        var tgl = $('#tanggalx').val();
        var kategori = $('#kategory').val();
        var bulan = $('#bulan').val();
        var tahun = $('#tahun').val();
        DataTables(kdcab, tgl, kategori, bulan, tahun);
    });

    function DataTables(kdcab=null, tgl=null, kategori=null, bulan=null, tahun=null) {
        let total_aset = 0;
        let total_susut = 0;
        let total_susut_ak = 0;
        let total_sisa = 0;

        $('#example1').DataTable({
            "processing": true,
            "serverSide": true,
            "stateSave": true,
            "autoWidth": false,
            "destroy": true,
            "responsive": true,
            "aaSorting": [[1, "asc"]],
            "columnDefs": [
                { "targets": 'no-sort', "orderable": false },
                { className: 'text-end', targets: [8, 9, 10, 11] }
            ],
            "iDisplayLength": 10,
            "aLengthMenu": [[10, 20, 50, 100], [10, 20, 50, 100]],
            "ajax": {
                url: base_url + active_controller + '/data_side_depreciation',
                type: "post",
                data: function(d) {
                    d.kdcab = kdcab;
                    d.tgl = tgl;
                    d.kategori = kategori;
                    d.bulan = bulan;
                    d.tahun = tahun;
                },
                dataSrc: function(data) {
                    total_aset = data.recordsAset;
                    total_susut = data.recordsSusut;
                    total_susut_ak = data.recordsSusutAk;
                    total_sisa = data.recordsSisa;
                    return data.data;
                }
            },
            drawCallback: function(settings) {
                var api = this.api();
                $(api.column(8).footer()).html("<div class='text-end'>" + number_format(total_aset) + "</div>");
                $(api.column(9).footer()).html("<div class='text-end'>" + number_format(total_susut) + "</div>");
                $(api.column(10).footer()).html("<div class='text-end'>" + number_format(total_susut_ak) + "</div>");
                $(api.column(11).footer()).html("<div class='text-end'>" + number_format(total_sisa) + "</div>");
            }
        });
    }

    function number_format(number, decimals, dec_point, thousands_sep) {
        number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
        var n = !isFinite(+number) ? 0 : +number,
            prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
            sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
            dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
            s = '',
            toFixedFix = function(n, prec) {
                var k = Math.pow(10, prec);
                return '' + Math.round(n * k) / k;
            };
        s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
        if (s[0].length > 3) {
            s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
        }
        if ((s[1] || '').length < prec) {
            s[1] = s[1] || '';
            s[1] += new Array(prec - s[1].length + 1).join('0');
        }
        return s.join(dec);
    }
</script>
