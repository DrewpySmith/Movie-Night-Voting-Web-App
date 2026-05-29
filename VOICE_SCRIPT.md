# Voice Script for Google AI Studio

## Scene
A university student presenting their software engineering project to a panel of professors. The setting is a classroom presentation with a screen showing the application. The tone is confident, clear, and professional but approachable — like a well-prepared student who genuinely enjoys what they built.

## Sample Context
The speaker is presenting a short lesson video about a Movie Night Voting Web Application they developed. They are demonstrating the system's features, code quality, design, and security to earn full marks on a grading rubric. The delivery should be natural, well-paced, and engaging — not robotic or overly rehearsed. Pauses should feel intentional. Emphasis should land on key technical terms and achievements.

## Text

Hi, I'm Drew. This is the Movie Night Voting App — a real-time platform where groups discover, suggest, and vote on movies together. Built with Laravel twelve, Livewire four, PostgreSQL on Supabase, and self-hosted WebSockets via Laravel Reverb. Let me walk you through it.

Starting with authentication. The app uses Laravel Breeze with full email verification and password reset. Users register with their email, verify their account, and log in securely. Passwords are hashed with bcrypt at twelve rounds, and sessions are stored in the database with encryption.

Once logged in, the user sees the dashboard. It displays trending movies from the TMDB API with a six-hour cache, their rooms, public rooms they can join, and any pending invitations. The trending section uses a dedicated Livewire component that fetches data asynchronously.

Now let me create a room. Rooms can be public or private, with optional scheduled expiry. Each room gets an auto-generated six-character invite code. The host can share this code or send email invitations directly from the app.

Inside the room, I can search for movies using the TMDB API. The search is debounced at three hundred milliseconds, so results appear as I type without overwhelming the API. When I find a movie I like, one click suggests it to the room.

Voting is simple. Upvote or downvote any suggested movie. The score updates in real-time for all members — no page refresh needed. This works through Laravel Reverb WebSockets, which broadcast vote events to all connected room members instantly.

Let me show the real-time capability. I'll open a second browser window as a different user, join the same room, and cast a vote. As you can see, the first user sees the vote update immediately. That's WebSocket-powered real-time communication.

The room also has a built-in chat. Members can comment on movies or discuss what to watch. Comments appear in real-time and can be deleted by the owner or an admin.

Notifications are another key feature. The bell icon in the header shows an unread count. Members get notified when someone votes, joins, or when the host declares a winner. Notifications are delivered in-app, via broadcast, and through email using Gmail SMTP.

When the host is ready, they can close the room and declare a winner. All members receive a notification with the result.

Now let me talk about code quality. The application follows Laravel conventions with a clean service layer. Business logic lives in five dedicated services: VotingService, RoomService, InvitationService, OmdbService, and TmdbService. Controllers stay thin and focused on handling requests.

Validation is handled by six Form Request classes. Each one defines typed rules for its specific endpoint. This keeps validation logic separate from controllers and makes it reusable.

The data model uses eight Eloquent models with expressive relationships. MovieRoom has hosts, members, movies, votes, and comments. Movies can be sourced from OMDb or TMDB. We use PHP eight-point-one backed enums for type safety across the codebase — VoteType, RoomStatus, NotificationType, and more.

The project has active Git history with conventional commits. Every change is tracked and documented, making it easy to understand the development process.

For testing, we have one hundred and sixty-six tests with three hundred and eighty-seven assertions. This covers unit tests for services, feature tests for API controllers and Livewire components, broadcast tests, and thirty-four dedicated security tests.

Speaking of security — let me show you what we tested. The security test suite covers eight attack vectors. SQL injection is tested across five endpoints with eight different payloads. Laravel's parameter binding and Eloquent ORM prevent all injection attempts.

Cross-site scripting is tested with seven XSS payloads against room titles, comments, and registration names. Blade's curly brace escaping catches all unescaped output.

Authentication and authorization are thoroughly tested. Unauthenticated users are blocked from all protected routes. Non-members cannot vote, comment, or view private rooms. The is_admin field is guarded and cannot be set via registration or profile update.

Rate limiting protects against brute force attacks. Five login attempts per minute, thirty movie searches per minute. API tokens expire after seven days through Laravel Sanctum. WebSocket channels require room membership — there are no public channels.

The application exposes thirty versioned API endpoints under API version one. Five API Resource classes ensure consistent JSON responses. The API supports full CRUD operations for rooms, movies, votes, comments, and invitations.

To summarize: the system delivers excellent functionality with all features working flawlessly, clean code following Laravel conventions, a professional responsive design, comprehensive security with thirty-four dedicated tests, and real-time updates via WebSockets. Thank you.
