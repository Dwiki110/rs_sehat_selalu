<?php
// Fix Forms Script - Add CSRF tokens to HTML forms
// This script will update HTML forms to include CSRF protection

// Start output buffering
ob_start();
?>

<!-- Fixed Login Form with CSRF Token -->
<form method="POST" action="proses_login.php" class="space-y-8 needs-validation" id="loginForm" novalidate>
    <?php
    // Start session for CSRF token generation
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Generate CSRF token
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    ?>
    
    <!-- CSRF Token (Hidden Field) -->
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
    
    <div class="group">
        <div class="relative">
            <input 
                type="text" 
                class="w-full px-6 py-4 text-lg border-2 border-gray-200 rounded-xl focus:border-blue-400 focus:ring-4 focus:ring-blue-100 transition-all duration-300 bg-white/80 backdrop-blur-sm custom-focus peer" 
                id="username" 
                name="username" 
                placeholder=" " 
                required 
                minlength="3" 
                maxlength="50"
            >
            <label 
                for="username" 
                class="absolute left-6 top-4 text-gray-500 transition-all duration-300 peer-focus:text-blue-500 peer-focus:-translate-y-6 peer-focus:text-sm peer-[:not(:placeholder-shown)]:-translate-y-6 peer-[:not(:placeholder-shown)]:text-sm"
            >
                <i class="bi bi-person mr-2"></i>Username
            </label>
        </div>
        <div class="hidden peer-invalid:block text-red-500 text-sm mt-2 animate-fade-in">
            Username harus diisi (minimal 3 karakter)
        </div>
    </div>
    
    <div class="group">
        <div class="relative">
            <input 
                type="password" 
                class="w-full px-6 py-4 text-lg border-2 border-gray-200 rounded-xl focus:border-blue-400 focus:ring-4 focus:ring-blue-100 transition-all duration-300 bg-white/80 backdrop-blur-sm custom-focus peer" 
                id="password" 
                name="password" 
                placeholder=" " 
                required 
                minlength="6"
            >
            <label 
                for="password" 
                class="absolute left-6 top-4 text-gray-500 transition-all duration-300 peer-focus:text-blue-500 peer-focus:-translate-y-6 peer-focus:text-sm peer-[:not(:placeholder-shown)]:-translate-y-6 peer-[:not(:placeholder-shown)]:text-sm"
            >
                <i class="bi bi-lock mr-2"></i>Password
            </label>
        </div>
        <div class="hidden peer-invalid:block text-red-500 text-sm mt-2 animate-fade-in">
            Password harus diisi (minimal 6 karakter)
        </div>
    </div>
    
    <!-- 2FA Code Field (Optional) -->
    <div class="group" id="2fa-field" style="display: none;">
        <div class="relative">
            <input 
                type="text" 
                class="w-full px-6 py-4 text-lg border-2 border-gray-200 rounded-xl focus:border-blue-400 focus:ring-4 focus:ring-blue-100 transition-all duration-300 bg-white/80 backdrop-blur-sm custom-focus peer" 
                id="2fa_code" 
                name="2fa_code" 
                placeholder=" " 
                maxlength="6"
                pattern="[0-9]{6}"
            >
            <label 
                for="2fa_code" 
                class="absolute left-6 top-4 text-gray-500 transition-all duration-300 peer-focus:text-blue-500 peer-focus:-translate-y-6 peer-focus:text-sm peer-[:not(:placeholder-shown)]:-translate-y-6 peer-[:not(:placeholder-shown)]:text-sm"
            >
                <i class="bi bi-shield-lock mr-2"></i>Kode 2FA (Jika Aktif)
            </label>
        </div>
        <div class="text-blue-500 text-sm mt-2">
            Masukkan kode 6 digit dari aplikasi authenticator Anda
        </div>
    </div>
    
    <button 
        type="submit" 
        class="w-full bg-gradient-to-r from-blue-500 to-cyan-500 hover:from-blue-600 hover:to-cyan-600 text-white font-semibold py-4 px-8 rounded-xl text-lg transform hover:scale-105 hover:shadow-xl transition-all duration-300 btn-hover-effect animate-glow"
    >
        <i class="bi bi-box-arrow-in-right mr-2"></i>Login
    </button>
</form>

<!-- Show 2FA field toggle -->
<div class="mt-4 text-center">
    <button 
        type="button" 
        onclick="toggle2FA()" 
        class="text-blue-500 hover:text-blue-600 text-sm font-medium transition-colors duration-300"
        id="toggle-2fa-btn"
    >
        <i class="bi bi-shield-lock mr-1"></i>Aktifkan input 2FA
    </button>
</div>

