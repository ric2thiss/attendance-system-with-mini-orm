const http = require('http');

const server = http.createServer((req, res) =>{
    
    if(req.url === "/") {
        res.writeHead(200, {"Content-Type": "text/html"})
        res.end("<h1>Welcome to my homepage! Go to about page <a href='/about'>click here</a></h1>")
    }else if(req.url === "/about"){
        res.writeHead(200, {"Content-Type": "text/html"})
        res.end("<h1>This is my about page. I am Ric your javascript developer</h1>")
    }else {
        res.writeHead(404, {"Content-Type": "text/plain"})
        res.end("Page not found")
    }

})

server.listen(3000, ()=> {
    console.log(`Server running at http://localhost:3000`);
})