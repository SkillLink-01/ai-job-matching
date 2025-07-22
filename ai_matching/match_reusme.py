# match_resume.py
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity
from training.preprocess import clean_text
from pymongo import MongoClient

def match_resume_to_jobs(resume_text, top_n=10):
    client = MongoClient("mongodb://localhost:27017/")
    db = client["job_matching_system"]
    jobs_collection = db["Jobs"]

    resume_clean = clean_text(resume_text)
    job_texts, job_ids, job_titles = [], [], []

    for job in jobs_collection.find():
        full_text = f"{job.get('title', '')} {job.get('description', '')} {' '.join(job.get('required_skills', []))}"
        job_texts.append(clean_text(full_text))
        job_ids.append(str(job['_id']))
        job_titles.append(job.get('title', 'Unknown'))

    vectorizer = TfidfVectorizer()
    tfidf_matrix = vectorizer.fit_transform([resume_clean] + job_texts)
    scores = cosine_similarity(tfidf_matrix[0], tfidf_matrix[1:]).flatten()

    ranked_indices = scores.argsort()[::-1][:top_n]
    return [
        {
            "job_id": job_ids[i],
            "job_title": job_titles[i],
            "match_score": round(float(scores[i]), 2)
        }
        for i in ranked_indices
    ]