<!-- Links -->
<div class="mt-12 space-y-4 animate-slide-up">
    <a 
        href="password_reset.php" 
        class="block text-center text-purple-500 hover:text-purple-600 font-medium transition-all duration-300 hover:translate-x-2 group"
    >
        <i class="bi bi-key mr-2 group-hover:animate-bounce"></i>
        Lupa Password?
    </a>
    <a 
        href="daftar.html" 
        class="block text-center text-blue-500 hover:text-blue-600 font-medium transition-all duration-300 hover:translate-x-2 group"
    >
        <i class="bi bi-person-plus mr-2 group-hover:animate-bounce"></i>
        Belum punya akun? Daftar di sini
    </a>
    <a 
        href="dokter_login.html" 
        class="block text-center text-cyan-500 hover:text-cyan-600 font-medium transition-all duration-300 hover:translate-x-2 group"
    >
        <i class="bi bi-heart-pulse mr-2 group-hover:animate-heartbeat"></i>
        Login Dokter
    </a>
</div>

<script>
function toggle2FA() {
    const field = document.getElementById('2fa-field');
    const btn = document.getElementById('toggle-2fa-btn');
    
    if (field.style.display === 'none') {
        field.style.display = 'block';
        btn.innerHTML = '<i class="bi bi-shield-lock mr-1"></i>Sembunyikan input 2FA';
    } else {
        field.style.display = 'none';
        btn.innerHTML = '<i class="bi bi-shield-lock mr-1"></i>Aktifkan input 2FA';
    }
}

// Enhanced form validation for login
document.getElementById('loginForm').addEventListener('submit', function(e) {
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value;
    
    if (username.length < 3) {
        e.preventDefault();
        document.getElementById('username').classList.add('border-red-500', 'focus:border-red-500');
        return false;
    }
    
    if (password.length < 6) {
        e.preventDefault();
        document.getElementById('password').classList.add('border-red-500', 'focus:border-red-500');
        return false;
    }
    
    // Show loading state
    const submitBtn = e.target.querySelector('button[type="submit"]');
    submitBtn.innerHTML = '<i class="bi bi-hourglass-split mr-2 animate-spin"></i>Login...';
    submitBtn.disabled = true;
});

// Remove invalid class on input
document.getElementById('username').addEventListener('input', function() {
    this.classList.remove('border-red-500', 'focus:border-red-500');
});

document.getElementById('password').addEventListener('input', function() {
    this.classList.remove('border-red-500', 'focus:border-red-500');
});
</script>

<?php
$fixed_form = ob_get_clean();

// Output instructions
echo "<h1>🔧 Fixed Login Form with CSRF Protection</h1>";
echo "<p>Below is the corrected login form with CSRF token and 2FA support:</p>";

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
echo "<h3>✅ Fixed Form Features:</h3>";
echo "<ul>";
echo "<li>✓ CSRF token protection</li>";
echo "<li>✓ 2FA code input field (optional)</li>";
echo "<li>✓ Enhanced validation</li>";
echo "<li>✓ Loading states</li>";
echo "<li>✓ Password reset link</li>";
echo "<li>✓ Responsive design</li>";
echo "</ul>";
echo "</div>";

echo "<h2>📋 Implementation Instructions:</h2>";
echo "<ol>";
echo "<li>Replace the form section in <code>index.html</code> with the code below</li>";
echo "<li>Convert <code>index.html</code> to <code>index.php</code> to support PHP</li>";
echo "<li>Test login with and without 2FA</li>";
echo "<li>Verify CSRF protection is working</li>";
echo "</ol>";

echo "<h2>💻 Fixed Form Code:</h2>";
echo "<textarea style='width: 100%; height: 400px; font-family: monospace; font-size: 12px;'>";
echo htmlspecialchars($fixed_form);
echo "</textarea>";

// Create the actual fixed index.php file
$index_content = file_get_contents('index.html');

