<?php
$cus = null;

if (!empty($results['cus']) && is_array($results['cus'])) {
    foreach ($results['cus'] as $item) {
        $cus = $item;
        break;
    }
}
if (!empty($results['rate']) && is_array($results['rate'])) {
    foreach ($results['rate'] as $rate) {
    } // ambil terakhir (sesuai kode kamu)
}

$readonly = isset($results['mode']) && $results['mode'] === 'view' ? 'readonly' : '';
$disabled = isset($results['mode']) && $results['mode'] === 'view' ? 'disabled' : '';
?>

<!-- ✅ Card Berry -->
<div class="card">
    <div class="card-body">
        <form id="data-form" method="post">
            <div class="row g-4">

                <!-- =========================
                    SECTION: IDENTITAS CUSTOMER
                ========================== -->
                <div class="col-12">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="mb-0 fw-bold">DETAIL IDENTITAS CUSTOMER</h6>
                        <span class="text-muted small">(*) wajib diisi</span>
                    </div>
                    <hr class="mt-2">
                </div>

                <!-- LEFT -->
                <div class="col-lg-6">
                    <!-- Id Customer -->
                    <div class="row mb-3 align-items-center">
                        <div class="col-md-5">
                            <label class="form-label mb-0">Id Customer</label>
                        </div>
                        <div class="col-md-7">
                            <input type="text" class="form-control"
                                id="id_customer"
                                value="<?= isset($cus->id_customer) ? $cus->id_customer : '' ?>"
                                required name="id_customer" readonly placeholder="Id Customer">
                        </div>
                    </div>

                    <!-- Category Customer (Hidden) -->
                    <div class="row mb-3 align-items-center" hidden>
                        <div class="col-md-5">
                            <label class="form-label mb-0" for="id_category_customer">Category Customer</label>
                        </div>
                        <div class="col-md-7">
                            <select id="id_category_customer" name="id_category_customer" class="form-select select">
                                <option value="">--Pilih--</option>
                                <?php foreach ($results['category'] as $category) { ?>
                                    <option value="<?= $category->id_category_customer ?>"
                                        <?= (isset($cus) && $cus->id_category_customer == $category->id_category_customer) ? 'selected' : '' ?>>
                                        <?= $category->name_category_customer ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <!-- Nama Customer -->
                    <div class="row mb-3 align-items-center">
                        <div class="col-md-5">
                            <label class="form-label mb-0">Nama Customer <span class="text-danger">*</span></label>
                        </div>
                        <div class="col-md-7">
                            <input type="text" class="form-control" id="name_customer"
                                value="<?= isset($cus->name_customer) ? $cus->name_customer : '' ?>"
                                required name="name_customer" placeholder="Nama Customer" <?= $readonly ?>>
                        </div>
                    </div>

                    <!-- Telephone -->
                    <div class="row mb-2 align-items-center">
                        <div class="col-md-5">
                            <label class="form-label mb-0">Telephone <span class="text-danger">*</span></label>
                        </div>
                        <div class="col-md-7">
                            <input type="text" class="form-control" id="telephone"
                                value="<?= isset($cus->telephone) ? $cus->telephone : '' ?>"
                                required name="telephone" placeholder="Nomor Telephone" <?= $readonly ?>>
                        </div>
                    </div>

                    <!-- Telephone 2 -->
                    <div class="row mb-3 align-items-center">
                        <div class="col-md-5"></div>
                        <div class="col-md-7">
                            <input type="text" class="form-control" id="telephone_2"
                                value="<?= isset($cus->telephone_2) ? $cus->telephone_2 : '' ?>"
                                name="telephone_2" placeholder="Nomor Telephone (optional)" <?= $readonly ?>>
                        </div>
                    </div>

                    <!-- Fax -->
                    <div class="row mb-3 align-items-center">
                        <div class="col-md-5">
                            <label class="form-label mb-0">Fax</label>
                        </div>
                        <div class="col-md-7">
                            <input type="text" class="form-control" id="fax" name="fax"
                                value="<?= isset($cus->fax) ? $cus->fax : '' ?>"
                                placeholder="Nomor Fax" <?= $readonly ?>>
                        </div>
                    </div>

                    <!-- Email (NOTE: di kode kamu value-nya salah pakai name_customer) -->
                    <div class="row mb-3 align-items-center">
                        <div class="col-md-5">
                            <label class="form-label mb-0">Email <span class="text-danger">*</span></label>
                        </div>
                        <div class="col-md-7">
                            <input type="text" class="form-control" id="email"
                                required name="email"
                                value="<?= isset($cus->email) ? $cus->email : '' ?>"
                                placeholder="email@domain.address" <?= $readonly ?>>
                        </div>
                    </div>

                    <!-- Start Date -->
                    <div class="row mb-3 align-items-center">
                        <div class="col-md-5">
                            <label class="form-label mb-0">Tanggal Mulai <span class="text-danger">*</span></label>
                        </div>
                        <div class="col-md-7">
                            <input type="date" class="form-control" id="start_date"
                                required name="start_date"
                                value="<?= isset($cus->start_date) ? $cus->start_date : '' ?>"
                                <?= $readonly ?>>
                        </div>
                    </div>

                    <!-- Sales -->
                    <div class="row mb-3 align-items-center">
                        <div class="col-md-5">
                            <label class="form-label mb-0">Sales <span class="text-danger">*</span></label>
                        </div>
                        <div class="col-md-7">
                            <select id="id_karyawan" name="id_karyawan" class="form-select select" required <?= $disabled ?>>
                                <option value="">--pilih--</option>
                                <?php foreach ($results['karyawan'] as $karyawan) {
                                    $selected = (isset($cus) && $cus->id_karyawan == $karyawan->id) ? 'selected' : '';
                                ?>
                                    <option value="<?= $karyawan->id ?>" <?= $selected ?>>
                                        <?= ucfirst(strtolower($karyawan->nm_karyawan)) ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <!-- Channel Pemasaran -->
                    <div class="row mb-3">
                        <div class="col-md-5">
                            <label class="form-label mb-0">Channel Pemasaran <span class="text-danger">*</span></label>
                        </div>
                        <div class="col-md-7">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="chanel_toko" name="chanel_toko"
                                    value="Toko dan User"
                                    <?= (isset($cus) && strpos($cus->chanel_pemasaran, 'Toko dan User') !== false) ? 'checked' : '' ?>
                                    <?= $disabled ?>>
                                <label class="form-check-label" for="chanel_toko">Toko dan User</label>
                            </div>

                            <div class="row g-2 align-items-center">
                                <div class="col-md-5">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="chanel_project" name="chanel_project"
                                            value="Project"
                                            <?= (isset($cus) && strpos($cus->chanel_pemasaran, 'Project') !== false) ? 'checked' : '' ?>
                                            onclick="togglePersentaseInput()" <?= $disabled ?>>
                                        <label class="form-check-label" for="chanel_project">Project</label>
                                    </div>
                                </div>
                                <div class="col-md-7">
                                    <div class="input-group">
                                        <input type="text" class="form-control divide" name="persentase" id="persentase"
                                            value="<?= (isset($cus) && strpos($cus->chanel_pemasaran, 'Project') !== false) ? $cus->persentase : '' ?>"
                                            <?= (isset($cus) && strpos($cus->chanel_pemasaran, 'Project') !== false) ? '' : 'disabled' ?>
                                            <?= $disabled ?>>
                                        <span class="input-group-text"><i class="fa fa-percent"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="row mb-3">
                        <div class="col-md-5">
                            <label class="form-label mb-0">Status <span class="text-danger">*</span></label>
                        </div>
                        <div class="col-md-7">
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" id="activation_aktif" name="activation"
                                            value="aktif"
                                            <?= (isset($cus) && $cus->activation == 'aktif') ? 'checked' : '' ?>
                                            required <?= $disabled ?>>
                                        <label class="form-check-label" for="activation_aktif">Aktif</label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" id="activation_inaktif" name="activation"
                                            value="inaktif"
                                            <?= (isset($cus) && $cus->activation == 'inaktif') ? 'checked' : '' ?>
                                            required <?= $disabled ?>>
                                        <label class="form-check-label" for="activation_inaktif">Non Aktif</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT -->
                <div class="col-lg-6">
                    <!-- Provinsi -->
                    <div class="row mb-3 align-items-center">
                        <div class="col-md-5">
                            <label class="form-label mb-0">Provinsi <span class="text-danger">*</span></label>
                        </div>
                        <div class="col-md-7">
                            <select id="id_prov" name="id_prov" class="form-select select" onchange="get_kota()" required <?= $disabled ?>>
                                <option value="">--Pilih--</option>
                                <?php foreach ($results['prov'] as $prov) {
                                    $selected = (isset($cus) && $cus->id_prov == $prov->id_prov) ? 'selected' : '';
                                ?>
                                    <option value="<?= $prov->id_prov ?>" <?= $selected ?>><?= $prov->provinsi ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <!-- Kabupaten/Kota -->
                    <div class="row mb-3 align-items-center">
                        <div class="col-md-5">
                            <label class="form-label mb-0">Kabupaten/Kota <span class="text-danger">*</span></label>
                        </div>
                        <div class="col-md-7">
                            <select id="id_kabkot" name="id_kabkot" class="form-select select" onchange="get_kec()" required <?= $disabled ?>>
                                <option value="">--Pilih--</option>
                                <?php foreach ($results['kabkot'] as $kabkot) {
                                    $selected = (isset($cus) && $cus->id_kabkot == $kabkot->id_kabkot) ? 'selected' : '';
                                ?>
                                    <option value="<?= $kabkot->id_kabkot ?>" <?= $selected ?>><?= $kabkot->kabkot ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <!-- Kecamatan -->
                    <div class="row mb-3 align-items-center">
                        <div class="col-md-5">
                            <label class="form-label mb-0">Kecamatan <span class="text-danger">*</span></label>
                        </div>
                        <div class="col-md-7">
                            <select id="id_kec" name="id_kec" class="form-select select" required <?= $disabled ?>>
                                <option value="">--Pilih--</option>
                                <?php foreach ($results['kec'] as $kec) {
                                    $selected = (isset($cus) && $cus->id_kec == $kec->id_kec) ? 'selected' : '';
                                ?>
                                    <option value="<?= $kec->id_kec ?>" <?= $selected ?>><?= $kec->kecamatan ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <!-- Alamat -->
                    <div class="row mb-3">
                        <div class="col-md-5">
                            <label class="form-label mb-0">Alamat <span class="text-danger">*</span></label>
                        </div>
                        <div class="col-md-7">
                            <textarea name="address_office" id="address_office" class="form-control"
                                placeholder="Alamat" <?= $readonly ?>><?= isset($cus->address_office) ? $cus->address_office : '' ?></textarea>
                        </div>
                    </div>

                    <!-- Kode Pos -->
                    <div class="row mb-3 align-items-center">
                        <div class="col-md-5">
                            <label class="form-label mb-0">Kode Pos</label>
                        </div>
                        <div class="col-md-7">
                            <input type="text" class="form-control" id="zip_code" name="zip_code"
                                value="<?= isset($cus->zip_code) ? $cus->zip_code : '' ?>" placeholder="Kode Pos" <?= $readonly ?>>
                        </div>
                    </div>

                    <!-- Longitude -->
                    <div class="row mb-3 align-items-center">
                        <div class="col-md-5">
                            <label class="form-label mb-0">Longitude <span class="text-danger">*</span></label>
                        </div>
                        <div class="col-md-7">
                            <input type="text" class="form-control" id="longitude" name="longitude"
                                value="<?= isset($cus->longitude) ? $cus->longitude : '' ?>" required placeholder="Longitude" <?= $readonly ?>>
                        </div>
                    </div>

                    <!-- Latitude -->
                    <div class="row mb-3 align-items-center">
                        <div class="col-md-5">
                            <label class="form-label mb-0">Latitude <span class="text-danger">*</span></label>
                        </div>
                        <div class="col-md-7">
                            <input type="text" class="form-control" id="latitude" name="latitude"
                                value="<?= isset($cus->latitude) ? $cus->latitude : '' ?>" required placeholder="Latitude" <?= $readonly ?>>
                        </div>
                    </div>

                    <!-- Mulai Usaha Sejak -->
                    <div class="row mb-3 align-items-center">
                        <div class="col-md-5">
                            <label class="form-label mb-0">Mulai Usaha Sejak</label>
                        </div>
                        <div class="col-md-7">
                            <select name="tahun_mulai" class="form-select select" <?= $disabled ?>>
                                <option value="">-- Pilih Tahun --</option>
                                <?php
                                $currentYear = date("Y");
                                for ($year = $currentYear; $year >= $currentYear - 50; $year--) {
                                    $selected = (isset($cus) && $year == $cus->tahun_mulai) ? 'selected' : '';
                                    echo "<option value='$year' $selected>$year</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <!-- Kategori Customer -->
                    <div class="row mb-3 align-items-center">
                        <div class="col-md-5">
                            <label class="form-label mb-0">Kategori Customer <span class="text-danger">*</span></label>
                        </div>
                        <div class="col-md-7">
                            <select name="kategori_cust" id="kategori_cust" class="form-select select" <?= $disabled ?>>
                                <option value="">-- Pilih --</option>
                                <option value="Distributor" <?= (isset($cus) && $cus->kategori_cust == 'Distributor') ? 'selected' : '' ?>>Distributor</option>
                                <option value="Retail" <?= (isset($cus) && $cus->kategori_cust == 'Retail') ? 'selected' : '' ?>>Retail</option>
                            </select>
                        </div>
                    </div>

                    <!-- Kategori Toko -->
                    <div class="row mb-3 align-items-center">
                        <div class="col-md-5">
                            <label class="form-label mb-0">Kategori Toko</label>
                        </div>
                        <div class="col-md-7">
                            <select name="kategori_toko" id="kategori_toko" class="form-select select" <?= $disabled ?>>
                                <option value="">-- Pilih --</option>
                                <?php
                                $opsiToko = ['Toko 1', 'Toko 2', 'Toko 3', 'Retail'];
                                foreach ($opsiToko as $kategori) {
                                    $selected = (isset($cus) && $cus->kategori_toko == $kategori) ? 'selected' : '';
                                    echo "<option value=\"$kategori\" $selected>$kategori</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- =========================
                    SECTION: PENILAIAN CUSTOMER
                ========================== -->
                <div class="col-12 mt-2">
                    <h6 class="mb-0 fw-bold">PENILAIAN CUSTOMER</h6>
                    <hr class="mt-2 mb-3">
                </div>

                <div class="col-lg-6">
                    <div class="row mb-3 align-items-center">
                        <div class="col-md-6"><label class="form-label mb-0">Bayar 3 Bulan On Time</label></div>
                        <div class="col-md-6">
                            <?php $ontime = isset($rate->ontime) ? $rate->ontime : ''; ?>
                            <div class="d-flex gap-3 flex-wrap">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="data4[ontime]" value="Yes" <?= $ontime === 'Yes' ? 'checked' : '' ?> <?= $disabled ?>>
                                    <label class="form-check-label">Yes</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="data4[ontime]" value="No" <?= $ontime === 'No' ? 'checked' : '' ?> <?= $disabled ?>>
                                    <label class="form-check-label">No</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="data4[ontime]" value="New" <?= $ontime === 'New' ? 'checked' : '' ?> <?= $disabled ?>>
                                    <label class="form-check-label">New</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3 align-items-center">
                        <div class="col-md-6"><label class="form-label mb-0">Toko Milik Sendiri</label></div>
                        <div class="col-md-6">
                            <?php $toko = isset($rate->toko_sendiri) ? $rate->toko_sendiri : ''; ?>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="data4[toko_sendiri]" value="Yes" <?= $toko === 'Yes' ? 'checked' : '' ?> <?= $disabled ?>>
                                    <label class="form-check-label">Yes</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="data4[toko_sendiri]" value="No" <?= $toko === 'No' ? 'checked' : '' ?> <?= $disabled ?>>
                                    <label class="form-check-label">No</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3 align-items-center">
                        <div class="col-md-6"><label class="form-label mb-0">Armada Pickup</label></div>
                        <div class="col-md-6">
                            <input type="number" min="0" class="form-control" name="data4[armada_pickup]"
                                value="<?= isset($rate->armada_pickup) ? $rate->armada_pickup : '' ?>" <?= $readonly ?>>
                        </div>
                    </div>

                    <div class="row mb-3 align-items-center">
                        <div class="col-md-6"><label class="form-label mb-0">Armada Truck</label></div>
                        <div class="col-md-6">
                            <input type="number" min="0" class="form-control" name="data4[armada_truck]"
                                value="<?= isset($rate->armada_truck) ? $rate->armada_truck : '' ?>" <?= $readonly ?>>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="row mb-3 align-items-center">
                        <div class="col-md-6"><label class="form-label mb-0">Attitude</label></div>
                        <div class="col-md-6">
                            <?php $attitude = isset($rate->attitude) ? $rate->attitude : ''; ?>
                            <div class="d-flex gap-3 flex-wrap">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="data4[attitude]" value="Yes" <?= $attitude === 'Yes' ? 'checked' : '' ?> <?= $disabled ?>>
                                    <label class="form-check-label">Yes</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="data4[attitude]" value="No" <?= $attitude === 'No' ? 'checked' : '' ?> <?= $disabled ?>>
                                    <label class="form-check-label">No</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="data4[attitude]" value="New" <?= $attitude === 'New' ? 'checked' : '' ?> <?= $disabled ?>>
                                    <label class="form-check-label">New</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3 align-items-center">
                        <div class="col-md-6"><label class="form-label mb-0">Luas Tanah</label></div>
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="number" min="0" class="form-control" name="data4[luas_tanah]"
                                    value="<?= isset($rate->luas_tanah) ? $rate->luas_tanah : '' ?>" id="luas_tanah" <?= $readonly ?>>
                                <span class="input-group-text"><b>M²</b></span>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3 align-items-center">
                        <div class="col-md-6"><label class="form-label mb-0">PBB</label></div>
                        <div class="col-md-6">
                            <?php $pbb = isset($rate->pbb) ? $rate->pbb : ''; ?>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="data4[pbb]" value="Yes" <?= $pbb === 'Yes' ? 'checked' : '' ?> <?= $disabled ?>>
                                    <label class="form-check-label">Yes</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="data4[pbb]" value="No" <?= $pbb === 'No' ? 'checked' : '' ?> <?= $disabled ?>>
                                    <label class="form-check-label">No</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- =========================
                    SECTION: SUPPLIER EXISTING CUSTOMER
                ========================== -->
                <div class="col-12 mt-2">
                    <h6 class="mb-0 fw-bold">SUPPLIER EXISTING CUSTOMER</h6>
                    <hr class="mt-2 mb-3">
                </div>

                <div class="col-12">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center">Nama PT</th>
                                    <th class="text-center">PIC</th>
                                    <th class="text-center">No Telepon</th>
                                    <th class="text-center" style="width:90px;">
                                        <?php if (empty($results['mode']) || $results['mode'] !== 'view') : ?>
                                            <button type="button" class="btn btn-success btn-sm" id="add-existing" <?= $disabled ?>>
                                                <i class="fa fa-plus me-1"></i> Add
                                            </button>
                                        <?php endif; ?>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="list_existing">
                                <?php
                                $loop = 0;
                                if (!empty($results['exis']) && is_array($results['exis'])) {
                                    foreach ($results['exis'] as $exis) {
                                        $loop++;
                                        $pt   = isset($exis->existing_pt) ? $exis->existing_pt : '';
                                        $pic  = isset($exis->existing_pic) ? $exis->existing_pic : '';
                                        $telp = isset($exis->existing_telp) ? $exis->existing_telp : '';

                                        echo "<tr id='tr_existing_$loop'>";
                                        echo "<td><input type='text' class='form-control form-control-sm' name='existing[$loop][existing_pt]' value='$pt' required $readonly></td>";
                                        echo "<td><input type='text' class='form-control form-control-sm' name='existing[$loop][existing_pic]' value='$pic' required $readonly></td>";
                                        echo "<td><input type='text' class='form-control form-control-sm' name='existing[$loop][existing_telp]' value='$telp' required $readonly></td>";

                                        if (empty($results['mode']) || $results['mode'] !== 'view') {
                                            echo "<td class='text-center'>
                                                    <button type='button' class='btn btn-outline-danger btn-sm' title='Hapus Data' onClick='return DelExisting($loop);'>
                                                        <i class='ti ti-trash'></i>
                                                    </button>
                                                </td>";
                                        } else {
                                            echo "<td></td>";
                                        }

                                        echo "</tr>";
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- =========================
                    SECTION: CATEGORY CUSTOMER
                ========================== -->
                <div class="col-12 mt-2">
                    <h6 class="mb-0 fw-bold">CATEGORY CUSTOMER</h6>
                    <hr class="mt-2 mb-3">
                </div>

                <div class="col-12">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center">Category Customer</th>
                                    <th class="text-center" style="width:90px;">
                                        <?php if (empty($results['mode']) || $results['mode'] !== 'view') : ?>
                                            <button type="button" class="btn btn-success btn-sm" id="add-category">
                                                <i class="fa fa-plus me-1"></i> Add
                                            </button>
                                        <?php endif; ?>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="list_category">
                                <?php
                                $loop = 0;
                                if (!empty($results['cate']) && is_array($results['cate'])) {
                                    foreach ($results['cate'] as $cate) {
                                        $loop++;
                                        $selectedVal = isset($cate->name_category_customer) ? $cate->name_category_customer : '';

                                        echo "<tr id='tr_$loop'>";
                                        echo "<td>";
                                        echo "<select name='data2[$loop][id_category_customer]' class='form-select select' required $disabled>";
                                        echo "<option value='$selectedVal' selected>$selectedVal</option>";

                                        if (!empty($results['category']) && is_array($results['category'])) {
                                            foreach ($results['category'] as $category) {
                                                $optionVal  = $category->name_category_customer;
                                                $isSelected = ($optionVal === $selectedVal) ? "selected" : "";
                                                echo "<option value='$optionVal' $isSelected>$optionVal</option>";
                                            }
                                        }
                                        echo "</select>";
                                        echo "</td>";

                                        if (empty($results['mode']) || $results['mode'] !== 'view') {
                                            echo "<td class='text-center'>
                                                    <button type='button' class='btn btn-outline-danger btn-sm' title='Hapus Data' onClick='return DelItem2($loop);'>
                                                        <i class='ti ti-trash'></i>
                                                    </button>
                                                </td>";
                                        } else {
                                            echo "<td></td>";
                                        }

                                        echo "</tr>";
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- =========================
                    SECTION: PIC
                ========================== -->
                <div class="col-12 mt-2">
                    <h6 class="mb-0 fw-bold">PIC</h6>
                    <hr class="mt-2 mb-3">
                </div>

                <div class="col-12">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center">Nama PIC</th>
                                    <th class="text-center">Nomor Telp</th>
                                    <th class="text-center">Email</th>
                                    <th class="text-center">Jabatan</th>
                                    <th class="text-center" style="width:90px;">
                                        <?php if (empty($results['mode']) || $results['mode'] !== 'view') : ?>
                                            <?= form_button(['type' => 'button', 'class' => 'btn btn-success btn-sm', 'content' => '<i class="fa fa-plus me-1"></i>Add', 'id' => 'add-payment']) ?>
                                        <?php endif; ?>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="list_payment">
                                <?php
                                $loop = 0;
                                $fields = ['name_pic', 'phone_pic', 'email_pic', 'position_pic'];
                                $defaultRows = [['name' => 'PIC'], ['name' => 'Owner'], ['name' => 'KA Toko']];

                                if (!empty($results['pic']) && is_array($results['pic'])) {
                                    foreach ($results['pic'] as $pic) {
                                        $loop++;
                                        echo "<tr id='tr_$loop'>";
                                        foreach ($fields as $field) {
                                            $value = isset($pic->$field) ? $pic->$field : '';
                                            $readonlyAttr = ($field === 'position_pic') ? 'readonly' : '';
                                            echo "<td><input type='text' class='form-control form-control-sm' name='data1[$loop][$field]' value='" . htmlspecialchars($value, ENT_QUOTES) . "' $readonlyAttr $disabled required></td>";
                                        }
                                        if (empty($results['mode']) || $results['mode'] !== 'view') {
                                            echo "<td class='text-center'>
                                                    <button type='button' class='btn btn-outline-danger btn-sm' title='Hapus Data' onclick='return DelItem($loop);'>
                                                        <i class='ti ti-trash'></i>
                                                    </button>
                                                </td>";
                                        } else {
                                            echo "<td></td>";
                                        }
                                        echo "</tr>";
                                    }
                                } else {
                                    foreach ($defaultRows as $index => $row) {
                                        $loop = $index + 1;
                                        echo "<tr id='tr_$loop'>";
                                        foreach ($fields as $field) {
                                            $value = ($field === 'position_pic') ? $row['name'] : '';
                                            $readonlyAttr = ($field === 'position_pic') ? 'readonly' : '';
                                            echo "<td><input type='text' class='form-control form-control-sm' name='data1[$loop][$field]' value='" . htmlspecialchars($value, ENT_QUOTES) . "' $readonlyAttr required></td>";
                                        }
                                        echo "<td class='text-center'>
                                                <button type='button' class='btn btn-outline-danger btn-sm' title='Hapus Data' onclick='return DelItem($loop);'>
                                                    <i class='ti ti-trash'></i>
                                                </button>
                                            </td>";
                                        echo "</tr>";
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- =========================
                    SECTION: INFORMASI PEMBAYARAN
                ========================== -->
                <div class="col-12 mt-2">
                    <h6 class="mb-0 fw-bold">INFORMASI PEMBAYARAN</h6>
                    <hr class="mt-2 mb-3">
                </div>

                <div class="col-lg-6">
                    <div class="mb-2 fw-semibold">Informasi Bank</div>

                    <?php
                    $bankFields = [
                        ['label' => 'Nama Bank', 'name' => 'name_bank'],
                        ['label' => 'Nomor Akun', 'name' => 'no_rekening'],
                        ['label' => 'Nama Akun', 'name' => 'nama_rekening'],
                        ['label' => 'Swift Code', 'name' => 'swift_code'],
                    ];

                    foreach ($bankFields as $field) {
                        $val = isset($cus->{$field['name']}) ? htmlspecialchars($cus->{$field['name']}) : '';
                        echo '
                        <div class="row mb-3 align-items-center">
                            <div class="col-md-5">
                                <label class="form-label mb-0">' . $field['label'] . '</label>
                            </div>
                            <div class="col-md-7">
                                <input type="text" class="form-control" name="' . $field['name'] . '" id="' . $field['name'] . '"
                                       value="' . $val . '" placeholder="' . $field['label'] . '" ' . $disabled . '>
                            </div>
                        </div>';
                    }
                    ?>

                    <div class="row mb-3">
                        <div class="col-md-5">
                            <label class="form-label mb-0">Alamat Bank</label>
                        </div>
                        <div class="col-md-7">
                            <textarea name="alamat_bank" id="alamat_bank" class="form-control" placeholder="Alamat Bank" <?= $disabled ?>><?= isset($cus->alamat_bank) ? htmlspecialchars($cus->alamat_bank) : '' ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="mb-2 fw-semibold">Informasi Pajak</div>

                    <?php
                    $pajakFields = [
                        ['label' => 'Nomor NPWP/KTP', 'name' => 'npwp', 'required' => true],
                        ['label' => 'Nama NPWP/KTP', 'name' => 'npwp_name', 'required' => true],
                        ['label' => 'Alamat NPWP/KTP', 'name' => 'npwp_address', 'required' => true],
                    ];

                    foreach ($pajakFields as $field) {
                        $val = isset($cus->{$field['name']}) ? htmlspecialchars($cus->{$field['name']}) : '';
                        $required = $field['required'] ? 'required' : '';
                        echo '
                        <div class="row mb-3 align-items-center">
                            <div class="col-md-5">
                                <label class="form-label mb-0">' . $field['label'] . ' <span class="text-danger">*</span></label>
                            </div>
                            <div class="col-md-7">
                                <input type="text" class="form-control" name="' . $field['name'] . '" id="' . $field['name'] . '"
                                       value="' . $val . '" placeholder="' . $field['label'] . '" ' . $required . ' ' . $disabled . '>
                            </div>
                        </div>';
                    }
                    ?>

                    <div class="row mb-3 align-items-center">
                        <div class="col-md-5"><label class="form-label mb-0">Term Of Payment <span class="text-danger">*</span></label></div>
                        <div class="col-md-7">
                            <select id="payment_term" name="payment_term" class="form-select select" required <?= $disabled ?>>
                                <option value="">-- Pilih --</option>
                                <?php foreach ($results['payment_terms'] as $terms): ?>
                                    <option value="<?= htmlspecialchars($terms->id) ?>" <?= (isset($cus->payment_term) && $cus->payment_term == $terms->id) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($terms->name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3 align-items-center">
                        <div class="col-md-5"><label class="form-label mb-0">Nominal DP <span class="text-danger">*</span></label></div>
                        <div class="col-md-7">
                            <input type="text" class="form-control" name="nominal_dp" id="nominal_dp"
                                value="<?= isset($cus->nominal_dp) ? htmlspecialchars($cus->nominal_dp) : '' ?>" required <?= $disabled ?>>
                        </div>
                    </div>

                    <div class="row mb-3 align-items-center">
                        <div class="col-md-5"><label class="form-label mb-0">Sisa Pembayaran <span class="text-danger">*</span></label></div>
                        <div class="col-md-7">
                            <select id="sisa_pembayaran" name="sisa_pembayaran" class="form-select select" required <?= $disabled ?>>
                                <?php
                                $options = ['15 After Delifery', '30 After Delifery'];
                                $current = isset($cus->sisa_pembayaran) ? $cus->sisa_pembayaran : '';
                                echo "<option value='$current'>$current</option>";
                                foreach ($options as $opt) {
                                    if ($opt !== $current) echo "<option value='$opt'>$opt</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- =========================
                    SECTION: INFORMASI INVOICE
                ========================== -->
                <div class="col-12 mt-2">
                    <h6 class="mb-0 fw-bold">INFORMASI INVOICE</h6>
                    <hr class="mt-2 mb-3">
                </div>

                <!-- Hari Terima -->
                <div class="col-12">
                    <div class="row mb-3 align-items-center">
                        <div class="col-md-3">
                            <label class="form-label mb-0">Hari Terima <span class="text-danger">*</span></label>
                        </div>
                        <div class="col-md-9">
                            <?php
                            $days = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'];
                            echo '<div class="d-flex gap-3 flex-wrap">';
                            foreach ($days as $day) {
                                $checked = isset($cus->$day) && $cus->$day === 'Y' ? 'checked' : '';
                                echo '
                                <div class="form-check">
                                    <input class="form-check-input hari-checkbox" type="checkbox" id="' . $day . '" name="' . $day . '" value="Y" ' . $checked . ' ' . $disabled . '>
                                    <label class="form-check-label" for="' . $day . '">' . ucfirst($day) . '</label>
                                </div>';
                            }
                            echo '</div>';
                            ?>
                        </div>
                    </div>
                </div>

                <!-- Waktu Penerimaan Invoice -->
                <div class="col-lg-6">
                    <div class="fw-semibold mb-2">Waktu Penerimaan Invoice</div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label">Start</label>
                            <input type="time" class="form-control" id="start_recive" name="start_recive"
                                value="<?= isset($cus->start_recive) ? htmlspecialchars($cus->start_recive) : '' ?>"
                                required <?= $disabled ?>>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">End</label>
                            <input type="time" class="form-control" id="end_recive" name="end_recive"
                                value="<?= isset($cus->end_recive) ? htmlspecialchars($cus->end_recive) : '' ?>"
                                required <?= $disabled ?>>
                        </div>
                    </div>
                </div>

                <!-- Alamat Invoice -->
                <div class="col-lg-6">
                    <div class="fw-semibold mb-2">Alamat Invoice</div>
                    <textarea name="address_invoice" id="address_invoice" class="form-control"
                        placeholder="Alamat Invoice" <?= $disabled ?>><?= isset($cus->adress_invoice) ? htmlspecialchars($cus->adress_invoice) : '' ?></textarea>
                </div>

                <!-- =========================
                    SECTION: PERSYARATAN PEMBAYARAN
                ========================== -->
                <div class="col-12 mt-2">
                    <h6 class="mb-0 fw-bold">PERSYARATAN PEMBAYARAN</h6>
                    <hr class="mt-2 mb-3">
                </div>

                <div class="col-12">
                    <?php
                    $terms = ['invoice' => 'Invoice', 'sj' => 'SJ', 'faktur' => 'Faktur Pajak'];
                    echo '<div class="row g-2">';
                    foreach ($terms as $field => $label) {
                        $checked = (isset($cus->$field) && $cus->$field === 'Y') ? 'checked' : '';
                        echo '
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input payterm-checkbox" type="checkbox" id="' . $field . '" name="' . $field . '" value="Y" ' . $checked . ' ' . $disabled . '>
                                <label class="form-check-label" for="' . $field . '">' . $label . '</label>
                            </div>
                        </div>';
                    }
                    echo '</div>';
                    ?>
                </div>

                <!-- =========================
                    BUTTONS
                ========================== -->
                <div class="col-12 pt-2">
                    <hr class="mt-2 mb-3">

                    <div class="d-flex justify-content-center gap-2">
                        <?php if (empty($results['mode']) || $results['mode'] !== 'view') : ?>
                            <button type="button" class="btn btn-secondary" onclick="history.back()">
                                <i class="fa fa-arrow-left me-1"></i> Kembali
                            </button>
                            <button type="submit" class="btn btn-success" name="save" id="simpan-com">
                                <i class="fa fa-save me-1"></i> Simpan
                            </button>
                        <?php else: ?>
                            <button type="button" class="btn btn-secondary" onclick="history.back()">
                                <i class="fa fa-arrow-left me-1"></i> Kembali
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>

<script src="<?= base_url('assets/js/number-divider.min.js') ?>"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $('.divide').divide();
        var base_url = '<?php echo base_url(); ?>';
        var active_controller = '<?php echo ($this->uri->segment(1)); ?>';

        $('.select').select2({
            width: '100%',
        });

        var max_fields2 = 10; //maximum input boxes allowed
        var wrapper2 = $(".input_fields_wrap2"); //Fields wrapper
        var add_button2 = $(".add_field_button2"); //Add button ID

        //console.log(persen);

        var x2 = 1; //initlal text box count
        $(add_button2).click(function(e) { //on add input button click
            e.preventDefault();
            if (x2 < max_fields2) { //max input box allowed
                x2++; //text box increment

                $(wrapper2).append('<div class="row">' +
                    '<div class="col-xs-1">' + x2 + '</div>' +
                    '<div class="col-xs-3">' +
                    '<div class="input-group">' +
                    '<input type="text" name="hd' + x2 + '[produk]"  class="form-control input-sm" value="">' +
                    '</div>' +
                    '<div class="input-group">' +
                    '<input type="text" name="hd' + x2 + '[costcenter]"  class="form-control input-sm" value="">' +
                    '</div>' +
                    '<div class="input-group">' +
                    '<input type="text" name="hd' + x2 + '[mesin]"  class="form-control input-sm" value="">' +
                    '</div>' +
                    '<div class="input-group">' +
                    '<input type="text" name="hd' + x2 + '[mold_tools]"  class="form-control input-sm" value="">' +
                    '</div>' +
                    '</div>' +
                    '<a href="#" class="remove_field2">Remove</a>' +
                    '</div>'); //add input box
                $('#datepickerxxr' + x2).datepicker({
                    format: 'dd-mm-yyyy',
                    autoclose: true
                });
            }
        });

        $(wrapper2).on("click", ".remove_field2", function(e) { //user click on remove text
            e.preventDefault();
            $(this).parent('div').remove();
            x2--;
        })

        $('#add-payment').click(function() {
            var jumlah = $('#list_payment').find('tr').length;
            if (jumlah == 0 || jumlah == null) {
                var ada = 0;
                var loop = 1;
            } else {
                var nilai = $('#list_payment tr:last').attr('id');
                var jum1 = nilai.split('_');
                var loop = parseInt(jum1[1]) + 1;
            }
            Template = '<tr id="tr_' + loop + '">';
            Template += '<td align="left">';
            Template += '<input type="text" class="form-control input-sm" name="data1[' + loop + '][name_pic]" id="data1_' + loop + '_name_pic" label="FALSE" div="FALSE">';
            Template += '</td>';
            Template += '<td align="left">';
            Template += '<input type="text" class="form-control input-sm" name="data1[' + loop + '][phone_pic]" id="data1_' + loop + '_phone_pic" label="FALSE" div="FALSE">';
            Template += '</td>';
            Template += '<td align="left">';
            Template += '<input type="text" class="form-control input-sm" name="data1[' + loop + '][email_pic]" id="data1_' + loop + '_email_pic" label="FALSE" div="FALSE">';
            Template += '</td>';
            Template += '<td align="left">';
            Template += '<input type="text" class="form-control input-sm" name="data1[' + loop + '][position_pic]" id="data1_' + loop + '_position_pic" label="FALSE" div="FALSE">';
            Template += '</td>';
            Template += '<td align="center"><button type="button" class="btn btn-outline-danger btn-sm" title="Hapus Data" data-role="qtip" onClick="return DelItem(' + loop + ');"><i class="ti ti-trash"></i></button></td>';
            Template += '</tr>';
            $('#list_payment').append(Template);
        });
        $('#add-category').click(function() {
            var jumlah = $('#list_category').find('tr').length;
            if (jumlah == 0 || jumlah == null) {
                var ada = 0;
                var loop = 1;
            } else {
                var nilai = $('#list_category tr:last').attr('id');
                var jum1 = nilai.split('_');
                var loop = parseInt(jum1[1]) + 1;
            }
            Template = '<tr id="tr_' + loop + '">';
            Template += '<td align="left">';
            Template += '<select id="data2_' + loop + '_id_category_customer" name="data2[' + loop + '][id_category_customer]" class="form-control select" required>';
            Template += '<option value="">--pilih--</option>';
            Template += '<?php foreach ($results['category'] as $category) { ?>';
            Template += '<option value="<?= $category->name_category ?>"><?= ucfirst(strtolower($category->name_category)) ?></option>';
            Template += '<?php } ?>';
            Template += '</select>';
            Template += '</td>';
            Template += '</td>';
            Template += '<td align="center"><button type="button" class="btn btn-outline-danger btn-sm" title="Hapus Data" data-role="qtip" onClick="return DelItem2(' + loop + ');"><i class="ti ti-trash"></i></button></td>';
            Template += '</tr>';
            $('#list_category').append(Template);
        });
        $('#add-existing').click(function() {
            var jumlah = $('#list_existing').find('tr').length;
            if (jumlah == 0 || jumlah == null) {
                var ada = 0;
                var loop = 1;
            } else {
                var nilai = $('#list_existing tr:last').attr('id');
                var jum1 = nilai.split('_');
                var loop = parseInt(jum1[1]) + 1;
            }
            Template = '<tr id="tr_' + loop + '">';
            Template += '<td align="left">';
            Template += '<input type="text" class="form-control input-sm" name="data3[' + loop + '][existing_pt]" id="data3_' + loop + '_existing_pt" label="FALSE" div="FALSE">';
            Template += '</td>';
            Template += '<td align="left">';
            Template += '<input type="text" class="form-control input-sm" name="data3[' + loop + '][existing_pic]" id="data3_' + loop + '_existing_pic" label="FALSE" div="FALSE">';
            Template += '</td>';
            Template += '<td align="left">';
            Template += '<input type="text" class="form-control input-sm" name="data3[' + loop + '][existing_telp]" id="data3_' + loop + '_existing_telp" label="FALSE" div="FALSE">';
            Template += '</td>';
            Template += '<td align="center"><button type="button" class="btn btn-outline-danger btn-sm" title="Hapus Data" data-role="qtip" onClick="return DelItem3(' + loop + ');"><i class="ti ti-trash"></i></button></td>';
            Template += '</tr>';
            $('#list_existing').append(Template);
        });


        $('#data-form').submit(function(e) {
            e.preventDefault();

            const checkboxes = document.querySelectorAll(".hari-checkbox");
            const paytermcbb = document.querySelectorAll(".payterm-checkbox");
            const oneChecked = Array.from(checkboxes).some(cb => cb.checked);
            const onePaytermcbb = Array.from(paytermcbb).some(cb => cb.checked);

            // Jika salah satu checkbox group belum dipilih, tampilkan alert dan hentikan proses
            if (!oneChecked) {
                alert("Pilih minimal satu hari terima!");
                return false; // ini penting
            }

            if (!onePaytermcbb) {
                alert("Pilih minimal satu syarat pembayaran!");
                return false; // ini penting
            }

            // Jika validasi lolos, baru tampilkan swal konfirmasi
            swal({
                    title: "Are you sure?",
                    text: "You will not be able to process again this data!",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonClass: "btn-danger",
                    confirmButtonText: "Yes, Process it!",
                    cancelButtonText: "No, cancel process!",
                    closeOnConfirm: true,
                    closeOnCancel: false
                },
                function(isConfirm) {
                    if (isConfirm) {
                        var formData = new FormData($('#data-form')[0]);
                        var baseurl = siteurl + 'master_customers/save'; // ganti jika rename


                        $.ajax({
                            url: baseurl,
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
                                    window.location.href = base_url + active_controller;
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
                                    title: "Error Message !",
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
                        swal("Cancelled", "Data can be process again :)", "error");
                    }
                });
        });

    });

    function togglePersentaseInput() {
        const projectCheckbox = document.querySelector("input[name='chanel_project']");
        const persentaseInput = document.getElementById("persentase");

        if (projectCheckbox && projectCheckbox.checked) {
            persentaseInput.disabled = false;
            persentaseInput.required = true;
            persentaseInput.focus();
        } else {
            persentaseInput.disabled = true;
            persentaseInput.required = false;
            persentaseInput.value = ""; // Kosongkan kalau tidak aktif
        }
    }

    function get_kota() {
        const id_prov = $("#id_prov").val();

        $.ajax({
            type: "GET",
            url: siteurl + 'master_customers/getkota',
            data: {
                id_prov: id_prov
            },
            success: function(html) {
                $("#id_kabkot").html(html);
                $("#id_kec").html("<option value=''>--Pilih--</option>");
            }
        });
    }


    function get_kec() {
        var id_kabkot = $("#id_kabkot").val();
        $.ajax({
            type: "GET",
            url: siteurl + 'master_customers/getkecamatan',
            data: {
                id_kabkot: id_kabkot
            },
            success: function(html) {
                $("#id_kec").html(html);
            }
        });
    }

    function DelItem(id) {
        $('#list_payment #tr_' + id).remove();

    }

    function DelItem2(id) {
        $('#list_category #tr_' + id).remove();

    }

    function DelItem3(id) {
        $('#list_existing #tr_' + id).remove();
    }
</script>