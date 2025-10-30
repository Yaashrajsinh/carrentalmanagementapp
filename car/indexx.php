<?php
session_start();

// Demo user
$users = ['user@example.com' => 'password123'];

// Car list
$cars = [
    ['id'=>1,'model'=>'Maruti Swift','hour'=>100,'km'=>10,'img'=>'swift.jpg'],
    ['id'=>2,'model'=>'Honda City','hour'=>130,'km'=>12,'img'=>'hondacity.jpg'],
    ['id'=>3,'model'=>'Toyota Innova','hour'=>180,'km'=>15,'img'=>'innova.jpg'],
    ['id'=>4,'model'=>'Hyundai Creta','hour'=>150,'km'=>13,'img'=>'creata.jpg'],
    ['id'=>5,'model'=>'Ford EcoSport','hour'=>140,'km'=>11,'img'=>'ecosport.jpg'],
    ['id'=>6,'model'=>'Maruti Baleno','hour'=>110,'km'=>9,'img'=>'baleno.jpg'],
    ['id'=>7,'model'=>'Maruti Dzire','hour'=>120,'km'=>10,'img'=>'dezire.jpg'],
    ['id'=>8,'model'=>'Hyundai Verna','hour'=>135,'km'=>12,'img'=>'verna.jpg'],
    ['id'=>9,'model'=>'Toyota Fortuner','hour'=>200,'km'=>18,'img'=>'fortuner.jpg'],
    ['id'=>10,'model'=>'Tata Harrier','hour'=>160,'km'=>14,'img'=>'harier.jpg'],
    ['id'=>11,'model'=>'Kia Seltos','hour'=>150,'km'=>13,'img'=>'seltos.jpg'],
    ['id'=>12,'model'=>'Hyundai Venue','hour'=>145,'km'=>12,'img'=>'venue.jpg'],
    ['id'=>13,'model'=>'Maruti Brezza','hour'=>130,'km'=>10,'img'=>'breeza.jpg'],
    ['id'=>14,'model'=>'Honda Amaze','hour'=>125,'km'=>9,'img'=>'amaze.jpg'],
    ['id'=>15,'model'=>'Tata Tiago','hour'=>95,'km'=>8,'img'=>'tiago.jpg'],
    ['id'=>16,'model'=>'Tata Nexon','hour'=>140,'km'=>11,'img'=>'nexon.jpg'],
    ['id'=>17,'model'=>'Toyota Innova Crysta','hour'=>185,'km'=>16,'img'=>'altura_crysta.jpg'],
    ['id'=>18,'model'=>'Mahindra XUV700','hour'=>170,'km'=>14,'img'=>'xuv700.jpg'],
    ['id'=>19,'model'=>'Tata Punch','hour'=>105,'km'=>9,'img'=>'punch.jpg'],
    ['id'=>20,'model'=>'Kia Sonet','hour'=>135,'km'=>12,'img'=>'sonet.jpg'],
    ['id'=>21,'model'=>'Santro zin','hour'=>135,'km'=>12,'img'=>'santro.jpg'],
];

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// Handle Login
if ($_POST['action'] ?? '' === 'login') {
    $email = $_POST['email'] ?? '';
    $pwd   = $_POST['password'] ?? '';
    if (isset($users[$email]) && $users[$email] === $pwd) {
        $_SESSION['user'] = $email;
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    } else {
        $error = "Invalid credentials.";
    }
}

// Show login if not authenticated
if (!isset($_SESSION['user'])):
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="carr.css">
</head>
<body>
<form method="POST">
    <h2>Login</h2>
    <?php if(isset($error)) echo "<div class='error'>$error</div>"; ?>
    <input type="hidden" name="action" value="login">
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Login</button>
</form>
</body>
</html>

<?php exit(); endif;

