<x-app-layout>
    <div class="max-w-3xl mx-auto px-5 py-8 sm:px-8 lg:px-12 space-y-6">
        <div class="rounded-[1.5rem] border border-white/10 bg-surface p-6 sm:p-8">
            @include('profile.partials.update-profile-information-form')
        </div>

        <div class="rounded-[1.5rem] border border-white/10 bg-surface p-6 sm:p-8">
            @include('profile.partials.update-password-form')
        </div>

        <div class="rounded-[1.5rem] border border-white/10 bg-surface p-6 sm:p-8">
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</x-app-layout>
