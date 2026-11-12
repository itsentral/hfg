<?php
$reject_msg = '';
if (isset($data->reject_reason) && !empty($data->reject_reason)) {
	$reject_msg = $data->reject_reason;
} elseif (isset($data->st_reject) && !empty($data->st_reject)) {
	$reject_msg = $data->st_reject;
}
?>

<?php if (!empty($reject_msg)) : ?>
	<div class="alert alert-danger border-0 shadow-sm rounded-3 p-3 mb-4 d-flex align-items-start gap-3">
		<div class="fs-3 text-danger"><i class="fa fa-ban"></i></div>
		<div>
			<h6 class="fw-bold mb-1 text-danger">Pengajuan Expense Ditolak!</h6>
			<p class="mb-0 text-dark small"><?= nl2br(htmlspecialchars($reject_msg)) ?></p>
		</div>
	</div>
<?php endif; ?>
