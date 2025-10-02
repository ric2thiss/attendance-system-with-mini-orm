const fs = require('fs');

fs.writeFileSync("messages.txt", "This is my first node.js file!");

const content = fs.readFileSync("messages.txt","utf-8");

console.log("File Content", content)