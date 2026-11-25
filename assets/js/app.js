document.addEventListener("DOMContentLoaded", () => {

    // Display dynamic green/red feedback banners
    window.showAlert = (elementId, message, isSuccess = false) => {

        const alertBox = document.getElementById(elementId);
        if (!alertBox) return;

        alertBox.className = isSuccess ? 'alert alert-success' : 'alert alert-danger';
        alertBox.innerText = message;
        alertBox.style.display = 'block';

        setTimeout(() => {
            alertBox.style.display = 'none';
        }, 5000);

    };

    // Asynchronous Form Submission (Used by Login, Register, and Book Appointment)
    window.handleModularSubmit = async (formElement, endpoint, alertId, reloadOnSuccess = false) => {

        const formData = new FormData(formElement);

        try {

            const response = await fetch(endpoint, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {

                showAlert(alertId, result.message, true);
                formElement.reset();

                if (result.redirect) {
                    setTimeout(() => window.location.href = result.redirect, 1200);
                } else if (reloadOnSuccess) {
                    setTimeout(() => window.location.reload(), 1200);
                }

            } else {
                showAlert(alertId, result.message, false);
            }

        } catch (error) {
            showAlert(alertId, "Server communication error. Please try again.", false);
        }
    };

    // Live Price & Duration Calculator
    const serviceSelector = document.getElementById("service_select");
    const priceDisplay = document.getElementById("price_display");
    const durationDisplay = document.getElementById("duration_display");

    if (serviceSelector && priceDisplay && durationDisplay) {

        serviceSelector.addEventListener("change", function () {

            const selectedOption = this.options[this.selectedIndex];
            const price = selectedOption.getAttribute("data-price") || "0.00";
            const duration = selectedOption.getAttribute("data-duration") || "0";

            priceDisplay.textContent = `R ${parseFloat(price).toFixed(2)}`;
            durationDisplay.textContent = `${duration} mins`;

        });
    }

    // Used for Cancel, Update Status, and Delete buttons
    window.callApi = async (endpoint, payload, confirmMsg = null) => {

        if (confirmMsg && !confirm(confirmMsg)) return;

        const formData = new FormData();
        for (const [key, val] of Object.entries(payload)) {
            formData.append(key, val);
        }

        try {

            const response = await fetch(endpoint, {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.success) {
                alert(result.message);
                if (result.redirect) {
                    window.location.href = result.redirect;
                } else {
                    window.location.reload();
                }
            } else {
                alert(result.message);
            }

        } catch (err) {
            alert("Error completing system request.");
        }
        
    };

    // Toggle visibility of the change password card
    window.togglePasswordCard = () => {

        const card = document.getElementById('password-card');
        if (!card) return;

        card.classList.toggle('hidden-card');

        if (!card.classList.contains('hidden-card')) {
            card.scrollIntoView({ behavior: 'smooth' });
        }
        
    };

});