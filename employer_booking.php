<?php
session_start();
if (!isset($_SESSION['employer_id'])) {
    header("Location: employer_login.php");
    exit();
}
require_once("db_connect.php");

$employee_id = $_GET['eid'] ?? null;

if (!$employee_id) {
    echo "Invalid employee ID.";
    exit();
}

// Fetch employee details
$stmt = $conn->prepare("SELECT Name, Skills FROM employee WHERE ID = ?");
$stmt->execute([$employee_id]);
$employee = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$employee) {
    echo "Employee not found.";
    exit();
}

$book_date = date('Y-m-d'); // today's date

// --- Review Section ---
$review_success = '';
$review_error = '';
$employer_id = $_SESSION['employer_id'];

// Fetch all completed bookings for this employer and employee
$completed_bookings_stmt = $conn->prepare("SELECT ID, Booking_date FROM bookings WHERE Homeowner_ID = ? AND Employee_ID = ? AND Status = 'completed'");
$completed_bookings_stmt->execute([$employer_id, $employee_id]);
$completed_bookings = $completed_bookings_stmt->fetchAll(PDO::FETCH_ASSOC);

// Find bookings that have not been reviewed yet
$unreviewed_bookings = [];
foreach ($completed_bookings as $booking) {
    $review_check = $conn->prepare("SELECT ID FROM review_table WHERE Booking_ID = ? AND Reviewer_type = 'employer'");
    $review_check->execute([$booking['ID']]);
    if (!$review_check->fetch()) {
        $unreviewed_bookings[] = $booking;
    }
}

// Handle review submission
if (isset($_POST['submit_review'])) {
    $rating = intval($_POST['rating']);
    $comment = trim($_POST['comment']);
    $booking_id = intval($_POST['booking_id'] ?? 0);
    // Check if this booking is valid and unreviewed
    $valid_booking = false;
    foreach ($unreviewed_bookings as $ub) {
        if ($ub['ID'] == $booking_id) {
            $valid_booking = true;
            break;
        }
    }
    if (!$valid_booking) {
        $review_error = 'Invalid or already reviewed booking.';
    } elseif ($rating > 0 && $rating <= 5 && $comment) {
        $stmt = $conn->prepare("INSERT INTO review_table (Booking_ID, Rating, Comment, Date, Reviewer_type) VALUES (?, ?, ?, ?, 'employer')");
        $stmt->execute([$booking_id, $rating, $comment, date('Y-m-d')]);
        $review_success = 'Review submitted!';
        // Refresh unreviewed bookings
        header("Location: employer_booking.php?eid=$employee_id");
        exit();
    } else {
        $review_error = 'Please provide a rating and comment.';
    }
}

