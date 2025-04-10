<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../../config/database.php';

// Check if the user is logged in and is a customer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: ../../login/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch data from the database
$sql_user = "SELECT * FROM users WHERE id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user_data = $result_user->fetch_assoc();

$sql_personal = "SELECT * FROM personal_details WHERE user_id = ?";
$stmt_personal = $conn->prepare($sql_personal);
$stmt_personal->bind_param("i", $user_id);
$stmt_personal->execute();
$result_personal = $stmt_personal->get_result();
$personal_data = $result_personal->fetch_assoc();

$sql_education = "SELECT * FROM education_resources WHERE user_id = ?";
$stmt_education = $conn->prepare($sql_education);
$stmt_education->bind_param("i", $user_id);
$stmt_education->execute();
$result_education = $stmt_education->get_result();
$education_data = $result_education->fetch_assoc();

$sql_business = "SELECT * FROM business_details WHERE user_id = ?";
$stmt_business = $conn->prepare($sql_business);
$stmt_business->bind_param("i", $user_id);
$stmt_business->execute();
$result_business = $stmt_business->get_result();
$business_data = $result_business->fetch_assoc();

// Handle form submissions for editing details
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_type = $_POST['form_type'] ?? '';

    if ($form_type === 'profile') {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_BCRYPT) : null;

        $sql = "UPDATE users SET name = ?, email = ?";
        if ($password) {
            $sql .= ", password = ?";
        }
        $sql .= " WHERE id = ?";

        $stmt = $conn->prepare($sql);
        if ($password) {
            $stmt->bind_param("sssi", $name, $email, $password, $user_id);
        } else {
            $stmt->bind_param("ssi", $name, $email, $user_id);
        }
        $stmt->execute();
    } elseif ($form_type === 'personal') {
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $phone_number = $_POST['phone_number'];
        $address = $_POST['address'];

        $sql = "UPDATE personal_details SET first_name = ?, last_name = ?, phone_number = ?, address = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $first_name, $last_name, $phone_number, $address, $user_id);
        $stmt->execute();
    } elseif ($form_type === 'education') {
        $education_type = $_POST['education_type'];
        $certification_level = $_POST['certification_level'];

        $sql = "UPDATE education_resources SET education_type = ?, certification_level = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $education_type, $certification_level, $user_id);
        $stmt->execute();
    } elseif ($form_type === 'business') {
        $business_type = $_POST['business_type'];
        $business_name = $_POST['business_name'];

        $sql = "UPDATE business_details SET business_type = ?, business_name = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $business_type, $business_name, $user_id);
        $stmt->execute();
    }
    header("Location: manage_profile.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Profile</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../../../assets/css/customer_manageprofile.css">
</head>
<body>

<div class="container py-5">
  <h1><i class="fas fa-user-circle"></i> Manage Profile</h1>

  <!-- Profile Overview -->
  <section class="profile-section mb-4">
    <h2>Profile Overview</h2>
    <p><strong>Name:</strong> <?php echo htmlspecialchars($user_data['name']); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($user_data['email']); ?></p>
    <button class="btn-primary" data-bs-toggle="modal" data-bs-target="#editProfileModal">Edit Profile</button>
  </section>

  <!-- Personal Details -->
  <section class="profile-section mb-4">
    <h2>Personal Details</h2>
    <p><strong>First Name:</strong> <?php echo htmlspecialchars($personal_data['first_name'] ?? ''); ?></p>
    <p><strong>Last Name:</strong> <?php echo htmlspecialchars($personal_data['last_name'] ?? ''); ?></p>
    <p><strong>Phone Number:</strong> <?php echo htmlspecialchars($personal_data['phone_number'] ?? ''); ?></p>
    <p><strong>Address:</strong> <?php echo htmlspecialchars($personal_data['address'] ?? ''); ?></p>
    <button class="btn-primary" data-bs-toggle="modal" data-bs-target="#editPersonalModal">Edit Personal Details</button>
  </section>

  <!-- Education Resources -->
  <section class="profile-section mb-4">
    <h2>Education Resources</h2>
    <p><strong>Education Type:</strong> <?php echo htmlspecialchars($education_data['education_type'] ?? ''); ?></p>
    <p><strong>Certification Level:</strong> <?php echo htmlspecialchars($education_data['certification_level'] ?? ''); ?></p>
    <button class="btn-primary" data-bs-toggle="modal" data-bs-target="#editEducationModal">Edit Education</button>
  </section>

  <!-- Business Details -->
  <section class="profile-section mb-4">
    <h2>Business Details</h2>
    <p><strong>Business Type:</strong> <?php echo htmlspecialchars($business_data['business_type'] ?? ''); ?></p>
    <p><strong>Business Name:</strong> <?php echo htmlspecialchars($business_data['business_name'] ?? ''); ?></p>
    <button class="btn-primary" data-bs-toggle="modal" data-bs-target="#editBusinessModal">Edit Business</button>
  </section>
</div>

<!-- Modals -->
<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="manage_profile.php">
        <input type="hidden" name="form_type" value="profile">
        <div class="modal-header">
          <h5 class="modal-title" id="editProfileModalLabel">Edit Profile</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label>Name</label>
            <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($user_data['name']); ?>" required>
          </div>
          <div class="mb-3">
            <label>Email</label>
            <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
          </div>
          <div class="mb-3">
            <label>Password</label>
            <input type="password" class="form-control" name="password" placeholder="Enter new password">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Bootstrap JS and Dependencies -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
