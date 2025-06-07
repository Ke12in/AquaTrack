import { MongoClient } from 'mongodb';

const uri = process.env.MONGODB_URI;
const client = new MongoClient(uri);

export default async function handler(req, res) {
    if (req.method === 'POST') {
        try {
            await client.connect();
            const database = client.db('aquatrack');
            const waterData = database.collection('water_data');

            const { userId, amount, timestamp } = req.body;

            const result = await waterData.insertOne({
                userId,
                amount,
                timestamp: new Date(timestamp),
                createdAt: new Date()
            });

            return res.status(201).json({ 
                message: 'Water intake recorded',
                dataId: result.insertedId
            });
        } catch (error) {
            console.error('Database error:', error);
            return res.status(500).json({ error: 'Internal server error' });
        } finally {
            await client.close();
        }
    }

    if (req.method === 'GET') {
        try {
            await client.connect();
            const database = client.db('aquatrack');
            const waterData = database.collection('water_data');

            const { userId, startDate, endDate } = req.query;

            const query = {
                userId,
                timestamp: {
                    $gte: new Date(startDate),
                    $lte: new Date(endDate)
                }
            };

            const data = await waterData.find(query).toArray();
            return res.status(200).json(data);
        } catch (error) {
            console.error('Database error:', error);
            return res.status(500).json({ error: 'Internal server error' });
        } finally {
            await client.close();
        }
    }

    return res.status(405).json({ error: 'Method not allowed' });
} 