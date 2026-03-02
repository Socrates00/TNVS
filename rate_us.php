<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$conn = new mysqli("localhost", "root", "", "byahero_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create ratings table if not exists
$create_table = "CREATE TABLE IF NOT EXISTS customer_ratings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    star_rating INT NOT NULL,
    feedback_text TEXT,
    categories JSON,
    relationship_indicator VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX(star_rating),
    INDEX(created_at)
)";
@$conn->query($create_table);

// Handle rating submission
$success_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_rating'])) {
    $user_id = $_SESSION['user_id'] ?? NULL;
    $star_rating = intval($_POST['star_rating']);
    $feedback_text = trim($_POST['feedback'] ?? '');
    $categories = isset($_POST['categories']) ? json_encode($_POST['categories']) : NULL;
    
    // Determine relationship indicator based on star rating
    $relationship_indicator = 'neutral';
    if ($star_rating >= 4) {
        $relationship_indicator = 'positive';
    } elseif ($star_rating <= 2) {
        $relationship_indicator = 'negative';
    }
    
    $stmt = $conn->prepare("INSERT INTO customer_ratings (user_id, star_rating, feedback_text, categories, relationship_indicator) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisss", $user_id, $star_rating, $feedback_text, $categories, $relationship_indicator);
    $stmt->execute();
    $stmt->close();
    
    $success_message = "Thank you for rating ByaHERO! Your feedback helps us improve.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rate ByaHERO</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="home.css">
    <style>
        body {
            background: #f8fafb;
        }

        .rate-us-page {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .rate-container {
            flex: 1;
            max-width: 600px;
            margin: 40px auto;
            padding: 30px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            width: 100%;
        }

        .rate-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 30px;
        }

        .rate-header .icon {
            font-size: 2.5rem;
            color: #00b14f;
        }

        .rate-header h1 {
            font-size: 1.8rem;
            color: #1a1a1a;
            margin: 0;
        }

        .rate-subtitle {
            color: #666;
            font-size: 0.95rem;
            margin-bottom: 30px;
        }

        .success-banner {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            border-left: 5px solid #28a745;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideIn 0.4s ease;
        }

        .success-banner i {
            font-size: 1.5rem;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-section {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 12px;
            font-size: 0.95rem;
        }

        .label-required {
            color: #dc3545;
            margin-left: 4px;
        }

        .label-optional {
            color: #999;
            font-size: 0.8rem;
            font-weight: 400;
            margin-left: 4px;
        }

        /* Star Rating */
        .star-rating-selector {
            display: flex;
            gap: 12px;
            font-size: 2.2rem;
            margin-bottom: 10px;
        }

        .star-rating-selector input {
            display: none;
        }

        .star-rating-selector label {
            cursor: pointer;
            color: #ddd;
            transition: all 0.2s ease;
            user-select: none;
            margin: 0;
            font-size: 2.2rem;
            padding: 0;
        }

        .star-rating-selector label:hover {
            transform: scale(1.15);
        }

        .star-rating-selector input:checked ~ label,
        .star-rating-selector label:hover,
        .star-rating-selector label:hover ~ label {
            color: #ffc107;
            text-shadow: 0 0 10px rgba(255, 193, 7, 0.3);
        }

        .rating-label {
            font-size: 0.85rem;
            color: #666;
            margin-top: 8px;
            font-style: italic;
        }

        /* Text Feedback */
        .form-group textarea {
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-family: inherit;
            font-size: 0.95rem;
            resize: vertical;
            min-height: 100px;
            transition: all 0.3s ease;
        }

        .form-group textarea:focus {
            outline: none;
            border-color: #00b14f;
            box-shadow: 0 0 0 3px rgba(0, 177, 79, 0.1);
        }

        /* Checkboxes */
        .checkbox-group {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .checkbox-item:hover {
            background: #f8f9fa;
        }

        .checkbox-item input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: #00b14f;
        }

        .checkbox-item label {
            margin: 0;
            cursor: pointer;
            font-weight: 500;
            flex: 1;
        }

        /* Buttons */
        .button-group {
            display: flex;
            gap: 12px;
            margin-top: 30px;
        }

        .btn {
            flex: 1;
            padding: 14px 20px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-submit {
            background: linear-gradient(135deg, #00b14f 0%, #009638 100%);
            color: white;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 177, 79, 0.3);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .btn-back {
            background: #f0f0f0;
            color: #333;
        }

        .btn-back:hover {
            background: #e0e0e0;
        }

        .form-hint {
            font-size: 0.85rem;
            color: #999;
            line-height: 1.5;
            margin-top: 8px;
        }

        .rating-display {
            text-align: center;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 8px;
            margin-top: 10px;
        }

        .rating-display .value {
            font-size: 1.8rem;
            font-weight: 900;
            color: #00b14f;
            margin: 10px 0;
        }

        .rating-display .text {
            font-size: 0.9rem;
            color: #666;
        }

        @media (max-width: 600px) {
            .rate-container {
                margin: 20px;
                padding: 20px;
                border-radius: 12px;
            }

            .rate-header {
                margin-bottom: 20px;
            }

            .rate-header h1 {
                font-size: 1.5rem;
            }

            .button-group {
                flex-direction: column;
            }

            .star-rating-selector {
                font-size: 1.8rem;
                gap: 8px;
            }
        }
    </style>
</head>
<body>
    <?php include('includes/header.php'); ?>

    <div class="rate-us-page">
        <div class="rate-container">
            <div class="rate-header">
                <div class="icon">⭐</div>
                <h1>Rate Us</h1>
            </div>

            <p class="rate-subtitle">Help us improve by sharing your feedback. Your opinion matters!</p>

            <?php if (!empty($success_message)): ?>
                <div class="success-banner">
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <strong><?php echo $success_message; ?></strong>
                        <p style="margin: 8px 0 0 0; font-size: 0.9rem;">Redirecting in 2 seconds...</p>
                    </div>
                </div>

                <script>
                    setTimeout(function() {
                        window.location.href = 'home.php';
                    }, 2000);
                </script>
            <?php else: ?>
                <form method="post" class="form-section">
                    <!-- 1️⃣ Star Rating (Required) -->
                    <div class="form-group">
                        <label>1️⃣ How was your overall experience?
                            <span class="label-required">*</span>
                        </label>
                        <div class="star-rating-selector">
                            <input type="radio" name="star_rating" value="1" id="star1" required>
                            <label for="star1" title="Poor">★</label>

                            <input type="radio" name="star_rating" value="2" id="star2">
                            <label for="star2" title="Fair">★</label>

                            <input type="radio" name="star_rating" value="3" id="star3">
                            <label for="star3" title="Good">★</label>

                            <input type="radio" name="star_rating" value="4" id="star4">
                            <label for="star4" title="Very Good">★</label>

                            <input type="radio" name="star_rating" value="5" id="star5">
                            <label for="star5" title="Excellent">★</label>
                        </div>
                        <div class="rating-display" id="ratingDisplay" style="display: none;">
                            <div class="text">Your rating</div>
                            <div class="value" id="ratingValue">5</div>
                        </div>
                        <p class="form-hint">Click on a star to rate your experience</p>
                    </div>

                    <!-- 2️⃣ Short Feedback (Optional) -->
                    <div class="form-group">
                        <label for="feedback">2️⃣ Tell us more
                            <span class="label-optional">(Optional)</span>
                        </label>
                        <textarea name="feedback" id="feedback" placeholder="What went well? What can we improve?" style="max-height: 120px;"></textarea>
                        <p class="form-hint">Your feedback helps us serve you better</p>
                    </div>

                    <!-- 3️⃣ Categories (Optional) -->
                    <div class="form-group">
                        <label>3️⃣ What was this about?
                            <span class="label-optional">(Optional - Select all that apply)</span>
                        </label>
                        <div class="checkbox-group">
                            <div class="checkbox-item">
                                <input type="checkbox" name="categories[]" value="Driver Service" id="cat1">
                                <label for="cat1">👤 Driver Service - Quality of driver interaction</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" name="categories[]" value="Vehicle Condition" id="cat2">
                                <label for="cat2">🚗 Vehicle Condition - Cleanliness & maintenance</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" name="categories[]" value="Booking Process" id="cat3">
                                <label for="cat3">📅 Booking Process - Ease of scheduling</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" name="categories[]" value="App/System Experience" id="cat4">
                                <label for="cat4">📱 App/System - User interface & features</label>
                            </div>
                        </div>
                    </div>

                    <!-- 4️⃣ Submit & Back -->
                    <div class="button-group">
                        <button type="submit" name="submit_rating" class="btn btn-submit">
                            <i class="fas fa-paper-plane"></i> Submit Rating
                        </button>
                        <a href="home.php" class="btn btn-back" style="text-decoration: none;">
                            <i class="fas fa-arrow-left"></i> Cancel
                        </a>
                    </div>

                    <p class="form-hint" style="text-align: center; margin-top: 20px;">
                        <i class="fas fa-lock"></i> Your feedback is secure and confidential
                    </p>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <?php include('includes/footer.php'); ?>

    <script>
        // Update rating display when star is selected
        const starInputs = document.querySelectorAll('.star-rating-selector input[type="radio"]');
        const ratingDisplay = document.getElementById('ratingDisplay');
        const ratingValue = document.getElementById('ratingValue');

        starInputs.forEach(input => {
            input.addEventListener('change', function() {
                ratingValue.textContent = this.value;
                ratingDisplay.style.display = 'block';
            });
        });
    </script>
</body>
</html>

<?php $conn->close(); ?>
