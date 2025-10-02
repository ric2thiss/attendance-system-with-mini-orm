// const WebSocket = require("ws");
// const wss = new WebSocket.Server({port: 8080});

// wss.on("connection", (ws)=>{
//     console.log("New client connected");
    

//     ws.on("close", ()=> console.log("Client Disconnected"))
// })

// console.log("✅ WebSocket server started on ws://localhost:8080");


// const WebSocket = require('ws');

// // Try built-in fetch if available, otherwise use node-fetch
// let fetchFn;
// try {
//     fetchFn = fetch; // Node 18+
// } catch (e) {
//     fetchFn = require('node-fetch'); // Node <18
// }

// const wss = new WebSocket.Server({ port: 8080 });

// const getAttendance = async () => {
//     const attendancesToday = await fetchFn("http://localhost/attendance-system/api/services.php?resource=attendances");
//     return await attendancesToday.json();
// };

// wss.on("connection", async (ws) => {
//     console.log("📡 New Client Connected");

//     try {
//         const attendanceData = await getAttendance();
//         ws.send(JSON.stringify({ type: "attendance", data: attendanceData }));
//     } catch (err) {
//         console.error("⚠️ Failed to fetch attendance:", err);
//         ws.send(JSON.stringify({ type: "error", message: "Could not fetch attendance data" }));
//     }

//     ws.on("message", (message) => {
//         console.log("💬 Client says:", message.toString());
//     });

//     ws.on("close", () => {
//         console.log("❌ Client Disconnected");
//     });
// });


const WebSocket = require('ws');
const wss = new WebSocket.Server({port : 8080})

let messages = []

wss.on("connection", (ws)=>{
    console.log("New Client Connected");
    // ws.send("I live in server")

    ws.on("message", (message)=>{
        messages.push(message.toString());
        // ws.send(JSON.stringify(messages))

        wss.clients.forEach(client => {
            client.send(JSON.stringify(messages))
        })

        console.log(wss.clients)
    })


     ws.on("close", () => {
        console.log("❌ Client Disconnected");
    });
})

