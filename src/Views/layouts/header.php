<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Ticketing Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .hero-section {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('https://images.unsplash.com/photo-1549451371-64aa98a1d771?ixlib=rb-4.0.3&auto=format&fit=crop&w=1470&q=80');
            background-size: cover;
            background-position: center;
            color: white;
        }

        .featured-event-card {
            transition: transform 0.3s;
            height: 100%;
        }

        .featured-event-card:hover {
            transform: scale(1.05);
        }

        .event-image {
            object-fit: cover;
            height: 250px;
            width: 100%;
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">EventHub</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="#">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Events</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Categories</a></li>
                </ul>
                <div class="d-flex">
                    <button class="btn btn-light me-2" data-bs-toggle="modal"
                        data-bs-target="#loginModal">Login</button>
                    <button class="btn btn-success" data-bs-toggle="modal"
                        data-bs-target="#registerModal">Register</button>
                </div>
            </div>
        </div>
    </nav>