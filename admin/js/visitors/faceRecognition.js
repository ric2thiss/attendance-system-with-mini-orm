/**
 * Face Recognition Module
 * Handles Face-API.js initialization and model loading
 */
export class FaceRecognition {
    constructor(modelsPath = './models') {
        this.modelsPath = modelsPath;
        this.faceMatcher = null;
        this.isModelsLoaded = false;
    }

    /**
     * Load Face-API models
     * Note: faceapi is a global variable from the CDN script
     */
    async loadModels() {
        try {
            // Access global faceapi variable from CDN
            const faceapi = window.faceapi || globalThis.faceapi;
            if (!faceapi) {
                throw new Error('Face-API.js library not loaded. Please ensure the CDN script is included.');
            }

            await faceapi.nets.tinyFaceDetector.loadFromUri(this.modelsPath);
            await faceapi.nets.faceLandmark68Net.loadFromUri(this.modelsPath);
            await faceapi.nets.ssdMobilenetv1.loadFromUri(this.modelsPath);
            await faceapi.nets.faceRecognitionNet.loadFromUri(this.modelsPath);
            this.isModelsLoaded = true;
            return true;
        } catch (error) {
            console.error("Error loading face-api models:", error);
            this.isModelsLoaded = false;
            return false;
        }
    }

    /**
     * Load labeled face descriptors from images
     */
    async loadLabeledImages(labeledDescriptors) {
        // Access global faceapi variable from CDN
        const faceapi = window.faceapi || globalThis.faceapi;
        if (!faceapi) {
            throw new Error('Face-API.js library not loaded');
        }

        return Promise.all(
            labeledDescriptors.map(async (person) => {
                try {
                    const img = await faceapi.fetchImage(person.img);
                    
                    // Try both TinyFaceDetector and SsdMobilenetv1 for robust detection on static image
                    let detection = await faceapi
                        .detectSingleFace(img, new faceapi.TinyFaceDetectorOptions({ inputSize: 320, scoreThreshold: 0.5 }))
                        .withFaceLandmarks()
                        .withFaceDescriptor();

                    if (!detection) {
                        detection = await faceapi
                            .detectSingleFace(img, new faceapi.SsdMobilenetv1Options({ minConfidence: 0.5 }))
                            .withFaceLandmarks()
                            .withFaceDescriptor();
                    }

                    if (!detection) {
                        console.warn(`⚠️ No face detected in ${person.img}. Skipping.`);
                        return null;
                    }

                    return new faceapi.LabeledFaceDescriptors(person.name, [detection.descriptor]);
                } catch (error) {
                    console.error(`Error loading image or detecting face for ${person.name}:`, error);
                    return null;
                }
            })
        );
    }

    /**
     * Initialize face matcher
     */
    async initializeFaceMatcher(labeledDescriptors, recognitionThreshold = 0.4) {
        // Access global faceapi variable from CDN
        const faceapi = window.faceapi || globalThis.faceapi;
        if (!faceapi) {
            throw new Error('Face-API.js library not loaded');
        }

        if (!this.isModelsLoaded) {
            const loaded = await this.loadModels();
            if (!loaded) {
                throw new Error('Failed to load Face-API models');
            }
        }

        const labeledFaceDescriptors = await this.loadLabeledImages(labeledDescriptors);
        const validDescriptors = labeledFaceDescriptors.filter(d => d !== null);

        if (validDescriptors.length === 0) {
            throw new Error('No valid face descriptors found');
        }

        this.faceMatcher = new faceapi.FaceMatcher(validDescriptors, recognitionThreshold);
        return this.faceMatcher;
    }

    /**
     * Get face matcher instance
     */
    getFaceMatcher() {
        return this.faceMatcher;
    }

    /**
     * Check if models are loaded
     */
    areModelsLoaded() {
        return this.isModelsLoaded;
    }
}
