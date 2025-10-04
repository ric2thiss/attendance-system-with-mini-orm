const http = require('http');
const fs = require('fs')
const PORT = 3001

const server = http.createServer((req, res) => {
    if(req.url === "/"){
        fs.readFile("index.html", (err, data)=> {
            if(err){
                res.writeHead(500, {"Content-Type":"text/plain"});
                res.end("Something went wrong on the server")
            }

            res.writeHead(200, {"Content-Type":"text/html"});
            res.end(data)
        })
    }else if(req.url === "/about") {
        fs.readFile("about.html", (err, data)=> {
            if(err){
                res.writeHead(500, {"Content-Type":"text/plain"});
                res.end("Something went wrong on the server")
            }

            res.writeHead(200, {"Content-Type":"text/html"});
            res.end(data)
        })
    }else{
        fs.readFile("404.html", (err, data)=> {
            if(err){
                res.writeHead(500, {"Content-Type":"text/plain"});
                res.end("Something went wrong on the server")
            }

            res.writeHead(404, {"Content-Type":"text/html"});
            res.end(data)
        })
    }
})

server.listen(PORT, ()=> {
    console.log(`Server running on http://localhost:${PORT}`)
})