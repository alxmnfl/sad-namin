  <?php
  session_start();
  ?>
  <!DOCTYPE html>
  <html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Abeth Hardware</title>
    <link rel="stylesheet" href="index.css?v=<?php echo filemtime(__DIR__ . '/index.css'); ?>" />
  </head>
  <body>

    <nav>
      <div class="logo"><strong>Abeth Hardware</strong></div>
      <div class="menu">
        <a href="#">Home</a>
        <a href="#categories">Categories</a>
        <a href="#about">About</a>
        <a href="#contact">Contact</a>

        <?php if (isset($_SESSION['username'])): ?>
          <span>Welcome, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></span>
          <a href="logout.php">Logout</a>
        <?php else: ?>
    <a href="#" onclick="openModal('login-modal'); return false;">Sign In</a> |
    <a href="#" onclick="openModal('register-modal'); return false;">Sign Up</a>
        <?php endif; ?>
      </div>
    </nav>

    <header>
      <h1>Exclusive Range of Hardware Materials</h1>
      <p>Gravel, Sand, Hollow Blocks, Cement, and More</p>

      <?php if (isset($_SESSION['username'])): ?>
        <button onclick="window.location.href='products.php'">Shop Now!</button>
      <?php else: ?>
        <button onclick="openModal('login-modal')">Shop Now!</button>
      <?php endif; ?>
    </header>

    <!-- Categories Section -->
    <section id="categories">
      <h2 class="section-title">Categories</h2>
      <div class="categories">
        <div class="cat-card">
          <img src="istockphoto-92775187-2048x2048.jpg" alt="Gravel">
          <h3>Gravel</h3>
        </div>
        <div class="cat-card">
          <img src="pexels-david-iloba-28486424-17268238.jpg" alt="Sand">
          <h3>Sand</h3>
        </div>
        <div class="cat-card">
          <img src="download.jpg" alt="Hollow Blocks">
          <h3>Hollow Blocks</h3>
        </div>
        <div class="cat-card">
          <img src="shopping.webp" alt="Cement">
          <h3>Cement</h3>
        </div>
      </div>
    </section>

    <!-- ABOUT -->
    <div id="about">
      <div class="about-img">
        <img src="about.jpg" alt="About Abeth Hardware">
      </div>
      <div class="about-text">
        <h2>About Us</h2>
        <p>Abeth Hardware is your trusted supplier of high-quality construction materials. From sand and gravel to cement and steel, we provide durable and affordable products to help you build your dreams. With years of experience in the hardware industry, we are committed to delivering excellent customer service and reliable materials for every project, big or small.</p>
      </div>
    </div>

    <!-- CONTACT -->
    <div id="contact">
      <div class="contact-info">
        <h2>Contact Us</h2>
        <p><b>Address:</b> B3/L11 Tiongquaio St. Manuyo Dos, Las Pinas City.</p>
        <p><b>Phone:</b> +63 966-866-9728 / +63 977-386-8066</p>
        <p><b>Email:</b> abethhardware@gmail.com</p>
        <p><b>Business Hours:</b> Mon–Sat: 8:00 AM – 5:00 PM</p>
      </div>
      <div class="map">
        <img src="map.jpg" alt="Abeth Hardware Location">
      </div>
    </div>

    <footer>
      <p>&copy; 2025 Abeth Hardware. All rights reserved.</p>
    </footer>

    <!-- LOGIN MODAL -->
    <div id="login-modal" class="modal">
      <div class="modal-content">
        <span class="close" onclick="closeModal('login-modal')">&times;</span>
        <h2>Sign In</h2>
        <form method="POST" action="login.php">
          <input type="text" name="username" placeholder="Username or Email" required>
    <div class="password-field">
            <input id="login-password" type="password" name="password" placeholder="Password" required class="modal-input password-input">
            <button type="button" class="password-toggle" onclick="togglePassword('login-password', this)" aria-pressed="false" aria-label="Show password" title="Show password"> 
              <!-- eye icon (open) -->
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z" stroke="#004080" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                <circle cx="12" cy="12" r="3" stroke="#004080" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </button>
          </div>
          <button type="submit">Sign In</button>
          <p>Don’t have an account? <a href="#" onclick="switchModal('login-modal','register-modal')">Sign Up</a></p>
        </form>
      </div>
    </div>

    <!-- REGISTER MODAL -->
    <div id="register-modal" class="modal">
      <div class="modal-content">
        <span class="close" onclick="closeModal('register-modal')">&times;</span>
        <h2>Create Account</h2>
        <form method="POST" action="register.php" id="register-form" autocomplete="off">
          <input type="text" name="fname" placeholder="First Name" required>
          <input type="text" name="lname" placeholder="Last Name" required>
          <input type="text" name="address" placeholder="Address" required>
          <input type="email" name="email" placeholder="Email" required>
          <input type="text" name="username" placeholder="Username" required>
          <div class="password-field">
            <input id="register-password" type="password" name="password" placeholder="Password" required class="modal-input password-input">
            <button type="button" class="password-toggle" onclick="togglePassword('register-password', this)" aria-pressed="false" aria-label="Show password" title="Show password">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z" stroke="#004080" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                <circle cx="12" cy="12" r="3" stroke="#004080" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </button>
          </div>
          <div class="password-field">
            <input id="register-password-confirm" type="password" name="password_confirm" placeholder="Confirm Password" required class="modal-input password-input">
            <button type="button" class="password-toggle" onclick="togglePassword('register-password-confirm', this)" aria-pressed="false" aria-label="Show password" title="Show password">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z" stroke="#004080" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                <circle cx="12" cy="12" r="3" stroke="#004080" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </button>
          </div>
          <div id="register-error" style="color:#c00; font-size:13px; min-height:18px; margin-bottom:2px; display:none;"></div>
          <button type="submit">Create Account</button>
          <p>Already have an account? <a href="#" onclick="switchModal('register-modal','login-modal'); return false;">Login</a></p>
        </form>
      </div>
    </div>

    <script>
      // Modal helpers
      function openModal(id) {
        var el = document.getElementById(id);
        if (!el) return;
        el.style.display = 'flex';
      }

      function closeModal(id) {
        var el = document.getElementById(id);
        if (!el) return;
        el.style.display = 'none';
      }

      function switchModal(fromId, toId) {
        closeModal(fromId);
        openModal(toId);
      }

      // Close modal when clicking outside
      window.onclick = function(event) {
        if (event.target && event.target.classList && event.target.classList.contains('modal')) {
          event.target.style.display = 'none';
        }
      };

      /**
       * Toggle password visibility for a given input id.
       * Swaps input type and toggles the eye / eye-off icon inside the button.
       * @param {string} targetId - the id of the password input
       * @param {HTMLElement} btn - the button element clicked
       */
      function togglePassword(targetId, btn) {
        var input = document.getElementById(targetId);
        if (!input) return;
        var isHidden = input.type === 'password';
        if (isHidden) {
          input.type = 'text';
          btn.setAttribute('aria-pressed', 'true');
          btn.title = 'Hide password';
          btn.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">' +
            '<path d="M17.94 17.94A10.94 10.94 0 0 1 12 19c-7 0-11-7-11-7a20.24 20.24 0 0 1 5.61-5.44" stroke="#004080" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>' +
            '<path d="M1 1l22 22" stroke="#004080" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>' +
            '</svg>';
        } else {
          input.type = 'password';
          btn.setAttribute('aria-pressed', 'false');
          btn.title = 'Show password';
          btn.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">' +
            '<path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z" stroke="#004080" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>' +
            '<circle cx="12" cy="12" r="3" stroke="#004080" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>' +
            '</svg>';
        }
      }

      // Register form password match validation
      document.addEventListener('DOMContentLoaded', function() {
        var regForm = document.getElementById('register-form');
        if (regForm) {
          var pw = document.getElementById('register-password');
          var pwc = document.getElementById('register-password-confirm');
          var err = document.getElementById('register-error');
          function checkMatch() {
            if (pw.value && pwc.value && pw.value !== pwc.value) {
              err.textContent = 'Passwords do not match.';
              err.style.display = 'block';
              return false;
            } else {
              err.textContent = '';
              err.style.display = 'none';
              return true;
            }
          }
          pw.addEventListener('input', checkMatch);
          pwc.addEventListener('input', checkMatch);
          regForm.addEventListener('submit', function(e) {
            if (!checkMatch()) {
              pwc.focus();
              e.preventDefault();
            }
          });
        }
      });
    </script>

  </body>
  </html>
