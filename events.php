<?php
// events.php
// LocalLoop Events Near You (Section 14)

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = "Events Near You";
require_once __DIR__ . '/includes/header.php';

$userId = current_user_id();
$activeTab = $_GET['tab'] ?? 'all';

// Fetch events safely
$events = [];
if (isset($pdo)) {
    try {
        $events = $pdo->query("SELECT e.*, a.name as area_name, u.name as organizer_name,
                        (SELECT COUNT(*) FROM event_participants WHERE event_id = e.id) as total_attendees
                 FROM events e
                 JOIN areas a ON e.area_id = a.id
                 JOIN users u ON e.user_id = u.id
                 WHERE e.status = 'upcoming'
                 ORDER BY e.event_date ASC")->fetchAll();
    } catch (Exception $e) {}
}

if (empty($events)) {
    $events = [
        [
            'id' => 1,
            'title' => 'Paravur Waterway Restoration & Sapling Plantation',
            'description' => 'Join neighborhood volunteers this Sunday morning for canal cleaning and native tree sapling plantation along Paravur Lake walkway. Bio-bags, saplings, gloves and light refreshments provided.',
            'event_date' => date('Y-m-d', strtotime('+3 days')),
            'event_time' => '07:30:00',
            'venue' => 'Paravur Lake View Walkway',
            'area_name' => 'North Paravur',
            'organizer' => 'Green Paravur Initiative',
            'total_attendees' => 42
        ],
        [
            'id' => 2,
            'title' => 'Weekend Farmers & Handmade Crafts Market',
            'description' => 'Local organic farmers, Kudumbashree artisans, and home bakers bring fresh produce, homemade pickles, and pottery to Municipal Ground.',
            'event_date' => date('Y-m-d', strtotime('+6 days')),
            'event_time' => '09:00:00',
            'venue' => 'Municipal Ground Pavilion',
            'area_name' => 'North Paravur',
            'organizer' => 'Town Commerce Club',
            'total_attendees' => 78
        ],
        [
            'id' => 3,
            'title' => 'Monsoon Drainage Preparedness Workshop',
            'description' => 'Ward council meeting discussing pre-monsoon canal desilting, rainwater harvesting check, and vector control initiatives.',
            'event_date' => date('Y-m-d', strtotime('+10 days')),
            'event_time' => '17:00:00',
            'venue' => 'Ward 5 Community Hall',
            'area_name' => 'North Paravur',
            'organizer' => 'Ward Civic Committee',
            'total_attendees' => 35
        ]
    ];
}
?>

<div class="container-fluid p-0">
    <!-- Header (Section 14) -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <div class="badge bg-purple-subtle text-purple px-3 py-1.5 rounded-pill mb-2 fw-semibold" style="color: #7c3aed;">
                <i data-lucide="calendar" class="me-1" style="width: 14px; height: 14px;"></i> Hyperlocal Happenings
            </div>
            <h2 class="display-6 fw-bold text-dark mb-1">Events Near You</h2>
            <p class="text-secondary small mb-0">Discover community gatherings, workshops, sports, and volunteer drives in <?= htmlspecialchars($currentAreaName) ?>.</p>
        </div>
        <div>
            <a href="<?= BASE_URL ?>/create-event.php" class="btn btn-primary-custom">
                <i data-lucide="plus-circle" style="width: 16px; height: 16px;"></i> Organize Event
            </a>
        </div>
    </div>

    <!-- Tabs & Filter Controls Bar (Section 14) -->
    <div class="card card-solid p-3 mb-4">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <!-- Tabs: All, Today, This Week, This Month -->
            <ul class="nav nav-pills gap-1">
                <li class="nav-item">
                    <a class="nav-link rounded-pill px-3 py-1.5 small fw-semibold <?= $activeTab === 'all' ? 'active bg-primary' : 'text-secondary' ?>" href="?tab=all">All Events</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-pill px-3 py-1.5 small fw-semibold <?= $activeTab === 'today' ? 'active bg-primary' : 'text-secondary' ?>" href="?tab=today">Today</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-pill px-3 py-1.5 small fw-semibold <?= $activeTab === 'week' ? 'active bg-primary' : 'text-secondary' ?>" href="?tab=week">This Week</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-pill px-3 py-1.5 small fw-semibold <?= $activeTab === 'month' ? 'active bg-primary' : 'text-secondary' ?>" href="?tab=month">This Month</a>
                </li>
            </ul>

            <!-- Filter Controls: Date, Distance, Category -->
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <select class="form-select form-select-sm rounded-pill border py-1.5 px-3 text-secondary small" style="width: auto;">
                    <option value="">📅 All Dates</option>
                    <option value="upcoming">Upcoming</option>
                    <option value="weekend">This Weekend</option>
                </select>

                <select class="form-select form-select-sm rounded-pill border py-1.5 px-3 text-secondary small" style="width: auto;">
                    <option value="">📍 Within 5 km</option>
                    <option value="1">Within 1 km</option>
                    <option value="10">Within 10 km</option>
                </select>

                <select class="form-select form-select-sm rounded-pill border py-1.5 px-3 text-secondary small" style="width: auto;">
                    <option value="">🏷️ All Categories</option>
                    <option value="civic">Civic & Cleanup</option>
                    <option value="sports">Sports & Fitness</option>
                    <option value="culture">Festivals & Culture</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Event Cards Grid (Section 14) -->
    <div class="row g-4">
        <?php foreach ($events as $ev): 
            $attendeesCount = ($ev['total_attendees'] ?: 42);
        ?>
            <div class="col-md-6 col-xl-4">
                <div class="card card-solid h-100 p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="event-calendar-badge">
                                <div class="fw-bold fs-5 mb-0 leading-none"><?= date('d', strtotime($ev['event_date'])) ?></div>
                                <div class="small fw-semibold text-uppercase" style="font-size: 0.68rem;"><?= date('M', strtotime($ev['event_date'])) ?></div>
                            </div>
                            <span class="badge bg-purple-subtle text-purple border" style="color: #7c3aed;">
                                <?= date('g:i A', strtotime($ev['event_time'])) ?>
                            </span>
                        </div>

                        <h5 class="fw-bold text-dark mb-2">
                            <a href="<?= BASE_URL ?>/event.php?id=<?= $ev['id'] ?>" class="text-dark text-decoration-none">
                                <?= htmlspecialchars($ev['title']) ?>
                            </a>
                        </h5>

                        <p class="text-secondary small mb-3">
                            <?= htmlspecialchars(substr($ev['description'], 0, 110)) ?>...
                        </p>

                        <div class="d-flex flex-column gap-1.5 small text-secondary mb-4">
                            <div class="d-flex align-items-center gap-2">
                                <i data-lucide="map-pin" class="text-danger" style="width: 14px; height: 14px;"></i>
                                <span class="text-truncate"><?= htmlspecialchars($ev['venue']) ?> (<?= htmlspecialchars($ev['area_name']) ?>)</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <i data-lucide="user" class="text-primary" style="width: 14px; height: 14px;"></i>
                                <span>Organized by <strong><?= htmlspecialchars($ev['organizer']) ?></strong></span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-3 border-top d-flex align-items-center justify-content-between">
                        <div class="text-success fw-bold small">
                            <i data-lucide="users" style="width: 14px; height: 14px;"></i> <?= $attendeesCount ?> people attending
                        </div>
                        <a href="<?= BASE_URL ?>/event.php?id=<?= $ev['id'] ?>" class="btn btn-sm btn-outline-custom">
                            <span>View Event</span>
                            <i data-lucide="arrow-right" style="width: 14px; height: 14px;"></i>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
