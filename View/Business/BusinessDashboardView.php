<!DOCTYPE html>

<head>
    <title>Dashboard</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="public\css\Styles.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>


<body class="d-flex flex-column min-vh-100">
    <!-- Header -->
    <header class="py-3 ps-4 pe-5 border-bottom">
        <div class="container-fluid">
            <div class="d-flex flex-wrap align-items-center justify-content-center">
                <img class="pt-1 px-3" src="Public\Images\scholarly logo.png" alt="Scholarly Logo" height="40" width="auto">
                <ul class="nav col-12 col-lg-auto me-lg-auto justify-content-center mb-md-0">
                    <li><a href="?controller=business&action=dashboard" class="nav-link px-2 link-secondary">Dashboard</a></li>
                    <li><a href="?controller=business&action=businessManager" class="nav-link px-2 link-body-emphasis">Business Management</a></li>
                </ul>

                <!-- Messages and Reviews Section -->
                <ul class="nav col-lg-auto justify-content-center">
                    <li>
                        <a href="#" class="nav-link link-body-emphasis">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512" width="22" height="22" fill="currentColor"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                                <path d="M160 32a104 104 0 1 1 0 208 104 104 0 1 1 0-208zm320 0a104 104 0 1 1 0 208 104 104 0 1 1 0-208zM0 416c0-70.7 57.3-128 128-128l64 0c70.7 0 128 57.3 128 128l0 16c0 26.5-21.5 48-48 48L48 480c-26.5 0-48-21.5-48-48l0-16zm448 64c-38.3 0-72.7-16.8-96.1-43.5c.1-1.5 .1-3 .1-4.5l0-16c0-34.9-11.2-67.1-30.1-93.4c5.8-20 24.2-34.6 46.1-34.6l224 0c26.5 0 48 21.5 48 48l0 16c0 70.7-57.3 128-128 128l-64 0z" />
                            </svg>
                        </a>
                    </li>
                </ul>

                <!-- Profile and Dropdown Section -->
                <div class="dropdown text-end">
                    <a href="#" class="d-block link-body-emphasis text-decoration-none dropdown-toggle p-2 ms-1" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="Public\Images\default_pfp_128.png" class="border" height="34" width="34" alt="pfp" style="border-radius: 50%;">
                    </a>
                    <ul class="dropdown-menu text-small">
                        <li><a class="dropdown-item" href="?controller=business&action=profile">Profile</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="?controller=auth&action=logout">Sign out</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container-fluid px-5 py-3">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2"><?php echo "Dashboard | ". htmlspecialchars($businessName)?></h1>
        </div>

        <canvas class="my-4 w-100" id="myChart" style="display: block; box-sizing: border-box; height: 378px; width: 895px;"></canvas>
        
        <!-- TODO: Complete script to work with our database -->
        <script>
            // Fetch PHP data and convert it to JavaScript arrays
            var price = <?php echo json_encode(array_column($stats, 'OrderPrice')); ?>;
            var timeOfOrder = <?php echo json_encode(array_column($stats, 'TimeOfOrder')); ?>;

            // Initialize Chart.js
            var ctx = document.getElementById('myChart').getContext('2d');
            var myChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: timeOfOrder, // X-axis labels from DB
                    datasets: [{
                        label: 'Monthly Sales',
                        data: price, // Y-axis data from DB
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        </script>

        <!-- Add filters? -->
        <h2>Orders</h2>
        <div class="table-responsive small">
            <table class="table table-striped table-sm">
            <thead>
                    <tr>
                        <th scope="col">User ID</th>
                        <th scope="col">Order Price</th>
                        <th scope="col">Time Of Order</th>
                        <th scope="col">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stats as $stat): ?>
                        <tr>
                            <td><?= htmlspecialchars($stat['UserID'])?></td>
                            <td><?= htmlspecialchars($stat['OrderPrice'])?></td>
                            <td><?= htmlspecialchars($stat['TimeOfOrder'])?></td>
                            <td><?= htmlspecialchars($stat['OrderStatus'])?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>