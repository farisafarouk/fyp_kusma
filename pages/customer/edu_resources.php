<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Education & Resources Preferences - KUSMA</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../../assets/css/forms.css">
</head>
<body>
<div class="container mt-5">
    <h1>Education & Resources Preferences</h1>
    <h5>Complete your education and resource preferences to get the best options</h5>

    <!-- Progress Bar -->
    <div class="progress my-4">
        <div class="progress-bar" role="progressbar" style="width: 100%;" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">
            Step 3 of 3: Education & Resources Preferences
        </div>
    </div>

    <form id="resourcePreferencesForm">
        <!-- Education Type -->
        <div class="mb-3">
            <label for="educationType" class="form-label">Education Type</label>
            <select class="form-select" id="educationType" name="educationType" required>
                <option value="" selected disabled>Select Education Type</option>
                <option value="Still Studying">Still Studying</option>
                <option value="Graduated">Graduated</option>
            </select>
        </div>

        <!-- Certification Level -->
        <div class="mb-3">
            <label for="certificationLevel" class="form-label">Certification Level</label>
            <select class="form-select" id="certificationLevel" name="certificationLevel" required>
                <option value="" selected disabled>Select Certification Level</option>
                <option value="SPM / SKM">SPM / SKM</option>
                <option value="Diploma">Diploma</option>
                <option value="Degree">Degree</option>
                <option value="Master / PhD">Master / PhD</option>
            </select>
        </div>

        <!-- Current Employment -->
        <div class="mb-3">
            <label for="employmentStatus" class="form-label">Current Employment Status</label>
            <select class="form-select" id="employmentStatus" name="employmentStatus" required>
                <option value="" selected disabled>Select Employment Status</option>
                <option value="Employed">Employed</option>
                <option value="Self-Employed">Self-Employed</option>
                <option value="Unemployed">Unemployed</option>
                <option value="Student">Student</option>
            </select>
        </div>

        <!-- Resource Type -->
        <div class="mb-3">
            <label class="form-label">What type of resource are you looking for? (Select all that apply)</label>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="loans" name="resourceType[]" value="Loans">
                <label class="form-check-label" for="loans">Loans</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="grants" name="resourceType[]" value="Grants">
                <label class="form-check-label" for="grants">Grants</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="trainingPrograms" name="resourceType[]" value="Training Programs">
                <label class="form-check-label" for="trainingPrograms">Training Programs</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="premises" name="resourceType[]" value="Premises">
                <label class="form-check-label" for="premises">Premise Lot</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="otherResource" name="resourceType[]" value="Other">
                <label class="form-check-label" for="otherResource">Other (specify)</label>
            </div>
            <div id="otherResourceInput" style="display: none;" class="mt-2">
                <input type="text" class="form-control" placeholder="Please specify">
            </div>
        </div>

        <!-- Loan Amount -->
        <div id="loanAmountSection" style="display: none;">
            <div class="mb-3">
                <label for="loanAmount" class="form-label">Preferred Loan Amount (RM)</label>
                <select class="form-select" id="loanAmount" name="loanAmount">
                    <option value="" selected disabled>Select Loan Amount</option>
                    <option value="1000 - 10000">1,000 - 10,000</option>
                    <option value="10000 - 50000">10,000 - 50,000</option>
                    <option value="50000 - 100000">50,000 - 100,000</option>
                    <option value="100000 - 150000">100,000 - 150,000</option>
                    <option value="150000 - 250000">150,000 - 250,000</option>
                    <option value="250000 - 500000">250,000 - 500,000</option>
                    <option value="500000 - 1000000">500,000 - 1,000,000</option>
                    <option value="1000000+">1,000,000+</option>
                </select>
            </div>
        </div>

        <!-- Training Areas -->
        <div id="trainingSection" style="display: none;">
            <div class="mb-3">
                <label for="trainingAreas" class="form-label">Which areas of training are you interested in?</label>
                <select class="form-select" id="trainingAreas" name="trainingAreas">
                    <option value="" selected disabled>Select Training Area</option>
                    <option value="Digital Marketing">Digital Marketing</option>
                    <option value="Business Management">Business Management</option>
                    <option value="Financial Literacy">Financial Literacy</option>
                    <option value="Leadership and Team Building">Leadership and Team Building</option>
                    <option value="Technical/Industry-specific Skills">Technical/Industry-specific Skills</option>
                    <option value="Other">Other (specify)</option>
                </select>
            </div>

            <!-- "Other" Training Area -->
            <div id="otherTrainingArea" style="display: none;" class="mb-3">
                <label for="otherTrainingInput" class="form-label">Please specify</label>
                <input type="text" class="form-control" id="otherTrainingInput" name="otherTrainingInput" placeholder="Specify training area">
            </div>
        </div>

        <!-- Urgency -->
        <div class="mb-3">
            <label for="urgency" class="form-label">How soon do you need this resource?</label>
            <select class="form-select" id="urgency" name="urgency">
                <option value="" selected disabled>Select Urgency</option>
                <option value="Immediate">Immediately</option>
                <option value="1-3 Months">Within 1-3 months</option>
                <option value="More than 3 Months">More than 3 months</option>
            </select>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary w-100">Submit</button>
    </form>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom JS -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const loansCheckbox = document.getElementById("loans");
        const loanAmountSection = document.getElementById("loanAmountSection");
        const otherResourceCheckbox = document.getElementById("otherResource");
        const otherResourceInput = document.getElementById("otherResourceInput");
        const trainingProgramsCheckbox = document.getElementById("trainingPrograms");
        const trainingSection = document.getElementById("trainingSection");
        const trainingAreas = document.getElementById("trainingAreas");
        const otherTrainingArea = document.getElementById("otherTrainingArea");
        const otherTrainingInput = document.getElementById("otherTrainingInput");

        loansCheckbox.addEventListener("change", function () {
            loanAmountSection.style.display = this.checked ? "block" : "none";
        });

        otherResourceCheckbox.addEventListener("change", function () {
            otherResourceInput.style.display = this.checked ? "block" : "none";
        });

        trainingProgramsCheckbox.addEventListener("change", function () {
            trainingSection.style.display = this.checked ? "block" : "none";
        });

        trainingAreas.addEventListener("change", function () {
            otherTrainingArea.style.display = this.value === "Other" ? "block" : "none";
            otherTrainingInput.required = this.value === "Other";
        });
    });
</script>
</body>
</html>
