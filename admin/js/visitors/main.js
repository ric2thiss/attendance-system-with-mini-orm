/**
 * Visitors Page Main Entry Point
 * Initializes all modules for the visitors page
 */
import { FaceRecognition } from './faceRecognition.js';
import { WebcamHandler } from './webcamHandler.js';
import { RecognitionLogic } from './recognitionLogic.js';
import { ActivityLogger } from './activityLogger.js';
import { VisitorAPI } from './api.js';
import { StatusUpdater } from './statusUpdater.js';
import { BookingModal } from './bookingModal.js';
import { NonResidentForm } from './nonResidentForm.js';
import { initSidebar } from '../shared/sidebar.js';
import { initSharedClock } from '../shared/clock.js';

// Known faces - will be loaded from API
let labeledDescriptors = [];

const RECOGNITION_THRESHOLD = 0.4; // Lower value = stricter match
const DETECTION_INTERVAL = 1000; // Check every 1000ms (1 second)

// Initialize modules
let faceRecognition;
let webcamHandler;
let recognitionLogic;
let activityLogger;
let visitorAPI;
let statusUpdater;
let bookingModal;
let nonResidentForm;
let faceRecognitionTimeout = null;
const FACE_RECOGNITION_TIMEOUT = 5000; // 5 seconds

/**
 * Handle recognized face
 */
async function handleRecognizedFace(id, name, residentData) {
    // Check if person is already logged
    if (recognitionLogic.isLoggedToday(id)) {
        return; // Skip if already logged
    }

    // Mark as logged
    recognitionLogic.markAsLogged(id, 300000); // 5 minutes

    // Update recognition status
    const personDetails = labeledDescriptors.find(p => p.id === id);
    statusUpdater.updateRecognized(name, personDetails);

    // Add to activity log
    activityLogger.addLogEntry(name);

    // Check for booking
    try {
        const bookingResult = await visitorAPI.checkBooking(id);
        
        if (bookingResult.has_booking && bookingResult.booking) {
            // Scenario 1: Resident with booking - auto-log
            await logResidentWithBooking(residentData, bookingResult.booking);
            bookingModal.showBooking(residentData, bookingResult.booking);
        } else {
            // Scenario 2: Resident without booking - show services
            const services = await visitorAPI.fetchServices();
            bookingModal.showServices(residentData, services, async (service) => {
                await logResidentWithoutBooking(residentData, service);
            });
        }
    } catch (error) {
        console.error("Error checking booking:", error);
        // On error, still show services modal
        try {
            const services = await visitorAPI.fetchServices();
            bookingModal.showServices(residentData, services, async (service) => {
                await logResidentWithoutBooking(residentData, service);
            });
        } catch (serviceError) {
            console.error("Error fetching services:", serviceError);
        }
    }
}

/**
 * Log resident visitor with booking (Scenario 1)
 */
async function logResidentWithBooking(residentData, booking) {
    try {
        // Fetch address
        const address = await visitorAPI.fetchResidentAddress(residentData.resident_id);
        
        if (!address) {
            console.error("Could not fetch address for resident");
            return;
        }

        // Log visitor entry
        await visitorAPI.logVisitor({
            resident_id: residentData.resident_id,
            first_name: residentData.first_name,
            middle_name: residentData.middle_name || null,
            last_name: residentData.last_name,
            address: address,
            purpose: booking.service_name || booking.purpose || 'Service',
            is_resident: true,
            had_booking: true,
            booking_id: booking.booking_id || null
        });
    } catch (error) {
        console.error("Error logging resident with booking:", error);
    }
}

/**
 * Log resident visitor without booking (Scenario 2)
 */
async function logResidentWithoutBooking(residentData, service) {
    try {
        // Fetch address
        const address = await visitorAPI.fetchResidentAddress(residentData.resident_id);
        
        if (!address) {
            console.error("Could not fetch address for resident");
            return;
        }

        // Log visitor entry
        await visitorAPI.logVisitor({
            resident_id: residentData.resident_id,
            first_name: residentData.first_name,
            middle_name: residentData.middle_name || null,
            last_name: residentData.last_name,
            address: address,
            purpose: service.service_name || service.name || 'Service',
            is_resident: true,
            had_booking: false
        });

        // Submit service application to external API (if service data provided)
        if (service.external_api_url && service.application_data) {
            try {
                await visitorAPI.submitServiceApplication(
                    service.application_data,
                    service.external_api_url
                );
            } catch (apiError) {
                console.error("Error submitting to external API:", apiError);
                // Continue even if external API fails
            }
        }
    } catch (error) {
        console.error("Error logging resident without booking:", error);
    }
}

