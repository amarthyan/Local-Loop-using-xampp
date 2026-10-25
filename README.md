# LocalLoop — Hyperlocal Community Platform

> **Tagline:** Your Community. One Platform.

LocalLoop is a full-featured, hyperlocal digital community web application built using **PHP 8+, MySQL, HTML5, CSS3, JavaScript, and Bootstrap 5** running on **XAMPP / Apache**.

---

## 🌟 Key Features

1. **Hyperlocal Community Feed (`feed.php`)**
   - Location-filtered community posts with town/locality switcher.
   - Channel categories: Announcements, Events, Lost & Found, Jobs, Buy & Sell, Community Help, Recommendations, Property, Education, Student Corner.
   - Interactive AJAX Likes, Bookmarks (Save for later), Inline Comments, and Content Reporting modal.
   - Rich image attachments, view counters, and sort filters (Latest, Popular, Category).

2. **Community Help Network (`help.php` & `help-request.php`)**
   - Urgent mutual assistance system for medical loans (wheelchairs, nebulizers), emergency blood donors, volunteer transport.
   - Urgency markers: Low, Medium, High, Critical.
   - Direct helper response and phone contact.
   - One-click resolution by requester that automatically awards reputation points and badges to helpers!

3. **Hyperlocal Marketplace (`marketplace.php` & `item.php`)**
   - Buy & sell pre-owned bikes, electronics, textbooks, laptops, and furniture locally.
   - Categorized catalog with condition badges (New, Like New, Good, Fair) and asking prices in INR.
   - Seller contact with direct call integration and status toggles (Available, Reserved, Sold).

4. **Local Jobs & Gigs (`jobs.php` & `job.php`)**
   - Local employment for businesses, cafes, shops, part-time student gigs, and freelance contracts.
   - Online job application portal with resume upload and cover letter.
   - Employer application review dashboard with applicant status tracking.

5. **Community Events & Meetups (`events.php` & `event.php`)**
   - Sports tournaments, cleanup drives, festivals, and tech meetups.
   - Date, time, venue, and organizer details.
   - Live AJAX RSVP system ("Going" / "Interested") with real-time attendee counter and attendee roster.

6. **Lost & Found Portal (`lost-found.php` & `create-lost-found.php`)**
   - Report misplaced or found keys, wallets, IDs, and valuables with date, location, and photos.
   - Quick one-click contact copying to reconnect owners and finders.

7. **User Profiles, Badges & Reputation (`profile.php`)**
   - Point-based community reputation system.
   - Automated achievement badges: *Helpful Neighbour*, *Trusted Seller*, *Community Contributor*, *Community Helper*, *Top Contributor*.
   - Profile photo uploads, bio, home locality, and personal activity history.

8. **Comprehensive Administrator Panel (`admin/`)**
   - **Dashboard Overview (`admin/index.php`)**: Live KPI metrics, queue alerts, and system health.
   - **Moderation Queue (`admin/reports.php`)**: Review user reports, dismiss false flags, or hide/delete reported posts.
   - **User Management (`admin/users.php`)**: Manage citizen accounts, issue warnings, suspend violators, promote admins, or delete accounts.
   - **Content Management (`admin/posts.php` & `admin/comments.php`)**: Moderate posts and comments across all localities.
   - **Channels & Localities (`admin/categories.php` & `admin/areas.php`)**: Add, customize, or toggle community channels and supported towns.
   - **Audit Trail (`admin/actions.php`)**: Complete chronological audit log of all administrative actions.

9. **Visual Analytics (`community-stats.php`)**
   - Interactive Chart.js charts displaying posts distribution by category, locality engagement, and help resolution rates.

---

## 🚀 Setup & Installation (XAMPP)

### Prerequisites
- **XAMPP** (Apache + MySQL + PHP 8+)
- Web browser (Chrome, Edge, Firefox, or Safari)

### Quick Start
1. Ensure Apache and MySQL are running in the XAMPP Control Panel.
2. The project files reside in `C:/xampp/htdocs/BCN project/`.
3. Open your browser and navigate to:
   ```
   http://localhost/BCN%20project/
   ```
4. If setting up on a fresh machine or reinstalling the database, import `database.sql` in phpMyAdmin (`http://localhost/phpmyadmin/`) or execute:
   ```bash
   mysql -u root < database.sql
   ```

---

## 🔑 Demo Accounts

For testing and demonstration, use the quick presets available directly on the login page (`login.php`):

