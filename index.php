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
      
      <!-- Burger Icon -->
      <div class="burger" onclick="toggleHomeMenu()">
        ☰
      </div>
      
      <div class="menu" id="homeNavMenu">
        <a href="#categories">Categories</a>
        <a href="#about">About</a>
        <a href="#contact">Contact</a>

        <?php if (isset($_SESSION['username'])): ?>
          <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
            <a href="admin.php" style="background: #ffcc00; color: #004080; padding: 6px 12px; border-radius: 6px; font-weight: bold;">Admin</a>
          <?php endif; ?>
          <a href="#" onclick="openModal('logout-modal'); return false;" class="logout-btn">Logout</a>
        <?php else: ?>
    <a href="#" onclick="openModal('register-modal'); return false;">Sign Up</a>
    <a href="#" onclick="openModal('login-modal'); return false;">Login</a>
        <?php endif; ?>
      </div>
    </nav>
    
<script>
function toggleHomeMenu() {
  document.getElementById("homeNavMenu").classList.toggle("active");
}
</script>

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
        <a href="products.php?category=Gravel" class="cat-card">
          <img src="istockphoto-92775187-2048x2048.jpg" alt="Gravel">
          <h3>Gravel</h3>
        </a>
        <a href="products.php?category=Sand" class="cat-card">
          <img src="pexels-david-iloba-28486424-17268238.jpg" alt="Sand">
          <h3>Sand</h3>
        </a>
        <a href="products.php?category=Hollow Blocks" class="cat-card">
          <img src="download.jpg" alt="Hollow Blocks">
          <h3>Hollow Blocks</h3>
        </a>
        <a href="products.php?category=Cement" class="cat-card">
          <img src="shopping.webp" alt="Cement">
          <h3>Cement</h3>
        </a>
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


    <div id="contact" style="display: flex; flex-wrap: wrap; align-items: flex-start; gap: 24px; margin-bottom: 24px;">
      <div class="contact-info" style="flex: 1 1 220px; min-width: 200px;">
        <h2>Contact Us</h2>
        <p><b>Address:</b> B3/L11 Tiongquaio St. Manuyo Dos, Las Pinas City.</p>
        <p><b>Phone:</b> +63 966-866-9728 / +63 977-386-8066</p>
        <p><b>Email:</b> abethhardware@gmail.com</p>
        <p><b>Business Hours:</b> Mon–Sat: 8:00 AM – 5:00 PM</p>
      </div>
  <div class="contact-placeholder" style="width:340px;height:240px;background:#f2f2f2;border:2px dashed #bbb;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#bbb;font-size:16px;min-width:180px;margin-left:-60px;margin-right:64px;">
    <style>
      @media (max-width: 900px) {
        .contact-placeholder {
          margin-left: 0 !important;
          width: 100% !important;
          max-width: 100%;
          height: 180px !important;
          margin-top: 16px;
        }
        #contact {
          flex-direction: column;
        }
      }
    </style>
        <!-- Placeholder for image or map -->
      </div>
    </div>

    <footer>
      <p>&copy; 2025 Abeth Hardware. All rights reserved.</p>
    </footer>

    <!-- LOGIN MODAL -->
    <div id="login-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
      <div style="background: white; padding: 30px; border-radius: 8px; max-width: 400px; width: 90%; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <div style="position: relative; margin-bottom: 20px;">
          <h2 style="color: #004080; margin: 0; text-align: center;">Login</h2>
          <span onclick="closeModal('login-modal')" style="position: absolute; top: -5px; right: -10px; font-size: 28px; cursor: pointer; color: #666;">&times;</span>
        </div>
        <form method="POST" action="login.php">
          <input type="text" name="username" placeholder="Username or Email" required style="width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;">
          <input type="password" name="password" placeholder="Password" required style="width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;">
          <button type="submit" style="width: 100%; padding: 12px; background: #004080; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 16px;">Login</button>
          <p style="text-align: center; margin-top: 15px; color: #666;">Don't have an account? <a href="#" onclick="switchModal('login-modal','register-modal'); return false;" style="color: #004080; font-weight: bold;">Sign Up</a></p>
        </form>
      </div>
    </div>

    <!-- REGISTER MODAL -->
    <div id="register-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
      <div style="background: white; padding: 30px; border-radius: 8px; max-width: 400px; width: 90%; box-shadow: 0 4px 6px rgba(0,0,0,0.1); max-height: 90vh; overflow-y: auto;">
        <div style="position: relative; margin-bottom: 20px;">
          <h2 style="color: #004080; margin: 0; text-align: center;">Sign Up</h2>
          <span onclick="closeModal('register-modal')" style="position: absolute; top: -5px; right: -10px; font-size: 28px; cursor: pointer; color: #666;">&times;</span>
        </div>
        <form method="POST" action="register.php" id="register-form" autocomplete="off">
          <input type="text" name="fname" placeholder="First Name" required style="width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;">
          <input type="text" name="lname" placeholder="Last Name" required style="width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;">
          <input type="text" name="address" placeholder="Address" required style="width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;">
          <input type="email" name="email" placeholder="Email" required style="width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;">
          <input type="text" name="username" placeholder="Username" required style="width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;">
          <input type="password" name="password" placeholder="Password" required style="width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;">
          <input type="password" name="password_confirm" placeholder="Confirm Password" required style="width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;">
          <button type="submit" style="width: 100%; padding: 12px; background: #004080; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 16px;">Create Account</button>
          <p style="text-align: center; margin-top: 15px; color: #666;">Already have an account? <a href="#" onclick="switchModal('register-modal','login-modal'); return false;" style="color: #004080; font-weight: bold;">Login</a></p>
        </form>
      </div>
    </div>

    <!-- LOGOUT CONFIRMATION MODAL -->
    <div id="logout-modal" class="modal">
      <div class="modal-content" style="max-width: 400px; text-align: center;">
        <span class="close" onclick="closeModal('logout-modal')">&times;</span>
        <h2>Confirm Logout</h2>
        <p style="margin: 20px 0;">Are you sure you want to logout?</p>
        <div style="display: flex; gap: 10px; justify-content: center;">
          <button onclick="window.location.href='logout.php'" style="background: #004080; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">Yes, Logout</button>
          <button onclick="closeModal('logout-modal')" style="background: #ccc; color: #333; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">Cancel</button>
        </div>
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
        var pw = document.getElementById('register-password');
        var pwc = document.getElementById('register-password-confirm');
        var err = document.getElementById('register-error');
        
        if (regForm && pw && pwc && err) {
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
