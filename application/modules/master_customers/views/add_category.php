<?php
$category = $results['category'] ?? null;
?>
<div class="row g-3">

	<div class="col-md-3">
		<label class="form-label mb-0">ID Kategori Customer</label>
	</div>
	<div class="col-md-9">
		<input type="text"
			class="form-control"
			name="id_category"
			value="<?= $category->id_category ?? '' ?>"
			readonly>
	</div>

	<div class="col-md-3">
		<label class="form-label mb-0">
			Kategori Customer <span class="text-danger">*</span>
		</label>
	</div>
	<div class="col-md-9">
		<input type="text"
			class="form-control"
			name="name_category"
			value="<?= $category->name_category ?? '' ?>"
			placeholder="Category Customer"
			required>
	</div>

</div>