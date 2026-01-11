/**
 * Attendance Handler Module
 * Handles attendance updates, duplicate prevention, and state management
 */
import { Toast } from './toast.js';
import { TableRenderer } from './tableRenderer.js';

export class AttendanceHandler {
    constructor(apiUrl) {
        this.apiUrl = apiUrl;
        this.toast = new Toast();
        this.tableRenderer = new TableRenderer();
        
        // Track processed attendance IDs
        this.lastProcessedAttendanceId = null;
        this.processedAttendanceIds = new Set();
        this.isInitialLoad = true;
        this.forceUpdateNext = false;
    }

    /**
     * Helper function to get window text
     */
    getWindowText(window) {
        return TableRenderer.getWindowText(window);
    }

    /**
     * Update current attendee display section
     */
    updateCurrentAttendeeDisplay(attendance, employee, resident) {
        try {
            const timeInEl = document.getElementById("time_in");
            const timeOutEl = document.getElementById("time_out");
            const roleEl = document.getElementById("role");
            const employeePhotoEl = document.getElementById("employee_photo");
            const empIdEl = document.getElementById("employee_id");
            const nameEl = document.getElementById("name");
            const windowEl = document.getElementById("window");

            if (!attendance) return;

            // Format time
            const date = new Date(attendance.created_at?.replace(" ", "T") || attendance.timestamp?.replace(" ", "T"));
            const formattedTime = date.toLocaleTimeString([], {
                hour: "2-digit",
                minute: "2-digit",
                second: "2-digit",
            });

            // Display Employee Info
            if (roleEl) roleEl.textContent = employee?.position_name || employee?.position || "N/A";
            if (empIdEl) empIdEl.textContent = attendance.employee_id || "Unknown";

            const firstName = resident?.first_name || "";
            const lastName = resident?.last_name || "";
            if (nameEl) nameEl.textContent = `${firstName} ${lastName}`.trim() || "Unnamed";
            if (employeePhotoEl) employeePhotoEl.src = `../${resident?.photo_path}` || "./logo.png";

            // Display Attendance Window
            const windowText = this.getWindowText(attendance.window);
            if (windowEl) windowEl.textContent = windowText;

            // Display Time In / Out
            if (windowText.includes("In")) {
                if (timeInEl) timeInEl.textContent = formattedTime;
                if (timeOutEl) timeOutEl.textContent = "-";
            } else if (windowText.includes("Out")) {
                if (timeOutEl) timeOutEl.textContent = formattedTime;
                if (timeInEl) timeInEl.textContent = "-";
            } else {
                if (timeInEl) timeInEl.textContent = "-";
                if (timeOutEl) timeOutEl.textContent = "-";
            }
        } catch (error) {
            console.error("❌ Error updating current attendee display:", error);
        }
    }

    /**
     * Handle attendance updates (called from both WebSocket and polling)
     */
    handleUpdate(data, forceUpdate = false) {
        try {
            const { lastAttendee, lastAttendeeEmployee, lastAttendeeResident } = data;
            
            if (!lastAttendee) {
                return;
            }

            // Extract attendance ID - handle both object and array access
            const currentAttendanceId = lastAttendee.id || lastAttendee['id'];
            if (!currentAttendanceId) {
                return;
            }

            // Update current attendee display (always show latest)
            this.updateCurrentAttendeeDisplay(lastAttendee, lastAttendeeEmployee, lastAttendeeResident);

            // On initial load, just mark as processed and don't show toast or update table
            if (this.isInitialLoad) {
                this.lastProcessedAttendanceId = currentAttendanceId;
                this.processedAttendanceIds.add(currentAttendanceId);
                this.isInitialLoad = false;
                return;
            }

            // Check if we should force update (from new_attendance message)
            const shouldForceUpdate = forceUpdate || this.forceUpdateNext;
            if (this.forceUpdateNext) {
                this.forceUpdateNext = false; // Reset flag
            }

            // Check if this is a duplicate (already processed) - skip unless forced
            if (!shouldForceUpdate && this.processedAttendanceIds.has(currentAttendanceId)) {
                return; // Skip duplicate
            }

            // Only update table and show toast if this is a NEW attendance OR if forced
            if (shouldForceUpdate || currentAttendanceId !== this.lastProcessedAttendanceId) {
                // Only add to table if it's a confirmed attendance (has id and employee_id)
                if (currentAttendanceId && lastAttendee.employee_id) {
                    // Remove from processed set if it was there (for force update)
                    if (shouldForceUpdate && this.processedAttendanceIds.has(currentAttendanceId)) {
                        this.processedAttendanceIds.delete(currentAttendanceId);
                    }
                    
                    // --- Update Attendance Table ---
                    this.tableRenderer.updateTable(lastAttendee, lastAttendeeResident);

                    // --- Show Success Notification ---
                    const firstName = lastAttendeeResident?.first_name || "";
                    const lastName = lastAttendeeResident?.last_name || "";
                    const employeeName = `${firstName} ${lastName}`.trim() || lastAttendee.employee_id || "Employee";
                    const windowText = this.getWindowText(lastAttendee.window);
                    this.toast.show("Attendance Logged Successfully", `${employeeName} - ${windowText}`, 'success');

                    // Mark as processed
                    this.processedAttendanceIds.add(currentAttendanceId);
                }
            }

            // Update the last processed attendance ID
            this.lastProcessedAttendanceId = currentAttendanceId;

        } catch (error) {
            console.error("❌ Error handling attendance update:", error);
        }
    }

    /**
     * Show error toast notification
     */
    showError(title, message) {
        this.toast.show(title, message, 'error');
    }

    /**
     * Fetch latest attendance data from API (only for initial load)
     */
    async fetchLatestAttendance() {
        try {
            // Add cache-busting parameter to ensure fresh data
            const url = this.apiUrl + (this.apiUrl.includes('?') ? '&' : '?') + '_t=' + Date.now();
            const response = await fetch(url, {
                method: 'GET',
                cache: 'no-cache',
                headers: {
                    'Cache-Control': 'no-cache',
                    'Pragma': 'no-cache'
                }
            });
            if (!response.ok) {
                console.error("❌ Failed to fetch attendance data:", response.status);
                return null;
            }
            const data = await response.json();
            return data;
        } catch (error) {
            console.error("❌ Error fetching attendance data:", error);
            return null;
        }
    }

    /**
     * Initialize with current attendance data on page load (one-time fetch)
     */
    async initialize() {
        try {
            const data = await this.fetchLatestAttendance();
            if (data && data.lastAttendee) {
                this.handleUpdate(data);
            }
        } catch (error) {
            console.error("❌ Error in initial fetch:", error);
        }
    }
}
