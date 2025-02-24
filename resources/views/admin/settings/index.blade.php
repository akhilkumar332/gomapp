<x-app-layout>
    <div class="container mx-auto px-4 py-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Application Settings</h1>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Branding Settings -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold">Branding Settings</h2>
                </div>
                <div class="p-6">
                    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf

                        <div class="mb-4">
                            <label for="app_name" class="block text-sm font-medium text-gray-700 mb-1">Application Name</label>
                            <input type="text" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('app_name') border-red-500 @enderror" 
                                   id="app_name" 
                                   name="app_name" 
                                   value="{{ old('app_name', $settings['branding']['app_name'] ?? config('app.name')) }}">
                            @error('app_name')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="primary_color" class="block text-sm font-medium text-gray-700 mb-1">Primary Color</label>
                            <input type="color" 
                                   class="h-10 p-1 rounded border border-gray-300 @error('primary_color') border-red-500 @enderror" 
                                   id="primary_color" 
                                   name="primary_color" 
                                   value="{{ old('primary_color', $settings['branding']['primary_color'] ?? '#007bff') }}">
                            @error('primary_color')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="secondary_color" class="block text-sm font-medium text-gray-700 mb-1">Secondary Color</label>
                            <input type="color" 
                                   class="h-10 p-1 rounded border border-gray-300 @error('secondary_color') border-red-500 @enderror" 
                                   id="secondary_color" 
                                   name="secondary_color" 
                                   value="{{ old('secondary_color', $settings['branding']['secondary_color'] ?? '#6c757d') }}">
                            @error('secondary_color')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="logo" class="block text-sm font-medium text-gray-700 mb-1">Logo</label>
                            @if(isset($settings['branding']['logo_url']))
                                <div class="mb-2">
                                    <img src="{{ $settings['branding']['logo_url'] }}" 
                                         alt="Current Logo" 
                                         class="max-h-24 rounded">
                                </div>
                            @endif
                            <input type="file" 
                                   class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100 @error('logo') border-red-500 @enderror" 
                                   id="logo" 
                                   name="logo" 
                                   accept="image/*">
                            @error('logo')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="favicon" class="block text-sm font-medium text-gray-700 mb-1">Favicon</label>
                            @if(isset($settings['branding']['favicon_url']))
                                <div class="mb-2">
                                    <img src="{{ $settings['branding']['favicon_url'] }}" 
                                         alt="Current Favicon" 
                                         class="max-h-8 rounded">
                                </div>
                            @endif
                            <input type="file" 
                                   class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100 @error('favicon') border-red-500 @enderror" 
                                   id="favicon" 
                                   name="favicon" 
                                   accept="image/x-icon,image/png">
                            @error('favicon')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Update Branding
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