| Role | Username | Password | Purpose |
| :--- | :--- | :--- | :--- |
| **System Admin** | `admin` | `admin123` | Full access to Moderation Queue, User Management, Audit Logs & Admin Dashboard |
| **Resident Citizen** | `rahulk` | `password123` | Community feed posts, liking, commenting, asking for help, RSVPing to events |
| **Local Business** | `malabarbakes` | `password123` | Posting job openings, business announcements, and hiring |
| **Doctor / Helper** | `ananya_doc` | `password123` | Offering community medical assistance, solving help requests |

---

## 📁 Project Structure

```
BCN project/
├── admin/                     # Administrator dashboard & moderation tools
│   ├── index.php              # KPI metrics & quick queue
│   ├── users.php              # User accounts & warning/suspension
│   ├── posts.php              # Post moderation
│   ├── comments.php           # Comment moderation
│   ├── reports.php            # User-submitted flags queue
│   ├── categories.php         # Community channels configuration
│   ├── areas.php              # Localities & town management
│   ├── actions.php            # Audit trail logs
│   ├── header.php             # Admin layout header
│   └── footer.php             # Admin layout footer
├── ajax/                      # Asynchronous API endpoints
│   ├── like.php               # AJAX post like/unlike toggle
│   ├── comment.php            # AJAX inline comment submission
│   ├── save.php               # AJAX bookmark save/unsave
│   ├── report.php             # AJAX content report submission
│   ├── event_rsvp.php         # AJAX event RSVP attendance toggle
│   └── help_offer.php         # AJAX community help offer submission
├── assets/
│   ├── css/
│   │   └── style.css          # Tailored modern CSS design system
│   ├── js/
│   │   └── app.js             # Client-side AJAX & toast scripts
│   └── images/
│       └── default-avatar.svg # SVG fallback avatar
├── config/
│   ├── database.php           # PDO connection configuration
│   └── config.php             # Dynamic BASE_URL, session, & password sync
├── includes/
│   ├── header.php             # HTML head, Google fonts, CSS links
│   ├── navbar.php             # Locality switcher, search pill, user dropdown
│   ├── footer.php             # Footer channels, report modal, JS scripts
│   ├── auth.php               # User authentication & RBAC guards
│   ├── functions.php          # Helpers (time_ago, reputation, flash, uploads)
│   └── csrf.php               # Cross-Site Request Forgery protection
├── uploads/                   # Media attachments
│   ├── profiles/              # User avatar uploads
│   ├── posts/                 # Post photos
│   ├── events/                # Event posters
│   ├── marketplace/           # Buy & sell item photos
│   ├── lostfound/             # Lost & found photos
│   └── resumes/               # Job applicant resumes
├── index.php                  # Landing page with stats & highlights
├── feed.php                   # Hyperlocal community feed
├── create-post.php            # Create community post
├── post.php                   # Single post view & comment thread
├── edit-post.php              # Edit post (author/admin only)
├── delete-post.php            # Delete post handler
├── delete-comment.php         # Delete comment handler
├── login.php                  # User authentication with demo presets
├── register.php               # User registration (Citizen / Business)
├── logout.php                 # Session destroy & logout
├── forgot-password.php        # Password recovery simulator
├── profile.php                # User profile, badges & reputation
├── edit-profile.php           # Personal settings & avatar upload
├── search.php                 # Unified keyword & filter search
├── saved.php                  # Bookmarked posts
├── notifications.php          # Real-time activity notifications
├── events.php                 # Events calendar & listings
├── event.php                  # Event details & RSVP roster
├── create-event.php           # Host an event
├── jobs.php                   # Local jobs directory
├── job.php                    # Job details & application modal
├── create-job.php             # Post a job opening
├── marketplace.php            # Hyperlocal buy & sell catalog
├── item.php                   # Marketplace item details & contact
├── create-item.php            # List an item for sale
├── help.php                   # Community help network
├── help-request.php           # Help request details & offers
├── create-help.php            # Submit help request
├── lost-found.php             # Lost & found directory
├── create-lost-found.php      # Report lost or found property
├── community-stats.php        # Public analytics with Chart.js
├── about.php                  # Platform mission & community guidelines
├── database.sql               # MySQL database schema & seed data
└── LocalLoop_Implementation_Plan.md
```

---

## 🔒 Security Implementations

- **SQL Injection Prevention:** 100% prepared PDO statements with parameter binding across all database queries.
- **Cross-Site Scripting (XSS):** Context-aware HTML escaping with `htmlspecialchars()`.
- **Cross-Site Request Forgery (CSRF):** Cryptographic token generation and verification on forms and critical actions.
- **Password Security:** One-way password hashing using `PASSWORD_BCRYPT`.
- **Authorization & Ownership:** Strict authorization checks on edit and delete operations to prevent ID parameter manipulation.
- **File Upload Hardening:** Extension whitelisting, MIME-type inspection via `finfo`, file size enforcement, and file renaming using cryptographic unique IDs.
