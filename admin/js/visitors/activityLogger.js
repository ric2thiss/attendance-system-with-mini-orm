/**
 * Activity Logger Module
 * Manages activity log UI display
 */
export class ActivityLogger {
    constructor(logListId = 'logList') {
        this.logList = document.getElementById(logListId);
    }

    /**
     * Add log entry
     */
    addLogEntry(name, action = 'Check-in') {
        if (!this.logList) return;

        const li = document.createElement("li");
        const now = new Date();
        const timeString = now.toLocaleTimeString();

        li.innerHTML = `<span class="font-medium">${timeString}:</span> ${name} (${action}) <span class="text-green-500 float-right">✅</span>`;
        
        // Prepend new log item to the top of the list
        this.logList.prepend(li);
        
        // Keep the list tidy (limit the number of entries)
        if (this.logList.children.length > 5) {
            this.logList.removeChild(this.logList.lastChild);
        }
    }

    /**
     * Clear all log entries
     */
    clear() {
        if (this.logList) {
            this.logList.innerHTML = '';
        }
    }
}
