<?php
require_once 'config.php';
session_start();
if (!isset($_SESSION['user_id'])) header('Location: login.php');
$uid = $_SESSION['user_id'];
$msg = '';

// --- 1. Calculate Total Fine & Fetch User Credit ---

// Get all overdue/approved requests to calculate current fine
$stmt_fines = $pdo->prepare("SELECT due_date FROM borrow_requests WHERE user_id = ? AND status = 'approved' AND due_date < CURDATE()");
$stmt_fines->execute([$uid]);
$total_fine = 0;
while ($row = $stmt_fines->fetch()) {
    $total_fine += calculate_fine($row['due_date']);
}
$total_fine = round($total_fine, 2);

// Fetch User's Current Credit
$stmt_user = $pdo->prepare("SELECT credit_balance FROM users WHERE user_id = ?");
$stmt_user->execute([$uid]);
$user_credit = $stmt_user->fetchColumn();
$user_credit = round($user_credit, 2);

// Total required payment (Fine minus Credit, cannot be negative)
$required_payment = max(0, $total_fine - $user_credit);
$default_amount = (float)($_GET['amount'] ?? $required_payment);

// --- 2. Handle POST Payment ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount_paid = floatval($_POST['amount'] ?? 0);
    $method = $_POST['method'] ?? 'card';
    $desc = trim($_POST['description'] ?? 'Payment');
    
    // ... (Card validation logic remains here) ...
    $card_number = $_POST['card_number'] ?? '';
    $card_expiry = $_POST['card_expiry'] ?? '';
    $card_cvv = $_POST['card_cvv'] ?? '';
    
    // Simple validation
    if ($amount_paid <= 0) {
        $msg = "<p class='error'>Enter a valid amount (RM).</p>";
    } elseif ($method == 'card' && (empty($card_number) || empty($card_cvv))) {
        // NOTE: In a real system, card details should not be stored/processed here.
        $msg = "<p class='error'>Card details are required for Card payment.</p>";
    } else {
        // ----------------------------------------------------
        // CORE CREDIT/FINE PROCESSING LOGIC
        // ----------------------------------------------------
        
        $net_amount = $amount_paid; // The amount we have to work with
        $current_credit = $user_credit;
        
        // 1. Calculate the actual fine amount due (Total Fine - Current Credit)
        $amount_to_cover = max(0, $total_fine - $current_credit);
        
        // 2. Determine how much of the payment covers the amount due
        $paid_to_cover = min($net_amount, $amount_to_cover);
        
        // 3. Update fines and calculate new credit
        $new_credit = $current_credit;
        
        if ($amount_to_cover > 0) {
            // Fines are being paid down (deduct the amount paid to cover from the fine)
            // This payment is applied to the net due, bringing the user's effective credit/debt closer to zero.
            $net_debt_change = $paid_to_cover;
            
            // Recalculate credit/debt based on the transaction
            $new_credit -= $paid_to_cover; 
            
            // Since we applied $paid_to_cover, the remaining cash is $net_amount - $paid_to_cover
            $net_amount -= $paid_to_cover;
        }

        // 4. Any remaining cash after fine payment becomes new credit
        $new_credit += $net_amount; 

        // 5. Update the user's credit balance
        $upd_credit = $pdo->prepare("UPDATE users SET credit_balance = ? WHERE user_id = ?");
        $upd_credit->execute([$new_credit, $uid]);
        
        // 6. Record the payment history
        $ins = $pdo->prepare("INSERT INTO payments (user_id, amount, description, payment_method) VALUES (?, ?, ?, ?)");
        $ins->execute([$uid, $amount_paid, $desc, $method]);
        
        $payment_id = $pdo->lastInsertId();
        header("Location: receipt.php?id=$payment_id");
        exit;
    }
}
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Make Payment - MatangReads</title>
<link rel="stylesheet" href="/matangreads/css/style.css">
</head><body>
<?php include 'navbar.php'; ?>
<div class="container content-wrapper">
<div class="form-container" style="max-width: 550px; margin: 30px auto;">
  <h2>Make a Payment</h2>
  <?php 
  if($msg) echo $msg; 
  ?>
  
  <p class="fine-notice" style="text-align:center; font-size:16px;">
    Total Outstanding Fine: <span style="font-weight:bold; color:red;">RM <?php echo number_format($total_fine, 2); ?></span>
    | Current Credit: <span style="font-weight:bold; color:green;">RM <?php echo number_format($user_credit, 2); ?></span>
    <br>
    **Net Amount Due Now:** <span style="font-weight:bold; color:#2d0115;">RM <?php echo number_format($required_payment, 2); ?></span>
  </p>

  <form method="post">
    <label>Amount (RM)</label>
    <input name="amount" type="number" step="0.01" value="<?php echo htmlspecialchars($default_amount); ?>" min="0.01" required>
    
    <label>Payment method</label>
    <select name="method" id="payment-method">
      <option value="card">Credit/Debit Card</option>
      <option value="paypal">PayPal</option>
      <option value="transfer">Bank Transfer (Manual)</option>
    </select>
    
    <!-- Realism: Credit Card Fields -->
    <div id="card-fields" style="border: 1px solid #ddd; padding: 15px; margin-top: 15px; border-radius: 8px;">
        <label>Card Number</label>
        <input name="card_number" type="text" placeholder="XXXX-XXXX-XXXX-XXXX" maxlength="19">
        <div style="display: flex; gap: 15px;">
            <div style="flex: 1;">
                <label>Expiry (MM/YY)</label>
                <input name="card_expiry" type="text" placeholder="MM/YY">
            </div>
            <div style="flex: 1;">
                <label>CVV</label>
                <input name="card_cvv" type="text" maxlength="4">
            </div>
        </div>
    </div>

    <label>Description</label>
    <input name="description" value="Payment towards fines/fees" required>
    
    <div class="form-buttons" style="margin-top: 20px;"><button type="submit" class="btn btn-primary">Pay</button></div>
  </form>
</div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const methodSelect = document.getElementById('payment-method');
    const cardFields = document.getElementById('card-fields');

    function toggleCardFields() {
        // We only require card fields if 'card' is selected
        const isCard = methodSelect.value === 'card';
        cardFields.style.display = isCard ? 'block' : 'none';
        cardFields.querySelectorAll('input').forEach(input => input.required = isCard);
    }

    methodSelect.addEventListener('change', toggleCardFields);
    toggleCardFields(); // Initialize on load
});
</script>
</body></html>