/**
 * Try again - reset recognition state and allow re-capture
 */
function tryAgain() {
    // Reset recognition state
    if (recognitionLogic) {
        recognitionLogic.resetLoggedState();
    }
    
    // Hide any open modals
    if (bookingModal) {
        bookingModal.hide();
    }
    if (nonResidentForm) {
        nonResidentForm.hide();
    }
    
    // Reset status to ready
    if (statusUpdater) {
        statusUpdater.resetToReady();
    }
    
    // Clear timeout if exists
    if (faceRecognitionTimeout) {
        clearTimeout(faceRecognitionTimeout);
        faceRecognitionTimeout = null;
    }
    
    // Restart timeout for non-resident form (if no face detected after 5 seconds)
    if (recognitionLogic) {
        faceRecognitionTimeout = setTimeout(() => {
            if (!recognitionLogic.isLoggedToday('timeout-check')) {
                // No face recognized - show non-resident form
                console.log('No face recognized after 5 seconds. Showing non-resident form.');
                nonResidentForm.show();
            }
        }, FACE_RECOGNITION_TIMEOUT);
    }
}

/**
 * Initialize face recognition system
 */
async function initializeFaceRecognition() {
    try {
        // Update status to loading
        statusUpdater.updateLoading('Loading residents from database...');

        // Fetch residents from API
        const residents = await visitorAPI.fetchResidents();
        
        if (residents.length === 0) {
            statusUpdater.updateError(
                'NO RESIDENTS FOUND!',
                'No residents with photos found in database. Please add resident photos first.'
            );
            return;
        }

        // Format residents for face recognition
        // Support multiple photos per resident (3 angles)
        labeledDescriptors = residents.map(resident => ({
            name: resident.name,
            id: resident.id,
            img: resident.img, // First photo for display
            imgs: resident.imgs || [resident.img], // All photos (3 angles) for recognition
            resident_id: resident.resident_id,
            data: resident // Store full resident data for later use
        }));

        statusUpdater.updateLoading(`Preparing ${labeledDescriptors.length} face models...`);

        // Initialize face recognition
        faceRecognition = new FaceRecognition('./models');
        await faceRecognition.loadModels();

        // Load known faces
        await faceRecognition.initializeFaceMatcher(labeledDescriptors, RECOGNITION_THRESHOLD);

        // Initialize webcam (but don't start it yet - wait for user to click Start button)
        webcamHandler = new WebcamHandler('webcam-video');

        // Initialize recognition logic with updated callback
        recognitionLogic = new RecognitionLogic(
            webcamHandler,
            faceRecognition,
            labeledDescriptors,
            DETECTION_INTERVAL
        );
        
        // Set callback that includes resident data
        recognitionLogic.setOnRecognizedCallback((id, name, personData) => {
            // Clear timeout if face is recognized
            if (faceRecognitionTimeout) {
                clearTimeout(faceRecognitionTimeout);
                faceRecognitionTimeout = null;
            }
            
            // Use personData if provided, otherwise find it
            let residentData = personData || labeledDescriptors.find(p => p.id === id);
            
            // Ensure we have the correct data structure
            // If personData has a nested 'data' property, use that, otherwise use personData directly
            if (residentData && residentData.data) {
                residentData = {
                    ...residentData.data,
                    id: residentData.id,
                    name: residentData.name
                };
            }
            
            handleRecognizedFace(id, name, residentData);
        });

        // Update status to ready (camera will be started manually via button)
        statusUpdater.updateReady();
    } catch (error) {
        console.error("Error initializing face recognition:", error);
        let errorTitle = 'MODEL LOAD FAILED!';
        let errorMessage = 'Check "models" folder path and network status.';
        
        if (error.message.includes('No valid face descriptors')) {
            errorTitle = 'NO KNOWN FACES!';
            errorMessage = 'Please add known faces to the list.';
        } else if (error.message.includes('CAMERA')) {
            errorTitle = 'CAMERA ERROR';
            errorMessage = 'Camera access denied. Check permissions.';
        }
        
        statusUpdater.updateError(errorTitle, errorMessage);
    }
}

