<?php include 'header.php'; ?>

<!-- About Page Content -->
<main style="margin:0%; margin-top:auto; padding:0;">
  <table style="width:100%; height:100vh; border-collapse:collapse;">
    <tr>
      <!-- Left column: iframe -->
      <td style="width:100%; padding:0; margin:0;">
        <iframe 
          src="https://homeworker.info/resources/" 
          style="border:none; width:100%; height:100vh; display:block;" 
          scrolling="auto" id="resourcesFrame" name ="resourcesFrame"> 
        </iframe>
      </td>

      <!-- Right column: placeholder -->
      <td style="width:0%; padding:0; margin:0; background:#f5f5f5;">
        <!-- Add your sidebar content here -->
      </td>
    </tr>
  </table>
</main>

<?php include 'footer.php'; ?>