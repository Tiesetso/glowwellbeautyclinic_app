<?php

require_once 'dbconnection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header('Location: index.html');
    exit;
}

$customerId = $_SESSION['user_id'];
$customerName = htmlspecialchars($_SESSION['full_name']);

// Fetch available services from the database
$servicesStmt = $pdo->query("SELECT * FROM services ORDER BY category, service_name");
$services = $servicesStmt->fetchAll();

// Fetch active specialists from the database
$staffStmt = $pdo->query("SELECT * FROM staff WHERE status = 'Active' ORDER BY full_name");
$staffMembers = $staffStmt->fetchAll();

// Fetch this customer's appointment history using a JOIN query
$apptsStmt = $pdo->prepare(
    "SELECT a.appointment_id, a.appointment_date, a.appointment_time, a.status, a.notes,
           s.service_name, s.price, st.full_name AS staff_name
    FROM appointments a
    JOIN services s ON a.service_id = s.service_id
    JOIN staff st ON a.staff_id = st.staff_id
    WHERE a.customer_id = ?
    ORDER BY a.appointment_date DESC, a.appointment_time DESC"
    );
$apptsStmt->execute([$customerId]);
$appointments = $apptsStmt->fetchAll();

// Fetch the next upcoming appointment for the countdown timer
$nextApptStmt = $pdo->prepare(
    "SELECT a.appointment_date, a.appointment_time, s.service_name 
    FROM appointments a
    JOIN services s ON a.service_id = s.service_id
    WHERE a.customer_id = ? 
      AND a.status IN ('Pending', 'Confirmed')
      AND CONCAT(a.appointment_date, ' ', a.appointment_time) >= NOW()
    ORDER BY a.appointment_date ASC, a.appointment_time ASC
    LIMIT 1"
    );
$nextApptStmt->execute([$customerId]);
$nextAppointment = $nextApptStmt->fetch();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>   
        .section-subtitle {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 24px;
            margin-top: 12px;
        } 

        .booking-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 48px;
        }

        .price-preview-box {
            background: #fdfaf6;
            padding: 16px;
            border-radius: 6px;
            margin: 16px 0;
            display: flex;
            gap: 32px;
        }

        .price-highlight {
            color: var(--primary-dark);
        }

        .empty-text {
            margin-top: 16px;
            color: #777;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        .btn-cancel-sm {
            padding: 5px 10px;
            font-size: 0.8rem;
        }

        .no-action-label {
            color: #999;
            font-size: 0.85rem;
        }

        .hidden-card {
            display: none;
        }

        .nav-link {
            color: var(--primary-dark);
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
        }

        .nav-link:hover {
            text-decoration: underline;
        }
    </style> 
    <script src="assets/js/app.js?v=2" defer></script>
