<?php
// Debug version of index.php with error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Debug Index Page</h2>";

try {
    $pageTitle = 'Home';
    echo "<p>✓ Page title set</p>";
    
    require_once __DIR__ . '/config/database.php';
    echo "<p>✓ Database config loaded</p>";
    
    require_once __DIR__ . '/includes/auth.php';
    echo "<p>✓ Auth loaded</p>";
    
    $currentUser = getCurrentUser();
    echo "<p>✓ Current user retrieved: " . ($currentUser ? $currentUser['username'] : 'null') . "</p>";
    
    if ($currentUser === null) {
        echo "<p>⚠ User not logged in - showing landing page</p>";
        $base = rtrim(WEB_BASE, '/');
        require_once __DIR__ . '/includes/header.php';
        echo "<p>✓ Header loaded</p>";
        // Show landing page content
        ?>
        <div class="row mb-4">
            <div class="col text-center">
                <h1 class="h2">Gradline: Senior High School Clearance</h1>
                <p class="text-muted">Digitize and streamline the clearance process. Choose how you want to sign in.</p>
            </div>
        </div>
        <div class="row g-4 justify-content-center py-4">
            <div class="col-md-6 col-lg-4">
                <a href="<?php echo $base; ?>/public/login.php?as=student" class="text-decoration-none">
                    <div class="card h-100 border-primary shadow-sm hover-lift">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-person-video3 text-primary" style="font-size: 3rem;"></i>
                            <h5 class="card-title mt-3">Student</h5>
                            <p class="card-text text-muted small">View your clearance status</p>
                            <span class="btn btn-primary mt-2">Log in as Student</span>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-6 col-lg-4">
                <a href="<?php echo $base; ?>/public/login.php?as=teacher" class="text-decoration-none">
                    <div class="card h-100 border-success shadow-sm hover-lift">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-person-badge text-success" style="font-size: 3rem;"></i>
                            <h5 class="card-title mt-3">Teacher</h5>
                            <p class="card-text text-muted small">Review and approve clearances</p>
                            <span class="btn btn-success mt-2">Log in as Teacher</span>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-6 col-lg-4">
                <a href="<?php echo $base; ?>/public/login.php?as=admin" class="text-decoration-none">
                    <div class="card h-100 border-secondary shadow-sm hover-lift">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-gear-wide-connected text-secondary" style="font-size: 3rem;"></i>
                            <h5 class="card-title mt-3">Admin</h5>
                            <p class="card-text text-muted small">Manage data and user accounts</p>
                            <span class="btn btn-secondary mt-2">Log in as Admin</span>
                        </div>
                    </div>
                </a>
            </div>
        </div>
        <?php
        require_once __DIR__ . '/includes/footer.php';
        echo "<p>✓ Footer loaded</p>";
    } else {
        echo "<p>✓ User logged in - role: " . $currentUser['role'] . "</p>";
        
        $pdo = getDB();
        echo "<p>✓ Database connection for dashboard</p>";
        
        // Test admin counts if applicable
        if ($currentUser['role'] === 'admin') {
            $tables = ['school_year' => 'School Years', 'departments' => 'Departments'];
            foreach (array_keys($tables) as $t) {
                try {
                    $stmt = $pdo->query("SELECT COUNT(*) FROM $t");
                    $count = (int) $stmt->fetchColumn();
                    echo "<p>✓ Table $t count: $count</p>";
                } catch (PDOException $e) {
                    echo "<p>✗ Error counting $t: " . $e->getMessage() . "</p>";
                }
            }
        }
        
        require_once __DIR__ . '/includes/header.php';
        echo "<p>✓ Header loaded for logged in user</p>";
        require_once __DIR__ . '/includes/footer.php';
        echo "<p>✓ Footer loaded for logged in user</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
    echo "<p>Stack trace: " . $e->getTraceAsString() . "</p>";
}
?>
