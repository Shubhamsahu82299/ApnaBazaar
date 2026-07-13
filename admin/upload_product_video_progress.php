<?php
include('includes/config.php');

header('Content-Type: application/json');

if(isset($_POST['upload_video']) && isset($_POST['product_id'])){
    $productId = intval($_POST['product_id']);
    $dir = "productimages/$productId";
    if(!is_dir($dir)) mkdir($dir,0755,true);

    if(isset($_FILES['product_video']) && $_FILES['product_video']['error']==0){
        $ext = strtolower(pathinfo($_FILES['product_video']['name'],PATHINFO_EXTENSION));
        if(!in_array($ext,['mp4','mov','webm','mkv'])){
            echo json_encode(['status'=>'error','message'=>'Invalid video format']); exit;
        }

        $newName = 'product_video_'.time().'.'.$ext;
        $dest = "$dir/$newName";

        if(move_uploaded_file($_FILES['product_video']['tmp_name'],$dest)){
            // Remove old video
            $res = mysqli_query($conn,"SELECT productVideo FROM products WHERE id=$productId");
            $row = mysqli_fetch_assoc($res);
            if($row['productVideo'] && file_exists("$dir/".$row['productVideo'])){
                unlink("$dir/".$row['productVideo']);
            }

            // Update DB
            mysqli_query($conn,"UPDATE products SET productVideo='$newName' WHERE id=$productId");

            echo json_encode(['status'=>'success','message'=>'✅ Video uploaded successfully','video'=>$newName]);
            exit;
        }
    }
}

echo json_encode(['status'=>'error','message'=>'❌ Failed to upload video']);
?>
