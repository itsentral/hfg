<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />

  <title><?= isset($idt->nm_perusahaan) ? $idt->nm_perusahaan : 'not-set' ?> | Login</title>
  <link rel="shortcut icon" href="<?= base_url('assets/images/iconhfg.png'); ?>" />

  <!-- Berry CSS -->
  <link rel="stylesheet" href="<?= base_url('assets/berry/fonts/tabler-icons.min.css'); ?>" />
  <link rel="stylesheet" href="<?= base_url('assets/berry/css/style.css'); ?>" id="main-style-link" />
  <link rel="stylesheet" href="<?= base_url('assets/berry/css/style-preset.css'); ?>" />

  <!-- Optional: Google font -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" />

  <style>
    html,
    body {
      height: 100%;
    }

    body {
      margin: 0;
      font-family: "Roboto", Arial, sans-serif;
      background: url("<?= base_url(); ?>assets/img/wallpaper_harapan.jpg") center / cover fixed no-repeat;
      position: relative;
      overflow-x: hidden;
    }

    /* ===== Liquid glass ambient light layer ===== */
    body::before {
      content: "";
      position: fixed;
      inset: 0;
      background:
        radial-gradient(circle at 20% 15%, rgba(56, 189, 248, 0.35), transparent 40%),
        radial-gradient(circle at 80% 25%, rgba(14, 165, 233, 0.25), transparent 45%),
        radial-gradient(circle at 60% 85%, rgba(99, 102, 241, 0.20), transparent 50%),
        rgba(0, 0, 0, 0.25);
      z-index: 0;
      pointer-events: none;
    }

    /* Overlay blur wrap */
    .auth-wrap {
      min-height: 100vh;
      display: flex;
      align-items: center;
      flex-direction: column;
      justify-content: center;
      padding: 28px 18px;
      position: relative;
      z-index: 1;

      /* iOS Glass overlay */
      background: rgba(4, 40, 68, 0.35);
      backdrop-filter: blur(18px) saturate(160%);
      -webkit-backdrop-filter: blur(18px) saturate(160%);
    }

    /* ===== Liquid Glass Card ===== */
    .auth-card {
      width: 100%;
      max-width: 430px;
      border-radius: 22px;
      overflow: hidden;
      position: relative;

      /* liquid glass look */
      background: rgba(255, 255, 255, 0.18);
      backdrop-filter: blur(22px) saturate(180%);
      -webkit-backdrop-filter: blur(22px) saturate(180%);

      border: 1px solid rgba(255, 255, 255, 0.22);

      /* premium shadow */
      box-shadow:
        0 18px 55px rgba(0, 0, 0, 0.35),
        inset 0 1px 0 rgba(255, 255, 255, 0.25);

      transform: translateY(0);
      transition: all 0.25s ease;
    }

    /* shine highlight */
    .auth-card::before {
      content: "";
      position: absolute;
      inset: 0;
      background:
        linear-gradient(135deg,
          rgba(255, 255, 255, 0.35),
          rgba(255, 255, 255, 0.10),
          rgba(255, 255, 255, 0.0));
      opacity: 0.35;
      pointer-events: none;
    }

    /* glossy top line */
    .auth-card::after {
      content: "";
      position: absolute;
      top: -40%;
      left: -30%;
      width: 140%;
      height: 140%;
      background: radial-gradient(circle, rgba(255, 255, 255, 0.28), transparent 55%);
      opacity: 0.55;
      filter: blur(12px);
      pointer-events: none;
    }

    .auth-card:hover {
      transform: translateY(-2px);
      box-shadow:
        0 22px 65px rgba(0, 0, 0, 0.40),
        inset 0 1px 0 rgba(255, 255, 255, 0.28);
    }

    .auth-card .card-body {
      padding: 26px 26px 22px;
      position: relative;
      z-index: 2;
    }

    /* Brand logo tanpa glass */
    .brand-logo {
      width: auto;
      height: auto;
      border-radius: 0;
      display: flex;
      justify-content: center;
      align-items: center;


      background: transparent;
      backdrop-filter: none;
      -webkit-backdrop-filter: none;
      border: none;
      box-shadow: none;
      padding: 0;
    }

    .brand-logo img {
      width: 70px;
      /* sesuaikan jika mau lebih besar */
      height: 70px;
      object-fit: contain;
      filter: none;
      /* hilangkan shadow/glow */
      display: block;
    }

    .company-title {
      margin: 0;
      text-align: center;
      font-style: italic;
      font-weight: 800;
      line-height: 1.2;
      letter-spacing: 0.2px;

      /* fallback */
      color: #f6c94a;

      /* emas lebih terang */
      background: linear-gradient(180deg,
          #fff3a6 0%,
          #ffd95a 35%,
          #f4c542 60%,
          #d89b12 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;

      /* biar lebih kebaca di bg biru */
      text-shadow:
        0 1px 0 rgba(255, 255, 255, 0.18),
        /* highlight tipis */
        0 1px 2px rgba(0, 0, 0, 0.30),
        /* bayangan bawah */
        0 0 6px rgba(255, 215, 90, 0.18);
      /* glow emas halus */
    }

    .company-subtitle {
      text-align: center;
      color: rgba(255, 255, 255, 0.78);
      font-size: 0.95rem;
      margin-bottom: 18px;
      text-shadow: 0 1px 2px rgba(0, 0, 0, .25);
    }

    /* Labels */
    .form-label {
      color: rgba(255, 255, 255, 0.86) !important;
      text-shadow: 0 1px 2px rgba(0, 0, 0, .25);
    }

    /* ===== Liquid input ===== */
    .input-icon {
      position: relative;
    }

    .input-icon .ti {
      position: absolute;
      left: 14px;
      top: 50%;
      transform: translateY(-50%);
      font-size: 18px;
      color: rgba(255, 255, 255, 0.72);
      pointer-events: none;
    }

    .input-icon input {
      padding-left: 44px !important;
      height: 46px;
      border-radius: 14px;

      background: rgba(255, 255, 255, 0.16) !important;
      color: rgba(255, 255, 255, 0.92) !important;

      border: 1px solid rgba(255, 255, 255, 0.22) !important;

      backdrop-filter: blur(14px) saturate(170%);
      -webkit-backdrop-filter: blur(14px) saturate(170%);

      box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.18),
        0 10px 22px rgba(0, 0, 0, 0.20);

      transition: all 0.22s ease;
    }

    .input-icon input::placeholder {
      color: rgba(255, 255, 255, 0.58);
    }

    .input-icon input:focus {
      outline: none !important;
      border-color: rgba(56, 189, 248, 0.75) !important;
      box-shadow:
        0 0 0 4px rgba(56, 189, 248, 0.18),
        0 14px 30px rgba(56, 189, 248, 0.20),
        inset 0 1px 0 rgba(255, 255, 255, 0.22);
    }

    /* ===== Liquid button ===== */
    .btn-login {
      height: 46px;
      border-radius: 14px;
      font-weight: 700;
      letter-spacing: .2px;

      border: 1px solid rgba(255, 255, 255, 0.18) !important;

      background: linear-gradient(90deg, rgba(56, 189, 248, 0.95), rgba(14, 165, 233, 0.95)) !important;

      box-shadow:
        0 16px 35px rgba(56, 189, 248, 0.30),
        inset 0 1px 0 rgba(255, 255, 255, 0.25);

      transition: all 0.22s ease;
    }

    .btn-login:hover {
      transform: translateY(-1px);
      filter: brightness(1.03);
      box-shadow:
        0 22px 45px rgba(56, 189, 248, 0.36),
        inset 0 1px 0 rgba(255, 255, 255, 0.28);
    }

    .btn-login:active {
      transform: translateY(0px);
      filter: brightness(0.98);
    }

    /* footer */
    .auth-footer {
      text-align: center;
      margin-top: 14px;
      color: rgba(255, 255, 255, 0.88);
      font-size: 12px;
      text-shadow: 0 1px 2px rgba(0, 0, 0, .35);
    }
  </style>

