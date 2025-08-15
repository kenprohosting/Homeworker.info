<?php include 'header.php'; ?>

<main>
  <div class="faq-container">
    <div class="faq-title">Contact Us</div>
    <form class="contact-form" method="post" action="#">
      <label for="name">Name</label>
      <input type="text" id="name" name="name" required>
      <label for="email">Email</label>
      <input type="email" id="email" name="email" required>
      <label for="message">Message</label>
      <textarea id="message" name="message" required></textarea>
      <button type="submit">Send Message</button>
    </form>
  </div>
  <div class="contact-details">
    <div>Email: <a href="mailto:support@homeworker.info">support@homeworker.info</a></div>
    <div>Phone: +254 725 788 400</div>
    <div>Address: Nairobi, Kenya</div>
  </div>
</main>
<?php include 'footer.php'; ?>