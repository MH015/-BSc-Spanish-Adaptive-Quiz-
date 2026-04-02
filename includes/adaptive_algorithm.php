<?php
/**
 * ENHANCED ADAPTIVE ALGORITHM
 * QuizNinja - Adaptive Quiz Web Application
 * Author: Matthew Holness
 * Student ID: 22068679
 *
 * Multi-factor adaptive mechanism incorporating:
 * 1. Current quiz score (primary factor)
 * 2. Per-category difficulty tracking
 * 3. Historical trend analysis (last 5 attempts)
 * 4. Consistency detection (streak analysis)
 * 5. Weighted scoring combining all factors
 */

/**
 * Calculate the adaptive difficulty adjustment using multiple factors.
 *
 * @param int    $user_id        The current user's ID
 * @param string $category       The quiz category just completed
 * @param int    $percentage     The score percentage achieved
 * @param string $current_level  The difficulty level of the quiz just taken
 * @return array                 Contains new_level, factors, feedback, and per-category data
 */
function calculate_adaptive_difficulty($user_id, $category, $percentage, $current_level) {

    // Factor 1: Current Score (weight: 0.50)
    // The most recent quiz score remains the primary indicator
    $score_factor = calculate_score_factor($percentage);

    // Factor 2: Historical Trend (weight: 0.25)
    // Analyses the last 5 attempts in this category to detect improvement or decline
    $trend_factor = calculate_trend_factor($user_id, $category);

    // Factor 3: Consistency / Streak (weight: 0.15)
    // Detects sustained high or low performance across recent attempts
    $consistency_factor = calculate_consistency_factor($user_id, $category);

    // Factor 4: Category Strength (weight: 0.10)
    // Compares performance in this category against overall average
    $category_factor = calculate_category_factor($user_id, $category);

    // Weighted combination of all factors
    $weights = [
        'score'       => 0.50,
        'trend'       => 0.25,
        'consistency' => 0.15,
        'category'    => 0.10
    ];

    $composite_score = ($score_factor * $weights['score'])
                     + ($trend_factor * $weights['trend'])
                     + ($consistency_factor * $weights['consistency'])
                     + ($category_factor * $weights['category']);

    // Determine difficulty adjustment based on composite score
    // Composite ranges from -1.0 (strong decrease) to +1.0 (strong increase)
    $new_level = $current_level;
    $adjustment = 'maintain';

    if ($composite_score >= 0.40) {
        // Strong evidence for advancement
        if ($current_level === 'easy') {
            $new_level = 'medium';
            $adjustment = 'increase';
        } elseif ($current_level === 'medium') {
            $new_level = 'hard';
            $adjustment = 'increase';
        }
    } elseif ($composite_score <= -0.30) {
        // Strong evidence for support
        if ($current_level === 'hard') {
            $new_level = 'medium';
            $adjustment = 'decrease';
        } elseif ($current_level === 'medium') {
            $new_level = 'easy';
            $adjustment = 'decrease';
        }
    }

    // Update per-category difficulty in database
    update_category_difficulty($user_id, $category, $new_level);

    // Generate detailed feedback
    $feedback = generate_adaptive_feedback($adjustment, $current_level, $new_level, $composite_score, [
        'score'       => $score_factor,
        'trend'       => $trend_factor,
        'consistency' => $consistency_factor,
        'category'    => $category_factor
    ]);

    return [
        'new_level'       => $new_level,
        'adjustment'      => $adjustment,
        'composite_score' => round($composite_score, 3),
        'factors'         => [
            'score'       => round($score_factor, 3),
            'trend'       => round($trend_factor, 3),
            'consistency' => round($consistency_factor, 3),
            'category'    => round($category_factor, 3)
        ],
        'weights'         => $weights,
        'feedback_title'  => $feedback['title'],
        'feedback_message'=> $feedback['message'],
        'feedback_detail' => $feedback['detail'],
        'feedback_type'   => $adjustment
    ];
}


