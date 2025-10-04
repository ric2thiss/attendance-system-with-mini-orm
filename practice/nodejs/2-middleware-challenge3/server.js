const express = require('express');
const app = express();
const PORT = 3000;

app.use(express.static("public"));
app.use(express.json());

const logger = (req, res, next) => {
  console.log(`${req.method} ${req.url} ${new Date().toISOString()}`);

  if (req.url === "/about") {
    const hour = new Date().getHours();
    // Allow only between 8 AM and 5 PM
    if (hour < 8 || hour > 17) {
      return res.status(403).send("Service hours are closed. Try again between 8 AM - 5 PM.");
    }
  }

  next(); // move on
};

app.use(logger);

app.get("/", (req, res) => {
  res.send("Homepage");
});

app.get("/about", (req, res) => {
  res.send("About Page - Open hours only!");
});

// Catch-all for 404
app.use((req, res) => {
  res.status(404).send("Page not found");
});

// Start server
app.listen(PORT, () => {
  console.log(`Server running on http://localhost:${PORT}`);
});
