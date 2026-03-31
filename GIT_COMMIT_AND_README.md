#
#  GIT COMMANDS - Run these in your project terminal
# 

# Stage all changed and new files
git add language_select.php pronunciation.php dashboard.php quiz.php results.php progress.php

# Commit with descriptive message
git commit -m "Add prototype future features: multi-language support and audio pronunciation

NEW FILES:
- language_select.php: Language selection page demonstrating planned
  multi-language support. Spanish is active with 96 questions; French,
  German, Italian, Portuguese, and Japanese shown as coming soon.
  Stores language preference in session (future: database persistence).

- pronunciation.php: Audio pronunciation practice page using the
  Web Speech API. Features text-to-speech playback at normal and
  slow speeds, speech recognition for user input with comparison
  scoring, animated waveform visualisation, phonetic guides, and
  pronunciation tips. Includes category filtering across 6 sample
  phrases covering Greetings, Food & Dining, Travel, and Common
  Phrases.

MODIFIED FILES:
- dashboard.php: Added 'Coming Soon' section with cards linking to
  prototype pages (Languages, Pronunciation, Spaced Repetition).
  Updated navbar with new navigation links.

- quiz.php, results.php, progress.php: Updated navbar to include
  Languages and Pronunciation links for consistent site navigation.

PROTOTYPE RATIONALE:
These features are implemented as functional prototypes to demonstrate
the application's extensibility beyond its current Spanish quiz scope.
They showcase how QuizNinja's adaptive learning framework could scale
to support multiple languages, audio-based learning, and speech
recognition — key areas identified in the literature review as
effective for language acquisition.

TECHNOLOGIES USED:
- Web Speech API (SpeechSynthesis + SpeechRecognition)
- CSS Grid for responsive card layouts
- PHP session management for language preferences
- CSS animations for audio waveform visualisation"


# Push to GitHub
git push origin main


# 
#  README.md - Add this section to your existing README
#
# Paste this below your existing features list in README.md:

## Prototype Features (Future Development)

QuizNinja includes prototype pages that demonstrate planned future enhancements, showcasing the application's extensibility:

### Multi-Language Support (`language_select.php`)
- Language selection interface with 6 language options
- Spanish fully active with 96 questions across 4 categories
- French, German, Italian, Portuguese, and Japanese marked as "Coming Soon"
- Session-based language preference storage
- Planned: database-driven language configuration, independent progress tracking per language

### Audio Pronunciation Practice (`pronunciation.php`)
- Text-to-speech playback using the Web Speech API (`SpeechSynthesis`)
- Normal speed and slow (0.6x) playback modes
- Speech recognition input using `SpeechRecognition` API
- Real-time comparison of user speech against expected phrases
- Animated waveform visualisation during audio playback
- Phonetic guides and contextual pronunciation tips
- Category-based filtering (Greetings, Food & Dining, Travel, Common Phrases)
- Planned: pre-recorded native speaker audio files, Levenshtein distance matching, pronunciation scoring history

### Additional Planned Features
- **Spaced Repetition**: Smart review scheduling at optimal intervals for long-term retention
- **Leaderboards**: Competitive rankings per language with weekly and all-time scores
- **Cross-Language Analytics**: Comparative progress visualisation across multiple languages

> **Note:** These prototype features use the browser's built-in Web Speech API for demonstration purposes. A production version would integrate pre-recorded native speaker audio and server-side speech analysis for higher accuracy.