/**
 * Factor 1: Score Factor
 * Maps the percentage score to a value between -1.0 and +1.0
 * 85%+ = strong positive, 75-84% = moderate positive,
 * 50-74% = neutral, 35-49% = moderate negative, <35% = strong negative
 */
function calculate_score_factor($percentage) {
    if ($percentage >= 85) return 1.0;
    if ($percentage >= 75) return 0.6;
    if ($percentage >= 60) return 0.1;
    if ($percentage >= 50) return -0.1;
    if ($percentage >= 35) return -0.5;
    return -1.0;
}


/**
 * Factor 2: Historical Trend
 * Analyses the last 5 attempts in the same category to detect
 * whether the user is improving, declining, or stable.
 * Uses linear regression slope on the score sequence.
 */
function calculate_trend_factor($user_id, $category) {
    $attempts = db_query(
        "SELECT percentage FROM quiz_attempts 
         WHERE user_id = ? AND category = ? 
         ORDER BY attempt_date DESC LIMIT 5",
        [$user_id, $category]
    );

    if (count($attempts) < 2) {
        // Not enough data to determine a trend
        return 0.0;
    }

    // Reverse so oldest is first (index 0)
    $scores = array_reverse(array_column($attempts, 'percentage'));
    $n = count($scores);

    // Calculate simple linear regression slope
    $sum_x = 0; $sum_y = 0; $sum_xy = 0; $sum_x2 = 0;
    for ($i = 0; $i < $n; $i++) {
        $sum_x  += $i;
        $sum_y  += $scores[$i];
        $sum_xy += $i * $scores[$i];
        $sum_x2 += $i * $i;
    }

    $denominator = ($n * $sum_x2) - ($sum_x * $sum_x);
    if ($denominator == 0) return 0.0;

    $slope = (($n * $sum_xy) - ($sum_x * $sum_y)) / $denominator;

    // Normalise slope to -1.0 to +1.0 range
    // A slope of +10 means gaining ~10% per quiz (strong improvement)
    // A slope of -10 means losing ~10% per quiz (strong decline)
    $normalised = max(-1.0, min(1.0, $slope / 10.0));

    return $normalised;
}


/**
 * Factor 3: Consistency / Streak Detection
 * Checks the last 3 attempts for sustained patterns:
 * - 3 consecutive scores above 70% = strong positive (ready to advance)
 * - 3 consecutive scores below 50% = strong negative (needs support)
 * - Mixed results = neutral
 */
function calculate_consistency_factor($user_id, $category) {
    $attempts = db_query(
        "SELECT percentage FROM quiz_attempts 
         WHERE user_id = ? AND category = ? 
         ORDER BY attempt_date DESC LIMIT 3",
        [$user_id, $category]
    );

    if (count($attempts) < 3) {
        return 0.0;
    }

    $scores = array_column($attempts, 'percentage');
    $above_70 = 0;
    $below_50 = 0;

    foreach ($scores as $score) {
        if ($score >= 70) $above_70++;
        if ($score < 50)  $below_50++;
    }

    if ($above_70 === 3) return 0.8;   // Consistent high performer
    if ($above_70 >= 2) return 0.3;    // Mostly strong
    if ($below_50 === 3) return -0.8;  // Consistent struggle
    if ($below_50 >= 2) return -0.3;   // Mostly struggling

    return 0.0; // Mixed results
}


/**
 * Factor 4: Category Strength
 * Compares the user's average score in this category against
 * their overall average across all categories.
 * A strong category gets a slight positive push;
 * a weak category gets a slight negative pull.
 */
