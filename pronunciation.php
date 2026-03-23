<?php
/**
 * 
 * AUDIO PRONUNCIATION PAGE (Prototype - Future Feature)
 * 
 * QuizNinja - Adaptive Quiz Web Application
 * Author: Matthew Holness
 * Student ID: 22068679
 * Module: 6COM2018
 * 
 * 
 * PURPOSE:
 * This page demonstrates a potential audio pronunciation feature
 * that would enhance the quiz experience by allowing users to:
 *   1. Listen to native pronunciation of Spanish words/phrases
 *   2. Practise speaking with speech recognition feedback
 *   3. Compare their pronunciation with the correct version
 * 
 * TECHNOLOGY:
 * - Web Speech API (SpeechSynthesis) for text-to-speech playback
 * - Web Speech API (SpeechRecognition) for user speech input
 * - Visual waveform animation for audio feedback
 * 
 * NOTE: This is a prototype demonstration. In a full implementation,
 * pre-recorded native speaker audio files would replace the
 * browser's built-in text-to-speech for higher quality pronunciation.
 * 
 */

session_start();
require_once 'includes/db_connect.php';
require_once 'includes/auth.php';

// Require user to be logged in
require_login();
$user = get_logged_in_user();

/**
 * SAMPLE PRONUNCIATION DATA
 * -------------------------
 * In the full implementation, this data would come from the
 * existing 'questions' table with an additional 'audio_file'
 * column, or from a dedicated 'pronunciation' table.
 * 
 * Each entry contains:
 * - spanish:    The word/phrase in Spanish
 * - english:    English translation for context
 * - phonetic:   Phonetic guide to aid pronunciation
 * - category:   Maps to existing quiz categories
 * - difficulty:  Maps to adaptive difficulty levels
 * - tips:       Pronunciation tips for the learner
 */
