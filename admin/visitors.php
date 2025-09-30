<?php

include_once '../shared/components/Sidebar.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resident Check-in | Face ID</title>
    <!-- Load Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Use Inter font family and custom styles from the dashboard -->
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
            background-color: #333;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.25rem;
            border: 4px solid #007bff; /* Highlight border */
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        /* Recognition status container */
        #recognition-status {
            min-height: 250px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>

    <!-- Main Container -->
    <div class="flex min-h-screen">

        <?=Sidebar("Visitors", null, "./Login_logo1.png")?>

        <!-- 2. MAIN CONTENT AREA -->
        <main class="flex-1 md:ml-64 p-6 transition-all duration-300">

            <!-- Top Header Bar -->
            <header class="mb-6">
                <div class="flex justify-between items-center mb-1">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-800">Visitors Logging</h1>
                        <p class="text-gray-500 text-sm">Use face recognition to quickly logging.</p>
                    </div>
                </div>
            </header>

            <!-- FACE RECOGNITION INTERFACE SECTION -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- 1. CAMERA FEED CONTAINER (Takes 2/3 width on desktop) -->
                <div class="lg:col-span-2 bg-white rounded-xl shadow-lg border border-gray-100 p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Live Camera Feed</h2>
                    
                    <!-- Static Placeholder for Video -->
                    <div id="video-feed-container" class="w-full rounded-xl overflow-hidden relative">
                        <p>Camera is loading...</p>
                        <!-- In a real app, the <video> element would go here -->
                        <video id="webcam-video" class="w-full h-full  object-cover hidden" autoplay playsinline></video>
                        
                        <!-- Overlay instructions -->
                        <div class="absolute inset-0 flex flex-col items-center justify-center p-4 bg-black bg-opacity-30 rounded-xl">
                            <svg class="w-20 h-20 text-blue-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.107 4h3.784a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            <p class="text-white font-medium">Please center your face within the frame.</p>
                            <p class="text-sm text-gray-200 mt-1">Status: Ready for scanning...</p>
                        </div>
                    </div>
                </div>

                <!-- 2. RECOGNITION STATUS CONTAINER (Takes 1/3 width on desktop) -->
                <div class="lg:col-span-1 flex flex-col space-y-6">

                    <!-- Recognition Status Box -->
                    <div id="recognition-status" class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 text-center flex-grow">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">Recognition Status</h2>
                        
                        <!-- Static Recognition Icon (Pending) -->
                        <svg class="w-16 h-16 text-yellow-500 mx-auto animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        
                        <p class="mt-4 text-2xl font-bold text-gray-800" id="status-title">Awaiting Face Scan...</p>
                        <p class="text-gray-500" id="status-message">Scan your face to Clock In or Clock Out.</p>
                        
                        <!-- Static Placeholder of Recognized User -->
                        <div id="recognized-user" class="mt-6 p-4 border-t border-gray-200 hidden">
                            <img src="https://placehold.co/80x80/007bff/white?text=R" alt="User Photo" class="w-20 h-20 rounded-full mx-auto mb-2">
                            <p class="text-lg font-semibold text-green-600">CHECK-IN SUCCESSFUL!</p>
                            <p class="text-sm text-gray-700">Resident: Jane Doe (Unit 302)</p>
                            <p class="text-xs text-gray-500">Time: 10:15 AM</p>
                        </div>
                    </div>

                    <!-- Last Activity Log Placeholder -->
                    <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-3">Last Activity</h2>
                        <ul class="space-y-2 text-sm text-gray-700">
                            <li class="border-b pb-2">
                                <span class="font-medium">10:15 AM:</span> Jane Doe (Check-in)
                            </li>
                            <li class="border-b pb-2">
                                <span class="font-medium">Yesterday 18:00 PM:</span> John Smith (Check-out)
                            </li>
                            <li>
                                <span class="font-medium">Yesterday 08:30 AM:</span> Alex Chen (Check-in)
                            </li>
                        </ul>
                    </div>

                </div>
            </div>

        </main>
    </div>

    <!-- JavaScript for Sidebar Toggle and (Static) Webcam Initialization -->
    <script>
        // --- Mobile Sidebar Toggle Logic (copied from other pages) ---
        const sidebar = document.getElementById('sidebar');
        const toggleButton = document.getElementById('sidebar-toggle');
        const mainContent = document.querySelector('main');

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

        // --- Static Example of Status Change (To be replaced by real face recognition logic) ---
        // This is static demonstration of how the status container would update.
        setTimeout(() => {
            document.getElementById('status-title').textContent = 'SCANNING...';
            document.getElementById('status-message').textContent = 'Please hold still, face detection in progress.';
        }, 3000);

        setTimeout(() => {
            document.getElementById('status-title').textContent = 'MATCH FOUND!';
            document.getElementById('status-message').textContent = 'Processing check-in...';
            document.getElementById('recognized-user').classList.remove('hidden');
            document.getElementById('recognition-status').classList.remove('text-center');
            document.querySelector('.animate-pulse').classList.add('hidden'); // Hide spinning icon
        }, 6000);

        // NOTE: Actual webcam access and face recognition logic requires a backend server and machine learning libraries,
        // and cannot run directly in this environment. This provides the necessary static UI layout.
    </script>
</body>
</html>
