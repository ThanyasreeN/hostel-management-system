<?php
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "hms");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Actions
$page = $_GET['page'] ?? 'home';

if ($page == 'register' && $_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["full_name"];
    $email = $_POST["email"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $password);
    echo $stmt->execute() ? "✅ Registered successfully. <a href='?page=login'>Login</a>" : "❌ Error: " . $stmt->error;
    exit;
}

if ($page == 'login' && $_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];
    $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($user = $res->fetch_assoc()) {
        if (password_verify($password, $user["password"])) {
            $_SESSION["user_id"] = $user["id"];
            header("Location: ?page=dashboard");
        } else echo "❌ Incorrect password!";
    } else echo "❌ User not found!";
    exit;
}

if ($page == 'logout') {
    session_destroy();
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION["user_id"] ?? null;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Hostel Management System</title>
</head>
<body>
<h2>Hostel Management System</h2>

<?php if (!$user_id): ?>

    <?php if ($page == 'register'): ?>
        <h3>Register</h3>
        <form method="POST">
            <input type="text" name="full_name" placeholder="Full Name"><br>
            <input type="email" name="email" placeholder="Email"><br>
            <input type="password" name="password" placeholder="Password"><br>
            <button type="submit">Register</button>
        </form>
        <a href="?page=login">Already have an account? Login</a>

    <?php elseif ($page == 'login'): ?>
        <h3>Login</h3>
        <form method="POST">
            <input type="email" name="email" placeholder="Email"><br>
            <input type="password" name="password" placeholder="Password"><br>
            <button type="submit">Login</button>
        </form>
        <a href="?page=register">Don't have an account? Register</a>

    <?php else: ?>
        <a href="?page=login">Login</a> | <a href="?page=register">Register</a>
    <?php endif; ?>

<?php else: ?>
    <h3>Dashboard</h3>
    <a href="?page=personal">Personal Details</a> |
    <a href="?page=room">Room Allocation</a> |
    <a href="?page=fee">Fee Payment</a> |
    <a href="?page=feedback">Feedback</a> |
    <a href="?page=logout">Logout</a>
    <hr>

    <?php
    if ($page == 'personal' && $_SERVER["REQUEST_METHOD"] == "POST") {
        $stmt = $conn->prepare("INSERT INTO personal_details (user_id, first_name, last_name, age) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("issi", $user_id, $_POST["fname"], $_POST["lname"], $_POST["age"]);
        echo $stmt->execute() ? "✅ Details saved!" : "❌ Error";
    }
    if ($page == 'room' && $_SERVER["REQUEST_METHOD"] == "POST") {
        $stmt = $conn->prepare("INSERT INTO room_allocations (user_id, room_no) VALUES (?, ?)");
        $stmt->bind_param("is", $user_id, $_POST["room"]);
        echo $stmt->execute() ? "✅ Room allocated!" : "❌ Error";
    }
    if ($page == 'fee' && $_SERVER["REQUEST_METHOD"] == "POST") {
        $stmt = $conn->prepare("INSERT INTO fee_payments (user_id, amount, payment_method) VALUES (?, ?, ?)");
        $stmt->bind_param("ids", $user_id, $_POST["amount"], $_POST["method"]);
        echo $stmt->execute() ? "✅ Payment successful!" : "❌ Error";
    }
    if ($page == 'feedback' && $_SERVER["REQUEST_METHOD"] == "POST") {
        $stmt = $conn->prepare("INSERT INTO feedback (user_id, comments) VALUES (?, ?)");
        $stmt->bind_param("is", $user_id, $_POST["comments"]);
        echo $stmt->execute() ? "✅ Feedback submitted!" : "❌ Error";
    }
    ?>

    <?php if ($page == 'personal'): ?>
        <h3>Personal Details</h3>
        <form method="POST">
            <input type="text" name="fname" placeholder="First Name"><br>
            <input type="text" name="lname" placeholder="Last Name"><br>
            <input type="number" name="age" placeholder="Age"><br>
            <button type="submit">Submit</button>
        </form>

    <?php elseif ($page == 'room'): ?>
        <h3>Room Allocation</h3>
        <form method="POST">
            <select name="room">
                <option>Room 101</option>
                <option>Room 102</option>
                <option>Room 103</option>
            </select><br>
            <button type="submit">Allocate</button>
        </form>

    <?php elseif ($page == 'fee'): ?>
        <h3>Fee Payment</h3>
        <form method="POST">
            <input type="number" name="amount" placeholder="Amount"><br>
            <select name="method">
                <option>UPI</option>
                <option>Bank Transfer</option>
                <option>Cash</option>
            </select><br>
            <button type="submit">Pay</button>
        </form>

    <?php elseif ($page == 'feedback'): ?>
        <h3>Feedback</h3>
        <form method="POST">
            <textarea name="comments" placeholder="Your feedback here..."></textarea><br>
            <button type="submit">Submit</button>
        </form>
    <?php endif; ?>

<?php endif; ?>
</body>
</html>