</head>
<body>

    <header class="navbar">
        <div class="brand">GlowWell Beauty Clinic</div>
        <div class="user-info">
            <span>Welcome, <strong><?= $customerName ?></strong></span>
            <a href="#" class="nav-link" onclick="event.preventDefault(); togglePasswordCard();">Change Password</a>
            <a href="api/logout.php?redirect=1" class="btn btn-secondary">Sign Out</a>
        </div>
    </header>

    <main class="container">

        <!-- Next appointment countdownbanner -->
        <?php if ($nextAppointment): ?>

            <?php 
                // Feeds the exact appointment date and time straight into JavaScript.
                $targetDateTime = $nextAppointment['appointment_date'] . 'T' . $nextAppointment['appointment_time'];
                $serviceTitle = htmlspecialchars($nextAppointment['service_name']);
            ?>

            <div class="data-table-card" style="background: linear-gradient(135deg, #f3e8f8 0%, #ffffff 100%); border-left: 5px solid var(--primary);">

                <h3 style="color: var(--primary-dark); margin-bottom: 8px; font-size: 1rem;">Countdown to Your Next Appointment</h3>
                <p style="font-size: 0.95rem; margin-bottom: 12px; color: var(--dark);">
                    <strong><?= $serviceTitle ?></strong> on <?= $nextAppointment['appointment_date'] ?> at <?= substr($nextAppointment['appointment_time'], 0, 5) ?>
                </p>

                <div id="countdown-timer" style="display: flex; gap: 16px; font-weight: 700; font-size: 1.2rem; color: var(--primary-dark);">

                    <div><span id="days">00</span> <small style="font-size: 0.75rem; color: #666; display: block;">Days</small></div>
                    <div>:</div>
                    <div><span id="hours">00</span> <small style="font-size: 0.75rem; color: #666; display: block;">Hours</small></div>
                    <div>:</div>
                    <div><span id="mins">00</span> <small style="font-size: 0.75rem; color: #666; display: block;">Mins</small></div>
                    <div>:</div>
                    <div><span id="secs">00</span> <small style="font-size: 0.75rem; color: #666; display: block;">Secs</small></div>

                </div>

            </div>

            <script>

                document.addEventListener("DOMContentLoaded", () => {

                    const targetDate = new Date("<?= $targetDateTime ?>").getTime();

                    const updateTimer = () => {

                        const now = new Date().getTime();
                        const distance = targetDate - now;

                        if (distance < 0) {
                            document.getElementById("countdown-timer").innerHTML = "<span style='font-size: 0.95rem; color: var(--success);'>Your appointment time has arrived! Please check in with your specialist.</span>";
                            return;
                        }

                        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                        document.getElementById("days").innerText = String(days).padStart(2, '0');
                        document.getElementById("hours").innerText = String(hours).padStart(2, '0');
                        document.getElementById("mins").innerText = String(minutes).padStart(2, '0');
                        document.getElementById("secs").innerText = String(seconds).padStart(2, '0');

                    };

                    setInterval(updateTimer, 1000);
                    updateTimer();

                });

            </script>

        <?php endif; ?>
        
        <!-- Booking form card -->
        <div class="data-table-card">

            <h2>Book an Appointment</h2>
            <p class="section-subtitle">Select your desired service, specialist, and preferred date/time slot.</p>
            
            <div id="booking-alert" class="alert"></div>

            <form onsubmit="event.preventDefault(); handleModularSubmit(this, 'api/book_appointment.php', 'booking-alert', true);">

                <div class="booking-grid">
                    
                    <div class="form-group">
                        <label for="service_select">Select Service</label>
                        <select id="service_select" name="service_id" class="form-control" required>
                            <option value="">-- Choose a Service --</option>
                            <?php foreach ($services as $srv): ?>
                                <option value="<?= $srv['service_id'] ?>" data-price="<?= $srv['price'] ?>" data-duration="<?= $srv['duration_minutes'] ?>">
                                    <?= htmlspecialchars($srv['service_name']) ?> (<?= htmlspecialchars($srv['category']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="staff_select">Select Specialist</label>
                        <select id="staff_select" name="staff_id" class="form-control" required>
                            <option value="">-- Choose Specialist --</option>
                            <?php foreach ($staffMembers as $stf): ?>
                                <option value="<?= $stf['staff_id'] ?>">
                                    <?= htmlspecialchars($stf['full_name']) ?> (<?= htmlspecialchars($stf['specialty']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="appointment_date">Preferred Date</label>
                        <input type="date" id="appointment_date" name="appointment_date" class="form-control" min="<?= date('Y-m-d') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="appointment_time">Time Slot</label>
                        <select id="appointment_time" name="appointment_time" class="form-control" required>
                            <option value="">-- Choose Time --</option>
                            <option value="09:00:00">09:00 AM</option>
                            <option value="10:00:00">10:00 AM</option>
                            <option value="11:00:00">11:00 AM</option>
                            <option value="12:00:00">12:00 PM</option>
                            <option value="14:00:00">02:00 PM</option>
                            <option value="15:00:00">03:00 PM</option>
                            <option value="16:00:00">04:00 PM</option>
                        </select>
                    </div>

                </div>

                <!-- Live javaScript price & duration preview -->
                <div class="price-preview-box">
                    <div>Estimated Price: <strong id="price_display" class="price-highlight">R 0.00</strong></div>
                    <div>Duration: <strong id="duration_display">0 mins</strong></div>
                </div>

                <div class="form-group">
                    <label for="notes">Special Requests / Notes (Optional)</label>
                </div>

                <div>
                    <textarea id="notes" name="notes" class="form-control" rows="2" placeholder="e.g. Allergies, areas of focus..."></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Confirm Appointment Request</button>

            </form>

        </div>

        <!-- Apponitment history -->
        <div class="data-table-card">

            <h2>Your Appointment History</h2>
            
            <?php if (count($appointments) === 0): ?>
                <p class="empty-text">You have not made any bookings yet.</p>
            <?php else: ?>
                <div class="table-wrapper">

                    <table>

                        <thead>

                            <tr>
                                <th>Booking Ref</th>
                                <th>Service</th>
                                <th>Specialist</th>
                                <th>Date & Time</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>

                        </thead>

                        <tbody>

                            <?php foreach ($appointments as $apt): ?>

                                <tr>
                                    <td>#<?= $apt['appointment_id'] ?></td>
                                    <td><strong><?= htmlspecialchars($apt['service_name']) ?></strong></td>
                                    <td><?= htmlspecialchars($apt['staff_name']) ?></td>
                                    <td><?= htmlspecialchars($apt['appointment_date']) ?> @ <?= substr($apt['appointment_time'], 0, 5) ?></td>
                                    <td>R <?= number_format($apt['price'], 2) ?></td>
                                    <td><span class="badge badge-<?= $apt['status'] ?>"><?= $apt['status'] ?></span></td>
                                    <td>
                                        <?php if ($apt['status'] === 'Pending' || $apt['status'] === 'Confirmed'): ?>
                                            <button class="btn btn-danger btn-cancel-sm"
                                                onclick="callApi('api/cancel_appointment.php', { appointment_id: <?= $apt['appointment_id'] ?> }, 'Are you sure you want to cancel this booking?')">
                                                Cancel
                                            </button>
                                        <?php else: ?>
                                            <span class="no-action-label">None</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                
                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>
            <?php endif; ?>
            
        </div>

        <!-- Change password card -->
        <div id="password-card" class="data-table-card hidden-card">

            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h2>Change Password</h2>
                <a href="#" class="nav-link" onclick="event.preventDefault(); togglePasswordCard();">Close ✕</a>
            </div>
            <p class="section-subtitle">Update your account password below.</p>

            <div id="password-alert" class="alert"></div>

            <form onsubmit="event.preventDefault(); handleModularSubmit(this, 'api/change_password.php', 'password-alert', false);">

                <div class="booking-grid">

                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" class="form-control" required placeholder="Enter current password">
                    </div>

                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" class="form-control" required placeholder="At least 6 characters">
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required placeholder="Re-enter new password">
                    </div>
                    
                </div>

                <button type="submit" class="btn btn-primary">Update Password</button>

            </form>

        </div>

    </main>

</body>
</html>