import pandas as pd
from sklearn.model_selection import train_test_split
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.ensemble import RandomForestClassifier
from sklearn.metrics import classification_report, accuracy_score
import joblib

# 1. Load your CSV data
df = pd.read_csv('training_data.csv')

# 2. Prepare features and labels
vectorizer = TfidfVectorizer(max_features=100)

# Combine job description and resume text into one feature set (you can customize this)
combined_text = df['job_description'] + ' ' + df['candidate_resume']
text_features = vectorizer.fit_transform(combined_text)

# Numeric features
numeric_features = df[['skill_match_count', 'min_experience_met']].values

# Combine all features (using scipy sparse hstack)
from scipy.sparse import hstack
X = hstack([text_features, numeric_features])

y = df['label']

# 3. Split data
X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

# 4. Train model
model = RandomForestClassifier(n_estimators=100, random_state=42)
model.fit(X_train, y_train)

# 5. Evaluate model
y_pred = model.predict(X_test)
print("Accuracy:", accuracy_score(y_test, y_pred))
print(classification_report(y_test, y_pred))

# 6. Save model and vectorizer
joblib.dump(model, 'job_matcher.pkl')
joblib.dump(vectorizer, 'tfidf_vectorizer.pkl')

print("Model and vectorizer saved.")
