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

// Create customer feedback table if not exists
$create_table = "CREATE TABLE IF NOT EXISTS customer_feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    star_rating INT NOT NULL,
    feedback_text TEXT,
    categories JSON,
    relationship_indicator VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX(star_rating),
    INDEX(created_at),
    INDEX(user_id)
)";
@$conn->query($create_table);

// Handle new rating submission
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
    
    $stmt = $conn->prepare("INSERT INTO customer_feedback (user_id, star_rating, feedback_text, categories, relationship_indicator) VALUES (?, ?, ?, ?, ?)");
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Rate Us - ByaHERO</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7f8 0%, #e8eef2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .rate-us-container {
            max-width: 600px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 15px;
            background: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            color: #333;
            font-weight: 600;
            margin-bottom: 20px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .back-btn:hover {
            background: #00b14f;
            color: white;
            transform: translateY(-2px);
        }

        .header-title {
            font-size: 2.5rem;
            color: #1a1a1a;
            margin-bottom: 10px;
        }

        .header-subtitle {
            font-size: 1rem;
            color: #666;
        }

        .form-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .success-banner {
            background: #d4edda;
            color: #155724;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            border-left: 4px solid #28a745;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: #333;
            margin-bottom: 12px;
            font-size: 1rem;
        }

        .star-rating-selector {
            display: flex;
            gap: 12px;
            font-size: 2.5rem;
        }

        .star-rating-selector input {
            display: none;
        }

        .star-rating-selector label {
            cursor: pointer;
            color: #ddd;
            transition: color 0.2s ease, transform 0.2s ease;
            user-select: none;
            font-size: 2.5rem;
            margin: 0;
        }

        .star-rating-selector label:hover {
            transform: scale(1.1);
        }

        textarea {
            width: 100%;
            min-height: 120px;
            padding: 12px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-family: inherit;
            font-size: 0.95rem;
            resize: vertical;
            transition: border 0.3s ease;
        }

        textarea:focus {
            outline: none;
            border-color: #00b14f;
            box-shadow: 0 0 0 3px rgba(0, 177, 79, 0.1);
        }

        .checkbox-group {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }

        .checkbox-item input {
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: #00b14f;
        }

        .checkbox-item label {
            cursor: pointer;
            margin: 0;
            font-weight: 500;
            color: #555;
        }

        .submit-btn {
            width: 100%;
            background: linear-gradient(135deg, #00b14f 0%, #009638 100%);
            color: white;
            border: none;
            padding: 14px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 177, 79, 0.3);
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 177, 79, 0.4);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        @media (max-width: 600px) {
            .form-card {
                padding: 20px;
            }

            .header-title {
                font-size: 2rem;
            }

            .star-rating-selector {
                font-size: 2rem;
            }

            .star-rating-selector label {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="rate-us-container">
        <div class="header">
            <a href="home.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            <h1 class="header-title">⭐ Rate Us</h1>
            <p class="header-subtitle">Share your experience with ByaHERO</p>
        </div>

        <div class="form-card">
            <?php if (!empty($success_message)): ?>
                <div class="success-banner">
                    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                </div>
                <script>
                    setTimeout(function() {
                        window.location.href = 'home.php';
                    }, 2000);
                </script>
            <?php endif; ?>

            <form method="post" id="ratingForm">
                <!-- 1️⃣ Star Rating -->
                <div class="form-group">
                    <label>1️⃣ How was your overall experience? *</label>
                    <div class="star-rating-selector" id="starRating">
                        <input type="radio" name="star_rating" value="1" id="star1">
                        <label for="star1">★</label>
                        <input type="radio" name="star_rating" value="2" id="star2">
                        <label for="star2">★</label>
                        <input type="radio" name="star_rating" value="3" id="star3">
                        <label for="star3">★</label>
                        <input type="radio" name="star_rating" value="4" id="star4">
                        <label for="star4">★</label>
                        <input type="radio" name="star_rating" value="5" id="star5">
                        <label for="star5">★</label>
                    </div>
                </div>

                <!-- 2️⃣ Short Feedback -->
                <div class="form-group">
                    <label for="feedback">2️⃣ Short Feedback (Optional)</label>
                    <textarea name="feedback" id="feedback" placeholder="Tell us what we can improve..."></textarea>
                </div>

                <!-- 3️⃣ Select Category -->
                <div class="form-group">
                    <label>3️⃣ Select Category (Optional)</label>
                    <div class="checkbox-group">
                        <div class="checkbox-item">
                            <input type="checkbox" name="categories[]" value="Driver Service" id="cat1">
                            <label for="cat1">Driver Service</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" name="categories[]" value="Vehicle Condition" id="cat2">
                            <label for="cat2">Vehicle Condition</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" name="categories[]" value="Booking Process" id="cat3">
                            <label for="cat3">Booking Process</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" name="categories[]" value="App/System Experience" id="cat4">
                            <label for="cat4">App/System Experience</label>
                        </div>
                    </div>
                </div>

                <!-- 4️⃣ Submit Button -->
                <button type="submit" name="submit_rating" class="submit-btn">
                    <i class="fas fa-paper-plane"></i> Submit Your Rating
                </button>
            </form>
        </div>
    </div>

    <script>
        // Star Rating Interactive Selector
        const starLabels = document.querySelectorAll('#starRating label');
        const starInputs = document.querySelectorAll('#starRating input[type="radio"]');

        starLabels.forEach((label, index) => {
            label.addEventListener('mouseenter', function() {
                starLabels.forEach((lbl, i) => {
                    if (i <= index) {
                        lbl.style.color = '#ffc107';
                    } else {
                        lbl.style.color = '#ddd';
                    }
                });
            });

            label.addEventListener('click', function() {
                starInputs[index].checked = true;
                updateStarColors();
            });
        });

        document.getElementById('starRating').addEventListener('mouseleave', updateStarColors);

        function updateStarColors() {
            const checkedIndex = Array.from(starInputs).findIndex(input => input.checked);
            starLabels.forEach((label, index) => {
                if (checkedIndex !== -1 && index <= checkedIndex) {
                    label.style.color = '#ffc107';
                } else {
                    label.style.color = '#ddd';
                }
            });
        }

        // Form validation
        document.getElementById('ratingForm').addEventListener('submit', function(e) {
            const selectedRating = document.querySelector('#starRating input[type="radio"]:checked');
            if (!selectedRating) {
                e.preventDefault();
                alert('Please select a star rating before submitting');
            }
        });
    </script>
</body>
</html>

<?php $conn->close(); ?>