// Fetch all reviews for this employee (from review_table)
$reviews = $conn->prepare("SELECT r.*, e.Name AS employer_name FROM review_table r JOIN bookings b ON r.Booking_ID = b.ID JOIN employer e ON b.Homeowner_ID = e.ID WHERE b.Employee_ID = ? AND r.Reviewer_type = 'employer' ORDER BY r.created_at DESC");
$reviews->execute([$employee_id]);
$all_reviews = $reviews->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $conn->prepare("INSERT INTO bookings 
        (Homeowner_ID, Employee_ID, Service_type, Booking_date, Status)
        VALUES (?, ?, ?, ?, 'pending')");

    $stmt->execute([
        $_SESSION['employer_id'],
        $employee_id,
        $employee['Skills'],
        $book_date
    ]);

    echo "<p style='color:green;'>✅ Booking submitted successfully!</p>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Book <?= htmlspecialchars($employee['Name']) ?></title>
    <style>
        body { font-family: Arial; background: #f0f0f0; padding: 30px; }
        .box {
            background: white;
            max-width: 500px;
            margin: auto;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        label { display: block; margin-top: 10px; font-weight: bold; }
        input, button {
            width: 100%; padding: 10px;
            margin-top: 5px; border-radius: 4px;
        }
        button {
            background-color: #00695c;
            color: white;
            border: none;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="box">
    <h2>Book <?= htmlspecialchars($employee['Name']) ?></h2>
    
    <!-- Review Section -->
    <div class="review-section" style="background:#fff;padding:20px;border-radius:8px;margin-bottom:20px;">
        <h3>Leave a Review</h3>
        <?php if ($review_success) echo "<p style='color:green;'>$review_success</p>"; ?>
        <?php if ($review_error) echo "<p style='color:red;'>$review_error</p>"; ?>
        <?php if (count($unreviewed_bookings) > 0): ?>
        <form method="post" id="reviewForm">
            <label for="booking_id">Select Completed Booking:</label>
            <select name="booking_id" required>
                <option value="">-- Select Booking Date --</option>
                <?php foreach ($unreviewed_bookings as $ub): ?>
                    <option value="<?= $ub['ID'] ?>">Booking on <?= htmlspecialchars($ub['Booking_date']) ?></option>
                <?php endforeach; ?>
            </select>
            <div class="star-rating" style="font-size:2rem;direction:rtl;display:inline-flex;">
                <?php for ($i = 5; $i >= 1; $i--): ?>
                    <input type="radio" id="star<?= $i ?>" name="rating" value="<?= $i ?>" style="display:none;">
                    <label for="star<?= $i ?>" style="cursor:pointer;">&#9733;</label>
                <?php endfor; ?>
            </div>
            <textarea name="comment" placeholder="Write your review..." required style="width:100%;margin-top:10px;"></textarea>
            <button type="submit" name="submit_review" style="margin-top:10px;">Submit Review</button>
        </form>
        <?php else: ?>
            <p style="color:gray;">You can only review after a completed booking. No unreviewed completed bookings found.</p>
        <?php endif; ?>
        <script>
        document.querySelectorAll('.star-rating label').forEach(label => {
            label.addEventListener('mouseover', function() {
                let val = this.htmlFor.replace('star','');
                document.querySelectorAll('.star-rating label').forEach(l => {
                    l.style.color = (l.htmlFor.replace('star','') <= val) ? '#FFD600' : '#ccc';
                });
            });
            label.addEventListener('mouseout', function() {
                let checked = document.querySelector('.star-rating input:checked');
                let val = checked ? checked.value : 0;
                document.querySelectorAll('.star-rating label').forEach(l => {
                    l.style.color = (l.htmlFor.replace('star','') <= val) ? '#FFD600' : '#ccc';
                });
            });
            label.addEventListener('click', function() {
                let val = this.htmlFor.replace('star','');
                document.getElementById('star'+val).checked = true;
                document.querySelectorAll('.star-rating label').forEach(l => {
                    l.style.color = (l.htmlFor.replace('star','') <= val) ? '#FFD600' : '#ccc';
                });
            });
        });
        window.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.star-rating label').forEach(l => l.style.color = '#ccc');
        });
        </script>
        <div style="margin-top:30px;">
            <h3>Reviews</h3>
            <?php foreach ($all_reviews as $review): ?>
                <div style="margin-bottom:20px;">
                    <strong><?= htmlspecialchars($review['employer_name']) ?></strong> 
                    <span style="color:#FFD600;font-size:1.2em;">
                        <?= str_repeat('★', $review['Rating']) . str_repeat('☆', 5 - $review['Rating']) ?>
                    </span><br>
                    <em><?= htmlspecialchars($review['Comment']) ?></em><br>
                    <small><?= $review['created_at'] ?></small>
                </div>
                <hr>
            <?php endforeach; ?>
        </div>
    </div>
    <!-- End Review Section -->

    <form method="POST">
        <label>Book Date:</label>
        <input type="text" name="book_date" value="<?= $book_date ?>" readonly>

        <label>Skill Booked:</label>
        <input type="text" name="skill" value="<?= htmlspecialchars($employee['Skills']) ?>" readonly>

        <button type="submit">Submit Booking</button>
    </form>
</div>

</body>
</html>