/**
 * Start camera and begin recognition
 */
async function startCamera() {
    const startBtn = document.getElementById('start-camera-btn');
    const stopBtn = document.getElementById('stop-camera-btn');
    
    if (!webcamHandler) {
        console.error('Webcam handler not initialized');
        return;
    }

    try {
        // Disable start button while starting
        if (startBtn) {
            startBtn.disabled = true;
            startBtn.textContent = 'Starting...';
        }

        // Start webcam
        await webcamHandler.start();

        // Initialize recognition logic if not already started
        if (recognitionLogic && !recognitionLogic.isRunning()) {
            recognitionLogic.start();
        }

        // Set timeout for non-resident form (Scenario 3)
        // If no face is recognized after 5 seconds, show non-resident form
        faceRecognitionTimeout = setTimeout(() => {
            if (recognitionLogic && !recognitionLogic.isLoggedToday('timeout-check')) {
                // No face recognized - show non-resident form
                console.log('No face recognized after 5 seconds. Showing non-resident form.');
                nonResidentForm.show();
            }
        }, FACE_RECOGNITION_TIMEOUT);

        // Update button visibility
        if (startBtn) {
            startBtn.classList.add('hidden');
        }
        if (stopBtn) {
            stopBtn.classList.remove('hidden');
        }

        // Update status
        if (statusUpdater) {
            statusUpdater.updateReady();
        }
    } catch (error) {
        console.error("Error starting camera:", error);
        
        // Re-enable start button on error
        if (startBtn) {
            startBtn.disabled = false;
            startBtn.innerHTML = `
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Start Camera
            `;
        }

        // Update status with error
        if (statusUpdater) {
            let errorTitle = 'CAMERA ERROR';
            let errorMessage = 'Camera access denied. Please check permissions and try again.';
            
            if (error.name === 'NotAllowedError' || error.name === 'PermissionDeniedError') {
                errorMessage = 'Camera permission denied. Please allow camera access and click Start again.';
            } else if (error.name === 'NotFoundError' || error.name === 'DevicesNotFoundError') {
                errorMessage = 'No camera found. Please connect a camera and try again.';
            }
            
            statusUpdater.updateError(errorTitle, errorMessage);
        }
    }
}

/**
 * Stop camera and pause recognition
 */
function stopCamera() {
    const startBtn = document.getElementById('start-camera-btn');
    const stopBtn = document.getElementById('stop-camera-btn');

    if (!webcamHandler) {
        console.error('Webcam handler not initialized');
        return;
    }

    // Stop webcam
    webcamHandler.stop();

    // Stop recognition logic
    if (recognitionLogic && recognitionLogic.isRunning()) {
        recognitionLogic.stop();
    }

    // Clear timeout if exists
    if (faceRecognitionTimeout) {
        clearTimeout(faceRecognitionTimeout);
        faceRecognitionTimeout = null;
    }

    // Update button visibility
    if (startBtn) {
        startBtn.classList.remove('hidden');
        startBtn.disabled = false;
        startBtn.innerHTML = `
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Start Camera
        `;
    }
    if (stopBtn) {
        stopBtn.classList.add('hidden');
    }

    // Update status
    if (statusUpdater) {
        statusUpdater.updateReady();
    }
}

/**
 * Initialize all modules
 */
function init() {
    // Initialize status updater
    statusUpdater = new StatusUpdater();
    statusUpdater.clearInitialLogItem();
    
    // Initialize activity logger
    activityLogger = new ActivityLogger();

    // Initialize visitor API
    visitorAPI = new VisitorAPI();

    // Initialize booking modal with tryAgain callback
    bookingModal = new BookingModal(tryAgain);

    // Initialize non-resident form with tryAgain callback
    nonResidentForm = new NonResidentForm(visitorAPI, tryAgain);

    // Initialize sidebar toggle
    initSidebar();
    
    // Initialize shared clock for consistent date display
    initSharedClock();

    // Set up camera control buttons
    const startBtn = document.getElementById('start-camera-btn');
    const stopBtn = document.getElementById('stop-camera-btn');

    if (startBtn) {
        startBtn.addEventListener('click', startCamera);
    }

    if (stopBtn) {
        stopBtn.addEventListener('click', stopCamera);
    }

    // Start face recognition system (but don't start camera automatically)
    initializeFaceRecognition();
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    // DOM is already ready
    init();
}
