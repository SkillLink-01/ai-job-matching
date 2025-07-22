import re
import string
import spacy

nlp = spacy.load("en_core_web_sm")
stop_words = nlp.Defaults.stop_words

def clean_text(text):
    text = text.lower()
    text = re.sub(r"\d+", "", text)  # remove numbers
    text = text.translate(str.maketrans("", "", string.punctuation))
    text = " ".join([word for word in text.split() if word not in stop_words])
    return text
