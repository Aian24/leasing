<?php
date_default_timezone_set('Asia/Manila');
require_once 'database/config.php';
require_once 'database/email_config.php';
require_once 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

// If already logged in, redirect based on role
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'Admin') {
        header('Location: admin/overview/dashboard.php');
    } else {
        header('Location: user/index.php');
    }
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    $action = $_POST['action'] ?? 'login';

    if ($action === 'login') {
        if ($username && $password) {
            $pdo = getPDO();
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                if ($remember) {
                    $params = session_get_cookie_params();
                    session_set_cookie_params(30 * 24 * 60 * 60, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
                    session_regenerate_id(true);
                }
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['name'] = $user['name'];

                // Update last login details
                $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW(), last_login_ip = ? WHERE id = ?");
                $updateStmt->execute([$_SERVER['REMOTE_ADDR'], $user['id']]);

                // Create session record for dashboard tracking
                $sessionStmt = $pdo->prepare("INSERT INTO user_sessions (user_id, session_token, ip_address, user_agent, expires_at) VALUES (?, ?, ?, ?, ?)");
                $sessionToken = bin2hex(random_bytes(32));
                $expiresAt = date('Y-m-d H:i:s', strtotime($remember ? '+30 days' : '+24 hours'));
                $sessionStmt->execute([$user['id'], $sessionToken, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'] ?? '', $expiresAt]);

                // Log successful login
                $_SESSION['username'] = $user['username']; // Ensure username is available for logging
                logAction('Successful Login', "User '{$user['username']}' logged in successfully.", 'success');

                header('Location: ' . ($user['role'] === 'Admin' ? 'admin/overview/dashboard.php' : 'user/index.php'));
                exit;
            } else {
                // Log failed attempt using a temporary fake session variable just for tracking this context
                if ($user) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    logAction('Failed Login', "Incorrect password attempt for user '{$user['username']}'.", 'warning');
                    session_unset();
                } else {
                    $_SESSION['username'] = htmlspecialchars($username);
                    logAction('Failed Login', "Attempted login with unknown username '{$username}'.", 'warning');
                    session_unset();
                }

                $error = 'Invalid username or password.';
            }
        } else {
            $error = 'Please fill in all fields.';
        }
    } 
    // Handle Forgot Password OTP Request
    elseif ($action === 'forgot_request') {
        $email = $_POST['email'] ?? '';
        $pdo = getPDO();
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));
            $stmt = $pdo->prepare("UPDATE users SET reset_otp = ?, reset_expires = ? WHERE id = ?");
            $stmt->execute([$otp, $expires, $user['id']]);

            // Check if SMTP is configured
            if (SMTP_USER === 'your-email@gmail.com') {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'System in Demo Mode. Connect SMTP in database/email_config.php to receive real emails.']);
                exit;
            }

            $mail = new PHPMailer(true);
            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host       = SMTP_HOST;
                $mail->SMTPAuth   = true;
                $mail->Username   = SMTP_USER;
                $mail->Password   = SMTP_PASS;
                $mail->SMTPSecure = SMTP_SECURE;
                $mail->Port       = SMTP_PORT;

                // Recipients
                $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
                $mail->addAddress($email);

                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Your LeasePro Security Code';
                $mail->Body    = "
                    <div style='font-family: Arial, sans-serif; padding: 20px; color: #1e293b; background: #f8fafc; border-radius: 12px;'>
                        <div style='background: #fff; padding: 24px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);'>
                            <h2 style='color: #2563eb; margin-top: 0;'>LeasePro Verification Code</h2>
                            <p style='font-size: 16px; line-height: 1.5;'>You requested to reset your password. Use the following 6-digit code to proceed:</p>
                            <div style='font-size: 36px; font-weight: 800; letter-spacing: 8px; color: #1e1b4b; background: #eff6ff; padding: 20px; text-align: center; border-radius: 12px; margin: 24px 0; border: 1px dashed #3b82f6;'>
                                $otp
                            </div>
                            <p style='font-size: 14px; color: #64748b;'>This code expires in <b>10 minutes</b>. If you didn't request this, please secure your account.</p>
                        </div>
                        <p style='text-align: center; font-size: 12px; color: #94a3b8; margin-top: 16px;'>&copy; 2026 LeasePro Security Service</p>
                    </div>";

                $mail->send();
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'OTP sent to your email!']);
                exit;
            } catch (Exception $e) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => "Mailer Error: {$mail->ErrorInfo}"]);
                exit;
            }
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Email address not found.']);
            exit;
        }
    }
    // Handle OTP Verification and Password Reset
    elseif ($action === 'reset_password') {
        $email = $_POST['email'] ?? '';
        $otp = $_POST['otp'] ?? '';
        $new_pass = $_POST['password'] ?? '';
        
        $now = date('Y-m-d H:i:s');
        $pdo = getPDO();
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND reset_otp = ? AND reset_expires > ?");
        $stmt->execute([$email, $otp, $now]);
        $user = $stmt->fetch();

        if ($user) {
            $hash = password_hash($new_pass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password_hash = ?, reset_otp = NULL, reset_expires = NULL WHERE id = ?");
            $stmt->execute([$hash, $user['id']]);
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Password reset successful!']);
            exit;
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid or expired OTP.']);
            exit;
        }
    }
}
?>
<?php
$pdo = getPDO();
$settings_stmt = $pdo->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('app_name', 'app_tagline', 'maintenance_mode')");
$app_settings = [];
while ($row = $settings_stmt->fetch(PDO::FETCH_ASSOC)) {
    $app_settings[$row['setting_key']] = $row['setting_value'];
}
$appName = $app_settings['app_name'] ?? 'LeasePro';
$appTagline = $app_settings['app_tagline'] ?? 'Lease Management System';
$isMaintenance = ($app_settings['maintenance_mode'] ?? 'false') === 'true';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($appName); ?> — Sign In</title>
    <meta name="description"
        content="Sign in to <?php echo htmlspecialchars($appName); ?> <?php echo htmlspecialchars($appTagline); ?> to manage contracts, lessees, and stalls.">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Local CSS -->
    <link rel="stylesheet" href="css/style.css">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                        },
                        dark: '#0f172a',
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600&display=swap');

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Outfit', sans-serif;
            min-height: 100vh;
        }

        /* ── Animated mesh background ── */
        .login-bg {
            position: fixed;
            inset: 0;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%);
            z-index: 0;
        }

        .mesh-blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(90px);
            opacity: 0.35;
            animation: blobFloat 8s ease-in-out infinite alternate;
        }

        .blob-1 {
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, #3b82f6, #2563eb);
            top: -200px;
            left: -150px;
            animation-delay: 0s;
        }

        .blob-2 {
            width: 450px;
            height: 450px;
            background: radial-gradient(circle, #2563eb, #ec4899);
            bottom: -150px;
            right: -100px;
            animation-delay: -3s;
        }

        .blob-3 {
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, #38bdf8, #3b82f6);
            top: 50%;
            left: 60%;
            animation-delay: -5s;
        }

        @keyframes blobFloat {
            0% {
                transform: translate(0, 0) scale(1);
            }

            100% {
                transform: translate(30px, 40px) scale(1.08);
            }
        }

        /* ── Grid pattern overlay ── */
        .grid-overlay {
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(59, 130, 246, 0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(59, 130, 246, 0.04) 1px, transparent 1px);
            background-size: 50px 50px;
            z-index: 0;
        }

        /* ── Login card ── */
        .login-card {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(59, 130, 246, 0.2);
            box-shadow:
                0 32px 80px rgba(0, 0, 0, 0.5),
                0 0 0 1px rgba(255, 255, 255, 0.04) inset;
            border-radius: 1.75rem;
            animation: cardReveal 0.7s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
        }

        @keyframes cardReveal {
            0% {
                opacity: 0;
                transform: translateY(32px) scale(0.97);
            }

            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* ── Logo badge ── */
        .logo-badge {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            box-shadow: 0 8px 32px rgba(59, 130, 246, 0.5);
        }

        /* ── Input fields ── */
        .login-input {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(59, 130, 246, 0.2);
            color: #e2e8f0;
            border-radius: 0.875rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-family: 'Inter', sans-serif;
            width: 100%;
            padding: 0.875rem 1rem 0.875rem 3rem;
            font-size: 0.9375rem;
            outline: none;
        }

        .login-input::placeholder {
            color: rgba(148, 163, 184, 0.6);
        }

        .login-input:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(59, 130, 246, 0.4);
        }

        .login-input:focus {
            background: rgba(15, 23, 42, 0.8);
            border-color: #3b82f6;
            box-shadow:
                0 0 0 4px rgba(59, 130, 246, 0.15),
                0 0 20px rgba(59, 130, 246, 0.1);
        }

        /* ── Login button ── */
        .login-btn {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow:
                0 8px 32px rgba(59, 130, 246, 0.4),
                0 0 0 1px rgba(255, 255, 255, 0.05) inset;
            color: #fff;
            border-radius: 0.875rem;
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            font-size: 1rem;
            letter-spacing: 0.025em;
            padding: 0.9rem 1.5rem;
            width: 100%;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .login-btn::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.15), transparent);
            opacity: 0;
            transition: opacity 0.3s;
        }

        .login-btn:hover::before {
            opacity: 1;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow:
                0 16px 48px rgba(59, 130, 246, 0.5),
                0 0 0 1px rgba(255, 255, 255, 0.1) inset;
        }

        .login-btn:active {
            transform: translateY(0);
        }

        /* ── Loading spinner ── */
        .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* ── Error message ── */
        .error-msg {
            background: rgba(239, 68, 68, 0.12);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
            display: none;
            align-items: center;
            gap: 0.5rem;
            animation: shakeX 0.4s ease;
        }

        @keyframes shakeX {

            0%,
            100% {
                transform: translateX(0);
            }

            20%,
            60% {
                transform: translateX(-6px);
            }

            40%,
            80% {
                transform: translateX(6px);
            }
        }

        /* ── Show password toggle ── */
        .toggle-pw {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: rgba(148, 163, 184, 0.6);
            transition: color 0.2s;
            background: none;
            border: none;
            padding: 0;
        }

        .toggle-pw:hover {
            color: #a5b4fc;
        }

        /* ── Input icon ── */
        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(59, 130, 246, 0.6);
            pointer-events: none;
            font-size: 0.9rem;
        }

        /* ── Decorative divider ── */
        .divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.3), transparent);
        }

        /* ── Floating label animation ── */
        .input-group {
            position: relative;
        }

        /* ── Shimmer on brand ── */
        @keyframes shimmer {
            0% {
                background-position: -200% center;
            }

            100% {
                background-position: 200% center;
            }
        }

        .brand-shimmer {
            background: linear-gradient(90deg, #e2e8f0 0%, #fff 40%, #a5b4fc 60%, #e2e8f0 100%);
            background-size: 200% auto;
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: shimmer 4s linear infinite;
        }

        /* ── Particles (pure CSS dots) ── */
        .particle {
            position: fixed;
            border-radius: 50%;
            background: rgba(59, 130, 246, 0.4);
            animation: particleDrift linear infinite;
        }

        @keyframes particleDrift {
            0% {
                transform: translateY(100vh) rotate(0deg);
                opacity: 0;
            }

            10% {
                opacity: 1;
            }

            90% {
                opacity: 0.5;
            }

            100% {
                transform: translateY(-100px) rotate(720deg);
                opacity: 0;
            }
        }
    </style>
</head>

<body class="relative bg-slate-950 text-slate-200 antialiased min-h-screen overflow-x-hidden">

    <!-- Background layers -->
    <div class="login-bg"></div>
    <div class="grid-overlay"></div>
    <div class="mesh-blob blob-1"></div>
    <div class="mesh-blob blob-2"></div>
    <div class="mesh-blob blob-3"></div>

    <!-- Floating particles -->
    <div class="particle" style="width:4px;height:4px;left:15%;animation-duration:12s;animation-delay:0s;"></div>
    <div class="particle" style="width:6px;height:6px;left:30%;animation-duration:16s;animation-delay:-4s;"></div>
    <div class="particle" style="width:3px;height:3px;left:55%;animation-duration:10s;animation-delay:-7s;"></div>
    <div class="particle" style="width:5px;height:5px;left:70%;animation-duration:14s;animation-delay:-2s;"></div>
    <div class="particle" style="width:4px;height:4px;left:85%;animation-duration:18s;animation-delay:-9s;"></div>
    <div class="particle" style="width:3px;height:3px;left:42%;animation-duration:11s;animation-delay:-5s;"></div>

    <!-- Main Content Wrapper (Grid Centering) -->
    <main class="relative z-10 min-h-screen w-full grid place-items-center p-4 md:p-8">
        <!-- Maintenance Alert -->
        <?php if($isMaintenance): ?>
        <div class="mb-6 w-full max-w-md bg-amber-500/10 border border-amber-500/30 p-4 rounded-2xl flex items-center gap-4 animate-pulse">
            <div class="w-10 h-10 bg-amber-500/20 rounded-full flex items-center justify-center text-amber-500 shrink-0">
                <i class="fa-solid fa-screwdriver-wrench"></i>
            </div>
            <div>
                <div class="text-sm font-bold text-amber-500">Scheduled Maintenance</div>
                <div class="text-xs text-amber-500/80">Site is currently restricted to administrators.</div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Login Card -->
        <div class="login-card w-full max-w-md mx-auto p-8 md:p-10">

        <!-- Brand Header -->
        <div class="flex flex-col items-center mb-8">
            <div
                class="logo-badge w-16 h-16 rounded-2xl flex items-center justify-center text-white text-3xl mb-4 shadow-2xl">
                <i class="fa-solid fa-building"></i>
            </div>
            <h1 class="text-3xl font-extrabold tracking-tight mb-1">
                <?php if ($appName === 'LeasePro'): ?>
                <span class="text-white">Lease</span><span class="text-gradient">Pro</span>
                <?php else: ?>
                <span class="text-white"><?php echo htmlspecialchars($appName); ?></span>
                <?php endif; ?>
            </h1>
            <p class="text-slate-400 text-sm font-medium tracking-wide"><?php echo htmlspecialchars($appTagline); ?></p>
        </div>

        <!-- Divider -->
        <div class="divider mb-8"></div>

        <!-- Welcome Text -->
        <div class="mb-7">
            <h2 class="text-xl font-bold text-white mb-1">Welcome back 👋</h2>
            <p class="text-slate-400 text-sm">Sign in to your account to continue</p>
        </div>

        <!-- Error Message -->
        <div class="error-msg mb-5" id="error-msg" role="alert" style="<?php echo $error ? 'display: flex;' : ''; ?>">
            <i class="fa-solid fa-circle-exclamation text-red-400"></i>
            <span id="error-text"><?php echo htmlspecialchars($error); ?></span>
        </div>

        <!-- Login Form -->
        <form id="login-form" method="POST" action="index.php" novalidate>

            <!-- Username Field -->
            <div class="mb-5">
                <label for="username" class="block text-sm font-semibold text-slate-300 mb-2 ml-1">
                    Username
                </label>
                <div class="input-group">
                    <i class="fa-solid fa-user input-icon"></i>
                    <input type="text" id="username" name="username" class="login-input"
                        placeholder="Enter your username" autocomplete="username" required>
                </div>
            </div>

            <!-- Password Field -->
            <div class="mb-5">
                <div class="mb-2 ml-1">
                    <label for="password" class="block text-sm font-semibold text-slate-300">
                        Password
                    </label>
                </div>
                <div class="input-group">
                    <i class="fa-solid fa-lock input-icon"></i>
                    <input type="password" id="password" name="password" class="login-input"
                        placeholder="Enter your password" autocomplete="current-password" required
                        style="padding-right: 3rem;">
                    <button type="button" class="toggle-pw" id="toggle-pw" aria-label="Toggle password visibility"
                        tabindex="-1">
                        <i class="fa-solid fa-eye" id="pw-eye-icon"></i>
                    </button>
                </div>
                <div class="flex justify-end mt-2 mr-1">
                    <a href="javascript:void(0)" onclick="openResetModal()" class="text-xs text-blue-400 hover:text-blue-300 font-medium transition-colors">
                        Forgot password?
                    </a>
                </div>
            </div>

            <!-- Remember Me -->
            <div class="flex items-center gap-3 mb-7">
                <input type="checkbox" id="remember" name="remember" class="w-4 h-4 rounded"
                    style="accent-color: #3b82f6; cursor: pointer;">
                <label for="remember"
                    class="text-sm text-slate-400 cursor-pointer select-none hover:text-slate-300 transition-colors">
                    Keep me signed in
                </label>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="login-btn" id="login-btn">
                <span id="btn-text" class="flex items-center justify-center gap-2">
                    <i class="fa-solid fa-arrow-right-to-bracket"></i>
                    Sign In
                </span>
                <div class="spinner mx-auto" id="btn-spinner"></div>
            </button>

        </form>

        <!-- Divider -->
        <div class="divider mt-8 mb-6"></div>

        <!-- Footer -->
        <div class="mt-10 pt-6 border-t border-slate-500/10 text-center">
            <p class="text-[11px] text-slate-500 font-bold uppercase tracking-[0.2em] leading-relaxed">
                © <?php echo date('Y'); ?> <span class="text-slate-400"><?php echo htmlspecialchars($appName); ?></span> &bull; Security Team
            </p>
            <p class="text-[9px] text-slate-600 font-medium mt-1 opacity-60">Authorized Personnel &bull; Encrypted Portal</p>
        </div>

    </div> <!-- Close Main Login Card -->
    </main> <!-- Close Main Wrapper -->

    <!-- Forgot Password Modal -->
    <div id="reset-modal" class="fixed inset-0 z-[110] hidden items-center justify-center p-4 bg-slate-950/95 backdrop-blur-2xl">
        <div class="login-card w-full max-w-md p-8 md:p-10 shadow-[0_0_60px_rgba(59,130,246,0.2)] border-blue-500/40 relative overflow-hidden" id="reset-card">
            
            <!-- Brand Header (Same as Login) -->
            <div class="flex flex-col items-center mb-8">
                <div class="logo-badge w-14 h-14 rounded-2xl flex items-center justify-center text-white text-2xl mb-4 shadow-xl">
                    <i class="fa-solid fa-building"></i>
                </div>
                <h1 class="text-2xl font-extrabold tracking-tight mb-1">
                    <?php if ($appName === 'LeasePro'): ?>
                    <span class="text-white">Lease</span><span class="text-gradient">Pro</span>
                    <?php else: ?>
                    <span class="text-white"><?php echo htmlspecialchars($appName); ?></span>
                    <?php endif; ?>
                </h1>
                <p class="text-slate-400 text-[10px] uppercase font-bold tracking-[0.2em]"><?php echo htmlspecialchars($appName); ?> Security Service</p>
            </div>

            <div class="divider mb-8"></div>
            
            <!-- Step 1: Email Request -->
            <div id="step-1">
                <div class="mb-7">
                    <h2 class="text-xl font-bold text-white mb-1">Reset Password</h2>
                    <p class="text-slate-400 text-sm">Enter your email and we'll send an OTP code to your inbox.</p>
                </div>

                <div class="space-y-5">
                    <div class="input-group">
                        <i class="fa-solid fa-envelope input-icon"></i>
                        <input type="email" id="reset-email" class="login-input" placeholder="Enter your email address">
                    </div>
                    <button onclick="requestOTP()" class="login-btn" id="btn-request-otp">
                        <span id="txt-request" class="flex items-center justify-center gap-2">
                            <i class="fa-solid fa-paper-plane text-sm"></i>
                            Send OTP Code
                        </span>
                    </button>
                    <button onclick="closeResetModal()" class="w-full text-slate-400 text-sm font-medium hover:text-white transition-colors py-2 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-arrow-left text-xs"></i>
                        Back to Login
                    </button>
                </div>
            </div>

            <!-- Step 2: Verification & Reset -->
            <div id="step-2" class="hidden">
                <div class="mb-7">
                    <h2 class="text-xl font-bold text-white mb-1">Security Verification</h2>
                    <p class="text-slate-400 text-sm">We've sent a 6-digit code to your device.</p>
                </div>
                
                <div class="space-y-6">
                    <!-- 6-digit OTP input boxes -->
                    <div class="flex justify-between gap-2" id="otp-inputs-container">
                        <input type="text" maxlength="1" class="otp-digit w-10 h-14 bg-white/5 border border-blue-500/30 rounded-2xl text-center text-2xl font-bold text-white focus:border-blue-500 focus:bg-slate-800 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all">
                        <input type="text" maxlength="1" class="otp-digit w-10 h-14 bg-white/5 border border-blue-500/30 rounded-2xl text-center text-2xl font-bold text-white focus:border-blue-500 focus:bg-slate-800 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all">
                        <input type="text" maxlength="1" class="otp-digit w-10 h-14 bg-white/5 border border-blue-500/30 rounded-2xl text-center text-2xl font-bold text-white focus:border-blue-500 focus:bg-slate-800 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all">
                        <input type="text" maxlength="1" class="otp-digit w-10 h-14 bg-white/5 border border-blue-500/30 rounded-2xl text-center text-2xl font-bold text-white focus:border-blue-500 focus:bg-slate-800 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all">
                        <input type="text" maxlength="1" class="otp-digit w-10 h-14 bg-white/5 border border-blue-500/30 rounded-2xl text-center text-2xl font-bold text-white focus:border-blue-500 focus:bg-slate-800 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all">
                        <input type="text" maxlength="1" class="otp-digit w-10 h-14 bg-white/5 border border-blue-500/30 rounded-2xl text-center text-2xl font-bold text-white focus:border-blue-500 focus:bg-slate-800 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all">
                    </div>

                    <div class="space-y-4">
                        <div class="input-group">
                            <i class="fa-solid fa-lock input-icon"></i>
                            <input type="password" id="reset-new-pass" class="login-input" placeholder="New Password">
                        </div>
                        <div class="input-group">
                            <i class="fa-solid fa-shield-check input-icon"></i>
                            <input type="password" id="reset-confirm-pass" class="login-input" placeholder="Confirm Password">
                        </div>
                    </div>

                    <button onclick="resetPassword()" class="login-btn" id="btn-reset-final">
                        <span class="flex items-center justify-center gap-2">
                            <i class="fa-solid fa-shield-halved text-sm"></i>
                            Verify & Update
                        </span>
                    </button>
                    
                    <p class="text-center text-[10px] text-slate-500 font-bold uppercase tracking-widest">
                        Didn't receive code? <a href="javascript:void(0)" onclick="requestOTP()" class="text-blue-500 hover:text-blue-400">Resend Email</a>
                    </p>
                </div>
            <div id="reset-toast" class="mt-6 p-4 rounded-2xl border text-sm hidden font-medium text-center"></div>
        </div>
    </div>

    <script>
        // ── Toggle password visibility ──
        const togglePw = document.getElementById('toggle-pw');
        const pwInput = document.getElementById('password');
        const pwEyeIcon = document.getElementById('pw-eye-icon');

        togglePw.addEventListener('click', () => {
            const isHidden = pwInput.type === 'password';
            pwInput.type = isHidden ? 'text' : 'password';
            pwEyeIcon.className = isHidden ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
        });

        // Form submission handled by PHP POST
        const loginForm = document.getElementById('login-form');
        const loginBtn = document.getElementById('login-btn');
        const btnText = document.getElementById('btn-text');
        const btnSpinner = document.getElementById('btn-spinner');

        loginForm.addEventListener('submit', () => {
            setLoading(true);
        });

        function setLoading(state) {
            loginBtn.disabled = state;
            btnText.style.display = state ? 'none' : 'flex';
            btnSpinner.style.display = state ? 'block' : 'none';
            loginBtn.style.opacity = state ? '0.85' : '1';
        }

        // ── Reset Password Flow ──
        const resetModal = document.getElementById('reset-modal');
        const resetCard = document.getElementById('reset-card');

        function openResetModal() {
            resetModal.classList.remove('hidden');
            resetModal.classList.add('flex');
            setTimeout(() => resetCard.classList.remove('scale-95'), 10);
            showStep(1);
        }

        function closeResetModal() {
            resetCard.classList.add('scale-95');
            setTimeout(() => {
                resetModal.classList.add('hidden');
                resetModal.classList.remove('flex');
                hideToast();
            }, 300);
        }

        function showStep(step) {
            document.getElementById('step-1').classList.toggle('hidden', step !== 1);
            document.getElementById('step-2').classList.toggle('hidden', step !== 2);
            hideToast();
        }

        async function requestOTP() {
            const email = document.getElementById('reset-email').value.trim();
            if (!email) { showToast('Please enter your email.', 'error'); return; }

            setBtnLoading('btn-request-otp', true, 'Sending...');
            
            const formData = new FormData();
            formData.append('action', 'forgot_request');
            formData.append('email', email);

            try {
                const res = await fetch('index.php', { method: 'POST', body: formData });
                const data = await res.json();
                
                if (data.success) {
                    Swal.fire({
                        title: 'OTP Sent!',
                        text: 'A 6-digit verification code has been sent to your inbox.',
                        icon: 'success',
                        background: '#0f172a',
                        color: '#fff',
                        confirmButtonColor: '#3b82f6',
                        customClass: {
                            popup: 'rounded-3xl border border-blue-500/20 shadow-2xl'
                        }
                    }).then(() => {
                        showStep(2);
                    });
                } else {
                    showToast(data.message, 'error');
                }
            } catch (e) {
                showToast('Something went wrong. Try again.', 'error');
            } finally {
                setBtnLoading('btn-request-otp', false, 'Send OTP Code');
            }
        }

        async function resetPassword() {
            const email = document.getElementById('reset-email').value.trim();
            const pass = document.getElementById('reset-new-pass').value;
            const confirm = document.getElementById('reset-confirm-pass').value;

            // Combine OTP digits
            const otpDigits = document.querySelectorAll('.otp-digit');
            let otp = '';
            otpDigits.forEach(input => otp += input.value);

            if (otp.length < 6) { showToast('Please enter the full 6-digit code.', 'error'); return; }
            if (!pass) { showToast('Please enter a new password.', 'error'); return; }
            if (pass !== confirm) { showToast('Passwords do not match.', 'error'); return; }

            setBtnLoading('btn-reset-final', true, 'Resetting...');

            const formData = new FormData();
            formData.append('action', 'reset_password');
            formData.append('email', email);
            formData.append('otp', otp);
            formData.append('password', pass);

            try {
                const res = await fetch('index.php', { method: 'POST', body: formData });
                const data = await res.json();
                
                if (data.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Your password has been updated securely.',
                        icon: 'success',
                        background: '#0f172a',
                        color: '#fff',
                        confirmButtonColor: '#3b82f6',
                        customClass: {
                            popup: 'rounded-3xl border border-blue-500/20 shadow-2xl'
                        }
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    showToast(data.message, 'error');
                }
            } catch (e) {
                showToast('Something went wrong. Try again.', 'error');
            } finally {
                setBtnLoading('btn-reset-final', false, 'Reset & Login');
            }
        }

        // ── OTP Digit Auto-focus logic ──
        const otpDigits = document.querySelectorAll('.otp-digit');
        otpDigits.forEach((input, index) => {
            input.addEventListener('input', (e) => {
                if (e.target.value.length === 1 && index < otpDigits.length - 1) {
                    otpDigits[index + 1].focus();
                }
            });

            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && e.target.value.length === 0 && index > 0) {
                    otpDigits[index - 1].focus();
                }
            });
        });

        function showToast(msg, type) {
            const toast = document.getElementById('reset-toast');
            toast.textContent = msg;
            toast.className = `mt-4 p-3 rounded-xl border text-xs ${type === 'success' ? 'bg-green-500/10 border-green-500/30 text-green-400' : 'bg-red-500/10 border-red-500/30 text-red-400'}`;
            toast.classList.remove('hidden');
        }

        function hideToast() {
            document.getElementById('reset-toast').classList.add('hidden');
        }

        function setBtnLoading(id, state, text) {
            const btn = document.getElementById(id);
            btn.disabled = state;
            btn.querySelector('span').textContent = text;
            btn.style.opacity = state ? '0.7' : '1';
        }

        // ── Input focus glow border highlight ──
        document.querySelectorAll('.login-input').forEach(input => {
            input.addEventListener('focus', () => {
                input.previousElementSibling &&
                    (input.previousElementSibling.style.color = '#60a5fa');
            });
            input.addEventListener('blur', () => {
                input.previousElementSibling &&
                    (input.previousElementSibling.style.color = 'rgba(59, 130, 246,0.6)');
            });
        });
    </script>

</body>

</html>