const express = require('express');

const app = express()

const PORT = 3000

app.use(express.json())


const postsRouter = require('./routes/posts');
const usersRouter = require('./routes/users')

app.get('/', (req, res) => {
    res.send('Home Page')
})

app.use('/posts', postsRouter);

app.use('/users', usersRouter)

app.listen(PORT, () => {
  console.log(`Server running on http://localhost:${PORT}`);
});