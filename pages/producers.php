<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Producers</title>
    <link rel="stylesheet" href="../styles/producers.css">
    <link rel="stylesheet" href="../styles/navbar.css">
</head>
<body>
  <?php include('../includes/navbar.php'); ?>
  <script src="../scripts/settings.js"></script>

<section class="producers-section">
    <h1>Our Producers</h1>
    <p>Meet the local farmers, artisans, and producers who make GLH possible. Each one is committed to sustainable, ethical practices and delivering the highest quality products.</p>

    <!-- Producer Card -->
    <div class="producer-card">
        <div class="producer-info">
            <h2>Green Valley Farm</h2>
            <p class="producer-meta">
                <span>📍 Millbrook, 5 miles from GLH</span> | 
                <span>📅 Est. 1987</span>
            </p>
            <p>Family-run organic farm specializing in seasonal vegetables and free-range eggs. We practice regenerative agriculture and never use synthetic pesticides.</p>
            <div class="methods">
                <span>Organic</span>
                <span>Regenerative Agriculture</span>
                <span>No Pesticides</span>
            </div>
            <div class="buttons">
                <button class="btn-primary" onclick="window.location.href='products.php'" >View Products</button>
            </div>
        </div>
    </div>

    <!-- Why Work With Local Producers -->
    <div class="benefits">
        <h2>Why Work With Local Producers?</h2>
        <div class="benefit-grid">
            <div class="benefit">
                <h3>Transparency</h3>
                <p>Know exactly where your food comes from and how it's produced. Visit our producers and see their methods firsthand.</p>
            </div>
            <div class="benefit">
                <h3>Freshness</h3>
                <p>Products travel just a few miles from farm to shop, ensuring maximum freshness and flavor.</p>
            </div>
            <div class="benefit">
                <h3>Sustainability</h3>
                <p>Reduced food miles mean lower carbon emissions. Our producers use sustainable and regenerative practices.</p>
            </div>
            <div class="benefit">
                <h3>Community</h3>
                <p>Support local families and businesses. Your purchases help keep our local food economy thriving.</p>
            </div>
        </div>
    </div>

</section>

</body>
</html>