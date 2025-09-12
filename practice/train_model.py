# train_model.py
import pandas as pd
from sklearn.linear_model import LogisticRegression
import joblib

# Load attendance dataset
df = pd.read_csv("attendance.csv")

# Convert to features/labels (simplified)
X = df[["employee_id"]]  # in real case, use history patterns
y = df["status"].apply(lambda x: 1 if x=="Absent" else 0)

model = LogisticRegression()
model.fit(X, y)

# Save trained model
joblib.dump(model, "attendance_model.pkl")
