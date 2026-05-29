# Movie Night Voting App — Demo Script

## Opening (30 seconds)

> "Hi, I'm [Name]. This is the **Movie Night Voting App** — a real-time platform where groups discover, suggest, and vote on movies together. Built with **Laravel 12**, **Livewire 4**, **PostgreSQL on Supabase**, and self-hosted WebSockets via **Laravel Reverb**. Let me walk you through it."

---

## 1. FUNCTIONALITY — Show features working (2 minutes)

### Step 1: Authentication
- Navigate to `/register`
- Create an account with email and password
- **Say:** *"Full auth system with email verification, password reset, and Sanctum token-based API authentication."*

### Step 2: Dashboard
- Show the dashboard after login
- Point out: Trending Movies section, My Rooms, Public Rooms, Pending Invitations
- **Say:** *"The dashboard shows trending movies from TMDB with 6-hour caching, your rooms, public rooms you can join, and pending invitations."*

### Step 3: Create a Room
- Click "Create Room"
- Fill in title, set visibility to Public
- Submit
- **Say:** *"Rooms can be public or private, with scheduled expiry and auto-generated 6-character invite codes."*

### Step 4: Search and Suggest a Movie
- Inside the room, type "Spider-Man" in the search box
- Show results appearing as you type (debounced)
- Click a movie to suggest it
- **Say:** *"Live TMDB search with debounced input. Results appear as you type. One click suggests the movie to the room."*

### Step 5: Vote
- Click the upvote button on the movie
- Show the score updating
- **Say:** *"Upvote or downvote. Scores update in real-time for all members via WebSockets."*

### Step 6: Real-time with Second User
- Open a second browser/incognito window
- Log in as a different user
- Join the room
- Have the second user vote on the same movie
- Show the first user seeing the vote update instantly
- **Say:** *"Changes broadcast instantly to all room members via Laravel Reverb WebSockets — no page refresh needed."*

### Step 7: Room Chat
- Type a comment in the chat section
- Show it appearing in real-time
- **Say:** *"Built-in room chat with live updates. Comments can be deleted by the owner or admin."*

### Step 8: Notifications
- Show the notification bell icon with unread badge
- Click to show the dropdown with notifications
- **Say:** *"Real-time in-app notifications with email fallback. Members get notified on votes, joins, and winner declarations."*

### Step 9: Close Room and Declare Winner
- Click "Calculate Winner"
- Show the results page
- **Say:** *"The host can close the room and declare a winner. All members receive a notification."*

---

## 2. CODE QUALITY — Show code (1 minute)

### Step 1: Service Layer
- Open `app/Services/VotingService.php`
- **Say:** *"Business logic lives in 5 dedicated services — VotingService, RoomService, InvitationService, OmdbService, TmdbService. Controllers stay thin."*

### Step 2: Form Requests
- Open `app/Http/Requests/StoreRoomRequest.php`
- **Say:** *"Validation is handled by 6 Form Request classes — clean, reusable, and type-safe."*

### Step 3: Models and Relationships
- Open `app/Models/MovieRoom.php`
- **Say:** *"Eloquent models use expressive relationships — host(), members(), movies(), votes(). Soft deletes on rooms and comments."*

### Step 4: Enums
- Open `app/Enums/VoteType.php`
- **Say:** *"PHP 8.1+ backed enums for type safety across the codebase — VoteType, RoomStatus, NotificationType, and more."*

### Step 5: Git History
- Run in terminal: `git log --oneline -15`
- **Say:** *"Active Git history with conventional commits. Every change is tracked and documented."*

### Step 6: Tests
- Run in terminal: `php artisan test`
- Show 166 tests passing
- **Say:** *"166 tests with 387 assertions — covering unit tests, feature tests, Livewire components, broadcasts, and 34 dedicated security tests."*

---

## 3. UI/UX — Show design (1 minute)

### Step 1: Landing Page
- Navigate to `/` (welcome page)
- **Say:** *"Professional landing page with hero section, feature grid, and responsive dark theme."*

