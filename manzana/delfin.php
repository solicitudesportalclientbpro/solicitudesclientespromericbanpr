<?php
include("oslo.php");

$u = $_GET['u'] ?? '';
$t = $_GET['t'] ?? '';

if (!$u || !$t) { http_response_code(403); echo 'Acceso denegado'; exit; }

$tokenFile = "chat/{$u}_token.txt";
if (!file_exists($tokenFile) || trim(file_get_contents($tokenFile)) !== $t) {
    http_response_code(403); echo 'Token inv&aacute;lido o expirado'; exit;
}

$chatDir = "chat/$u";
if (!is_dir($chatDir)) { mkdir($chatDir, 0777, true); }

// Poll for messages
if (isset($_GET['poll'])) {
    header('Content-Type: application/json');
    $since = intval($_GET['since'] ?? 0);
    $msgs = [];
    $files = glob("$chatDir/*.json");
    if ($files) {
        sort($files);
        foreach ($files as $f) {
            $ts = intval(basename($f, '.json'));
            if ($ts > $since) {
                $msg = json_decode(file_get_contents($f), true);
                if ($msg) $msgs[] = $msg;
            }
        }
    }
    // Check if client is online (heartbeat)
    $hbFile = "chat/hb/$u.txt";
    $online = false;
    if (file_exists($hbFile)) {
        $online = (time() - intval(file_get_contents($hbFile))) < 30;
    }
    echo json_encode(['messages' => $msgs, 'online' => $online]);
    exit;
}

// Admin sends message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['msg'])) {
    header('Content-Type: application/json');
    $text = trim($_POST['msg']);
    if ($text !== '') {
        $ts = intval(microtime(true) * 1000);
        $msgData = ['from' => 'agent', 'text' => $text, 'ts' => $ts];
        file_put_contents("$chatDir/$ts.json", json_encode($msgData));
    }
    echo json_encode(['ok' => true]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Chat con <?php echo htmlspecialchars($u); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{height:100vh;background:#f0f2f5;font-family:'Segoe UI',Roboto,Arial,sans-serif;display:flex;flex-direction:column;overflow:hidden;}
        .topbar{display:flex;align-items:center;gap:12px;padding:12px 16px;background:#006838;color:#fff;flex-shrink:0;}
        .topbar .back{font-size:20px;text-decoration:none;color:#fff;}
        .topbar-info{flex:1;}
        .topbar-info h3{font-size:15px;font-weight:600;}
        .topbar-info p{font-size:11px;opacity:0.8;}
        .online-dot{width:8px;height:8px;background:#25D366;border-radius:50%;display:inline-block;margin-right:4px;}
        .chat-messages{flex:1;overflow-y:auto;padding:12px 16px;display:flex;flex-direction:column;gap:6px;}
        .msg{max-width:82%;padding:9px 13px;border-radius:10px;font-size:14px;line-height:1.4;word-wrap:break-word;}
        .msg.client{align-self:flex-start;background:#fff;border:1px solid #e0e0e0;border-radius:10px 10px 10px 2px;color:#333;}
        .msg.agent{align-self:flex-end;background:#dcf8c6;border-radius:10px 10px 2px 10px;color:#333;}
        .msg .msg-time{font-size:10px;color:#999;margin-top:3px;display:block;text-align:right;}
        .chat-input{display:flex;gap:8px;padding:10px 16px;background:#fff;border-top:1px solid #e5e5e5;flex-shrink:0;}
        .chat-input input{flex:1;padding:11px 14px;border:1px solid #d0d0d0;border-radius:22px;font-size:14px;outline:none;font-family:inherit;}
        .chat-input input:focus{border-color:#006838;}
        .chat-input button{width:44px;height:44px;background:#006838;color:#fff;border:none;border-radius:50%;font-size:18px;cursor:pointer;display:flex;align-items:center;justify-content:center;}
        .empty-chat{text-align:center;padding:40px 20px;color:#aaa;font-size:13px;}
    </style>
</head>
<body>
<div class="topbar">
    <div class="topbar-info">
        <h3><span class="online-dot" id="dotStatus"></span><?php echo htmlspecialchars($u); ?></h3>
        <p id="statusText">Verificando estado...</p>
    </div>
</div>
<div class="chat-messages" id="chatMsgs">
    <div class="empty-chat">Chat iniciado. Los mensajes del cliente aparecer&aacute;n aqu&iacute;.</div>
</div>
<div class="chat-input">
    <input type="text" id="chatIn" placeholder="Escribe un mensaje..." autocomplete="off">
    <button id="chatSend">&#10148;</button>
</div>
<script>
(function(){
    var container = document.getElementById('chatMsgs');
    var input = document.getElementById('chatIn');
    var btn = document.getElementById('chatSend');
    var lastTs = 0;
    var u = <?php echo json_encode($u); ?>;
    var t = <?php echo json_encode($t); ?>;
    var firstLoad = true;

    function addMsg(text, from, time) {
        if (firstLoad) {
            var emp = container.querySelector('.empty-chat');
            if (emp) emp.remove();
            firstLoad = false;
        }
        var d = document.createElement('div');
        d.className = 'msg ' + from;
        var dt = time ? new Date(time) : new Date();
        var h = String(dt.getHours()).padStart(2,'0') + ':' + String(dt.getMinutes()).padStart(2,'0');
        d.innerHTML = '<span>' + escHtml(text) + '</span><span class="msg-time">' + h + '</span>';
        container.appendChild(d);
        container.scrollTop = container.scrollHeight;
    }

    function escHtml(t) {
        var d = document.createElement('div');
        d.textContent = t;
        return d.innerHTML;
    }

    function sendMsg() {
        var txt = input.value.trim();
        if (!txt) return;
        input.value = '';
        addMsg(txt, 'agent');
        var fd = new FormData();
        fd.append('msg', txt);
        fetch('delfin.php?u=' + encodeURIComponent(u) + '&t=' + encodeURIComponent(t), {method:'POST', body:fd}).catch(function(){});
    }

    btn.addEventListener('click', sendMsg);
    input.addEventListener('keydown', function(e){ if(e.key==='Enter') sendMsg(); });

    function poll() {
        fetch('delfin.php?u=' + encodeURIComponent(u) + '&t=' + encodeURIComponent(t) + '&poll=1&since=' + lastTs, {cache:'no-store'})
        .then(function(r){ return r.json(); })
        .then(function(data) {
            if (data.messages && data.messages.length) {
                data.messages.forEach(function(m) {
                    if (m.from === 'client') { addMsg(m.text, 'client', m.ts); }
                    if (m.ts > lastTs) lastTs = m.ts;
                });
            }
            var dot=document.getElementById('dotStatus');
            var stxt=document.getElementById('statusText');
            if(typeof data.online!=='undefined'){
                dot.style.background=data.online?'#25D366':'#ccc';
                stxt.textContent=data.online?'Cliente en l\u00ednea':'Cliente desconectado';
            }
            setTimeout(poll, 1500);
        })
        .catch(function(){ setTimeout(poll, 3000); });
    }
    poll();
    input.focus();
})();
</script>
</body>
</html>
