<?php
include_once('includes/config.php');

echo "<h2>Running SQL Setup for Stock Management</h2>";

// Read SQL file
$sqlFile = 'add-stock-management-column.sql';
if (file_exists($sqlFile)) {
    $sql = file_get_contents($sqlFile);
    
    // Split SQL into individual queries
    $queries = explode(';', $sql);
    
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>Executing SQL Queries:</h3>";
    
    foreach ($queries as $query) {
        $query = trim($query);
        if (!empty($query) && !str_starts_with($query, '--')) {
            echo "<p><strong>Query:</strong> " . htmlspecialchars($query) . "</p>";
            
            $result = mysqli_query($conn, $query);
            
            if ($result) {
                echo "<p style='color: green;'>✅ Success</p>";
                
                // If it's a SELECT query, show results
                if (strtoupper(substr(trim($query), 0, 6)) === 'SELECT') {
                    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
                    $first = true;
                    while ($row = mysqli_fetch_assoc($result)) {
                        if ($first) {
                            echo "<tr>";
                            foreach ($row as $key => $value) {
                                echo "<th>" . htmlspecialchars($key) . "</th>";
                            }
                            echo "</tr>";
                            $first = false;
                        }
                        echo "<tr>";
                        foreach ($row as $value) {
                            echo "<td>" . htmlspecialchars($value) . "</td>";
                        }
                        echo "</tr>";
                    }
                    echo "</table>";
                }
            } else {
                echo "<p style='color: red;'>❌ Error: " . mysqli_error($conn) . "</p>";
            }
            echo "<hr>";
        }
    }
    echo "</div>";
    
    echo "<h3>Setup Complete!</h3>";
    echo "<p><a href='edit-products.php?id=1' class='btn btn-primary'>Test Edit Product</a></p>";
    
} else {
    echo "<p style='color: red;'>❌ SQL file not found: $sqlFile</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
.btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; }
.btn:hover { background: #0056b3; }
</style> 