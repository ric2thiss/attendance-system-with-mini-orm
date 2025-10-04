
const express = require('express')

const app = express();

const path = require('path')

const PORT = 3000;
app.use(express.json());
app.use(express.urlencoded({extended : true}))
app.use(express.static("public"))
// app.use((req, res, next)=>{
//     console.log(`${req.method} ${req.url} ${new Date().toLocaleString()}`)
//     next()
// })

app.get("/", (req, res)=>{
    res.sendFile(path.join(__dirname, "index.html"))
})

app.get("/about", (req, res)=>{
    res.sendFile(path.join(__dirname, req.url+".html"))
})

const employees = [
  { id: 1, name: "Alice" },
  { id: 2, name: "John Doe" },
  { id: 3, name: "John Doe" },
];

app.get('/employee/:id', (req, res)=>{
    const req_id = parseInt(req.params.id)
    const user = employees.find(user => user.id === req_id)
    res.json(user)
})

app.get("/employees", (req, res)=>{
    res.json(employees)
})



app.get("/search", (req, res)=>{
    const keyword = req.query.keyword.toLowerCase();

    const result = employees.filter(e=>{
        console.log(`id: ${e.id} name: ${e.name}`)
        return e.name.toLowerCase().includes(keyword)
    })

    res.json(result)
})

//POSTS

const POSTS = [
  {
    "id": 1,
    "title": "Introduction to JavaScript",
    "content": "Learn the basics of JavaScript and how it powers the web.",
    "category": "Programming"
  },
  {
    "id": 2,
    "title": "Top 10 Healthy Foods",
    "content": "A list of foods that are both tasty and healthy.",
    "category": "programming"
  },
  {
    "id": 3,
    "title": "Travel Guide to Japan",
    "content": "Best places to visit and things to do in Japan.",
    "category": "tour"
  }
]

app.get("/posts", (req, res)=>{
    res.json(POSTS)
})

app.get("/posts/:id", (req, res) => {
    const {id} = req.params
    const post = POSTS.find(p => p.id === Number(id))
    res.json(post)
})

// NOT CLEAN VERSION
// app.get("/posts/:id/:category", (req, res) => {
//     const {id, category} = req.params

//     if(!category){
//         const post = POSTS.find(p => p.id === Number(id))
//         res.json(post)
//     }

//     const posts = POSTS.filter(p => p.category.toLowerCase() === category.toLowerCase())
//     if(posts){
//         res.json(posts.find(p => Number(p.id) === Number(id)))
//     }

// })

// CLEANER VERSION
app.get("/posts/:id/:category", (req, res) => {
    const { id, category } = req.params;

    // First filter by category
    const postsByCategory = POSTS.filter(
        p => p.category.toLowerCase() === category.toLowerCase()
    );

    // Then find the post by ID within that category
    const post = postsByCategory.find(p => p.id === Number(id));

    if (!post) {
        return res.status(404).json({ message: "Post not found in this category" });
    }

    res.json(post);
});



app.post("/posts", (req, res) => {
    const {id, title, content, category} = req.body
    if (!req.body) {
        return res.status(400).json({ error: "No body received" });
    }

    const getNewId = POSTS.length + 1

    const newPost = {id: getNewId, title, content, category}


    POSTS.push(newPost)
    
    res.status(201).json({
        message: "New Post Created",
        data: newPost
    })

})

app.delete("/posts/:id", (req, res) => {
    const id = Number(req.params.id)
    const index = POSTS.findIndex(e => e.id === id)

    POSTS.splice(index, 1);
    res.status(200).json(
        {
            message: `Post id:${id} deleted successfully`,
            data: POSTS
        })

})

// app.post("/posts", (req, res) => {
//   console.log("Headers:", req.headers["content-type"]);
//   console.log("Body:", req.body);

//   if (!req.body) {
//     return res.status(400).json({ error: "No body received" });
//   }

//   res.json({ message: "Post received", data: req.body });
// });







app.use((req, res)=>{
    res.status(404).sendFile(path.join(__dirname, "404.html"))
})

app.listen(PORT, ()=>{
    console.log(`Server running on http://localhost:${PORT}`)
})