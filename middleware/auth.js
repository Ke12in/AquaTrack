const jwt = require('jsonwebtoken');

module.exports = function(req, res, next) {
  console.log('Auth middleware reached'); // Log 1
  // Get token from header
  // Check for Authorization header with Bearer token
  const authHeader = req.header('Authorization');
  
  console.log('Authorization header:', authHeader); // Log 2

  if (!authHeader) {
     console.log('No Authorization header found'); // Log 3
     return res.status(401).json({ msg: 'No token, authorization denied' });
  }

  // Extract token (remove "Bearer " prefix)
  const token = authHeader.split(' ')[1];
  console.log('Extracted token:', token ? token.substring(0, 10) + '...' : 'No token after split'); // Log 4 (log first few chars)

  // Check if no token was extracted
  if (!token) {
    console.log('Token extraction failed'); // Log 5
    return res.status(401).json({ msg: 'No token provided or token format is incorrect' });
  }

  try {
    console.log('Attempting JWT verification'); // Log 6
    const decoded = jwt.verify(token, process.env.JWT_SECRET);
    console.log('JWT verification successful', decoded); // Log 7
    req.user = decoded.user;
    console.log('req.user set:', req.user); // Log 8
    next();
  } catch (err) {
    console.error('JWT verification failed:', err.message); // Log 9
    res.status(401).json({ msg: 'Token is not valid' });
  }
}; 