@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto py-8">
    <div class="flex" style="align-items: center; justify-content: center">
        <img src="https://i.imgur.com/NjCXThW.png" style="width: 30%; margin-right: 30px;">
        <div>
            <h1 class="text-2xl font-bold text-black dark:text-white"> Welcome to Senti It! </h1>
            <p class="text-gray-700 dark:text-gray-300"> 
                Seek out whether the text is Positive, Negative, or Neutral with Senti It!
                <br>
                Start finding out by creating a group below.
            </p>
        </div>
    </div>

    <hr class="mb-5 mt-6">

    <h1 class="text-2xl font-bold text-black dark:text-white">Sentiment Groups</h1>
    <p class="text-gray-700 dark:text-gray-300 mb-6"> Categorize your texts and their results via groups. </p>

    <!-- Create Group Form -->
    <div class="mb-8">
        <form method="POST" action="{{ route('groups.store') }}" class="flex items-center space-x-4">
            @csrf
            <input 
                type="text" 
                name="name" 
                placeholder="Group name" 
                required 
                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg dark:bg-gray-800 dark:border-gray-700 dark:text-white"
            >
            <button 
                type="submit" 
                class="px-4 py-2 bg-blue-500 text-white font-semibold rounded-lg hover:bg-blue-600 dark:bg-blue-700 dark:hover:bg-blue-600"
            >
                Create Group
            </button>
        </form>
    </div>

    <!-- List of Groups -->
    @if ($groups->isEmpty())
        <p class="text-gray-700 dark:text-gray-300">No Groups are found.</p>
    @else
        <ul class="space-y-4">
            @foreach ($groups as $group)
                <li class="p-4 border border-gray-300 rounded-lg dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center justify-between">
                        <!-- Group Name -->
                        <div>
                            <a 
                                href="{{ route('groups.show', $group) }}" 
                                class="text-lg font-medium text-blue-600 dark:text-blue-400 hover:underline"
                            >
                                {{ $group->name }}
                            </a>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center space-x-4">
                            <!-- Edit Button -->
                            <button 
                                onclick="toggleEditForm({{ $group->id }})" 
                                class="text-sm text-gray-600 dark:text-gray-300 hover:text-blue-500"
                            >
                                Edit
                            </button>

                            <!-- Delete Button -->
                            <form method="POST" action="{{ route('groups.destroy', $group) }}">
                                @csrf
                                @method('DELETE')
                                <button 
                                    type="submit" 
                                    class="text-sm text-red-500 hover:text-red-700"
                                >
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Rename Form (Hidden by Default) -->
                    <form 
                        method="POST" 
                        action="{{ route('groups.update', $group) }}" 
                        id="edit-form-{{ $group->id }}" 
                        class="mt-4 space-y-2 hidden"
                    >
                        @csrf
                        @method('PUT')
                        <input 
                            type="text" 
                            name="name" 
                            value="{{ $group->name }}" 
                            required 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg dark:bg-gray-800 dark:border-gray-700 dark:text-white"
                        >
                        <div class="flex justify-end space-x-4">
                            <button 
                                type="submit" 
                                class="px-4 py-2 bg-green-500 text-white font-semibold rounded-lg hover:bg-green-600"
                            >
                                Save
                            </button>
                            <button 
                                type="button" 
                                onclick="cancelEdit({{ $group->id }})" 
                                class="px-4 py-2 bg-gray-500 text-white font-semibold rounded-lg hover:bg-gray-600"
                            >
                                Cancel
                            </button>
                        </div>
                    </form>
                </li>
            @endforeach
        </ul>
    @endif
</div>

<script>
    function toggleEditForm(groupId) {
        // Hide group name link
        const groupLink = document.querySelector(`a[href$="/groups/${groupId}"]`);
        if (groupLink) {
            groupLink.style.display = 'none';
        }

        // Show the edit form
        const form = document.getElementById(`edit-form-${groupId}`);
        if (form) {
            form.style.display = 'block';
        }
    }

    function cancelEdit(groupId) {
        // Show group name link
        const groupLink = document.querySelector(`a[href$="/groups/${groupId}"]`);
        if (groupLink) {
            groupLink.style.display = 'inline';
        }

        // Hide the edit form
        const form = document.getElementById(`edit-form-${groupId}`);
        if (form) {
            form.style.display = 'none';
        }
    }
</script>
@endsection
