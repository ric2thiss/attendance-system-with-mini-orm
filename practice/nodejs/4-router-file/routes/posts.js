const express = require('express');
const router = express.Router();

const POSTS = [
  { id: 1, title: "Intro to JS", content: "Learn basics", category: "Programming" },
  { id: 2, title: "Healthy Foods", content: "Tasty and healthy", category: "Lifestyle" }
];

// GET all posts
router.get('/', (req, res) => {
  res.json(POSTS);
});

// GET a single post by ID
router.get('/:id', (req, res) => {
  const post = POSTS.find(p => p.id === Number(req.params.id));
  if (!post) return res.status(404).json({ error: "Post not found" });
  res.json(post);
});

// POST a new post
router.post('/', (req, res) => {
  const { title, content, category } = req.body;
  const newPost = { id: POSTS.length + 1, title, content, category };
  POSTS.push(newPost);
  res.status(201).json(newPost);
});

module.exports = router;
