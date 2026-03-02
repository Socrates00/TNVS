<?php
include('session_check.php');
$conn = new mysqli("localhost", "root", "", "byahero_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle add rating
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_rating'])) {
    $service_provider = $_POST['service_provider'];
    $work_quality = $_POST['work_quality'];
    $timeliness = $_POST['timeliness'];
    $price_value = $_POST['price_value'];
    $overall_rating = ($work_quality + $timeliness + $price_value) / 3;
    $comments = $_POST['comments'];
    
    $stmt = $conn->prepare("INSERT INTO service_ratings (service_provider, work_quality, timeliness, price_value, overall_rating, comments) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("siiiis", $service_provider, $work_quality, $timeliness, $price_value, $overall_rating, $comments);
    $stmt->execute();
    $stmt->close();
    header("Location: ratings.php");
    exit();
}

// Fetch ratings
$ratings = $conn->query("SELECT * FROM service_ratings ORDER BY created_at DESC LIMIT 50");

// Calculate average ratings
$avg_ratings = $conn->query("SELECT 
                            AVG(overall_rating) as avg_overall,
                            AVG(work_quality) as avg_quality,
                            AVG(timeliness) as avg_timeliness,
                            AVG(price_value) as avg_price
                            FROM service_ratings");
$avg_data = $avg_ratings->fetch_assoc();
?>

<?php include('includes/header.php'); ?>

<link rel="stylesheet" href="dashboard.css">

<style>
    .ratings-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    .ratings-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 2px solid #f0f0f0;
    }

    .ratings-header h1 {
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

    .average-ratings {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .rating-card {
        background: white;
        border: 1px solid #f0f0f0;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        text-align: center;
    }

    .rating-card h3 {
        font-size: 0.9rem;
        font-weight: 600;
        color: #999;
        text-transform: uppercase;
        margin: 0 0 15px 0;
        letter-spacing: 0.5px;
    }

    .rating-value {
        font-size: 2.5rem;
        font-weight: 700;
        color: #00b14f;
        margin: 0 0 10px 0;
    }

    .stars {
        color: #ffc107;
        font-size: 1.2rem;
        margin: 0;
    }

    .add-form-section {
        background: white;
        border: 1px solid #f0f0f0;
        border-radius: 12px;
        padding: 25px;
        margin-bottom: 30px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }

    .form-title {
        font-size: 1.2rem;
        font-weight: 600;
        color: #1a1a1a;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 15px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-group label {
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
        font-size: 0.9rem;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        padding: 12px;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        font-size: 0.95rem;
        font-family: inherit;
        transition: border 0.3s ease;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #00b14f;
        box-shadow: 0 0 0 3px rgba(0, 177, 79, 0.1);
    }

    .form-group textarea {
        resize: vertical;
        min-height: 100px;
    }

    .rating-input {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .rating-input input[type="range"] {
        flex: 1;
    }

    .rating-input .value {
        min-width: 40px;
        font-weight: 600;
        color: #00b14f;
    }

    .form-actions {
        display: flex;
        gap: 10px;
        margin-top: 20px;
    }

    .btn-submit {
        background: #00b14f;
        color: white;
        border: none;
        padding: 12px 25px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-submit:hover {
        background: #009440;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 177, 79, 0.3);
    }

    .btn-reset {
        background: #f0f0f0;
        color: #333;
        border: none;
        padding: 12px 25px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-reset:hover {
        background: #e0e0e0;
    }

    .records-section {
        background: white;
        border: 1px solid #f0f0f0;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }

    .records-header {
        padding: 20px 25px;
        border-bottom: 2px solid #f0f0f0;
        background: #f8fafb;
    }

    .records-header h2 {
        margin: 0;
        font-size: 1.2rem;
        color: #1a1a1a;
    }

    .ratings-list {
        display: flex;
        flex-direction: column;
    }

    .rating-item {
        padding: 20px 25px;
        border-bottom: 1px solid #f0f0f0;
        transition: background 0.3s ease;
    }

    .rating-item:hover {
        background: #f8fafb;
    }

    .rating-item:last-child {
        border-bottom: none;
    }

    .provider-name {
        font-size: 1.1rem;
        font-weight: 600;
        color: #1a1a1a;
        margin: 0 0 10px 0;
    }

    .rating-scores {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 15px;
        margin: 15px 0;
    }

    .score-item {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .score-label {
        font-size: 0.9rem;
        color: #666;
        min-width: 100px;
    }

    .score-value {
        font-weight: 600;
        color: #00b14f;
        min-width: 30px;
    }

    .score-bar {
        height: 8px;
        background: #f0f0f0;
        border-radius: 4px;
        overflow: hidden;
        flex: 1;
    }

    .score-fill {
        height: 100%;
        background: linear-gradient(90deg, #00b14f, #009440);
        border-radius: 4px;
    }

    .overall-rating {
        font-size: 1.3rem;
        font-weight: 700;
        color: #00b14f;
        margin: 10px 0;
    }

    .rating-comments {
        color: #666;
        font-size: 0.95rem;
        margin-top: 10px;
        padding: 10px;
        background: #f8fafb;
        border-left: 3px solid #00b14f;
        border-radius: 4px;
    }

    .rating-date {
        font-size: 0.85rem;
        color: #999;
        margin-top: 10px;
    }

    .no-records {
        padding: 40px;
        text-align: center;
        color: #999;
    }

    .no-records p {
        margin: 0;
        font-size: 1.1rem;
    }
</style>

<main class="ratings-container">
    <div class="ratings-header">
        <h1>⭐ Service Ratings</h1>
        <a href="admin.php" class="back-button">← Back to Dashboard</a>
    </div>

    <?php if ($avg_data['avg_overall'] !== null): ?>
    <div class="average-ratings">
        <div class="rating-card">
            <h3>Overall Rating</h3>
            <p class="rating-value"><?php echo number_format($avg_data['avg_overall'], 1); ?></p>
            <p class="stars"><?php echo str_repeat('★', round($avg_data['avg_overall'])); ?></p>
        </div>
        <div class="rating-card">
            <h3>Work Quality</h3>
            <p class="rating-value"><?php echo number_format($avg_data['avg_quality'], 1); ?></p>
            <p class="stars"><?php echo str_repeat('★', round($avg_data['avg_quality'])); ?></p>
        </div>
        <div class="rating-card">
            <h3>Timeliness</h3>
            <p class="rating-value"><?php echo number_format($avg_data['avg_timeliness'], 1); ?></p>
            <p class="stars"><?php echo str_repeat('★', round($avg_data['avg_timeliness'])); ?></p>
        </div>
        <div class="rating-card">
            <h3>Price & Value</h3>
            <p class="rating-value"><?php echo number_format($avg_data['avg_price'], 1); ?></p>
            <p class="stars"><?php echo str_repeat('★', round($avg_data['avg_price'])); ?></p>
        </div>
    </div>
    <?php endif; ?>

    <div class="add-form-section">
        <div class="form-title">⭐ Add Service Provider Rating</div>
        <form method="post" class="ratings-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="service_provider">Service Provider Name *</label>
                    <input type="text" id="service_provider" name="service_provider" placeholder="e.g., ABC Auto Shop" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="work_quality">Work Quality (1-5) *</label>
                    <div class="rating-input">
                        <input type="range" id="work_quality" name="work_quality" min="1" max="5" value="3" style="margin: 0;">
                        <span class="value" id="quality-value">3</span>
                    </div>
                </div>
                <div class="form-group">
                    <label for="timeliness">Timeliness (1-5) *</label>
                    <div class="rating-input">
                        <input type="range" id="timeliness" name="timeliness" min="1" max="5" value="3" style="margin: 0;">
                        <span class="value" id="timeliness-value">3</span>
                    </div>
                </div>
                <div class="form-group">
                    <label for="price_value">Price & Value (1-5) *</label>
                    <div class="rating-input">
                        <input type="range" id="price_value" name="price_value" min="1" max="5" value="3" style="margin: 0;">
                        <span class="value" id="price-value">3</span>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="comments">Comments</label>
                    <textarea id="comments" name="comments" placeholder="Share your experience with this service provider..."></textarea>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" name="add_rating" class="btn-submit">✓ Submit Rating</button>
                <button type="reset" class="btn-reset">Clear Form</button>
            </div>
        </form>
    </div>

    <div class="records-section">
        <div class="records-header">
            <h2>Rating History</h2>
        </div>
        <div class="ratings-list">
            <?php 
            if ($ratings && $ratings->num_rows > 0):
                while ($record = $ratings->fetch_assoc()): 
            ?>
            <div class="rating-item">
                <h3 class="provider-name"><?php echo htmlspecialchars($record['service_provider']); ?></h3>
                
                <div class="overall-rating">
                    Overall: <?php echo number_format($record['overall_rating'], 1); ?>/5 <?php echo str_repeat('★', round($record['overall_rating'])); ?>
                </div>

                <div class="rating-scores">
                    <div class="score-item">
                        <span class="score-label">Quality:</span>
                        <span class="score-value"><?php echo $record['work_quality']; ?>/5</span>
                        <div class="score-bar">
                            <div class="score-fill" style="width: <?php echo ($record['work_quality'] / 5) * 100; ?>%;"></div>
                        </div>
                    </div>
                    <div class="score-item">
                        <span class="score-label">Timeliness:</span>
                        <span class="score-value"><?php echo $record['timeliness']; ?>/5</span>
                        <div class="score-bar">
                            <div class="score-fill" style="width: <?php echo ($record['timeliness'] / 5) * 100; ?>%;"></div>
                        </div>
                    </div>
                    <div class="score-item">
                        <span class="score-label">Price:</span>
                        <span class="score-value"><?php echo $record['price_value']; ?>/5</span>
                        <div class="score-bar">
                            <div class="score-fill" style="width: <?php echo ($record['price_value'] / 5) * 100; ?>%;"></div>
                        </div>
                    </div>
                </div>

                <?php if (!empty($record['comments'])): ?>
                <div class="rating-comments">
                    <strong>Comments:</strong> <?php echo htmlspecialchars($record['comments']); ?>
                </div>
                <?php endif; ?>

                <div class="rating-date">
                    <?php echo date('M d, Y H:i', strtotime($record['created_at'])); ?>
                </div>
            </div>
            <?php 
                endwhile;
            else:
            ?>
            <div class="no-records">
                <p>No ratings yet. Add one to get started!</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<script>
document.getElementById('work_quality').addEventListener('input', function() {
    document.getElementById('quality-value').textContent = this.value;
});

document.getElementById('timeliness').addEventListener('input', function() {
    document.getElementById('timeliness-value').textContent = this.value;
});

document.getElementById('price_value').addEventListener('input', function() {
    document.getElementById('price-value').textContent = this.value;
});
</script>
<?php $conn->close(); ?>
