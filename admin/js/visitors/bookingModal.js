/**
 * Booking Modal Module
 * Handles displaying booking information or available services
 */
export class BookingModal {
    constructor(onTryAgain = null) {
        this.modal = null;
        this.onTryAgain = onTryAgain;
        this.createModal();
    }

    /**
     * Create modal HTML structure
     */
    createModal() {
        // Create modal container
        const modalHTML = `
            <div id="booking-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
                <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                    <!-- Modal Header -->
                    <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center">
                        <h2 id="modal-title" class="text-2xl font-bold text-gray-800">Visitor Information</h2>
                        <button id="modal-close" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6">
                        <!-- Visitor Info Section -->
                        <div id="visitor-info" class="mb-6 pb-6 border-b border-gray-200">
                            <div class="flex items-center space-x-4">
                                <img id="modal-visitor-photo" src="" alt="Visitor Photo" class="w-20 h-20 rounded-full object-cover border-4 border-blue-500">
                                <div>
                                    <h3 id="modal-visitor-name" class="text-xl font-semibold text-gray-800"></h3>
                                    <p id="modal-visitor-id" class="text-sm text-gray-500"></p>
                                </div>
                            </div>
                        </div>

                        <!-- Booking Info Section -->
                        <div id="booking-info" class="hidden">
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                                <div class="flex items-center space-x-2 mb-2">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <h4 class="text-lg font-semibold text-green-800">You have a booking!</h4>
                                </div>
                                <div class="space-y-2 text-gray-700">
                                    <p><span class="font-medium">Service:</span> <span id="booking-service"></span></p>
                                    <p><span class="font-medium">Date:</span> <span id="booking-date"></span></p>
                                    <p><span class="font-medium">Time:</span> <span id="booking-time"></span></p>
                                    <p><span class="font-medium">Status:</span> <span id="booking-status" class="px-2 py-1 rounded text-sm"></span></p>
                                    <p id="booking-notes" class="text-sm text-gray-600 mt-2"></p>
                                </div>
                            </div>
                        </div>

                        <!-- Services Section -->
                        <div id="services-section" class="hidden">
                            <div class="mb-4">
                                <h4 class="text-lg font-semibold text-gray-800 mb-2">Available Services</h4>
                                <p class="text-sm text-gray-600">Please select a service you would like to avail:</p>
                            </div>
                            <div id="services-list" class="space-y-3">
                                <!-- Services will be dynamically inserted here -->
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="sticky bottom-0 bg-gray-50 border-t border-gray-200 px-6 py-4 flex justify-between items-center">
                        <button id="modal-try-again" class="px-4 py-2 text-blue-600 bg-white border border-blue-300 rounded-lg hover:bg-blue-50 transition-colors hidden">
                            Try Again (Face Recognition)
                        </button>
                        <div class="flex justify-end space-x-3 ml-auto">
                            <button id="modal-cancel" class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Insert modal into body
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        this.modal = document.getElementById('booking-modal');

        // Add event listeners
        const closeBtn = document.getElementById('modal-close');
        const cancelBtn = document.getElementById('modal-cancel');
        const tryAgainBtn = document.getElementById('modal-try-again');
        
        if (closeBtn) {
            closeBtn.addEventListener('click', () => this.hide());
        }
        if (cancelBtn) {
            cancelBtn.addEventListener('click', () => this.hide());
        }
        if (tryAgainBtn) {
            tryAgainBtn.addEventListener('click', () => {
                if (this.onTryAgain && typeof this.onTryAgain === 'function') {
                    this.onTryAgain();
                }
            });
        }

        // Close on backdrop click
        this.modal.addEventListener('click', (e) => {
            if (e.target === this.modal) {
                this.hide();
            }
        });

        // Close on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !this.modal.classList.contains('hidden')) {
                this.hide();
            }
        });
    }

    /**
     * Show booking information
     */
    showBooking(residentData, booking) {
        if (!this.modal) return;

        // Set visitor info
        const visitorPhoto = document.getElementById('modal-visitor-photo');
        const visitorName = document.getElementById('modal-visitor-name');
        const visitorId = document.getElementById('modal-visitor-id');

        if (visitorPhoto && residentData?.img) {
            visitorPhoto.src = residentData.img;
        }
        if (visitorName && residentData?.name) {
            visitorName.textContent = residentData.name;
        }
        if (visitorId && residentData?.phil_sys_number) {
            visitorId.textContent = `PhilSys: ${residentData.phil_sys_number}`;
        }

        // Set booking info
        const bookingInfo = document.getElementById('booking-info');
        const bookingService = document.getElementById('booking-service');
        const bookingDate = document.getElementById('booking-date');
        const bookingTime = document.getElementById('booking-time');
        const bookingStatus = document.getElementById('booking-status');
        const bookingNotes = document.getElementById('booking-notes');

        if (bookingInfo) bookingInfo.classList.remove('hidden');
        if (bookingService) bookingService.textContent = booking.service_name || 'N/A';
        
        if (bookingDate && booking.appointment_date) {
            const date = new Date(booking.appointment_date);
            bookingDate.textContent = date.toLocaleDateString('en-US', { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
        }
        
        if (bookingTime && booking.appointment_time) {
            bookingTime.textContent = booking.appointment_time;
        }
        
        if (bookingStatus) {
            bookingStatus.textContent = booking.status || 'Pending';
            const statusClass = booking.status === 'confirmed' ? 'bg-green-100 text-green-800' : 
                              booking.status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                              'bg-gray-100 text-gray-800';
            bookingStatus.className = `px-2 py-1 rounded text-sm ${statusClass}`;
        }
        
        if (bookingNotes && booking.notes) {
            bookingNotes.textContent = booking.notes;
            bookingNotes.classList.remove('hidden');
        } else if (bookingNotes) {
            bookingNotes.classList.add('hidden');
        }

        // Hide services section
        const servicesSection = document.getElementById('services-section');
        if (servicesSection) servicesSection.classList.add('hidden');
        
        // Hide try again button when booking is shown (has appointment)
        const tryAgainBtn = document.getElementById('modal-try-again');
        if (tryAgainBtn) {
            tryAgainBtn.classList.add('hidden');
        }

        // Update modal title
        const modalTitle = document.getElementById('modal-title');
        if (modalTitle) modalTitle.textContent = 'Booking Information';

        // Show modal
        this.show();
    }

    /**
     * Show available services
     * @param {Object} residentData - Resident data (null for non-residents)
     * @param {Array} services - Available services
     * @param {Function} onServiceSelect - Callback when service is selected
     */
    showServices(residentData, services, onServiceSelect = null) {
        if (!this.modal) return;

        // Set visitor info
        const visitorPhoto = document.getElementById('modal-visitor-photo');
        const visitorName = document.getElementById('modal-visitor-name');
        const visitorId = document.getElementById('modal-visitor-id');

        if (visitorPhoto && residentData?.img) {
            visitorPhoto.src = residentData.img;
        }
        if (visitorName && residentData?.name) {
            visitorName.textContent = residentData.name;
        }
        if (visitorId && residentData?.phil_sys_number) {
            visitorId.textContent = `PhilSys: ${residentData.phil_sys_number}`;
        }

        // Hide booking info
        const bookingInfo = document.getElementById('booking-info');
        if (bookingInfo) bookingInfo.classList.add('hidden');

        // Show services section
        const servicesSection = document.getElementById('services-section');
        const servicesList = document.getElementById('services-list');
        
        if (servicesSection) servicesSection.classList.remove('hidden');
        
        // Show try again button when services are shown (no appointment)
        const tryAgainBtn = document.getElementById('modal-try-again');
        if (tryAgainBtn) {
            tryAgainBtn.classList.remove('hidden');
        }
        
        if (servicesList) {
            servicesList.innerHTML = '';
            
            if (services.length === 0) {
                servicesList.innerHTML = '<p class="text-gray-500 text-center py-4">No services available at this time.</p>';
            } else {
                services.forEach(service => {
                    const serviceCard = document.createElement('div');
                    serviceCard.className = 'border border-gray-200 rounded-lg p-4 hover:border-blue-500 hover:shadow-md transition-all cursor-pointer';
                    serviceCard.innerHTML = `
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h5 class="font-semibold text-gray-800 mb-1">${this.escapeHtml(service.service_name || 'Service')}</h5>
                                <p class="text-sm text-gray-600 mb-2">${this.escapeHtml(service.description || '')}</p>
                                <div class="flex items-center space-x-4 text-xs text-gray-500">
                                    <span>⏱️ ${service.duration || 'N/A'}</span>
                                    <span>💰 ₱${service.fee || 0}</span>
                                </div>
                            </div>
                            <button class="ml-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                                Select
                            </button>
                        </div>
                    `;
                    
                    // Add click handler
                    const selectBtn = serviceCard.querySelector('button');
                    selectBtn.addEventListener('click', () => {
                        this.handleServiceSelection(service, residentData, onServiceSelect);
                    });
                    
                    servicesList.appendChild(serviceCard);
                });
            }
        }

        // Update modal title
        const modalTitle = document.getElementById('modal-title');
        if (modalTitle) modalTitle.textContent = 'Available Services';

        // Show modal
        this.show();
    }

    /**
     * Handle service selection
     */
    handleServiceSelection(service, residentData, onServiceSelect) {
        console.log('Service selected:', service);
        console.log('Resident:', residentData);
        
        // Call the callback if provided
        if (onServiceSelect && typeof onServiceSelect === 'function') {
            onServiceSelect(service);
        }
        
        // Show confirmation
        alert(`Service "${service.service_name}" selected. Processing...`);
        this.hide();
    }

    /**
     * Show modal
     */
    show() {
        if (this.modal) {
            this.modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden'; // Prevent background scrolling
            document.dispatchEvent(new CustomEvent('visitor:modal-opened', { detail: { id: 'booking-modal' } }));
        }
    }

    /**
     * Hide modal
     */
    hide() {
        if (this.modal) {
            this.modal.classList.add('hidden');
            document.body.style.overflow = ''; // Restore scrolling
            document.dispatchEvent(new CustomEvent('visitor:modal-closed', { detail: { id: 'booking-modal' } }));
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
