const express = require('express')
const app = express()

const PORT = 3000;

app.use(express.json());
app.use(express.urlencoded({extended: true}))
app.use(express.static('public'))



const studentsRoute = require('./routes/students')

// Routes

app.use('/students', studentsRoute)

// Error handler
app.use((err, req, res, next) => {
    if(err.message === "Access Denied") {
        return res.status(403).json({error : err.message})
    }

    if(err.message === "Not found") {
        return res.status(404).json({error: err.message})
    }
})

app.listen(PORT, ()=>{
    console.log(`Server running on PORT http://localhost:${PORT}`)
})