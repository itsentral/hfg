<form action="#" method="POST" id="form_proses_bro" enctype="multipart/form-data" autocomplete='off'>
	<div class="card shadow-sm border-0">
		<div class="card-body">
			<div class="form-group row">
				<label class="label-control col-sm-2"><b>Metode Pembelian <span class="text-danger">*</span></b></label>
				<div class="col-sm-4">
					<select id="jenis_pembelian" name="jenis_pembelian" class="form-control input-sm">
						<option value="0">Select Type</option>
						<option value="po">PO</option>
						<option value="non po">NON PO</option>
					</select>
				</div>

				<label class="label-control col-sm-2"><b>Category <span class="text-danger">*</span></b></label>
				<div class="col-sm-4">
					<select id="category" name="category" class="form-control input-sm">
						<option value="0">Select Category</option>
						<option value="material">MATERIAL</option>
						<option value="stok">STOK</option>
						<option value="departemen">DEPARTEMEN</option>
						<option value="asset">ASSET</option>
					</select>
				</div>
			</div>

			<hr>

			<div class="table-responsive">
				<table class="table table-bordered table-striped" id="my-grid3" width="100%">
					<thead>
						<tr>
							<th class="text-center no-sort" width="4%">#</th>
							<th class="text-center" width="7%">No PR</th>
							<th class="text-center" width="10%">Tanggal PR</th>
							<th class="text-center">Item</th>
							<th class="text-center">Nama Lain</th>
							<th class="text-center">QTY</th>
							<th class="text-center" width="10%">Category</th>
							<th class="text-center" width="10%">Dibutuhkan</th>
							<th class="text-center" width="9%">Request By</th>
							<th class="text-center" width="10%">Request Date</th>
						</tr>
					</thead>
					<tbody></tbody>
				</table>
			</div>

			<div class="text-center mt-3">
				<?php
				echo form_button([
					'type' => 'button',
					'class' => 'btn btn-dark',
					'style' => 'min-width:100px;',
					'content' => '<i class="fas fa-reply"></i> Kembali',
					'id' => 'back'
				]);
				echo ' ';
				echo form_button([
					'type' => 'button',
					'class' => 'btn btn-success',
					'style' => 'min-width:100px;',
					'content' => '<i class="fas fa-save"></i> Buat PO',
					'id' => 'save_rfq'
				]);
				?>
			</div>
		</div>
	</div>
</form>

<div class="modal fade" id="ModalView2" style="overflow-y: auto;">
	<div class="modal-dialog" style="width: 80%;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title" id="head_title2"></h4>
			</div>
			<div class="modal-body" id="view2"></div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<script src="https://cdn.datatables.net/2.0.5/js/dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js" integrity="sha512-rMGGF4wg1R73ehtnxXBt5mbUfN9JUJwbk21KMlnLZDJh7BkPmeovBuddZCENJddHYYMkCh9hPFnPmS9sspki8g==" crossorigin="anonymous"></script>

<script>
	$(document).ready(function() {
		$('.chosen-select').chosen();
		$('.group-po').hide();
		$('.group-nonpo').hide();

		var category = $('#category').val();
		DataTables3(category);

		$(document).on('change', '#category', function(e) {
			e.preventDefault();
			var category = $('#category').val();
			DataTables3(category);
		});

		$(document).on('change', '#jenis_pembelian', function(e) {
			e.preventDefault();
			var jenis_pembelian = $('#jenis_pembelian').val();
			if (jenis_pembelian == 'po') {
				$('.group-po').show();
				$('.group-nonpo').hide();
			} else {
				$('.group-po').hide();
				$('.group-nonpo').show();
			}
		});
	});

	$(document).on('click', '#back', function() {
		window.location.href = base_url + active_controller + 'index_pr';
	});

	$(document).on('click', '#save_rfq', function(e) {
		e.preventDefault();

		var jenis_pembelian = $('#jenis_pembelian').val();
		var category = $('#category').val();

		if (jenis_pembelian == '0') {
			swal({
				title: "Error Message!",
				text: 'Jenis Pembelian Not Select, please input first ...',
				type: "warning"
			});
			return false;
		}

		if (category == '0') {
			swal({
				title: "Error Message!",
				text: 'Category Not Select, please input first ...',
				type: "warning"
			});
			return false;
		}

		if ($('input[type=checkbox]:checked').length == 0) {
			swal({
				title: "Error Message!",
				text: 'Checklist Minimal One Component',
				type: "warning"
			});
			return false;
		}

		swal({
			title: "Are you sure?",
			text: "You will be able to process again this data!",
			type: "warning",
			showCancelButton: true,
			confirmButtonClass: "btn-danger",
			confirmButtonText: "Yes, Process it!",
			cancelButtonText: "No, cancel process!",
			closeOnConfirm: false,
			closeOnCancel: false
		}, function(isConfirm) {
			if (isConfirm) {
				var formData = new FormData($('#form_proses_bro')[0]);
				$.ajax({
					url: base_url + active_controller + '/save_rfq',
					type: "POST",
					data: formData,
					cache: false,
					dataType: 'json',
					processData: false,
					contentType: false,
					success: function(data) {
						if (data.status == 1) {
							swal({
								title: "Save Success!",
								text: data.pesan,
								type: "success",
								timer: 7000,
								showCancelButton: false,
								showConfirmButton: false,
								allowOutsideClick: false
							});
							window.location.href = base_url + active_controller + '/index_pr';
						} else {
							swal({
								title: "Save Failed!",
								text: data.pesan,
								type: "warning",
								timer: 7000,
								showCancelButton: false,
								showConfirmButton: false,
								allowOutsideClick: false
							});
						}
					},
					error: function() {
						swal({
							title: "Error Message!",
							text: 'An Error Occured During Process. Please try again..',
							type: "warning",
							timer: 7000,
							showCancelButton: false,
							showConfirmButton: false,
							allowOutsideClick: false
						});
					}
				});
			} else {
				swal("Cancelled", "Data can be processed again :)", "error");
			}
		});
	});

	$(document).on('click', '.changeCheckList', function() {
		let id = $(this).val();
		let flag = $(this).is(':checked') ? 1 : 0;
		$.ajax({
			url: base_url + active_controller + '/save_checked_rfq',
			type: "POST",
			data: {
				"id": id,
				"flag": flag
			},
			cache: false,
			dataType: 'json',
			success: function(data) {
				console.log(data.pesan);
			},
			error: function() {
				console.log('error connection serve !');
			}
		});
	});

	function DataTables3(category = null) {
		var dataTable = $('#my-grid3').DataTable({
			"processing": true,
			"serverSide": true,
			"stateSave": true,
			"bAutoWidth": true,
			"destroy": true,
			"responsive": true,
			"aaSorting": [
				[1, "asc"]
			],
			"columnDefs": [{
				"targets": 'no-sort',
				"orderable": false,
			}],
			"sPaginationType": "simple_numbers",
			"iDisplayLength": 10,
			"aLengthMenu": [
				[10, 20, 50, 100, 150],
				[10, 20, 50, 100, 150]
			],
			"ajax": {
				url: base_url + active_controller + '/server_side_list_pr',
				type: "post",
				data: function(d) {
					d.category = category;
				},
				cache: false,
				error: function() {
					$(".my-grid-error").html("");
					$("#my-grid").append('<tbody class="my-grid-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
					$("#my-grid_processing").css("display", "none");
				}
			}
		});
	}
</script>