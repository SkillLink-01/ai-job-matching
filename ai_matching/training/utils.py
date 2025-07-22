import joblib

def combine_features(job, seeker):
    job_text = f"{job.get('title', '')} {job.get('description', '')} {' '.join(job.get('required_skills', []))}"
    seeker_text = f"{seeker.get('name', '')} {' '.join(seeker.get('skills', []))}"
    return f"{job_text} {seeker_text}"

def preprocess_text(texts):
    vectorizer = joblib.load('model/vectorizer.pkl')
    return vectorizer.transform(texts)