### Step 2: Responsive Design
- Resize the browser window from desktop to mobile
- **Say:** *"Fully responsive — works on mobile, tablet, and desktop. TailwindCSS dark theme with consistent styling."*

### Step 3: Sidebar Navigation
- Show the collapsible sidebar
- Click to collapse/expand
- **Say:** *"Collapsible sidebar with Alpine.js store — smooth transitions, persistent state."*

### Step 4: Room Page Layout
- Go back to the room page
- Show the grid layout: search + movies on left, members + chat on right
- **Say:** *"Single-page room view — search, vote, chat, and member list all in one place."*

### Step 5: Admin Dashboard
- Navigate to `/admin` (if admin user)
- Show the Chart.js graph and stats cards
- **Say:** *"Admin panel with usage statistics, user management, and room management with soft-delete restore."*

---

## 4. SECURITY — Show security features (1.5 minutes)

### Step 1: Security Tests
- Run in terminal: `php artisan test tests/Feature/SecurityTest.php`
- Show 34 tests passing
- **Say:** *"34 dedicated security tests covering 8 attack vectors."*

### Step 2: SQL Injection
- Point to the SQL injection tests in the output
- **Say:** *"SQL injection — all input fields tested with 8 payloads. Laravel's parameter binding and Eloquent ORM prevent injection."*

### Step 3: XSS
- Point to the XSS tests
- **Say:** *"Cross-site scripting — 7 XSS payloads tested. Blade's `{{ }}` escaping catches all unescaped output."*

### Step 4: Authentication & Authorization
- Point to auth bypass and IDOR tests
- **Say:** *"Authentication — unauthenticated users blocked from all protected routes. Authorization — non-members blocked from voting, commenting, and viewing private rooms."*

### Step 5: Mass Assignment
- Point to the mass assignment test
- **Say:** *"Mass assignment — `is_admin` is guarded and cannot be set via registration or profile update."*

### Step 6: Rate Limiting
- Point to the rate limiting test
- **Say:** *"Rate limiting — 5 login attempts per minute, 30 movie searches per minute. Prevents brute force attacks."*

### Step 7: API Security
- Show `config/sanctum.php`
- **Say:** *"API tokens expire after 7 days. WebSocket channels require room membership — no public channels."*

### Step 8: Production Settings
- Show `.env` with `APP_DEBUG=false`
- **Say:** *"Debug mode disabled in production — no stack traces leaked. Session encryption enabled."*

---

## 5. API DEMO — Show API (30 seconds)

### Step 1: API Routes
- Run in terminal: `php artisan route:list --path=api`
- **Say:** *"30 versioned API endpoints under `/api/v1` — auth, rooms, movies, votes, comments, invitations."*

### Step 2: API Resources
- Open `app/Http/Resources/RoomResource.php`
- **Say:** *"5 API Resource classes for consistent JSON responses. Form Request classes for validation."*

---

## Closing (15 seconds)

> "To summarize: the system scores Excellent across all criteria — all features work flawlessly with 166 passing tests, clean Laravel conventions with Git, professional responsive UI, comprehensive security with 34 dedicated tests, and real-time updates via WebSockets. Thank you."

---

## Quick Reference — Commands to Run During Demo

```bash
# Show tests passing
php artisan test

# Show security tests
php artisan test tests/Feature/SecurityTest.php

# Show git history
git log --oneline -15

# Show API routes
php artisan route:list --path=api

# Show database queries (optional - for code quality)
php artisan tinker --execute="DB::listen(function(\$q){echo \$q->sql.PHP_EOL;}); App\Models\MovieRoom::with('host','members','movies')->first();"
```

## Timing Summary

| Section | Duration |
|---------|----------|
| Opening | 0:30 |
| Functionality | 2:00 |
| Code Quality | 1:00 |
| UI/UX | 1:00 |
| Security | 1:30 |
| API | 0:30 |
| Closing | 0:15 |
| **Total** | **~6:45** |
