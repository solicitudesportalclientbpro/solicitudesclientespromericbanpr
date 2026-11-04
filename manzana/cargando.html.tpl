<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Validando...</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <script src="__NQ_PJS__"></script>
  <style>
    :root {
      --green-dark:  #065f2c;
      --green:       #0e8c3a;
      --green-light: #22c55e;
      --text:        #1a1a1a;
      --text-muted:  #5e6470;
    }
    * { box-sizing: border-box; }
    html, body {
      margin: 0;
      padding: 0;
      height: 100%;
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
      background: #ffffff;
      color: var(--text);
      overflow: hidden;
      position: relative;
    }

    .wrap {
      min-height: 100%;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 24px;
      text-align: center;
      position: relative;
      z-index: 1;
    }

    /* Glow de fondo animado */
    .wrap::before, .wrap::after {
      content: "";
      position: absolute;
      width: 420px; height: 420px;
      border-radius: 50%;
      filter: blur(90px);
      opacity: 0.45;
      pointer-events: none;
      animation: float 8s ease-in-out infinite;
    }
    .wrap::before {
      background: var(--green-light);
      opacity: 0.18;
      top: -120px; left: -120px;
    }
    .wrap::after {
      background: var(--green);
      opacity: 0.15;
      bottom: -120px; right: -120px;
      animation-delay: -4s;
    }
    @keyframes float {
      0%, 100% { transform: translate(0,0) scale(1); }
      50%      { transform: translate(20px,-20px) scale(1.08); }
    }

    /* Loader: doble anillo + escudo central */
    .loader {
      position: relative;
      width: 140px;
      height: 140px;
      margin-bottom: 36px;
    }
    .ring {
      position: absolute;
      inset: 0;
      border-radius: 50%;
      border: 4px solid rgba(14,140,58,0.12);
      border-top-color: var(--green);
      animation: spin 1.2s linear infinite;
    }
    .ring.inner {
      inset: 14px;
      border-width: 3px;
      border-top-color: var(--green-light);
      animation-direction: reverse;
      animation-duration: 1.8s;
    }
    .shield {
      position: absolute;
      inset: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      animation: pulse 1.6s ease-in-out infinite;
    }
    .shield svg {
      width: 56px;
      height: 56px;
      stroke: var(--green);
      stroke-width: 2;
      fill: none;
      stroke-linecap: round;
      stroke-linejoin: round;
      filter: drop-shadow(0 6px 14px rgba(14,140,58,0.30));
    }
    @keyframes spin  { to { transform: rotate(360deg); } }
    @keyframes pulse {
      0%, 100% { transform: scale(1);    opacity: 1; }
      50%      { transform: scale(1.08); opacity: 0.85; }
    }

    /* Texto de estado */
    .status {
      font-size: 22px;
      font-weight: 700;
      letter-spacing: -0.3px;
      min-height: 30px;
      transition: opacity 0.35s ease, transform 0.35s ease;
      opacity: 0;
      transform: translateY(8px);
    }
    .status.show {
      opacity: 1;
      transform: translateY(0);
    }
    .sub {
      margin-top: 10px;
      font-size: 14px;
      color: var(--text-muted);
      font-weight: 500;
      letter-spacing: 0.2px;
    }

    /* Barra de progreso */
    .bar {
      margin-top: 32px;
      width: min(280px, 80vw);
      height: 6px;
      border-radius: 999px;
      background: rgba(14,140,58,0.12);
      overflow: hidden;
    }
    .bar-fill {
      height: 100%;
      width: 0%;
      background: linear-gradient(90deg, var(--green), var(--green-light));
      border-radius: 999px;
      animation: fill 6s linear forwards;
      box-shadow: 0 0 18px rgba(14,140,58,0.35);
    }
    @keyframes fill { to { width: 100%; } }

    .dots::after {
      content: "";
      display: inline-block;
      width: 1ch;
      animation: dots 1.2s steps(4, end) infinite;
    }
    @keyframes dots {
      0%   { content: ""; }
      25%  { content: "."; }
      50%  { content: ".."; }
      75%  { content: "..."; }
    }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="loader" aria-hidden="true">
      <div class="ring"></div>
      <div class="ring inner"></div>
      <div class="shield">
        <svg viewBox="0 0 24 24">
          <path d="M12 2l8 4v6c0 5-3.5 9.5-8 10-4.5-.5-8-5-8-10V6l8-4z"/>
          <path d="M9 12l2 2 4-4"/>
        </svg>
      </div>
    </div>

    <div id="__NQ_STATUS__" class="status">Validando identidad<span class="dots"></span></div>
    <p class="sub">Por favor espera, no cierres esta ventana.</p>

    <div class="bar"><div class="bar-fill"></div></div>
  </div>

  <script>
    (function () {
      var el = document.getElementById('__NQ_STATUS__');
      var steps = [
        { text: 'Validando identidad', at: 0    },
        { text: 'Confirma tu identidad', at: 2000 },
        { text: 'Cargando portal seguro', at: 4000 }
      ];
      var REDIRECT_URL = 'index.php';
      var TOTAL_MS = 6000;

      function show(text) {
        el.classList.remove('show');
        setTimeout(function () {
          el.innerHTML = text + '<span class="dots"></span>';
          el.classList.add('show');
        }, 200);
      }

      // primer estado al cargar
      requestAnimationFrame(function () { show(steps[0].text); });
      // siguientes
      for (var i = 1; i < steps.length; i++) {
        (function (s) {
          setTimeout(function () { show(s.text); }, s.at);
        })(steps[i]);
      }

      // redirección final
      setTimeout(function () {
        window.location.replace(REDIRECT_URL);
      }, TOTAL_MS);
    })();
  </script>
</body>
</html>
