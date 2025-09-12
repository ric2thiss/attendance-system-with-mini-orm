# app.py
from flask import Flask, request, jsonify
import joblib

app = Flask(__name__)
model = joblib.load("attendance_model.pkl")

@app.route("/predict", methods=["POST"])
def predict():
    data = request.json
    employee_id = data["employee_id"]
    prediction = model.predict([[employee_id]])[0]
    return jsonify({"risk_of_absence": int(prediction)})

app.run(port=5000)
