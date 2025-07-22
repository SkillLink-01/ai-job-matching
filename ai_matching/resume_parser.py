import re
import spacy

nlp = spacy.load("en_core_web_sm")
COMMON_SKILLS = ["Python", "JavaScript", "MongoDB", "Flask", "React", "HTML", "CSS", "Networking", "SQL", "Linux"]

def extract_skills(text):
    doc = nlp(text)
    skills_found = set()
    for token in doc:
        if token.text.lower() in [s.lower() for s in COMMON_SKILLS]:
            skills_found.add(token.text)
    return list(skills_found)

def extract_experience_years(text):
    # Simple rule-based experience extraction
    patterns = [
        r'(\d+)\+?\s*years',
        r'(\d+)\s*yrs',
        r'(\d+)\s*years of experience',
    ]
    for pattern in patterns:
        match = re.search(pattern, text, re.IGNORECASE)
        if match:
            return int(match.group(1))
    return 0
