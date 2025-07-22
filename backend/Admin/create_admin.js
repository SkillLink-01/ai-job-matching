const bcrypt = require('bcrypt');
const { MongoClient } = require('mongodb');

// ✅ MongoDB connection URI
const uri = 'mongodb://localhost:27017';
const client = new MongoClient(uri);

async function run() {
  try {
    await client.connect();
    const db = client.db('job_matching_system');
    const users = db.collection('admin'); // ✅ make sure your collection is named 'admin'

    const plainPassword = 'SL00814010GeMeSaTiTs5'; // ✅ this is the password you'll log in with
    const hashedPassword = await bcrypt.hash(plainPassword, 10); // ✅ securely hash it

    const admin = {
      name: 'Admin',
      email: 'admin@skilllink.com',
      password: hashedPassword, // ✅ use the hashed password
      role: 'admin'
    };

    // Optional: delete any existing admin with the same email
    await users.deleteMany({ email: admin.email });

    await users.insertOne(admin);
    console.log('✅ Admin inserted successfully!');
  } catch (error) {
    console.error('❌ Error:', error);
  } finally {
    await client.close();
  }
}

run();
