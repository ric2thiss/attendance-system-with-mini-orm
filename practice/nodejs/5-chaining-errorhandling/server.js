const express = require('express')
const app = express()

const PORT = 3000;


app.use(express.json())

const studentsRoute = require('./router/students')

app.use('/students', studentsRoute)

app.use((err, req, res, next) => {
    console.error("Error caught:", err.message);
    
    if(err.message === "Access denied") return res.status(403).json({error : err.message})
        
    res.status(500).json({ error: "Server error", details: err.message });

});

app.listen(PORT, ()=>{
    console.log(`Server running on http://localhost:${PORT}`)
})