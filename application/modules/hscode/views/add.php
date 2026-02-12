<!-- ✅ Card Berry -->
<div class="card">
    <div class="card-body">
        <form id="data-form" method="post">
            <div class="col-md-12">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h5 class="mb-0 fw-bold">Form HS Code</h5>
                    <span class="text-muted small">(*) wajib diisi</span>
                </div>
                <hr class="mt-2">

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group row mb-2">
                            <div class="col-md-4">
                                <label>ID HS Code</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="id" placeholder="ID HS Code" value="<?= isset($hs) ? $hs->id : null; ?>" readonly>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="row">
                    <!-- Sisi Kiri -->
                    <div class="col-md-6">
                        <div class="form-group row mb-2">
                            <div class="col-md-4">
                                <label for="local_code">Local Code <span class="text-danger">*</span></label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" id="local_code" value="<?= isset($hs) ? $hs->local_code : null; ?>" required name="local_code" placeholder="Local Code">
                            </div>
                        </div>

                        <div class="form-group row mb-2">
                            <div class="col-md-4">
                                <label for="origin_code">Origin Code <span class="text-danger">*</span></label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" id="origin_code" value="<?= isset($hs) ? $hs->origin_code : ''; ?>" required name="origin_code" placeholder="Origin Code">
                            </div>
                        </div>

                        <div class="form-group row mb-2">
                            <div class="col-md-4">
                                <label for="brand">Type</label>
                            </div>
                            <div class="col-md-8">
                                <textarea class="form-control" rows="4" id="brand" name="brand" placeholder="Type"><?= isset($hs) ? $hs->brand : ''; ?></textarea>
                            </div>
                        </div>
                    </div>
                    <!-- Sisi Kanan -->
                    <div class="col-md-6">
                        <div class="form-group row mb-2">
                            <div class="col-md-4">
                                <label for="lartas">Lartas</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="lartas" placeholder="Lartas">
                            </div>
                        </div>
                        <div class="form-group row mb-3">
                            <div class="col-md-4">
                                <label for="ls_yes">LS <span class="text-danger">*</span></label>
                            </div>
                            <div class="col-md-8">
                                <div class="row mt-2">
                                    <div class="col-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" id="ls_yes" name="ls" value="Y" <?= isset($hs) && ($hs->ls == "Y") ? 'checked' : ''; ?> required>
                                            <label class="form-check-label" for="ls">Aktif</label>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" id="ls_no" name="ls" value="N" <?= isset($hs) && ($hs->ls == "N") ? 'checked' : ''; ?> required>
                                            <label class="form-check-label" for="ls">Non Aktif</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row mb-2">
                            <div class="col-md-4">
                                <label for="description">Description <span class="text-danger">*</span></label>
                            </div>
                            <div class="col-md-8">
                                <textarea class="form-control" rows="4" required id="description" name="description" placeholder="Description"><?= isset($hs) ? $hs->description : ''; ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <!-- Sisi Kiri -->
                    <div class="col-md-6">
                        <div class="form-group row mb-2">
                            <div class="col-md-4">
                                <label for="bm_mfn">BM MFN </label>
                            </div>
                            <div class="col-md-8">
                                <div class="input-group">
                                    <input type="number" step="0.01" class="form-control text-end" id="bm_mfn" name="bm_mfn" value="<?= isset($hs) ? $hs->bm_mfn : '' ?>" placeholder="0" data-parsley-inputs data-parsley-errors-container="#error-bm_mfn">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row mb-2">
                            <div class="col-md-4">
                                <label for="ppn_yes">PPn </label>
                            </div>
                            <div class="col-md-8">
                                <div class="d-lg-flex justify-content-between align-items-center">
                                    <div class="row">
                                        <div class="col-2 mt-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" id="ppn_yes" name="ppn" value="Y" <?= isset($hs) && ($hs->ppn == "Y") ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="ppn_yes">Yes</label>
                                            </div>
                                        </div>
                                        <div class="col-2 mt-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" id="ppn_no" name="ppn" value="N" <?= isset($hs) && ($hs->ppn == "N") ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="ppn_no">No</label>
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="input-group">
                                                <span class="input-group-text">Current</span>
                                                <input type="number" step="0.01" id="current" class="form-control text-end" readonly value="<?= isset($def_ppn) && $def_ppn ? $def_ppn : 0; ?>">
                                                <span class="input-group-text">%</span>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <div class="form-group row mb-2">
                            <div class="col-md-4">
                                <label for="ppn_bm">PPn BM</label>
                            </div>
                            <div class="col-md-8">
                                <div class="input-group">
                                    <input type="number" step="0.01" class="form-control text-end" id="ppn_bm" value="<?= isset($hs) ? $hs->ppn_bm : 0; ?>" name="ppn_bm" data-parsley-inputs data-parsley-errors-container="#error-ppn_bm" placeholder="0">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row mb-2">
                            <div class="col-md-4">
                                <label for="pph_api">PPH API </label>
                            </div>
                            <div class="col-md-8">
                                <div class="input-group">
                                    <input type="number" step="0.01" class="form-control text-end" id="pph_api" value="<?= isset($hs) ? $hs->pph_api : (isset($def_pph_api) && $def_pph_api ? $def_pph_api : 0); ?>" name="pph_api" required data-parsley-inputs data-parsley-errors-container="#error-pph_api" placeholder="0">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row mb-2">
                            <div class="col-md-4">
                                <label for="pph_napi">PPH (NON-API)</label>
                            </div>
                            <div class="col-md-8">
                                <div class="input-group">
                                    <input type="number" step="0.01" class="form-control text-end" id="pph_napi" value="<?= isset($hs) ? $hs->pph_napi : (isset($def_pph_napi) && $def_pph_napi ? $def_pph_napi : 0); ?>" name="pph_napi" placeholder="0">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row mb-2">
                            <div class="col-md-4">
                                <label for="cukai">Cukai</label>
                            </div>
                            <div class="col-md-8">
                                <div class="input-group">
                                    <input type="number" step="0.01" class="form-control text-end" id="cukai" value="<?= isset($hs) ? $hs->cukai : ''; ?>" name="cukai" placeholder="0">

                                    <select class="custom-select"
                                        name="unit_cukai" aria-label="Unit cukai">
                                        <!-- <option value=""></option> -->
                                        <option value="kg" <?= isset($hs->unit_cukai) && $hs->unit_cukai == 'kg' ? 'selected' : ''; ?>>Kg</option>
                                        <option value="m" <?= isset($hs->unit_cukai) && $hs->unit_cukai == 'm'  ? 'selected' : ''; ?>>Meter</option>
                                        <option value="rp" <?= isset($hs->unit_cukai) && $hs->unit_cukai == 'rp' ? 'selected' : ''; ?>>Rp</option>
                                        <option value="percent" <?= isset($hs->unit_cukai) && $hs->unit_cukai == 'percent' ? 'selected' : ''; ?>>%</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row mb-2">
                            <div class="col-md-4">
                                <label for="cukai">UOM</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="uom" value="<?= (isset($hs->uom) && $hs->uom) ? $hs->uom : ''; ?>" id="uom" placeholder="Ex: TNE | KGS ...">
                            </div>
                        </div>
                    </div>
                    <!-- Sisi Kanan -->
                    <div class="col-md-6">
                        <div class="form-group row mb-2">
                            <div class="col-md-4">
                                <label for="bmad">BMAD</label>
                            </div>
                            <div class="col-md-8">
                                <div class="input-group">
                                    <input type="number" step="0.01" name="bmad" id="bmad" class="form-control text-end" placeholder="0" value="<?= isset($hs) ? $hs->bmad : '0'; ?>">
                                    <select class="custom-select" name="unit_bmad">
                                        <!-- <option value=""></option> -->
                                        <option value="kg" <?= isset($hs->unit_bmad) && $hs->unit_bmad == 'kg' ? 'selected' : ''; ?>>Kg</option>
                                        <option value="m" <?= isset($hs->unit_bmad) && $hs->unit_bmad == 'm' ? 'selected' : ''; ?>>Meter</option>
                                        <option value="rp" <?= isset($hs->unit_bmad) && $hs->unit_bmad == 'rp' ? 'selected' : ''; ?>>Rp</option>
                                        <option value="percent" <?= isset($hs->unit_bmad) && $hs->unit_bmad == 'percent' ? 'selected' : ''; ?>>%</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row mb-2">
                            <div class="col-md-4">
                                <label for="bmtp">BMTP</label>
                            </div>
                            <div class="col-md-8">
                                <div class="input-group">
                                    <input type="number" step="0.01" class="form-control text-end" id="bmtp" value="<?= isset($hs) ? $hs->bmtp : ''; ?>" name="bmtp" placeholder="0">

                                    <select class="custom-select" name="unit_bmtp">
                                        <!-- <option value=""></option> -->
                                        <option value="kg" <?= isset($hs->bmtp) && $hs->unit_bmtp == 'kg' ? 'selected' : ''; ?>>Kg</option>
                                        <option value="m" <?= isset($hs->bmtp) && $hs->unit_bmtp == 'm' ? 'selected' : ''; ?>>Meter</option>
                                        <option value="rp" <?= isset($hs->bmtp) && $hs->unit_bmtp == 'rp' ? 'selected' : ''; ?>>Rp</option>
                                        <option value="percent" <?= isset($hs->bmtp) && $hs->unit_bmtp == 'percent' ? 'selected' : ''; ?>>%</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row mb-2">
                            <div class="col-md-4">
                                <label for="bm_im">BM IM</label>
                            </div>
                            <div class="col-md-8">
                                <div class="input-group">
                                    <input type="number" step="0.01" class="form-control text-end" id="bm_im" value="<?= isset($hs) ? $hs->bm_im : ''; ?>" name="bm_im" placeholder="0">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row mb-2">
                            <div class="col-md-4">
                                <label for="bk">BK</label>
                            </div>
                            <div class="col-md-8">
                                <div class="input-group">
                                    <input type="number" step="0.01" class="form-control text-end" id="bk" value="<?= isset($hs) ? $hs->bk : ''; ?>" name="bk" placeholder="0">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row mb-2">
                            <div class="col-md-4">
                                <label for="dana_sawit">Tarif Dana Sawit</label>
                            </div>
                            <div class="col-md-8">
                                <div class="input-group">
                                    <input type="number" step="0.01" class="form-control text-end" id="dana_sawit" value="<?= isset($hs) ? $hs->dana_sawit : ''; ?>" name="dana_sawit" placeholder="0">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row mb-2">
                            <div class="col-md-4">
                                <label for="dhe_sda">Wajib Lapor DHE-SDA</label>
                            </div>
                            <div class="col-md-8">
                                <div class="input-group">
                                    <input type="number" step="0.01" class="form-control text-end" id="dhe_sda" value="<?= isset($hs) ? $hs->dhe_sda : ''; ?>" name="dhe_sda" placeholder="0">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row mb-2">
                            <div class="col-md-4">
                                <label for="statusActive">Status</label>
                            </div>
                            <div class="col-md-8">
                                <div class="row mt-2">
                                    <div class="col-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" id="aktif" name="status" value="1" <?= isset($hs) && ($hs->status == 1) ? 'checked' : ''; ?> required>
                                            <label class="form-check-label" for="status">Aktif</label>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" id="inaktif" name="status" value="0" <?= isset($hs) && ($hs->status == 0) ? 'checked' : ''; ?> required>
                                            <label class="form-check-label" for="status">Non Aktif</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h5 class="mb-0 fw-bold">BM With SKA</h6>
                            <button type="button" id="add-item-origin" class="btn btn-success btn-sm">
                                <i class="fa fa-plus"></i> Add Item
                            </button>
                    </div>

                    <div class="col-md-12">
                        <table id="tblBM" class="table table-sm w-100 mb-1">
                            <thead>
                                <tr class="text-center">
                                    <th width="30">No</th>
                                    <th>Origin</th>
                                    <th width="50">Opsi</th>
                                </tr>
                            </thead>

                            <tbody class="origin">
                                <?php
                                $originIndex = 1;
                                if (!empty($origins)) {
                                    foreach ($origins as $originData) {
                                ?>
                                        <tr class="origin-row">
                                            <input type="hidden" name="origin_bm[<?= $originIndex; ?>][id]" value="<?= $originData['id'] ?: ''; ?>">

                                            <td class="text-center origin-no"><?= $originIndex; ?></td>

                                            <td>
                                                <div class="parsley-select">
                                                    <!-- BS5: select pakai form-select -->
                                                    <select name="origin_bm[<?= $originIndex; ?>][country_id]"
                                                        class="form-select form-select-sm select country-select"
                                                        required>
                                                        <option value=""></option>
                                                        <?php foreach ($countries as $country) { ?>
                                                            <option value="<?= $country->id; ?>" <?= ($country->id == $originData['origin_id']) ? 'selected' : ''; ?>>
                                                                <?= $country->country_code . ' - ' . $country->name ?>
                                                            </option>
                                                        <?php } ?>
                                                    </select>
                                                </div>

                                                <div class="sub-table-container mt-2">
                                                    <table class="table table-sm mb-0 bm-details" data-origin="<?= $originIndex; ?>">
                                                        <tbody class="origin-bm">
                                                            <?php
                                                            $detailIndex = 1;
                                                            foreach ($originData['details'] as $bmData) {
                                                            ?>
                                                                <tr class="origin-bm-row">
                                                                    <input type="hidden" name="origin_bm[<?= $originIndex; ?>][details][<?= $detailIndex; ?>][id]" value="<?= $bmData['id'] ?: ''; ?>">

                                                                    <td class="text-center bm-no" width="30"><?= $detailIndex; ?></td>

                                                                    <td width="220">
                                                                        <input type="text"
                                                                            name="origin_bm[<?= $originIndex; ?>][details][<?= $detailIndex; ?>][bm_name]"
                                                                            class="form-control form-control-sm"
                                                                            value="<?= $bmData['bm_name']; ?>"
                                                                            placeholder="Name">
                                                                    </td>

                                                                    <td width="180">
                                                                        <!-- BS5 input group -->
                                                                        <div class="input-group input-group-sm">
                                                                            <input type="number" step="0.01"
                                                                                name="origin_bm[<?= $originIndex; ?>][details][<?= $detailIndex; ?>][bm_value]"
                                                                                class="form-control text-end"
                                                                                value="<?= $bmData['bm_value']; ?>"
                                                                                placeholder="0">
                                                                            <span class="input-group-text">%</span>
                                                                        </div>
                                                                    </td>

                                                                    <td>
                                                                        <input type="text"
                                                                            name="origin_bm[<?= $originIndex; ?>][details][<?= $detailIndex; ?>][bm_document]"
                                                                            class="form-control form-control-sm"
                                                                            value="<?= $bmData['bm_document']; ?>"
                                                                            placeholder="Document">
                                                                    </td>

                                                                    <td class="text-center" width="50">
                                                                        <button type="button" class="btn btn-sm btn-danger del-bm" data-id="<?= $bmData['id']; ?>">
                                                                            <i class="fa fa-trash-alt"></i>
                                                                        </button>
                                                                    </td>
                                                                </tr>
                                                            <?php
                                                                $detailIndex++;
                                                            }
                                                            ?>
                                                        </tbody>
                                                    </table>

                                                    <button type="button" class="btn btn-sm btn-success add-bm mt-2" data-origin="<?= $originIndex; ?>">
                                                        <i class="fas fa-plus"></i> Add BM
                                                    </button>
                                                </div>
                                            </td>

                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-danger del-origin" data-id="<?= $originData['id']; ?>">
                                                    <i class="fa fa-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php
                                        $originIndex++;
                                    }
                                } else {
                                    ?>
                                    <!-- kalau kosong, tampilkan 1 row awal -->
                                    <tr class="origin-row">
                                        <input type="hidden" name="origin_bm[1][id]" value="">
                                        <td class="text-center origin-no">1</td>
                                        <td>
                                            <select name="origin_bm[1][country_id]" class="form-select form-select-sm select country-select" required>
                                                <option value=""></option>
                                                <?php foreach ($countries as $country) { ?>
                                                    <option value="<?= $country->id; ?>"><?= $country->country_code . ' - ' . $country->name; ?></option>
                                                <?php } ?>
                                            </select>

                                            <div class="sub-table-container mt-2">
                                                <table class="table table-sm mb-0 bm-details" data-origin="1">
                                                    <tbody class="origin-bm">
                                                        <tr class="origin-bm-row">
                                                            <input type="hidden" name="origin_bm[1][details][1][id]" value="">
                                                            <td class="text-center bm-no" width="30">1</td>
                                                            <td width="220"><input type="text" name="origin_bm[1][details][1][bm_name]" class="form-control form-control-sm" placeholder="Name"></td>
                                                            <td width="180">
                                                                <div class="input-group input-group-sm">
                                                                    <input type="number" step="0.01" name="origin_bm[1][details][1][bm_value]" class="form-control text-end" placeholder="0">
                                                                    <span class="input-group-text">%</span>
                                                                </div>
                                                            </td>
                                                            <td><input type="text" name="origin_bm[1][details][1][bm_document]" class="form-control form-control-sm" placeholder="Document"></td>
                                                            <td class="text-center" width="50">
                                                                <button type="button" class="btn btn-sm btn-danger del-bm"><i class="fa fa-trash-alt"></i></button>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>

                                                <button type="button" class="btn btn-sm btn-success add-bm mt-2" data-origin="1">
                                                    <i class="fas fa-plus"></i> Add BM
                                                </button>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-danger del-origin"><i class="fa fa-trash-alt"></i></button>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <h5 class="mb-2 fw-bold">Document Requirements</h5>

                    <ul class="nav nav-tabs mb-3" id="customerTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tab-local" data-bs-toggle="tab" data-bs-target="#req1" type="button" role="tab">
                                Regulasi Impor Tataniaga Border (Lartas)
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-category" data-bs-toggle="tab" data-bs-target="#req2" type="button" role="tab">
                                Regulasi Impor Tataniaga Post Boder
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content br-profile-body">
                        <div class="tab-pane fade active show" id="req1">
                            <table id="table-req1" class="table table-sm rounded-10 overflow-hidden border">
                                <thead>
                                    <tr>
                                        <th class="text-center" width="50">No</th>
                                        <th>Requirement Name</th>
                                        <th>Description</th>
                                        <th class="text-center" width="100">Opsi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $n = 0;
                                    if (isset($ArrRQ['RQ1']) && $ArrRQ['RQ1']) {
                                        foreach ($ArrRQ['RQ1'] as $r1) {
                                            ++$n; ?>
                                            <tr>
                                                <td class="text-center">
                                                    <?= $n; ?>
                                                    <input type="hidden" class="id_rq" value="<?= $r1['id']; ?>">
                                                </td>
                                                <td><?= $r1['name']; ?></td>
                                                <td><?= $r1['description']; ?></td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-success editRQ" data-type="<?= $r1['type']; ?>" data-id="<?= $r1['id']; ?>"><i class="fas fa-edit"></i></button>
                                                    <button type="button" class="btn btn-sm btn-danger deleteRQ" data-type="<?= $r1['type']; ?>" data-id="<?= $r1['id']; ?>"><i class="fas fa-trash"></i></button>
                                                </td>
                                            </tr>
                                    <?php }
                                    } ?>
                                </tbody>
                            </table>
                            <button type="button" id="add-req1" class="btn btn-success wd-100 btn-sm"><i class="fa fa-plus" aria-hidden="true"></i> Add</button>
                        </div>

                        <div class="tab-pane fade" id="req2">
                            <table id="table-req2" class="table table-sm rounded-10 overflow-hidden border">
                                <thead>
                                    <tr>
                                        <th class="text-center" width="50">No</th>
                                        <th>Requirement Name</th>
                                        <th>Description</th>
                                        <th class="text-center" width="100">Opsi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $n = 0;
                                    if (isset($ArrRQ['RQ2']) && $ArrRQ['RQ2']) {
                                        foreach ($ArrRQ['RQ2'] as $r1) {
                                            ++$n; ?>
                                            <tr>
                                                <td class="text-center"><?= $n; ?></td>
                                                <td><?= $r1['name']; ?></td>
                                                <td><?= $r1['description']; ?></td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-success editRQ" data-type="<?= $r1['type']; ?>" data-id="<?= $r1['id']; ?>"><i class="fas fa-edit"></i></button>
                                                    <button type="button" class="btn btn-sm btn-danger deleteRQ" data-type="<?= $r1['type']; ?>" data-id="<?= $r1['id']; ?>"><i class="fas fa-trash"></i></button>
                                                </td>
                                            </tr>
                                    <?php }
                                    } ?>
                                </tbody>
                            </table>
                            <button type="button" id="add-req2" class="btn btn-success wd-100 btn-sm"><i class="fa fa-plus" aria-hidden="true"></i> Add</button>
                        </div>
                    </div>
                </div>
            </div>



            <div class="col-12 pt-2">
                <hr class="mt-2 mb-3">
                <div class="d-flex justify-content-center gap-2">
                    <?php if (empty($results['mode']) || $results['mode'] !== 'view') : ?>
                        <button type="button" class="btn btn-secondary btn-md" onclick="history.back()">
                            <i class="fa fa-arrow-left me-1"></i> Kembali
                        </button>
                        <button type="submit" class="btn btn-success btn-md" name="save" id="simpan-com">
                            <i class="fa fa-save me-1"></i> Simpan
                        </button>
                    <?php else: ?>
                        <button type="button" class="btn btn-secondary btn-md" onclick="history.back()">
                            <i class="fa fa-arrow-left me-1"></i> Kembali
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>

<script type="application/json" id="countries-data">
    <?= json_encode(array_map(function ($c) {
        return ['id' => $c->id, 'country_code' => $c->country_code, 'name' => $c->name];
    }, $countries)); ?>
</script>

<script>
    $(function() {
        const countries = JSON.parse($('#countries-data').text() || '[]');

        function getDropdownParent() {
            // kalau di modal, biar dropdown gak kepotong
            const $m = $('.modal.show .modal-body');
            return $m.length ? $m : $('body');
        }

        function initSelect2($el) {
            if (!$el || !$el.length) return;

            $el.each(function() {
                const $s = $(this);
                if ($s.hasClass('select2-hidden-accessible')) return; // jangan init 2x

                $s.select2({
                    placeholder: 'Choose one',
                    dropdownParent: getDropdownParent(),
                    width: '100%',
                    allowClear: true
                    // theme: 'bootstrap-5' // aktifkan kalau kamu pakai theme select2 bootstrap5
                });
            });
        }

        function countryOptionsHTML() {
            return countries.map(c =>
                `<option value="${c.id}">${c.country_code} - ${c.name}</option>`
            ).join('');
        }

        // Template BM row (BS5: input-group-text langsung, tanpa input-group-append)
        function bmRowTemplate(originIndex, detailIndex) {
            return `
        <tr class="origin-bm-row">
            <input type="hidden" name="origin_bm[${originIndex}][details][${detailIndex}][id]" value="">
            <td class="text-center bm-no" width="30">${detailIndex}</td>
            <td width="220">
            <input type="text"
                    name="origin_bm[${originIndex}][details][${detailIndex}][bm_name]"
                    class="form-control form-control-sm"
                    placeholder="Name">
            </td>
            <td width="180">
            <div class="input-group input-group-sm">
                <input type="number" step="0.01"
                    name="origin_bm[${originIndex}][details][${detailIndex}][bm_value]"
                    class="form-control text-end"
                    placeholder="0">
                <span class="input-group-text">%</span>
            </div>
            </td>
            <td>
            <input type="text"
                    name="origin_bm[${originIndex}][details][${detailIndex}][bm_document]"
                    class="form-control form-control-sm"
                    placeholder="Document">
            </td>
            <td class="text-center" width="50">
            <button type="button" class="btn btn-sm btn-danger del-bm" data-id="">
                <i class="fa fa-trash-alt"></i>
            </button>
            </td>
        </tr>
        `;
        }

        // Template Origin row (mengikuti HTML BS5 yang aku kasih)
        function originRowTemplate(originIndex) {
            return `
        <tr class="origin-row">
            <input type="hidden" name="origin_bm[${originIndex}][id]" value="">
            <td class="text-center origin-no">${originIndex}</td>
            <td>
            <select name="origin_bm[${originIndex}][country_id]"
                    class="form-select form-select-sm select country-select"
                    required>
                <option value=""></option>
                ${countryOptionsHTML()}
            </select>

            <div class="sub-table-container mt-2">
                <table class="table table-sm mb-0 bm-details" data-origin="${originIndex}">
                <tbody class="origin-bm">
                    ${bmRowTemplate(originIndex, 1)}
                </tbody>
                </table>

                <button type="button" class="btn btn-sm btn-success add-bm mt-2" data-origin="${originIndex}">
                <i class="fas fa-plus"></i> Add BM
                </button>
            </div>
            </td>
            <td class="text-center">
            <button type="button" class="btn btn-sm btn-danger del-origin" data-id="">
                <i class="fa fa-trash-alt"></i>
            </button>
            </td>
        </tr>
        `;
        }

        // Reindex No + update name="origin_bm[..]" biar tidak loncat setelah delete
        function reindexAll() {
            $('#tblBM tbody.origin tr.origin-row').each(function(i) {
                const o = i + 1;
                const $originRow = $(this);

                // nomor tampilan
                $originRow.find('.origin-no').text(o);

                // update name origin_bm[old] -> origin_bm[o]
                $originRow.find('[name^="origin_bm["]').each(function() {
                    this.name = this.name.replace(/origin_bm\[\d+\]/, `origin_bm[${o}]`);
                });

                // update data-origin
                $originRow.find('.bm-details').attr('data-origin', o);
                $originRow.find('.add-bm').attr('data-origin', o);

                // reindex detail
                $originRow.find('tr.origin-bm-row').each(function(j) {
                    const d = j + 1;
                    const $bmRow = $(this);

                    $bmRow.find('.bm-no').text(d);

                    $bmRow.find('[name^="origin_bm["]').each(function() {
                        this.name = this.name.replace(
                            /origin_bm\[\d+\]\[details\]\[\d+\]/,
                            `origin_bm[${o}][details][${d}]`
                        );
                    });
                });
            });
        }

        function togglePpnCurrent() {
            const val = $('input[name="ppn"]:checked').val(); // Y / N
            const isNo = (val === 'N');

            $('#current').prop('readonly', isNo);

            // Opsional: kalau No, set nilai jadi 0
            if (isNo) {
                $('#current').val(0);
            }
        }

        $(document).on('change', 'input[name="ppn"]', togglePpnCurrent);

        // ===== Add Origin =====
        $(document).on('click', '#add-item-origin', function() {
            const nextIndex = $('#tblBM tbody.origin tr.origin-row').length + 1;
            $('#tblBM tbody.origin').append(originRowTemplate(nextIndex));

            // init select2 hanya untuk select baru
            initSelect2($('#tblBM tbody.origin tr.origin-row:last .country-select'));

            reindexAll();
        });

        // ===== Delete Origin =====
        $(document).on('click', '.del-origin', function() {
            const $btn = $(this);
            const id = $btn.data('id') || '';

            // kalau data belum tersimpan DB (id kosong) -> remove langsung
            if (!id) {
                // destroy select2 sebelum remove
                const $sel = $btn.closest('tr').find('.country-select');
                if ($sel.hasClass('select2-hidden-accessible')) $sel.select2('destroy');

                $btn.closest('tr').remove();
                reindexAll();
                return;
            }

            Swal.fire({
                title: "Confirm",
                text: "Are you sure to delete this Origin and related BM Origin data?",
                icon: "question",
                showCancelButton: true,
                confirmButtonText: "Yes",
                cancelButtonText: "No",
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return $.ajax({
                        url: siteurl + thisController + 'deleteOriginBm',
                        type: "POST",
                        dataType: 'JSON',
                        data: {
                            id,
                            type: 'origin'
                        }
                    }).catch(() => {
                        Swal.showValidationMessage("Server error / timeout");
                    });
                }
            }).then((val) => {
                if (!val.isConfirmed) return;

                if (val.value && val.value.status === '1') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted',
                        text: val.value.msg || 'Success'
                    });

                    // destroy select2 sebelum remove
                    const $sel = $btn.closest('tr').find('.country-select');
                    if ($sel.hasClass('select2-hidden-accessible')) $sel.select2('destroy');

                    $btn.closest('tr').remove();
                    reindexAll();
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Failed',
                        text: val.value?.msg || 'Failed delete'
                    });
                }
            });
        });

        // ===== Add BM =====
        $(document).on('click', '.add-bm', function() {
            const originId = $(this).data('origin');
            const $originRow = $(this).closest('tr.origin-row');
            const $tbody = $originRow.find('tbody.origin-bm');
            const nextDetail = $tbody.find('tr.origin-bm-row').length + 1;

            $tbody.append(bmRowTemplate(originId, nextDetail));
            reindexAll();
        });

        // ===== Delete BM =====
        $(document).on('click', '.del-bm', function() {
            const $btn = $(this);
            const bmId = $btn.data('id') || '';

            if (!bmId) {
                $btn.closest('tr').remove();
                reindexAll();
                return;
            }

            Swal.fire({
                title: "Confirm",
                text: "Are you sure to delete this BM Origin data?",
                icon: "question",
                showCancelButton: true,
                confirmButtonText: "Yes",
                cancelButtonText: "No",
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return $.ajax({
                        url: siteurl + thisController + 'deleteOriginBm',
                        type: "POST",
                        dataType: 'JSON',
                        data: {
                            id: bmId,
                            type: 'bm'
                        }
                    }).catch(() => {
                        Swal.showValidationMessage("Server error / timeout");
                    });
                }
            }).then((val) => {
                if (!val.isConfirmed) return;

                if (val.value && val.value.status === '1') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted',
                        text: val.value.msg || 'Success'
                    });
                    $btn.closest('tr').remove();
                    reindexAll();
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Failed',
                        text: val.value?.msg || 'Failed delete'
                    });
                }
            });
        });

        // init awal untuk select yang sudah ada di halaman
        initSelect2($('.country-select'));

        /* Add Doc Requirement */
        $(document).on('click', '#add-req1', function() {
            let n = 0
            n = $('table#table-req1 tbody tr').length + 1
            var html = '';
            html += `<tr style="background-color:#fff5de">
						<td class="text-center">
                            <i class="fa fa-plus tx-10" aria-hidden="true"></i>
                            <input type="hidden"class="form-control" readonly name="requirement[RQ1_` + n + `][type]" value="RQ1">
                        </td>
						<td class="align-top"><input type="text" class="form-control" name="requirement[RQ1_` + n + `][name]" placeholder="Requirement Name"></td> 
						<td class="align-top"><textarea type="text" class="form-control" name="requirement[RQ1_` + n + `][description]" placeholder="Description"></textarea></td> 
						<td class="text-center"><button type="button" class="btn btn-sm btn-warning del-item" title="Hapus Data"><i class="fa fa-times"></i></button></td>
					</tr>`;
            $('table#table-req1 tbody').append(html);
        })


        $(document).on('click', '#add-req2', function() {
            let n = 0
            n = $('table#table-req2 tbody tr').length + 1
            var html = '';
            html += `<tr style="background-color:#fff5de">
						<td class="text-center">
                            <i class="fa fa-plus tx-10" aria-hidden="true"></i>
                            <input type="hidden"class="form-control" readonly name="requirement[RQ2_` + n + `][type]" value="RQ2">
                        </td>
						<td class="align-top"><input type="text" class="form-control" name="requirement[RQ2_` + n + `][name]" placeholder="Requirement Name"></td> 
						<td class="align-top"><textarea type="text" class="form-control" name="requirement[RQ2_` + n + `][description]" placeholder="Description"></textarea></td> 
						<td class="text-center"><button type="button" class="btn btn-sm btn-warning del-item" title="Hapus Data"><i class="fa fa-times"></i></button></td>
					</tr>`;
            $('table#table-req2 tbody').append(html);
        })

        $(document).on('click', '.del-item', function() {
            $(this).parents('tr').fadeOut().css('background-color', '#000')
            setTimeout(() => {
                $(this).parents('tr').remove()
            }, 500);
        })

        $('#data-form').submit(function(e) {
            e.preventDefault();

            // (OPSIONAL) kalau ada validasi custom seperti checkbox group, taruh di sini
            // contoh:
            // const checkboxes = document.querySelectorAll(".hari-checkbox");
            // const oneChecked = Array.from(checkboxes).some(cb => cb.checked);
            // if (!oneChecked) { alert("Pilih minimal satu hari terima!"); return false; }

            swal({
                    title: "Are you sure?",
                    text: "You will not be able to process again this data!",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonClass: "btn-danger",
                    confirmButtonText: "Yes, Save it!",
                    cancelButtonText: "No, cancel!",
                    closeOnConfirm: true,
                    closeOnCancel: false
                },
                function(isConfirm) {
                    if (isConfirm) {

                        var formData = new FormData($('#data-form')[0]);
                        var baseurl = base_url + active_controller + 'save';

                        $.ajax({
                            url: baseurl,
                            type: "POST",
                            data: formData,
                            cache: false,
                            dataType: 'json',
                            processData: false,
                            contentType: false,
                            success: function(data) {
                                if (data.status == 1 || data.status == '1') {
                                    swal({
                                        title: "Save Success!",
                                        text: data.msg || data.pesan || "Data saved successfully.",
                                        type: "success",
                                        timer: 3000,
                                        showCancelButton: false,
                                        showConfirmButton: false,
                                        allowOutsideClick: false
                                    });
                                    window.location.href = base_url + active_controller;
                                } else {
                                    swal({
                                        title: "Save Failed!",
                                        text: data.msg || data.pesan || "Failed to save data.",
                                        type: "warning",
                                        timer: 4000,
                                        showCancelButton: false,
                                        showConfirmButton: false,
                                        allowOutsideClick: false
                                    });
                                }
                            },
                            error: function() {
                                swal({
                                    title: "Error Message!",
                                    text: "An Error Occured During Process. Please try again..",
                                    type: "warning",
                                    timer: 4000,
                                    showCancelButton: false,
                                    showConfirmButton: false,
                                    allowOutsideClick: false
                                });
                            }
                        });

                    } else {
                        swal("Cancelled", "Data can be processed again :)", "error");
                    }
                }
            );
        });

    });
</script>