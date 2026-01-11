/**
 * API Module
 * Handles API calls for visitor logging
 */
export class VisitorAPI {
    constructor(baseUrl = 'http://localhost/attendance-system/resident/logbook.php') {
        this.baseUrl = baseUrl;
    }

    /**
     * Log visitor attendance
     */
    async logAttendance(id, name, action = 'Check-in') {
        try {
            const response = await fetch(this.baseUrl, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    id,
                    name,
                    action
                })
            });

            const data = await response.text();
            console.log("✅ Logged entry:", data);
            return data;
        } catch (err) {
            console.error("❌ Error saving to logbook.php:", err);
            throw err;
        }
    }
}
