<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Help & Documentation
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Search Bar -->
            <div class="mb-8">
                <input 
                    type="text" 
                    id="searchInput"
                    placeholder="Search documentation..." 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <!-- Sidebar Navigation -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-lg shadow-sm p-6 sticky top-20">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Contents</h3>
                        <nav class="space-y-2">
                            <button 
                                onclick="showSection('getting-started')"
                                class="section-btn w-full text-left px-3 py-2 rounded-md text-sm font-medium transition-colors bg-blue-100 text-blue-900"
                            >
                                Getting Started
                            </button>
                            <button 
                                onclick="showSection('roles-permissions')"
                                class="section-btn w-full text-left px-3 py-2 rounded-md text-sm font-medium transition-colors text-gray-700 hover:bg-gray-100"
                            >
                                Roles & Permissions
                            </button>
                            <button 
                                onclick="showSection('contacts')"
                                class="section-btn w-full text-left px-3 py-2 rounded-md text-sm font-medium transition-colors text-gray-700 hover:bg-gray-100"
                            >
                                Contacts Module
                            </button>
                            <button 
                                onclick="showSection('tasks')"
                                class="section-btn w-full text-left px-3 py-2 rounded-md text-sm font-medium transition-colors text-gray-700 hover:bg-gray-100"
                            >
                                Tasks Module
                            </button>
                            <button 
                                onclick="showSection('projects')"
                                class="section-btn w-full text-left px-3 py-2 rounded-md text-sm font-medium transition-colors text-gray-700 hover:bg-gray-100"
                            >
                                Projects Module
                            </button>
                            <button 
                                onclick="showSection('notifications')"
                                class="section-btn w-full text-left px-3 py-2 rounded-md text-sm font-medium transition-colors text-gray-700 hover:bg-gray-100"
                            >
                                Notifications
                            </button>
                            <button 
                                onclick="showSection('workflows')"
                                class="section-btn w-full text-left px-3 py-2 rounded-md text-sm font-medium transition-colors text-gray-700 hover:bg-gray-100"
                            >
                                Common Workflows
                            </button>
                            <button 
                                onclick="showSection('troubleshooting')"
                                class="section-btn w-full text-left px-3 py-2 rounded-md text-sm font-medium transition-colors text-gray-700 hover:bg-gray-100"
                            >
                                Troubleshooting
                            </button>
                            <button 
                                onclick="showSection('faq')"
                                class="section-btn w-full text-left px-3 py-2 rounded-md text-sm font-medium transition-colors text-gray-700 hover:bg-gray-100"
                            >
                                FAQ
                            </button>
                        </nav>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="lg:col-span-3">
                    <!-- Getting Started -->
                    <section id="getting-started" class="section-content bg-white rounded-lg shadow-sm p-6 mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Getting Started</h2>
                        <div class="space-y-6">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">Login</h3>
                                <p class="text-gray-700">Navigate to the application and enter your email and password. Contact your administrator if you don't have an account.</p>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">First Time Setup</h3>
                                <ul class="list-disc pl-6 text-gray-700 space-y-2">
                                    <li>Add your first contact or import existing ones</li>
                                    <li>Create a task and assign it to team members</li>
                                    <li>Organize tasks by creating projects</li>
                                </ul>
                            </div>
                        </div>
                    </section>

                    <!-- Roles & Permissions -->
                    <section id="roles-permissions" class="section-content bg-white rounded-lg shadow-sm p-6 mb-8 hidden">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Roles & Permissions</h2>
                        <div class="space-y-6">
                            <div class="border-l-4 border-red-500 pl-4">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">Admin Role</h3>
                                <p class="text-gray-600 mb-3">Full system access. Can manage all content and users.</p>
                                <ul class="list-disc pl-6 text-gray-700 space-y-1">
                                    <li>Create, view, edit, delete all contacts</li>
                                    <li>Create, view, edit, delete all tasks</li>
                                    <li>Assign tasks to any team member</li>
                                    <li>Create and manage projects</li>
                                    <li>Invite new users</li>
                                </ul>
                            </div>
                            <div class="border-l-4 border-blue-500 pl-4">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">Editor Role</h3>
                                <p class="text-gray-600 mb-3">Can create and manage content but limited to own tasks.</p>
                                <ul class="list-disc pl-6 text-gray-700 space-y-1">
                                    <li>Create and edit contacts</li>
                                    <li>Create and manage own tasks</li>
                                    <li>Assign own tasks to others</li>
                                </ul>
                            </div>
                            <div class="border-l-4 border-green-500 pl-4">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">Viewer Role</h3>
                                <p class="text-gray-600 mb-3">Read-only access with limited actions.</p>
                                <ul class="list-disc pl-6 text-gray-700 space-y-1">
                                    <li>View all contacts (read-only)</li>
                                    <li>View assigned tasks</li>
                                    <li>Add comments to tasks</li>
                                    <li>Attach files to tasks</li>
                                </ul>
                            </div>
                        </div>
                    </section>

                    <!-- Contacts Module -->
                    <section id="contacts" class="section-content bg-white rounded-lg shadow-sm p-6 mb-8 hidden">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Contacts Module</h2>
                        <div class="space-y-8">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-3">Creating a Contact</h3>
                                <ol class="list-decimal pl-6 text-gray-700 space-y-2">
                                    <li>Go to <strong>Contacts</strong> → <strong>New Contact</strong></li>
                                    <li>Fill in the required field: <strong>Name</strong></li>
                                    <li>Add optional details</li>
                                    <li>Click <strong>Save</strong></li>
                                </ol>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-3">Importing Contacts</h3>
                                <ol class="list-decimal pl-6 text-gray-700 space-y-2">
                                    <li>Go to <strong>Contacts</strong> → <strong>Import</strong></li>
                                    <li>Select your CSV file</li>
                                    <li>Click <strong>Preview</strong> to verify data</li>
                                    <li>Click <strong>Import</strong> to complete</li>
                                </ol>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-3">Exporting Contacts</h3>
                                <ol class="list-decimal pl-6 text-gray-700 space-y-2">
                                    <li>Go to <strong>Contacts</strong></li>
                                    <li>Click <strong>Export</strong> button</li>
                                    <li>Download CSV file</li>
                                </ol>
                            </div>
                        </div>
                    </section>

                    <!-- Tasks Module -->
                    <section id="tasks" class="section-content bg-white rounded-lg shadow-sm p-6 mb-8 hidden">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Tasks Module</h2>
                        <div class="space-y-8">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-3">Creating a Task</h3>
                                <ol class="list-decimal pl-6 text-gray-700 space-y-2">
                                    <li>Go to <strong>Tasks</strong> → <strong>New Task</strong></li>
                                    <li>Enter task title (required)</li>
                                    <li>Add optional details</li>
                                    <li>Click <strong>Save</strong></li>
                                </ol>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-3">Task Status</h3>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="p-3 bg-gray-50 rounded border-l-4 border-gray-400">
                                        <p class="font-semibold">To Do</p>
                                        <p class="text-sm text-gray-600">Not started</p>
                                    </div>
                                    <div class="p-3 bg-blue-50 rounded border-l-4 border-blue-500">
                                        <p class="font-semibold">In Progress</p>
                                        <p class="text-sm text-gray-600">Being worked on</p>
                                    </div>
                                    <div class="p-3 bg-yellow-50 rounded border-l-4 border-yellow-500">
                                        <p class="font-semibold">In Review</p>
                                        <p class="text-sm text-gray-600">Waiting approval</p>
                                    </div>
                                    <div class="p-3 bg-green-50 rounded border-l-4 border-green-500">
                                        <p class="font-semibold">Done</p>
                                        <p class="text-sm text-gray-600">Completed</p>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-3">Assigning Tasks</h3>
                                <ol class="list-decimal pl-6 text-gray-700 space-y-2">
                                    <li>Open a task</li>
                                    <li>Click <strong>Assign</strong></li>
                                    <li>Select a team member</li>
                                    <li>Team member receives notification</li>
                                </ol>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-3">Kanban Board</h3>
                                <ol class="list-decimal pl-6 text-gray-700 space-y-2">
                                    <li>Go to <strong>Tasks</strong> → <strong>Board</strong></li>
                                    <li>See tasks organized by status</li>
                                    <li>Drag tasks to update status</li>
                                </ol>
                            </div>
                        </div>
                    </section>

                    <!-- Projects Module -->
                    <section id="projects" class="section-content bg-white rounded-lg shadow-sm p-6 mb-8 hidden">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Projects Module</h2>
                        <div class="space-y-8">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-3">Creating a Project</h3>
                                <ol class="list-decimal pl-6 text-gray-700 space-y-2">
                                    <li>Go to <strong>Projects</strong> → <strong>New Project</strong></li>
                                    <li>Fill in project name (required)</li>
                                    <li>Add optional details</li>
                                    <li>Click <strong>Save</strong></li>
                                </ol>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-3">Adding Tasks to Projects</h3>
                                <p class="text-gray-700">Select the project from the <strong>Project</strong> dropdown when creating or editing a task.</p>
                            </div>
                        </div>
                    </section>

                    <!-- Notifications -->
                    <section id="notifications" class="section-content bg-white rounded-lg shadow-sm p-6 mb-8 hidden">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Notifications</h2>
                        <div class="space-y-6">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-3">Types of Notifications</h3>
                                <ul class="list-disc pl-6 text-gray-700 space-y-2">
                                    <li><strong>Task Assigned:</strong> You've been assigned a task</li>
                                    <li><strong>Task Commented:</strong> Someone commented on your task</li>
                                    <li><strong>Task Status Changed:</strong> Task status updated</li>
                                    <li><strong>File Attached:</strong> File added to a task</li>
                                    <li><strong>Mention:</strong> Someone mentioned you</li>
                                </ul>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-3">Managing Notifications</h3>
                                <ol class="list-decimal pl-6 text-gray-700 space-y-2">
                                    <li>Click the <strong>Bell icon</strong> in header</li>
                                    <li>View notifications</li>
                                    <li>Mark as read or delete</li>
                                </ol>
                            </div>
                        </div>
                    </section>

                    <!-- Workflows -->
                    <section id="workflows" class="section-content bg-white rounded-lg shadow-sm p-6 mb-8 hidden">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Common Workflows</h2>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Task Completion Workflow</h3>
                            <div class="space-y-4">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <div class="flex items-center justify-center h-8 w-8 rounded-full bg-blue-600 text-white font-semibold">1</div>
                                    </div>
                                    <div class="ml-4">
                                        <h4 class="font-semibold text-gray-900">Login</h4>
                                        <p class="text-gray-600 text-sm">Enter credentials and access application</p>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <div class="flex items-center justify-center h-8 w-8 rounded-full bg-blue-600 text-white font-semibold">2</div>
                                    </div>
                                    <div class="ml-4">
                                        <h4 class="font-semibold text-gray-900">Create/View Contact</h4>
                                        <p class="text-gray-600 text-sm">Add new or locate existing contact</p>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <div class="flex items-center justify-center h-8 w-8 rounded-full bg-blue-600 text-white font-semibold">3</div>
                                    </div>
                                    <div class="ml-4">
                                        <h4 class="font-semibold text-gray-900">Create Task</h4>
                                        <p class="text-gray-600 text-sm">New task with title and details</p>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <div class="flex items-center justify-center h-8 w-8 rounded-full bg-blue-600 text-white font-semibold">4</div>
                                    </div>
                                    <div class="ml-4">
                                        <h4 class="font-semibold text-gray-900">Assign Task</h4>
                                        <p class="text-gray-600 text-sm">Assign to team member</p>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <div class="flex items-center justify-center h-8 w-8 rounded-full bg-blue-600 text-white font-semibold">5</div>
                                    </div>
                                    <div class="ml-4">
                                        <h4 class="font-semibold text-gray-900">Work on Task</h4>
                                        <p class="text-gray-600 text-sm">Update status, add comments, attach files</p>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <div class="flex items-center justify-center h-8 w-8 rounded-full bg-blue-600 text-white font-semibold">6</div>
                                    </div>
                                    <div class="ml-4">
                                        <h4 class="font-semibold text-gray-900">Complete Task</h4>
                                        <p class="text-gray-600 text-sm">Change status to Done</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Troubleshooting -->
                    <section id="troubleshooting" class="section-content bg-white rounded-lg shadow-sm p-6 mb-8 hidden">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Troubleshooting</h2>
                        <div class="space-y-6">
                            <div class="border rounded-lg p-4 bg-red-50">
                                <h3 class="text-lg font-semibold text-red-900 mb-2">Permission Denied</h3>
                                <p class="text-red-800"><strong>Solution:</strong> Check your role and verify permissions. Contact administrator.</p>
                            </div>
                            <div class="border rounded-lg p-4 bg-yellow-50">
                                <h3 class="text-lg font-semibold text-yellow-900 mb-2">Item Not Appearing</h3>
                                <p class="text-yellow-800"><strong>Solution:</strong> Refresh page, check filters, verify permissions.</p>
                            </div>
                            <div class="border rounded-lg p-4 bg-blue-50">
                                <h3 class="text-lg font-semibold text-blue-900 mb-2">Upload Failed</h3>
                                <p class="text-blue-800"><strong>Solution:</strong> Check file size (max 10MB) and format.</p>
                            </div>
                        </div>
                    </section>

                    <!-- FAQ -->
                    <section id="faq" class="section-content bg-white rounded-lg shadow-sm p-6 mb-8 hidden">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">FAQ</h2>
                        <div class="space-y-4">
                            <div class="border rounded-lg">
                                <button onclick="toggleFaq(this)" class="w-full px-4 py-3 text-left flex justify-between items-center hover:bg-gray-50">
                                    <span class="font-semibold text-gray-900">Who can invite new users?</span>
                                    <span class="text-gray-500">+</span>
                                </button>
                                <div class="faq-answer hidden px-4 py-3 border-t bg-gray-50 text-gray-700">
                                    Only Admin users can invite new team members through Admin > Invitations.
                                </div>
                            </div>
                            <div class="border rounded-lg">
                                <button onclick="toggleFaq(this)" class="w-full px-4 py-3 text-left flex justify-between items-center hover:bg-gray-50">
                                    <span class="font-semibold text-gray-900">Can I change my password?</span>
                                    <span class="text-gray-500">+</span>
                                </button>
                                <div class="faq-answer hidden px-4 py-3 border-t bg-gray-50 text-gray-700">
                                    Yes, go to Profile > Change Password.
                                </div>
                            </div>
                            <div class="border rounded-lg">
                                <button onclick="toggleFaq(this)" class="w-full px-4 py-3 text-left flex justify-between items-center hover:bg-gray-50">
                                    <span class="font-semibold text-gray-900">What happens if I delete a contact?</span>
                                    <span class="text-gray-500">+</span>
                                </button>
                                <div class="faq-answer hidden px-4 py-3 border-t bg-gray-50 text-gray-700">
                                    Deleted contacts cannot be recovered. Only Admins can delete contacts.
                                </div>
                            </div>
                            <div class="border rounded-lg">
                                <button onclick="toggleFaq(this)" class="w-full px-4 py-3 text-left flex justify-between items-center hover:bg-gray-50">
                                    <span class="font-semibold text-gray-900">Can I export my data?</span>
                                    <span class="text-gray-500">+</span>
                                </button>
                                <div class="faq-answer hidden px-4 py-3 border-t bg-gray-50 text-gray-700">
                                    Yes, go to Contacts and click Export to download as CSV.
                                </div>
                            </div>
                            <div class="border rounded-lg">
                                <button onclick="toggleFaq(this)" class="w-full px-4 py-3 text-left flex justify-between items-center hover:bg-gray-50">
                                    <span class="font-semibold text-gray-900">Can tasks be assigned to multiple people?</span>
                                    <span class="text-gray-500">+</span>
                                </button>
                                <div class="faq-answer hidden px-4 py-3 border-t bg-gray-50 text-gray-700">
                                    Currently, tasks are assigned to one person. Mention others in comments for collaboration.
                                </div>
                            </div>
                            <div class="border rounded-lg">
                                <button onclick="toggleFaq(this)" class="w-full px-4 py-3 text-left flex justify-between items-center hover:bg-gray-50">
                                    <span class="font-semibold text-gray-900">Do I need to create a project for tasks?</span>
                                    <span class="text-gray-500">+</span>
                                </button>
                                <div class="faq-answer hidden px-4 py-3 border-t bg-gray-50 text-gray-700">
                                    No, projects are optional. You can create tasks independently.
                                </div>
                            </div>
                            <div class="border rounded-lg">
                                <button onclick="toggleFaq(this)" class="w-full px-4 py-3 text-left flex justify-between items-center hover:bg-gray-50">
                                    <span class="font-semibold text-gray-900">Is there mobile support?</span>
                                    <span class="text-gray-500">+</span>
                                </button>
                                <div class="faq-answer hidden px-4 py-3 border-t bg-gray-50 text-gray-700">
                                    Yes, the application is responsive and works on mobile devices.
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showSection(sectionId) {
            // Hide all sections
            document.querySelectorAll('.section-content').forEach(el => {
                el.classList.add('hidden');
            });

            // Show selected section
            document.getElementById(sectionId).classList.remove('hidden');

            // Update button styles
            document.querySelectorAll('.section-btn').forEach(btn => {
                btn.classList.remove('bg-blue-100', 'text-blue-900');
                btn.classList.add('text-gray-700', 'hover:bg-gray-100');
            });

            // Highlight active button
            event.target.classList.remove('text-gray-700', 'hover:bg-gray-100');
            event.target.classList.add('bg-blue-100', 'text-blue-900');
        }

        function toggleFaq(button) {
            const answer = button.nextElementSibling;
            answer.classList.toggle('hidden');
            const icon = button.querySelector('span:last-child');
            icon.textContent = answer.classList.contains('hidden') ? '+' : '−';
        }

        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            document.querySelectorAll('.section-btn').forEach(btn => {
                const text = btn.textContent.toLowerCase();
                btn.style.display = text.includes(searchTerm) ? 'block' : 'none';
            });
        });
    </script>
</x-app-layout>