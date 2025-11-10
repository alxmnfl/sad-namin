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
        <button class="shop-now" onclick="window.location.href='products.php'">Shop Now!</button>
      <?php else: ?>
        <button class="shop-now" onclick="openModal('login-modal')">Shop Now!</button>
      <?php endif; ?>
    </header>

    <!-- Categories Section -->
    <section id="categories">
      <h2 class="section-title">Categories</h2>
      <div class="categories">
        <div class="cat-card">
          <img src="istockphoto-92775187-2048x2048.jpg" alt="Gravel">
          <h3>Gravel</h3>
          <p class="info">Used for construction and road building</p>
          <div class="hover-content">
            <h3>Gravel</h3>
            <p>High-quality construction gravel available in various sizes. Perfect for driveways, landscaping, and construction projects. Our gravel is carefully screened and washed.</p>
            <button class="buy-now" onclick="checkLoginAndRedirect('products.php')">Buy Now</button>
          </div>
        </div>

        <div class="cat-card">
          <img src="pexels-david-iloba-28486424-17268238.jpg" alt="Sand">
          <h3>Sand</h3>
          <p class="info">Fine aggregate for construction</p>
          <div class="hover-content">
            <h3>Sand</h3>
            <p>Premium construction sand suitable for concrete mixing, plastering, and masonry work. Clean, well-graded, and meets industry standards.</p>
            <button class="buy-now" onclick="checkLoginAndRedirect('products.php')">Buy Now</button>
          </div>
        </div>

        <div class="cat-card">
          <img src="download.jpg" alt="Hollow Blocks">
          <h3>Hollow Blocks</h3>
          <p class="info">Durable building blocks</p>
          <div class="hover-content">
            <h3>Hollow Blocks</h3>
            <p>Strong and reliable hollow blocks perfect for walls and foundations. Available in different sizes and load-bearing capacities.</p>
            <button class="buy-now" onclick="checkLoginAndRedirect('products.php')">Buy Now</button>
          </div>
        </div>

        <div class="cat-card">
          <img src="shopping.webp" alt="Cement">
          <h3>Cement</h3>
          <p class="info">High-strength cement mix</p>
          <div class="hover-content">
            <h3>Cement</h3>
            <p>Quality cement for all your construction needs. Fast-setting and durable, suitable for foundations, walls, and general masonry work.</p>
            <button class="buy-now" onclick="checkLoginAndRedirect('products.php')">Buy Now</button>
          </div>
        </div>
      </div>
      <div class="categories-footer">
        <a href="products.php" class="more-link">More</a>
      </div>
    </section>

    <!-- ABOUT -->
    <div id="about">
      <div class="about-img">
        <img src="images/about-us.jpg" alt="About Abeth Hardware">
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
      // Login check and redirect
      function checkLoginAndRedirect(destination) {
        <?php if (isset($_SESSION['username'])): ?>
          window.location.href = destination;
        <?php else: ?>
          openModal('login-modal');
        <?php endif; ?>
      }
      
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

      // Shop Now and About scroll animations
    document.addEventListener('DOMContentLoaded', function() {
      // If user prefers reduced motion, do not run animations
      var prefersReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      if (prefersReduced) {
        document.querySelectorAll('.shop-now').forEach(function(b){ b.style.animation = 'none'; });
      }

      // About scroll reveal using IntersectionObserver and typing animation (replays on re-entry)
      try {
        var about = document.getElementById('about');
        if (about) {
          // start hidden
          about.classList.add('about-hidden');

          var p = about.querySelector('.about-text p');
          if (p) {
            p.setAttribute('data-original-text', p.textContent.trim());
            if (!prefersReduced) p.textContent = '';
          }

          var isTyping = false;
          var typingInterval = null;

          var io = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
              var target = entry.target;
              var textContainer = target.querySelector('.about-text');
              var para = textContainer ? textContainer.querySelector('p') : null;

              if (entry.isIntersecting) {
                // reveal container
                target.classList.add('in-view');
                if (textContainer) setTimeout(function(){ textContainer.classList.add('in-view'); }, 80);

                if (!para) return;
                // if reduced motion, show immediately
                if (prefersReduced) {
                  if (!para.getAttribute('data-typed')) {
                    para.textContent = para.getAttribute('data-original-text') || para.textContent;
                    para.setAttribute('data-typed','1');
                  }
                  return;
                }

                // start typing only if not already typed or typing
                if (!para.getAttribute('data-typed') && !isTyping) {
                  isTyping = true;
                  para.classList.add('typing');
                  var original = para.getAttribute('data-original-text') || '';
                  var i = 0;
                  var speed = 8; // faster ms per character (reduced for snappier typing)

                  var revealImmediately = function() {
                    if (typingInterval) clearInterval(typingInterval);
                    para.textContent = original;
                    para.classList.remove('typing');
                    para.setAttribute('data-typed','1');
                    isTyping = false;
                    para.removeEventListener('click', revealImmediately);
                  };

                  para.addEventListener('click', revealImmediately);
                  typingInterval = setInterval(function() {
                    i++;
                    para.textContent = original.slice(0, i);
                    if (i >= original.length) {
                      clearInterval(typingInterval);
                      para.classList.remove('typing');
                      para.setAttribute('data-typed','1');
                      isTyping = false;
                      para.removeEventListener('click', revealImmediately);
                    }
                  }, speed);
                }
              } else {
                // left the viewport — reset so typing can replay next time
                if (prefersReduced) return;
                if (!textContainer) return;
                if (isTyping) {
                  clearInterval(typingInterval);
                  isTyping = false;
                }
                if (para) {
                  para.textContent = '';
                  para.classList.remove('typing');
                  para.removeAttribute('data-typed');
                }
                target.classList.remove('in-view');
                textContainer.classList.remove('in-view');
              }
            });
          }, { threshold: 0.35 });

          io.observe(about);
        }
      } catch (e) {
        // IntersectionObserver not supported — fallback: reveal immediately
        var about = document.getElementById('about');
        if (about) {
          about.classList.add('in-view');
          var p = about.querySelector('.about-text p');
          if (p) p.textContent = p.getAttribute('data-original-text') || p.textContent;
        }
      }

      // Align about/contact decorative panels with the first and last visible category card
      (function alignPanels(){
        var cats = document.querySelectorAll('.cat-card');
        var aboutEl = document.getElementById('about');
        var contactEl = document.getElementById('contact');
        if (!aboutEl) return;

        function update() {
          var aboutRect = aboutEl.getBoundingClientRect();

          // Prefer aligning to the contact block if it exists (keeps both borders visually matched)
          if (contactEl) {
            var contactRect = contactEl.getBoundingClientRect();
            var aboutLeft = Math.max(0, contactRect.left - aboutRect.left);
            var aboutWidth = Math.max(0, contactRect.width);

            aboutEl.style.setProperty('--panel-left', aboutLeft + 'px');
            aboutEl.style.setProperty('--panel-width', aboutWidth + 'px');
            aboutEl.style.setProperty('--panel-translate', '0');
            return;
          }

          // Fallback: align to category area if contact not present
          if (!cats || cats.length === 0) return;
          var minLeft = Infinity, maxRight = -Infinity;
          cats.forEach(function(c){
            var r = c.getBoundingClientRect();
            if (r.width === 0) return; // hidden
            minLeft = Math.min(minLeft, r.left);
            maxRight = Math.max(maxRight, r.right);
          });
          if (!isFinite(minLeft)) return;

          var aboutLeft2 = Math.max(0, minLeft - aboutRect.left);
          var aboutWidth2 = Math.max(0, maxRight - minLeft);
          aboutEl.style.setProperty('--panel-left', aboutLeft2 + 'px');
          aboutEl.style.setProperty('--panel-width', aboutWidth2 + 'px');
          aboutEl.style.setProperty('--panel-translate', '0');
        }

        // run initially and on resize/scroll (debounced)
        var t;
        function schedule(){ clearTimeout(t); t=setTimeout(update, 60); }
        update();
        window.addEventListener('resize', schedule);
        window.addEventListener('orientationchange', schedule);
        // some layouts change on images load
        window.addEventListener('load', schedule);
      })();
    });
    </script>

  </body>
  </html>
