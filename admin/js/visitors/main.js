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
import { initSidebar } from '../shared/sidebar.js';

// Known faces (You need to adjust these details for your residents/visitors)
const labeledDescriptors = [
    { name: "Rich", id: 1, img: "assets/rich.jpg" },
    { name: "Ric", id: 2, img: "assets/ric.png" },
    { name: "JP", id: 3, img: "assets/pretche.jpg" },
    // Add more people here: { name: "Jane Doe", id: 3, img: "assets/jane.jpg" }
];

const RECOGNITION_THRESHOLD = 0.4; // Lower value = stricter match
const DETECTION_INTERVAL = 1000; // Check every 1000ms (1 second)

// Initialize modules
let faceRecognition;
let webcamHandler;
let recognitionLogic;
let activityLogger;
let visitorAPI;
let statusUpdater;

/**
 * Handle recognized face
 */
async function handleRecognizedFace(id, name) {
    // Check if person is already logged
    if (recognitionLogic.isLoggedToday(id)) {
        return; // Skip if already logged
    }

    // Mark as logged
    recognitionLogic.markAsLogged(id, 300000); // 5 minutes

    // Update recognition status
    const personDetails = labeledDescriptors.find(p => p.name === name);
    statusUpdater.updateRecognized(name, personDetails);

    // Add to activity log
    activityLogger.addLogEntry(name);

    // Send to backend
    try {
        await visitorAPI.logAttendance(id, name, "Check-in");
    } catch (error) {
        console.error("Error logging attendance:", error);
    }
}

/**
 * Initialize face recognition system
 */
async function initializeFaceRecognition() {
    try {
        // Update status to loading
        statusUpdater.updateLoading(`Preparing ${labeledDescriptors.length} face models.`);

        // Initialize face recognition
        faceRecognition = new FaceRecognition('./models');
        await faceRecognition.loadModels();

        // Load known faces
        await faceRecognition.initializeFaceMatcher(labeledDescriptors, RECOGNITION_THRESHOLD);

        // Initialize webcam
        webcamHandler = new WebcamHandler('webcam-video');
        await webcamHandler.start();

        // Initialize recognition logic
        recognitionLogic = new RecognitionLogic(
            webcamHandler,
            faceRecognition,
            labeledDescriptors,
            DETECTION_INTERVAL
        );
        recognitionLogic.setOnRecognizedCallback(handleRecognizedFace);
        recognitionLogic.start();

        // Update status to ready
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

    // Initialize sidebar toggle
    initSidebar();

    // Start face recognition system
    initializeFaceRecognition();
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    // DOM is already ready
    init();
}