// Handle Booking
if ($_POST['action'] ?? '' === 'rent') {
    $carId     = intval($_POST['car_id']);
    $name      = trim(htmlspecialchars($_POST['name'] ?? ''));
    $phone     = trim(htmlspecialchars($_POST['phone'] ?? ''));
    $aadhar    = trim(htmlspecialchars($_POST['aadhar'] ?? ''));
    $hours     = intval($_POST['hours'] ?? 0);
    $kms       = intval($_POST['kilometers'] ?? 0);
    $payment   = htmlspecialchars($_POST['payment'] ?? '');

    if (!$name || !$phone || !$aadhar || $hours <= 0 || $kms < 0 || !$payment) {
        $rentError = "Please fill everything correctly.";
    } else {
        foreach ($cars as $c) if ($c['id']===$carId) { $hr=$c['hour']; $kmr=$c['km']; break; }
        $cost = ($hr * $hours) + ($kmr * $kms);
        if ($payment === 'UPI') $discount = 0.05 * $cost;
        else $discount = 0;
        $final = round($cost - $discount, 2);

        $_SESSION['rentals'][] = [
            'car_id'=>$carId, 'name'=>$name, 'phone'=>$phone,
            'aadhar'=>$aadhar, 'hours'=>$hours, 'kms'=>$kms,
            'payment'=>$payment, 'subtotal'=>$cost,
            'discount'=>$discount, 'total'=>$final,
            'user'=>$_SESSION['user'], 'time'=>date('Y-m-d H:i:s'),
        ];
        $rentSuccess = "Booked! Subtotal: ₹$cost. Discount: ₹$discount. Total: ₹$final. Paid via: $payment.";
    }
}

$rentalCar = null;
if (isset($_GET['rent'])) {
    foreach ($cars as $c) if ($c['id'] === intval($_GET['rent'])) { $rentalCar = $c; break; }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Car Rental</title>
    <link rel="stylesheet" href="carr.css">
</head>

<body>
<div>
    <div>Logged in as <strong><?= $_SESSION['user'] ?></strong> | <a href="?logout=1">Logout</a></div>
    <h1>Car Rental Service</h1>

    <?php if ($rentalCar): ?>
        <h2>Book: <?= $rentalCar['model'] ?></h2>
        <div><img src="<?= $rentalCar['img']?>" width="300"></div>
        <p>₹<?= $rentalCar['hour'] ?>/hr & ₹<?= $rentalCar['km']?>/km</p>
        <?php if(isset($rentError)) echo "<p>$rentError</p>"; ?>
        <?php if(isset($rentSuccess)) echo "<p>$rentSuccess</p>"; ?>
        <form method="POST">
            <input type="hidden" name="action" value="rent">
            <input type="hidden" name="car_id" value="<?= $rentalCar['id'] ?>">
            <label>Name:</label><input type="text" name="name" required>
            <label>Phone:</label><input type="tel" name="phone" required>
            <label>Aadhar:</label><input type="text" name="aadhar" required>
            <label>Hours:</label><input type="number" name="hours" min="1" required>
            <label>Estimated km:</label><input type="number" name="kilometers" min="0" required>
            <label>Payment Method:</label>
            <select name="payment" required>
                <option value="">--Select--</option>
                <option value="UPI">UPI (5% off)</option>
                <option value="Card">Credit/Debit Card</option>
                <option value="Cash">Cash</option>
            </select>
            <button type="submit">Confirm Booking</button>
        </form>
        <p><a href="<?= $_SERVER['PHP_SELF']?>">Back to list</a></p>
    <?php else: ?>
        <h2>Available Cars</h2>
<div class="car-grid">
<?php foreach ($cars as $c): ?>
    <div class="car-card">
        <img src="<?= $c['img'] ?>" alt="<?= $c['model'] ?>">
        <h3><?= $c['model'] ?></h3>
        <p>₹<?= $c['hour']?>/hr & ₹<?= $c['km']?>/km</p>
        <p><a href="?rent=<?= $c['id'] ?>"><button>Book Now</button></a></p>
    </div>
<?php endforeach; ?>
</div>

        

        <?php if (!empty($_SESSION['rentals'])): ?>
        <h2>Your Bookings</h2>
        <table border="1" cellpadding="6" cellspacing="0">
            <tr>
                <th>Car</th><th>Name</th><th>Phone</th><th>Aadhar</th><th>Hours</th><th>Km</th>
                <th>Payment</th><th>Subtotal</th><th>Discount</th><th>Total</th><th>Time</th>
            </tr>
            <?php foreach ($_SESSION['rentals'] as $r): ?>
                <?php foreach ($cars as $c) if ($c['id']===$r['car_id']) { $cm = $c['model']; break;} ?>
                <tr>
                    <td><?= $cm ?></td><td><?= $r['name'] ?></td><td><?= $r['phone'] ?></td><td><?= $r['aadhar'] ?></td>
                    <td><?= $r['hours'] ?></td><td><?= $r['kms'] ?></td><td><?= $r['payment'] ?></td>
                    <td>₹<?= $r['subtotal'] ?></td><td>₹<?= $r['discount'] ?></td><td>₹<?= $r['total'] ?></td><td><?= $r['time'] ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    <?php endif; ?>
</div>
</body>
</html>
