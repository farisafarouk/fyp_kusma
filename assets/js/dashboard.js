document.addEventListener("DOMContentLoaded", () => {
    // Fetch user-specific data (Simulated for now)
    const userName = "John Doe"; // Replace with dynamic data
    const programs = [
        { name: "Program A", description: "A great program for startups." },
        { name: "Program B", description: "Ideal for growing businesses." },
        { name: "Program C", description: "Advanced business grants." }
    ];

    const consultants = [
        { name: "Jane Smith", expertise: "Business Strategy" },
        { name: "John Doe", expertise: "Financial Planning" }
    ];

    // Update User Name
    document.getElementById("userName").textContent = `Welcome, ${userName}`;

    // Populate Resources
    const resourceList = document.getElementById("resourceList");
    programs.forEach(program => {
        const resourceCard = `
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5>${program.name}</h5>
                        <p>${program.description}</p>
                        <a href="#" class="btn btn-primary">View Details</a>
                    </div>
                </div>
            </div>
        `;
        resourceList.innerHTML += resourceCard;
    });

    // Populate Consultants
    const consultantList = document.getElementById("consultantList");
    consultants.forEach(consultant => {
        const consultantCard = `
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5>${consultant.name}</h5>
                        <p>Expertise: ${consultant.expertise}</p>
                        <a href="#" class="btn btn-primary">Book Appointment</a>
                    </div>
                </div>
            </div>
        `;
        consultantList.innerHTML += consultantCard;
    });
});
