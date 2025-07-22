import pandas as pd
import joblib
from scipy.sparse import hstack

# Load vectorizer and model once
vectorizer = joblib.load('tfidf_vectorizer.pkl')
model = joblib.load('job_matcher.pkl')

def preprocess(new_data):
    combined_text = new_data['job_description'] + " " + new_data['candidate_resume']
    X_text = vectorizer.transform(combined_text)

    X_num = new_data[['skill_match_count', 'min_experience_met']].values
    X = hstack([X_text, X_num])

    return X

def predict_match(resume_text, jobs):
    rows = []
    for job in jobs:
        if not isinstance(job, dict):
            continue  # Skip if job is not a dictionary

        job_description = " ".join(job.get("required_skills", [])) + " " + job.get("title", "")
        rows.append({
            "job_description": job_description,
            "candidate_resume": resume_text,
            "skill_match_count": len(job.get("required_skills", [])),
            "min_experience_met": 1 if int(job.get("min_experience", 0)) <= 2 else 0
        })

    if not rows:
        return []

    new_data = pd.DataFrame(rows)
    X = preprocess(new_data)
    predictions = model.predict(X)

    return predictions.tolist()
