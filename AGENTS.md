# Movie Night Voting App

## Stack
- **Laravel 12** (PHP 8.2.12)
- **PostgreSQL** via Supabase (hosted, `db.jazlniemhbwynynongsh.supabase.co`)
- **TailwindCSS** dark theme (`#09090b` bg, `#ef4444` accent, `#171719` cards)
- **Blade** + **Alpine.js** (bundled with Livewire, never import separately from npm) + **Livewire 4** frontend
- **Laravel Reverb** self-hosted WebSocket (localhost:8080)
- **Laravel Sanctum** API auth
- **OMDb API** (key: `998de2c6`), **TMDB API** (key: `5b973d742e193f758a69a55bd043bd02`)
- **Gmail SMTP** (`drewpy69420@gmail.com`) for email + in-app notifications

## Setup
```
cd R:\Projects\Integrative\movie-night-voting
composer install
npm install && npm run build
php artisan migrate
php artisan db:seed
php artisan storage:link
php artisan reverb:start          # real-time WebSocket
php artisan schedule:work          # auto-close scheduled rooms
```
**Note:** `QUEUE_CONNECTION=sync` in `.env` — everything processes inline (no worker needed). For production, switch to `database` + `php artisan queue:work`.

## Key Architecture

### Models & Relationships
- `User` — HasApiTokens, MustVerifyEmail, is_admin flag
- `MovieRoom` — host, members (pivot: room_members), movies (pivot: room_movies), soft deletes
- `Movie` — OMDb or TMDB sourced data (`omdb_id` + `tmdb_id`), shared across rooms
- `MovieVote` — unique(room_id, movie_id, user_id)
- `Comment` — polymorphic to room/movie, soft deletes
- `Invitation` — email/code invite flow

### Services
- `OmdbService` — cached (1hr TTL), rate-limited (5/sec), retry (3x), stale-cache fallback
- `TmdbService` — search + findMovie + trending via TMDB, local fallback, 6hr cache
- `RoomService` — create/join/leave/close/regenerate-code/transferHost
- `VotingService` — cast/remove vote, calculate winner, declare winner
- `InvitationService` — create/accept/decline/pending invitations

### Real-time & Notifications
- Events: `VoteCast`, `MovieSuggested`, `RoomUpdated` — broadcast on public `room.{id}` channel
- Auth: Private channel auth via `routes/channels.php` checking room membership
- Client: `laravel-echo` + `pusher-js` configured in `resources/js/bootstrap.js`
- **In-app notifications**: `RoomNotification` with `database` + `broadcast` + `mail` channels
- Types: `vote_received`, `new_member_joined`, `invitation_created`, `invitation_accepted`, `winner_declared`
- `NotificationBell` Livewire component in header — bell icon with unread badge, dropdown list, mark read
- Echo listener on private `App.Models.User.{id}` channel for real-time notification updates
- Email via Gmail SMTP on every notification (queued via `ShouldQueue`)

### .env Essentials
```
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=880103
REVERB_APP_KEY=zcedp7g4ooh7cztf8j49
REVERB_APP_SECRET=1otxjkdzllqovh38jrgr
REVERB_HOST="localhost"
REVERB_PORT=8080
REVERB_SCHEME=http

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=drewpy69420@gmail.com
MAIL_PASSWORD="acur lpav nuff zych"
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="drewpy69420@gmail.com"

OMDB_API_KEY=998de2c6
TMDB_API_KEY=5b973d742e193f758a69a55bd043bd02
```

## Routes
- **Web** (`routes/web.php`): dashboard, rooms CRUD, profile, admin, invitations
- **API** (`routes/api.php`): v1 — auth, rooms, movies, votes, comments, invitations (Sanctum)
- **Auth**: Breeze scaffold with email verification + password reset

## Testing
```
php artisan test    # 132 tests, 281 assertions
```
Tests cover: unit (VotingService), feature (API controllers, Livewire components, auth, admin, invitations, broadcasts, room scheduling)

## Commands
- `rooms:check-winners` — scheduled every minute, closes rooms past scheduled_at, calculates winner

## Admin
- `AdminMiddleware` gates `/admin/*` routes
- Dashboard with Chart.js (7-day room creation), user/room management
- Soft-delete restore for rooms and comments

## Livewire 4 + Alpine Setup

### Alpine Initialization
- **Never import Alpine from npm.** Livewire 4 bundles its own Alpine via `public/vendor/livewire/livewire.js`.
- `app.js` should only do `import './bootstrap'` — no `import Alpine from 'alpinejs'` or `Alpine.start()`.
- Register custom Alpine stores via an inline `<script>` in the layout **before** `@livewireScripts`:
  ```html
  <script>document.addEventListener('alpine:init', () => Alpine.store('sidebar', {...}))</script>
  ```
  The `alpine:init` event fires at `livewire.js:1737` before DOM expressions evaluate.

### wire:model Requires .live
- In Livewire 4, `wire:model` only updates local Alpine state by default.
- To trigger a server round-trip (and `updatedX()` hooks), you **must** add the `.live` modifier:
  - ✅ `wire:model.live.debounce.300ms="query"`
  - ❌ `wire:model.debounce.300ms="query"` — no server update

### Livewire Scripts
- Use `@livewireScripts` when assets are published (reads from `public/vendor/livewire/`).
- Do NOT use `@livewireScriptConfig` with published assets — it skips DOMContentLoaded initialization, leaving Alpine unstarted.

## Future Work

### Trending Movies
- **Goal**: Show popular/trending movies on dashboard or welcome page, sourced from OMDb or TMDB
- **Approach**: 
  - Add a `trending` endpoint in `OmdbService` that calls OMDb's search with predefined queries or TMDB trending API
  - Cache results for 6+ hours since trends change slowly
  - Display as a horizontal scrollable section on dashboard
- **Considerations**: OMDb has no dedicated trending endpoint; use TMDB's `/trending/movie/week` as alternative or supplement with a second API key

### Additional Roadmap
- **Docker setup** — containerize app + Reverb for consistent dev/deploy
- **Email templates** — HTML email views already published, can customize further
- **API pagination** — add cursor pagination to large collections
- **Search/filter admin** — search users, rooms, comments in admin panel
- **Rate limiting UI feedback** — show remaining attempts on login/register
- **CI pipeline** — GitHub Actions for test suite on push
- **Dark/light mode toggle** — currently dark-only
- **OAuth login** — Google/GitHub socialite integration

### Bugs Fixed (Session 2026-05-23)
1. **Sidebar `$store.sidebar` crash** — Fixed by removing duplicate Alpine import from `app.js`. Livewire 4 bundles its own Alpine; importing a second copy from npm caused `Cannot read properties of undefined (reading 'expanded')`. Moved store registration to an inline `alpine:init` script in layouts.
2. **Search "nothing happens"** — Fixed by adding `.live` modifier on `wire:model` in `movie-search.blade.php`. Livewire 4 requires `.live` for server round-trips.
3. **Suggest movie fails silently** — Fixed by running pending migration `2026_05_23_115952_add_tmdb_id_to_movies_table.php`. `TmdbService::findMovie()` calls `Movie::updateOrCreate(['tmdb_id' => ...])` but the column didn't exist in the schema.
