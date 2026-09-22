<?php
session_start();

// Keluar dari mode admin, kembali ke hadiah
if (isset($_GET['exit_admin'])) {
    unset($_SESSION['secret_admin']); // keluar dari admin rahasia
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Mode admin rahasia
if (isset($_GET['secret_admin']) && $_SESSION['role'] === 'hadiah') {
    $_SESSION['secret_admin'] = true;
}

// ===== COUNTER AKSES LINGGA =====
$counter_file = "counter.txt";

$last_access_file = "last_access.txt";
date_default_timezone_set("Asia/Jakarta");
$daily_access_file = "daily_access.txt";

if (!file_exists($counter_file)) {
    file_put_contents($counter_file, "0");
}


// Logout handler
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

$login = false;
$error = "";

// Tanggal dan nama valid (ubah jika perlu)
$tanggal_valid = "2005-09-23";
$nama_terima = "lingga syafira"; // Nama target dalam format lowercase
// User admin (Irfan)
$admin_nama = "muhammad irfan fauzi";
$admin_tanggal = "2005-06-08";


// Fungsi untuk normalisasi nama: trim, collapse whitespace, lowercase
function normalize_name($s) {
    $s = trim($s);
    $s = preg_replace('/\s+/', ' ', $s);
    $s = mb_strtolower($s, 'UTF-8');
    return $s;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_raw = isset($_POST['nama']) ? $_POST['nama'] : '';
    $tanggal = isset($_POST['tanggal']) ? $_POST['tanggal'] : '';
    
    $nama_display = htmlspecialchars(trim($nama_raw));
    $nama_norm = normalize_name($nama_raw);
    
   // ===== LOGIN LINGGA (USER HADIAH) =====
if ($tanggal === $tanggal_valid && $nama_norm === $nama_terima) {
    $_SESSION['nama'] = $nama_display;
    $_SESSION['tanggal'] = $tanggal;
    $_SESSION['role'] = 'hadiah';

    // Tambah counter SETIAP kali Lingga login
    $jumlah = (int) file_get_contents($counter_file);
    $jumlah++;
    file_put_contents($counter_file, $jumlah);
    // Simpan waktu terakhir akses Lingga
    $waktu_terakhir = date("d F Y, H:i:s");
    file_put_contents($last_access_file, $waktu_terakhir);

    // ===== CATAT SETIAP AKSES (TANPA RINGKASAN) =====
$tanggal = date("d-m-Y");
$jam = date("H:i:s");

// ambil max 300 baris terakhir biar aman
$data = file_exists($daily_access_file)
    ? array_slice(file($daily_access_file, FILE_IGNORE_NEW_LINES), -300)
    : [];

$data[] = "$tanggal | $jam";

file_put_contents($daily_access_file, implode(PHP_EOL, $data));

    $login = true;
    
}

// ===== LOGIN IRFAN (ADMIN) =====
elseif ($tanggal === $admin_tanggal && $nama_norm === $admin_nama) {
    $_SESSION['nama'] = $nama_display;
    $_SESSION['tanggal'] = $tanggal;
    $_SESSION['role'] = 'admin';
    $login = true;
}
 else {
        $error = "❌ Nama dan tanggal lahir harus sesuai kocak 🤣. Coba lagi hehe.";
    }
}

if (isset($_SESSION['nama'], $_SESSION['tanggal'], $_SESSION['role'])) {
    if (
        ($_SESSION['role'] === 'hadiah' && $_SESSION['tanggal'] === $tanggal_valid) ||
        ($_SESSION['role'] === 'admin'  && $_SESSION['tanggal'] === $admin_tanggal)
    ) {
        $login = true;
    } else {
        session_unset();
        session_destroy();
        $login = false;
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Happy Birthday!</title>
<style>
    :root {
        --primary-glow: rgba(255, 105, 180, 0.9);
        --secondary-glow: rgba(255, 180, 220, 1);
        --bg-grad-1: #ee7752;
        --bg-grad-2: #e73c7e;
        --bg-grad-3: #23a6d5;
        --bg-grad-4: #23d5ab;
    }
    body {
        margin: 0; height: 100vh; overflow: hidden; display: flex;
        justify-content: center; align-items: center;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(-45deg, var(--bg-grad-1), var(--bg-grad-2), var(--bg-grad-3), var(--bg-grad-4));
        background-size: 400% 400%;
        animation: gradientBG 15s ease infinite; color: white;
    }
    @keyframes gradientBG { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }

    .form-box {
        background: rgba(255,255,255,0.1); backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,0.2); padding: 40px; border-radius: 20px;
        text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        max-width: 420px; width: 92vw; z-index: 10;
        animation: popIn 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
    }
    @keyframes popIn { from { opacity: 0; transform: scale(0.8); } to { opacity: 1; transform: scale(1); } }
    .form-box h1 {
        animation: shimmer 4s infinite linear;
        background: linear-gradient(90deg, #ff9a9e, #fecfef, #a1c4fd, #c2e9fb);
        background-size: 300% 300%;
        -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    }
    input { padding: 12px 18px; margin: 8px 0; border-radius: 10px; border: 1px solid #ccc; font-size: 16px; width: 100%; box-sizing: border-box; outline: none; transition: all 0.3s ease; }
    input:focus { border-color: var(--bg-grad-2); box-shadow: 0 0 15px rgba(231, 60, 126, 0.5); }
    button { padding: 12px 18px; margin-top: 10px; border-radius: 10px; border: none; font-size: 16px; font-weight: bold; background: linear-gradient(135deg, #ff6ec4, #7873f5); color: white; cursor: pointer; width: 100%; transition: transform 0.2s ease, box-shadow 0.2s ease; text-transform: uppercase; }
    button:hover { transform: translateY(-3px) scale(1.03); box-shadow: 0 8px 25px rgba(0,0,0,0.3); }
    .error-message { background: rgba(255, 82, 82, 0.7); color: white; padding: 10px; border-radius: 8px; font-weight: bold; margin-top: 12px; min-height: 20px; }

    .hidden {
        opacity: 0 !important;
        transform: scale(1.5) !important;
        pointer-events: none !important;
        display: none !important;
    }

    #gift-section { text-align: center; user-select: none; z-index: 10; transition: opacity 0.5s, transform 0.5s; animation: popIn 0.8s ease; }
    #gift { font-size: 150px; cursor: pointer; filter: drop-shadow(0 0 15px var(--primary-glow)); transition: transform 0.3s ease, filter 0.3s ease; display: inline-block; }
    #gift:hover { transform: scale(1.15) rotate(-10deg); filter: drop-shadow(0 0 25px var(--primary-glow)); }
    #gift.shake { animation: shakeGift 0.5s cubic-bezier(.36,.07,.19,.97) both; }
    @keyframes shakeGift { 10%, 90% { transform: translate3d(-1px, 0, 0) rotate(-1deg); } 20%, 80% { transform: translate3d(2px, 0, 0) rotate(2deg); } 30%, 50%, 70% { transform: translate3d(-4px, 0, 0) rotate(-3deg); } 40%, 60% { transform: translate3d(4px, 0, 0) rotate(3deg); } }
    #click-gift-text { margin-top: 15px; font-size: 1.2em; font-weight: 600; text-shadow: 0 0 8px rgba(255,255,255,0.8); animation: pulseText 2s infinite ease-in-out; }
    @keyframes pulseText { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.05); } }

    #content { text-align: center; z-index: 10; max-width: 700px; margin: 0 auto; }
    .cake-container { position: relative; animation: popIn 1s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
    .cake { font-size: 180px; filter: drop-shadow(0 0 25px var(--secondary-glow)); margin-bottom: -40px; }
    #flame { font-size: 50px; position: absolute; top: -20px; left: 50%; transform: translateX(-50%); cursor: pointer; transition: all 0.3s ease; text-shadow: 0 0 20px #ffc107, 0 0 30px #ff9800; animation: flicker 1.5s infinite alternate; }
    #flame.out { animation: puff-out 0.5s forwards ease-out; }
    @keyframes flicker { 0%, 18%, 22%, 25%, 53%, 57%, 100% { opacity: 1; } 20%, 24%, 55% { opacity: 0.8; } }
    @keyframes puff-out { 0% { opacity: 1; transform: translateX(-50%) scale(1); } 100% { opacity: 0; transform: translateX(-50%) scale(0) translateY(-20px); } }
    @keyframes bounceContinuously { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-20px); } }

    /* --- PERUBAHAN CSS DIMULAI DI SINI --- */
    #instruction-text {
        animation: fadeIn 1s 1s forwards;
        opacity: 0;
        /* Transisi untuk opacity dan visibility agar animasi halus */
        transition: opacity 0.5s ease-out, visibility 0.5s ease-out;
    }
    /* Class baru ini akan kita tambahkan via JavaScript */
    #instruction-text.fade-out-final {
        opacity: 0;
        visibility: hidden; /* Sembunyikan elemen sepenuhnya setelah transisi */
    }
    /* --- PERUBAHAN CSS SELESAI --- */

    .card { background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.2); padding: 30px 40px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); opacity: 0; transform: translateY(30px); visibility: hidden; }
    .card.visible { visibility: visible; animation: cardFadeUp 1s ease forwards; }
    @keyframes cardFadeUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
    .glow-text { font-size: 2.2em; font-weight: bold; margin-bottom: 20px; }
    .glow-text span { opacity: 0; animation: letter-fade-in 0.5s forwards; }
    @keyframes letter-fade-in { to { opacity: 1; } }
    .typewriter { text-align:justify; line-height:1.6; min-height:150px; white-space: pre-line; font-size: 1.1em; }
    
    #music-toggle { position: fixed; top: 20px; right: 20px; z-index: 100; background: rgba(255,255,255,0.2); width: 50px; height: 50px; border-radius: 50%; font-size: 24px; padding: 0; }
    .particle { position: fixed; top: 0; left: 0; pointer-events: none; z-index: 1; opacity: 0; }
    .confetti { width: 10px; height: 10px; background-color: #f00; animation: fall 5s linear infinite; }
    .balloon { font-size: 40px; animation: floatUp 10s linear infinite; }
    @keyframes fall { 0% { transform: translateY(-20vh) translateX(0) rotate(0deg); opacity: 1; } 100% { transform: translateY(120vh) translateX(var(--x-end)) rotate(720deg); opacity: 0; } }
    @keyframes floatUp { 0% { transform: translateY(120vh) translateX(0) rotate(0deg); opacity: 1; } 100% { transform: translateY(-20vh) translateX(var(--x-end)) rotate(var(--rotate-end)); opacity: 0; } }
    @keyframes shimmer { from { background-position: 0% 50%; } to { background-position: 300% 50%; } }
    @keyframes fadeIn { to { opacity: 1; } }

    /* === ADMIN RIWAYAT AKSES === */
.history-box {
    background: rgba(0,0,0,0.25);
    padding: 12px;
    border-radius: 12px;
    max-height: 180px;
    overflow-y: auto;
    text-align: left;
    font-size: 13px;
}

.history-day {
    margin-bottom: 10px;
}

.history-date {
    font-weight: bold;
    margin-bottom: 4px;
    color: #ffe6f0;
}

.history-time {
    padding-left: 12px;
    opacity: 0.9;
}

</style>
</head>
<body>

<audio id="birthday-song" src="songss.mp3" loop preload="auto"></audio>

<?php if (!$login): ?>
    <div class="form-box">
        <h1>🎁 What’s Inside?</h1>
        <form method="post" novalidate>
            <p><input type="text" name="nama" placeholder="Masukkan nama lengkap" required autocomplete="name" /></p>
            <p><input type="date" name="tanggal" required /></p>
            <button type="submit">Unlock Surprise</button>
        </form>
        <?php if (!empty($error)): ?>
            <p class="error-message"><?php echo $error; ?></p>
        <?php endif; ?>
        <p style="font-size:12px; margin-top:15px; color:rgba(255,255,255,0.7)">*Login hanya berhasil jika "<strong>nama dan tanggal lahir</strong>" cocok.</p>
    </div>
<?php else: ?>
    <?php if (
    $_SESSION['role'] === 'admin' ||
    (isset($_SESSION['secret_admin']) && $_SESSION['role'] === 'hadiah')
): ?>

    <div class="form-box">
        <h1>📊 Statistik Akses</h1>
        <p>👁️ Web dibuka oleh Lingga sebanyak:</p>
        <h2>
            <?php echo file_get_contents("counter.txt"); ?> kali
        </h2>
        <p>⏰ Terakhir diakses Lingga:</p>
<h3>
    <?php echo file_get_contents("last_access.txt"); ?>
</h3>
<p>📅 Riwayat Akses:</p>
<div class="history-box">
<?php
if (file_exists("daily_access.txt")) {
    $logs = file("daily_access.txt", FILE_IGNORE_NEW_LINES);
    $group = [];

    foreach ($logs as $log) {
        [$tgl, $jam] = explode(" | ", $log);
        $group[$tgl][] = $jam;
    }

    foreach ($group as $tgl => $jamList) {
        echo "<div class='history-day'>";
        echo "<div class='history-date'>📆 $tgl</div>";
        foreach ($jamList as $j) {
            echo "<div class='history-time'>• $j</div>";
        }
        echo "</div>";
    }
} else {
    echo "-";
}
?>
</div>


        <a href="?exit_admin=1"><button>Kembali ke Hadiah</button></a>
                <a href="?logout=1"><button>Logout</button></a>

    </div>
<?php else: ?>

    <button id="music-toggle">▶</button>
    <div id="gift-section">
        <div id="gift" title="Click to open" tabindex="0">🎁</div>
        <div id="click-gift-text">🎈 Click the Gift! 🎈</div>
    </div>
    <div id="content" class="hidden">
        <div class="cake-container">
            <div class="cake">🎂<span id="flame" title="Make a wish & click!">🔥</span></div><br>
            <p id="instruction-text">Make a wish & click the flame!</p>
        </div>
        <div class="card">
            <h1 class="glow-text" data-text="Happy Birthday, <?php echo htmlspecialchars($_SESSION['nama']); ?>!">Happy Birthday, <?php echo htmlspecialchars($_SESSION['nama']); ?>!</h1>
            <p id="wish-text" class="typewriter"></p>
            <br />
            <a href="?logout=1"><button>🔙 Logout</button></a>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const giftSection = document.getElementById("gift-section");
    const gift = document.getElementById("gift");
    const content = document.getElementById("content");
    const flame = document.getElementById("flame");
    const card = document.querySelector(".card");
    const wishText = document.getElementById("wish-text");
    const cake = document.querySelector(".cake");
    const instructionText = document.getElementById("instruction-text");
    const birthdaySong = document.getElementById("birthday-song");
    const musicToggle = document.getElementById("music-toggle");
    
    const fullMessage = `Wishing you a day filled with joy, laughter, and everything you wished for. 🥳✨ May this year bring you endless happiness, new adventures, and dreams turning into reality. May every little moment today remind you how loved and cherished you are. 🌸💫 Here’s to more smiles, more growth, and beautiful memories waiting ahead. May you continue to shine brighter each day, inspiring everyone around you with your kindness and positive energy. 🌟 Never stop chasing your dreams, because you truly deserve all the wonderful things life has to offer. May your journey ahead be full of surprises that make your heart happy, friendships that last a lifetime, and moments that you’ll treasure forever. Happy Birthday! 🎂🎉`;

    function openGift() {
        gift.classList.add('shake');
        setTimeout(() => {
            giftSection.classList.add("hidden");
            content.classList.remove("hidden");
        }, 500);
    }

    function blowCandle() {
        flame.classList.add('out');
        
        // --- PERUBAHAN JAVASCRIPT ---
        // Kita menambahkan class 'fade-out-final' ke elemen teks.
        // CSS akan menangani animasi fade-out secara otomatis.
        instructionText.classList.add('fade-out-final');
        // --- PERUBAHAN JAVASCRIPT SELESAI ---
        
        if (birthdaySong) {
            birthdaySong.volume = 0.5;
            birthdaySong.play().catch(e => console.error("Gagal memutar musik:", e));
            musicToggle.textContent = '⏸';
        }

        if (cake) cake.style.animation = 'bounceContinuously 2s ease-in-out infinite';

        setTimeout(() => {
            card.classList.add('visible');
            animateTitle();
            typeWriterEffect(fullMessage, wishText, 80);
        }, 500);
    }
    
    function toggleMusic() {
        if (birthdaySong.paused) {
            birthdaySong.play();
            musicToggle.textContent = '⏸';
        } else {
            birthdaySong.pause();
            musicToggle.textContent = '▶';
        }
    }

    if (gift) gift.addEventListener("click", openGift);
    if (flame) flame.addEventListener("click", blowCandle, { once: true });
    if (musicToggle) musicToggle.addEventListener("click", toggleMusic);

    function typeWriterEffect(text, element, speed = 50) {
        let i = 0;
        element.innerHTML = "";
        function typing() {
            if (i < text.length) {
                element.innerHTML += text.charAt(i); i++;
                setTimeout(typing, speed);
            }
        }
        typing();
    }

    function animateTitle() {
        const title = document.querySelector('.glow-text');
        const text = title.dataset.text;
        title.innerHTML = '';
        text.split('').forEach((char, index) => {
            const span = document.createElement('span');
            span.textContent = char === ' ' ? '\u00A0' : char;
            span.style.animationDelay = `${index * 0.05}s`;
            title.appendChild(span);
        });
    }
    
    const particleContainer = document.body;
    const confettiColors = ['#f94144','#f3722c','#f8961e','#f9c74f','#90be6d','#43aa8b','#577590'];
    function createParticle(type) {
        const el = document.createElement('div');
        el.className = 'particle';
        const xStart = Math.random() * 100;
        el.style.left = `${xStart}vw`;
        el.style.setProperty('--x-end', `${(Math.random() - 0.5) * 50}vw`);
        const animationDelay = Math.random() * 10;
        el.style.animationDelay = `${animationDelay}s`;
        
        if (type === 'confetti') {
            el.classList.add('confetti');
            el.style.backgroundColor = confettiColors[Math.floor(Math.random() * confettiColors.length)];
            const size = Math.random() * 8 + 6;
            el.style.width = `${size}px`; el.style.height = `${size}px`;
            el.style.borderRadius = Math.random() > 0.5 ? '50%' : '0';
        } else if (type === 'balloon') {
            el.classList.add('balloon');
            el.textContent = '🎈';
            el.style.fontSize = `${(Math.random() * 30 + 30)}px`;
            el.style.setProperty('--rotate-end', `${(Math.random() - 0.5) * 90}deg`);
        }
        particleContainer.appendChild(el);
        setTimeout(() => el.remove(), 10000 + animationDelay * 1000);
    }

    for (let i = 0; i < 50; i++) createParticle('confetti');
    for (let i = 0; i < 5; i++) createParticle('balloon');
    setInterval(() => createParticle('confetti'), 300);
    setInterval(() => createParticle('balloon'), 2000);
});
// ===== ADMIN MODE RAHASIA =====
let secretCount = 0;
let secretTimer = null;

const cake = document.querySelector('.cake'); // elemen rahasia 🎂

if (cake) {
    cake.addEventListener('click', () => {
        secretCount++;

        if (secretCount === 1) {
            secretTimer = setTimeout(() => {
                secretCount = 0;
            }, 3000); // harus cepat (3 detik)
        }

        if (secretCount >= 5) { // 5x klik
            window.location.href = "?secret_admin=1";
        }
    });
}

</script>
<?php endif; ?>
<?php endif; ?>
</body>
</html>