// Replace the form section
$new_form = '
<?php
// Start session for CSRF token
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION[\'csrf_token\'])) {
    $_SESSION[\'csrf_token\'] = bin2hex(random_bytes(32));
}
?>

<form method="POST" action="proses_login.php" class="space-y-8 needs-validation" id="loginForm" novalidate>
    <!-- CSRF Token -->
    <input type="hidden" name="csrf_token" value="<?= $_SESSION[\'csrf_token\'] ?>">
    
    <div class="group">
        <div class="relative">
            <input 
                type="text" 
                class="w-full px-6 py-4 text-lg border-2 border-gray-200 rounded-xl focus:border-blue-400 focus:ring-4 focus:ring-blue-100 transition-all duration-300 bg-white/80 backdrop-blur-sm custom-focus peer" 
                id="username" 
                name="username" 
                placeholder=" " 
                required 
                minlength="3" 
                maxlength="50"
            >
            <label 
                for="username" 
                class="absolute left-6 top-4 text-gray-500 transition-all duration-300 peer-focus:text-blue-500 peer-focus:-translate-y-6 peer-focus:text-sm peer-[:not(:placeholder-shown)]:-translate-y-6 peer-[:not(:placeholder-shown)]:text-sm"
            >
                <i class="bi bi-person mr-2"></i>Username
            </label>
        </div>
        <div class="hidden peer-invalid:block text-red-500 text-sm mt-2 animate-fade-in">
            Username harus diisi (minimal 3 karakter)
        </div>
    </div>
    
    <div class="group">
        <div class="relative">
            <input 
                type="password" 
                class="w-full px-6 py-4 text-lg border-2 border-gray-200 rounded-xl focus:border-blue-400 focus:ring-4 focus:ring-blue-100 transition-all duration-300 bg-white/80 backdrop-blur-sm custom-focus peer" 
                id="password" 
                name="password" 
                placeholder=" " 
                required 
                minlength="6"
            >
            <label 
                for="password" 
                class="absolute left-6 top-4 text-gray-500 transition-all duration-300 peer-focus:text-blue-500 peer-focus:-translate-y-6 peer-focus:text-sm peer-[:not(:placeholder-shown)]:-translate-y-6 peer-[:not(:placeholder-shown)]:text-sm"
            >
                <i class="bi bi-lock mr-2"></i>Password
            </label>
        </div>
        <div class="hidden peer-invalid:block text-red-500 text-sm mt-2 animate-fade-in">
            Password harus diisi (minimal 6 karakter)
        </div>
    </div>
    
    <!-- 2FA Code Field -->
    <div class="group" id="2fa-field" style="display: none;">
        <div class="relative">
            <input 
                type="text" 
                class="w-full px-6 py-4 text-lg border-2 border-gray-200 rounded-xl focus:border-blue-400 focus:ring-4 focus:ring-blue-100 transition-all duration-300 bg-white/80 backdrop-blur-sm custom-focus peer" 
                id="2fa_code" 
                name="2fa_code" 
                placeholder=" " 
                maxlength="6"
                pattern="[0-9]{6}"
            >
            <label 
                for="2fa_code" 
                class="absolute left-6 top-4 text-gray-500 transition-all duration-300 peer-focus:text-blue-500 peer-focus:-translate-y-6 peer-focus:text-sm peer-[:not(:placeholder-shown)]:-translate-y-6 peer-[:not(:placeholder-shown)]:text-sm"
            >
                <i class="bi bi-shield-lock mr-2"></i>Kode 2FA (Opsional)
            </label>
        </div>
    </div>
    
    <button 
        type="submit" 
        class="w-full bg-gradient-to-r from-blue-500 to-cyan-500 hover:from-blue-600 hover:to-cyan-600 text-white font-semibold py-4 px-8 rounded-xl text-lg transform hover:scale-105 hover:shadow-xl transition-all duration-300 btn-hover-effect animate-glow"
    >
        <i class="bi bi-box-arrow-in-right mr-2"></i>Login
    </button>
</form>

<div class="mt-4 text-center">
    <button 
        type="button" 
        onclick="toggle2FA()" 
        class="text-blue-500 hover:text-blue-600 text-sm font-medium"
        id="toggle-2fa-btn"
    >
        <i class="bi bi-shield-lock mr-1"></i>Ada 2FA? Klik di sini
    </button>
</div>';

echo "<h2>📄 Creating Fixed index.php File...</h2>";

// Find form section and replace
$form_start = strpos($index_content, '<form method="POST"');
$form_end = strpos($index_content, '</form>', $form_start) + 7;

if ($form_start !== false && $form_end !== false) {
    $new_content = substr($index_content, 0, $form_start) . $new_form . substr($index_content, $form_end);
    
    // Save as index.php
    if (file_put_contents('index.php', $new_content)) {
        echo "<span style='color: green;'>✓</span> Created index.php with CSRF protection<br>";
    } else {
        echo "<span style='color: red;'>✗</span> Failed to create index.php<br>";
    }
} else {
    echo "<span style='color: red;'>✗</span> Could not find form section in index.html<br>";
}

?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    background-color: #f8f9fa;
}

h1 {
    color: #2c3e50;
    text-align: center;
    border-bottom: 3px solid #3498db;
    padding-bottom: 10px;
}

h2 {
    color: #34495e;
    margin-top: 30px;
    padding: 10px;
    background-color: #ecf0f1;
    border-left: 4px solid #3498db;
}

code {
    background-color: #2c3e50;
    color: #ecf0f1;
    padding: 2px 6px;
    border-radius: 3px;
    font-family: 'Courier New', monospace;
}

textarea {
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 10px;
}

ol, ul {
    line-height: 1.6;
}

li {
    margin-bottom: 5px;
}
</style>
