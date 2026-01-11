/**
 * WebSocket Server Configuration
 * This file contains all configuration settings for the WebSocket server
 */

module.exports = {
    // WebSocket Server Configuration
    websocket: {
        port: 8081,
        host: 'localhost'
    },

    // API Configuration
    api: {
        baseUrl: 'http://localhost/attendance-system',
        endpoints: {
            // OLD ENDPOINTS (backward compatible):
            // attendances: '/api/services.php?resource=attendances',
            // templates: '/api/services.php?resource=templates',
            // attendanceWindows: '/api/services.php?resource=attendance-windows',
            // employees: '/api/services.php?resource=employees'
            // NEW ENDPOINTS:
            attendances: '/api/attendance/index.php',
            templates: '/api/templates/index.php',
            attendanceWindows: '/api/attendance/windows.php',
            employees: '/api/employees/index.php'
        }
    },

    // Server Settings
    server: {
        // Timeout settings (in milliseconds)
        heartbeatInterval: 30000, // 30 seconds
        reconnectDelay: 5000 // 5 seconds
    },

    // Logging
    logging: {
        enabled: true,
        level: 'info' // 'debug', 'info', 'warn', 'error'
    }
};
