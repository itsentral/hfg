	//List
  	$(function() {
    	$("#mytabledata").DataTable();
    	$("#form-data").hide();
  	});
	//Back to list
  	function list_view(){
		$(".box").show();
		$("#form-data").hide();
	}
	//Create
  	function data_add(){
		$(".box").hide();
		$("#form-data").show();
		$("#form-data").load(url_add);
	}
	//Edit
  	function data_edit(id){
		if(id!=""){
			$(".box").hide();
			$("#form-data").show();
			$("#form-data").load(url_edit+id);
		}
	}
	//View
  	function data_view(id){
		if(id!=""){
			$(".box").hide();
			$("#form-data").show();
			$("#form-data").load(url_view+id);
		}
	}
	//Delete
	function data_delete(id){
		Swal.fire({
			title: "Anda Yakin?",
			text: "Data Akan Dihapus!",
			icon: "warning",
			showCancelButton: true,
			confirmButtonColor: "#dc3545",
			confirmButtonText: "Ya, hapus!",
			cancelButtonText: "Tidak!"
		}).then(function(result){
			if (result.isConfirmed) {
				$.ajax({
					url: url_delete+id,
					dataType: "json",
					type: 'POST',
					success: function(msg){
						if(msg['delete']=='1'){
							Swal.fire({
								title: "Terhapus!",
								text: "Data berhasil dihapus",
								icon: "success",
								timer: 1500,
								showConfirmButton: false
							}).then(function(){ window.location.reload(); });
						} else {
							Swal.fire({
								title: "Gagal!",
								text: "Data gagal dihapus",
								icon: "error",
								confirmButtonText: "OK"
							});
						}
						console.log(msg);
					},
					error: function(msg){
						Swal.fire({
							title: "Gagal!",
							text: "Gagal Eksekusi Ajax",
							icon: "error",
							confirmButtonText: "OK"
						});
						console.log(msg);
					}
				});
			}
		});
	}

    function cancel(){
        $(".box").show();
        $("#form-data").hide();
    }

    function data_save(){
		Swal.fire({
			title: "Anda Yakin?",
			text: "Data Akan Disimpan!",
			icon: "info",
			showCancelButton: true,
			confirmButtonText: "Ya, simpan!",
			cancelButtonText: "Tidak!"
		}).then(function(result){
			if (result.isConfirmed) {
				var formdata = $("#frm_data").serialize();
				$.ajax({
					url: url_save,
					dataType: "json",
					type: 'POST',
					data: formdata,
					success: function(msg){
						if(msg['save']=='1'){
							Swal.fire({
								title: "Sukses!",
								text: "Data Berhasil Di Simpan",
								icon: "success",
								timer: 1500,
								showConfirmButton: false
							}).then(function(){ cancel(); window.location.reload(); });
						} else {
							Swal.fire({
								title: "Gagal!",
								text: "Data Gagal Di Simpan",
								icon: "error",
								confirmButtonText: "OK"
							});
						}
						console.log(msg);
					},
					error: function(msg){
						Swal.fire({
							title: "Gagal!",
							text: "Ajax Data Gagal Di Proses",
							icon: "error",
							confirmButtonText: "OK"
						});
						console.log(msg);
					}
				});
			}
		});
	}

    function data_save_detail(){
		Swal.fire({
			title: "Anda Yakin?",
			text: "Data Akan Disimpan!",
			icon: "info",
			showCancelButton: true,
			confirmButtonText: "Ya, simpan!",
			cancelButtonText: "Tidak!"
		}).then(function(result){
			if (result.isConfirmed) {
				var formdata = $("#form_modal").serialize();
				$.ajax({
					url: url_save_detail,
					dataType: "json",
					type: 'POST',
					data: formdata,
					success: function(msg){
						if(msg['save']=='1'){
							Swal.fire({
								title: "Sukses!",
								text: "Data Berhasil Di Simpan",
								icon: "success",
								timer: 1500,
								showConfirmButton: false
							}).then(function(){ reload_detail(); });
						} else {
							Swal.fire({
								title: "Gagal!",
								text: "Data Gagal Di Simpan",
								icon: "error",
								confirmButtonText: "OK"
							});
						}
						console.log(msg);
					},
					error: function(msg){
						Swal.fire({
							title: "Gagal!",
							text: "Ajax Data Gagal Di Proses",
							icon: "error",
							confirmButtonText: "OK"
						});
						console.log(msg);
					}
				});
			}
		});
	}

	//Delete detail
	function data_delete_detail(id){
		Swal.fire({
			title: "Anda Yakin?",
			text: "Data Akan Dihapus!",
			icon: "warning",
			showCancelButton: true,
			confirmButtonColor: "#dc3545",
			confirmButtonText: "Ya, hapus!",
			cancelButtonText: "Tidak!"
		}).then(function(result){
			if (result.isConfirmed) {
				$.ajax({
					url: url_delete_detail+id,
					dataType: "json",
					type: 'POST',
					success: function(msg){
						if(msg['delete']=='1'){
							Swal.fire({
								title: "Terhapus!",
								text: "Data berhasil dihapus",
								icon: "success",
								timer: 1500,
								showConfirmButton: false
							}).then(function(){ reload_detail(); });
						} else {
							Swal.fire({
								title: "Gagal!",
								text: "Data gagal dihapus",
								icon: "error",
								confirmButtonText: "OK"
							});
						}
						console.log(msg);
					},
					error: function(msg){
						Swal.fire({
							title: "Gagal!",
							text: "Gagal Eksekusi Ajax",
							icon: "error",
							confirmButtonText: "OK"
						});
						console.log(msg);
					}
				});
			}
		});
	}

    function data_save_self(){
		Swal.fire({
			title: "Anda Yakin?",
			text: "Data Akan Disimpan!",
			icon: "info",
			showCancelButton: true,
			confirmButtonText: "Ya, simpan!",
			cancelButtonText: "Tidak!"
		}).then(function(result){
			if (result.isConfirmed) {
				var formdata = $("#frm_data").serialize();
				$.ajax({
					url: url_save,
					dataType: "json",
					type: 'POST',
					data: formdata,
					success: function(msg){
						if(msg['save']=='1'){
							Swal.fire({
								title: "Sukses!",
								text: "Data Berhasil Di Simpan",
								icon: "success",
								timer: 1500,
								showConfirmButton: false
							}).then(function(){ $("#form-data").load(url_edit+msg['id']); });
						} else {
							Swal.fire({
								title: "Gagal!",
								text: "Data Gagal Di Simpan",
								icon: "error",
								confirmButtonText: "OK"
							});
						}
						console.log(msg);
					},
					error: function(msg){
						Swal.fire({
							title: "Gagal!",
							text: "Ajax Data Gagal Di Proses",
							icon: "error",
							confirmButtonText: "OK"
						});
						console.log(msg);
					}
				});
			}
		});
	}
