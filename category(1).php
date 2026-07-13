<!-- Sidebar: fixed to left, just below header -->
<div>
<div class="hh">
   <?php include('includes/side-menu.php'); ?> 
</div>
</div>
<style>
    /* Fixed Side Menu Styles */
    .hh {
        position: fixed !important;
        left: 0;
        right: 0;
        z-index: 800;
        background: rgba(255, 255, 255, 0.96); /* Premium glass blur opacity */
        backdrop-filter: blur(8px);
        border-bottom: 1px solid #e2e8f0; /* Crisp slate trace line */
        box-shadow: 0 4px 12px -4px rgba(0, 0, 0, 0.03);
    }
    
    /* Add padding to body to prevent content overlap */
    body {
        padding-top: 130px !important; 
    }
    
    /* Mobile adjustments */
    @media (max-width: 768px) {
        .hh {
            margin-bottom: 0px;
            padding-bottom: 0px;
            position: fixed !important;
            left: 0;
            right: 0;
            background: #ffffff;
            opacity: 0.98;
            font-weight: 600;
            top: 170px !important;
            z-index: 10;
            border-bottom: 1px solid #f1f5f9;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }
        body {
            padding-top: 220px !important; 
        }
    }
</style>