const express = require("express");
const flightRoutes = require("./routes/flight.routes");

const app = express();

app.use(flightRoutes);

module.exports = app;
