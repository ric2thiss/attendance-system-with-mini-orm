const WebSocket = require('ws');
const PORT = 8080;

const wss = new WebSocket.Server({ port: PORT });

console.log(`✅ WebSocket Server running on ws://localhost:${PORT}`);

// Broadcast helper
async function broadcast(message) {
  try {
    // Fetch data only once
    const res = await fetch('http://localhost/attendance-system/api/services.php?resource=attendances');
    const data = await res.json();

    // Loop through connected clients
    for (const client of wss.clients) {
      if (client.readyState === WebSocket.OPEN) {
        // Send attendance data
        client.send(JSON.stringify(data));
      }
    }
  } catch (err) {
    console.error('❌ Error during broadcast:', err);
  }
}

wss.on('connection', (ws) => {
  console.log('🟢 New client connected');
  
  // Notify everyone (including the new client)
  broadcast(`👥 Connected clients: ${wss.clients.size}`);

  ws.on('message', (msg) => {
    console.log('📨 Message from client:', msg.toString());
  });

  ws.on('close', () => {
    console.log('🔴 Client disconnected');
    broadcast(`👥 Connected clients: ${wss.clients.size}`);
  });
});
