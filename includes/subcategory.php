<div style="">
  <div class="subcat-scroll-container">
    <?php 
      $activeSub = isset($_GET['scid']) ? intval($_GET['scid']) : 0;
      $sql = mysqli_query($con, "SELECT id, subcategory FROM subcategory WHERE categoryid='$cid'");
      while ($row = mysqli_fetch_array($sql)) { 
        $isActive = ($activeSub == $row['id']) ? 'active-subcat' : '';
        echo "<a href='sub-category.php?scid={$row['id']}' class='subcat-item $isActive'>
                <i class='fa fa-tag'></i>".htmlentities($row['subcategory'])."
              </a>";
      }
    ?>
  </div>
  
</div>

<style>
  .cat-header {
    font-size: 16px;
    font-weight: 600;
    margin: 10px 12px 4px;
    color: #333;
  }

  .subcat-scroll-container {
    overflow-x: auto;
    white-space: nowrap;
    background-color: transparent;
    border-bottom: 1px solid #eee;
    margin:12px 0px;
    padding: 0px 0px;
    -webkit-overflow-scrolling: touch;
  }

  .subcat-scroll-container::-webkit-scrollbar {
    display: none;
  }

  .subcat-item {
    display: inline-flex;
    align-items: center;
    padding: 6px 14px;
    margin: 3px;
    border-radius: 16px !important;
    background-color: #f1f3f6;
    color: #333;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    border: 1px solid transparent;
    transition: all 0.25s ease-in-out;
    box-shadow: 0 0 0 rgba(0,0,0,0);
  }

  .subcat-item i {
    margin-right: 6px;
    font-size: 13px;
  }

  .subcat-item:hover {
    background-color: #e6e9ef;
    color: #000;
    transform: scale(1.03);
  }

  .active-subcat {
    background-color: #fdee68ff !important; /* Change this to black */
  color: #fa0505ff !important;
  border-color: #2874f0;
  box-shadow: 0 4px 10px rgba(40, 116, 240, 0.15);
  animation: subcatSelectGlow 1.2s ease;
  }

  
</style>
