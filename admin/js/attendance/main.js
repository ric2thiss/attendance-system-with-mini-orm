/**
 * Attendance Page Main Entry Point
 * Initializes all modules for the attendance page
 */
import { WebSocketManager } from './websocket.js';
import { AttendanceHandler } from './attendanceHandler.js';
import { ConnectionStatus } from './connectionStatus.js';
import { Clock } from './clock.js';
import { Weather } from './weather.js';
import { initSidebar } from '../shared/sidebar.js';

// Get configuration from data attributes on the script tag or meta tags
// These values are injected by PHP in attendance.php
function getConfig() {
    // Try to get from current script tag's data attributes
    const currentScript = document.currentScript;
    let websocketUrl = currentScript?.dataset?.websocketUrl || '';
    let attendanceApiUrl = currentScript?.dataset?.attendanceApiUrl || '';
    
    // Fallback: try to get from meta tags if not in script data attributes
    if (!websocketUrl) {
        const metaWs = document.querySelector('meta[name="websocket-url"]');
        if (metaWs) websocketUrl = metaWs.content;
    }

    if (!attendanceApiUrl) {
        const metaApi = document.querySelector('meta[name="attendance-api-url"]');
        if (metaApi) attendanceApiUrl = metaApi.content;
    }
    
    return { websocketUrl, attendanceApiUrl };
}

const config = getConfig();

// Initialize modules
let clock;
let weather;
let connectionStatus;
let attendanceHandler;
let websocketManager;

/**
 * Initialize all modules
 */
function init() {
    // Initialize connection status
    connectionStatus = new ConnectionStatus();
    connectionStatus.update(false, 'Connecting...');

    // Initialize attendance handler
    attendanceHandler = new AttendanceHandler(config.attendanceApiUrl);

    // Initialize WebSocket manager
    if (config.websocketUrl) {
        websocketManager = new WebSocketManager(
            config.websocketUrl,
            connectionStatus,
            attendanceHandler,
            10 // max reconnect attempts
        );
        websocketManager.init();
    }

    // Initialize clock
    clock = new Clock();
    clock.start();

    // Initialize weather
    weather = new Weather(8.95, 125.53); // Default: Butuan City, Philippines
    weather.init();

    // Initialize sidebar toggle
    initSidebar();

    // Initialize attendance data on page load
    attendanceHandler.initialize();

    // Handle page visibility changes (when tab loses/gains focus)
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden && websocketManager) {
            websocketManager.reconnectIfNeeded();
        }
    });
    
    // Handle focus events (when window loses/gains focus)
    window.addEventListener('focus', () => {
        if (websocketManager) {
            websocketManager.reconnectIfNeeded();
        }
    });

    // Cleanup WebSocket connection when page is unloaded
    window.addEventListener('beforeunload', () => {
        if (websocketManager) {
            websocketManager.close();
        }
        if (clock) {
            clock.stop();
        }
        if (weather) {
            weather.stop();
        }
    });
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    // DOM is already ready
    init();
}
