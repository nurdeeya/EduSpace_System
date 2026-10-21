
<?php
session_start();

require_once __DIR__ . '/../config/database.php';

// Accept login requests only through POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php');
    exit;
}

// Display a general login failure message.
function loginFailed(): void
{
    $_SESSION['login_error'] =
        'Invalid username or password.';

    header('Location: ../login.php');
    exit;
}

// Display an account lockout message.
function loginLocked(int $remainingMinutes = 15): void
{
    $remainingMinutes = max(1, $remainingMinutes);

    $_SESSION['login_error'] =
        'Your account is temporarily locked due to '
        . '5 consecutive failed login attempts. '
        . 'Please try again in approximately '
        . $remainingMinutes
        . ' minute(s).';

     $_SESSION['show_lockout_popup'] = true;

    header('Location: ../login.php');
    exit;
}

// Get submitted login details.
$loginIdentifier = trim($_POST['login_identifier'] ?? '');
$password = $_POST['password'] ?? '';

// Check required fields.
if ($loginIdentifier === '' || $password === '') {
    loginFailed();
}

try {
    // Find the student or lecturer account.
    $sql = 'SELECT
                user_id,
                login_identifier,
                full_name,
                password_hash,
                role,
                failed_login_attempts,
                locked_until,
                is_active
            FROM users
            WHERE login_identifier = :identifier
            LIMIT 1';

    //Prepared statement dan parameter binding
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'identifier' => $loginIdentifier
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Reject unknown or inactive accounts.
    if (!$user || !(bool) $user['is_active']) {
        loginFailed();
    }

    // Check lockout using the same clock as MySQL.
    if ($user['locked_until'] !== null) {
        $timeQuery = $pdo->prepare(
            'SELECT TIMESTAMPDIFF(
                SECOND,
                NOW(),
                :locked_until
            )'
        );

        $timeQuery->execute([
            'locked_until' => $user['locked_until']
        ]);

        $remainingSeconds = (int) $timeQuery->fetchColumn();

        if ($remainingSeconds > 0) {
            $remainingMinutes = (int) ceil(
                $remainingSeconds / 60
            );

            loginLocked($remainingMinutes);
        }

        // Lockout has expired; reset the counter.
        $resetLock = $pdo->prepare(
            'UPDATE users
             SET failed_login_attempts = 0,
                 locked_until = NULL
             WHERE user_id = :user_id'
        );

        $resetLock->execute([
            'user_id' => $user['user_id']
        ]);

        $user['failed_login_attempts'] = 0;
        $user['locked_until'] = null;
    }

    // Verify the submitted password.
    if (!password_verify($password, $user['password_hash'])) {
        $failedAttempts =
            (int) $user['failed_login_attempts'] + 1;

        // Lock the account for 15 minutes on attempt 5.
        $update = $pdo->prepare(
            'UPDATE users
             SET failed_login_attempts = :attempts,
                 locked_until = CASE
                     WHEN :lock_attempts >= 5
                     THEN DATE_ADD(NOW(), INTERVAL 15 MINUTE)
                     ELSE NULL
                 END
             WHERE user_id = :user_id'
        );

        $update->execute([
            'attempts' => $failedAttempts,
            'lock_attempts' => $failedAttempts,
            'user_id' => $user['user_id']
        ]);

        if ($failedAttempts >= 5) {
            loginLocked(15);
        }

        loginFailed();
    }

    // Allow only recognised roles.
    if (!in_array(
        $user['role'],
        ['student', 'lecturer'],
        true
    )) {
        loginFailed();
    }

    // Reset failed attempts after successful login.
    $reset = $pdo->prepare(
        'UPDATE users
         SET failed_login_attempts = 0,
             locked_until = NULL
         WHERE user_id = :user_id'
    );

    $reset->execute([
        'user_id' => $user['user_id']
    ]);

    // Regenerate the session ID after authentication.
    session_regenerate_id(true);

    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['login_identifier'] = $user['login_identifier'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['role'] = $user['role'];

    // Redirect according to the user's role.
    if ($user['role'] === 'student') {
        header('Location: ../student_dashboard.php');
        exit;
    }

    if ($user['role'] === 'lecturer') {
        header('Location: ../lecturer_dashboard.php');
        exit;
    }

    loginFailed();

} catch (PDOException $e) {
    error_log($e->getMessage());

    http_response_code(500);
    exit('A system error occurred. Please try again later.');
}
