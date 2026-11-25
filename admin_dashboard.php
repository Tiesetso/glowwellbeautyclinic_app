<?php

require_once 'dbconnection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.html');
    exit;
}

// Analytics & revenue summary queries
$totalAppts = $pdo->query("SELECT COUNT(*) FROM appointments")->fetchColumn();
$pendingAppts = $pdo->query("SELECT COUNT(*) FROM appointments WHERE status = 'Pending'")->fetchColumn();
$confirmedAppts = $pdo->query("SELECT COUNT(*) FROM appointments WHERE status = 'Confirmed'")->fetchColumn();
$totalRevenue = $pdo->query(
    "SELECT COALESCE(SUM(s.price), 0) 
    FROM appointments a 
    JOIN services s ON a.service_id = s.service_id 
    WHERE a.status = 'Completed'"
    )->fetchColumn();

// Fetch staff roster with active booking workload
$staffStmt = $pdo->query(
    "SELECT st.*, COUNT(a.appointment_id) AS total_bookings
    FROM staff st
    LEFT JOIN appointments a ON st.staff_id = a.staff_id AND a.status != 'Cancelled'
    GROUP BY st.staff_id
    ORDER BY total_bookings DESC"
    );
$staffList = $staffStmt->fetchAll();

// Fetch all customer appointments for CRUD management
$bookingsStmt = $pdo->query(
    "SELECT a.appointment_id, a.appointment_date, a.appointment_time, a.status, a.notes,
           u.full_name AS customer_name, u.phone AS customer_phone,
           s.service_name, s.price, st.full_name AS staff_name
    FROM appointments a
    JOIN users u ON a.customer_id = u.user_id
    JOIN services s ON a.service_id = s.service_id
    JOIN staff st ON a.staff_id = st.staff_id
    ORDER BY a.appointment_date DESC, a.appointment_time ASC"
    );
$bookings = $bookingsStmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .section-subtitle {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 24px;
            margin-top: 12px;
        }
        
        .stat-card-warning {
            border-left-color: var(--warning);
        }

        .stat-card-info {
            border-left-color: #3498db;
        }

        .stat-card-success {
            border-left-color: var(--success);
        }

        .section-subtitle {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 24px;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        .customer-phone {
            color: #777;
            font-size: 0.82rem;
        }

        .status-select {
            padding: 4px 8px;
            font-size: 0.85rem;
            border-radius: 4px;
            border: 1px solid var(--border);
        }

        .btn-delete-sm {
            padding: 4px 10px;
            font-size: 0.8rem;
            margin-left: 6px;
            margin-top: 6px;
        }

        .booking-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
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
        <div class="brand">GlowWell Beauty Clinic [Administrator Portal]</div>
        <div class="user-info">
            <span>Logged in as: <strong>Admin</strong></span>
            <a href="#" class="nav-link" onclick="event.preventDefault(); togglePasswordCard();">Change Password</a>
            <a href="api/logout.php?redirect=1" class="btn btn-secondary">Sign Out</a>
        </div>
    </header>

    <main class="container">

        <!-- Analytics & reporting cards -->
        <section class="stats-grid">

            <div class="stat-card">
                <h3>Total Bookings</h3>
                <p><?= $totalAppts ?></p>
            </div>

            <div class="stat-card stat-card-warning">
                <h3>Pending Actions</h3>
                <p><?= $pendingAppts ?></p>
            </div>

            <div class="stat-card stat-card-info">
                <h3>Confirmed Schedule</h3>
                <p><?= $confirmedAppts ?></p>
            </div>

            <div class="stat-card stat-card-success">
                <h3>Realized Revenue</h3>
                <p>R <?= number_format($totalRevenue, 2) ?></p>
            </div>

        </section>

        <!-- Bookings management table (Read, Update Status, Delete) -->
        <div class="data-table-card">

            <h2>Appointments Management</h2>
            <p class="section-subtitle">Update appointment workflows or permanently remove invalid schedule entries.</p>
            
            <div class="table-wrapper">

                <table>

                    <thead>

                        <tr>
                            <th>Ref</th>
                            <th>Customer & Phone</th>
                            <th>Service</th>
                            <th>Assigned Staff</th>
                            <th>Schedule</th>
                            <th>Current Status</th>
                            <th>Actions</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php foreach ($bookings as $b): ?>

                            <tr>
                                <td>#<?= $b['appointment_id'] ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($b['customer_name']) ?></strong><br>
                                    <span class="customer-phone"><?= htmlspecialchars($b['customer_phone']) ?></span>
                                </td>
                                <td><?= htmlspecialchars($b['service_name']) ?> (R <?= number_format($b['price'], 2) ?>)</td>
                                <td><?= htmlspecialchars($b['staff_name']) ?></td>
                                <td><?= htmlspecialchars($b['appointment_date']) ?> @ <?= substr($b['appointment_time'], 0, 5) ?></td>
                                <td><span class="badge badge-<?= $b['status'] ?>"><?= $b['status'] ?></span></td>
                                <td>
                                    <select class="status-select"
                                        onchange="callApi('api/update_status.php', { appointment_id: <?= $b['appointment_id'] ?>, status: this.value })">
                                        <option value="">Change Status...</option>
                                        <option value="Confirmed" <?= $b['status'] === 'Confirmed' ? 'disabled' : '' ?>>Confirm</option>
                                        <option value="Completed" <?= $b['status'] === 'Completed' ? 'disabled' : '' ?>>Complete</option>
                                        <option value="Cancelled" <?= $b['status'] === 'Cancelled' ? 'disabled' : '' ?>>Cancel</option>
                                    </select>

                                    <button class="btn btn-danger btn-delete-sm"
                                        onclick="callApi('api/delete_appointment.php', { appointment_id: <?= $b['appointment_id'] ?> }, 'Permanently delete this booking record?')">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                            
                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        </div>

        <!-- Staff roster and schedule time -->
        <div class="data-table-card">

            <h2>Staff Specialist Rosters</h2>

            <div class="table-wrapper">

                <table>

                    <thead>

                        <tr>
                            <th>Specialist Name</th>
                            <th>Specialty</th>
                            <th>Shift Hours</th>
                            <th>Status</th>
                            <th>Active Workload</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php foreach ($staffList as $stf): ?>

                            <tr>
                                <td><strong><?= htmlspecialchars($stf['full_name']) ?></strong></td>
                                <td><?= htmlspecialchars($stf['specialty']) ?></td>
                                <td><?= substr($stf['shift_start'], 0, 5) ?> - <?= substr($stf['shift_end'], 0, 5) ?></td>
                                <td><span class="badge badge-Confirmed"><?= $stf['status'] ?></span></td>
                                <td><?= $stf['total_bookings'] ?> bookings assigned</td>
                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

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