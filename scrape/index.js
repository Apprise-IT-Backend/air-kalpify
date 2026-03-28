const app = require("./src/app");

const PORT = 3000;

app.listen(PORT, () => {
  console.log(`Server running on http://localhost:${PORT}`);
  console.log(
    `Try: http://localhost:${PORT}/api/flights?from=DAC&to=CXB&date=2026-03-27`
  );
});
