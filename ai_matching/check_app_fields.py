from pymongo import MongoClient

client = MongoClient("mongodb://localhost:27017/")
db = client["job_matching_system"]

application = db["applications"].find_one()
print(application)
