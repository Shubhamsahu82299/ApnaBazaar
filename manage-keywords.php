<?php
include('includes/config.php');
session_start();

// Add new keyword
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_keyword'])) {
    $productId = intval($_POST['product_id']);
    $keyword = trim($_POST['keyword']);

    if ($productId && $keyword != '') {
        $stmt = $con->prepare("INSERT INTO product_keywords (product_id, keyword) VALUES (?, ?)");
        $stmt->bind_param("is", $productId, $keyword);
        $stmt->execute();
        $message = "Keyword added successfully.";
    } else {
        $error = "Please select a product and enter a keyword.";
    }
}

// Delete keyword
if (isset($_GET['delete'])) {
    $kid = intval($_GET['delete']);
    $con->query("DELETE FROM product_keywords WHERE id = $kid");
    header("Location: manage-keywords.php");
    exit;
}

// Fetch all products
$products = [];
$res = $con->query("SELECT id, productName FROM products ORDER BY productName ASC");
while ($row = $res->fetch_assoc()) {
    $products[$row['id']] = $row['productName'];
}

// Fetch keywords
$keywords = [];
$res2 = $con->query("SELECT k.id, k.keyword, k.product_id, p.productName FROM product_keywords k LEFT JOIN products p ON k.product_id = p.id ORDER BY p.productName ASC");
while ($row = $res2->fetch_assoc()) {
    $keywords[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Product Keywords</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>
<body class="container mt-4">
    <h2>Manage Product Keywords</h2>

    <?php if (!empty($message)) echo "<div class='alert alert-success'>$message</div>"; ?>
    <?php if (!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <form method="post" class="form-inline mb-4">
        <div class="form-group mr-2">
            <label>Product:</label>
            <select name="product_id" class="form-control ml-2" required id="productSelect">
    <option value="">Select Product</option>
    <?php foreach ($products as $pid => $pname): ?>
        <option value="<?php echo $pid; ?>" data-name="<?php echo htmlentities($pname); ?>">
            <?php echo htmlentities($pname); ?>
        </option>
    <?php endforeach; ?>
</select>



        </div>

        <div class="form-group mr-2">
            <label>Keyword:</label>
         <input type="text" name="keyword" class="form-control ml-2" required id="keywordInput">   
       
        </div>

        <button type="submit" name="add_keyword" class="btn btn-primary">Add Keyword</button>
    </form>

    <h4>Existing Keywords</h4>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Product</th>
                <th>Keyword</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($keywords as $index => $kw): ?>
                <tr>
                    <td><?php echo $index + 1; ?></td>
                    <td><?php echo htmlentities($kw['productName']); ?></td>
                    <td><?php echo htmlentities($kw['keyword']); ?></td>
                    <td><a href="?delete=<?php echo $kw['id']; ?>" onclick="return confirm('Delete this keyword?');" class="btn btn-sm btn-danger">Delete</a></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($keywords)) echo "<tr><td colspan='4'>No keywords found.</td></tr>"; ?>
        </tbody>
    </table>
</body>
</html>
<script>
    fetch('https://libretranslate.com/translate', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    q: productName,
    source: 'en',
    target: 'hi',
    format: 'text'
  })
})

document.getElementById('productSelect').addEventListener('change', function () {
    const selectedOption = this.options[this.selectedIndex];
    const productName = selectedOption.getAttribute('data-name');

    if (productName) {
        fetch('https://libretranslate.com/translate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                q: productName,
                source: 'en',
                target: 'hi',
                format: 'text'
            })
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('keywordInput').value = data.translatedText;
        })
        .catch(error => {
            console.error("Translation error:", error);
        });
    }
});
</script>

