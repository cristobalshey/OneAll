<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'connect.php'; // DB connection

// Fetch all users
$sql = "SELECT id, first_name, last_name, email, role, status, waste_points FROM users ORDER BY id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>User Management | EcoTrack Admin</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link rel="stylesheet" href="usermanagement.css">
</head>
<body>

  <div class="sidebar">
    <h1>EcoTrack</h1>
    <nav>
      <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
      <a href="usermanagement.php" class="active"><i class="fas fa-users"></i> User Management</a>
      <a href="wastereports.php"><i class="fas fa-recycle"></i> Waste Reports</a>
      <a href="projects.php"><i class="fas fa-leaf"></i> Project</a>
      <a href="feedback.php"><i class="fas fa-comment-dots"></i> Feedback</a>
      <a href="notification.php"><i class="fas fa-bell"></i> Notifications</a>
      <a href="donations.php"><i class="fas fa-hand-holding-heart"></i> Donations</a>
    </nav>
    <div class="sidebar-footer">
      &copy; 2025 EcoTrack Admin
    </div>
  </div>

  <div class="main-content">
    <div class="header">
      <span class="title">User Management</span>
      <div class="header-actions">
        <div class="profile">
          <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Admin">
          <span>Admin</span>
        </div>
      </div>
    </div>

    <!-- 🔍 Search + Filters -->
    <div class="search-filter-section">
      <div class="search-row">
        <input type="text" id="searchInput" placeholder="Search users...">
      </div>
      <div class="filter-row">
        <select id="statusFilter">
          <option value="">All Status</option>
          <option value="approved">Approved</option>
          <option value="pending">Pending</option>
          <option value="banned">Banned</option>
        </select>
        <select id="roleFilter">
          <option value="">All Roles</option>
          <option value="user">User</option>
          <option value="facilitator">Moderator</option>
          <option value="admin">Admin</option>
        </select>
      </div>
    </div>

    <!-- 👥 User Table -->
    <div class="user-table-section">
      <table>
        <thead>
          <tr>
            <th>NAME</th>
            <th>EMAIL</th>
            <th>WASTE POINTS</th>
            <th>ROLE</th>
            <th>STATUS</th>
            <th>ACTIONS</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
              <tr 
                data-id="<?= $row['id'] ?>" 
                data-status="<?= htmlspecialchars($row['status']) ?>" 
                data-role="<?= htmlspecialchars($row['role']) ?>">
                
                <td class="profile-info">
                  <img src="https://randomuser.me/api/portraits/lego/1.jpg" alt="Profile">
                  <span class="name"><?= htmlspecialchars($row['first_name'] . " " . $row['last_name']) ?></span>
                </td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= htmlspecialchars($row['waste_points']) ?></td>
                <td class="role-cell"><?= ucfirst(htmlspecialchars($row['role'])) ?></td>
                <td>
                  <span class="status-badge status-<?= htmlspecialchars($row['status']) ?>">
                    <?= ucfirst($row['status']) ?>
                  </span>
                </td>
                <td class="action-icons">
                  <i class="fas fa-check" title="Approve User" onclick="approveUser(<?= $row['id'] ?>, '<?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?>')"></i>
                  <i class="fas fa-list" title="Activity Log"></i>
                  <i class="fas fa-eye" title="View Details"></i>
                  <i class="fas fa-ban" title="Ban User" onclick="confirmBan(<?= $row['id'] ?>, '<?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?>')"></i>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="6" style="text-align:center;">No users found</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- ✅ JS for Approve/Ban -->
  <script>
    function approveUser(userId, name) {
      if (!confirm(`Approve ${name}?`)) return;
      updateStatus(userId, "approved");
    }

    function confirmBan(userId, name) {
      if (!confirm(`Ban ${name}?`)) return;
      updateStatus(userId, "banned");
    }

    function updateStatus(userId, newStatus) {
      fetch("update_status.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `id=${encodeURIComponent(userId)}&status=${encodeURIComponent(newStatus)}`
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          // find the row and update badge
          const row = document.querySelector(`tr[data-id='${userId}']`);
          if (row) {
            const badge = row.querySelector(".status-badge");
            badge.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
            badge.className = `status-badge status-${newStatus}`;
          }
          alert("User status updated to " + newStatus);
        } else {
          alert("Error: " + data.message);
        }
      })
      .catch(err => {
        console.error("Update failed", err);
        alert("Update failed. Check console for details.");
      });
    }
  </script>
</body>
</html>
