<?php
if (class_exists('mPDF')) {
    $mpdf = new mPDF('utf-8', 'A4');
} elseif (class_exists('\Mpdf\Mpdf')) {
    $mpdf = new \Mpdf\Mpdf(['format' => 'A4']);
} else {
    require_once FCPATH . 'vendor/autoload.php';
    $mpdf = new \Mpdf\Mpdf(['format' => 'A4']);
}

set_time_limit(0);
ini_set('memory_limit', '1024M');

ob_start();
date_default_timezone_set('Asia/Jakarta');
$today = date('l, d F Y [H:i:s]');

$rest_d		= $this->db->get_where('warehouse_adjustment_detail', array('kode_trans' => $kode_trans))->result_array();
$rest_data 	= $this->db->get_where('warehouse_adjustment', array('kode_trans' => $kode_trans))->result_array();

$TANGGAL = (!empty($rest_data[0]['tanggal'])) ? date('d F Y', strtotime($rest_data[0]['tanggal'])) : (isset($rest_data[0]['created_date']) ? date('d F Y', strtotime($rest_data[0]['created_date'])) : date('d F Y'));
$no_ipp = isset($rest_data[0]['no_ipp']) ? $rest_data[0]['no_ipp'] : '-';
$pic = isset($rest_data[0]['pic']) ? $rest_data[0]['pic'] : '-';
$note = isset($rest_data[0]['note']) ? $rest_data[0]['note'] : '-';
?>

<table class="gridtable2" border='1' width='100%' cellpadding='2'>
	<tr>
		<td align='center'><b>PT. HARAPAN FAJAR GEMILANG</b></td>
	</tr>
	<tr>
		<td align='center'><b><h2>TANDA TERIMA BARANG ASSET</h2></b></td>
	</tr>
</table>
<br>
<br>
<table class="gridtable2" width="100%" border='0'>
	<thead>
		<tr>
			<td class="mid" width='15%'>No Transaksi</td>
			<td class="mid" width='2%'>:</td>
			<td class="mid" colspan='4'><?= $kode_trans; ?></td>
		</tr>
		<tr>
			<td class="mid" width='15%'>No PO / Non-PO</td>
			<td class="mid" width='2%'>:</td>
			<td class="mid" width='33%'><?= $no_ipp; ?></td>
			<td class="mid" width='10%'>PIC</td>
			<td class="mid" width='2%'>:</td>
			<td class="mid" width='38%'><?= $pic; ?></td>
		</tr>
		<tr>
			<td class="mid">Tanggal Terima</td>
			<td class="mid">:</td>
			<td class="mid"><?= $TANGGAL; ?></td>
			<td class="mid">Note</td>
			<td class="mid">:</td>
			<td class="mid"><?= $note; ?></td>
		</tr>
	</thead>
</table><br>
<table class="gridtable" width='100%' border='1' cellpadding='2'>
	<thead align='center'>
		<tr>
            <th class="mid" style='vertical-align:middle;' width='4%'>#</th>
            <th class="mid" style='vertical-align:middle;'>Nama Barang / Asset</th>
            <th class="mid" style='vertical-align:middle;' width='10%'>Qty</th>
            <th class="mid" style='vertical-align:middle;' width='25%'>Keterangan</th>
            <th class="mid" style='vertical-align:middle;' width='20%'>Pemeriksa</th> 
		</tr>
	</thead>
	<tbody>
		<?php
		$No = 0;
		if (!empty($rest_d)) {
			foreach ($rest_d as $val => $valx) {
				$No++;
				$qty_oke 		= number_format($valx['qty_oke'], 2);
				$keterangan 	= (!empty($valx['keterangan'])) ? ucfirst($valx['keterangan']) : '-';
				$pemeriksa 	= (!empty($valx['ket_req_po'])) ? ucfirst($valx['ket_req_po']) : ((!empty($valx['ket_req_pro'])) ? ucfirst($valx['ket_req_pro']) : '-');
				
				echo "<tr>";
					echo "<td align='center'>" . $No . "</td>";
					echo "<td>" . strtoupper($valx['nm_material']) . "</td>";
					echo "<td align='center'>" . $qty_oke . "</td>";
					echo "<td>" . $keterangan . "</td>";
					echo "<td>" . $pemeriksa . "</td>";
				echo "</tr>";
			}
		} else {
			echo "<tr><td colspan='5' align='center'>Data item tidak ditemukan</td></tr>";
		}
		?>
	</tbody>
</table><br><br><br>
<table class="gridtable2" width='100%' border='0' cellpadding='2'>
	<tr>
		<td width='65%'></td>
		<td align='center'></td>
		<td></td>
		<td width='5%'></td>
		<td align='center'>Ttd,</td>
		<td></td>
	</tr>
	<tr>
		<td height='50px'></td>
		<td></td>
		<td></td>
		<td></td>
		<td></td>
		<td></td>
	</tr>
	<tr>
		<td></td>
		<td align='center'></td>
		<td></td>
		<td></td>
		<td align='center'>QC / Penerima</td>
		<td></td>
	</tr>
</table>

<style type="text/css">
	@page {
		margin-top: 1cm;
		margin-left: 0.5cm;
		margin-right: 0.5cm;
		margin-bottom: 1cm;
	}
	.mid {
		vertical-align: middle !important;
	}
	table.gridtable {
		font-family: verdana,arial,sans-serif;
		font-size: 10px;
		color: #333333;
		border-width: 1px;
		border-color: #666666;
		border-collapse: collapse;
	}
	table.gridtable th {
		border-width: 1px;
		padding: 6px;
		border-style: solid;
		border-color: #666666;
		background-color: #f2f2f2;
	}
	table.gridtable td {
		border-width: 1px;
		padding: 6px;
		border-style: solid;
		border-color: #666666;
		background-color: #ffffff;
	}
	table.gridtable2 {
		font-family: verdana,arial,sans-serif;
		font-size: 11px;
		color: #333333;
		border-width: 1px;
		border-color: #666666;
		border-collapse: collapse;
	}
	table.gridtable2 th, table.gridtable2 td {
		border-width: 1px;
		padding: 3px;
		border-style: none;
		border-color: #666666;
	}
</style>

<?php
$print_user = !empty($printby) ? $printby : 'admin';
$footer = "Printed by : " . ucfirst(strtolower($print_user)) . ", " . $today . " / " . $kode_trans;
$html = ob_get_contents();
ob_end_clean();
$mpdf->SetTitle($kode_trans);
$mpdf->AddPage();
$mpdf->SetFooter($footer);
$mpdf->WriteHTML($html);
$mpdf->Output('tanda-terima-incoming-asset-' . $kode_trans . '.pdf', 'I');
?>
