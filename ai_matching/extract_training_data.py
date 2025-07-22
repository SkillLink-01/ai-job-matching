from pymongo import MongoClient
import csv

# Connect to MongoDB
client = MongoClient("mongodb://localhost:27017/")
db = client['job_matching_system']

# Collections
jobseekers_col = db['Jobseekers']
jobs_col = db['Jobs']
applications_col = db['applications']

# Build caches for quick lookup
jobseekers_cache = {str(js['_id']): js for js in jobseekers_col.find()}
jobs_cache = {str(job['_id']): job for job in jobs_col.find()}

with open('training_data.csv', 'w', newline='', encoding='utf-8') as csvfile:
    fieldnames = [
        'jobseeker_id', 'job_id',
        'resume_text', 'job_description',
        'skills_jobseeker', 'skills_job',
        'years_experience', 'min_experience',
        'label'
    ]
    writer = csv.DictWriter(csvfile, fieldnames=fieldnames)
    writer.writeheader()

    for app in applications_col.find():
        jobseeker_id = str(app.get('jobseeker_id'))
        job_id = str(app.get('job_id'))
        status = app.get('status', '').lower()

        # Get jobseeker and job details
        seeker = jobseekers_cache.get(jobseeker_id)
        job = jobs_cache.get(job_id)

        if not seeker or not job:
            continue  # skip if missing data

        # Prepare features
        resume_text = seeker.get('resume_text', '') or ''
        job_description = job.get('description', '') or ''

        skills_jobseeker = seeker.get('skills', [])
        skills_job = job.get('required_skills', [])

        # Calculate years of experience from experience array
        experience_list = seeker.get('experience', [])
        total_exp_years = 0
        for exp in experience_list:
            start_year = exp.get('start_year') or exp.get('start_date', '')[:4]
            end_year = exp.get('end_year') or exp.get('end_date', '')[:4]

            try:
                start = int(start_year)
                end = int(end_year) if end_year else 2025
                if end >= start:
                    total_exp_years += (end - start)
            except:
                pass  # ignore malformed dates

        min_experience = job.get('min_experience', 0)

        # Label: 1 if hired or shortlisted, else 0
        label = 1 if status in ['hired', 'shortlisted'] else 0

        writer.writerow({
            'jobseeker_id': jobseeker_id,
            'job_id': job_id,
            'resume_text': resume_text,
            'job_description': job_description,
            'skills_jobseeker': ','.join(skills_jobseeker),
            'skills_job': ','.join(skills_job),
            'years_experience': total_exp_years,
            'min_experience': min_experience,
            'label': label
        })

print("Training data extracted to training_data.csv")
