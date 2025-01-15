<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Personal Details - KUSMA</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../../assets/css/forms.css">
</head>
<body>
  <div class="container my-5">
    <div class="form-container">
      <h1>Welcome to KUSMA!</h1>
      <p>Let's set up your profile to provide you with personalized loan and business recommendations</p>
      <div class="progress my-4">
    <div class="progress-bar" role="progressbar" style="width: 33%;" aria-valuenow="33" aria-valuemin="0" aria-valuemax="100">
        Step 1 of 3: Personal Details
    </div>
</div>

<form action="process_personal_details.php" method="POST">
    <!-- Title -->
    <div class="row mb-3">
        <div class="col-md-6">
            <label for="title" class="form-label">Title</label>
            <select class="form-select" id="title" name="title" required>
                <option value="" selected disabled>Select Title</option>
                <option value="Mr">Mr.</option>
                <option value="Ms">Ms.</option>
                <option value="Mrs">Mrs.</option>
            </select>
        </div>
    </div>

    <!-- Name -->
    <div class="row mb-3">
        <div class="col-md-6">
            <label for="first-name" class="form-label">First Name</label>
            <input type="text" class="form-control" id="first-name" name="first-name" placeholder="Enter your first name" required>
        </div>
        <div class="col-md-6">
            <label for="last-name" class="form-label">Last Name</label>
            <input type="text" class="form-control" id="last-name" name="last-name" placeholder="Enter your last name" required>
        </div>
    </div>

    <!-- Gender -->
    <div class="mb-3">
        <label for="gender" class="form-label">Gender</label>
        <select class="form-select" id="gender" name="gender" required>
            <option value="" selected disabled>Select Gender</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
        </select>
    </div>

    <!-- Bumiputera Status -->
    <div class="mb-3">
        <label for="bumiputera-status" class="form-label">Bumiputera Status</label>
        <select class="form-select" id="bumiputera-status" name="bumiputera_status" required>
            <option value="Bumiputera" selected>Bumiputera</option>
            <option value="Non-Bumiputera">Non-Bumiputera</option>
        </select>
    </div>

    <!-- Phone, Email, MyKad, Birthdate -->
    <div class="mb-3">
        <label for="phone" class="form-label">Phone Number</label>
        <input type="tel" class="form-control" id="phone" name="phone" placeholder="Enter your phone number" required>
    </div>

    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
    </div>

    <div class="mb-3">
        <label for="mykad" class="form-label">MyKad Number</label>
        <input type="text" class="form-control" id="mykad" name="mykad" placeholder="Enter your MyKad number" required>
    </div>

    <div class="mb-3">
        <label for="birthdate" class="form-label">Birthdate</label>
        <input type="date" class="form-control" id="birthdate" name="birthdate" required>
    </div>

    <!-- Address -->
    <div class="mb-3">
        <label for="address" class="form-label">Address</label>
        <input type="text" class="form-control" id="address" name="address" placeholder="Enter your address" required>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <label for="city" class="form-label">City</label>
            <input type="text" class="form-control" id="city" name="city" placeholder="Enter your city" required>
        </div>
        <div class="col-md-6">
            <label for="postcode" class="form-label">Postcode</label>
            <input type="text" class="form-control" id="postcode" name="postcode" placeholder="Enter your postcode" required>
        </div>
    </div>

    <div class="mb-3">
        <label for="state" class="form-label">State</label>
        <select class="form-select" id="state" name="state" required>
            <option value="" selected disabled>Select State</option>
            <option value="Johor">Johor</option>
            <option value="Kedah">Kedah</option>
            <option value="Kelantan">Kelantan</option>
            <option value="Melaka">Melaka</option>
            <option value="Negeri Sembilan">Negeri Sembilan</option>
            <option value="Pahang">Pahang</option>
            <option value="Perak">Perak</option>
            <option value="Perlis">Perlis</option>
            <option value="Penang">Penang</option>
            <option value="Sabah">Sabah</option>
            <option value="Sarawak">Sarawak</option>
            <option value="Selangor">Selangor</option>
            <option value="Kuala Lumpur">Kuala Lumpur</option>
            <option value="Labuan">Labuan</option>
            <option value="Putrajaya">Putrajaya</option>
        </select>
    </div>

    <!-- OKU -->
    <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" id="oku" name="oku">
        <label class="form-check-label" for="oku">I have an OKU card</label>
    </div>

    <!-- Submit -->
    <button type="submit" class="btn btn-primary w-100">Next</button>
</form>
