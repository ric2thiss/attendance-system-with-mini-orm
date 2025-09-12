const http = require('http');

const data = [
  {id: 1, name: 'Alice'},
  {id: 2, name: 'Bob'}
]

const server = http.createServer((req, res) => {
    if(req.url === "/employee") {
        res.writeHead(200, {'Content-Type':'application/json'})
        res.end(JSON.stringify(data))
    }else{
        res.writeHead(400, {"Content-Type":"application/json"});
        res.end(JSON.stringify({message: "not found"}))
    }
    
});

server.listen(3000, () => {
    console.log('Server running at http://localhost:3000/');
});
