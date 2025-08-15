 function toggleRegisterDropdown() {
        const dropdown = document.getElementById('registerDropdown');
        dropdown.classList.toggle('show');
      }
      function toggleLoginDropdown() {
        const dropdown = document.getElementById('loginDropdown');
        dropdown.classList.toggle('show');
      }
      function toggleJobsDropdown() {
        const dropdown = document.getElementById('jobsDropdown');
        dropdown.classList.toggle('show');
      }
      // Close dropdowns if clicked outside or on Escape
      window.addEventListener('click', function(e) {
        if (!e.target.closest('.pro-btn')) {
          const dd = document.getElementById('registerDropdown');
          if (dd && dd.classList.contains('show')) {
            dd.classList.remove('show');
          }
        }
        if (!e.target.closest('.pro-btn')) {
          const ld = document.getElementById('loginDropdown');
          if (ld && ld.classList.contains('show')) {
            ld.classList.remove('show');
          }
        }
        if (!e.target.closest('.pro-btn')) {
          const jd = document.getElementById('jobsDropdown');
          if (jd && jd.classList.contains('show')) {
            jd.classList.remove('show');
          }
        }
      });
      window.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
          const dd = document.getElementById('registerDropdown');
          if (dd) dd.classList.remove('show');
          const ld = document.getElementById('loginDropdown');
          if (ld) ld.classList.remove('show');
          const jd = document.getElementById('jobsDropdown');
          if (jd) jd.classList.remove('show');
        }
      });