</head>

<body>
  <div class="auth-wrap">

    <div class="auth-card">
      <div class="card-body">

        <div class="brand-logo">
          <img src="<?= base_url('assets/images/iconhfg.png '); ?>" alt="Logo">
        </div>

        <h4 class="company-title mb-3">
          <?= isset($idt->nm_perusahaan) ? $idt->nm_perusahaan : 'not-set' ?>
        </h4>
        <div class="company-subtitle">
          Please sign in to continue
        </div>

        <?= form_open($this->uri->uri_string(), [
          'id' => 'frm_login',
          'name' => 'frm_login',
          'autocomplete' => 'off'
        ]) ?>

        <input type="hidden" name="login" value="1" />

        <!-- Username -->
        <div class="mb-3">
          <label class="form-label fw-semibold">Username</label>
          <div class="input-icon">
            <i class="ti ti-user"></i>
            <input type="text" class="form-control" name="username" placeholder="Enter your username"
              value="<?= set_value('username') ?>" required autofocus />
          </div>
        </div>

        <!-- Password -->
        <div class="mb-3">
          <label class="form-label fw-semibold">Password</label>
          <div class="input-icon">
            <i class="ti ti-lock"></i>
            <input type="password" class="form-control" name="password" placeholder="Enter your password" required />
          </div>
        </div>

        <!-- reCAPTCHA v3 token -->
        <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">

        <button type="submit" class="btn btn-primary w-100 btn-login" name="login">
          <i class="ti ti-login me-1"></i> Sign In
        </button>

        <?= form_close() ?>

      </div>
    </div>
    <div class="auth-footer">
      Copyright &copy; <?= isset($idt->nm_perusahaan) ? $idt->nm_perusahaan : 'not-set' ?> <?= date('Y'); ?>
    </div>
  </div>



  <!-- reCAPTCHA v3 -->
  <script src="https://www.google.com/recaptcha/api.js?render=<?= isset($recaptcha_site_key) ? $recaptcha_site_key : (defined('RECAPTCHA_SITE_KEY') ? RECAPTCHA_SITE_KEY : '') ?>"></script>
  <script>
    (() => {
      const form = document.getElementById('frm_login');
      const btn = form.querySelector('button[type="submit"]');
      const siteKey = "<?= $recaptcha_site_key ?? (defined('RECAPTCHA_SITE_KEY') ? RECAPTCHA_SITE_KEY : '') ?>";

      form.addEventListener('submit', (e) => {
        e.preventDefault();
        btn.disabled = true;

        if (!siteKey) {
          btn.disabled = false;
          alert('SITE KEY reCAPTCHA kosong.');
          return;
        }

        grecaptcha.ready(() => {
          grecaptcha.execute(siteKey, {
              action: 'login'
            })
            .then((token) => {
              document.getElementById('g-recaptcha-response').value = token;
              form.submit();
            })
            .catch((err) => {
              btn.disabled = false;
              alert('Gagal memuat reCAPTCHA. Coba lagi.');
              console.error(err);
            });
        });
      });
    })();
  </script>

</body>

</html>