function calculate_category_factor($user_id, $category) {
    // Category average
    $cat_avg = db_query_single(
        "SELECT AVG(percentage) as avg_score FROM quiz_attempts 
         WHERE user_id = ? AND category = ?",
        [$user_id, $category]
    );

    // Overall average
    $overall_avg = db_query_single(
        "SELECT AVG(percentage) as avg_score FROM quiz_attempts 
         WHERE user_id = ?",
        [$user_id]
    );

    if (!$cat_avg || !$overall_avg || !$overall_avg['avg_score']) {
        return 0.0;
    }

    $difference = $cat_avg['avg_score'] - $overall_avg['avg_score'];

    // Normalise: +20% above average = +1.0, -20% below = -1.0
    $normalised = max(-1.0, min(1.0, $difference / 20.0));

    return $normalised;
}


/**
 * Update per-category difficulty tracking.
 * Stores the current difficulty level for each category independently,
 * allowing different difficulty levels across different categories.
 */
function update_category_difficulty($user_id, $category, $new_level) {
    // Check if a record exists for this user-category pair
    $existing = db_query_single(
        "SELECT id FROM category_difficulty 
         WHERE user_id = ? AND category = ?",
        [$user_id, $category]
    );

    if ($existing) {
        db_execute(
            "UPDATE category_difficulty SET difficulty_level = ?, updated_at = NOW() 
             WHERE user_id = ? AND category = ?",
            [$new_level, $user_id, $category]
        );
    } else {
        db_execute(
            "INSERT INTO category_difficulty (user_id, category, difficulty_level, updated_at) 
             VALUES (?, ?, ?, NOW())",
            [$user_id, $category, $new_level]
        );
    }
}


/**
 * Get the per-category difficulty for a user.
 * Falls back to the user's global level if no category-specific level exists.
 */
function get_category_difficulty($user_id, $category) {
    $result = db_query_single(
        "SELECT difficulty_level FROM category_difficulty 
         WHERE user_id = ? AND category = ?",
        [$user_id, $category]
    );

    if ($result) {
        return $result['difficulty_level'];
    }

    // Fall back to global user level
    return $_SESSION['current_level'] ?? 'easy';
}


/**
 * Generate human-readable feedback explaining the adaptive decision.
 * Includes both a summary and a detailed breakdown of contributing factors.
 */
function generate_adaptive_feedback($adjustment, $old_level, $new_level, $composite, $factors) {
    $title = '';
    $message = '';
    $detail = '';

    if ($adjustment === 'increase') {
        $title = 'Level Up!';
        $message = 'Your difficulty has been increased from ' . ucfirst($old_level) 
                 . ' to ' . ucfirst($new_level) . '.';
    } elseif ($adjustment === 'decrease') {
        $title = 'Adjusting Difficulty';
        $message = 'Your difficulty has been adjusted from ' . ucfirst($old_level) 
                 . ' to ' . ucfirst($new_level) . ' to help you build confidence.';
    } else {
        $title = 'Staying at ' . ucfirst($new_level);
        $message = 'You are well placed at the ' . ucfirst($new_level) . ' level. Keep practising!';
    }

    // Build factor breakdown
    $detail = 'Decision factors: ';
    $parts = [];

    if ($factors['score'] >= 0.5) $parts[] = 'strong quiz score';
    elseif ($factors['score'] <= -0.5) $parts[] = 'quiz score needs improvement';

    if ($factors['trend'] >= 0.3) $parts[] = 'improving trend';
    elseif ($factors['trend'] <= -0.3) $parts[] = 'declining trend';

    if ($factors['consistency'] >= 0.3) $parts[] = 'consistent performance';
    elseif ($factors['consistency'] <= -0.3) $parts[] = 'inconsistent results';

    if ($factors['category'] >= 0.3) $parts[] = 'strong in this category';
    elseif ($factors['category'] <= -0.3) $parts[] = 'weaker category';

    if (empty($parts)) $parts[] = 'balanced performance across factors';

    $detail .= implode(', ', $parts) . '. (Composite score: ' . round($composite, 2) . ')';

    return [
        'title'   => $title,
        'message' => $message,
        'detail'  => $detail
    ];
}
?>
