<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Education & Resources Preferences - KUSMA</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/forms.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<div class="container mt-5">
    <h1>Education & Resources Preferences</h1>
    <h5>Complete your education and resource preferences to get the best options</h5>
    <div class="progress my-4">
        <div class="progress-bar" role="progressbar" style="width: 100%;" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">
            Step 3 of 3: Education & Resources Preferences
        </div>
    </div>

    <form id="eduForm" action="process_edu_resources.php" method="POST">
        <div class="mb-3">
            <label for="educationType" class="form-label">Education Type</label>
            <select class="form-select" id="educationType" name="educationType" required>
                <option value="" selected disabled>Select Education Type</option>
                <option value="Still Studying">Still Studying</option>
                <option value="Graduated">Graduated</option>
            </select>
        </div>
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
        </div>
        <div class="mb-3">
            <label for="loanAmount" class="form-label">Preferred Loan Amount (if applicable)</label>
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
        <div class="mb-3">
            <label for="urgency" class="form-label">How soon do you need this resource?</label>
            <select class="form-select" id="urgency" name="urgency" required>
                <option value="" selected disabled>Select Urgency</option>
                <option value="Immediate">Immediately</option>
                <option value="1-3 Months">Within 1-3 months</option>
                <option value="More than 3 Months">More than 3 months</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary w-100">Submit</button>
    </form>
</div>

<script>
document.getElementById('eduForm').addEventListener('submit', function(e) {
    const educationType = document.getElementById('educationType').value;
    const certificationLevel = document.getElementById('certificationLevel').value;
    const employmentStatus = document.getElementById('employmentStatus').value;
    const urgency = document.getElementById('urgency').value;
    const resourcesChecked = document.querySelectorAll('input[name="resourceType[]"]:checked').length;

    if (!educationType || !certificationLevel || !employmentStatus || !urgency) {
        e.preventDefault();
        Swal.fire({
            icon: 'error',
            title: 'Missing Fields',
            text: 'Please fill in all required fields.',
            confirmButtonColor: '#7B1FA2'
        });
        return;
    }

    if (resourcesChecked === 0) {
        e.preventDefault();
        Swal.fire({
            icon: 'warning',
            title: 'No Resource Type Selected',
            text: 'Please select at least one type of resource.',
            confirmButtonColor: '#7B1FA2'
        });
    }
});
</script>
</body>
</html>
