const express = require('express')

const router = express.Router();

let users = [
  { id: 1, name: "Alice", age: 21 },
  { id: 2, name: "John Doe", age: 22 },
  { id: 3, name: "John Doe", age: 24 },
];


router.route('/')
  .get((req, res) => {
      res.json(users)
  })
  .post((req, res) => {
    const newUser = {id: users.length+1, name: req.body.name}
    users.push(newUser)
    res.status(201).json(users)
  })

router.route('/:id')
  .get((req, res) => {
    const id = parseInt(req.params.id)
    const user = users.find(u => u.id === id)
    if(!user) res.status(404).json({message: "User not found"})
    res.status(302).json(user)
  })
  .delete((req, res) => {
    const id = parseInt(req.params.id);
    users = users.filter(u => u.id !== id);
    res.status(200).json({ users, message: `User deleted id: ${id}` });
  })
  .put((req, res) => {
    const id = parseInt(req.params.id)
    const index = users.findIndex(u => u.id === id)
    
    if(index === -1){
      res.status(404).json({message: `User not found`})
    }

    users[index] = {id, name: req.body.name}

    res.status(200).json({message: "Updated Successfully", data: users[index]})
  })
  .patch((req, res) => {
    const id = parseInt(req.params.id)
    const user = users.find(u => u.id === id)

    if(!user) return res.status(404).json({message: "User not found"})
    
    if(!req.body.name) return res.status(400).json({message: "Request body cannot be empty"})

    user.name = req.body.name

    res.status(200).json({message:"Successfully updated", data: user})
  })

// router.get('/:id', (req, res) => {
//     const id =  parseInt(req.params.id)
    
//     const user = users.find(u => u.id === id)

//     res.status(302).json(user)
// })

module.exports = router

