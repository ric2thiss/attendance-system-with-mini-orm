const express = require("express");
const app = express();
const PORT = 3000;

// Middleware to parse JSON body

app.use(express.json());

// Data

const employees = [
  {id: 1, name: 'Alice'},
  {id: 2, name: 'Bob'}
];

//Root route
app.get('/', (req, res) => {
    res.send("Welcome to my Attendance System API")
});

// GET all employees
app.get('/employees', (req, res) => {
  res.json(employees);
});

// Get one employee by id

app.get('/employee/:id', (req, res) => {
    const id = parseInt(req.params.id);
    const employee = employees.find(e=> e.id === id);

    if(!employee) {
        return res.status(404).json({message: "Employee not found!"});
    }

    res.json(employee);
})


app.post('/employees', (req, res) => {
    const {name} = req.body;

    if(!name) {
        return res.status(400).json({message: "Name is required!"});
    }

    const newEmployee = {
        id: employees.length + 1, 
        name
    };

    employees.push(newEmployee);
    res.status(201).json(employees);
})

app.delete('/employee/:id', (req, res) => {
    const id = parseInt(req.params.id);
    const employee = employees.findIndex(e => e.id === id);

    if(employee === -1) {
        return res.status(404).json({message: "User is not existing"});
    }

    const deletedEmployee = employees.splice(employee, 1)

    res.status(201).json({message: "Successfully deleted", deletedEmployee})
})

app.put('/employee/:id', (req, res) => {
    const id = parseInt(req.params.id);
    const user = employees.find(u => u.id === id);

    if(!user) {
        return res.status(400).json({message : "Employee not found"});
    }

    const {name} = req.body;

    if(!name) return res.status(400).json({message: "Name is required"});

    user.name = name;

    res.json({
        message: "Employee updated successfully",
        updated: user
    })

    
})


// Start server
app.listen(PORT, ()=>{
    console.log(`Server running at http://localhost:${PORT}`);
})