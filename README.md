<p align="center">
  <picture>
    <source media="(prefers-color-scheme: dark)" srcset="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg">
    <img alt="Movie Night Voting App" src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400">
  </picture>
</p>

# Movie Night Voting App

A real-time movie night voting platform built with **Laravel 12**, **Livewire 4**, **Alpine.js**, and **Tailwind CSS**. Create rooms, suggest movies, vote, and settle on what to watch — all in real time.

## Features

- **Room-based voting** — Create private or public rooms, invite friends, suggest movies
- **Real-time updates** — Votes, suggestions, and chat update instantly via Laravel Reverb WebSockets
- **Movie discovery** — Search TMDB API for movies to suggest, trending movies section
- **In-app notifications** — Bell icon with unread count, real-time Echo listener
- **Email notifications** — Invitations, winner announcements, vote/suggestion alerts via Gmail SMTP
- **Admin panel** — Dashboard with Chart.js stats, user/room management, soft-delete restore
- **API** — Full REST API with Sanctum token auth for rooms, movies, votes, comments, invitations

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | PHP 8.2, Laravel 12 |
| Frontend | Blade, Livewire 4, Alpine.js (bundled with Livewire), Tailwind CSS |
| Database | MySQL via XAMPP |
| Real-time | Laravel Reverb (self-hosted WebSocket on localhost:8080) |
| Auth | Laravel Breeze (email verification, password reset), Sanctum (API tokens) |
| APIs | TMDB API (movie search/details/trending), OMDb API (movie metadata) |
| Mail | Gmail SMTP |
| Queue | Sync (dev) / Database (prod) |

## Setup

```bash
cd C:/[Your Directory of choice]
composer install
npm install && npm run build
php artisan migrate
php artisan db:seed
php artisan storage:link
php artisan reverb:start          # real-time WebSocket
php artisan schedule:work          # auto-close scheduled rooms
```

### Environment

Copy `.env.example` to `.env` and configure:

- **Database** — MySQL credentials (`127.0.0.1:3306`, root/no password for XAMPP)
- **Reverb** — `BROADCAST_CONNECTION=reverb` with app ID, key, secret
- **Mail** — Gmail SMTP credentials
- **APIs** — `OMDB_API_KEY` and `TMDB_API_KEY`

## Testing

```bash
php artisan test    # 132 tests, 281 assertions
```

Tests cover unit services (VotingService, TmdbService), feature controllers (API, Auth, Admin, Livewire components), broadcasts, notifications, and scheduled commands.

## Architecture

### Models
- `User` — HasApiTokens, MustVerifyEmail, `is_admin` flag
- `MovieRoom` — host, members (pivot), movies (pivot), soft deletes, scheduled expiry
- `Movie` — sourced from OMDb or TMDB (`omdb_id` + `tmdb_id`), shared across rooms
- `MovieVote` — unique per room/movie/user with up/down vote type
- `Comment` — polymorphic to room or movie, soft deletes
- `Invitation` — email/code invite flow with expiration

### Services
- `OmdbService` — cached (1hr TTL), rate-limited, retry (3x), stale-cache fallback
- `TmdbService` — search/findMovie/trending via TMDB, 6hr cache, local fallback
- `RoomService` — create/join/leave/close/regenerate-code/transferHost
- `VotingService` — cast/remove vote, calculate winner, declare winner
- `InvitationService` — create/accept/decline/pending invitations

### Real-time
- Events: `VoteCast`, `MovieSuggested`, `RoomUpdated` — broadcast on public `room.{id}` channel
- Notifications: `RoomNotification` via `database` + `broadcast` + `mail` channels
- Client: `laravel-echo` + `pusher-js` configured in `resources/js/bootstrap.js`

## Livewire 4 Notes

- **Alpine is bundled with Livewire** — never import Alpine from npm. Register custom stores via `alpine:init` event in an inline `<script>` before `@livewireScripts`.
- **`wire:model` requires `.live`** — without `.live`, only local Alpine state updates; server round-trips (and `updatedX()` hooks) need the modifier.
- **Use `@livewireScripts`** (not `@livewireScriptConfig`) when assets are published to `public/vendor/livewire/`.

## Commands

- `rooms:check-winners` — scheduled every minute, closes rooms past `scheduled_at`, calculates winner

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