$pronunciation_items = [
    [
        'spanish'    => 'Buenos días',
        'english'    => 'Good morning',
        'phonetic'   => 'BWEH-nos DEE-ahs',
        'category'   => 'Greetings',
        'difficulty' => 'easy',
        'tips'       => 'The "u" in "Buenos" is pronounced like "oo" in "food". Stress falls on the first syllable of "días".'
    ],
    [
        'spanish'    => '¿Cómo estás?',
        'english'    => 'How are you?',
        'phonetic'   => 'KOH-moh ehs-TAHS',
        'category'   => 'Greetings',
        'difficulty' => 'easy',
        'tips'       => 'The accent on "Cómo" indicates stress on the first syllable. "Estás" has stress on the last syllable.'
    ],
    [
        'spanish'    => 'Me gustaría una mesa para dos',
        'english'    => 'I would like a table for two',
        'phonetic'   => 'meh goos-tah-REE-ah OO-nah MEH-sah PAH-rah dohs',
        'category'   => 'Travel',
        'difficulty' => 'medium',
        'tips'       => 'Roll the "r" slightly in "gustaría". The "j" sound does not appear here — keep vowels pure.'
    ],
    [
        'spanish'    => 'La cuenta, por favor',
        'english'    => 'The bill, please',
        'phonetic'   => 'lah KWEHN-tah, pohr fah-VOHR',
        'category'   => 'Food & Dining',
        'difficulty' => 'easy',
        'tips'       => '"Cuenta" has a diphthong "ue" — say "kweh" quickly. "Favor" stresses the last syllable.'
    ],
    [
        'spanish'    => '¿Dónde está la estación de tren?',
        'english'    => 'Where is the train station?',
        'phonetic'   => 'DOHN-deh ehs-TAH lah ehs-tah-SYOHN deh trehn',
        'category'   => 'Travel',
        'difficulty' => 'medium',
        'tips'       => '"Estación" has the stress on the final syllable "-ción". The "r" in "tren" is a single tap.'
    ],
    [
        'spanish'    => 'Necesito hablar con el gerente',
        'english'    => 'I need to speak with the manager',
        'phonetic'   => 'neh-seh-SEE-toh ah-BLAHR kohn ehl heh-REHN-teh',
        'category'   => 'Common Phrases',
        'difficulty' => 'hard',
        'tips'       => 'The "h" in "hablar" is silent. "Gerente" has a soft "g" (like English "h") before "e".'
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audio Pronunciation - QuizNinja</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /**
         * AUDIO PRONUNCIATION STYLES
         * --------------------------
         * Custom styles for the pronunciation practice interface.
         * Extends the QuizNinja purple theme with audio-specific
         * visual elements like waveform animations and status indicators.
         */

        /* Page Header */
        .audio-header {
            text-align: center;
            padding: 2rem 0 1rem;
        }

        .audio-header h1 {
            font-size: 2rem;
            color: var(--text-primary, #1a1a2e);
            margin-bottom: 0.5rem;
        }

        .audio-header p {
            color: var(--text-muted, #6b7280);
            font-size: 1.05rem;
        }

        /* Prototype Banner */
        .prototype-banner {
            background: linear-gradient(135deg, var(--primary-color, #0d7377) 0%, var(--accent-color, #d4a843) 100%);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius-lg, 12px);
            text-align: center;
            margin-bottom: 2rem;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .prototype-banner .badge-proto {
            background: rgba(255, 255, 255, 0.25);
            padding: 0.2rem 0.6rem;
            border-radius: 999px;
            font-weight: 600;
            font-size: 0.75rem;
        }

        /* Filter Controls */
        .filter-bar {
            display: flex;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 0.5rem 1rem;
            border-radius: 999px;
            border: 2px solid var(--gray-200, #e5e7eb);
            background: var(--white, #fff);
            color: var(--text-muted, #6b7280);
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .filter-btn:hover,
        .filter-btn.active {
            border-color: var(--primary-color, var(--primary-color, #0d7377));
            color: var(--primary-color, var(--primary-color, #0d7377));
            background: rgba(13, 115, 119, 0.08);
        }

        /* Pronunciation Card */
        .pronunciation-card {
            background: var(--white, #ffffff);
            border-radius: var(--radius-xl, 16px);
            padding: 1.75rem;
            margin-bottom: 1.25rem;
            box-shadow: var(--shadow-sm, 0 1px 3px rgba(0,0,0,0.1));
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .pronunciation-card:hover {
            box-shadow: var(--shadow-md, 0 4px 12px rgba(0,0,0,0.12));
        }

        .pronunciation-card.playing {
            border-color: var(--primary-color, var(--primary-color, #0d7377));
            box-shadow: 0 0 0 3px rgba(13, 115, 119, 0.15);
        }

        /* Card top row: Spanish text + controls */
        .card-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 0.75rem;
            gap: 1rem;
        }

        .spanish-text {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary, #1a1a2e);
        }

        .english-text {
            font-size: 1rem;
            color: var(--text-muted, #6b7280);
            margin-bottom: 0.5rem;
        }

        .phonetic-text {
            font-size: 0.9rem;
            color: var(--primary-color, var(--primary-color, #0d7377));
            font-style: italic;
            font-family: 'Courier New', monospace;
            background: rgba(13, 115, 119, 0.06);
            padding: 0.35rem 0.75rem;
            border-radius: var(--radius-md, 8px);
            display: inline-block;
            margin-bottom: 0.75rem;
        }

        /* Badge row */
        .badge-row {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 0.75rem;
        }

        .badge-category {
            background: var(--gray-100, #f3f4f6);
            color: var(--text-muted, #6b7280);
            padding: 0.2rem 0.6rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        /* Audio Control Buttons */
        .audio-controls {
            display: flex;
            gap: 0.5rem;
        }

        .btn-audio {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            transition: all 0.2s ease;
        }

        .btn-listen {
            background: linear-gradient(135deg, var(--primary-color, #0d7377), var(--accent-color, #d4a843));
            color: white;
        }

        .btn-listen:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 12px rgba(13, 115, 119, 0.4);
        }

        .btn-slow {
            background: var(--gray-100, #f3f4f6);
            color: var(--text-primary, #1a1a2e);
        }

        .btn-slow:hover {
            background: var(--gray-200, #e5e7eb);
        }

        .btn-mic {
            background: linear-gradient(135deg, var(--success-color, #2e7d32), #256d29);
            color: white;
        }

        .btn-mic:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 12px rgba(46, 125, 50, 0.4);
        }

        .btn-mic.recording {
            animation: pulse-mic 1.5s infinite;
            background: linear-gradient(135deg, var(--error-color, #c62828), #b71c1c);
        }

        @keyframes pulse-mic {
            0%, 100% { box-shadow: 0 0 0 0 rgba(198, 40, 40, 0.5); }
            50% { box-shadow: 0 0 0 10px rgba(198, 40, 40, 0); }
        }

        /* Waveform Animation */
        .waveform-container {
            display: none;
            align-items: center;
            justify-content: center;
            gap: 3px;
            padding: 0.75rem 0;
            margin-top: 0.5rem;
        }

        .waveform-container.visible {
            display: flex;
        }

        .wave-bar {
            width: 4px;
            height: 20px;
            background: var(--primary-color, var(--primary-color, #0d7377));
            border-radius: 2px;
            animation: waveform 0.8s ease-in-out infinite;
        }

        .wave-bar:nth-child(1) { animation-delay: 0s; }
        .wave-bar:nth-child(2) { animation-delay: 0.1s; }
        .wave-bar:nth-child(3) { animation-delay: 0.2s; }
        .wave-bar:nth-child(4) { animation-delay: 0.3s; }
        .wave-bar:nth-child(5) { animation-delay: 0.4s; }
        .wave-bar:nth-child(6) { animation-delay: 0.3s; }
        .wave-bar:nth-child(7) { animation-delay: 0.2s; }
        .wave-bar:nth-child(8) { animation-delay: 0.1s; }

        @keyframes waveform {
            0%, 100% { height: 8px; opacity: 0.5; }
            50% { height: 28px; opacity: 1; }
        }

        /* Pronunciation tips section */
        .tips-section {
            background: var(--gray-50, #f9fafb);
            border-radius: var(--radius-lg, 12px);
            padding: 0.75rem 1rem;
            margin-top: 0.75rem;
            font-size: 0.85rem;
            color: var(--text-muted, #6b7280);
            line-height: 1.5;
        }

        .tips-section strong {
            color: var(--text-primary, #1a1a2e);
        }

        /* Speech Recognition Result Display */
        .speech-result {
            display: none;
            margin-top: 0.75rem;
            padding: 0.75rem 1rem;
            border-radius: var(--radius-lg, 12px);
            font-size: 0.9rem;
        }

        .speech-result.visible {
            display: block;
        }

        .speech-result.success {
            background: rgba(46, 125, 50, 0.1);
            border: 1px solid rgba(46, 125, 50, 0.3);
            color: #166534;
        }

        .speech-result.partial {
            background: rgba(234, 179, 8, 0.1);
            border: 1px solid rgba(234, 179, 8, 0.3);
            color: #854d0e;
        }

        .speech-result.error {
            background: rgba(198, 40, 40, 0.1);
            border: 1px solid rgba(198, 40, 40, 0.3);
            color: #991b1b;
        }

        /* Speed selector */
        .speed-indicator {
            font-size: 0.7rem;
            font-weight: 600;
            color: var(--text-muted, #6b7280);
            text-align: center;
            margin-top: 2px;
        }

        /* How it works info section */
        .how-it-works {
            background: var(--white, #ffffff);
            border-radius: var(--radius-xl, 16px);
            padding: 2rem;
            margin-top: 2rem;
            box-shadow: var(--shadow-sm, 0 1px 3px rgba(0,0,0,0.1));
        }

        .how-it-works h3 {
            margin-bottom: 1.25rem;
            color: var(--text-primary, #1a1a2e);
        }

        .steps-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.25rem;
        }

        .step-item {
            text-align: center;
            padding: 1.25rem;
        }

        .step-number {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary-color, #0d7377), var(--accent-color, #d4a843));
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            margin: 0 auto 0.75rem;
        }

        .step-item h4 {
            font-size: 0.95rem;
            margin-bottom: 0.3rem;
            color: var(--text-primary, #1a1a2e);
        }

        .step-item p {
            font-size: 0.8rem;
            color: var(--text-muted, #6b7280);
            line-height: 1.4;
        }

        /* Responsive adjustments */
        @media (max-width: 640px) {
            .card-top {
                flex-direction: column;
            }
            .audio-controls {
                align-self: flex-start;
            }
        }

        /* ── Prototype Detail Box ── */
        .prototype-detail-box {
            background: var(--white, #ffffff);
            border-radius: var(--radius-xl, 16px);
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-sm, 0 1px 3px rgba(0,0,0,0.1));
            border-left: 5px solid var(--primary-color, #0d7377);
        }

        .prototype-detail-box h3 {
            font-size: 1.2rem;
            margin-bottom: 0.75rem;
            color: var(--text-primary, #1a1a2e);
        }

        .prototype-detail-box > p {
            color: var(--text-muted, #6b7280);
            font-size: 0.92rem;
            line-height: 1.65;
            margin-bottom: 1.5rem;
        }

        .proto-columns {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .proto-col h4 {
            font-size: 0.95rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            color: var(--text-primary, #1a1a2e);
        }

        .proto-list {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .proto-list-item {
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            font-size: 0.85rem;
            color: var(--text-muted, #6b7280);
            line-height: 1.45;
        }

        .proto-check {
            color: var(--success-color, #2e7d32);
            font-weight: 700;
            flex-shrink: 0;
        }

        .proto-arrow {
            color: var(--primary-color, #0d7377);
            font-weight: 700;
            flex-shrink: 0;
        }

        .proto-list-item code {
            background: rgba(13, 115, 119, 0.08);
            color: var(--primary-color, #0d7377);
            padding: 0.1rem 0.35rem;
            border-radius: 4px;
            font-size: 0.8rem;
        }

        .proto-tech {
            background: var(--gray-50, #f9fafb);
            border-radius: var(--radius-lg, 12px);
            padding: 1.25rem;
            margin-bottom: 1rem;
        }

        .proto-tech h4 {
            font-size: 0.95rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            color: var(--text-primary, #1a1a2e);
        }

        .tech-badges {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 0.75rem;
        }

        .tech-badge {
            background: var(--white, #fff);
            border: 1px solid var(--gray-200, #e5e7eb);
            border-radius: 8px;
            padding: 0.65rem 0.85rem;
        }

        .tech-badge strong {
            display: block;
            font-size: 0.82rem;
            color: var(--primary-color, #0d7377);
            margin-bottom: 0.2rem;
        }

        .tech-badge span {
            font-size: 0.78rem;
            color: var(--text-muted, #6b7280);
            line-height: 1.4;
        }

        .proto-limitations {
            background: rgba(234, 179, 8, 0.06);
            border: 1px solid rgba(234, 179, 8, 0.2);
            border-radius: var(--radius-lg, 12px);
            padding: 1rem 1.25rem;
        }

        .proto-limitations h4 {
            font-size: 0.95rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: #854d0e;
        }

        .proto-limitations p {
            font-size: 0.83rem;
            color: #854d0e;
            line-height: 1.55;
        }

        .proto-limitations code {
            background: rgba(13, 115, 119, 0.08);
            color: var(--primary-color, #0d7377);
            padding: 0.1rem 0.35rem;
            border-radius: 4px;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <div class="page-wrapper">

        <!-- 
             NAVIGATION BAR
              -->
        <nav class="navbar">
            <div class="container">
                <a href="dashboard.php" class="navbar-brand">QuizNinja</a>
                <ul class="navbar-nav">
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="language_select.php">Languages</a></li>
                    <li><a href="pronunciation.php" class="active">Pronunciation</a></li>
                    <li><a href="progress.php">Progress</a></li>
                    <li><a href="logout.php">Logout</a></li>
                    <li>
                        <div class="user-avatar" title="<?php echo htmlspecialchars($user['username']); ?>">
                            <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- 
             MAIN CONTENT
             -->
        <main class="main-content">
            <div class="container">

                <!-- Page Header -->
                <div class="audio-header">
                    <h1>🔊 Audio Pronunciation Practice</h1>
                    <p>Listen, repeat, and perfect your Spanish pronunciation</p>
                </div>

                <!-- Prototype Indicator -->
                <div class="prototype-banner">
                    <span class="badge-proto">PROTOTYPE</span>
                    This feature uses the browser's Web Speech API for demonstration. A production version would use pre-recorded native speaker audio.
                </div>

                <!-- 
                     DETAILED PROTOTYPE EXPLANATION
                      -->
                <div class="prototype-detail-box">
                    <h3>🔬 About This Prototype</h3>
                    <p>
                        This page is a <strong>functional prototype</strong> demonstrating how audio-based pronunciation 
                        practice could be integrated into QuizNinja's adaptive learning system. It uses the browser's 
                        built-in <strong>Web Speech API</strong> — both <code>SpeechSynthesis</code> for text-to-speech 
                        playback and <code>SpeechRecognition</code> for capturing user speech — to create a working 
                        demonstration without requiring external services or API keys. This prototype is intentionally 
                        included to showcase the application's potential for multi-modal language learning.
                    </p>

                    <div class="proto-columns">
                        <div class="proto-col">
                            <h4>✅ What This Prototype Demonstrates</h4>
                            <div class="proto-list">
                                <div class="proto-list-item">
                                    <span class="proto-check">✓</span>
                                    <span>Text-to-speech playback of Spanish phrases at normal and slow (0.6×) speed using <code>SpeechSynthesis</code></span>
                                </div>
                                <div class="proto-list-item">
                                    <span class="proto-check">✓</span>
                                    <span>Speech recognition input via <code>SpeechRecognition</code> with real-time transcription and confidence scoring</span>
                                </div>
                                <div class="proto-list-item">
                                    <span class="proto-check">✓</span>
                                    <span>String normalisation and comparison logic to evaluate user pronunciation accuracy</span>
                                </div>
                                <div class="proto-list-item">
                                    <span class="proto-check">✓</span>
                                    <span>Animated waveform visualisation during audio playback for user feedback</span>
                                </div>
                                <div class="proto-list-item">
                                    <span class="proto-check">✓</span>
                                    <span>Category-based filtering across Greetings, Food &amp; Dining, Travel, and Common Phrases</span>
                                </div>
                                <div class="proto-list-item">
                                    <span class="proto-check">✓</span>
                                    <span>Phonetic guides (IPA-style) and contextual pronunciation tips per phrase</span>
                                </div>
                                <div class="proto-list-item">
                                    <span class="proto-check">✓</span>
                                    <span>Graceful error handling for unsupported browsers and denied microphone permissions</span>
                                </div>
                            </div>
                        </div>

                        <div class="proto-col">
                            <h4>🔮 What a Production Version Would Require</h4>
                            <div class="proto-list">
                                <div class="proto-list-item">
                                    <span class="proto-arrow">→</span>
                                    <span>Pre-recorded <code>.mp3</code>/<code>.ogg</code> audio files from native Spanish speakers instead of browser TTS</span>
                                </div>
                                <div class="proto-list-item">
                                    <span class="proto-arrow">→</span>
                                    <span>An <code>audio_file</code> column added to the <code>questions</code> table referencing stored audio assets</span>
                                </div>
                                <div class="proto-list-item">
                                    <span class="proto-arrow">→</span>
                                    <span>Levenshtein distance or phonetic matching algorithm (e.g. Soundex) instead of exact string comparison</span>
                                </div>
                                <div class="proto-list-item">
                                    <span class="proto-arrow">→</span>
                                    <span>Server-side speech analysis using a service like Google Cloud Speech-to-Text for higher accuracy</span>
                                </div>
                                <div class="proto-list-item">
                                    <span class="proto-arrow">→</span>
                                    <span>A <code>pronunciation_scores</code> table to track user improvement over time</span>
                                </div>
                                <div class="proto-list-item">
                                    <span class="proto-arrow">→</span>
                                    <span>Integration with the adaptive algorithm so pronunciation scores influence difficulty progression</span>
                                </div>
                                <div class="proto-list-item">
                                    <span class="proto-arrow">→</span>
                                    <span>Wider browser compatibility testing (currently best in Chrome/Edge)</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="proto-tech">
                        <h4>⚙️ Technologies Used in This Prototype</h4>
                        <div class="tech-badges">
                            <div class="tech-badge">
                                <strong>SpeechSynthesis API</strong>
                                <span>Browser-native text-to-speech with Spanish (es-ES) voice selection</span>
                            </div>
                            <div class="tech-badge">
                                <strong>SpeechRecognition API</strong>
                                <span>Browser-native speech-to-text with confidence scoring (Chrome/Edge)</span>
                            </div>
                            <div class="tech-badge">
                                <strong>CSS Animations</strong>
                                <span>Keyframe-based waveform visualisation and recording pulse indicator</span>
                            </div>
                            <div class="tech-badge">
                                <strong>PHP Data Layer</strong>
                                <span>Sample phrases served via PHP array (production: database query)</span>
                            </div>
                        </div>
                    </div>

                    <div class="proto-limitations">
                        <h4>⚠️ Known Prototype Limitations</h4>
                        <p>
                            Browser text-to-speech quality varies by device and operating system — some voices sound more natural than others.
                            Speech recognition requires microphone permission and works best in Chrome or Edge; Firefox does not support the <code>SpeechRecognition</code> API.
                            The current comparison uses exact string matching after normalisation, meaning minor variations in phrasing may be marked as incorrect even when pronunciation is acceptable.
                            Pronunciation scores are not persisted to the database in this prototype — they are displayed in-session only.
                        </p>
                    </div>
                </div>

                <!-- 
                     CATEGORY FILTER BAR
                     
                     Allows users to filter pronunciation cards by category.
                     Uses JavaScript to show/hide cards based on selection.
                -->
                <div class="filter-bar">
                    <button class="filter-btn active" onclick="filterCards('all')">All</button>
                    <button class="filter-btn" onclick="filterCards('Greetings')">Greetings</button>
                    <button class="filter-btn" onclick="filterCards('Food & Dining')">Food & Dining</button>
                    <button class="filter-btn" onclick="filterCards('Travel')">Travel</button>
                    <button class="filter-btn" onclick="filterCards('Common Phrases')">Common Phrases</button>
                </div>

                <!-- ========================================================
                     PRONUNCIATION CARDS
                     ========================================================
                     Each card displays a Spanish phrase with:
                     - Listen button (text-to-speech at normal speed)
                     - Slow button (text-to-speech at 0.6x speed)
                     - Microphone button (speech recognition input)
                     - Phonetic guide and pronunciation tips
                -->
                <?php foreach ($pronunciation_items as $index => $item): ?>
                <div class="pronunciation-card" id="card-<?php echo $index; ?>" data-category="<?php echo htmlspecialchars($item['category']); ?>">
                    <div class="card-top">
                        <div>
                            <div class="spanish-text"><?php echo htmlspecialchars($item['spanish']); ?></div>
                            <div class="english-text"><?php echo htmlspecialchars($item['english']); ?></div>
                            <div class="phonetic-text"><?php echo htmlspecialchars($item['phonetic']); ?></div>
                            <div class="badge-row">
                                <span class="badge-category"><?php echo htmlspecialchars($item['category']); ?></span>
                                <span class="badge badge-<?php echo $item['difficulty']; ?>"><?php echo ucfirst($item['difficulty']); ?></span>
                            </div>
                        </div>
                        <div class="audio-controls">
                            <!-- Listen at normal speed -->
                            <button class="btn-audio btn-listen" 
                                    onclick="speakText('<?php echo addslashes($item['spanish']); ?>', 1.0, <?php echo $index; ?>)" 
                                    title="Listen to pronunciation">
                                🔊
                            </button>
                            <!-- Listen at slow speed -->
                            <div style="text-align:center;">
                                <button class="btn-audio btn-slow" 
                                        onclick="speakText('<?php echo addslashes($item['spanish']); ?>', 0.6, <?php echo $index; ?>)" 
                                        title="Listen slowly">
                                    🐢
                                </button>
                                <div class="speed-indicator">Slow</div>
                            </div>
                            <!-- Record and compare -->
                            <div style="text-align:center;">
                                <button class="btn-audio btn-mic" 
                                        id="mic-btn-<?php echo $index; ?>"
                                        onclick="startRecognition('<?php echo addslashes($item['spanish']); ?>', <?php echo $index; ?>)" 
                                        title="Try speaking">
                                    🎙️
                                </button>
                                <div class="speed-indicator">Speak</div>
                            </div>
                        </div>
                    </div>

                    <!-- Waveform animation (shown during playback) -->
                    <div class="waveform-container" id="waveform-<?php echo $index; ?>">
                        <div class="wave-bar"></div>
                        <div class="wave-bar"></div>
                        <div class="wave-bar"></div>
                        <div class="wave-bar"></div>
                        <div class="wave-bar"></div>
                        <div class="wave-bar"></div>
                        <div class="wave-bar"></div>
                        <div class="wave-bar"></div>
                    </div>

                    <!-- Speech recognition result (shown after user speaks) -->
                    <div class="speech-result" id="result-<?php echo $index; ?>"></div>

                    <!-- Pronunciation tips -->
                    <div class="tips-section">
                        <strong>💡 Tip:</strong> <?php echo htmlspecialchars($item['tips']); ?>
                    </div>
                </div>
                <?php endforeach; ?>

                <!-- 
                     HOW IT WORKS SECTION
                      -->
                <div class="how-it-works">
                    <h3>How Pronunciation Practice Works</h3>
                    <div class="steps-grid">
                        <div class="step-item">
                            <div class="step-number">1</div>
                            <h4>Listen</h4>
                            <p>Click the speaker button to hear the correct pronunciation at normal or slow speed.</p>
                        </div>
                        <div class="step-item">
                            <div class="step-number">2</div>
                            <h4>Read</h4>
                            <p>Follow the phonetic guide and pronunciation tips for each phrase.</p>
                        </div>
                        <div class="step-item">
                            <div class="step-number">3</div>
                            <h4>Speak</h4>
                            <p>Click the microphone button and say the phrase aloud for instant feedback.</p>
                        </div>
                        <div class="step-item">
                            <div class="step-number">4</div>
                            <h4>Improve</h4>
                            <p>Compare your attempt with the original and repeat until confident.</p>
                        </div>
                    </div>
                </div>

            </div>
        </main>

        <!-- Footer -->
        <footer class="footer">
            <div class="container">
                <p>&copy; 2025 QuizNinja - Adaptive Language Learning | 6COM2018 Final Year Project</p>
            </div>
        </footer>
    </div>

    <!-- 
         JAVASCRIPT: Speech Synthesis and Recognition
         
         Uses the Web Speech API, which is built into modern browsers.
         
         SpeechSynthesis: Converts text to spoken audio output
         SpeechRecognition: Captures and transcribes user's spoken input
         
         BROWSER SUPPORT:
         - Chrome, Edge: Full support for both APIs
         - Firefox: Synthesis only (recognition not supported)
         - Safari: Partial support
         
         FUTURE IMPROVEMENT:
         Replace browser TTS with pre-recorded .mp3/.ogg files from
         native speakers stored in an 'audio/' directory, referenced
         via an 'audio_file' column in the questions table.
     -->
    <script>
        /**
         * speakText()
         * -----------
         * Uses the Web Speech API's SpeechSynthesis interface to
         * pronounce Spanish text aloud through the browser.
         *
         * @param {string} text     - The Spanish text to pronounce
         * @param {number} rate     - Playback speed (1.0 = normal, 0.6 = slow)
         * @param {number} cardIndex - Index of the card for visual feedback
         */
        function speakText(text, rate, cardIndex) {
            // Cancel any ongoing speech
            window.speechSynthesis.cancel();

            // Create a new speech utterance
            const utterance = new SpeechSynthesisUtterance(text);
            
            /**
             * Set the language to Spanish (Spain).
             * The browser will attempt to use a Spanish voice if available.
             * Common voice names include 'Google español' and 'Mónica'.
             */
            utterance.lang = 'es-ES';
            utterance.rate = rate;
            utterance.pitch = 1.0;
            utterance.volume = 1.0;

            // Try to find a Spanish voice specifically
            const voices = window.speechSynthesis.getVoices();
            const spanishVoice = voices.find(v => v.lang.startsWith('es'));
            if (spanishVoice) {
                utterance.voice = spanishVoice;
            }

            // Visual feedback: show waveform, highlight card
            const card = document.getElementById('card-' + cardIndex);
            const waveform = document.getElementById('waveform-' + cardIndex);

            utterance.onstart = function() {
                card.classList.add('playing');
                waveform.classList.add('visible');
            };

            utterance.onend = function() {
                card.classList.remove('playing');
                waveform.classList.remove('visible');
            };

            utterance.onerror = function() {
                card.classList.remove('playing');
                waveform.classList.remove('visible');
            };

            // Speak the text
            window.speechSynthesis.speak(utterance);
        }

        /**
         * startRecognition()
         * 
         * Uses the Web Speech API's SpeechRecognition interface to
         * capture the user's spoken Spanish and compare it against
         * the expected phrase.
         *
         * @param {string} expectedText - The correct Spanish phrase
         * @param {number} cardIndex    - Index of the card for visual feedback
         */
        function startRecognition(expectedText, cardIndex) {
            // Check browser support for SpeechRecognition
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            
            if (!SpeechRecognition) {
                const resultDiv = document.getElementById('result-' + cardIndex);
                resultDiv.className = 'speech-result visible error';
                resultDiv.innerHTML = '⚠️ Speech recognition is not supported in this browser. Please try Chrome or Edge.';
                return;
            }

            const recognition = new SpeechRecognition();
            const micBtn = document.getElementById('mic-btn-' + cardIndex);
            const resultDiv = document.getElementById('result-' + cardIndex);

            /**
             * Configure recognition for Spanish input.
             * interimResults shows partial transcription as the user speaks.
             */
            recognition.lang = 'es-ES';
            recognition.interimResults = false;
            recognition.maxAlternatives = 1;

            // Visual feedback: recording state
            micBtn.classList.add('recording');
            resultDiv.className = 'speech-result visible partial';
            resultDiv.innerHTML = '🎙️ Listening... Speak now!';

            recognition.onresult = function(event) {
                const transcript = event.results[0][0].transcript;
                const confidence = Math.round(event.results[0][0].confidence * 100);
                
                /**
                 * COMPARISON LOGIC
                 * 
                 * Normalises both strings by converting to lowercase,
                 * removing punctuation (including ¿ and ¡), and trimming
                 * whitespace before comparing.
                 * 
                 * In a full implementation, this would use a more
                 * sophisticated comparison algorithm such as
                 * Levenshtein distance or phonetic matching.
                 */
                const normalise = (str) => str.toLowerCase()
                    .replace(/[¿¡?,.\/#!$%\^&\*;:{}=\-_`~()]/g, '')
                    .trim();
                
                const expected = normalise(expectedText);
                const spoken = normalise(transcript);
                
                if (expected === spoken) {
                    resultDiv.className = 'speech-result visible success';
                    resultDiv.innerHTML = 
                        '✅ <strong>Excellent!</strong> You said: "' + transcript + 
                        '" — Perfect match! (Confidence: ' + confidence + '%)';
                } else {
                    resultDiv.className = 'speech-result visible partial';
                    resultDiv.innerHTML = 
                        '🔄 <strong>Close!</strong> You said: "' + transcript + 
                        '" — Expected: "' + expectedText + 
                        '" (Confidence: ' + confidence + '%). Try again!';
                }
            };

            recognition.onerror = function(event) {
                resultDiv.className = 'speech-result visible error';
                if (event.error === 'no-speech') {
                    resultDiv.innerHTML = '🔇 No speech detected. Click the microphone and try again.';
                } else if (event.error === 'not-allowed') {
                    resultDiv.innerHTML = '🔒 Microphone access denied. Please allow microphone permissions in your browser settings.';
                } else {
                    resultDiv.innerHTML = '⚠️ Recognition error: ' + event.error + '. Please try again.';
                }
            };

            recognition.onend = function() {
                micBtn.classList.remove('recording');
            };

            // Begin listening
            recognition.start();
        }

        /**
         * filterCards()
         * 
         * Shows/hides pronunciation cards based on the selected
         * category filter. Uses CSS display property for simplicity.
         *
         * @param {string} category - Category to filter by, or 'all'
         */
        function filterCards(category) {
            // Update active button state
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');

            // Show/hide cards based on category
            document.querySelectorAll('.pronunciation-card').forEach(card => {
                if (category === 'all' || card.dataset.category === category) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        /**
         * VOICE LOADING
         * 
         * Browser voices may load asynchronously. This listener
         * ensures Spanish voices are available when the page loads.
         * Without this, the first speech attempt might use a
         * non-Spanish default voice.
         */
        if (window.speechSynthesis) {
            window.speechSynthesis.onvoiceschanged = function() {
                const voices = window.speechSynthesis.getVoices();
                const spanishVoices = voices.filter(v => v.lang.startsWith('es'));
                console.log('Available Spanish voices:', spanishVoices.map(v => v.name));
            };
        }
    </script>
</body>
</html>
