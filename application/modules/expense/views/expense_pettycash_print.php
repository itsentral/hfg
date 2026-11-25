<html>

<head>
    <title> PRINT EXPENSE PETTY CASH </title>
</head>

<body>
    <style>
        body {
            font-family: sans-serif;
        }

        table.garis {
            border-collapse: collapse;
            font-size: 0.9em;
            font-family: sans-serif;
        }
    </style>
    <table cellpadding=2 cellspacing=0 border=0 width=650>
        <tr>
            <td colspan=8 style="border-bottom: 2px solid #333; padding-bottom: 6px;">
                <table cellpadding=0 cellspacing=0 border=0 width="100%">
                    <tr>
                        <td width="65" valign="top"><img src="<?= html_escape(get_company_logo('icon')) ?>" width="55" height="55" alt="Logo"></td>
                        <td valign="top" style="padding-left: 10px; font-size: 11px; line-height: 1.4;">
                            <strong style="font-size: 13px;"><?= html_escape(get_company_name(true, true)) ?></strong><br>
                            <?= get_company_address_html() ?>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <th colspan=8><br />Form Expense : Pettycash</th>
        </tr>
        <tr>
            <th colspan=8 align="left">Pettycash : <?= $data->pettycash ?></th>
        </tr>
        <tr>
            <td colspan=8>
                <table cellpadding=2 cellspacing=0 border=1 width=650 class="garis">
                    <tr>
                        <th nowrap>No</th>
                        <th nowrap>Tgl</th>
                        <th nowrap>Goods / Services</th>
                        <th nowrap>Description</th>
                        <th nowrap>Jml</th>
                        <th nowrap>Estimated Unit Cost</th>
                        <th nowrap>Total Cost</th>
                    </tr>
                    <?php $total_expense = 0;
                    $total_tol = 0;
                    $total_parkir = 0;
                    $total_kasbon = 0;
                    $idd = 1;
                    $total_km = 0;
                    $grand_total = 0;
                    $i = 0;
                    $gambar = "";
                    if (!empty($data_detail)) {
                        foreach ($data_detail as $record) {
                            $i++; ?>
                            <tr>
                                <td><?= $i; ?></td>
                                <td><?= date('d F Y', strtotime((string) $data->created_on)); ?></td>
                                <td><?= $record->deskripsi; ?></td>
                                <td><?= $record->keterangan; ?></td>
                                <td align="right"><?= number_format((float) $record->qty); ?></td>
                                <td align="right"><?= number_format((float) $record->harga); ?></td>
                                <td align="right"><?= number_format((float) $record->expense); ?></td>
                            </tr>
                    <?php
                            if ($record->doc_file != '' && file_exists('assets/expense/' . $record->doc_file)) {
                                if (strpos((string) $record->doc_file, 'pdf', 0) > 1) {
                                    $gambar .= '<iframe src="' . base_url('assets/expense/' . $record->doc_file) . '#toolbar=0&navpanes=0" title="PDF" style="width:600px; height:500px;" frameborder="0"></iframe><br /><br />';
                                } else {
                                    $gambar .= '<img src="' . base_url() . 'assets/expense/' . $record->doc_file . '" width="500"><br />';
                                }
                            }
                            $total_expense = ($total_expense + ($record->expense));
                            $idd++;
                        }
                    }
                    $grand_total = ($total_expense);
                    for ($x = 0; $x < (5 - $i); $x++) {
                        echo '
                            <tr>
                                <td>&nbsp;</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>   
                            </tr>
                        ';
                    }
                    ?>
                    <tr>
                        <td colspan=6 align=center><strong>Total</strong></td>
                        <td align="right"><?= number_format((float) $grand_total); ?></td>
                    </tr>
                </table>
            </td>
        </tr>

        <?php
        $pelapor = $this->db->query("SELECT b.nm_karyawan as name FROM users a left join employee b on a.employee_id=b.id WHERE a.username='" . $data->created_by . "'")->row();
        $mengetahui = $this->db->query("SELECT b.nm_karyawan as name FROM users a left join employee b on a.employee_id=b.id WHERE a.username='" . $data->approved_by . "'")->row();
        ?>


    </table>
    <table cellpadding=2 cellspacing=0 border=0 width=650 style="margin-top: 20px;">
        <tr>
            <td colspan=2 align=center>Mengajukan</td>
            <td width="150"></td>
            <td align=center colspan=3>Menyetujui</td>
            <td width="150"></td>
            <td align=center>Mengetahui</td>
        </tr>
        <tr>
            <td colspan=8>&nbsp;</td>
        </tr>
        <tr height=120>
            <td colspan=2 align=center nowrap valign="bottom" width=100>
                ______________________
            </td>
            <td width=25>&nbsp;</td>
            <td colspan=3 align=center nowrap valign="bottom" width=120>
                ______________________
            </td>
            <td>&nbsp;</td>
            <td colspan=3 align=center nowrap valign="bottom" width=120>
                ______________________
            </td>
        </tr>
    </table>
    <em>STM/FR02/09/01/00</em>

    <br />
    <?= $gambar ?>

    <script>
        window.print();
    </script>
</body>

</html>