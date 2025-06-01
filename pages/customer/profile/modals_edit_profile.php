<?php
// $personal, $business, $education, $currentEmail assumed to be available
?>

<!-- Personal Modal -->
<div class="modal" id="personalModal">
  <div class="modal-content">
    <span class="close-btn" onclick="closeModal('personalModal')">&times;</span>
    <h2>Edit Personal Information</h2>
    <form method="POST" action="update_personal_details.php">
      <label>Title</label>
      <select name="title" required>
        <?php foreach (['Mr','Ms','Mrs'] as $opt): ?>
          <option value="<?= $opt ?>" <?= $personal['title'] === $opt ? 'selected' : '' ?>><?= $opt ?></option>
        <?php endforeach; ?>
      </select>

      <label>First Name</label>
      <input type="text" name="first_name" value="<?= htmlspecialchars($personal['first_name']) ?>" required>

      <label>Last Name</label>
      <input type="text" name="last_name" value="<?= htmlspecialchars($personal['last_name']) ?>" required>

      <label>Gender</label>
      <select name="gender" required>
        <option value="Male" <?= $personal['gender'] === 'Male' ? 'selected' : '' ?>>Male</option>
        <option value="Female" <?= $personal['gender'] === 'Female' ? 'selected' : '' ?>>Female</option>
      </select>

      <label>Phone Number</label>
      <input type="text" name="phone" value="<?= htmlspecialchars($personal['phone_number']) ?>" required>

      <label>Birthdate</label>
      <input type="date" name="birthdate" value="<?= $personal['birthdate'] ?>" required>

      <label>MyKad Number</label>
      <input type="text" name="mykad" value="<?= htmlspecialchars($personal['mykad_number']) ?>" required>

      <label>Bumiputera Status</label>
      <select name="bumiputera_status" required>
        <option value="Bumiputera" <?= $personal['bumiputera_status'] === 'Bumiputera' ? 'selected' : '' ?>>Bumiputera</option>
        <option value="Non-Bumiputera" <?= $personal['bumiputera_status'] === 'Non-Bumiputera' ? 'selected' : '' ?>>Non-Bumiputera</option>
      </select>

      <label>Address</label>
      <input type="text" name="address" value="<?= htmlspecialchars($personal['address']) ?>" required>

      <label>City</label>
      <input type="text" name="city" value="<?= htmlspecialchars($personal['city']) ?>" required>

      <label>State</label>
      <select name="state" required>
        <?php foreach (["Johor","Kedah","Kelantan","Melaka","Negeri Sembilan","Pahang","Perak","Perlis","Penang","Sabah","Sarawak","Selangor","Kuala Lumpur","Labuan","Putrajaya"] as $state): ?>
          <option value="<?= $state ?>" <?= $personal['state'] === $state ? 'selected' : '' ?>><?= $state ?></option>
        <?php endforeach; ?>
      </select>

      <label>Postcode</label>
      <input type="text" name="postcode" value="<?= htmlspecialchars($personal['postcode']) ?>" required>

      <label><input type="checkbox" name="oku" value="1" <?= $personal['oku_status'] ? 'checked' : '' ?>> OKU Status</label>

      <label>Email</label>
      <input type="email" name="email" value="<?= htmlspecialchars($currentEmail) ?>" required>

      <label>New Password (optional)</label>
      <input type="password" name="new_password" placeholder="Leave blank to keep current">

      <label>Confirm Password</label>
      <input type="password" name="confirm_password" placeholder="Retype new password">

      <button type="submit" class="dashboard-btn">Save Changes</button>
    </form>
  </div>
</div>

