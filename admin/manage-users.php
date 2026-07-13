<?php
session_start();
include('includes/config.php');
date_default_timezone_set('Asia/Kolkata');
$currentTime = date('d-m-Y h:i:s A', time());

// Delete user
if (isset($_GET['del'])) {
    mysqli_query($conn, "DELETE FROM users WHERE id = '" . $_GET['id'] . "'");
    $_SESSION['delmsg'] = "User deleted!";
}

// Get search and sort values
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : '';

// WHERE clause
$where = "";
if ($search !== '') {
    $escapedSearch = mysqli_real_escape_string($conn, $search);
    $where = "WHERE name LIKE '%$escapedSearch%' OR email LIKE '%$escapedSearch%'";
}

// ORDER BY clause
$order = "ORDER BY id DESC"; // default
switch ($sort) {
    case 'name_asc':
        $order = "ORDER BY name ASC";
        break;
    case 'name_desc':
        $order = "ORDER BY name DESC";
        break;
    case 'date_new':
        $order = "ORDER BY regDate DESC";
        break;
    case 'date_old':
        $order = "ORDER BY regDate ASC";
        break;
}

// Final SQL Query
$sql = "SELECT * FROM users $where $order";
$query = mysqli_query($conn, $sql);

// Get all emails for bulk send
$emailQuery = mysqli_query($conn, "SELECT email FROM users");
$emails = [];
while ($erow = mysqli_fetch_assoc($emailQuery)) {
    if (!empty($erow['email'])) {
        $emails[] = $erow['email'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin | Manage Users</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

  <style>
    body { background-color: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
    .page-header { background: #343a40; color: #ffc107; font-weight: 600; font-size: 18px; padding: 15px 20px; margin-bottom: 0; }
    .container { padding: 20px; }
    .table th, .table td { vertical-align: middle; }
    .btn-sm { padding: 4px 10px; font-size: 0.85rem; }
    .table-responsive { margin-top: 20px; }
    .progress { height: 25px; margin-top: 10px; }
  </style>
</head>
<body>
<?php include_once('includes/main-header.php'); ?>

<div class="container">

  <!-- Bulk Email Sender -->
  <div class="card mb-4">
    <div class="card-header bg-dark text-warning"><i class="fas fa-envelope"></i> Bulk Email Sender</div>
    <div class="card-body">
      <form id="bulkEmailForm">
        <div class="mb-3">
          <label class="form-label">Email Subject</label>
          <input type="text" name="subject" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Email Message (Ad)</label>
          <textarea name="message" rows="5" class="form-control" placeholder="Write your advertisement here..." required></textarea>
        </div>
        <button type="button" class="btn btn-primary" id="sendEmailsBtn">
          <i class="fas fa-paper-plane"></i> Send to All Users
        </button>
      </form>
      <!-- Progress Bar -->
      <div class="progress mt-3 d-none" id="progressWrapper">
        <div class="progress-bar progress-bar-striped progress-bar-animated" id="progressBar" style="width:0%">0%</div>
      </div>
      <div id="emailStatus" class="mt-2 text-success"></div>
    </div>
  </div>

  <!-- Users Table -->
  <div class="table-responsive">
    <table class="table table-striped table-bordered align-middle text-center">
      <thead class="table-dark">
        <tr>
          <th>#</th><th>Name</th><th>Email</th><th>Contact</th><th>Shipping Address</th><th>Registered</th><th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php $cnt=1; while ($row=mysqli_fetch_assoc($query)): ?>
        <tr>
          <td><?= $cnt++; ?></td>
          <td><?= htmlentities($row['name']); ?></td>
          <td><?= htmlentities($row['email']); ?></td>
          <td><?= htmlentities($row['contactno']); ?></td>
          <td><?= htmlentities($row['shippingAddress']).", ".htmlentities($row['shippingCity']).", ".htmlentities($row['shippingState'])." - ".htmlentities($row['shippingPincode']); ?></td>
          <td><?= htmlentities($row['regDate']); ?></td>
          <td>
            <a href="manage-users.php?del=1&id=<?= $row['id']; ?>" class="btn btn-sm btn-danger"
               onclick="return confirm('Are you sure you want to delete this user?')">
              <i class="fas fa-trash-alt"></i> Delete
            </a>
          </td>
        </tr>
        <?php endwhile; ?>
        <?php if (mysqli_num_rows($query)===0): ?>
          <tr><td colspan="7">No users found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
const emails = <?= json_encode($emails); ?>;
document.getElementById('sendEmailsBtn').addEventListener('click', function(){
    const subject = document.querySelector('[name="subject"]').value.trim();
    const message = document.querySelector('[name="message"]').value.trim();
    if(!subject || !message){ alert("Please fill subject and message."); return; }

    let total = emails.length;
    let sent = 0;
    const progressWrapper = document.getElementById('progressWrapper');
    const progressBar = document.getElementById('progressBar');
    const statusDiv = document.getElementById('emailStatus');

    progressWrapper.classList.remove('d-none');
    statusDiv.innerHTML = "Sending emails...";

    function sendNext(){
        if(sent >= total){
            statusDiv.innerHTML = "✅ All emails sent successfully!";
            progressBar.classList.remove('progress-bar-animated');
            return;
        }
        let email = emails[sent];
        fetch('send_bulk_email.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'email='+encodeURIComponent(email)+'&subject='+encodeURIComponent(subject)+'&message='+encodeURIComponent(message)
        })
        .then(r=>r.text())
        .then(res=>{
            sent++;
            let percent = Math.round((sent/total)*100);
            progressBar.style.width = percent+'%';
            progressBar.textContent = percent+'%';
            sendNext();
        })
        .catch(err=>{
            console.error("Error sending to "+email, err);
            sent++;
            sendNext();
        });
    }
    sendNext();
});
</script>
</body>
</html>
