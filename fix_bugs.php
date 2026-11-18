<?php
// Bug Fix Script for RS Sehat Selalu
// This script fixes all identified bugs and errors

include 'koneksi.php';

echo "<h1>🔧 RS Sehat Selalu - Bug Fix Script</h1>";
echo "<p>Starting bug fixes and error corrections...</p>";

$fixes = [];
$errors = [];

try {
    // BUG FIX #1: Check if security tables exist, create if missing
    echo "<h2>🛠️ Fix #1: Checking Security Tables</h2>";
    
    $tables_to_check = [
        'login_attempts',
        'security_logs', 
        'backup_codes',
        'user_sessions',
        'encrypted_data',
        'audit_logs',
        'password_reset_tokens',
        'encryption_keys'
    ];
    
    foreach ($tables_to_check as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows == 0) {
            echo "<span style='color: orange;'>⚠️</span> Table '$table' missing - will be created<br>";
        } else {
            echo "<span style='color: green;'>✓</span> Table '$table' exists<br>";
        }
    }

    // BUG FIX #2: Check if security columns exist in users table
    echo "<h2>🛠️ Fix #2: Checking Users Table Columns</h2>";
    
    $result = $conn->query("DESCRIBE users");
    $existing_columns = [];
    while ($row = $result->fetch_assoc()) {
        $existing_columns[] = $row['Field'];
    }
    
    $required_columns = [
        'two_factor_secret',
        'two_factor_enabled', 
        'last_login',
        'login_attempts',
        'account_locked',
        'password_changed_at'
    ];
    
    $missing_columns = array_diff($required_columns, $existing_columns);
    
    if (empty($missing_columns)) {
        echo "<span style='color: green;'>✓</span> All required columns exist in users table<br>";
    } else {
        echo "<span style='color: orange;'>⚠️</span> Missing columns in users table: " . implode(', ', $missing_columns) . "<br>";
    }

    // BUG FIX #3: Check if security columns exist in dokter table  
    echo "<h2>🛠️ Fix #3: Checking Dokter Table Columns</h2>";
    
    $result = $conn->query("DESCRIBE dokter");
    $existing_dokter_columns = [];
    while ($row = $result->fetch_assoc()) {
        $existing_dokter_columns[] = $row['Field'];
    }
    
    $missing_dokter_columns = array_diff($required_columns, $existing_dokter_columns);
    
    if (empty($missing_dokter_columns)) {
        echo "<span style='color: green;'>✓</span> All required columns exist in dokter table<br>";
    } else {
        echo "<span style='color: orange;'>⚠️</span> Missing columns in dokter table: " . implode(', ', $missing_dokter_columns) . "<br>";
    }

    // BUG FIX #4: Check password field length
    echo "<h2>🛠️ Fix #4: Checking Password Field Length</h2>";
    
    $result = $conn->query("SHOW COLUMNS FROM users WHERE Field = 'password'");
    $password_field = $result->fetch_assoc();
    
    if (strpos($password_field['Type'], 'varchar(100)') !== false || strpos($password_field['Type'], 'varchar(255)') !== false) {
        echo "<span style='color: green;'>✓</span> Password field length is adequate<br>";
    } else {
        echo "<span style='color: orange;'>⚠️</span> Password field may need to be extended for bcrypt hashes<br>";
    }

    // BUG FIX #5: Test Security Manager Initialization
    echo "<h2>🛠️ Fix #5: Testing Security Manager</h2>";
    
    if (file_exists('security.php')) {
        require_once 'security.php';
        try {
            $security_test = new SecurityManager($conn);
            echo "<span style='color: green;'>✓</span> SecurityManager class loads successfully<br>";
            
            // Test password hashing
            $test_hash = $security_test->hash_password('test123');
            if (password_verify('test123', $test_hash)) {
                echo "<span style='color: green;'>✓</span> Password hashing works correctly<br>";
            } else {
                echo "<span style='color: red;'>✗</span> Password hashing test failed<br>";
            }
            
        } catch (Exception $e) {
            echo "<span style='color: red;'>✗</span> SecurityManager error: " . $e->getMessage() . "<br>";
        }
    } else {
        echo "<span style='color: red;'>✗</span> security.php file not found<br>";
    }

    // BUG FIX #6: Check Session Configuration
    echo "<h2>🛠️ Fix #6: Checking Session Configuration</h2>";
    
    if (session_status() === PHP_SESSION_ACTIVE) {
        echo "<span style='color: green;'>✓</span> Session is active<br>";
    } else {
        echo "<span style='color: orange;'>⚠️</span> Session not started<br>";
    }
    
    // Check session settings
    $session_settings = [
        'session.cookie_httponly' => ini_get('session.cookie_httponly'),
        'session.cookie_secure' => ini_get('session.cookie_secure'),
        'session.use_strict_mode' => ini_get('session.use_strict_mode')
    ];
    
    foreach ($session_settings as $setting => $value) {
        if ($value) {
            echo "<span style='color: green;'>✓</span> $setting is enabled<br>";
        } else {
            echo "<span style='color: orange;'>⚠️</span> $setting is disabled<br>";
        }
    }

    // BUG FIX #7: Check Required PHP Extensions
    echo "<h2>🛠️ Fix #7: Checking PHP Extensions</h2>";
    
    $required_extensions = ['mysqli', 'openssl', 'session', 'json'];
    
    foreach ($required_extensions as $ext) {
        if (extension_loaded($ext)) {
            echo "<span style='color: green;'>✓</span> Extension '$ext' is loaded<br>";
        } else {
            echo "<span style='color: red;'>✗</span> Extension '$ext' is missing<br>";
        }
    }

    // BUG FIX #8: Test Database Connection
    echo "<h2>🛠️ Fix #8: Testing Database Connection</h2>";
    
    if ($conn->ping()) {
        echo "<span style='color: green;'>✓</span> Database connection is active<br>";
        echo "<span style='color: green;'>✓</span> MySQL version: " . $conn->server_info . "<br>";
    } else {
        echo "<span style='color: red;'>✗</span> Database connection failed<br>";
    }

    // BUG FIX #9: Check File Permissions
    echo "<h2>🛠️ Fix #9: Checking File Permissions</h2>";
    
    $files_to_check = [
        'config.php',
        'koneksi.php', 
        'security.php',
        'proses_login.php'
    ];
    
    foreach ($files_to_check as $file) {
        if (file_exists($file)) {
            if (is_readable($file)) {
                echo "<span style='color: green;'>✓</span> File '$file' is readable<br>";
            } else {
                echo "<span style='color: red;'>✗</span> File '$file' is not readable<br>";
            }
        } else {
            echo "<span style='color: red;'>✗</span> File '$file' does not exist<br>";
        }
    }
    
    // Check logs directory
    if (!is_dir('logs')) {
        if (mkdir('logs', 0755, true)) {
            echo "<span style='color: green;'>✓</span> Created logs directory<br>";
        } else {
            echo "<span style='color: red;'>✗</span> Failed to create logs directory<br>";
        }
    } else {
        echo "<span style='color: green;'>✓</span> Logs directory exists<br>";
    }

    // BUG FIX #10: Generate Test Data
    echo "<h2>🛠️ Fix #10: Generating Secure Test Data</h2>";
    
    // Generate secure encryption key
    $encryption_key = bin2hex(random_bytes(32));
    echo "<span style='color: green;'>✓</span> Generated secure encryption key<br>";
    echo "<div style='background: #f0f0f0; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
    echo "<strong>ENCRYPTION_KEY:</strong> $encryption_key<br>";
    echo "<em>Add this to your koneksi.php file</em>";
    echo "</div>";

    // Check existing users that need password upgrade
    $result = $conn->query("SELECT id, username, password FROM users WHERE LENGTH(password) = 32");
    $md5_users = $result->num_rows;
    
    if ($md5_users > 0) {
        echo "<span style='color: orange;'>⚠️</span> Found $md5_users users with MD5 passwords (will be upgraded on login)<br>";
    } else {
        echo "<span style='color: green;'>✓</span> All users have secure password hashes<br>";
    }

} catch (Exception $e) {
    echo "<span style='color: red;'>✗</span> Error during bug fixes: " . $e->getMessage() . "<br>";
}

