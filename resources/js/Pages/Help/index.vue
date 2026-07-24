<template>
  <AppLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Help & Documentation
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Search Bar -->
        <div class="mb-8">
          <input 
            v-model="searchQuery" 
            type="text" 
            placeholder="Search documentation..." 
            class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
          <!-- Table of Contents Sidebar -->
          <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-sm p-6 sticky top-20">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Contents</h3>
              <nav class="space-y-2">
                <button 
                  v-for="section in filteredSections" 
                  :key="section.id"
                  @click="activeSection = section.id"
                  :class="[
                    'w-full text-left px-3 py-2 rounded-md text-sm font-medium transition-colors',
                    activeSection === section.id 
                      ? 'bg-blue-100 text-blue-900' 
                      : 'text-gray-700 hover:bg-gray-100'
                  ]"
                >
                  {{ section.title }}
                </button>
              </nav>
            </div>
          </div>

          <!-- Main Content -->
          <div class="lg:col-span-3">
            <!-- Getting Started Section -->
            <section 
              v-if="activeSection === 'getting-started'" 
              class="bg-white rounded-lg shadow-sm p-8 mb-8"
            >
              <h2 class="text-2xl font-bold text-gray-900 mb-6">Getting Started</h2>
              
              <div class="space-y-6">
                <div>
                  <h3 class="text-lg font-semibold text-gray-900 mb-2">Login</h3>
                  <p class="text-gray-700">Navigate to the application and enter your email and password. If you don't have an account, contact your administrator to receive an invitation.</p>
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

            <!-- Roles & Permissions Section -->
            <section 
              v-if="activeSection === 'roles-permissions'" 
              class="bg-white rounded-lg shadow-sm p-8 mb-8"
            >
              <h2 class="text-2xl font-bold text-gray-900 mb-6">Roles & Permissions</h2>
              
              <div class="space-y-6">
                <!-- Admin Role -->
                <div class="border-l-4 border-red-500 pl-4">
                  <h3 class="text-lg font-semibold text-gray-900 mb-2 flex items-center">
                    <span class="inline-block w-3 h-3 bg-red-500 rounded-full mr-2"></span>
                    Admin Role
                  </h3>
                  <p class="text-gray-600 mb-3">Full system access. Can manage all content and users.</p>
                  <ul class="list-disc pl-6 text-gray-700 space-y-1">
                    <li>Create, view, edit, delete all contacts</li>
                    <li>Create, view, edit, delete all tasks</li>
                    <li>Assign tasks to any team member</li>
                    <li>Create and manage projects</li>
                    <li>Import/export contacts</li>
                    <li>Invite new users</li>
                  </ul>
                </div>

                <!-- Editor Role -->
                <div class="border-l-4 border-blue-500 pl-4">
                  <h3 class="text-lg font-semibold text-gray-900 mb-2 flex items-center">
                    <span class="inline-block w-3 h-3 bg-blue-500 rounded-full mr-2"></span>
                    Editor Role
                  </h3>
                  <p class="text-gray-600 mb-3">Can create and manage content but limited to own tasks.</p>
                  <ul class="list-disc pl-6 text-gray-700 space-y-1">
                    <li>Create and edit contacts</li>
                    <li>Create and manage own tasks</li>
                    <li>Assign own tasks to others</li>
                    <li>Import/export contacts</li>
                    <li>Create and manage projects</li>
                  </ul>
                </div>

                <!-- Viewer Role -->
                <div class="border-l-4 border-green-500 pl-4">
                  <h3 class="text-lg font-semibold text-gray-900 mb-2 flex items-center">
                    <span class="inline-block w-3 h-3 bg-green-500 rounded-full mr-2"></span>
                    Viewer Role
                  </h3>
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

            <!-- Contacts Module Section -->
            <section 
              v-if="activeSection === 'contacts'" 
              class="bg-white rounded-lg shadow-sm p-8 mb-8"
            >
              <h2 class="text-2xl font-bold text-gray-900 mb-6">Contacts Module</h2>
              
              <div class="space-y-8">
                <div>
                  <h3 class="text-lg font-semibold text-gray-900 mb-3">Creating a Contact</h3>
                  <ol class="list-decimal pl-6 text-gray-700 space-y-2">
                    <li>Go to <strong>Contacts</strong> → <strong>New Contact</strong></li>
                    <li>Fill in the required field: <strong>Name</strong></li>
                    <li>Add optional details: Email, Phone, Company, Position, etc.</li>
                    <li>Click <strong>Save</strong></li>
                  </ol>
                </div>

                <div>
                  <h3 class="text-lg font-semibold text-gray-900 mb-3">Editing a Contact</h3>
                  <ol class="list-decimal pl-6 text-gray-700 space-y-2">
                    <li>Go to <strong>Contacts</strong></li>
                    <li>Click on a contact to view details</li>
                    <li>Click <strong>Edit</strong> button</li>
                    <li>Modify the information</li>
                    <li>Click <strong>Save</strong></li>
                  </ol>
                </div>

                <div>
                  <h3 class="text-lg font-semibold text-gray-900 mb-3">Importing Contacts</h3>
                  <ol class="list-decimal pl-6 text-gray-700 space-y-2">
                    <li>Go to <strong>Contacts</strong> → <strong>Import</strong></li>
                    <li>Download the CSV template (optional)</li>
                    <li>Select your CSV file</li>
                    <li>Click <strong>Preview</strong> to verify data</li>
                    <li>Click <strong>Import</strong> to complete</li>
                  </ol>
                  <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded">
                    <p class="text-sm text-blue-900"><strong>Supported columns:</strong> name, email, phone, company, position, country, type</p>
                  </div>
                </div>

                <div>
                  <h3 class="text-lg font-semibold text-gray-900 mb-3">Exporting Contacts</h3>
                  <ol class="list-decimal pl-6 text-gray-700 space-y-2">
                    <li>Go to <strong>Contacts</strong></li>
                    <li>Apply filters (optional)</li>
                    <li>Click <strong>Export</strong> button</li>
                    <li>Download CSV file</li>
                  </ol>
                </div>
              </div>
            </section>

            <!-- Tasks Module Section -->
            <section 
              v-if="activeSection === 'tasks'" 
              class="bg-white rounded-lg shadow-sm p-8 mb-8"
            >
              <h2 class="text-2xl font-bold text-gray-900 mb-6">Tasks Module</h2>
              
              <div class="space-y-8">
                <div>
                  <h3 class="text-lg font-semibold text-gray-900 mb-3">Creating a Task</h3>
                  <ol class="list-decimal pl-6 text-gray-700 space-y-2">
                    <li>Go to <strong>Tasks</strong> → <strong>New Task</strong></li>
                    <li>Enter task details:
                      <ul class="list-disc pl-6 mt-2 space-y-1">
                        <li><strong>Title</strong> (required)</li>
                        <li>Description (optional)</li>
                        <li>Project (optional)</li>
                        <li>Priority (optional)</li>
                        <li>Due Date (optional)</li>
                      </ul>
                    </li>
                    <li>Click <strong>Save</strong></li>
                  </ol>
                </div>

                <div>
                  <h3 class="text-lg font-semibold text-gray-900 mb-3">Task Status</h3>
                  <p class="text-gray-700 mb-3">Tasks have four statuses:</p>
                  <div class="grid grid-cols-2 gap-4">
                    <div class="p-3 bg-gray-50 rounded border-l-4 border-gray-400">
                      <p class="font-semibold text-gray-900">To Do</p>
                      <p class="text-sm text-gray-600">Task not started</p>
                    </div>
                    <div class="p-3 bg-blue-50 rounded border-l-4 border-blue-500">
                      <p class="font-semibold text-gray-900">In Progress</p>
                      <p class="text-sm text-gray-600">Currently being worked on</p>
                    </div>
                    <div class="p-3 bg-yellow-50 rounded border-l-4 border-yellow-500">
                      <p class="font-semibold text-gray-900">In Review</p>
                      <p class="text-sm text-gray-600">Waiting for approval</p>
                    </div>
                    <div class="p-3 bg-green-50 rounded border-l-4 border-green-500">
                      <p class="font-semibold text-gray-900">Done</p>
                      <p class="text-sm text-gray-600">Task completed</p>
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
                    <li>Assignee can <strong>Accept</strong> or <strong>Reject</strong></li>
                  </ol>
                </div>

                <div>
                  <h3 class="text-lg font-semibold text-gray-900 mb-3">Collaborating on Tasks</h3>
                  <ul class="list-disc pl-6 text-gray-700 space-y-2">
                    <li><strong>Comments:</strong> Add comments to discuss task details</li>
                    <li><strong>Attachments:</strong> Attach files for reference</li>
                    <li><strong>Tags:</strong> Categorize tasks with labels</li>
                    <li><strong>Dependencies:</strong> Link related tasks</li>
                  </ul>
                </div>

                <div>
                  <h3 class="text-lg font-semibold text-gray-900 mb-3">Kanban Board</h3>
                  <p class="text-gray-700">Visualize your workflow in the Board view:</p>
                  <ol class="list-decimal pl-6 text-gray-700 space-y-2 mt-2">
                    <li>Go to <strong>Tasks</strong> → <strong>Board</strong></li>
                    <li>See tasks organized by status</li>
                    <li>Drag tasks between columns to update status</li>
                    <li>Click tasks for detailed view</li>
                  </ol>
                </div>
              </div>
            </section>

            <!-- Projects Module Section -->
            <section 
              v-if="activeSection === 'projects'" 
              class="bg-white rounded-lg shadow-sm p-8 mb-8"
            >
              <h2 class="text-2xl font-bold text-gray-900 mb-6">Projects Module</h2>
              
              <div class="space-y-8">
                <div>
                  <h3 class="text-lg font-semibold text-gray-900 mb-3">Creating a Project</h3>
                  <ol class="list-decimal pl-6 text-gray-700 space-y-2">
                    <li>Go to <strong>Projects</strong> → <strong>New Project</strong></li>
                    <li>Fill in details:
                      <ul class="list-disc pl-6 mt-2 space-y-1">
                        <li><strong>Name</strong> (required)</li>
                        <li>Description (optional)</li>
                        <li>Status (optional)</li>
                        <li>Start/End dates (optional)</li>
                      </ul>
                    </li>
                    <li>Click <strong>Save</strong></li>
                  </ol>
                </div>

                <div>
                  <h3 class="text-lg font-semibold text-gray-900 mb-3">Adding Tasks to Projects</h3>
                  <p class="text-gray-700">When creating or editing a task, select the project from the <strong>Project</strong> dropdown. Tasks automatically appear in the project.</p>
                </div>

                <div>
                  <h3 class="text-lg font-semibold text-gray-900 mb-3">Viewing Projects</h3>
                  <ol class="list-decimal pl-6 text-gray-700 space-y-2">
                    <li>Go to <strong>Projects</strong></li>
                    <li>Click on a project to view details</li>
                    <li>See all associated tasks</li>
                    <li>View project statistics and progress</li>
                  </ol>
                </div>
              </div>
            </section>

            <!-- Notifications Section -->
            <section 
              v-if="activeSection === 'notifications'" 
              class="bg-white rounded-lg shadow-sm p-8 mb-8"
            >
              <h2 class="text-2xl font-bold text-gray-900 mb-6">Notifications</h2>
              
              <div class="space-y-6">
                <div>
                  <h3 class="text-lg font-semibold text-gray-900 mb-3">Types of Notifications</h3>
                  <ul class="list-disc pl-6 text-gray-700 space-y-2">
                    <li><strong>Task Assigned:</strong> You've been assigned a new task</li>
                    <li><strong>Task Commented:</strong> Someone commented on your task</li>
                    <li><strong>Task Status Changed:</strong> Task status was updated</li>
                    <li><strong>File Attached:</strong> File added to a task</li>
                    <li><strong>Mention:</strong> Someone mentioned you</li>
                  </ul>
                </div>

                <div>
                  <h3 class="text-lg font-semibold text-gray-900 mb-3">Managing Notifications</h3>
                  <ol class="list-decimal pl-6 text-gray-700 space-y-2">
                    <li>Click the <strong>Bell icon</strong> in the header</li>
                    <li>View recent notifications</li>
                    <li>Click to navigate to task</li>
                    <li>Mark as read individually or all at once</li>
                  </ol>
                </div>
              </div>
            </section>

            <!-- Workflows Section -->
            <section 
              v-if="activeSection === 'workflows'" 
              class="bg-white rounded-lg shadow-sm p-8 mb-8"
            >
              <h2 class="text-2xl font-bold text-gray-900 mb-6">Common Workflows</h2>
              
              <div class="space-y-8">
                <div>
                  <h3 class="text-lg font-semibold text-gray-900 mb-4">Standard Task Completion Workflow</h3>
                  
                  <div class="space-y-4">
                    <div class="flex items-start">
                      <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-8 w-8 rounded-full bg-blue-600 text-white font-semibold">1</div>
                      </div>
                      <div class="ml-4">
                        <h4 class="font-semibold text-gray-900">Login</h4>
                        <p class="text-gray-600 text-sm">Enter your credentials and access the application</p>
                      </div>
                    </div>

                    <div class="flex items-start">
                      <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-8 w-8 rounded-full bg-blue-600 text-white font-semibold">2</div>
                      </div>
                      <div class="ml-4">
                        <h4 class="font-semibold text-gray-900">Create/View Contact</h4>
                        <p class="text-gray-600 text-sm">Add a new contact or locate an existing one</p>
                      </div>
                    </div>

                    <div class="flex items-start">
                      <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-8 w-8 rounded-full bg-blue-600 text-white font-semibold">3</div>
                      </div>
                      <div class="ml-4">
                        <h4 class="font-semibold text-gray-900">Create Task</h4>
                        <p class="text-gray-600 text-sm">Create a new task with title, description, and details</p>
                      </div>
                    </div>

                    <div class="flex items-start">
                      <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-8 w-8 rounded-full bg-blue-600 text-white font-semibold">4</div>
                      </div>
                      <div class="ml-4">
                        <h4 class="font-semibold text-gray-900">Assign Task</h4>
                        <p class="text-gray-600 text-sm">Assign the task to a team member</p>
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
                        <p class="text-gray-600 text-sm">Change status to Done when task is finished</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </section>

            <!-- Troubleshooting Section -->
            <section 
              v-if="activeSection === 'troubleshooting'" 
              class="bg-white rounded-lg shadow-sm p-8 mb-8"
            >
              <h2 class="text-2xl font-bold text-gray-900 mb-6">Troubleshooting</h2>
              
              <div class="space-y-6">
                <div class="border rounded-lg p-4 bg-red-50">
                  <h3 class="text-lg font-semibold text-red-900 mb-2">Permission Denied Error</h3>
                  <p class="text-red-800"><strong>Solution:</strong> Check your user role (Admin/Editor/Viewer) and verify you have permission for this action. Contact your administrator if you need access.</p>
                </div>

                <div class="border rounded-lg p-4 bg-yellow-50">
                  <h3 class="text-lg font-semibold text-yellow-900 mb-2">Item Not Appearing</h3>
                  <p class="text-yellow-800"><strong>Solution:</strong> Try refreshing the page (Ctrl+F5), check if filters are hiding the item, and verify permissions.</p>
                </div>

                <div class="border rounded-lg p-4 bg-blue-50">
                  <h3 class="text-lg font-semibold text-blue-900 mb-2">Upload Failed</h3>
                  <p class="text-blue-800"><strong>Solution:</strong> Check file size (usually max 10MB), verify file format, and try uploading again.</p>
                </div>

                <div class="border rounded-lg p-4 bg-purple-50">
                  <h3 class="text-lg font-semibold text-purple-900 mb-2">Notification Not Received</h3>
                  <p class="text-purple-800"><strong>Solution:</strong> Check notification settings, verify internet connection, refresh notifications page, and check spam folder for email.</p>
                </div>

                <div class="border rounded-lg p-4 bg-green-50">
                  <h3 class="text-lg font-semibold text-green-900 mb-2">Import CSV Failed</h3>
                  <p class="text-green-800"><strong>Solution:</strong> Verify CSV format matches template, check required columns exist, ensure no duplicate emails, and retry.</p>
                </div>
              </div>
            </section>

            <!-- FAQ Section -->
            <section 
              v-if="activeSection === 'faq'" 
              class="bg-white rounded-lg shadow-sm p-8 mb-8"
            >
              <h2 class="text-2xl font-bold text-gray-900 mb-6">Frequently Asked Questions</h2>
              
              <div class="space-y-4">
                <div 
                  v-for="(faq, index) in helpContent.faqs" 
                  :key="index"
                  class="border rounded-lg"
                >
                  <button 
                    @click="toggleFaq(index)"
                    class="w-full px-4 py-3 text-left flex justify-between items-center hover:bg-gray-50"
                  >
                    <span class="font-semibold text-gray-900">{{ faq.question }}</span>
                    <span class="text-gray-500">{{ expandedFaqs[index] ? '−' : '+' }}</span>
                  </button>
                  
                  <div 
                    v-if="expandedFaqs[index]" 
                    class="px-4 py-3 border-t bg-gray-50 text-gray-700"
                  >
                    {{ faq.answer }}
                  </div>
                </div>
              </div>
            </section>

            <!-- Getting Help Section -->
            <section 
              v-if="activeSection === 'help'" 
              class="bg-white rounded-lg shadow-sm p-8 mb-8"
            >
              <h2 class="text-2xl font-bold text-gray-900 mb-6">Getting Help</h2>
              
              <div class="space-y-6">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                  <h3 class="text-lg font-semibold text-blue-900 mb-2">Contact Administrator</h3>
                  <p class="text-blue-800">Reach out to your system administrator for technical support or access issues.</p>
                </div>

                <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                  <h3 class="text-lg font-semibold text-green-900 mb-2">Best Practices</h3>
                  <ul class="list-disc pl-6 text-green-800 space-y-2">
                    <li>Use clear, descriptive task titles</li>
                    <li>Add detailed descriptions for context</li>
                    <li>Set appropriate due dates</li>
                    <li>Use projects to organize related tasks</li>
                    <li>Comment regularly to keep team updated</li>
                    <li>Attach supporting files</li>
                  </ul>
                </div>
              </div>
            </section>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  helpContent: {
    type: Object,
    required: true
  }
});

const searchQuery = ref('');
const activeSection = ref('getting-started');
const expandedFaqs = ref({});

const filteredSections = computed(() => {
  if (!searchQuery.value) {
    return props.helpContent.sections;
  }
  
  const query = searchQuery.value.toLowerCase();
  return props.helpContent.sections.filter(section => 
    section.title.toLowerCase().includes(query) ||
    section.subsections.some(sub => 
      sub.title.toLowerCase().includes(query) ||
      sub.content.toLowerCase().includes(query)
    )
  );
});

const toggleFaq = (index) => {
  expandedFaqs.value[index] = !expandedFaqs.value[index];
};
</script>