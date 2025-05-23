const express = require('express');
const router = express.Router();
const { check, validationResult } = require('express-validator');
const Reminder = require('../models/Reminder');

// @route   POST api/reminders
// @desc    Create a reminder
router.post('/', [
  check('time', 'Time is required').not().isEmpty(),
  check('message', 'Message is required').not().isEmpty()
], async (req, res) => {
  const errors = validationResult(req);
  if (!errors.isEmpty()) {
    return res.status(400).json({ errors: errors.array() });
  }

  try {
    const { time, message } = req.body;
    const reminder = new Reminder({
      user: req.user.id,
      time,
      message
    });

    await reminder.save();
    res.json(reminder);
  } catch (err) {
    console.error(err.message);
    res.status(500).send('Server error');
  }
});

// @route   GET api/reminders
// @desc    Get user's reminders
router.get('/', async (req, res) => {
  try {
    const reminders = await Reminder.find({ user: req.user.id })
      .sort({ time: 1 });
    res.json(reminders);
  } catch (err) {
    console.error(err.message);
    res.status(500).send('Server error');
  }
});

// @route   PUT api/reminders/:id
// @desc    Update a reminder
router.put('/:id', [
  check('time', 'Time is required').not().isEmpty(),
  check('message', 'Message is required').not().isEmpty()
], async (req, res) => {
  const errors = validationResult(req);
  if (!errors.isEmpty()) {
    return res.status(400).json({ errors: errors.array() });
  }

  try {
    const { time, message, isActive } = req.body;
    let reminder = await Reminder.findById(req.params.id);

    if (!reminder) {
      return res.status(404).json({ msg: 'Reminder not found' });
    }

    if (reminder.user.toString() !== req.user.id) {
      return res.status(401).json({ msg: 'Not authorized' });
    }

    reminder = await Reminder.findByIdAndUpdate(
      req.params.id,
      { time, message, isActive },
      { new: true }
    );

    res.json(reminder);
  } catch (err) {
    console.error(err.message);
    res.status(500).send('Server error');
  }
});

// @route   DELETE api/reminders/:id
// @desc    Delete a reminder
router.delete('/:id', async (req, res) => {
  try {
    const reminder = await Reminder.findById(req.params.id);

    if (!reminder) {
      return res.status(404).json({ msg: 'Reminder not found' });
    }

    if (reminder.user.toString() !== req.user.id) {
      return res.status(401).json({ msg: 'Not authorized' });
    }

    await reminder.remove();
    res.json({ msg: 'Reminder removed' });
  } catch (err) {
    console.error(err.message);
    res.status(500).send('Server error');
  }
});

module.exports = router; 