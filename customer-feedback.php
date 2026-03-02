<?php
include('session_check.php');
$conn = new mysqli("localhost", "root", "", "byahero_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is admin
$admin_user = $_SESSION['username'] === 'admin';
if (!$admin_user) {
    header('Location: home.php');
    exit();
}

// Handle delete feedback
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_action'])) {
    $action = $_POST['admin_action'];
    $feedback_id = intval($_POST['feedback_id']);
    
    if ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM customer_feedback WHERE id = ?");
        $stmt->bind_param("i", $feedback_id);
        $stmt->execute();
        $stmt->close();
        header("Location: customer-feedback.php");
        exit();
    }
}

// Fetch all feedback
$all_feedback = $conn->query("SELECT * FROM customer_feedback ORDER BY created_at DESC");
$feedbacks = [];
if ($all_feedback) {
    while ($row = $all_feedback->fetch_assoc()) {
        $feedbacks[] = $row;
    }
}

// Calculate metrics
$total_feedback = count($feedbacks);
$overall_rating = 0;
$star_distribution = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
$relationship_counts = ['positive' => 0, 'neutral' => 0, 'negative' => 0];
$category_counts = [];

if ($total_feedback > 0) {
    $sum_stars = 0;
    foreach ($feedbacks as $feedback) {
        $sum_stars += $feedback['star_rating'];
        $star_distribution[$feedback['star_rating']]++;
        
        if (isset($feedback['relationship_indicator'])) {
            $relationship_counts[$feedback['relationship_indicator']]++;
        }
        
        if ($feedback['categories']) {
            $cats = json_decode($feedback['categories'], true);
            if (is_array($cats)) {
                foreach ($cats as $cat) {
                    $category_counts[$cat] = ($category_counts[$cat] ?? 0) + 1;
                }
            }
        }
    }
    $overall_rating = round($sum_stars / $total_feedback, 2);
}

