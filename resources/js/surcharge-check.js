// Surcharge Check Functions
const SurchargeCheck = {
    // Check surcharge for a location
    check: async function(country, zipCode, partnerId = null) {
        try {
            const response = await fetch('/api/surcharge/check', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ country, zip_code: zipCode, partner_id: partnerId }),
            });

            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Error checking surcharge:', error);
            return { success: false, error: error.message };
        }
    },

    // Display surcharge warning in UI
    displayWarning: function(data, containerId) {
        const container = document.getElementById(containerId);
        if (!container) return;

        if (data.success && data.is_remote) {
            container.innerHTML = `
                <div class="bg-yellow-50 border border-yellow-400 text-yellow-700 px-4 py-3 rounded-lg mb-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-yellow-500"></i>
                        </div>
                        <div class="ml-3">
                            <p class="font-medium">⚠️ Remote Area Surcharge Applied</p>
                            <p class="text-sm">${data.data.message}</p>
                            <p class="text-sm text-yellow-600 mt-1">${data.data.warning}</p>
                            ${data.data.surcharge_amount > 0 ? `
                                <p class="text-sm font-medium mt-1">Additional Charge: $${data.data.surcharge_amount.toFixed(2)}</p>
                            ` : ''}
                            ${data.data.surcharge_percentage > 0 ? `
                                <p class="text-sm font-medium mt-1">Additional Charge: ${data.data.surcharge_percentage}% of base rate</p>
                            ` : ''}
                        </div>
                    </div>
                </div>
            `;
        } else {
            container.innerHTML = `
                <div class="bg-green-50 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">
                    <i class="fas fa-check-circle mr-2"></i>
                    ${data.message || 'No remote area surcharge applies to this location.'}
                </div>
            `;
        }
    },

    // Setup auto-check on form fields
    setupAutoCheck: function(countryFieldId, zipFieldId, resultContainerId, partnerId = null) {
        const countryField = document.getElementById(countryFieldId);
        const zipField = document.getElementById(zipFieldId);
        const container = document.getElementById(resultContainerId);

        if (!countryField || !zipField || !container) return;

        const check = async () => {
            const country = countryField.value.trim();
            const zip = zipField.value.trim();

            if (country && zip) {
                const result = await this.check(country, zip, partnerId);
                this.displayWarning(result, resultContainerId);
            } else {
                container.innerHTML = '';
            }
        };

        // Check on input change
        zipField.addEventListener('blur', check);
        zipField.addEventListener('input', check);
        countryField.addEventListener('change', check);

        // Initial check if fields have values
        setTimeout(check, 500);
    }
};

// Make it available globally
window.SurchargeCheck = SurchargeCheck;