<?php
require_once '../config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$uid = $_SESSION['user_id'];
$msg = '';

// --- Initialize method for dropdown to avoid warnings ---
$method = $_GET['method'] ?? 'transfer';

// --- 1. Calculate Total Fine & Fetch User Credit ---

// Get all overdue/approved requests to calculate current fine
$stmt_fines = $pdo->prepare("
    SELECT due_date 
    FROM borrow_requests 
    WHERE user_id = ? 
      AND status = 'approved' 
      AND due_date < CURDATE()
");
$stmt_fines->execute([$uid]);

$total_fine = 0;
while ($row = $stmt_fines->fetch()) {
    $total_fine += calculate_fine($row['due_date']);
}
$total_fine = round($total_fine, 2);

// Fetch user's current credit
$stmt_user = $pdo->prepare("SELECT credit_balance FROM users WHERE user_id = ?");
$stmt_user->execute([$uid]);
$user_credit = (float) $stmt_user->fetchColumn();
$user_credit = round($user_credit, 2);

// Total required payment (Fine minus Credit, cannot be negative)
$required_payment = max(0, $total_fine - $user_credit);
$default_amount = (float)($_GET['amount'] ?? $required_payment);

// --- 2. Handle POST Payment ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount_paid = floatval($_POST['amount'] ?? 0);
    $method = $_POST['method'] ?? 'card';
    $desc = trim($_POST['description'] ?? 'Payment');

    // Handle proof upload for QR/Transfer
    $proof_filename = null;
    if (($method === 'qr' || $method === 'transfer') && !empty($_FILES['proof_file']['name'])) {
        $tmp = $_FILES['proof_file']['tmp_name'];
        $proof_filename = $uid . '_' . time() . '_' . basename($_FILES['proof_file']['name']);

        $target_dir = __DIR__ . "/Images/payment-proofs/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        if (!move_uploaded_file($tmp, $target_dir . $proof_filename)) {
            $msg = "<p class='error'>Error uploading proof of payment. Please check folder permissions.</p>";
        }
    }

    if ($amount_paid <= 0) {
        $msg = "<p class='error'>Enter a valid amount (RM).</p>";
    } elseif (($method === 'qr' || $method === 'transfer') && !$proof_filename && empty($msg)) {
        $msg = "<p class='error'>Please upload proof of payment for the selected manual method.</p>";
    } elseif (!$msg) {
        // Core logic
        $net_amount = $amount_paid;
        $new_credit = $user_credit;

        $amount_to_cover = max(0, $total_fine - $user_credit);
        $paid_to_cover = min($net_amount, $amount_to_cover);

        if ($amount_to_cover > 0) {
            $new_credit -= $paid_to_cover;
            $net_amount -= $paid_to_cover;
        }

        $new_credit += $net_amount;

        // Update credit
        $upd_credit = $pdo->prepare("UPDATE users SET credit_balance = ? WHERE user_id = ?");
        $upd_credit->execute([$new_credit, $uid]);

        // Record payment
        $ins = $pdo->prepare("
            INSERT INTO payments (user_id, amount, description, payment_method, proof_file) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $ins->execute([$uid, $amount_paid, $desc, $method, $proof_filename]);

        $payment_id = $pdo->lastInsertId();
        header("Location: receipt.php?id=$payment_id");
        exit;
    }
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Make Payment - MatangReads</title>
    <link rel="stylesheet" href="/matangreads/css/style.css">
</head>
<body>
<?php include '../navbar.php'; ?>
<div class="container">
    <div class="payment-modal">
        <h2 style="color: #F7EF8A;">Make a Payment</h2>
        <?php if ($msg) echo $msg; ?>

        <p style="text-align: center; margin-bottom: 20px;">
            Total Fine: <span style="font-weight:bold; color:red;">RM <?php echo number_format($total_fine, 2); ?></span>
            | Credit: <span style="font-weight:bold; color:green;">RM <?php echo number_format($user_credit, 2); ?></span>
            <br>
            **Net Amount Due:** 
            <span style="font-weight:bold; color:#F7EF8A;">RM <?php echo number_format($required_payment, 2); ?></span>
        </p>

        <form method="post" enctype="multipart/form-data">
            <div id="payment-step-1">
                <div class="payment-grid">
                    <label>Amount (RM)</label>
                    <input name="amount" type="number" step="0.01" value="<?php echo htmlspecialchars($default_amount); ?>" min="0.01" required>

                    <label>Payment Method</label>
                    <select name="method" id="payment-method">
                        <option value="card" <?php echo ($method === 'card' ? 'selected' : ''); ?>>Credit/Debit Card (Simulated)</option>
                        <option value="qr" <?php echo ($method === 'qr' ? 'selected' : ''); ?>>QR Code (Scan & Upload Proof)</option>
                        <option value="transfer" <?php echo ($method === 'transfer' ? 'selected' : ''); ?>>Bank Transfer (Manual)</option>
                    </select>
                </div>

                <div id="card-fields" style="background:#222; padding: 15px; margin-top: 15px; border-radius: 8px;">
                    <p style="font-style: italic;">Card payment is simulated. Proceed to confirm payment.</p>
                    <label style="color: #f0f0f0;">Card Number (Simulated)</label>
                    <input name="card_number" type="text" placeholder="XXXX-XXXX-XXXX-XXXX" maxlength="19">
                    <label style="color: #f0f0f0;">CVV (Simulated)</label>
                    <input name="card_cvv" type="text" maxlength="4">
                </div>

                <div class="description-group">
                    <label>Description</label>
                    <input name="description" value="Payment towards fines/fees" required>
                </div>

                <div class="form-buttons" style="margin-top: 20px;">
                    <button type="submit" class="btn" id="confirm-payment-btn">Confirm Payment</button>
                </div>
            </div>

            <div id="payment-step-qr" style="display: none; text-align: center;">
                <h3 style="color: #F7EF8A;">Scan QR Code to Pay</h3>
                <p>Please scan the QR code below to make your payment of RM <?php echo number_format($default_amount, 2); ?>.</p>

                <div class="qr-container">
                    <img src="/matangreads/Images/QRbank.png" alt="Payment QR Code">
                </div>

                <label style="color: #f0f0f0; display: block; margin-top: 20px;">Upload Proof of Payment (Screenshot)</label>
                <input type="file" name="proof_file" accept="image/*" style="width: 100%; border: 1px dashed #AE8625;">

                <div class="info-status">Status: Pending Payment (Upload Proof and Confirm Below)</div>

                <div class="form-buttons" style="margin-top: 20px;">
                    <button type="submit" class="btn" style="background-color: green;">Confirm Upload & Pay</button>
                    <button type="button" class="btn" onclick="document.getElementById('payment-method').value='card'; togglePaymentMethod();" style="background-color: #555;">Change Method</button>
                </div>
            </div>

            <div id="payment-step-transfer" style="display: none; text-align: center;">
                <h3 style="color: #F7EF8A;">Manual Bank Transfer Instructions</h3>
                <p>Please transfer the amount of RM <?php echo number_format($default_amount, 2); ?> to the account below.</p>

                <div style="background:#333; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                    <p style="text-align: left; font-size: 1.1em;">
                        <strong>Bank Name:</strong> Maybank<br>
                        <strong>Account Number:</strong> 1234 5678 9012 (Example)<br>
                        <strong>Account Name:</strong> MatangReads Sdn Bhd
                    </p>
                </div>

                <label style="color: #f0f0f0; display: block; margin-top: 20px;">Upload Proof of Payment (Bank Receipt/Screenshot)</label>
                <input type="file" name="proof_file" accept="image/*" style="width: 100%; border: 1px dashed #AE8625;">

                <div class="info-status">Status: Pending Transfer (Upload Proof and Confirm Below)</div>

                <div class="form-buttons" style="margin-top: 20px;">
                    <button type="submit" class="btn" style="background-color: green;">Confirm Upload & Pay</button>
                    <button type="button" class="btn" onclick="document.getElementById('payment-method').value='card'; togglePaymentMethod();" style="background-color: #555;">Change Method</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function togglePaymentMethod() {
    const method = document.getElementById('payment-method').value;
    const step1 = document.getElementById('payment-step-1');
    const stepQr = document.getElementById('payment-step-qr');
    const stepTransfer = document.getElementById('payment-step-transfer');
    const cardFields = document.getElementById('card-fields');

    stepQr.style.display = 'none';
    stepTransfer.style.display = 'none';

    if (method === 'qr') {
        step1.style.display = 'none';
        stepQr.style.display = 'block';
        cardFields.style.display = 'none';
    } else if (method === 'transfer') {
        step1.style.display = 'none';
        stepTransfer.style.display = 'block';
        cardFields.style.display = 'none';
    } else {
        step1.style.display = 'block';
        cardFields.style.display = 'block';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const methodSelect = document.getElementById('payment-method');
    methodSelect.addEventListener('change', togglePaymentMethod);
    togglePaymentMethod();
});
</script>
</body>
</html>
