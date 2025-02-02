<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - <?= $title ?? 'Dashboard' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
    body {
        padding-top: 56px;
    }

    .sidebar {
        padding-top: 1rem;
        min-height: calc(100vh - 56px);
        box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
        background-color: #212529;
        transition: all 0.3s ease-in-out;
        position: fixed;
        width: inherit;
        z-index: 100;
    }

    @media (max-width: 768px) {
        .sidebar {
            margin-left: -100%;
            width: 250px;
        }

        .sidebar.show {
            margin-left: 0;
        }

        main {
            margin-left: 0;
        }
    }

    .nav-link {
        color: rgba(255, 255, 255, .9) !important;
        /* Brighter text */
        padding: .75rem 1rem;
        transition: all 0.2s ease;
    }

    .nav-link:hover {
        color: #fff !important;
        background: rgba(255, 255, 255, .1);
    }

    .nav-link.active {
        color: #fff !important;
        background: rgba(255, 255, 255, .15);
        font-weight: 500;
    }

    .nav-link i {
        margin-right: 0.5rem;
        opacity: 0.8;
    }

    .sidebar.collapsed {
        margin-left: -16.66667%;
    }

    .sidebar-sticky {
        position: sticky;
        top: 0;
        padding-top: 1rem;
    }

    main {
        transition: margin 0.3s ease-in-out;
        margin-left: 16.66667%;
        padding-top: 2rem;
    }

    main.expanded {
        margin-left: 0;
    }

    @media (max-width: 768px) {
        .sidebar {
            margin-left: -100%;
        }

        main {
            margin-left: 0;
        }

        .sidebar.collapsed {
            margin-left: -100%;
        }
    }
    </style>
</head>

<body>
    <nav class="navbar navbar-dark bg-dark fixed-top px-3">
        <div class="container-fluid">
            <button class="btn btn-dark" id="sidebarToggle">
                <i class="bi bi-list"></i>
            </button>
            <a class="navbar-brand" href="/admin">Event Management</a>

            <div class="dropdown">
                <button class="btn btn-dark dropdown-toggle" type="button" id="profileDropdown"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-person-circle"></i> <?= $_SESSION['user']['name'] ?? 'User' ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end navbar-profile-dropdown" aria-labelledby="profileDropdown">
                    <li><a class="dropdown-item" href="/admin/profile"><i class="bi bi-person"></i> Profile</a></li>
                    <li><a class="dropdown-item" href="/admin/profile/password"><i class="bi bi-key"></i> Change
                            Password</a></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item text-danger" href="/logout"><i class="bi bi-box-arrow-right"></i> Sign
                            Out</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <nav id="sidebar" class="col-md-3 col-lg-2 sidebar">
                <div class="sidebar-sticky">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?= $view === 'admin/dashboard' ? 'active' : '' ?>"
                                href="/admin/dashboard">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                        <!-- TODO: IN FUTURE -->
                        <!-- <li class="nav-item">
                                <a class="nav-link <?= strpos($view, 'admin/categories') !== false ? 'active' : '' ?>"
                                    href="/admin/categories">
                                    <i class="bi bi-grid"></i> Categories
                                </a>
                            </li> -->
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($view, 'admin/users') !== false ? 'active' : '' ?>"
                                href="/admin/users">
                                <i class="bi bi-people"></i> Users
                            </a>
                        </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($view, 'admin/events') !== false ? 'active' : '' ?>"
                                href="/admin/events">
                                <i class="bi bi-calendar-event"></i> Events
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= $_SESSION['success'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= $_SESSION['error'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <?= $content ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const main = document.querySelector('main');

    sidebarToggle.addEventListener('click', function() {
        if (window.innerWidth <= 768) {
            sidebar.classList.toggle('show');
        } else {
            sidebar.classList.toggle('collapsed');
            main.classList.toggle('expanded');
        }
    });

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
        if (window.innerWidth <= 768 &&
            !sidebar.contains(event.target) &&
            !sidebarToggle.contains(event.target) &&
            sidebar.classList.contains('show')) {
            sidebar.classList.remove('show');
        }
    });

    // Close sidebar on mobile when clicking a nav item
    if (window.innerWidth <= 768) {
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                sidebar.classList.remove('show');
            });
        });
    }
    </script>
</body>

</html>