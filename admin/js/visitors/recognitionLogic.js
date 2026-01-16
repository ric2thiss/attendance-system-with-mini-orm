/**
 * Recognition Logic Module
 * Handles face detection and matching logic
 */
export class RecognitionLogic {
    constructor(webcamHandler, faceRecognition, labeledDescriptors, detectionInterval = 1000) {
        this.webcamHandler = webcamHandler;
        this.faceRecognition = faceRecognition;
        this.labeledDescriptors = labeledDescriptors;
        this.detectionInterval = detectionInterval;
        this.detectionIntervalId = null;
        this.loggedToday = new Set(); // To prevent logging the same person multiple times per session
        this.onRecognizedCallback = null;
    }

    /**
     * Set callback for when a face is recognized
     */
    setOnRecognizedCallback(callback) {
        this.onRecognizedCallback = callback;
    }

    /**
     * Start face detection loop
     */
    start() {
        // Access global faceapi variable from CDN
        const faceapi = window.faceapi || globalThis.faceapi;
        if (!faceapi) {
            console.error('Face-API.js library not loaded');
            return;
        }

        const video = this.webcamHandler.getVideo();
        const overlay = this.webcamHandler.getOverlay();
        
        if (!video || !overlay) {
            console.error('Video or overlay element not found');
            return;
        }

        const ctx = overlay.getContext('2d');

        video.addEventListener('play', () => {
            const displaySize = { width: video.clientWidth, height: video.clientHeight };
            faceapi.matchDimensions(overlay, displaySize);

            this.detectionIntervalId = setInterval(async () => {
                const faceMatcher = this.faceRecognition.getFaceMatcher();
                if (!faceMatcher) return;

                const detections = await faceapi
                    .detectAllFaces(video, new faceapi.TinyFaceDetectorOptions({
                        inputSize: 416,
                        scoreThreshold: 0.6
                    }))
                    .withFaceLandmarks()
                    .withFaceDescriptors();

                // Clear canvas
                ctx.clearRect(0, 0, overlay.width, overlay.height);

                // Resize detections to match video dimensions
                const resizedDetections = faceapi.resizeResults(detections, displaySize);

                if (resizedDetections.length && faceMatcher) {
                    const results = resizedDetections.map(d =>
                        faceMatcher.findBestMatch(d.descriptor)
                    );

                    results.forEach((result, i) => {
                        const box = resizedDetections[i].detection.box;
                        const label = result.label;
                        const distance = result.distance.toFixed(2);

                        // Draw bounding box
                        ctx.strokeStyle = label === "unknown" ? "red" : "lime";
                        ctx.lineWidth = 3;
                        ctx.strokeRect(box.x, box.y, box.width, box.height);

                        // Draw label
                        ctx.fillStyle = label === "unknown" ? "red" : "lime";
                        ctx.font = "16px Inter, sans-serif";
                        ctx.fillText(`${label} (${distance})`, box.x, box.y - 8);

                        if (label !== "unknown") {
                            const person = this.labeledDescriptors.find(p => p.name === label);
                            if (person && this.onRecognizedCallback) {
                                // Pass person data to callback
                                this.onRecognizedCallback(person.id, person.name, person);
                            }
                        }
                    });
                }
            }, this.detectionInterval);
        });
    }

    /**
     * Stop face detection loop
     */
    stop() {
        if (this.detectionIntervalId) {
            clearInterval(this.detectionIntervalId);
            this.detectionIntervalId = null;
        }
    }

    /**
     * Check if recognition is currently running
     */
    isRunning() {
        return this.detectionIntervalId !== null;
    }

    /**
     * Check if person is already logged today
     */
    isLoggedToday(id) {
        return this.loggedToday.has(id);
    }

    /**
     * Mark person as logged
     */
    markAsLogged(id, duration = 300000) { // 5 minutes default
        this.loggedToday.add(id);
        setTimeout(() => {
            this.loggedToday.delete(id);
            console.log(`Person ${id} is ready for a new log entry.`);
        }, duration);
    }

    /**
     * Reset logged state - clear all logged entries
     */
    resetLoggedState() {
        this.loggedToday.clear();
        console.log('Recognition state reset - ready for new face capture.');
    }
}
