<?php
include('session_check.php');
$conn = new mysqli("localhost", "root", "", "byahero_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle add service provider
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_provider'])) {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $specialties = $_POST['specialties'];
    $average_cost = $_POST['average_cost'];
    $contact_person = $_POST['contact_person'];
    $notes = $_POST['notes'];
    
    $stmt = $conn->prepare("INSERT INTO service_providers (name, phone, email, address, city, specialties, average_cost, contact_person, notes) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssss", $name, $phone, $email, $address, $city, $specialties, $average_cost, $contact_person, $notes);
    
    if ($stmt->execute()) {
        header("Location: service_providers.php?success=1");
        exit();
    }
    $stmt->close();
}

// Handle delete provider
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_provider'])) {
    $id = $_POST['provider_id'];
    $stmt = $conn->prepare("DELETE FROM service_providers WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: service_providers.php");
    exit();
}

// Fetch all providers
$providers = $conn->query("SELECT * FROM service_providers WHERE active = 1 ORDER BY name");

// Get provider count
$provider_count = $providers->num_rows;

// Get average rating
$avg_rating = $conn->query("SELECT AVG(rating) as avg FROM service_providers WHERE active = 1")->fetch_assoc()['avg'];
?>

<?php include('includes/header.php'); ?>

<link rel="stylesheet" href="dashboard.css">

<style>
    .providers-container { max-width: 1200px; margin: 20px auto; }
    .content-card { background: white; border-radius: 8px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 20px; }
    .stats-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
    .provider-table { width: 100%; border-collapse: collapse; }
    .provider-table th { background: #f8f9fa; padding: 12px; text-align: left; font-weight: bold; border-bottom: 2px solid #ddd; }
    .provider-table td { padding: 12px; border-bottom: 1px solid #eee; }
    .provider-table tr:hover { background: #f9f9f9; }
    .spec-tag { display: inline-block; background: #e3f2fd; color: #1976d2; padding: 4px 10px; border-radius: 4px; margin-right: 5px; font-size: 12px; }
    .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin-bottom: 20px; }
    .form-group { display: flex; flex-direction: column; }
    .form-group label { margin-bottom: 5px; font-weight: bold; }
    .form-group input, .form-group textarea { padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
    .action-btns { display: flex; gap: 5px; }
    .btn-sm { padding: 4px 8px; font-size: 11px; border: none; border-radius: 3px; cursor: pointer; }
    .btn-delete { background: #dc3545; color: white; }
    .btn-delete:hover { background: #c82333; }
    .rating-badge { color: #ffc107; font-size: 18px; }
</style>

<main class="providers-container">
    <div class="header-flex">
        <h1 class="dashboard-title">Service Providers Management</h1>
        <button type="button" class="btn-add" onclick="toggleAddForm()">+ Add Provider</button>
    </div>

    <!-- Stats -->
    <div class="stats-row">
        <div class="stat-card">
            <span class="stat-label">Total Providers</span>
            <span class="stat-value"><?php echo $provider_count; ?></span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Average Rating</span>
            <span class="stat-value"><span class="rating-badge">★</span><?php echo number_format($avg_rating ?? 0, 2); ?>/5.00</span>
        </div>
    </div>

    <!-- Add Provider Form -->
    <div id="add-provider-form" class="content-card" style="display: none;">
        <h3>Add New Service Provider</h3>
        <form method="post">
            <div class="form-grid">
                <div class="form-group">
                    <label for="name">Provider Name *</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="text" id="phone" name="phone">
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email">
                </div>
                <div class="form-group">
                    <label for="address">Address</label>
                    <input type="text" id="address" name="address">
                </div>
                <div class="form-group">
                    <label for="city">City</label>
                    <input type="text" id="city" name="city">
                </div>
                <div class="form-group">
                    <label for="specialties">Specialties (comma-separated)</label>
                    <input type="text" id="specialties" name="specialties" placeholder="e.g., Engine, Brakes, Tires">
                </div>
                <div class="form-group">
                    <label for="average_cost">Average Cost</label>
                    <input type="number" id="average_cost" name="average_cost" step="0.01">
                </div>
                <div class="form-group">
                    <label for="contact_person">Contact Person</label>
                    <input type="text" id="contact_person" name="contact_person">
                </div>
            </div>
            <div class="form-group">
                <label for="notes">Notes</label>
                <textarea id="notes" name="notes" rows="3"></textarea>
            </div>
            <div style="display: flex; gap: 10px;">
                <button type="submit" name="add_provider" class="btn-add">Add Provider</button>
                <button type="button" onclick="toggleAddForm()" class="btn-remove">Cancel</button>
            </div>
        </form>
    </div>

    <!-- Providers Table -->
    <div class="content-card">
        <h3>Service Providers</h3>
        <?php if ($providers->num_rows > 0): ?>
        <table class="provider-table">
            <thead>
                <tr>
                    <th>PROVIDER NAME</th>
                    <th>PHONE</th>
                    <th>EMAIL</th>
                    <th>CITY</th>
                    <th>SPECIALTIES</th>
                    <th>AVG COST</th>
                    <th>RATING</th>
                    <th>ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($provider = $providers->fetch_assoc()): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($provider['name']); ?></strong></td>
                    <td><?php echo htmlspecialchars($provider['phone']); ?></td>
                    <td><?php echo htmlspecialchars($provider['email']); ?></td>
                    <td><?php echo htmlspecialchars($provider['city']); ?></td>
                    <td>
                        <?php 
                        $specs = explode(',', $provider['specialties']);
                        foreach ($specs as $spec) {
                            echo '<span class="spec-tag">' . htmlspecialchars(trim($spec)) . '</span>';
                        }
                        ?>
                    </td>
                    <td>₱<?php echo number_format($provider['average_cost'], 2); ?></td>
                    <td><span class="rating-badge">★</span><?php echo number_format($provider['rating'], 2); ?></td>
                    <td>
                        <form method="post" style="display: inline;">
                            <input type="hidden" name="provider_id" value="<?php echo $provider['id']; ?>">
                            <button type="submit" name="delete_provider" class="btn-sm btn-delete" onclick="return confirm('Delete this provider?')">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p style="color: #666; text-align: center; padding: 20px;">No service providers added yet.</p>
        <?php endif; ?>
    </div>
</main>

<script>
function toggleAddForm() {
    var form = document.getElementById('add-provider-form');
    form.style.display = form.style.display === 'none' || form.style.display === '' ? 'block' : 'none';
}
</script>

<?php $conn->close(); ?>
