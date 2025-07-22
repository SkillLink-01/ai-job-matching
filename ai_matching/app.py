from flask import Flask, request, jsonify
from flask_cors import CORS
from pymongo import MongoClient

app = Flask(__name__)
CORS(app)

client = MongoClient('mongodb://localhost:27017/')
db = client['job_matching_system']
jobseekers_collection = db['Jobseekers']
jobs_collection = db['Jobs']

def extract_skills_from_resume(resume_text):
    keywords = ["python", "flask", "mongodb", "javascript", "html", "css", "rest", "api", "communication"]
    text = resume_text.lower()
    found = [skill for skill in keywords if skill in text]
    return list(set(found))

def compute_match_score(resume_skills, job):
    job_skills = [s.lower() for s in job.get('required_skills') or []]
    overlap = set(resume_skills).intersection(set(job_skills))
    score = len(overlap) / max(len(job_skills), 1)
    return round(score, 2)

@app.route('/recommend', methods=['POST'])
def recommend():
    try:
        resume_file = request.files.get('resume')
        jobseeker_id = request.form.get('jobseeker_id')

        if not resume_file or not jobseeker_id:
            return jsonify({'success': False, 'message': 'Missing resume or jobseeker_id'}), 400

        resume_text = resume_file.read().decode('utf-8')
        resume_skills = extract_skills_from_resume(resume_text)

        jobs = list(jobs_collection.find())
        matches = []

        for job in jobs:
            match_score = compute_match_score(resume_skills, job)
            if match_score > 0:  # ✅ Filter out 0-score matches
                matches.append({
                    "job_title": job.get('title'),
                    "company_name": job.get('company'),
                    "match_score": match_score
                })

        matches = sorted(matches, key=lambda x: x['match_score'], reverse=True)

        return jsonify({'success': True, 'matches': matches})

    except Exception as e:
        return jsonify({'success': False, 'message': str(e)}), 500

if __name__ == '__main__':
    app.run(debug=True)
