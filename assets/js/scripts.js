

    // Toggle Business Details Section
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
            }
        });
    }
