<?php
require_once 'config/database.php'; // adjust if your DB file is in another folder

$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name && $email && $subject && $message) {
        $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("ssss", $name, $email, $subject, $message);
            if ($stmt->execute()) {
                $successMessage = "Your message has been sent. Thank you!";
            } else {
                $errorMessage = "Error saving message. Please try again.";
            }
            $stmt->close();
        } else {
            $errorMessage = "Database error: " . $conn->error;
        }
    } else {
        $errorMessage = "Please fill in all fields.";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Konsortium Usahawan Madani</title>
  <meta name="description" content="">
  <meta name="keywords" content="">

  <!-- Favicons -->
  <link href="assets/img/kusma_small.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Nunito:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/pricing_section.css">

  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="assets/css/main.css" rel="stylesheet">

  <!-- =======================================================
  * Template Name: FlexStart
  * Template URL: https://bootstrapmade.com/flexstart-bootstrap-startup-template/
  * Updated: Nov 01 2024 with Bootstrap v5.3.3
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->
</head>

<body class="index-page">
  <!-- Include Navbar -->
  <?php include 'pages/includes/navbar.php'; ?>



  <main class="main">

    <!-- Hero Section -->
    <section id="hero" class="hero section">

      <div class="container">
        <div class="row gy-4">
          <div class="col-lg-6 order-2 order-lg-1 d-flex flex-column justify-content-center">
            <h1 data-aos="fade-up">Fueling Business Growth with Easy Access to Government Funding.</h1>
            <p data-aos="fade-up" data-aos-delay="100">Simplifying loans, grants, training, and tools for Malaysian businesses and startups.</p>
            <div class="d-flex flex-column flex-md-row" data-aos="fade-up" data-aos-delay="200">
              <a href="pages/signup/signup.php" class="btn-get-started">Get Started <i class="bi bi-arrow-right"></i></a>
             
            </div>
          </div>
          <div class="col-lg-6 order-1 order-lg-2 hero-img" data-aos="zoom-out">
            <img src="assets/img/hero-img.png" class="img-fluid animated" alt="">
          </div>
        </div>
      </div>

    </section><!-- /Hero Section -->

    <!-- About Section -->
    <section id="about" class="about section">

      <div class="container" data-aos="fade-up">
        <div class="row gx-0">

          <div class="col-lg-6 d-flex flex-column justify-content-center" data-aos="fade-up" data-aos-delay="200">
            <div class="content">
              <h3>About KUSMA</h3>
              <h2>KUSMA</h2>
              <p>
                KUSMA Sdn Bhd (Established on August, 2024) is an integrated Business & Financial
Consultation solutions provider, specializing in Business Planning & Financial Planning of commercial and government business trade. With our technical expertise, remarkable service and rapid responds towards our clients has made us recognized worldwide.
              </p>
              <div class="text-center text-lg-start">
                <a href="#" class="btn-read-more d-inline-flex align-items-center justify-content-center align-self-center">
                  <span>Read More</span>
                  <i class="bi bi-arrow-right"></i>
                </a>
              </div>
            </div>
          </div>

          <div class="col-lg-6 d-flex align-items-center" data-aos="zoom-out" data-aos-delay="200">
            <img src="assets/img/team.png" class="img-fluid" alt="">
          </div>

        </div>
      </div>

    </section><!-- /About Section -->

    <!-- Values Section -->
    <section id="values" class="values section">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h2>Financing Your Business Has Never Been Simpler</h2>
        <p>Follow these steps to embark on a new journey!<br></p>
      </div><!-- End Section Title -->

      <div class="container">

        <div class="row gy-4">

          <div class="col-lg-4" data-aos="fade-up" data-aos-delay="100">
            <div class="card">
              <img src="assets/img/values-1.png" class="img-fluid" alt="">
              <h3>Register & Log In </h3>
              
            </div>
          </div><!-- End Card Item -->

          <div class="col-lg-4" data-aos="fade-up" data-aos-delay="200">
            <div class="card">
              <img src="assets/img/values-2.png" class="img-fluid" alt="">
              <h3>Get Personalized Recommendations by 66 agencies</h3>
          
            </div>
          </div><!-- End Card Item -->

          <div class="col-lg-4" data-aos="fade-up" data-aos-delay="300">
            <div class="card">
              <img src="assets/img/values-3.png" class="img-fluid" alt="">
              <h3>Receive Support Programs and Grow Your Business!</h3>

            </div>
          </div><!-- End Card Item -->

        </div>

      </div>

    </section><!-- /Values Section -->

    

    <!-- Services Section -->
    <section id="services" class="services section">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h2>Services</h2>
        <p>Check Our Services<br></p>
      </div><!-- End Section Title -->

      <div class="container">

        <div class="row gy-4">

          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
            <div class="service-item item-cyan position-relative">
              <i class="bi bi-activity icon"></i>
              <h3>Personalized Business Resources</h3>
              <p>Discover government-backed loans, grants, and training programs tailored to your business needs. Our intelligent recommendation system matches you with the right resources to support your entrepreneurial journey.</p>
              
            </div>
          </div><!-- End Service Item -->

          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
            <div class="service-item item-orange position-relative">
              <i class="bi bi-broadcast icon"></i>
              <h3>Expert Consultation</h3>
              <p>Connect with experienced consultants to gain insights and strategies for business growth. Schedule appointments at your convenience and receive guidance tailored to your industry and goals.</p>
            
            </div>
          </div><!-- End Service Item -->

          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
            <div class="service-item item-teal position-relative">
              <i class="bi bi-easel icon"></i>
              <h3>Agent Referral Program</h3>
              <p>Partner with us as an agent to earn commissions while supporting entrepreneurs. Track your referrals, monitor earnings, and manage your profile through our dedicated dashboard.</p>
           
            </div>
          </div><!-- End Service Item -->

         

        </div>

      </div>

    </section><!-- /Services Section -->

<!-- Pricing Section -->
<section id="pricing" class="pricing-section">
  <div class="container section-title" data-aos="fade-up">
    <h2>Subscription Plans</h2>
    <p>Check Our Affordable Pricing</p>
  </div>

  <div class="container">
    <div class="pricing-cards">

      <!-- Free Plan -->
      <div class="pricing-item" data-aos="zoom-in" data-aos-delay="100">
        <h3 class="plan-title free">Free Plan</h3>
        <div class="plan-price">RM <span class="amount">0</span> <span class="duration"></span></div>
        <div class="plan-icon"><i class="bi bi-box"></i></div>
        <ul class="plan-features">
          <li>🔍 View up to 2 recommendations</li>
          <li>🚫 No access to full recommendations features</li>
          <li>🚫 Cannot view full government-linked program list</li>
          <li>🚫 Limited personalization</li>
        </ul>
        <a href="pages/signup/signup.php" class="btn-buy free">Buy Now</a>
      </div>

      <!-- Premium Plan -->
      <div class="pricing-item featured" data-aos="zoom-in" data-aos-delay="200">
        <span class="badge-featured">Featured</span>
        <h3 class="plan-title premium">Premium Plan</h3>
        <div class="plan-price">RM <span class="amount">99.90</span> <span class="duration">/ year</span></div>
        <div class="plan-icon"><i class="bi bi-send"></i></div>
        <ul class="plan-features">
          <li>✅ Unlimited Program Recommendations</li>
          <li>✅ Full access to personalised results</li>
          <li>✅ Access all government-linked funding/ grant programs</li>
          <li>✅ E-invoice download after payment</li>
          <li>✅ Early access to future upgrades</li>
        </ul>
        <a href="pages/signup/signup.php" class="btn-buy premium">Buy Now</a>
      </div>

    </div>
  </div>
</section>


          

   
          

         

    <!-- Contact Section -->
    <section id="contact" class="contact section">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h2>Contact</h2>
        <p>Contact Us</p>
      </div><!-- End Section Title -->

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row gy-4">

          <div class="col-lg-6">

            <div class="row gy-4">
              <div class="col-md-6">
                <div class="info-item" data-aos="fade" data-aos-delay="200">
                  <i class="bi bi-geo-alt"></i>
                  <h3>Address</h3>
                  <p>Level 8–09 Wangsa 118, Wangsa Maju</p>
                  <p>Jalan Wangsa Delima, 53300 Kuala Lumpur, Malaysia</p>
                </div>
              </div><!-- End Info Item -->

              <div class="col-md-6">
                <div class="info-item" data-aos="fade" data-aos-delay="300">
                  <i class="bi bi-telephone"></i>
                  <h3>Call Us</h3>
                  <p>(+6) 03-2303 9512</p>
                
                </div>
              </div><!-- End Info Item -->

              <div class="col-md-6">
                <div class="info-item" data-aos="fade" data-aos-delay="400">
                  <i class="bi bi-envelope"></i>
                  <h3>Email Us</h3>
                  <p>✉️ info@kusma.my (Admin Dept.)</p>
            <p>✉️ idzham@kusma.my (Manager Dept.)</p>
                </div>
              </div><!-- End Info Item -->

              <div class="col-md-6">
                <div class="info-item" data-aos="fade" data-aos-delay="500">
                  <i class="bi bi-clock"></i>
                  <h3>Open Hours</h3>
                  <p>Monday - Friday</p>
                  <p>9:00AM - 05:00PM</p>
                </div>
              </div><!-- End Info Item -->

            </div>

          </div>

          <div class="col-lg-6">
          <style>
  /* Form container */
  .php-email-form {
    background: #f9f9f9;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  }

  .php-email-form .form-control {
    border-radius: 10px;
    border: 1px solid #ddd;
    padding: 12px 15px;
    font-size: 15px;
    transition: 0.3s;
  }

  .php-email-form .form-control:focus {
    border-color: #6610f2;
    box-shadow: 0 0 0 0.15rem rgba(102, 16, 242, 0.2);
  }

  .php-email-form textarea.form-control {
    resize: vertical;
  }

  .php-email-form .btn {
    background-color: #6610f2;
    color: #fff;
    padding: 12px 25px;
    font-size: 16px;
    border-radius: 50px;
    border: none;
    transition: all 0.3s ease;
  }

  .php-email-form .btn:hover {
    background-color: #4b0dc6;
  }

  /* Form feedback messages */
  .form-message {
  margin-bottom: 20px;     /* Space below message */
  margin-top: 10px;        /* Space above message */
  padding: 15px 20px;
  border-radius: 10px;
  font-weight: 500;
  font-size: 15px;
}


  .success-message {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
  }

  .error-message {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
  }

  /* Responsive tweaks if needed */
  @media (max-width: 768px) {
    .php-email-form .btn {
      width: 100%;
    }
  }
</style>


  <?php if (!empty($successMessage)): ?>
    <div class="form-message success-message"><?= $successMessage ?></div>
  <?php elseif (!empty($errorMessage)): ?>
    <div class="form-message error-message"><?= $errorMessage ?></div>
  <?php endif; ?>

  <form method="POST" action="#contact">
    <div class="row gy-4">
      <div class="col-md-6">
        <input type="text" name="name" class="form-control" placeholder="Your Name" required>
      </div>
      <div class="col-md-6">
        <input type="email" name="email" class="form-control" placeholder="Your Email" required>
      </div>
      <div class="col-12">
        <input type="text" name="subject" class="form-control" placeholder="Subject" required>
      </div>
      <div class="col-12">
        <textarea name="message" rows="6" class="form-control" placeholder="Message" required></textarea>
      </div>
      <div class="col-12 text-center">
        <button type="submit" name="contact_submit" class="btn btn-primary">Send Message</button>
      </div>
    </div>
  </form>
</div>
<!-- End Contact Form -->

        </div>

      </div>

    </section><!-- /Contact Section -->

  </main>

  <footer id="footer" class="footer">
   
  
    <!-- Bottom Section -->
    <div class="container copyright text-center mt-4">
      <p>© <span>Copyright</span> <strong class="px-1 sitename">KUSMA SDN. BHD.</strong> <span>All Rights Reserved</span></p>
      
    </div>
  </footer>
  
  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
  
  <!-- Vendor JS Files -->
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
  <script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>
  <script src="assets/vendor/imagesloaded/imagesloaded.pkgd.min.js"></script>
  <script src="assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>
  <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>

  <!-- Main JS File -->
  <script src="assets/js/main.js"></script>

</body>

</html>