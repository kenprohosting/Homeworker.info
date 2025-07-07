document.addEventListener("DOMContentLoaded", function () {
  const navToggle = document.querySelector(".nav-toggle");
  const navLinks = document.querySelector(".nav-links");

  // ☰ toggle menu
  navToggle.addEventListener("click", () => {
    navLinks.classList.toggle("active");
  });

  // Open dropdown on mobile
  document.querySelectorAll(".dropdown > a").forEach(link => {
    link.addEventListener("click", function (e) {
      e.preventDefault();
      const dropdown = this.parentElement;
      dropdown.classList.toggle("open");
    });
  });

  // Close dropdowns when clicking outside
  window.addEventListener("click", function (e) {
    if (!e.target.closest("nav")) {
      document.querySelectorAll(".dropdown").forEach(d => d.classList.remove("open"));
    }
  });
});
