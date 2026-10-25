<?php
// prototype.php
// LocalLoop High-Fidelity Prototype Showcase & Component System
// Interactive 30+ Screens Flow Navigator & Device Frame Simulator (Sections 32, 33, 34, 36)

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = "UI Prototype & Design System — 30+ Screens";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container-fluid p-0">
    <!-- Top Showcase Control Bar -->
    <div class="card card-solid p-4 mb-4 border-2 border-primary-subtle shadow-card" style="background: linear-gradient(135deg, #0b132b 0%, #1c2541 100%); color: #ffffff;">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge bg-primary px-3 py-1 rounded-pill" style="font-size: 0.75rem;">Production-Ready UI</span>
                    <span class="badge bg-success-subtle text-success px-2.5 py-1 rounded-pill" style="font-size: 0.75rem;">30+ Complete Screens</span>
                    <span class="badge bg-warning-subtle text-warning px-2.5 py-1 rounded-pill" style="font-size: 0.75rem;">B.Tech Final Year Demo</span>
                </div>
                <h3 class="fw-bold mb-1 text-white">LocalLoop — Master UI Prototype & Design System</h3>
                <p class="text-white-50 small mb-0">Interactive device simulator & component library. Click any screen below to preview in Desktop, Tablet, or Mobile frames, or open live.</p>
            </div>

            <!-- Device Viewport Switcher Controls & Animation Playground -->
            <div class="d-flex flex-wrap align-items-center gap-2">
                <div class="d-flex align-items-center gap-2 bg-white bg-opacity-10 p-2 rounded-pill border border-white border-opacity-15">
                    <button type="button" class="btn btn-sm btn-light rounded-pill px-3 py-1.5 fw-semibold" id="btnViewDesktop" onclick="setDeviceView('desktop')">
                        <i data-lucide="monitor" style="width: 15px; height: 15px;" class="me-1"></i> Desktop (1440×1024)
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3 py-1.5 fw-semibold" id="btnViewTablet" onclick="setDeviceView('tablet')">
                        <i data-lucide="tablet" style="width: 15px; height: 15px;" class="me-1"></i> Tablet (1024×1366)
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3 py-1.5 fw-semibold" id="btnViewMobile" onclick="setDeviceView('mobile')">
                        <i data-lucide="smartphone" style="width: 15px; height: 15px;" class="me-1"></i> Mobile (390×844)
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-2.5 py-1.5" onclick="openCurrentScreenInNewTab()" title="Open Fullscreen in New Tab">
                        <i data-lucide="external-link" style="width: 15px; height: 15px;"></i>
                    </button>
                </div>

                <!-- Animation Live Triggers -->
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-warning rounded-pill px-3 py-1.5 fw-bold shadow-sm" onclick="triggerConfetti()" title="Trigger celebratory confetti blast">
                        🎉 Confetti
                    </button>
                    <button type="button" class="btn btn-sm btn-danger rounded-pill px-3 py-1.5 fw-bold shadow-sm" onclick="testHeartBurst(event)" title="Trigger floating heart particles">
                        ❤️ Heart Pop
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Prototype Workspace: Left 30+ Screens Navigator + Right Live Device Frame -->
    <div class="row g-4">
        
        <!-- Left Column: 30+ Screens Flow Navigator -->
        <div class="col-xl-4 col-lg-5">
            <div class="card card-solid p-3" style="max-height: 860px; overflow-y: auto;">
                <div class="d-flex align-items-center justify-content-between mb-3 px-2">
                    <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        <i data-lucide="layers" class="text-primary" style="width: 18px; height: 18px;"></i>
                        <span>Application Screens Navigator</span>
                    </h6>
                    <span class="badge bg-light text-secondary border">37 Screens</span>
                </div>

                <!-- Accordion / Grouped Flows -->
                <div class="accordion accordion-flush" id="screensAccordion">
                    
                    <!-- FLOW 1: AUTHENTICATION & ONBOARDING -->
                    <div class="accordion-item border-0 mb-2">
                        <h2 class="accordion-header">
                            <button class="accordion-button rounded-3 p-2.5 fw-bold bg-light text-dark small" type="button" data-bs-toggle="collapse" data-bs-target="#flowAuth">
                                🔐 1. Authentication & Onboarding (4 Screens)
                            </button>
                        </h2>
                        <div id="flowAuth" class="accordion-collapse collapse show" data-bs-parent="#screensAccordion">
                            <div class="accordion-body p-2 d-flex flex-column gap-1">
                                <button type="button" class="btn btn-sm text-start screen-btn active p-2 rounded-2" onclick="loadScreen('login.php', this)">
                                    <div class="fw-semibold text-dark">01. Split-Screen Login</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Branding illustration + glassmorphic card (Section 5)</div>
                                </button>
                                <button type="button" class="btn btn-sm text-start screen-btn p-2 rounded-2" onclick="loadScreen('register.php', this)">
                                    <div class="fw-semibold text-dark">02. Registration Form</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Clean registration with 5 area selectors (Section 6)</div>
                                </button>
                                <button type="button" class="btn btn-sm text-start screen-btn p-2 rounded-2" onclick="loadScreen('onboarding.php', this)">
                                    <div class="fw-semibold text-dark">03. Choose Your Community</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Searchable locality cards onboarding (Section 6)</div>
                                </button>
                                <button type="button" class="btn btn-sm text-start screen-btn p-2 rounded-2" onclick="loadScreen('forgot-password.php', this)">
                                    <div class="fw-semibold text-dark">04. Password Recovery</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Email/phone verification code flow</div>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- FLOW 2: HOME FEED & POSTING -->
                    <div class="accordion-item border-0 mb-2">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed rounded-3 p-2.5 fw-bold bg-light text-dark small" type="button" data-bs-toggle="collapse" data-bs-target="#flowFeed">
                                🏠 2. Home Feed & Community Notice Board (5 Screens)
                            </button>
                        </h2>
                        <div id="flowFeed" class="accordion-collapse collapse" data-bs-parent="#screensAccordion">
                            <div class="accordion-body p-2 d-flex flex-column gap-1">
                                <button type="button" class="btn btn-sm text-start screen-btn active p-2 rounded-2" onclick="loadScreen('feed.php', this)">
                                    <div class="fw-semibold text-dark">05. Home Dashboard (Civic Glass)</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Redesigned Civic Glass feed, pulse ribbon & rail (Sections 7-12)</div>
                                </button>
                                <button type="button" class="btn btn-sm text-start screen-btn p-2 rounded-2" onclick="loadScreen('stitch_localloop_community_platform_ui/code.html', this)">
                                    <div class="fw-semibold text-primary">✨ Stitch Master Template</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Standalone Civic Glass design reference screen</div>
                                </button>
                                <button type="button" class="btn btn-sm text-start screen-btn p-2 rounded-2" onclick="loadScreen('feed.php?category=announcements', this)">
                                    <div class="fw-semibold text-dark">06. Announcements Channel</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Ward 5 pipeline maintenance update (Section 11)</div>
                                </button>
                                <button type="button" class="btn btn-sm text-start screen-btn p-2 rounded-2" onclick="loadScreen('create-post.php', this)">
                                    <div class="fw-semibold text-dark">07. Multi-Step Post Creation</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Type select &rarr; form fields &rarr; live preview (Section 22)</div>
                                </button>
                                <button type="button" class="btn btn-sm text-start screen-btn p-2 rounded-2" onclick="loadScreen('post.php?id=1', this)">
                                    <div class="fw-semibold text-dark">08. Post Discussion & Comments</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Post details, likes, comment thread, report modal</div>
                                </button>
                                <button type="button" class="btn btn-sm text-start screen-btn p-2 rounded-2" onclick="loadScreen('index.php', this)">
                                    <div class="fw-semibold text-dark">09. Platform Welcome Landing</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Public welcome hero & value props</div>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- FLOW 3: EXPLORE & DIRECTORY -->
                    <div class="accordion-item border-0 mb-2">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed rounded-3 p-2.5 fw-bold bg-light text-dark small" type="button" data-bs-toggle="collapse" data-bs-target="#flowExplore">
                                🧭 3. Explore & Search (3 Screens)
                            </button>
                        </h2>
                        <div id="flowExplore" class="accordion-collapse collapse" data-bs-parent="#screensAccordion">
                            <div class="accordion-body p-2 d-flex flex-column gap-1">
                                <button type="button" class="btn btn-sm text-start screen-btn p-2 rounded-2" onclick="loadScreen('explore.php', this)">
                                    <div class="fw-semibold text-dark">10. Explore LocalLoop</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Large interactive category cards with counts (Section 13)</div>
                                </button>
                                <button type="button" class="btn btn-sm text-start screen-btn p-2 rounded-2" onclick="loadScreen('search.php?q=laptop', this)">
                                    <div class="fw-semibold text-dark">11. Universal Search Results</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Tabs (Posts, Market, Jobs, Events) & 5 filters (Section 23)</div>
                                </button>
                                <button type="button" class="btn btn-sm text-start screen-btn p-2 rounded-2" onclick="loadScreen('community-stats.php', this)">
                                    <div class="fw-semibold text-dark">12. Community Statistics</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">2,480 Members, 1,284 Posts, charts (Section 29)</div>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- FLOW 4: EVENTS -->
                    <div class="accordion-item border-0 mb-2">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed rounded-3 p-2.5 fw-bold bg-light text-dark small" type="button" data-bs-toggle="collapse" data-bs-target="#flowEvents">
                                📅 4. Events Platform (3 Screens)
                            </button>
                        </h2>
                        <div id="flowEvents" class="accordion-collapse collapse" data-bs-parent="#screensAccordion">
                            <div class="accordion-body p-2 d-flex flex-column gap-1">
                                <button type="button" class="btn btn-sm text-start screen-btn p-2 rounded-2" onclick="loadScreen('events.php', this)">
                                    <div class="fw-semibold text-dark">13. Events Near You</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Clean-Up Drive, filters by date/distance/category (Section 14)</div>
                                </button>
                                <button type="button" class="btn btn-sm text-start screen-btn p-2 rounded-2" onclick="loadScreen('event.php?id=1', this)">
                                    <div class="fw-semibold text-dark">14. Event Details & RSVPs</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Banner, description, participant avatars, Join Event (Section 15)</div>
                                </button>
                                <button type="button" class="btn btn-sm text-start screen-btn p-2 rounded-2" onclick="loadScreen('create-event.php', this)">
                                    <div class="fw-semibold text-dark">15. Organize Event Form</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Event title, date, time, venue, banner upload</div>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- FLOW 5: LOCAL JOBS -->
                    <div class="accordion-item border-0 mb-2">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed rounded-3 p-2.5 fw-bold bg-light text-dark small" type="button" data-bs-toggle="collapse" data-bs-target="#flowJobs">
                                💼 5. Local Jobs Board (3 Screens)
                            </button>
                        </h2>
                        <div id="flowJobs" class="accordion-collapse collapse" data-bs-parent="#screensAccordion">
                            <div class="accordion-body p-2 d-flex flex-column gap-1">
                                <button type="button" class="btn btn-sm text-start screen-btn p-2 rounded-2" onclick="loadScreen('jobs.php', this)">
                                    <div class="fw-semibold text-dark">16. Local Jobs Board</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Part-Time Graphic Designer, 5 filters (Section 16)</div>
                                </button>
                                <button type="button" class="btn btn-sm text-start screen-btn p-2 rounded-2" onclick="loadScreen('job.php?id=1', this)">
                                    <div class="fw-semibold text-dark">17. Job Details & Apply</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Responsibilities, requirements, Apply Now, Save Job (Section 17)</div>
                                </button>
                                <button type="button" class="btn btn-sm text-start screen-btn p-2 rounded-2" onclick="loadScreen('create-job.php', this)">
                                    <div class="fw-semibold text-dark">18. Post Job Opening</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Company, salary, job type, requirements</div>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- FLOW 6: MARKETPLACE -->
                    <div class="accordion-item border-0 mb-2">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed rounded-3 p-2.5 fw-bold bg-light text-dark small" type="button" data-bs-toggle="collapse" data-bs-target="#flowMarket">
                                🛍️ 6. Hyperlocal Marketplace (3 Screens)
                            </button>
                        </h2>
                        <div id="flowMarket" class="accordion-collapse collapse" data-bs-parent="#screensAccordion">
                            <div class="accordion-body p-2 d-flex flex-column gap-1">
                                <button type="button" class="btn btn-sm text-start screen-btn p-2 rounded-2" onclick="loadScreen('marketplace.php', this)">
                                    <div class="fw-semibold text-dark">19. Local Marketplace</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Used Study Table ₹2,500, 7 category pills (Section 18)</div>
                                </button>
                                <button type="button" class="btn btn-sm text-start screen-btn p-2 rounded-2" onclick="loadScreen('item.php?id=1', this)">
                                    <div class="fw-semibold text-dark">20. Product Details</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Gallery, condition, seller Rahul Menon, Message Seller (Section 19)</div>
                                </button>
                                <button type="button" class="btn btn-sm text-start screen-btn p-2 rounded-2" onclick="loadScreen('create-item.php', this)">
                                    <div class="fw-semibold text-dark">21. List Item for Sale</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Photo upload, price, condition, description</div>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- FLOW 7: LOST & FOUND -->
                    <div class="accordion-item border-0 mb-2">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed rounded-3 p-2.5 fw-bold bg-light text-dark small" type="button" data-bs-toggle="collapse" data-bs-target="#flowLost">
                                🔍 7. Lost & Found (2 Screens)
                            </button>
                        </h2>
                        <div id="flowLost" class="accordion-collapse collapse" data-bs-parent="#screensAccordion">
                            <div class="accordion-body p-2 d-flex flex-column gap-1">
                                <button type="button" class="btn btn-sm text-start screen-btn p-2 rounded-2" onclick="loadScreen('lost-found.php', this)">
                                    <div class="fw-semibold text-dark">22. Lost & Found Portal</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">I Lost / I Found buttons, Lost Black Wallet card (Section 20)</div>
                                </button>
                                <button type="button" class="btn btn-sm text-start screen-btn p-2 rounded-2" onclick="loadScreen('create-lost-found.php', this)">
                                    <div class="fw-semibold text-dark">23. Report Misplaced/Found Item</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Item name, date, landmark, contact info</div>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- FLOW 8: COMMUNITY HELP -->
                    <div class="accordion-item border-0 mb-2">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed rounded-3 p-2.5 fw-bold bg-light text-dark small" type="button" data-bs-toggle="collapse" data-bs-target="#flowHelp">
                                🤝 8. Community Help Network (3 Screens)
                            </button>
                        </h2>
                        <div id="flowHelp" class="accordion-collapse collapse" data-bs-parent="#screensAccordion">
                            <div class="accordion-body p-2 d-flex flex-column gap-1">
                                <button type="button" class="btn btn-sm text-start screen-btn p-2 rounded-2" onclick="loadScreen('help.php', this)">
                                    <div class="fw-semibold text-dark">24. Community Help</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Ask for Help / Offer Help, moving a table (Section 21)</div>
                                </button>
                                <button type="button" class="btn btn-sm text-start screen-btn p-2 rounded-2" onclick="loadScreen('help-request.php?id=1', this)">
                                    <div class="fw-semibold text-dark">25. Help Request & Response</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Urgency badge, location, Offer Assistance form</div>
                                </button>
                                <button type="button" class="btn btn-sm text-start screen-btn p-2 rounded-2" onclick="loadScreen('create-help.php', this)">
                                    <div class="fw-semibold text-dark">26. Ask for Community Assistance</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Urgency picker (Low, Medium, High, Critical)</div>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- FLOW 9: MESSAGES & NOTIFICATIONS -->
                    <div class="accordion-item border-0 mb-2">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed rounded-3 p-2.5 fw-bold bg-light text-dark small" type="button" data-bs-toggle="collapse" data-bs-target="#flowChat">
                                💬 9. Messages & Notifications (3 Screens)
                            </button>
                        </h2>
                        <div id="flowChat" class="accordion-collapse collapse" data-bs-parent="#screensAccordion">
                            <div class="accordion-body p-2 d-flex flex-column gap-1">
                                <button type="button" class="btn btn-sm text-start screen-btn p-2 rounded-2" onclick="loadScreen('messages.php?user=rahulmenon', this)">
                                    <div class="fw-semibold text-dark">27. Two-Column Direct Chat</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Conversation list + active chat thread (Section 25)</div>
                                </button>
                                <button type="button" class="btn btn-sm text-start screen-btn p-2 rounded-2" onclick="loadScreen('notifications.php', this)">
                                    <div class="fw-semibold text-dark">28. Notifications Center</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">All, Activity, Community, Mentions with unread tint (Section 24)</div>
                                </button>
                                <button type="button" class="btn btn-sm text-start screen-btn p-2 rounded-2" onclick="loadScreen('saved.php', this)">
                                    <div class="fw-semibold text-dark">29. Saved Bookmarks</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Tabs: Posts, Events, Jobs, Market + Remove (Section 26)</div>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- FLOW 10: USER PROFILE & SETTINGS -->
                    <div class="accordion-item border-0 mb-2">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed rounded-3 p-2.5 fw-bold bg-light text-dark small" type="button" data-bs-toggle="collapse" data-bs-target="#flowProfile">
                                👤 10. Profile & Settings (3 Screens)
                            </button>
                        </h2>
                        <div id="flowProfile" class="accordion-collapse collapse" data-bs-parent="#screensAccordion">
                            <div class="accordion-body p-2 d-flex flex-column gap-1">
                                <button type="button" class="btn btn-sm text-start screen-btn p-2 rounded-2" onclick="loadScreen('profile.php?user=amarthyan', this)">
                                    <div class="fw-semibold text-dark">30. Amarthyan S S Profile</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">128 Posts, 342 Likes, 27 Help, 15 Events (Section 27)</div>
                                </button>
                                <button type="button" class="btn btn-sm text-start screen-btn p-2 rounded-2" onclick="loadScreen('edit-profile.php', this)">
                                    <div class="fw-semibold text-dark">31. Edit Profile Form</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Avatar upload, bio, location selector</div>
                                </button>
                                <button type="button" class="btn btn-sm text-start screen-btn p-2 rounded-2" onclick="loadScreen('settings.php', this)">
                                    <div class="fw-semibold text-dark">32. Settings Dashboard</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Account, Privacy, Notifications, Theme, Security (Section 28)</div>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- FLOW 11: ADMIN & MODERATION -->
                    <div class="accordion-item border-0 mb-2">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed rounded-3 p-2.5 fw-bold bg-light text-dark small" type="button" data-bs-toggle="collapse" data-bs-target="#flowAdmin">
                                🛡️ 11. Admin & Moderation (5 Screens)
                            </button>
                        </h2>
                        <div id="flowAdmin" class="accordion-collapse collapse" data-bs-parent="#screensAccordion">
                            <div class="accordion-body p-2 d-flex flex-column gap-1">
                                <button type="button" class="btn btn-sm text-start screen-btn p-2 rounded-2" onclick="loadScreen('admin/index.php', this)">
                                    <div class="fw-semibold text-dark">33. Admin Console Dashboard</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">6 cards, charts, ingestion tables (Section 30)</div>
                                </button>
                                <button type="button" class="btn btn-sm text-start screen-btn p-2 rounded-2" onclick="loadScreen('admin/reports.php', this)">
                                    <div class="fw-semibold text-dark">34. Reports Management</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Report #1042, Review, Dismiss, Remove, Warn (Section 31)</div>
                                </button>
                                <button type="button" class="btn btn-sm text-start screen-btn p-2 rounded-2" onclick="loadScreen('admin/users.php', this)">
                                    <div class="fw-semibold text-dark">35. User Management Table</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Role assignment, suspension, verification</div>
                                </button>
                                <button type="button" class="btn btn-sm text-start screen-btn p-2 rounded-2" onclick="loadScreen('admin/areas.php', this)">
                                    <div class="fw-semibold text-dark">36. Localities & Areas Directory</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Manage wards, pincodes, and active loops</div>
                                </button>
                                <button type="button" class="btn btn-sm text-start screen-btn p-2 rounded-2" onclick="loadScreen('admin/actions.php', this)">
                                    <div class="fw-semibold text-dark">37. Security Audit Logs</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Administrative history and action records</div>
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Right Column: Interactive Device Simulator Viewport -->
        <div class="col-xl-8 col-lg-7">
            <div class="card card-solid p-4 text-center bg-light" style="min-height: 860px;">
                <!-- Device Frame Indicator -->
                <div class="d-flex align-items-center justify-content-between mb-3 px-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-dark rounded-pill px-3 py-1" id="deviceLabelBadge">Desktop View (1440 × 1024)</span>
                        <span class="text-secondary small" id="currentScreenUrlLabel"><?= BASE_URL ?>/login.php</span>
                    </div>
                    <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-3" onclick="reloadDeviceFrame()">
                        <i data-lucide="rotate-cw" style="width: 13px; height: 13px;"></i> Reload Screen
                    </button>
                </div>

                <!-- Simulated Device Outer Wrapper -->
                <div class="mx-auto transition-normal" id="deviceWrapper" style="width: 100%; max-width: 100%; height: 780px; border-radius: 16px; overflow: hidden; box-shadow: 0 12px 36px rgba(15, 23, 42, 0.12); border: 8px solid #0b132b; background: #ffffff;">
                    <iframe id="deviceIframe" src="<?= BASE_URL ?>/login.php" style="width: 100%; height: 100%; border: none;"></iframe>
                </div>
            </div>
        </div>

    </div>

    <!-- Section 33 & 34: Component Library & Design System Showcase -->
    <div class="card card-solid p-5 mt-5">
        <div class="d-flex align-items-center gap-2 mb-2">
            <span class="badge bg-primary px-3 py-1 rounded-pill">Section 33 & 34</span>
            <h4 class="fw-bold mb-0 text-dark">LocalLoop Design System & Reusable Components</h4>
        </div>
        <p class="text-secondary small mb-4">
            Built using an 8px grid system, Lucide outline icons, restrained navy palette, and soft rounded corners.
        </p>

        <div class="row g-4">
            <!-- Palette -->
            <div class="col-md-6 col-lg-3">
                <h6 class="fw-bold small text-uppercase text-secondary mb-3">Color Tokens</h6>
                <div class="d-flex flex-column gap-2 small">
                    <div class="d-flex align-items-center gap-2">
                        <span style="width: 24px; height: 24px; background: #0b132b; border-radius: 6px;"></span>
                        <span><strong>#0B132B</strong> — Primary Navy</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span style="width: 24px; height: 24px; background: #2563eb; border-radius: 6px;"></span>
                        <span><strong>#2563EB</strong> — Brand Blue</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span style="width: 24px; height: 24px; background: #0ea5e9; border-radius: 6px;"></span>
                        <span><strong>#0EA5E9</strong> — Soft Cyan Accent</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span style="width: 24px; height: 24px; background: #10b981; border-radius: 6px;"></span>
                        <span><strong>#10B981</strong> — Success Green</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span style="width: 24px; height: 24px; background: #ef4444; border-radius: 6px;"></span>
                        <span><strong>#EF4444</strong> — Destructive Red</span>
                    </div>
                </div>
            </div>

            <!-- Buttons -->
            <div class="col-md-6 col-lg-3">
                <h6 class="fw-bold small text-uppercase text-secondary mb-3">Button Variants</h6>
                <div class="d-flex flex-column gap-2">
                    <button class="btn btn-primary-custom btn-sm">Primary Navy Action</button>
                    <button class="btn btn-blue-accent btn-sm">Blue Cyan Accent</button>
                    <button class="btn btn-outline-custom btn-sm">Outline Neutral</button>
                    <button class="btn btn-outline-danger btn-sm rounded-pill">Destructive Pill</button>
                </div>
            </div>

            <!-- Typography & Radii -->
            <div class="col-md-6 col-lg-3">
                <h6 class="fw-bold small text-uppercase text-secondary mb-3">Border Radius System</h6>
                <div class="d-flex flex-column gap-2 small">
                    <div class="p-2 border rounded-1 bg-light">Small: 8px (Buttons, badges)</div>
                    <div class="p-2 border rounded-3 bg-light">Medium: 12px (Cards, pills)</div>
                    <div class="p-2 border rounded-4 bg-light">Large: 16px (Panels, heroes)</div>
                    <div class="p-2 border rounded-pill bg-light text-center">Pill: 9999px (Controls, avatars)</div>
                </div>
            </div>

            <!-- Spacing Grid -->
            <div class="col-md-6 col-lg-3">
                <h6 class="fw-bold small text-uppercase text-secondary mb-3">8px Spacing System</h6>
                <div class="small text-secondary">
                    <div><code>--space-2: 8px</code> (Micro spacing)</div>
                    <div><code>--space-4: 16px</code> (Card standard padding)</div>
                    <div><code>--space-6: 24px</code> (Component separation)</div>
                    <div><code>--space-8: 32px</code> (Section margins)</div>
                    <div><code>--space-12: 48px</code> (Layout gutter)</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let currentScreenUrl = '<?= BASE_URL ?>/login.php';

    function loadScreen(url, btnElement) {
        document.querySelectorAll('.screen-btn').forEach(b => b.classList.remove('active', 'bg-primary-subtle'));
        if (btnElement) {
            btnElement.classList.add('active', 'bg-primary-subtle');
        }
        
        currentScreenUrl = '<?= BASE_URL ?>/' + url;
        document.getElementById('deviceIframe').src = currentScreenUrl;
        document.getElementById('currentScreenUrlLabel').textContent = currentScreenUrl;
    }

    function setDeviceView(mode) {
        const wrapper = document.getElementById('deviceWrapper');
        const badge = document.getElementById('deviceLabelBadge');
        const btnD = document.getElementById('btnViewDesktop');
        const btnT = document.getElementById('btnViewTablet');
        const btnM = document.getElementById('btnViewMobile');

        btnD.className = 'btn btn-sm btn-outline-light rounded-pill px-3 py-1.5 fw-semibold';
        btnT.className = 'btn btn-sm btn-outline-light rounded-pill px-3 py-1.5 fw-semibold';
        btnM.className = 'btn btn-sm btn-outline-light rounded-pill px-3 py-1.5 fw-semibold';

        if (mode === 'desktop') {
            wrapper.style.maxWidth = '100%';
            wrapper.style.height = '780px';
            wrapper.style.borderRadius = '16px';
            wrapper.style.borderWidth = '8px';
            badge.textContent = 'Desktop View (1440 × 1024)';
            btnD.className = 'btn btn-sm btn-light rounded-pill px-3 py-1.5 fw-semibold';
        } else if (mode === 'tablet') {
            wrapper.style.maxWidth = '768px';
            wrapper.style.height = '780px';
            wrapper.style.borderRadius = '24px';
            wrapper.style.borderWidth = '12px';
            badge.textContent = 'Tablet View (1024 × 1366)';
            btnT.className = 'btn btn-sm btn-light rounded-pill px-3 py-1.5 fw-semibold';
        } else if (mode === 'mobile') {
            wrapper.style.maxWidth = '390px';
            wrapper.style.height = '760px';
            wrapper.style.borderRadius = '38px';
            wrapper.style.borderWidth = '14px';
            badge.textContent = 'Mobile View (390 × 844 iPhone)';
            btnM.className = 'btn btn-sm btn-light rounded-pill px-3 py-1.5 fw-semibold';
        }
    }

    function reloadDeviceFrame() {
        document.getElementById('deviceIframe').src = currentScreenUrl;
    }

    function openCurrentScreenInNewTab() {
        window.open(currentScreenUrl, '_blank');
    }

    function testHeartBurst(e) {
        const rect = e.target.getBoundingClientRect();
        if (typeof spawnHeartParticles === 'function') {
            spawnHeartParticles(rect.left + rect.width / 2, rect.top);
        }
        if (typeof showToast === 'function') {
            showToast('Micro-interaction: Heart burst particles & bounce!', 'success');
        }
    }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
