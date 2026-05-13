<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Lab Biosains</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Inter', sans-serif; 
            background-color: #f4f7f6; 
        }
        .sidebar { 
            min-height: 100vh; 
            background: linear-gradient(180deg, #1a202c 0%, #2d3748 100%); 
            color: white; 
            transition: all 0.3s; 
            box-shadow: 4px 0 10px rgba(0,0,0,0.1);
        }
        .sidebar a { 
            color: #a0aec0; 
            text-decoration: none; 
            padding: 16px 20px; 
            display: block; 
            transition: 0.3s; 
            font-weight: 500;
        }
        .sidebar a:hover, .sidebar a.active { 
            color: white; 
            background: rgba(255,255,255,0.05); 
            border-left: 4px solid #4299e1; 
        }
        .sidebar-heading { 
            padding: 25px 20px; 
            font-weight: 700; 
            font-size: 1.25rem; 
            letter-spacing: 1px; 
            color: #fff; 
            border-bottom: 1px solid rgba(255,255,255,0.1); 
            text-transform: uppercase;
        }
        .main-content { 
            padding: 30px; 
            width: 100%; 
            overflow-x: hidden;
        }
        .card { 
            border: none; 
            border-radius: 12px; 
            box-shadow: 0 4px 20px rgba(0,0,0,0.05); 
            transition: transform 0.2s ease;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .stat-icon { 
            font-size: 2.5rem; 
            opacity: 0.8; 
        }
        .navbar-custom {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-radius: 12px;
            padding: 15px 20px;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
<div class="d-flex flex-column flex-md-row">
