
<?php
session_start();

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/vendor/autoload.php';

use RobThree\Auth\TwoFactorAuth;
use RobThree\Auth\Providers\Qr\BaconQrCodeProvider;

// Only users with a pending login may verify MFA.
if (
    !isset(
        $_SESSION['pending_mfa_user_id'],
        $_SESSION['pending_mfa_role']
    ) ||
    !in_array(
        $_SESSION['pending_mfa_role'],
        ['student', 'lecturer'],
        true
    )
) {
    header('Location: login.php');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';

try {
    // Read the account's current MFA settings from the database.
    $stmt = $pdo->prepare(
        'SELECT
            user_id,
            login_identifier,
            full_name,
            role,
            mfa_enabled,
            mfa_secret
         FROM users
         WHERE user_id = :user_id
           AND role = :role
           AND is_active = 1
         LIMIT 1'
    );

    $stmt->execute([
        'user_id' => $_SESSION['pending_mfa_user_id'],
        'role' => $_SESSION['pending_mfa_role']
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        session_unset();
        session_destroy();
        header('Location: login.php');
        exit;
    }

    // If MFA is not enabled, finish setting it up first.
    if (
        (int) $user['mfa_enabled'] !== 1 ||
        empty($user['mfa_secret'])
    ) {
        header('Location: setup_mfa.php');
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $csrf = $_POST['csrf_token'] ?? '';
        $code = trim($_POST['verification_code'] ?? '');

        if (
            !is_string($csrf) ||
            !hash_equals($_SESSION['csrf_token'], $csrf)
        ) {
            $error = 'Invalid request. Please try again.';
        } elseif (!preg_match('/^\d{6}$/', $code)) {
            $error = 'Enter the six-digit code from your authenticator app.';
        } else {
            $tfa = new TwoFactorAuth(
                new BaconQrCodeProvider(format: 'svg'),
                'EduSphere'
            );

            if (!$tfa->verifyCode($user['mfa_secret'], $code)) {
                $error = 'Invalid code. Check your authenticator app and try again.';
            } else {
                // The second factor has now been verified.
                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['login_identifier'] = $user['login_identifier'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['auth_complete'] = true;

                // Remove temporary login state.
                unset(
                    $_SESSION['pending_mfa_user_id'],
                    $_SESSION['pending_mfa_role'],
                    $_SESSION['pending_mfa_secret'],
                    $_SESSION['csrf_token']
                );

                if ($user['role'] === 'student') {
                    header('Location: student_dashboard.php');
                    exit;
                }

                if ($user['role'] === 'lecturer') {
                    header('Location: lecturer_dashboard.php');
                    exit;
                }

                session_unset();
                session_destroy();
                header('Location: login.php');
                exit;
            }
        }
    }

} catch (Throwable $e) {
    error_log((string) $e);
    http_response_code(500);
    exit('A system error occurred. Please try again later.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify MFA - EduSphere</title>
</head>
<body>
    <h1>Multi-Factor Authentication</h1>

    <p>
        Account:
        <?= htmlspecialchars(
            $user['login_identifier'],
            ENT_QUOTES,
            'UTF-8'
        ) ?>
    </p>

    <p>
        Enter the six-digit code from your authenticator app
        to complete login.
    </p>

    <?php if ($error !== ''): ?>
        <p role="alert">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </p>
    <?php endif; ?>

    <form method="POST" action="verify_mfa.php">
        <input
            type="hidden"
            name="csrf_token"
            value="<?= htmlspecialchars(
                $_SESSION['csrf_token'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>"
        >

        <label for="verification_code">Verification code</label>
        <input
            type="text"
            id="verification_code"
            name="verification_code"
            inputmode="numeric"
            pattern="[0-9]{6}"
            maxlength="6"
            autocomplete="one-time-code"
            required
        >

        <button type="submit">Verify and Log In</button>
    </form>
</body>
</html>
