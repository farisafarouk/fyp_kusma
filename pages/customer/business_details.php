<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Details - KUSMA</title>

    <!-- Google Fonts - Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../../assets/css/forms.css">
</head>
<body>

<div class="container mt-5 form-step">
    <h1>Business Details</h1>
    <h5>Provide your business information to help us find suitable loan programs for you</h5>
    <div class="progress my-4">
    <div class="progress-bar" role="progressbar" style="width: 66%;" aria-valuenow="66" aria-valuemin="0" aria-valuemax="100">
        Step 2 of 3: Business Details
    </div>
</div>



    <form action="education_details.html">

        <!-- Do you have a registered business? -->
        <div class="mb-3">
            <label class="form-label">Do you have a registered business (SSM)?</label>
            <div class="form-check">
                <input class="form-check-input" type="radio" id="businessYes" name="businessRegistered" value="Yes" required>
                <label class="form-check-label" for="businessYes">Yes</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" id="businessNo" name="businessRegistered" value="No" required>
                <label class="form-check-label" for="businessNo">No</label>
            </div>
        </div>

        <!-- Business Information (Hidden if "No") -->
        <div id="businessDetails" style="display: none;">
            <div class="mb-3">
                <label for="industry" class="form-label">Industry</label>
                <select class="form-select" id="industry" name="industry">
                    <option value="" selected disabled>Select Industry</option>
                    <option value="Agriculture & Food">Agriculture & Food</option>
<option value="Technology & IT">Technology & IT</option>
<option value="Construction & Real Estate">Construction & Real Estate</option>
<option value="Healthcare & Pharmaceuticals">Healthcare & Pharmaceuticals</option>
<option value="Education & Training">Education & Training</option>
<option value="Finance & Banking">Finance & Banking</option>
<option value="Tourism & Hospitality">Tourism & Hospitality</option>
<option value="Transportation & Logistics">Transportation & Logistics</option>
<option value="Energy & Utilities">Energy & Utilities</option>
<option value="Entertainment & Media">Entertainment & Media</option>
<option value="Manufacturing & Production">Manufacturing & Production</option>
<option value="Retail & E-commerce">Retail & E-commerce</option>
<option value="Professional & Business Services">Professional & Business Services</option>
<option value="Public & Non-Profit">Public & Non-Profit</option>

                    
                </select>
            </div>

            <div class="mb-3">
                <label for="businessName" class="form-label">Business Name</label>
                <input type="text" class="form-control" id="businessName" name="businessName" placeholder="Enter your business name">
            </div>

            <div class="mb-3">
                <label for="businessReg" class="form-label">Business Registration Number (SSM)</label>
                <input type="text" class="form-control" id="businessReg" name="businessReg" placeholder="Enter your registration number">
            </div>

            <div class="mb-3">
                <label for="businessPremises" class="form-label">Business Premises</label>
                <select class="form-select" id="businessPremises" name="businessPremises">
                    <option value="" selected disabled>Select Premises Type</option>
                    <option value="Online">Online</option>
                    <option value="Physical">Physical</option>
                    <option value="Both">Both</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="businessExperience" class="form-label">Business Experience</label>
                <select class="form-select" id="businessExperience" name="businessExperience">
                    <option value="" selected disabled>Select Experience</option>
                    <option value="Less than 6 months">Less than 6 months</option>
                    <option value="1-2 years">1-2 years</option>
                    <option value="More than 2 years">More than 2 years</option>
                </select>
            </div>
        </div>

        <!-- Navigation Buttons -->
        <div class="d-flex justify-content-between mt-4">
            <a href="personal_details.php" class="btn btn-secondary prev-btn">Previous</a>
            <button type="submit" class="btn btn-primary next-btn">Next</button>
        </div>
    </form>
</div>

<!-- Bootstrap JS CDN -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom JS -->
<script src="../../assets/js/scripts.js"></script>
</body>
</html>
