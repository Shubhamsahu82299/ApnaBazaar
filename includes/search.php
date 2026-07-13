<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Blinkit Style Add Button</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    * {
      box-sizing: border-box;
    }

    body {
      font-family: Arial, sans-serif;
      padding: 20px;
    }

    .cart-action {
      display: flex;
      align-items: center;
      justify-content: center;
      flex: 1;
      margin-bottom: 20px;
    }

    .cart-action .add-btn,
    .cart-action .qty-btn {
      background-color: #fb641b;
      color: #fff;
      border: none;
      font-size: 14px;
      font-weight: 600;
      padding: 6px 12px;
      border-radius: 20px;
      cursor: pointer;
      box-shadow: 0 2px 5px rgba(0,0,0,0.15);
      transition: 0.3s;
    }

    .cart-action .qty-container {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .cart-action .qty-btn {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      font-size: 18px;
      padding: 0;
    }

    .cart-action .qty-value {
      font-weight: bold;
      min-width: 20px;
      text-align: center;
      font-size: 16px;
    }

    @media (max-width: 480px) {
      .cart-action .add-btn,
      .cart-action .qty-btn {
        font-size: 13px;
        padding: 5px 10px;
      }

      .cart-action .qty-btn {
        width: 28px;
        height: 28px;
      }
    }
  </style>
</head>
<body>

<!-- You can repeat this div for each product -->
<div class="cart-action" data-id="101">
  <button class="add-btn"><i class="fa fa-plus"></i> Add</button>
</div>

<script>
  document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".cart-action").forEach(function (container) {
      const addBtn = container.querySelector(".add-btn");

      addBtn.addEventListener("click", function () {
        showQtyCounter(container, 1);
      });
    });

    function showQtyCounter(container, qty) {
      const id = container.getAttribute("data-id");

      container.innerHTML = `
        <div class="qty-container">
          <button class="qty-btn minus">−</button>
          <span class="qty-value">${qty}</span>
          <button class="qty-btn plus">+</button>
        </div>
      `;

      const minusBtn = container.querySelector(".minus");
      const plusBtn = container.querySelector(".plus");
      const qtyVal = container.querySelector(".qty-value");

      minusBtn.addEventListener("click", function () {
        let q = parseInt(qtyVal.textContent);
        if (q > 1) {
          qtyVal.textContent = q - 1;
        } else {
          container.innerHTML = `<button class="add-btn"><i class="fa fa-plus"></i> Add</button>`;
          container.querySelector(".add-btn").addEventListener("click", function () {
            showQtyCounter(container, 1);
          });
        }
      });

      plusBtn.addEventListener("click", function () {
        let q = parseInt(qtyVal.textContent);
        qtyVal.textContent = q + 1;
      });
    }
  });
</script>

</body>
</html>