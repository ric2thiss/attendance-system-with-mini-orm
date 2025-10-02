const fs = require('fs');

fs.writeFileSync("bootcamp.txt", "Node.js is awesome!");

const content = fs.readFileSync("bootcamp.txt", "utf-8");

console.log(`The file says: ${content}`)