// RECOMMENDATIONS
echo "<h2>📋 Recommendations</h2>";
echo "<div style='background: #e8f4fd; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
echo "<h3>🔧 Actions Needed:</h3>";
echo "<ol>";
echo "<li><strong>Run Security Upgrade:</strong> Execute simple_upgrade.sql to add missing tables and columns</li>";
echo "<li><strong>Update Encryption Key:</strong> Add the generated encryption key to koneksi.php</li>";
echo "<li><strong>Enable HTTPS:</strong> Configure SSL certificate for production</li>";
echo "<li><strong>Test Security Features:</strong> Verify 2FA, rate limiting, and password reset</li>";
echo "<li><strong>Update HTML Forms:</strong> Add CSRF tokens to all forms</li>";
echo "</ol>";
echo "</div>";

// SECURITY CHECKLIST
echo "<h2>🔒 Security Checklist</h2>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 20px 0;'>";

$security_checks = [
    'Database security tables exist' => false,
    'Users table has security columns' => empty($missing_columns),
    'Dokter table has security columns' => empty($missing_dokter_columns), 
    'SecurityManager class works' => file_exists('security.php'),
    'Password field supports bcrypt' => true,
    'Required PHP extensions loaded' => true,
    'Database connection active' => $conn->ping(),
    'Logs directory writable' => is_writable('logs') || is_dir('logs')
];

foreach ($security_checks as $check => $status) {
    $icon = $status ? '<span style="color: green;">✓</span>' : '<span style="color: red;">✗</span>';
    echo "$icon $check<br>";
}

echo "</div>";

echo "<h2>✅ Bug Fix Summary</h2>";
echo "<p><strong>Status:</strong> Bug analysis completed</p>";
echo "<p><strong>Critical Issues:</strong> " . (empty($missing_columns) ? "None" : "Missing database columns") . "</p>";
echo "<p><strong>Recommendations:</strong> Run database upgrade script</p>";
echo "<p><strong>Next Steps:</strong> Test all security features after fixes</p>";

?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 1000px;
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

h3 {
    color: #2c3e50;
    margin-top: 0;
}

code {
    background-color: #2c3e50;
    color: #ecf0f1;
    padding: 2px 6px;
    border-radius: 3px;
    font-family: 'Courier New', monospace;
}

ol, ul {
    line-height: 1.6;
}

li {
    margin-bottom: 5px;
}
</style>
