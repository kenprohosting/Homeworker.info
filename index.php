<?php include 'header.php'; ?>

<section class="hero">
  <div class="hero-content">
    <h1>Find Validated Homeworkers Easily</h1>
    <p>Connecting employers with verified and trained homeworkers across the world.</p>
    <p> +1000 verified homeworkers </p>
    <p> +100 verified freelancers </p>
    <p> +100 verified agents </p>

    
    <!-- Hero Action Card -->
    <div class="hero-action-card horizontal-card">
      <div class="hero-action-section">
        <button onclick="toggleRegisterDropdown()" class="btn pro-btn">
          Register <span class="chevron">▼</span>
        </button>
        <div id="registerDropdown" class="pro-dropdown">
          <a href="employer_register.php" class="pro-dropdown-link">Employer Register</a>
          <a href="agent_register.php" class="pro-dropdown-link">Agent Register</a>
          <a href="freelancer_register.php" class="pro-dropdown-link">Freelancer Register</a>
        </div>
      </div>
      <div class="hero-divider-vertical"></div>
      <div class="hero-action-section">
        <button onclick="toggleLoginDropdown()" class="btn pro-btn">
          Login <span class="chevron">▼</span>
        </button>
        <div id="loginDropdown" class="pro-dropdown">
          <a href="employer_login.php" class="pro-dropdown-link">Employer Login</a>
          <a href="employee_login.php" class="pro-dropdown-link">Househelp Login</a>
          <a href="agent_login.php" class="pro-dropdown-link">Agent Login</a>
          <a href="freelancer_login.php" class="pro-dropdown-link">Freelancer Login</a>
        </div>
      </div>
    </div>
    <script scr="scripts.js"></script>
  </div>
</section>


<?php include 'footer.php'; ?>
