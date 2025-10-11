const express = require('express')
const router = express.Router()

const API_KEY = "HelloWorld"


const students = [
  { id: 1, name: "Ric", course: "IT" },
  { id: 1, name: "Ric Charles", course: "IT" },
  { id: 2, name: "Trixxie", course: "HUMSERV" },
  { id: 3, name: "John", course: "IT" },
  { id: 4, name: "Marie", course: "CRIM" },
];


router.route('/')
    .get((req, res, next)=>{
        const apikey = req.headers['x-api-key']

        if(!apikey || apikey === "" || apikey == null || apikey !== API_KEY) {
            return next(new Error("Access Denied"))
        }
        
        res.status(200).json({message: "Success", students})
    })

router.route('/search')
  .get((req, res, next) => {
    const { course, name } = req.query;

    // ensure case-insensitive name search
    const baseOnName = name
      ? students.filter(s =>
          s.name.toLowerCase().includes(name.toLowerCase())
        )
      : [];

    // exact match for course
    const baseOnCourse = course
      ? students.filter(s => s.course === course)
      : [];

    // ✅ CASE 1: search by course only
    if (course && !name) {
      return res.status(200).json(baseOnCourse);
    }

    // ✅ CASE 2: search by name only
    if (name && !course) {
      return res.status(200).json(baseOnName);
    }

    // ✅ CASE 3: search by both name + course
    if (name && course) {
      const combined = students.filter(
        s =>
          s.course === course &&
          s.name.toLowerCase().includes(name.toLowerCase())
      );
      return res.status(200).json(combined);
    }

    // ❌ CASE 4: missing both
    return res.status(400).json({ message: "Bad request. Please provide 'name' or 'course'." });
  });




    module.exports = router