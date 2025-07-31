<?php
session_start();
require_once 'PHPMailer/PHPMailer.php';
require_once 'PHPMailer/SMTP.php';
require_once 'PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $contact = trim($_POST['contact']);
    $subject = trim($_POST['subject']);
    
    // Validation
    if (empty($name)) {
        $error = 'Name is required';
    } elseif (empty($email)) {
        $error = 'Email is required';
    } elseif (empty($contact)) {
        $error = 'Contact number is required';
    } elseif (empty($subject)) {
        $error = 'Subject is required';
    } elseif (!isset($_FILES['cv']) || $_FILES['cv']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Please upload your CV. Upload error: ' . ($_FILES['cv']['error'] ?? 'File not found');
    } elseif (!isset($_FILES['application_letter']) || $_FILES['application_letter']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Please upload your Application Letter. Upload error: ' . ($_FILES['application_letter']['error'] ?? 'File not found');
    } else {
        $cv_file = $_FILES['cv'];
        $application_file = $_FILES['application_letter'];
        $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($cv_file['type'], $allowed_types)) {
            $error = 'CV must be a PDF or Word document';
        } elseif ($cv_file['size'] > $max_size) {
            $error = 'CV file size must be less than 5MB';
        } elseif (!in_array($application_file['type'], $allowed_types)) {
            $error = 'Application Letter must be a PDF or Word document';
        } elseif ($application_file['size'] > $max_size) {
            $error = 'Application Letter file size must be less than 5MB';
        } else {
            // Create email content
            $email_subject = 'Agent Application: ' . $subject;
            $email_body = "
New Agent Application

Name: $name
Email: $email
Contact: $contact
Subject: $subject
Application Date: " . date('Y-m-d H:i:s') . "

Files attached:
- CV: " . $cv_file['name'] . "
- Application Letter: " . $application_file['name'] . "
            ";
            
            // Save files to server and send email notification
            $upload_dir = 'uploads/agent_applications/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Generate unique filenames
            $timestamp = date('Y-m-d_H-i-s');
            $safe_name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $name);
            
            $cv_filename = $upload_dir . $timestamp . '_' . $safe_name . '_CV_' . $cv_file['name'];
            $app_filename = $upload_dir . $timestamp . '_' . $safe_name . '_Application_' . $application_file['name'];
            
            // Move uploaded files to permanent location
            if (move_uploaded_file($cv_file['tmp_name'], $cv_filename) && 
                move_uploaded_file($application_file['tmp_name'], $app_filename)) {
                
                // Send email with file attachments using PHPMailer
                try {
                    $mail = new PHPMailer(true);
                    
                    // Use simple mail configuration (no SMTP)
                    $mail->isMail(); // Use PHP's mail() function
                    
                    // Recipients
                    $mail->setFrom('noreply@homeworker.info', 'Homeworker Connect');
                    $mail->addAddress('support@homeworker.info', 'Homeworker Connect');
                    $mail->addReplyTo($email, $name);
                    
                    // Attach the saved files to the email
                    $mail->addAttachment($cv_filename, 'CV_' . $cv_file['name']);
                    $mail->addAttachment($app_filename, 'Application_Letter_' . $application_file['name']);
                    
                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = $email_subject;
                    $mail->Body    = "
                        <h2>New Agent Application</h2>
                        <p><strong>Name:</strong> " . htmlspecialchars($name) . "</p>
                        <p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>
                        <p><strong>Contact:</strong> " . htmlspecialchars($contact) . "</p>
                        <p><strong>Subject:</strong> " . htmlspecialchars($subject) . "</p>
                        <p><strong>Application Date:</strong> " . date('Y-m-d H:i:s') . "</p>
                        <hr>
                        <p><strong>Documents Attached:</strong></p>
                        <ul>
                            <li>CV: " . htmlspecialchars($cv_file['name']) . " (" . round($cv_file['size']/1024, 2) . " KB)</li>
                            <li>Application Letter: " . htmlspecialchars($application_file['name']) . " (" . round($application_file['size']/1024, 2) . " KB)</li>
                        </ul>
                        <p><em>Please find the application documents attached to this email.</em></p>
                    ";
                    
                    // Send the email with attachments
                    if ($mail->send()) {
                        $success = 'Your agent application with documents has been submitted successfully! We will contact you soon.';
                    } else {
                        $success = 'Your agent application with documents has been submitted successfully! We will contact you soon.';
                    }
                    
                } catch (Exception $e) {
                    // Even if email fails, files are saved
                    $success = 'Your agent application with documents has been submitted successfully! We will contact you soon.';
                    error_log("Agent application email with attachments failed: " . $e->getMessage());
                }
                
                // Clear form data on success
                $_POST = array();
                
            } else {
                $error = 'Failed to upload files. Please try again.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Agent Application - Homeworker Connect</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <style>
        .file-input-wrapper {
            position: relative;
            display: inline-block;
            width: 100%;
        }
        .file-input {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        .file-input-button {
            display: block;
            padding: 12px;
            border: 2px dashed #e0e0e0;
            border-radius: 8px;
            text-align: center;
            background: #f8f9fa;
            color: #666;
            cursor: pointer;
            transition: all 0.3s;
        }
        .file-input-button:hover {
            border-color: #197b88;
            background: #f0f8f9;
            color: #197b88;
        }
        .file-selected {
            border-color: #197b88;
            background: #e6f4ea;
            color: #2e7d32;
        }
    </style>
</head>
<body style="background: #f4f8fb; font-family: 'Segoe UI', Arial, sans-serif; display: flex; flex-direction: column; min-height: 100vh;">

    <div style="width:100%;text-align:center;margin:0;padding:0;">
        <img src="bghse.png" alt="Logo" style="height:48px;display:inline-block;margin:0 auto 0 auto;padding-top:8px;">
    </div>
    
    <div class="form-container" style="max-width: 400px; margin: 24px auto; background: #ffffff; border-radius: 16px; box-shadow: 0 8px 32px rgba(0,0,0,0.1); padding: 24px; display: flex; flex-direction: column; gap: 16px;">
        <a href="agent_register.php" style="color: #197b88; text-decoration: none; font-weight: 500; align-self: flex-start;">&larr; Back</a>
        <h2 style="text-align: center; color: #197b88; margin: 0; font-size: 1.5rem;">Agent Application</h2>
        <p style="text-align: center; color: #666; margin: 0; font-size: 0.9rem;">
            Apply to become an authorized agent for Homeworker Connect
        </p>
        
        <?php if ($error): ?>
            <p style="background: #ffeaea; color: #c0392b; padding: 8px 12px; border-radius: 8px; margin: 0; text-align: center;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <?php if ($success): ?>
            <p style="background: #e6f4ea; color: #2e7d32; padding: 8px 12px; border-radius: 8px; margin: 0; text-align: center;"><?= htmlspecialchars($success) ?></p>
        <?php endif; ?>
        
        <form method="POST" action="" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 12px;">
            <input type="text" name="name" placeholder="Full Name" value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>" required style="padding: 12px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s;">
            <input type="email" name="email" placeholder="Email Address" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required style="padding: 12px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s;">
            <input type="tel" name="contact" placeholder="Phone Number" value="<?= isset($_POST['contact']) ? htmlspecialchars($_POST['contact']) : '' ?>" required style="padding: 12px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s;">
            <input type="text" name="subject" placeholder="Subject (e.g., Application for Agent Position)" value="<?= isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : '' ?>" required style="padding: 12px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s;">
            
            <div class="file-input-wrapper">
                <input type="file" name="application_letter" id="application_letter" class="file-input" accept=".pdf,.doc,.docx" required>
                <label for="application_letter" class="file-input-button" id="application-label">
                    📄 Click to upload your Application Letter (PDF or Word)
                </label>
            </div>
            <small style="color: #666; margin: -8px 0 0; font-size: 0.8rem;">Maximum file size: 5MB. Accepted formats: PDF, DOC, DOCX</small>

            <div class="file-input-wrapper">
                <input type="file" name="cv" id="cv" class="file-input" accept=".pdf,.doc,.docx" required>
                <label for="cv" class="file-input-button" id="cv-label">
                    📄 Click to upload your CV (PDF or Word)
                </label>
            </div>
            <small style="color: #666; margin: -8px 0 0; font-size: 0.8rem;">Maximum file size: 5MB. Accepted formats: PDF, DOC, DOCX</small>
            
            <button type="submit" style="background: linear-gradient(135deg, #197b88, #1ec8c8); color: #fff; border: none; border-radius: 8px; padding: 12px; font-size: 1.1rem; font-weight: 600; cursor: pointer; transition: background 0.3s; margin-top: 8px;">Submit Application</button>
        </form>
        
        <p style="text-align: center; margin: 0; font-size: 0.9rem; color: #666;">
            Already have an agent account? <a href="agent_login.php" style="color: #197b88; text-decoration: none;">Login here</a>
        </p>
    </div>

    <footer style="margin-top: auto; text-align: center; color: #888; padding: 16px 0;">
        <p>&copy; <?= date("Y") ?> Homeworker Connect. All rights reserved.</p>
    </footer>

    <script>
        // Handle Application Letter upload
        document.getElementById('application_letter').addEventListener('change', function() {
            const fileLabel = document.getElementById('application-label');
            const fileName = this.files[0] ? this.files[0].name : '';
            
            if (fileName) {
                fileLabel.textContent = '✅ ' + fileName;
                fileLabel.classList.add('file-selected');
            } else {
                fileLabel.textContent = '📄 Click to upload your Application Letter (PDF or Word)';
                fileLabel.classList.remove('file-selected');
            }
        });

        // Handle CV upload
        document.getElementById('cv').addEventListener('change', function() {
            const fileLabel = document.getElementById('cv-label');
            const fileName = this.files[0] ? this.files[0].name : '';
            
            if (fileName) {
                fileLabel.textContent = '✅ ' + fileName;
                fileLabel.classList.add('file-selected');
            } else {
                fileLabel.textContent = '📄 Click to upload your CV (PDF or Word)';
                fileLabel.classList.remove('file-selected');
            }
        });
    </script>

</body>
</html>