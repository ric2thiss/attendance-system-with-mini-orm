const express = require('express')

const router = express.Router()

const API_KEY = 12345

const students = [
    {
        id: 1,
        name : "Ric Charles",
        course: "IT"
    },
    {
        id: 2,
        name: "Trixxie",
        course: "HUMSERV"
    }
]

router.route('/')
    .get((req, res, next) => {
        if(parseInt(req.headers["api_key"]) !== API_KEY) {
            return next(new Error("Access denied"))
        }

        return res.status(200).json({apikey : API_KEY ,students})
    })

module.exports = router