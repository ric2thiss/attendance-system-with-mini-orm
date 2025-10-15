<?php

include_once '../shared/components/Sidebar.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resident Check-in | Face ID</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f7f9fc; /* Light background for the main content area */
        }
        /* Custom dark blue for the sidebar */
        .sidebar-bg {
            background-color: #172B4D; /* A deep navy blue */
        }
        /* Active link background color */
        .active-link {
            background-color: rgba(255, 255, 255, 0.1);
            border-left: 4px solid #007bff; /* Light blue border highlight */
        }
        /* Video container for visual cue */
        #video-feed-container {
            min-height: 400px;
            /* background-color: #333; <-- Removed as video will show through */
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.25rem;
            border: 4px solid #007bff; /* Highlight border */
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            position: relative; /* Needed for canvas overlay */
        }
        /* Style for the actual video and canvas */
        #webcam-video, #video-overlay {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: absolute;
            top: 0;
            left: 0;
            z-index: 10;
        }
        #video-overlay {
            z-index: 20; /* Canvas on top of video */
        }
        /* Recognition status container */
        #recognition-status {
            min-height: 250px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        /* Log list style for better look */
        #logList li {
            padding: 6px 0;
            border-bottom: 1px dashed #e5e7eb;
        }
        #logList li:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>

    <div class="flex min-h-screen">

        <?=Sidebar("Visitors", null)?>

        <main class="flex-1 md:ml-64 p-6 transition-all duration-300">

            <header class="mb-6">
                <div class="flex justify-between items-center mb-1">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-800">Visitors Logging</h1>
                        <p class="text-gray-500 text-sm">Use face recognition to quickly logging.</p>
                    </div>
                </div>
            </header>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <div class="lg:col-span-2 bg-white rounded-xl shadow-lg border border-gray-100 p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Live Camera Feed</h2>
                    
                    <div id="video-feed-container" class="w-full rounded-xl overflow-hidden relative">
                        <video id="webcam-video" class="w-full h-full object-cover hidden" autoplay playsinline></video>
                        
                        <canvas id="video-overlay" class="w-full h-full hidden"></canvas>

                        <div id="video-placeholder" class="absolute inset-0 flex flex-col items-center justify-center p-4 bg-black bg-opacity-30 rounded-xl">
                            <svg class="w-20 h-20 text-blue-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.107 4h3.784a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            <p class="text-white font-medium">Camera is loading...</p>
                            <p class="text-sm text-gray-200 mt-1" id="camera-status">Status: Waiting for permission.</p>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-1 flex flex-col space-y-6">

                    <div id="recognition-status" class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 text-center flex-grow">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">Recognition Status</h2>
                        
                        <svg id="status-icon" class="w-16 h-16 text-yellow-500 mx-auto animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        
                        <p class="mt-4 text-2xl font-bold text-gray-800" id="status-title">Loading Models...</p>
                        <p class="text-gray-500" id="status-message">Please wait for the system to initialize.</p>
                        
                        <div id="recognized-user" class="mt-6 p-4 border-t border-gray-200 hidden">
                            <img id="user-photo" src="https://placehold.co/80x80/007bff/white?text=R" alt="User Photo" class="w-20 h-20 rounded-full mx-auto mb-2">
                            <p class="text-lg font-semibold text-green-600" id="user-action">CHECK-IN SUCCESSFUL!</p>
                            <p class="text-sm text-gray-700" id="user-details">Resident: Jane Doe (Unit 302)</p>
                            <p class="text-xs text-gray-500" id="user-time">Time: 10:15 AM</p>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-3">Recent Activity Log</h2>
                        <ul id="logList" class="space-y-2 text-sm text-gray-700">
                            <li class="text-center text-gray-400">No recent activity.</li>
                        </ul>
                    </div>

                </div>
            </div>

        </main>
    </div>

    <script>
        // --- UI Elements ---
        const sidebar = document.getElementById('sidebar');
        const toggleButton = document.getElementById('sidebar-toggle');
        const mainContent = document.querySelector('main');
        const video = document.getElementById('webcam-video');
        const overlay = document.getElementById('video-overlay');
        const videoPlaceholder = document.getElementById('video-placeholder');
        const cameraStatus = document.getElementById('camera-status');
        const statusIcon = document.getElementById('status-icon');
        const statusTitle = document.getElementById('status-title');
        const statusMessage = document.getElementById('status-message');
        const recognizedUserDiv = document.getElementById('recognized-user');
        const userAction = document.getElementById('user-action');
        const userDetails = document.getElementById('user-details');
        const userTime = document.getElementById('user-time');
        const logList = document.getElementById('logList');
        const ctx = overlay.getContext('2d');
        let initialLogItem = logList.querySelector('li');
        if (initialLogItem) initialLogItem.remove(); 

        // --- FACE-API.JS VARIABLES ---
        // Known faces (You need to adjust these details for your residents/visitors)
        const labeledDescriptors = [
            { name: "Rich", id: 1, img: "assets/rich.jpg" },
            { name: "Ric",  id: 2, img: "assets/ric.png" },
            {name: "JP", id:3, img: 'assets/pretche.jpg'},
            // {name: "Pretche", id:3, img: 'assets/pretche.jpg'},
            // {name: "Reynolds", id:3, img: 'assets/reynolds.jpg'},
            // {name: "Keneth", id:3, img: 'assets/keneth.jpg'},
            // {name: "JP", id:3, img: 'assets/jp.jpg'},
            // {name: "Neil", id:3, img: 'assets/neil.jpg'},
            // Add more people here: { name: "Jane Doe", id: 3, img: "assets/jane.jpg" }
        ];

        let faceMatcher;
        const loggedToday = new Set(); // To prevent logging the same person multiple times per session
        const RECOGNITION_THRESHOLD = 0.4; // Lower value = stricter match
        const DETECTION_INTERVAL = 1000; // Check every 1000ms (1 second)

        // --- LOGIC FUNCTIONS ---

        async function loadLabeledImages() {
            statusTitle.textContent = 'Loading Known Faces...';
            statusMessage.textContent = `Preparing ${labeledDescriptors.length} face models.`;

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

        async function startFaceRecognition() {
            statusTitle.textContent = 'Awaiting Face Scan...';
            statusMessage.textContent = 'Scan your face to Clock In or Clock Out.';
            
            // 1. Load models
            statusTitle.textContent = 'Loading AI Models...';
            try {
                await faceapi.nets.tinyFaceDetector.loadFromUri('./models');
                await faceapi.nets.faceLandmark68Net.loadFromUri('./models');
                 await faceapi.nets.ssdMobilenetv1.loadFromUri('models');
                await faceapi.nets.faceRecognitionNet.loadFromUri('./models');
            } catch (error) {
                statusTitle.textContent = 'MODEL LOAD FAILED!';
                statusMessage.textContent = 'Check "models" folder path and network status.';
                statusIcon.classList.remove('animate-pulse', 'text-yellow-500');
                statusIcon.classList.add('text-red-500');
                console.error("Error loading face-api models:", error);
                return;
            }


            // 2. Load known faces
            const labeledFaceDescriptors = await loadLabeledImages();
            const validDescriptors = labeledFaceDescriptors.filter(d => d !== null);

            if (validDescriptors.length === 0) {
                 statusTitle.textContent = 'NO KNOWN FACES!';
                 statusMessage.textContent = 'Please add known faces to the list.';
                 statusIcon.classList.remove('animate-pulse', 'text-yellow-500');
                 statusIcon.classList.add('text-red-500');
                 return;
            }

            faceMatcher = new faceapi.FaceMatcher(validDescriptors, RECOGNITION_THRESHOLD);

            // 3. Start Webcam
            cameraStatus.textContent = "Status: Requesting camera permission...";
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ video: true });
                video.srcObject = stream;
                video.classList.remove('hidden');
                overlay.classList.remove('hidden');
                videoPlaceholder.classList.add('hidden'); // Hide the placeholder
                cameraStatus.textContent = "Status: Live feed active.";
                statusTitle.textContent = 'READY TO SCAN';
                statusIcon.classList.remove('text-yellow-500');
                statusIcon.classList.add('text-green-500');

            } catch (err) {
                console.error("Error accessing the camera:", err);
                cameraStatus.textContent = "Status: CAMERA ACCESS DENIED. Check permissions.";
                statusTitle.textContent = 'CAMERA ERROR';
                statusIcon.classList.remove('animate-pulse', 'text-yellow-500');
                statusIcon.classList.add('text-red-500');
            }
        }

        video.addEventListener('play', () => {
            const displaySize = { width: video.clientWidth, height: video.clientHeight };
            faceapi.matchDimensions(overlay, displaySize);

            setInterval(async () => {
                const detections = await faceapi
                    .detectAllFaces(video, new faceapi.TinyFaceDetectorOptions({
                        inputSize: 416, // Optimized for performance
                        scoreThreshold: 0.6 // Good balance
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
                        const distance = result.distance.toFixed(2); // How close the match is

                        // Draw bounding box
                        ctx.strokeStyle = label === "unknown" ? "red" : "lime";
                        ctx.lineWidth = 3;
                        ctx.strokeRect(box.x, box.y, box.width, box.height);

                        // Draw label
                        ctx.fillStyle = label === "unknown" ? "red" : "lime";
                        ctx.font = "16px Inter, sans-serif";
                        ctx.fillText(`${label} (${distance})`, box.x, box.y - 8);

                        if (label !== "unknown") {
                            const person = labeledDescriptors.find(p => p.name === label);
                            if (person) {
                                logAttendance(person.id, person.name);
                            }
                        }
                    });
                }
            }, DETECTION_INTERVAL);
        });

        function updateRecognitionStatus(name) {
            // Update the detailed recognition box on successful scan
            recognizedUserDiv.classList.remove('hidden');
            statusIcon.classList.add('hidden');
            statusTitle.textContent = 'MATCH FOUND!';
            statusMessage.textContent = 'Logging entry...';

            const now = new Date();
            const timeString = now.toLocaleTimeString();

            // Find the full details, assuming you'll fetch more from a database later
            const personDetails = labeledDescriptors.find(p => p.name === name);

            // Update user details
            document.getElementById('user-photo').src = personDetails ? personDetails.img : 'https://placehold.co/80x80/007bff/white?text=R';
            userAction.textContent = 'CHECK-IN LOGGED!';
            userDetails.textContent = `Resident: ${name}`;
            userTime.textContent = `Time: ${timeString}`;
        }

        function addLogEntry(name) {
             // Add to log list
            const li = document.createElement("li");
            const now = new Date();
            const timeString = now.toLocaleTimeString();

            // Simple check-in/out logic: Assume check-in for now
            li.innerHTML = `<span class="font-medium">${timeString}:</span> ${name} (Check-in) <span class="text-green-500 float-right">✅</span>`;
            
            // Prepend new log item to the top of the list
            logList.prepend(li); 
            // Keep the list tidy (optional: limit the number of entries)
            if (logList.children.length > 5) {
                logList.removeChild(logList.lastChild);
            }
        }


        function logAttendance(id, name) {
            // Check if the person has already been logged in this session (e.g., in the last 5 minutes)
            if (!loggedToday.has(id)) {
                loggedToday.add(id);

                updateRecognitionStatus(name);
                addLogEntry(name);

                // --- Send data to PHP backend (logbook.php) ---
                fetch("http://localhost/attendance-system/resident/logbook.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({
                        id,
                        name,
                        action: "Check-in" // Assuming default action is Check-in
                    })
                })
                .then(res => res.text())
                .then(data => console.log("✅ Logged entry:", data))
                .catch(err => console.error("❌ Error saving to logbook.php:", err));

                // Remove from 'loggedToday' Set after a short period (e.g., 5 minutes = 300,000ms)
                setTimeout(() => {
                    loggedToday.delete(id);
                    console.log(`${name} is ready for a new log entry.`);
                }, 300000); // 5 minutes
            }
        }


        // --- Startup ---
        startFaceRecognition();

        // --- Mobile Sidebar Toggle Logic ---
        toggleButton.addEventListener('click', () => {
            if (sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.remove('-translate-x-full');
                sidebar.classList.add('translate-x-0');
                mainContent.classList.add('opacity-50', 'pointer-events-none');
            } else {
                sidebar.classList.remove('translate-x-0');
                sidebar.classList.add('-translate-x-full');
                mainContent.classList.remove('opacity-50', 'pointer-events-none');
            }
        });

        // Close sidebar if main content is clicked on mobile
        mainContent.addEventListener('click', () => {
            if (window.innerWidth < 768 && sidebar.classList.contains('translate-x-0')) {
                 sidebar.classList.remove('translate-x-0');
                sidebar.classList.add('-translate-x-full');
                mainContent.classList.remove('opacity-50', 'pointer-events-none');
            }
        });

    </script>
</body>
</html>