// Calculate percentages
$positive_percent = $total_feedback > 0 ? round(($relationship_counts['positive'] / $total_feedback) * 100) : 0;
$neutral_percent = $total_feedback > 0 ? round(($relationship_counts['neutral'] / $total_feedback) * 100) : 0;
$negative_percent = $total_feedback > 0 ? round(($relationship_counts['negative'] / $total_feedback) * 100) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Feedback - ByaHERO Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="dashboard.css">
    <style>
        .feedback-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .feedback-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }

        .feedback-header h1 {
            font-size: 2rem;
            color: #1a1a1a;
            margin: 0;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 15px;
            background: #f0f0f0;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .back-button:hover {
            background: #00b14f;
            color: white;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border: 1px solid #f0f0f0;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            text-align: center;
        }

        .stat-card .label {
            font-size: 0.85rem;
            color: #999;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .stat-card .value {
            font-size: 2.5rem;
            font-weight: 900;
            color: #00b14f;
        }

        .stat-card .subtext {
            font-size: 0.9rem;
            color: #666;
            margin-top: 8px;
        }

        /* Overall Rating */
        .overall-section {
            background: linear-gradient(135deg, #00b14f 0%, #009638 100%);
            color: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 177, 79, 0.2);
        }

        .overall-section h2 {
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0 0 15px 0;
            opacity: 0.95;
        }

        .rating-stars {
            font-size: 2.5rem;
            margin: 15px 0;
            letter-spacing: 4px;
        }

        .rating-number {
            font-size: 2rem;
            font-weight: 900;
            margin: 10px 0;
        }

        /* Relationship Indicators */
        .relationship-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .relationship-card {
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            border: 2px solid #f0f0f0;
        }

        .relationship-card.positive {
            background: rgba(40, 167, 69, 0.1);
            border-color: #28a745;
        }

        .relationship-card.neutral {
            background: rgba(255, 193, 7, 0.1);
            border-color: #ffc107;
        }

        .relationship-card.negative {
            background: rgba(220, 53, 69, 0.1);
            border-color: #dc3545;
        }

        .relationship-card .icon {
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .relationship-card.positive .icon,
        .relationship-card.positive .percent {
            color: #28a745;
        }

        .relationship-card.neutral .icon,
        .relationship-card.neutral .percent {
            color: #ffc107;
        }

        .relationship-card.negative .icon,
        .relationship-card.negative .percent {
            color: #dc3545;
        }

        .relationship-card .percent {
            font-size: 1.8rem;
            font-weight: 900;
            margin: 10px 0;
        }

        .relationship-card .label {
            font-weight: 600;
            color: #1a1a1a;
            font-size: 0.9rem;
        }

        /* Feedback List */
        .feedback-section {
            background: white;
            border: 1px solid #f0f0f0;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }

        .feedback-section h3 {
            font-size: 1.2rem;
            margin: 0 0 20px 0;
            color: #1a1a1a;
        }

        .feedback-item {
            border-bottom: 1px solid #f0f0f0;
            padding: 20px 0;
        }

        .feedback-item:last-child {
            border-bottom: none;
        }

        .feedback-header-row {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 10px;
        }

        .feedback-rating {
            font-size: 1rem;
            color: #ffc107;
            letter-spacing: 1px;
        }

        .feedback-date {
            font-size: 0.85rem;
            color: #999;
        }

        .feedback-text {
            color: #555;
            line-height: 1.6;
            margin: 10px 0;
            padding: 10px;
            background: #f9f9f9;
            border-left: 3px solid #00b14f;
            border-radius: 4px;
            font-style: italic;
        }

        .category-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin: 10px 0;
        }

        .category-tag {
            background: #e8f5e9;
            color: #00b14f;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .admin-actions {
            display: flex;
            gap: 8px;
            margin-top: 10px;
        }

        .btn-delete {
            background: #dc3545;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.8rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-delete:hover {
            background: #c82333;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }

        .empty-state i {
            font-size: 3rem;
            color: #ddd;
            margin-bottom: 15px;
        }

        @media (max-width: 768px) {
            .feedback-header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .relationship-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include('includes/header.php'); ?>

    <div class="feedback-container">
        <div class="feedback-header">
            <h1>📣 Customer Feedback</h1>
            <a href="admin.php" class="back-button">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <!-- Stats Overview -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="label">Total Feedback</div>
                <div class="value"><?php echo $total_feedback; ?></div>
                <div class="subtext">submissions received</div>
            </div>
            <div class="stat-card">
                <div class="label">Average Rating</div>
                <div class="value" style="color: #ffc107;"><?php echo number_format($overall_rating, 1); ?>/5</div>
                <div class="subtext">customer satisfaction</div>
            </div>
            <div class="stat-card">
                <div class="label">Positive Feedback</div>
                <div class="value" style="color: #28a745;"><?php echo $positive_percent; ?>%</div>
                <div class="subtext"><?php echo $relationship_counts['positive']; ?> customers happy</div>
            </div>
            <div class="stat-card">
                <div class="label">Needs Work</div>
                <div class="value" style="color: #dc3545;"><?php echo $negative_percent; ?>%</div>
                <div class="subtext"><?php echo $relationship_counts['negative']; ?> concerns</div>
            </div>
        </div>

        <!-- Overall Rating -->
        <?php if ($total_feedback > 0): ?>
            <div class="overall-section">
                <h2>Overall Customer Satisfaction</h2>
                <div class="rating-stars">
                    <?php for ($i = 1; $i <= 5; $i++) {
                        echo ($i <= round($overall_rating)) ? '★' : '☆';
                    } ?>
                </div>
                <div class="rating-number"><?php echo number_format($overall_rating, 1); ?>/5.0</div>
                <div>Based on <?php echo $total_feedback; ?> customer rating<?php echo $total_feedback !== 1 ? 's' : ''; ?></div>
            </div>

            <!-- Relationship Breakdown -->
            <h3 style="margin-bottom: 15px; color: #1a1a1a;">Customer Sentiment Breakdown</h3>
            <div class="relationship-grid">
                <div class="relationship-card positive">
                    <div class="icon"><i class="fas fa-smile"></i></div>
                    <div class="label">Positive</div>
                    <div class="percent"><?php echo $positive_percent; ?>%</div>
                    <small><?php echo $relationship_counts['positive']; ?> customers</small>
                </div>
                <div class="relationship-card neutral">
                    <div class="icon"><i class="fas fa-meh"></i></div>
                    <div class="label">Neutral</div>
                    <div class="percent"><?php echo $neutral_percent; ?>%</div>
                    <small><?php echo $relationship_counts['neutral']; ?> customers</small>
                </div>
                <div class="relationship-card negative">
                    <div class="icon"><i class="fas fa-frown"></i></div>
                    <div class="label">Negative</div>
                    <div class="percent"><?php echo $negative_percent; ?>%</div>
                    <small><?php echo $relationship_counts['negative']; ?> customers</small>
                </div>
            </div>
        <?php endif; ?>

        <!-- Feedback List -->
        <div class="feedback-section" style="margin-top: 30px;">
            <h3>Recent Customer Feedback</h3>
            <?php if (empty($feedbacks)): ?>
                <div class="empty-state">
                    <i class="fas fa-comments"></i>
                    <p>No customer feedback yet. Customers can rate using the "Rate Us" button on their home page.</p>
                </div>
            <?php else: ?>
                <?php foreach ($feedbacks as $feedback): ?>
                    <div class="feedback-item">
                        <div class="feedback-header-row">
                            <div class="feedback-rating">
                                <?php for ($i = 1; $i <= 5; $i++) {
                                    echo ($i <= $feedback['star_rating']) ? '★' : '☆';
                                } ?>
                            </div>
                            <div class="feedback-date">
                                <?php echo date('M d, Y h:i A', strtotime($feedback['created_at'])); ?>
                            </div>
                        </div>

                        <?php if (!empty($feedback['feedback_text'])): ?>
                            <div class="feedback-text">
                                "<?php echo htmlspecialchars($feedback['feedback_text']); ?>"
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($feedback['categories'])): ?>
                            <div class="category-tags">
                                <?php
                                    $cats = json_decode($feedback['categories'], true);
                                    if (is_array($cats)) {
                                        foreach ($cats as $cat) {
                                            echo '<span class="category-tag">' . htmlspecialchars($cat) . '</span>';
                                        }
                                    }
                                ?>
                            </div>
                        <?php endif; ?>

                        <div class="admin-actions">
                            <form method="post" style="display: inline;" onsubmit="return confirm('Delete this feedback?');">
                                <input type="hidden" name="admin_action" value="delete">
                                <input type="hidden" name="feedback_id" value="<?php echo $feedback['id']; ?>">
                                <button type="submit" class="btn-delete">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php include('includes/footer.php'); ?>
</body>
</html>

<?php $conn->close(); ?>
