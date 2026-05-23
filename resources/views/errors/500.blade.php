<x-app-layout>
    <x-slot name="title">Server Error</x-slot>
    <div class="max-w-lg mx-auto px-4 py-24 text-center">
        <h1 class="text-8xl font-bold text-netflix-accent mb-4">500</h1>
        <h2 class="text-2xl font-semibold text-white mb-2">Something Went Wrong</h2>
        <p class="text-gray-400 mb-8">An unexpected error occurred. Please try again later.</p>
        <a href="{{ route('dashboard') }}" class="inline-block bg-netflix-accent hover:bg-netflix-accent-hover text-white font-bold py-3 px-8 rounded-lg transition">
            Go to Dashboard
        </a>
    </div>
</x-app-layout>