<!-- Business Modal -->
<div class="modal" id="businessModal">
  <div class="modal-content">
    <span class="close-btn" onclick="closeModal('businessModal')">&times;</span>
    <h2>Edit Business Information</h2>
    <form method="POST" action="update_business_details.php">
      <label>Is Registered</label>
      <select name="is_registered" required>
        <option value="1" <?= $business['is_registered'] == 1 ? 'selected' : '' ?>>Yes</option>
        <option value="0" <?= $business['is_registered'] == 0 ? 'selected' : '' ?>>No</option>
      </select>

      <label>Business Type</label>
      <select name="business_type" required>
        <?php foreach (['Sole Proprietor', 'Partnership', 'Private Limited (SDNBHD)'] as $bt): ?>
          <option value="<?= $bt ?>" <?= $business['business_type'] === $bt ? 'selected' : '' ?>><?= $bt ?></option>
        <?php endforeach; ?>
      </select>

      <label>Business Name</label>
      <input type="text" name="business_name" value="<?= htmlspecialchars($business['business_name']) ?>" required>

      <label>Registration Number</label>
      <input type="text" name="registration_number" value="<?= htmlspecialchars($business['registration_number']) ?>">

      <label>Industry</label>
      <select name="industry" required>
        <?php foreach (['Agriculture & Food','Technology & IT','Construction & Real Estate','Healthcare & Pharmaceuticals','Education & Training','Finance & Banking','Tourism & Hospitality','Transportation & Logistics','Energy & Utilities','Entertainment & Media','Manufacturing & Production','Retail & E-commerce','Professional & Business Services','Public & Non-Profit'] as $ind): ?>
          <option value="<?= $ind ?>" <?= $business['industry'] === $ind ? 'selected' : '' ?>><?= $ind ?></option>
        <?php endforeach; ?>
      </select>

      <label>Premises Type</label>
      <select name="premises_type" required>
        <?php foreach (['Online', 'Physical', 'Both'] as $pt): ?>
          <option value="<?= $pt ?>" <?= $business['premises_type'] === $pt ? 'selected' : '' ?>><?= $pt ?></option>
        <?php endforeach; ?>
      </select>

      <label>Business Experience</label>
      <select name="business_experience" required>
        <?php foreach (['None', 'Less than 3 months', '3 months', '6 months', 'More than 6 months'] as $exp): ?>
          <option value="<?= $exp ?>" <?= $business['business_experience'] === $exp ? 'selected' : '' ?>><?= $exp ?></option>
        <?php endforeach; ?>
      </select>
<label>PBT License</label>
<select name="pbt_license" required>
  <option value="1" <?= $business['pbt_license'] == 1 ? 'selected' : '' ?>>Yes</option>
  <option value="0" <?= $business['pbt_license'] == 0 ? 'selected' : '' ?>>No</option>
</select>


      <button type="submit" class="dashboard-btn">Save Changes</button>
    </form>
  </div>
</div>

<!-- Education Modal -->
<div class="modal" id="educationModal">
  <div class="modal-content">
    <span class="close-btn" onclick="closeModal('educationModal')">&times;</span>
    <h2>Edit Education Information</h2>
    <form method="POST" action="update_education_resources.php">
      <label>Education Type</label>
      <select name="education_type" required>
        <option value="Still Studying" <?= $education['education_type'] === 'Still Studying' ? 'selected' : '' ?>>Still Studying</option>
        <option value="Graduated" <?= $education['education_type'] === 'Graduated' ? 'selected' : '' ?>>Graduated</option>
      </select>

      <label>Certification Level</label>
      <select name="certification_level" required>
        <?php foreach (['SPM / SKM', 'Diploma', 'Degree', 'Master / PhD'] as $cert): ?>
          <option value="<?= $cert ?>" <?= $education['certification_level'] === $cert ? 'selected' : '' ?>><?= $cert ?></option>
        <?php endforeach; ?>
      </select>

      <label>Employment Status</label>
      <select name="employment_status" required>
        <?php foreach (['Employed', 'Self-Employed', 'Unemployed', 'Student'] as $emp): ?>
          <option value="<?= $emp ?>" <?= $education['employment_status'] === $emp ? 'selected' : '' ?>><?= $emp ?></option>
        <?php endforeach; ?>
      </select>

     <label>Resource Types (multiple)</label>
<div style="margin-bottom: 10px;">
<?php
  $res_types = json_decode($education['resource_type'] ?? '[]');
  foreach (['Loans','Grants','Training Programs','Premises'] as $res): ?>
  <label style="display: block; margin: 4px 0;">
    <input type="checkbox" name="resource_type[]" value="<?= $res ?>" <?= in_array($res, $res_types) ? 'checked' : '' ?>> <?= $res ?>
  </label>
<?php endforeach; ?>
</div>

<label>Preferred Loan Range</label>
<select name="preferred_loan_range" required>
  <?php
    $ranges = [
      "1000 - 10000" => "RM1K - RM10K",
      "10000 - 50000" => "RM10K - RM50K",
      "50000 - 100000" => "RM50K - RM100K",
      "100000 - 150000" => "RM100K - RM150K",
      "150000 - 250000" => "RM150K - RM250K",
      "250000 - 500000" => "RM250K - RM500K",
      "500000 - 1000000" => "RM500K - RM1M",
      "1000000+" => "RM1M+"
    ];

    foreach ($ranges as $value => $label): ?>
      <option value="<?= $value ?>" <?= $education['preferred_loan_range'] === $value ? 'selected' : '' ?>>
        <?= $label ?>
      </option>
  <?php endforeach; ?>
</select>

   

      <button type="submit" class="dashboard-btn">Save Changes</button>
    </form>
  </div>
</div>
