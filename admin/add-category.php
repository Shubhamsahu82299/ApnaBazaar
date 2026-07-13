<?php
include('includes/main-header.php');

// DB Connection
include_once('includes/config.php');

// Add Category
if (isset($_POST['add_category'])) {
    $category = trim($_POST['category']);
    $description = trim($_POST['description']);
    if (!empty($category) && !empty($description)) {
        $stmt = $conn->prepare("INSERT INTO category (categoryName, categoryDescription) VALUES (?, ?)");
        $stmt->bind_param("ss", $category, $description);
        $stmt->execute() ? $_SESSION['success'] = "✅ Category added!" : $_SESSION['error'] = "❌ Failed!";
        $stmt->close();
    } else {
        $_SESSION['error'] = "❌ All fields required.";
    }
    redirectToSelf();
}

// Delete Category
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM category WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute() ? $_SESSION['success'] = "🗑️ Deleted!" : $_SESSION['error'] = "❌ Error!";
    $stmt->close();
     redirectToSelf();
}

// Edit Category
if (isset($_POST['update_category'])) {
    $id = intval($_POST['id']);
    $category = trim($_POST['category']);
    $description = trim($_POST['description']);
    if (!empty($category) && !empty($description)) {
        $stmt = $conn->prepare("UPDATE category SET categoryName=?, categoryDescription=? WHERE id=?");
        $stmt->bind_param("ssi", $category, $description, $id);
        $stmt->execute() ? $_SESSION['success'] = "✏️ Updated!" : $_SESSION['error'] = "❌ Update failed.";
        $stmt->close();
    } else {
        $_SESSION['error'] = "❌ All fields required.";
    }
    redirectToSelf();;
}

// Fetch all categories
$categories = [];
$res = $conn->query("SELECT * FROM category ORDER BY id DESC");
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $categories[] = $row;
    }
}

// If editing
$editData = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM category WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $editData = $res->fetch_assoc();
    $stmt->close();
}

$conn->close();

// Messages
$successMsg = $_SESSION['success'] ?? "";
$errorMsg = $_SESSION['error'] ?? "";
unset($_SESSION['success'], $_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Category Manager</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .main-box { margin-top: 30px; }
        .card { border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.05); }
        .form-label { font-weight: 600; }
        .toggle-btn {
            display: none;
            margin-bottom: 10px;
        }
        @media (max-width: 768px) {
            .category-list-section {
                display: none;
            }
            .toggle-btn {
                display: block;
            }
        }
    </style>
</head>
<body>

<?php if ($successMsg): ?>
    <div class="alert alert-success text-center m-3"><?= $successMsg ?></div>
<?php elseif ($errorMsg): ?>
    <div class="alert alert-danger text-center m-3"><?= $errorMsg ?></div>
<?php endif; ?>

<div class="container main-box">
    <div class="row g-4">

        <!-- 🔧 Form Section -->
        <div class="col-md-6">
            <div class="card p-4">
                <h5 class="text-center mb-3"><?= $editData ? "✏️ Edit Category" : "➕ Add New Category" ?></h5>
                <form method="post">
                    <?php if ($editData): ?>
                        <input type="hidden" name="id" value="<?= $editData['id'] ?>">
                    <?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label">Category Name</label>
                        <input type="text" name="category" class="form-control" required
                               value="<?= $editData ? htmlspecialchars($editData['categoryName']) : '' ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category Description</label>
                        <textarea name="description" class="form-control" rows="3" required><?= $editData ? htmlspecialchars($editData['categoryDescription']) : '' ?></textarea>
                    </div>
                    <button type="submit" name="<?= $editData ? 'update_category' : 'add_category' ?>" class="btn btn-primary w-100">
                        <?= $editData ? 'Update Category' : 'Add Category' ?>
                    </button>
                </form>
            </div>
        </div>

        <!-- 📂 Category List -->
        <div class="col-md-6">
            <button class="btn btn-secondary w-100 toggle-btn" onclick="toggleList()">📂 Show/Hide Categories</button>
            <div class="card p-3 category-list-section">
                <h5 class="text-center mb-2">All Categories</h5>
                <?php if (count($categories) > 0): ?>
                    <ul class="list-group" id="categoryList">
                        <?php foreach ($categories as $cat): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-start">
                                <div>
                                    <strong><?= htmlspecialchars($cat['categoryName']) ?></strong><br>
                                    <small><?= htmlspecialchars($cat['categoryDescription']) ?></small>
                                </div>
                                <div>
                                    <a href="?edit=<?= $cat['id'] ?>" class="btn btn-sm text-primary">✏️</a>
                                    <a href="?delete=<?= $cat['id'] ?>" onclick="return confirm('Delete?')" class="btn btn-sm text-danger">🗑️</a>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-muted text-center">No categories yet.</p>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleList() {
    const section = document.querySelector('.category-list-section');
    section.style.display = section.style.display === 'none' ? 'block' : 'none';
}

// ✅ Working search
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById("searchInput");
    searchInput.addEventListener("input", function () {
        const filter = this.value.toLowerCase();
        const items = document.querySelectorAll("#categoryList li");
        items.forEach(li => {
            const text = li.textContent.toLowerCase();
            li.style.display = text.includes(filter) ? '' : 'none';
        });
    });
});

// ✅ Auto-hide alerts
setTimeout(() => {
    const alert = document.querySelector('.alert');
    if (alert) alert.remove();
}, 2000);
</script>

</body>
</html>
