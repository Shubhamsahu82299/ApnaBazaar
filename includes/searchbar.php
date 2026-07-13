<!-- Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
  .search-bar-wrapper {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    width: 100%;
    max-width: 480px; /* Dynamic constraint */
    flex-grow: 1;
    min-width: 0;
  }

  .search-bar {
    width: 100%;
  }

  .search-bar form {
    display: flex;
    align-items: center;
    background: #f1f5f9; /* Modern Slate-100 Background */
    border: 1px solid #e2e8f0; /* Subtle Border */
    border-radius: 10px; /* Matching your button's radius */
    padding: 0 12px;
    height: 38px; /* Strict matching height for actions-container elements */
    box-sizing: border-box;
    transition: all 0.2s ease;
  }

  /* Focus handling for premium input feel */
  .search-bar form:focus-within {
    background: #ffffff;
    border-color: #0d9488; /* Teal brand color focus */
    box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.15);
  }

  .search-bar input[type="text"] {
    flex: 1;
    border: none;
    background: transparent;
    font-size: 13px;
    font-weight: 500;
    color: #1e293b;
    padding: 0 8px;
    outline: none;
    height: 100%;
  }

  .search-bar input[type="text"]::placeholder {
    color: #94a3b8;
    font-weight: 500;
  }

  .search-bar button {
    background: transparent;
    border: none;
    padding: 0 6px;
    cursor: pointer;
    color: #64748b;
    font-size: 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: color 0.2s;
    height: 100%;
  }

  .search-bar button:hover {
    color: #0f172a;
  }

  .mic-btn {
    border-left: 1px solid #cbd5e1 !important; /* Left separator split line */
    padding-left: 10px !important;
    margin-left: 4px;
  }

  .mic-btn.recording {
    color: #ef4444;
    animation: pulse 1s infinite;
  }

  @keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.4; }
    100% { opacity: 1; }
  }

  @media (max-width: 768px) {
    .search-bar-wrapper {
      max-width: 100%; /* Spans full horizontal scale on mobile row breakdown */
      margin: 4px 0;
    }

    .search-bar form {
      height: 36px; /* Smooth down scaling for mobile canvas */
    }

    .search-bar input[type="text"] {
      font-size: 12px;
    }
  }
  /* Container Box & Alignment Grid - Laptop View Fix */
/* --- SEARCH BAR PREMIUM LAYOUT FIXES --- */

.search-bar-wrapper {
  display: flex !important;
  align-items: center;
  justify-content: flex-start;
  width: 100%;
  max-width: 680px; /* Laptop par size 480px se badha kar 680px kar diya hai */
  flex: 1; 
  min-width: 0;
}

.search-bar {
  width: 100%;
}

.search-bar form {
  display: flex;
  align-items: center;
  background: #f1f5f9; 
  border: 1px solid #e2e8f0; 
  border-radius: 10px; 
  padding: 8px 14px; /* Internal height underflow ko rokne ke liye strict vertical tracking di hai */
  min-height: 40px; /* Form dynamic text scalable bounds hold karega */
  width: 100%;
  box-sizing: border-box;
  transition: all 0.2s ease;
}

/* Premium dynamic interaction focus state */
.search-bar form:focus-within {
  background: #ffffff;
  border-color: #0d9488; 
  box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.15);
}

.search-bar input[type="text"] {
  flex: 1;
  border: none;
  background: transparent;
  font-size: 14px;
  font-weight: 500;
  color: #1e293b;
  padding: 0 12px;
  outline: none;
  width: 100%;
  min-width: 0; /* Text container overflow strict blocking */
}

.search-bar input[type="text"]::placeholder {
  color: #94a3b8;
  font-weight: 500;
}

.search-bar button {
  background: transparent;
  border: none;
  padding: 0 4px;
  cursor: pointer;
  color: #64748b;
  font-size: 15px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: color 0.2s;
}

.search-bar button:hover {
  color: #0f172a;
}

.mic-btn {
  border-left: 1px solid #cbd5e1 !important; 
  padding-left: 12px !important;
  margin-left: 6px;
}

/* --- MOBILE RESPONSIVE ADAPTATION --- */
@media (max-width: 768px) {
  .search-bar-wrapper {
    max-width: 100%; 
    margin: 4px 0;
  }

  .search-bar form {
    padding: 6px 10px;
    min-height: 36px; 
  }

  .search-bar input[type="text"] {
    font-size: 13px;
    padding: 0 8px;
  }
}
</style>

<!-- HTML Framework Structure -->
<div class="search-bar-wrapper">
  <div class="search-bar">
    <form method="post" action="search-result">
      <button type="submit" aria-label="Search"><i class="fas fa-search"></i></button>
      <input type="text" name="product" id="searchInput" placeholder="Search for Mango" aria-label="Search products" autocomplete="off" />
      <button type="button" class="mic-btn" onclick="startVoiceSearch()" aria-label="Voice Search">
        <i class="fas fa-microphone"></i>
      </button>
    </form>
  </div>
</div>

<!-- JS Blocks (Same as your logic but safe-guarded focus validation) -->
<script>
  const suggestions = ["Mango", "Banana", "Onion", "Tomato", "Milk", "Bread", "Cold Drink", "Paneer"];
  let index = 0;

  function rotatePlaceholder() {
    const input = document.getElementById('searchInput');
    if (input && document.activeElement !== input) {
      input.placeholder = "Search for " + suggestions[index];
    }
    index = (index + 1) % suggestions.length;
  }

  setInterval(rotatePlaceholder, 2000);
</script>

<script>
  function startVoiceSearch() {
    if ('webkitSpeechRecognition' in window) {
      const micBtn = document.querySelector('.mic-btn');
      micBtn.classList.add('recording');

      const recognition = new webkitSpeechRecognition();
      recognition.lang = 'en-IN';
      recognition.interimResults = false;
      recognition.maxAlternatives = 1;

      recognition.onresult = function (event) {
        const transcript = event.results[0][0].transcript;
        document.getElementById('searchInput').value = transcript;
        document.querySelector('.search-bar form').submit(); 
      };

      recognition.onerror = function () {
        alert("Voice input failed. Try again.");
      };

      recognition.onend = function () {
        micBtn.classList.remove('recording');
      };

      recognition.start();
    } else {
      alert("Your browser doesn't support voice search.");
    }
  }
</script>