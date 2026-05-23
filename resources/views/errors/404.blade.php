<x-app-layout>
    <x-slot name="title">Page Not Found</x-slot>
    <div class="max-w-lg mx-auto px-4 py-24 text-center">
        <h1 class="text-8xl font-bold text-netflix-accent mb-4">404</h1>
        <h2 class="text-2xl font-semibold text-white mb-2">Page Not Found</h2>
        <p class="text-gray-400 mb-8">The page you're looking for doesn't exist or has been moved.</p>
        <a href="{{ route('dashboard') }}" class="inline-block bg-netflix-accent hover:bg-netflix-accent-hover text-white font-bold py-3 px-8 rounded-lg transition">
            Go to Dashboard
        </a>
    </div>
</x-app-layout>
