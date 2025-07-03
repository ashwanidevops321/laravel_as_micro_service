<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <!-- Include Tailwind CSS via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        /* Custom CSS for profile page */
        .profile-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
        }
        .profile-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 2rem;
        }
        .avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #e5e7eb;
        }
        .edit-form {
            display: none;
        }
        .edit-form.active {
            display: block;
        }
        .profile-details {
            display: block;
        }
        .profile-details.hidden {
            display: none;
        }
        .btn {
            transition: background-color 0.3s, transform 0.2s;
        }
        .btn:hover {
            transform: scale(1.05);
        }
        .error {
            color: red;
            font-size: 0.875rem;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="profile-container">
        <!-- Profile Card -->
        <div class="profile-card">
            <div class="flex flex-col items-center md:flex-row md:items-start">
                <!-- Avatar Section -->
                <div class="flex-shrink-0">
                    <img src="{{ asset('images/default-avatar.jpg') }}" alt="Profile Avatar" class="avatar" id="avatar-img">
                    <input type="file" id="avatar-upload" class="mt-2" accept="image/*" style="display: none;">
                    <button onclick="document.getElementById('avatar-upload').click()" class="mt-2 bg-blue-500 text-white px-4 py-2 rounded btn">Change Avatar</button>
                </div>
                <!-- Profile Details Section -->
                <div class="mt-4 md:mt-0 md:ml-6 flex-1">
                    <div class="profile-details" id="profile-details">
                        <h2 class="text-2xl font-bold text-gray-800">{{ Auth::user()->name ?? 'John Doe' }}</h2>
                        <p class="text-gray-600">{{ Auth::user()->email ?? 'john.doe@example.com' }}</p>
                        <p class="text-gray-600">Bio: <span id="bio-text">{{ $user->bio ?? 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.' }}</span></p>
                        <p class="text-gray-600">Location: <span id="location-text">{{ $user->location ?? 'New York, USA' }}</span></p>
                        <p class="text-gray-600">
                        Joined: 
                        {{ Auth::user()?->created_at ? Auth::user()->created_at->format('M d, Y') : 'Jan 1, 2023' }}
                        </p>
                        <button onclick="toggleEditForm()" class="mt-4 bg-green-500 text-white px-4 py-2 rounded btn">Edit Profile</button>
                    </div>
                    <!-- Edit Form Section -->
                    <form id="edit-form" class="edit-form" method="POST" action="{{ route('profile.update') }}">
                        @csrf
                        @method('PATCH')
                        <div class="mb-4">
                            <label for="name" class="block text-gray-700">Name</label>
                            <input type="text" id="name" name="name" value="{{ Auth::user()->name ?? 'John Doe' }}" class="w-full border rounded px-3 py-2">
                            <span id="name-error" class="error"></span>
                        </div>
                        <div class="mb-4">
                            <label for="email" class="block text-gray-700">Email</label>
                            <input type="email" id="email" name="email" value="{{ Auth::user()->email ?? 'john.doe@example.com' }}" class="w-full border rounded px-3 py-2">
                            <span id="email-error" class="error"></span>
                        </div>
                        <div class="mb-4">
                            <label for="bio" class="block text-gray-700">Bio</label>
                            <textarea id="bio" name="bio" class="w-full border rounded px-3 py-2">{{ $user->bio ?? 'Lorem ipsum dolor sit amet.' }}</textarea>
                            <span id="bio-error" class="error"></span>
                        </div>
                        <div class="mb-4">
                            <label for="location" class="block text-gray-700">Location</label>
                            <input type="text" id="location" name="location" value="{{ $user->location ?? 'New York, USA' }}" class="w-full border rounded px-3 py-2">
                            <span id="location-error" class="error"></span>
                        </div>
                        <div class="flex space-x-2">
                            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded btn">Save Changes</button>
                            <button type="button" onclick="toggleEditForm()" class="bg-gray-500 text-white px-4 py-2 rounded btn">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle between profile details and edit form
        function toggleEditForm() {
            const form = document.getElementById('edit-form');
            const details = document.getElementById('profile-details');
            form.classList.toggle('active');
            details.classList.toggle('hidden');
        }

        // Avatar upload preview
        document.getElementById('avatar-upload').addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('avatar-img').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });

        // Form validation
        document.getElementById('edit-form').addEventListener('submit', function(event) {
            event.preventDefault();
            let isValid = true;
            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;
            const bio = document.getElementById('bio').value;
            const location = document.getElementById('location').value;

            // Clear previous errors
            document.querySelectorAll('.error').forEach(error => error.textContent = '');

            // Basic validation
            if (!name || name.length < 2) {
                document.getElementById('name-error').textContent = 'Name must be at least 2 characters long.';
                isValid = false;
            }
            if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                document.getElementById('email-error').textContent = 'Please enter a valid email address.';
                isValid = false;
            }
            if (bio.length > 500) {
                document.getElementById('bio-error').textContent = 'Bio cannot exceed 500 characters.';
                isValid = false;
            }
            if (!location) {
                document.getElementById('location-error').textContent = 'Location is required.';
                isValid = false;
            }

            if (isValid) {
                // Simulate form submission (replace with actual submission logic)
                alert('Profile updated successfully!');
                toggleEditForm();
                // Update displayed details
                document.getElementById('bio-text').textContent = bio;
                document.getElementById('location-text').textContent = location;
            }
        });
    </script>
</body>
</html>