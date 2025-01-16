// Toggle Business Details Section
document.addEventListener("DOMContentLoaded", function () {
    const businessDetailsSection = document.getElementById("businessDetails");
    const businessYes = document.getElementById("businessYes");
    const businessNo = document.getElementById("businessNo");

    if (businessYes && businessNo) {
        businessYes.addEventListener("change", function () {
            if (this.checked) {
                businessDetailsSection.style.display = "block";
            }
        });

        businessNo.addEventListener("change", function () {
            if (this.checked) {
                businessDetailsSection.style.display = "none";

                // Clear fields in hidden sections
                clearFields(businessDetailsSection);
            }
        });
    }

    // Utility function to clear hidden field values
    function clearFields(section) {
        const inputs = section.querySelectorAll("input, select");
        inputs.forEach((input) => {
            input.value = "";
        });
    }
});
