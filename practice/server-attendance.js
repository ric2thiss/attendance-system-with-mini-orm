const express = require("express");
const app = express();
const PORT = 3000;

app.use(express.json());

const attendanceDB = [{id : 1, name: "Alice", status: "In", time: "8:30"}];

//Routes
app.get("/attendance", (req, res)=>{
    res.json(attendanceDB);
})

// POST new attendance log
app.post("/attendance", (req, res) => {
  const { id, name, status, time } = req.body;

  if (!id || !name || !status || !time) {
    return res.status(400).json({ message: "All fields are required" });
  }

  // Find all logs for this employee today
  const today = new Date().toISOString().split("T")[0]; // e.g. "2025-09-08"
  const userLogsToday = attendanceDB.filter(
    (log) => log.id === parseInt(id) && log.date === today
  );

  // Rule 1: if first log today, it must be IN
  if (userLogsToday.length === 0 && status !== "IN") {
    return res
      .status(400)
      .json({ message: "First log of the day must be TIME IN" });
  }

  // Rule 2: prevent duplicate IN or OUT
  if (userLogsToday.length > 0) {
    const lastLog = userLogsToday[userLogsToday.length - 1];

    if (lastLog.status === status) {
      return res
        .status(400)
        .json({ message: `Already logged ${status}, must alternate` });
    }
  }

  // Save new log
  const newLog = {
    log_id: attendanceDB.length + 1,
    id: parseInt(id),
    name,
    status,
    time,
    date: today,
  };

  attendanceDB.push(newLog);

  res.status(201).json({
    message: `Attendance ${status} recorded`,
    // log: newLog,
  });
});


app.listen(PORT, ()=>{
    console.log(`Server running at http://localhost:${PORT}`);
})