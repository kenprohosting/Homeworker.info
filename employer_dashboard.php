<?php
session_start();
if (!isset($_SESSION['employer_id'])) {
    header("Location:employer_login.php");
    exit();
}
require_once('db_connect.php');

// Fetch employees with filters
$filter_sql = "SELECT * FROM employee WHERE 1";
$params = [];

if (!empty($_GET['skill'])) {
    $filter_sql .= " AND Skills LIKE ?";
    $params[] = '%' . $_GET['skill'] . '%';
}
if (!empty($_GET['location'])) {
    $filter_sql .= " AND Location LIKE ?";
    $params[] = '%' . $_GET['location'] . '%';
}
if (!empty($_GET['gender'])) {
    $filter_sql .= " AND Gender = ?";
    $params[] = $_GET['gender'];
}
if (!empty($_GET['residence'])) {
    $filter_sql .= " AND residence_type = ?";
    $params[] = $_GET['residence'];
}

$stmt = $conn->prepare($filter_sql);
$stmt->execute($params);
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch employer's bookings
$stmt2 = $conn->prepare("
    SELECT b.*, emp.Name AS employee_name 
    FROM bookings b 
    JOIN employee emp ON b.Employee_ID = emp.ID 
    WHERE b.Homeowner_ID = ?
    ORDER BY b.Booking_date DESC
");
$stmt2->execute([$_SESSION['employer_id']]);
$bookings = $stmt2->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
  <title>Employer Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body { font-family: 'Segoe UI', sans-serif; background: #f5f5f5; margin: 0; }
    header { background: rgb(24, 123, 136); color: white; padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; }
    .logo { font-size: 1.5em; font-weight: bold; }
    nav ul { list-style: none; display: flex; gap: 20px; margin: 0; }
    nav a { color: white; text-decoration: none; }
    .form-container { padding: 30px; }
    .filter-form input, .filter-form select, .filter-form button {
      padding: 10px; margin-right: 10px; margin-top: 10px;
    }
    .card-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 20px;
      margin-top: 30px;
    }
    .card {
      background: white;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
      text-align: center;
    }
    .card img {
      width: 100px;
      height: 100px;
      border-radius: 50%;
      object-fit: cover;
      margin-bottom: 10px;
    }
    .btn {
      display: inline-block;
      padding: 10px 16px;
      background: #00695c;
      color: white;
      border: none;
      border-radius: 5px;
      margin-top: 10px;
      text-decoration: none;
    }
    table {
      width: 100%;
      margin-top: 30px;
      border-collapse: collapse;
      background: white;
    }
    th, td {
      padding: 12px;
      border: 1px solid #ccc;
      text-align: center;
    }
    th { background: #eeeeee; }
  </style>
</head>
<body>

<header>
  <div class="logo">Houselp Connect</div>
  <nav>
    <ul class="nav-links">
      <li>Hello, <?= htmlspecialchars($_SESSION['employer_name']) ?></li>
      <li><a href="employer_logout.php">Logout</a></li>
    </ul>
  </nav>
</header>

<div class="form-container">
  <h2>Find a Househelp</h2>

  <!-- Filter Form -->
  <form method="GET" class="filter-form">
    <input type="text" name="skill" placeholder="Skill (e.g. Driving)">
    
    
  <select id="county" name="county" required>
    <option value="" disabled selected>Select a county</option>
    <option value="baringo">Baringo</option>
    <option value="bomet">Bomet</option>
    <option value="bungoma">Bungoma</option>
    <option value="busia">Busia</option>
    <option value="elgeyo_marakwet">Elgeyo-Marakwet</option>
    <option value="embu">Embu</option>
    <option value="garissa">Garissa</option>
    <option value="homa_bay">Homa Bay</option>
    <option value="isiolo">Isiolo</option>
    <option value="kajiado">Kajiado</option>
    <option value="kakamega">Kakamega</option>
    <option value="kericho">Kericho</option>
    <option value="kiambu">Kiambu</option>
    <option value="kilifi">Kilifi</option>
    <option value="kirinyaga">Kirinyaga</option>
    <option value="kisii">Kisii</option>
    <option value="kisumu">Kisumu</option>
    <option value="kitui">Kitui</option>
    <option value="kwale">Kwale</option>
    <option value="laikipia">Laikipia</option>
    <option value="lamu">Lamu</option>
    <option value="machakos">Machakos</option>
    <option value="makueni">Makueni</option>
    <option value="mandera">Mandera</option>
    <option value="marsabit">Marsabit</option>
    <option value="meru">Meru</option>
    <option value="migori">Migori</option>
    <option value="mombasa">Mombasa</option>
    <option value="muranga">Murang'a</option>
    <option value="nairobi">Nairobi</option>
    <option value="nakuru">Nakuru</option>
    <option value="nandi">Nandi</option>
    <option value="narok">Narok</option>
    <option value="nyamira">Nyamira</option>
    <option value="nyandarua">Nyandarua</option>
    <option value="nyeri">Nyeri</option>
    <option value="samburu">Samburu</option>
    <option value="siaya">Siaya</option>
    <option value="taita_taveta">Taita-Taveta</option>
    <option value="tana_river">Tana River</option>
    <option value="tharaka_nithi">Tharaka-Nithi</option>
    <option value="trans_nzoia">Trans Nzoia</option>
    <option value="turkana">Turkana</option>
    <option value="uasin_gishu">Uasin Gishu</option>
    <option value="vihiga">Vihiga</option>
    <option value="wajir">Wajir</option>
    <option value="west_pokot">West Pokot</option>
  </select>
    <select name="gender">
      <option value="">Gender</option>
      <option>Male</option>
      <option>Female</option>
    </select>
    <select name="residence">
      <option value="">Residence</option>
      <option value="urban">Urban</option>
      <option value="rural">Rural</option>
    </select>
    <button type="submit" class="btn">Search</button>
  </form>

  <!-- Employee Cards -->
  <div class="card-grid">
    <?php foreach ($employees as $emp): ?>
      <div class="card">
        <?php
          $profile = 'uploads/default.jpg';
          if (!empty($emp['profile_pic'])) {
              if (file_exists(__DIR__ . '/' . $emp['profile_pic'])) {
                  $profile = $emp['profile_pic'];
              }
          }
        ?>
        <img src="<?= htmlspecialchars($profile) ?>" alt="Profile Picture">
        <h3><?= htmlspecialchars($emp['Name']) ?> (<?= $emp['Age'] ?>)</h3>
        <p><strong>Skill:</strong> <?= htmlspecialchars($emp['Skills']) ?></p>
        <p><strong>Location:</strong> <?=htmlspecialchars($emp['Location']) ?></p>
        <p><strong>Language:</strong> <?= htmlspecialchars($emp['Language']) ?></p>
        <p><strong>Education:</strong> <?= htmlspecialchars($emp['Education_level']) ?></p>
        <a href="employer_booking.php?eid=<?= $emp['ID'] ?>" class="btn">Book Now</a>
      </div>
    <?php endforeach; ?>
  </div>

  <h2 style="margin-top: 50px;">My Bookings</h2>

  <?php if (count($bookings) == 0): ?>
    <p>You have no bookings yet.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>Employee</th>
          <th>Service</th>
          <th>Date</th>
          <th>Time</th>
          <th>Status</th>
          <th>Payment</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($bookings as $b): ?>
        <tr>
          <td><?= htmlspecialchars($b['employee_name']) ?></td>
          <td><?= htmlspecialchars($b['Service_type']) ?></td>
          <td><?= htmlspecialchars($b['Booking_date']) ?></td>
          <td><?= $b['Start_time'] ?> - <?= $b['End_time'] ?></td>
          <td><?= htmlspecialchars($b['Status']) ?></td>
          <td>
            <?php if ($b['Status'] === 'confirmed'): ?>
                <a href="employer_payment.php?bid=<?= $b['ID'] ?>" class="btn">Make Payment</a>
            <?php elseif ($b['Status'] === 'completed'): ?>
                <span style="color:green;">Paid</span>
            <?php elseif ($b['Status'] === 'cancelled'): ?>
                <span style="color:red;">Cancelled</span>
            <?php else: ?>
                <span style="color:gray;">Pending</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

</body>
</html>
