<?php
$level = isset($_SESSION['userlevel']) ? intval($_SESSION['userlevel']) : 3;
?>

<ul class="nav navbar-nav">
    <!-- All logged-in users -->
    <li><a href="clients.php">Clients</a></li>

    <!-- Cashiers & Admins -->
    <?php if (in_array($level, [1, 2])): ?>
        <li><a href="billing.php">Billing</a></li>
        <li><a href="viewpayment.php">Payments</a></li>
    <?php endif; ?>

    <!-- Admins Only -->
    <?php if ($level === 1): ?>
        <li><a href="user.php">Manage Users</a></li>
    <?php endif; ?>
    
    <li><a href="logout.php">Logout</a></li>
</ul>