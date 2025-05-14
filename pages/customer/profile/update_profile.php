<!-- Personal Modal -->
<div class="modal" id="personalModal">
  <div class="modal-content">
    <span class="close-btn" onclick="closeModal('personalModal')">&times;</span>
    <h2>Edit Personal Information</h2>
    <form method="POST">
      <input type="hidden" name="section" value="personal">
      
      <label>First Name</label>
      <input type="text" name="first_name" value="<?= htmlspecialchars($personal['first_name']) ?>" required>

      <label>Last Name</label>
      <input type="text" name="last_name" value="<?= htmlspecialchars($personal['last_name']) ?>" required>

      <label>Phone Number</label>
      <input type="text" name="phone" value="<?= htmlspecialchars($personal['phone_number']) ?>" required>

      <label>Address</label>
      <input type="text" name="address" value="<?= htmlspecialchars($personal['address']) ?>" required>

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
    <form method="POST">
      <input type="hidden" name="section" value="business">
      
      <label>Business Type</label>
      <input type="text" name="business_type" value="<?= htmlspecialchars($business['business_type']) ?>" required>

      <label>Business Name</label>
      <input type="text" name="business_name" value="<?= htmlspecialchars($business['business_name']) ?>" required>

      <label>Industry</label>
      <input type="text" name="industry" value="<?= htmlspecialchars($business['industry']) ?>" required>

      <label>Experience</label>
<select name="experience" class="form-select" required>
    <option value="" disabled <?= empty($business['business_experience']) ? 'selected' : '' ?>>Select Experience</option>
    <option value="None" <?= $business['business_experience'] === 'None' ? 'selected' : '' ?>>None</option>
    <option value="Less than 3 months" <?= $business['business_experience'] === 'Less than 3 months' ? 'selected' : '' ?>>Less than 3 months</option>
    <option value="3 months" <?= $business['business_experience'] === '3 months' ? 'selected' : '' ?>>3 months</option>
    <option value="6 months" <?= $business['business_experience'] === '6 months' ? 'selected' : '' ?>>6 months</option>
    <option value="More than 6 months" <?= $business['business_experience'] === 'More than 6 months' ? 'selected' : '' ?>>More than 6 months</option>
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
    <form method="POST">
      <input type="hidden" name="section" value="education">
      
      <label>Education Type</label>
      <select name="education_type" required>
        <option value="">-- Select --</option>
        <option value="Formal" <?= $education['education_type'] === 'Formal' ? 'selected' : '' ?>>Formal</option>
        <option value="Informal" <?= $education['education_type'] === 'Informal' ? 'selected' : '' ?>>Informal</option>
      </select>

      <label>Certification Level</label>
      <select name="certification" required>
        <option value="">-- Select --</option>
        <option value="SPM" <?= $education['certification_level'] === 'SPM' ? 'selected' : '' ?>>SPM</option>
        <option value="Diploma" <?= $education['certification_level'] === 'Diploma' ? 'selected' : '' ?>>Diploma</option>
        <option value="Degree" <?= $education['certification_level'] === 'Degree' ? 'selected' : '' ?>>Degree</option>
        <option value="Master" <?= $education['certification_level'] === 'Master' ? 'selected' : '' ?>>Master</option>
        <option value="PhD" <?= $education['certification_level'] === 'PhD' ? 'selected' : '' ?>>PhD</option>
        <option value="Other" <?= $education['certification_level'] === 'Other' ? 'selected' : '' ?>>Other</option>
      </select>

      <button type="submit" class="dashboard-btn">Save Changes</button>
    </form>
  </div>
</div>
