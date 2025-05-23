const express = require('express');
const router = express.Router();
const { check, validationResult } = require('express-validator');
const Water = require('../models/Water');
const User = require('../models/User');

// @route   POST api/water
// @desc    Add water intake
router.post('/', [
  check('amount', 'Amount is required').isNumeric()
], async (req, res) => {
  const errors = validationResult(req);
  if (!errors.isEmpty()) {
    return res.status(400).json({ errors: errors.array() });
  }

  try {
    const { amount } = req.body;
    const water = new Water({
      user: req.user.id,
      amount
    });

    await water.save();
    res.json(water);
  } catch (err) {
    console.error(err.message);
    res.status(500).send('Server error');
  }
});

// @route   GET api/water
// @desc    Get user's water intake
router.get('/', async (req, res) => {
  try {
    const water = await Water.find({ user: req.user.id })
      .sort({ date: -1 });
    res.json(water);
  } catch (err) {
    console.error(err.message);
    res.status(500).send('Server error');
  }
});

// @route   PUT api/water/goal
// @desc    Update water goal
router.put('/goal', [
  check('goal', 'Goal is required').isNumeric()
], async (req, res) => {
  const errors = validationResult(req);
  if (!errors.isEmpty()) {
    return res.status(400).json({ errors: errors.array() });
  }

  try {
    const { goal } = req.body;
    const user = await User.findByIdAndUpdate(
      req.user.id,
      { waterGoal: goal },
      { new: true }
    );
    res.json(user);
  } catch (err) {
    console.error(err.message);
    res.status(500).send('Server error');
  }
});

// @route   GET api/water/stats
// @desc    Get water intake statistics
router.get('/stats', async (req, res) => {
  try {
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    const water = await Water.find({
      user: req.user.id,
      date: { $gte: today }
    });

    const totalAmount = water.reduce((sum, entry) => sum + entry.amount, 0);
    const user = await User.findById(req.user.id);

    res.json({
      totalAmount,
      goal: user.waterGoal,
      remaining: user.waterGoal - totalAmount
    });
  } catch (err) {
    console.error(err.message);
    res.status(500).send('Server error');
  }
});

module.exports = router; 