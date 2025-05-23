# AquaDTrack - Water Tracking Application

A modern water tracking application built with Node.js, Express, and MongoDB.

## Features

- User authentication (signup/login)
- Water intake tracking
- Daily water goals
- Reminders
- Profile management
- Statistics and progress tracking

## Prerequisites

- Node.js (v14 or higher)
- MongoDB database
- Vercel account (for deployment)

## Setup

1. Clone the repository:
```bash
git clone <your-repo-url>
cd aquadtrack
```

2. Install dependencies:
```bash
npm install
```

3. Create a `.env` file in the root directory with the following variables:
```
MONGODB_URI=your_mongodb_uri
JWT_SECRET=your_jwt_secret
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=your_email@gmail.com
SMTP_PASS=your_email_password
NODE_ENV=development
```

4. Start the development server:
```bash
npm run dev
```

## Deployment on Vercel

1. Push your code to GitHub

2. Connect your GitHub repository to Vercel

3. Configure the following environment variables in Vercel:
   - MONGODB_URI
   - JWT_SECRET
   - SMTP_HOST
   - SMTP_PORT
   - SMTP_USER
   - SMTP_PASS
   - NODE_ENV=production

4. Deploy!

## API Endpoints

### Authentication
- POST /api/auth/register - Register a new user
- POST /api/auth/login - Login user

### Water Tracking
- POST /api/water - Add water intake
- GET /api/water - Get water intake history
- PUT /api/water/goal - Update water goal
- GET /api/water/stats - Get water intake statistics

### Reminders
- POST /api/reminders - Create a reminder
- GET /api/reminders - Get user's reminders
- PUT /api/reminders/:id - Update a reminder
- DELETE /api/reminders/:id - Delete a reminder

### Profile
- GET /api/profile - Get user profile
- PUT /api/profile - Update user profile
- PUT /api/profile/password - Update user password

## License

MIT 