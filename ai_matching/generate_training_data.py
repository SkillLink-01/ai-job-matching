import csv
from pymongo import MongoClient

client = MongoClient("mongodb://localhost:27017/")
db = client["job_matching_system"]

jobseekers = list(db["Jobseekers"].find())
jobs = list(db["Jobs"].find())
applications = list(db["applications"].find())

applied_pairs = set()
for app in applications:
    seeker_id = app.get('jobseeker_id') or app.get('user_id')  # handle both possible keys
    job_id = app.get('job_id')
    if seeker_id is not None and job_id is not None:
        applied_pairs.add((str(seeker_id), str(job_id)))
    else:
        print(f"Skipping application missing jobseeker_id or job_id: {app}")

def count_skill_matches(seeker_skills, job_skills):
    if not seeker_skills or not job_skills:
        return 0
    return len(set(map(str.lower, seeker_skills)) & set(map(str.lower, job_skills)))

with open('training_data.csv', 'w', newline='', encoding='utf-8') as csvfile:
    fieldnames = ['jobseeker_id', 'job_id', 'skill_match_count', 'min_experience_met', 'label']
    writer = csv.DictWriter(csvfile, fieldnames=fieldnames)
    writer.writeheader()

    for seeker in jobseekers:
        seeker_id = str(seeker['_id'])
        seeker_skills = seeker.get('skills', [])
        seeker_experience_years = 0

        for exp in seeker.get('experience', []):
            start_year = int(exp.get('start_date', '1970')[:4])
            end_year = int(exp.get('end_date', '2025')[:4])
            seeker_experience_years += (end_year - start_year)

        for job in jobs:
            job_id = str(job['_id'])
            job_skills = job.get('required_skills') or []
            min_exp = job.get('min_experience', 0)

            skill_matches = count_skill_matches(seeker_skills, job_skills)
            min_exp_met = 1 if seeker_experience_years >= min_exp else 0

            label = 1 if (seeker_id, job_id) in applied_pairs else 0

            writer.writerow({
                'jobseeker_id': seeker_id,
                'job_id': job_id,
                'skill_match_count': skill_matches,
                'min_experience_met': min_exp_met,
                'label': label
            })

print("training_data.csv generated successfully.")
