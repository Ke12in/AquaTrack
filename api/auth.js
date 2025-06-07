import { MongoClient } from 'mongodb';
import bcrypt from 'bcryptjs';

const uri = process.env.MONGODB_URI;
const client = new MongoClient(uri);

export default async function handler(req, res) {
    if (req.method === 'POST') {
        try {
            await client.connect();
            const database = client.db('aquatrack');
            const users = database.collection('users');

            const { action, email, password, fullname } = req.body;

            if (action === 'signup') {
                // Check if user exists
                const existingUser = await users.findOne({ email });
                if (existingUser) {
                    return res.status(400).json({ error: 'User already exists' });
                }

                // Hash password
                const hashedPassword = await bcrypt.hash(password, 10);

                // Create user
                const result = await users.insertOne({
                    fullname,
                    email,
                    password: hashedPassword,
                    height: null,
                    weight: null,
                    activityLevel: 'moderate',
                    soundNotifications: true,
                    browserNotifications: true,
                    dailyReminders: true,
                    theme: 'light',
                    profileImage: null,
                    createdAt: new Date()
                });

                return res.status(201).json({ 
                    message: 'User created successfully',
                    userId: result.insertedId
                });
            }

            if (action === 'login') {
                // Find user
                const user = await users.findOne({ email });
                if (!user) {
                    return res.status(400).json({ error: 'User not found' });
                }

                // Check password
                const validPassword = await bcrypt.compare(password, user.password);
                if (!validPassword) {
                    return res.status(400).json({ error: 'Invalid password' });
                }

                // Remove password from response
                const { password: _, ...userWithoutPassword } = user;
                return res.status(200).json(userWithoutPassword);
            }

            return res.status(400).json({ error: 'Invalid action' });
        } catch (error) {
            console.error('Database error:', error);
            return res.status(500).json({ error: 'Internal server error' });
        } finally {
            await client.close();
        }
    }

    return res.status(405).json({ error: 'Method not allowed' });
} 