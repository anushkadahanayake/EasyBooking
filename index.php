<?php
include "config/db.php";
include 'header.php'; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AC Bus Booking | Premium Bus Reservation System</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="public/css/style.css">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --secondary: #f8fafc;
            --dark: #1e293b;
            --light: #f8fafc;
            --gray: #94a3b8;
            --success: #10b981;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            color: var(--dark);
            line-height: 1.6;
            background-color: var(--secondary);
            width: 100%;
        }
        
        .hero {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 5rem 1rem;
            text-align: center;
            position: relative;
            overflow: hidden;
            width: 100%;
        }
        
        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiPjxkZWZzPjxwYXR0ZXJuIGlkPSJwYXR0ZXJuIiB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHBhdHRlcm5Vbml0cz0idXNlclNwYWNlT25Vc2UiIHBhdHRlcm5UcmFuc2Zvcm09InJvdGF0ZSg0NSkiPjxyZWN0IHdpZHRoPSIyMCIgaGVpZ2h0PSIyMCIgZmlsbD0icmdiYSgyNTUsMjU1LDI1NSwwLjA1KSIvPjwvcGF0dGVybj48L2RlZnM+PHJlY3Qgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgZmlsbD0idXJsKCNwYXR0ZXJuKSIvPjwvc3ZnPg==');
            opacity: 0.3;
        }
        
        .hero-content {
            position: relative;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .center-title {
            font-size: 2.8rem;
            font-weight: 700;
            margin-bottom: 1rem;
            line-height: 1.2;
        }
        
        .hero p {
            font-size: 1.25rem;
            max-width: 700px;
            margin: 0 auto 2rem;
            opacity: 0.9;
        }
        
        .time-card {
            background-color: white;
            max-width: 600px;
            margin: 2rem auto 0;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            text-align: center;
            color: var(--dark);
        }
        
        .time-card h3 {
            margin-bottom: 0.5rem;
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary);
        }
        
        .time-card p {
            font-size: 1.1rem;
            margin: 0;
            color: var(--dark);
        }
        
        .section {
            padding: 5rem 1rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .section-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 2rem;
            text-align: center;
            color: var(--dark);
            position: relative;
        }
        
        .section-title::after {
            content: '';
            display: block;
            width: 80px;
            height: 4px;
            background: var(--primary);
            margin: 0.5rem auto 0;
            border-radius: 2px;
        }
        
        .about p {
            font-size: 1.1rem;
            max-width: 800px;
            margin: 0 auto;
            text-align: center;
            color: #64748b;
        }
        
        .highlights {
            background-color: white;
        }
        
        .highlights ul {
            list-style: none;
            padding: 0;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .highlights li {
            padding: 0.75rem 0;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
        }
        
        .highlights li::before {
            content: '✓';
            color: var(--success);
            font-weight: bold;
            margin-right: 0.75rem;
            font-size: 1.2rem;
        }
        
        .subscription-plans {
            background-color: #f1f5f9;
            margin-top: 20px;
            margin-bottom: 20px;
        }
        
        .subscription-container {
            background-color: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        .subscription-table {
            width: 100%;
            border-collapse: collapse;
            margin: 2rem 0;
            font-size: 1rem;
        }
        
        .subscription-table th, 
        .subscription-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .subscription-table th {
            background-color: var(--primary);
            color: white;
            font-weight: 500;
        }
        
        .subscription-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        
        .subscription-table tr:hover {
            background-color: #f1f5f9;
        }
        
        .subscription-info {
            font-size: 1rem;
            color: #64748b;
            text-align: center;
            margin: 1.5rem 0;
        }
        
        .subscribe-btn {
            display: inline-block;
            background-color: var(--primary);
            color: white;
            padding: 0.75rem 1.5rem;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: 2px solid var(--primary);
            text-align: center;
            margin: 0 auto;
            display: block;
            max-width: 200px;
        }
        
        .subscribe-btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        @media (max-width: 768px) {
            .center-title {
                font-size: 2rem;
            }
            
            .hero p {
                font-size: 1rem;
            }
            
            .section {
                padding: 3rem 1rem;
            }
            
            .section-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>

<body>
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1 class="center-title">Premium AC Bus Booking</h1>
            <p>Experience seamless travel with our advanced bus reservation system. Book your journey in just a few clicks.</p>
        </div>

        <div class="time-card">
            <h3>Current Date & Time</h3>
            <p><?php echo date("l, F j, Y, g:i a"); ?></p>
        </div>
    </section>

    <!-- About Section -->
    <section class="section about">
        <h2 class="section-title">About Our Service</h2>
        <p>AC Bus Booking provides a modern solution for travelers seeking comfort and convenience. Our platform offers real-time seat availability, dynamic scheduling, and premium features to enhance your travel experience.</p>
    </section>

    <!-- Subscription Plan Section -->
    <section class="section subscription-plans">
        <h2 class="section-title">Service Plans</h2>
        <div class="subscription-container">
            <table class="subscription-table">
                <thead>
                    <tr>
                        <th>Feature</th>
                        <th>Standard</th>
                        <th>Premium</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Timetable Access</td>
                        <td>✓</td>
                        <td>✓</td>
                    </tr>
                    <tr>
                        <td>Real-Time Tracking</td>
                        <td>—</td>
                        <td>✓</td>
                    </tr>
                    <tr>
                        <td>Seat Reservation</td>
                        <td>—</td>
                        <td>✓</td>
                    </tr>
                    <tr>
                        <td>Support Priority</td>
                        <td>Standard</td>
                        <td>24/7 Priority</td>
                    </tr>
                    <tr>
                        <td>Monthly Cost</td>
                        <td>Free</td>
                        <td>Rs 599.00</td>
                    </tr>
                </tbody>
            </table>
            <p class="subscription-info">Upgrade to Premium for full access to all features and priority support.</p>
            <a href="subscription.php" class="subscribe-btn">Get Started</a>
        </div>
    </section>

    <!-- Highlights Section -->
    <section class="section highlights">
        <h2 class="section-title">Why Choose Us?</h2>
        <ul>
            <li>Intuitive online booking platform</li>
            <li>Real-time bus tracking (Premium feature)</li>
            <li>Mobile-responsive design</li>
            <li>Secure payment processing</li>
            <li>Dedicated customer support team</li>
        </ul>
    </section>

    <?php include "footer.php"; ?>
</body>
</html>
