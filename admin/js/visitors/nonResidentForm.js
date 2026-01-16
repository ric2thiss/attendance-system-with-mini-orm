/**
 * Non-Resident Visitor Form Module
 * Handles form display and submission for non-resident visitors
 */
export class NonResidentForm {
    constructor(visitorAPI, onTryAgain = null) {
        this.visitorAPI = visitorAPI;
        this.onTryAgain = onTryAgain;
        this.formModal = null;
        this.createFormModal();
    }

    /**
     * Create form modal HTML structure
     */
    createFormModal() {
        const modalHTML = `
            <div id="non-resident-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
                <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                    <!-- Modal Header -->
                    <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center">
                        <h2 class="text-2xl font-bold text-gray-800">Visitor Registration</h2>
                        <button id="non-resident-close" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6">
                        <p class="text-gray-600 mb-6">Please provide your information to proceed.</p>

                        <!-- Visitor Information Form -->
                        <form id="non-resident-form" class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- First Name -->
                                <div>
                                    <label for="visitor-first-name" class="block text-sm font-medium text-gray-700 mb-1">
                                        First Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="visitor-first-name" name="first_name" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>

                                <!-- Middle Name -->
                                <div>
                                    <label for="visitor-middle-name" class="block text-sm font-medium text-gray-700 mb-1">
                                        Middle Name
                                    </label>
                                    <input type="text" id="visitor-middle-name" name="middle_name"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Last Name -->
                                <div>
                                    <label for="visitor-last-name" class="block text-sm font-medium text-gray-700 mb-1">
                                        Last Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="visitor-last-name" name="last_name" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>

                                <!-- Birthdate -->
                                <div>
                                    <label for="visitor-birthdate" class="block text-sm font-medium text-gray-700 mb-1">
                                        Birthdate <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date" id="visitor-birthdate" name="birthdate" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>

                            <!-- Address -->
                            <div>
                                <label for="visitor-address" class="block text-sm font-medium text-gray-700 mb-1">
                                    Address <span class="text-red-500">*</span>
                                </label>
                                <textarea id="visitor-address" name="address" rows="3" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="House Number, Street, Barangay, City, Province"></textarea>
                            </div>

                            <!-- Services Section (will be populated dynamically) -->
                            <div id="non-resident-services-section" class="hidden">
                                <h3 class="text-lg font-semibold text-gray-800 mb-3">Select Service</h3>
                                <div id="non-resident-services-list" class="space-y-3">
                                    <!-- Services will be dynamically inserted here -->
                                </div>
                            </div>

                            <!-- Error Message -->
                            <div id="non-resident-error" class="hidden bg-red-50 border border-red-200 rounded-lg p-3 text-red-700 text-sm"></div>
                        </form>
                    </div>

                    <!-- Modal Footer -->
                    <div class="sticky bottom-0 bg-gray-50 border-t border-gray-200 px-6 py-4 flex justify-between items-center">
                        <button id="non-resident-try-again" class="px-4 py-2 text-blue-600 bg-white border border-blue-300 rounded-lg hover:bg-blue-50 transition-colors">
                            Try Again (Face Recognition)
                        </button>
                        <div class="flex justify-end space-x-3 ml-auto">
                            <button id="non-resident-cancel" class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                Cancel
                            </button>
                            <button id="non-resident-submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                Continue
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHTML);
        this.formModal = document.getElementById('non-resident-modal');

        // Add event listeners
        const closeBtn = document.getElementById('non-resident-close');
        const cancelBtn = document.getElementById('non-resident-cancel');
        const submitBtn = document.getElementById('non-resident-submit');
        const tryAgainBtn = document.getElementById('non-resident-try-again');
        const form = document.getElementById('non-resident-form');

        if (closeBtn) {
            closeBtn.addEventListener('click', () => this.hide());
        }
        if (cancelBtn) {
            cancelBtn.addEventListener('click', () => this.hide());
        }
        if (submitBtn) {
            submitBtn.addEventListener('click', () => this.handleSubmit());
        }
        if (tryAgainBtn) {
            tryAgainBtn.addEventListener('click', () => {
                if (this.onTryAgain && typeof this.onTryAgain === 'function') {
                    this.onTryAgain();
                }
            });
        }

        // Close on backdrop click
        this.formModal.addEventListener('click', (e) => {
            if (e.target === this.formModal) {
                this.hide();
            }
        });

        // Close on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !this.formModal.classList.contains('hidden')) {
                this.hide();
            }
        });

        // Prevent form submission on Enter (we'll handle it manually)
        if (form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleSubmit();
            });
        }
    }

    /**
     * Show form for non-resident visitor
     */
    async show() {
        if (!this.formModal) return;

        // Reset form
        const form = document.getElementById('non-resident-form');
        if (form) form.reset();

        // Hide error
        const errorDiv = document.getElementById('non-resident-error');
        if (errorDiv) {
            errorDiv.classList.add('hidden');
            errorDiv.textContent = '';
        }

        // Fetch services
        try {
            const services = await this.visitorAPI.fetchServices();
            this.populateServices(services);
        } catch (error) {
            console.error('Error fetching services:', error);
            this.showError('Failed to load services. Please try again.');
        }

        // Show modal
        this.formModal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    /**
     * Hide form modal
     */
    hide() {
        if (this.formModal) {
            this.formModal.classList.add('hidden');
            document.body.style.overflow = '';
        }
    }

    /**
     * Populate services list
     */
    populateServices(services) {
        const servicesSection = document.getElementById('non-resident-services-section');
        const servicesList = document.getElementById('non-resident-services-list');

        if (!servicesSection || !servicesList) return;

        if (services.length === 0) {
            servicesSection.classList.add('hidden');
            return;
        }

        servicesSection.classList.remove('hidden');
        servicesList.innerHTML = '';

        services.forEach(service => {
            const serviceCard = document.createElement('div');
            serviceCard.className = 'border border-gray-200 rounded-lg p-4 hover:border-blue-500 hover:shadow-md transition-all cursor-pointer';
            serviceCard.dataset.serviceId = service.service_id;
            serviceCard.dataset.serviceName = service.service_name;
            serviceCard.innerHTML = `
                <div class="flex items-start">
                    <input type="radio" name="service" value="${service.service_id}" id="service-${service.service_id}" 
                        class="mt-1 mr-3" required>
                    <label for="service-${service.service_id}" class="flex-1 cursor-pointer">
                        <h5 class="font-semibold text-gray-800 mb-1">${this.escapeHtml(service.service_name || 'Service')}</h5>
                        <p class="text-sm text-gray-600 mb-2">${this.escapeHtml(service.description || '')}</p>
                        <div class="flex items-center space-x-4 text-xs text-gray-500">
                            <span>⏱️ ${service.duration || 'N/A'}</span>
                            <span>💰 ₱${service.fee || 0}</span>
                        </div>
                    </label>
                </div>
            `;

            // Add click handler to select radio
            serviceCard.addEventListener('click', (e) => {
                if (e.target.type !== 'radio') {
                    const radio = serviceCard.querySelector('input[type="radio"]');
                    if (radio) radio.checked = true;
                }
            });

            servicesList.appendChild(serviceCard);
        });
    }

    /**
     * Handle form submission
     */
    async handleSubmit() {
        const form = document.getElementById('non-resident-form');
        if (!form) return;

        // Validate form
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        // Get form data
        const formData = new FormData(form);
        const visitorData = {
            first_name: formData.get('first_name').trim(),
            middle_name: formData.get('middle_name')?.trim() || null,
            last_name: formData.get('last_name').trim(),
            birthdate: formData.get('birthdate'),
            address: formData.get('address').trim()
        };

        // Get selected service
        const selectedServiceRadio = form.querySelector('input[name="service"]:checked');
        if (!selectedServiceRadio) {
            this.showError('Please select a service.');
            return;
        }

        const selectedService = {
            service_id: selectedServiceRadio.value,
            service_name: selectedServiceRadio.closest('[data-service-name]')?.dataset.serviceName || 'Service'
        };

        // Validate required fields
        if (!visitorData.first_name || !visitorData.last_name || !visitorData.birthdate || !visitorData.address) {
            this.showError('Please fill in all required fields.');
            return;
        }

        try {
            // Log visitor entry
            await this.visitorAPI.logVisitor({
                resident_id: null,
                first_name: visitorData.first_name,
                middle_name: visitorData.middle_name,
                last_name: visitorData.last_name,
                birthdate: visitorData.birthdate,
                address: visitorData.address,
                purpose: selectedService.service_name,
                is_resident: false,
                had_booking: false
            });

            // Submit service application to external API (if configured)
            // Note: Service application data structure depends on external API requirements
            // This is a placeholder - adjust based on actual API requirements
            if (selectedService.external_api_url) {
                const servicePayload = {
                    visitor: visitorData,
                    service: selectedService
                };
                
                try {
                    await this.visitorAPI.submitServiceApplication(
                        servicePayload,
                        selectedService.external_api_url
                    );
                } catch (apiError) {
                    console.error('Error submitting to external API:', apiError);
                    // Continue even if external API fails
                }
            }

            // Show success message
            alert('Visitor registered successfully!');
            this.hide();

        } catch (error) {
            console.error('Error submitting form:', error);
            this.showError(error.message || 'Failed to register visitor. Please try again.');
        }
    }

    /**
     * Show error message
     */
    showError(message) {
        const errorDiv = document.getElementById('non-resident-error');
        if (errorDiv) {
            errorDiv.textContent = message;
            errorDiv.classList.remove('hidden');
        }
    }

    /**
     * Escape HTML to prevent XSS
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}
