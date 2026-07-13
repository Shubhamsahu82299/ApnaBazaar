<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Slide to Pay</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body style="margin:0; padding:0; background:#f5f5f7; font-family:Roboto, sans-serif;">

<div style="max-width:480px; margin:50px auto; padding:0 15px; box-sizing:border-box;">
  <form method="post">
    <input type="hidden" name="submit" value="1" id="realSubmit">

    <div id="sliderContainer" style="
      position: relative;
      width: 100%;
      height: 55px;
      background: #e0e0e0;
      border-radius: 30px;
      overflow: hidden;
      cursor: pointer;
      touch-action: pan-y;
      user-select: none;
    "
      onmousedown="startSlide(event)"
      ontouchstart="startSlide(event)"
      onmousemove="moveSlide(event)"
      ontouchmove="moveSlide(event)"
      onmouseup="endSlide(event)"
      ontouchend="endSlide(event)"
    >
      <div id="sliderThumb" style="
        position: absolute;
        width: 55px;
        height: 55px;
        background: #0d6efd;
        border-radius: 50%;
        top: 0;
        left: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.3s;
      ">
        <i class="fa fa-arrow-right" style="color:#fff; font-size:1.3rem;"></i>
      </div>
      <span id="sliderText" style="
        position: absolute;
        width: 100%;
        text-align: center;
        line-height: 55px;
        font-size:1.1rem;
        font-weight:600;
        color:#555;
        pointer-events:none;
      ">Slide to Pay</span>
    </div>
  </form>
</div>

<script>
  let isSliding = false;
  let hasMoved = false;
  let startX = 0;
  let thumb, container;

  function startSlide(e) {
    e.preventDefault();
    isSliding = true;
    hasMoved = false;
    thumb = document.getElementById('sliderThumb');
    container = document.getElementById('sliderContainer');
    startX = (e.touches ? e.touches[0].clientX : e.clientX) - thumb.offsetLeft;
  }

  function moveSlide(e) {
    if (!isSliding) return;
    let clientX = e.touches ? e.touches[0].clientX : e.clientX;
    let offset = clientX - startX;
    offset = Math.max(0, Math.min(offset, container.clientWidth - thumb.clientWidth));
    thumb.style.left = offset + 'px';
    if (offset > 5) hasMoved = true;
    if (offset >= container.clientWidth - thumb.clientWidth - 5) {
      completeSlide();
    }
  }

  function endSlide() {
    if (!isSliding) return;
    isSliding = false;
    if (!hasMoved) {
      completeSlide();
    } else {
      thumb.style.left = '0px';
    }
  }

  function completeSlide() {
    isSliding = false;
    const text = document.getElementById('sliderText');
    text.innerText = 'Processing Payment...';
    thumb.style.background = '#28a745';
    thumb.innerHTML = '<i class="fa fa-check"></i>';
    container.style.pointerEvents = 'none';
    setTimeout(() => {
      document.getElementById('realSubmit').closest('form').submit();
    }, 500);
  }
</script>

</body>
</html>