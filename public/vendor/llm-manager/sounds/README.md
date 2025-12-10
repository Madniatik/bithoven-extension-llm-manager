# Sound Notifications - Audio Files

This directory contains audio files used for sound notifications when AI responses are ready.

## Required Files

The following sound files are expected by the system:

1. **notification.mp3** (Default)
   - Professional, subtle notification sound
   - Recommended: 0.5-1 second duration
   - Volume: Moderate (will be played at 50% volume)

2. **ping.mp3**
   - Short, high-pitched ping sound
   - Recommended: 0.2-0.5 second duration
   - Example: Single sine wave beep

3. **chime.mp3**
   - Melodic, pleasant chime sound
   - Recommended: 0.5-1.5 seconds duration
   - Example: Wind chime or bell sound

4. **beep.mp3**
   - Technical beep sound
   - Recommended: 0.3-0.8 seconds duration
   - Example: Electronic beep

5. **swoosh.mp3**
   - Soft, whoosh/swipe sound
   - Recommended: 0.4-1 second duration
   - Example: Gentle air movement

## File Format

- **Format:** MP3 (MPEG Audio Layer III)
- **Sample Rate:** 44.1 kHz recommended
- **Bit Rate:** 128-192 kbps recommended
- **Channels:** Mono or Stereo
- **Max File Size:** < 100KB per file (keep sounds short and optimized)

## Free Sound Resources

You can find free notification sounds at:

- **Freesound.org:** https://freesound.org/search/?q=notification+beep
- **Zapsplat:** https://www.zapsplat.com/sound-effect-category/notifications/
- **Mixkit:** https://mixkit.co/free-sound-effects/notification/
- **Notification Sounds:** https://notificationsounds.com/

## License Considerations

Ensure any sounds you use are:
- Licensed for commercial use (if applicable)
- Free to redistribute
- Properly attributed if required

## Testing Sounds

After adding sound files, test them in the Chat Settings:
1. Go to Chat → Settings → UX Enhancements
2. Enable "Sound Notifications"
3. Select a sound file from the dropdown
4. Send a message and switch to another tab
5. Verify the sound plays when the response completes

## Current Status

⚠️ **Sound files not included** - This directory is a placeholder.

You must add the 5 required MP3 files manually. Until then, sound notifications will fail silently (with console warnings).

## Implementation Details

- Sounds are played at **50% volume** (0.5)
- Only play when **tab is hidden** (user in another app/tab)
- Audio element is created dynamically on each notification
- Errors are caught and logged to console (won't break functionality)
- Path used: `/vendor/llm-manager/sounds/{filename}`
