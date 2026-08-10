const express = require('express');
const path = require('path');

const app = express();
const PORT = process.env.PORT || 3000;

// Sirve todo lo que esté dentro de /public como archivos estáticos
app.use(express.static(path.join(__dirname, 'public')));

// Ruta principal explícita (por si luego se agregan más rutas/vistas)
app.get('/', (req, res) => {
  res.sendFile(path.join(__dirname, 'public', 'index.html'));
});

app.listen(PORT, () => {
  console.log(`Servidor corriendo en http://localhost:${PORT}`);
});
