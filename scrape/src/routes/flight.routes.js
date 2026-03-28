const express = require("express");
const { getFlights, healthCheck } = require("../controllers/flight.controller");

const router = express.Router();

router.get("/", healthCheck);
router.get("/api/flights", getFlights);

module.exports = router;
